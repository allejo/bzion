var sentMessage = null;

/**
 * Schedule a refresh
 * @param {int} id The ID of the new message about which we were notified by the event server
 */
Queue.prototype.addRefresh = function(id) {
    var queue = this;

    this.add(function() {
        var deferred = this;

        // The refresh is going to take place later in the queue, there is
        // no need to refresh if there are items waiting in the queue
        if (!queue.isLast()) {
            return true;
        }

        // If the server informs us about a message we've already sent, don't refresh again
        if (sentMessage === id) {
            return true;
        }

        $.get(document.URL, { end: getLastID() }, function(data) {
            html = $($.parseHTML(data));
            updateLastMessage(html);

            deferred.resolve();
        }, "html").error(function() {
            deferred.reject();
        });
    });
};

// A queue for AJAX calls for new messages
var q = new Queue();

reactor.addEventListener("push-event", function(data) {
    if (data.type != 'message') {
        return;
    }

    conversationId = $("#conversationMessages").attr("data-id");

    if (conversationId && conversationId == data.data.conversation) {
        q.addRefresh(data.data.message);
    } else {
        updateSelectors([".conversations", "nav"]);
    }
});

var messageView, messageList, conversationMessages;

function initPage() {
    var noMoreScrolling = false;
    conversationMessages = $("#conversationMessages");

    if (conversationMessages.attr("data-id")) {
        messageView = $("#messageView");
        messageList = messageView.children("ul");
        var olderMessageLink = messageView.hideOlder();

        // Scroll message list to the bottom
        messageView.scrollTop(messageView.prop("scrollHeight"));

        var infiniteScroll = function() {
            if(messageView.scrollTop() < 40 && !noMoreScrolling && olderMessageLink !== undefined) {
                noMoreScrolling = true;

                // TODO: Use a loading indicator to let the user know that more
                // messages are being loaded

                $.get(olderMessageLink + "&nolayout&reviewLastDetails=1&format=json", function(data) {
                    // Properly scroll the message view
                    var firstMessage = messageView.find("li").eq(0);
                    var curOffset = firstMessage.offset().top - messageView.scrollTop();

                    html = $($.parseHTML(data.content));

                    if (data.hideLastDetails) {
                        messageList.find(".details").first().remove();
                    }

                    olderMessageLink = html.hideOlder();
                    messageList.prepend(html.children("li"));
                    messageView.scrollTop(firstMessage.offset().top - curOffset);
                    noMoreScrolling = false;

                    // Scroll up more if we're not there yet
                    infiniteScroll();
                }, "json");
            }
        };

        // Load older messages when the user scrolls to the top
        messageView.scroll(infiniteScroll);

        // Load older messages automatically if there's enough space
        infiniteScroll();
    }

    updateFavicon();
}

$(document).ready(function() {
    initPage();
});

var pageSelector = $(".c-page");

// Hide the "load new messages" div for non-JS users
$.fn.hideOlder = function() {
    var elem = this.find(".c-messenger__conversation__archiver");

    if (elem.length === 0) {
        elem = this.closest(".c-messenger__conversation__archiver");
    }

    return elem.css('display', 'none').find("a").attr("href");
};

function updateSelector(selector) {
    $(selector).load(window.location.pathname + " " + selector + " > *", function() {
        initPage();
    });
}

function setSelectors(selectors, data) {
    $.each(selectors, function(i, key) {
        var selector = data.closest(key);
        if (!selector.length)
            selector = data.find(key);

        $(key).html(selector.html());
    });
}

function updateSelectors(selectors) {
    $.get(window.location.pathname, function(data) {
        setSelectors(selectors, $(data));
        initPage();
    }, 'html');
}

function updatePage() {
    return updateSelectors([".c-page", "nav"]);
}

function updateLastMessage(html) {
    setSelectors([".c-conversations", "nav"], html);

    loadedView = html.find(".c-messenger__conversation__messages li");
    loadedView.appendTo(messageList);

    // Scroll message list to the bottom
    messageView.animate({ scrollTop: messageView.prop("scrollHeight") });

    updateFavicon();
}

// Use "on" instead of just "click"/"submit", so that new elements of that class added
// to the page using $.load() also respond to events

// Response submit event
pageSelector.on("submit", ".c-messenger__conversation__response", function(event) {
    // Don't let the link change the web page,
    // AJAX will handle the click
    event.preventDefault();

    var selector = $(this);

    q.add(function() {
        var deferred = this;

        // TODO: Fix issue when a lot of messages are added to the conversation
        // before the user presses Submit
        sendMessage(selector, function(msg, form) {
            sentMessage = msg.id;

            html = $(msg.content);
            updateLastMessage(html);
            form[0].reset();

            deferred.resolve();
        }, function() {
            deferred.reject();
        });
    });
});

// Discussion create event
pageSelector.on("submit", ".compose_form", function(event) {
    event.preventDefault();

    sendMessage($(this), function(msg) {
        redirect(msg.id);
    });
});

// Conversation click event
pageSelector.on("click", ".c-messages__inbox__message", function(event) {
    event.preventDefault();
    redirect($(this).attr("data-id"));
});
pageSelector.on("click", ".compose-link", function(event) {
    event.preventDefault();
    redirect();
});

// Response Ctrl+Enter event
pageSelector.on("keydown", ".input_compose_area", function(event) {
    if ((event.keyCode === 10 || event.keyCode === 13) && event.ctrlKey) {
        $(this).trigger('submit');
    }
});

/**
 * Get the ID of the last sent message
 */
function getLastID() {
    return conversationMessages.find("li").last().attr('data-id');
}

/**
 * Perform an AJAX request to send a message
 */
function sendMessage(form, onSuccess, onError) {
    if (typeof(Ladda) !== "undefined") {
        var l = Ladda.create( form.find('button').get()[0] );
        l.start();
    }

    $.ajax({
        type: form.attr('method'),
        url: form.attr('action') + "?end=" + getLastID() + '&hideFirstDetails=1',
        data: form.serialize() + "&format=json",
        dataType: "json"
    }).done(function( msg ) {
        // Find the notification type
        var type = msg.success ? "success" : "error";

        notify(msg.message, type);
        if (msg.success) {
            onSuccess(msg, form);
        }
    }).error(function( jqXHR, textStatus, errorThrown ) {
        // TODO: Catch bad JSON
        var message = (errorThrown === "") ? textStatus : errorThrown;
        notify(message, "error");

        if (onError !== undefined) {
            onError.apply();
        }
    }).complete(function() {
        if (l)
            l.stop();
    });
}

function redirect(conversationTo) {
    var conversationId = typeof conversationTo !== 'undefined' ? conversationTo : null;
    var stateObj = { conversation: conversationId };
    var url = baseURLNoHost + "/messages";

    url += (conversationId) ? "/"+conversationId : "";

    history.pushState(stateObj, "", url);

    updatePage();
}
