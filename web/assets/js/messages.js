var sentMessage = null;

/**
 * A Queue for AJAX requests related to messages
 */
var Queue = function() {
    var lastPromise = null;
    var count = 0;

    var setCount = function(d) {
        count = count + d;
    };

    var setup = function() {
        var queueDeferred = $.Deferred();

        // when the previous method returns, resolve this one
        $.when(lastPromise).always(function() {
            queueDeferred.resolve();
        });

        return queueDeferred.promise();
    };

    this.add = function(callback) {
        // Increase count when a callback is added to the queue and decrease it
        // when it's over
        setCount(1);

        var methodDeferred = $.Deferred();
        var queueDeferred = setup();

        methodDeferred.always(function() {
            setCount(-1);
        });

        // execute next queue method
        queueDeferred.done(function() {
            if (callback.apply(methodDeferred)) {
                methodDeferred.resolve();
            }
        });

        lastPromise = methodDeferred.promise();
    };

    /**
     * Whether the currently running callback is the last in the queue
     * @return boolean
     */
    this.isLast = function() {
        return count <= 1;
    };

    /**
     * Schedule a refresh
     * @param {int} id The ID of the new message about which we were notified by the event server
     */
    this.addRefresh = function(id) {
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
};

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

function format(item) { return item.username; }

function initializeSelect() {
    $(".player-select").attr('placeholder','Add a recipient').select2({
        allowClear: true,
        multiple: true,
        minimumInputLength: 1,
        ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
            url: baseURLNoHost + "/players",
            dataType: 'json',
            data: function (term, page) {
                return {
                    format: 'json',
                    exceptMe: null,
                    startsWith: term, // search term
                };
            },
            results: function (data, page) { // parse the results into the format expected by Select2.
                // since we are using custom formatting functions we do not need to alter remote JSON data
                return {results: data.players};
            }
        },
        formatSelection: format,
        formatResult: format,
    });

    // Make sure that PHP knows we are sending player IDs, not usernames
    $("#form_Recipients_ListUsernames").attr('value', '0');
}

var messageView, conversationMessages;

function initPage() {
    // Hide any dimmers that might have been left
    $(".dimmer, .spinner").fadeOut('fast');

    var dimmer  = $("<div/>").hide().addClass("dimmer");
    var spinner = $("<div/>").hide().addClass("spinner").text("Loading...");

    // Add an invisible dimmer to elements that don't have one yet
    $(".dimmable").each(function() {
        if (!$(this).find('.dimmer').length) {
            $(this).prepend(dimmer).prepend(spinner);
        }
    });

    var noMoreScrolling = false;
    conversationMessages   = $("#conversationMessages");

    if (conversationMessages.attr("data-id")) {
        messageView = $("#messageView");
        var olderMessageLink = messageView.hideOlder();

        // Scroll message list to the bottom
        messageView.scrollTop(messageView.prop("scrollHeight"));

        // Load older messages when the user scrolls to the top
        messageView.scroll(function() {
            if($(this).scrollTop() < 20 && !noMoreScrolling && olderMessageLink !== undefined) {
                noMoreScrolling = true;

                // TODO: Let the user know that more messages are being loaded

                $.get(olderMessageLink + "&nolayout", function(data) {
                    // Properly scroll the message view
                    var firstMessage = messageView.find("li.message").eq(0);
                    var curOffset = firstMessage.offset().top - messageView.scrollTop();

                    html = $($.parseHTML(data));
                    olderMessageLink = html.hideOlder();
                    messageView.prepend(html);
                    messageView.scrollTop(firstMessage.offset().top - curOffset);
                    noMoreScrolling = false;
                }, "html");
            }
        });
    } else {
        initializeSelect();
    }

    updateFavicon();
}

$(document).ready(function() {
    initPage();
});

var pageSelector = $(".c-page");

$.fn.startSpinners = function() {
    this.children(".dimmable").children(".dimmer, .spinner").fadeIn('fast');
    return this;
};

$.fn.stopSpinners = function() {
    this.children(".dimmable").children(".dimmer, .spinner").fadeOut('fast');
    return this;
};

// Hide the "load new messages" div for non-JS users
$.fn.hideOlder = function() {
    var elem = this.find(".older_messages");
    if (elem.length === 0) {
        elem = this.closest(".older_messages");
    }

    return elem.hide().attr("href");
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
    $("#conversationMessages").startSpinners();
    return updateSelectors([".c-page", "nav"]);
}

function updateLastMessage(html) {
    setSelectors([".conversations", "nav"], html);

    loadedView = html.find("#messageView > *").not(".older_messages");
    loadedView.appendTo(messageView);
    conversationMessages.stopSpinners();

    // Scroll message list to the bottom
    messageView.animate({ scrollTop: messageView.prop("scrollHeight") });

    updateFavicon();
}

// Use "on" instead of just "click"/"submit", so that new elements of that class added
// to the page using $.load() also respond to events

// Response submit event
pageSelector.on("submit", ".reply_form", function(event) {
    // Don't let the link change the web page,
    // AJAX will handle the click
    event.preventDefault();

    var selector = $(this);

    q.add(function() {
        var deferred = this;

        sendMessage(selector, function(msg, form) {
            sentMessage = msg.id;

            html = $(msg.content);
            updateLastMessage(html);
            form[0].reset();

            deferred.resolve();
        }, function() {
            deferred.reject();
        }, false);
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
    return conversationMessages.find("li.message").last().attr('data-id');
}

/**
 * Perform an AJAX request to send a message
 */
function sendMessage(form, onSuccess, onError, spinners) {
    if (typeof(Ladda) !== "undefined") {
        var l = Ladda.create( form.find('button').get()[0] );
        l.start();
    }

    if (spinners !== undefined && spinners) {
        conversationMessages.startSpinners();
    }

    $.ajax({
        type: form.attr('method'),
        url: form.attr('action') + "?end=" + getLastID(),
        data: form.serialize() + "&format=json",
        dataType: "json"
    }).done(function( msg ) {
        // Find the notification type
        var type = msg.success ? "success" : "error";

        notify(msg.message, type);
        if (msg.success) {
            onSuccess(msg, form);
        } else {
            conversationMessages.stopSpinners();
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
        // Don't stop the spinners - wait until the AJAX call to reload the page
        // is complete
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
