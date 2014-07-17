reactor.addEventListener("push-event", function(data) {
    if (data.type != 'message') {
        return;
    }

    groupId = $("#groupMessages").attr("data-id");

    if (groupId && groupId == data.data.discussion) {
        // Find the ID of the last message, so we can fetch anything sent after
        // it
        var lastId = $( "#messageView li:last-child" ).attr('data-id');

        $.get(document.URL, { end: lastId }, function(data) {
            html = $($.parseHTML(data));
            updateLastMessage(html);
        }, "html");
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

var messageView, groupMessages;

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
    groupMessages   = $("#groupMessages");

    if (groupMessages.attr("data-id")) {
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
                    var firstMessage = messageView.find("li").eq(0);
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
}

$(document).ready(function() {
    initPage();
});

var pageSelector = $(".messaging");

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
    $("#groupMessages").startSpinners();
    return updateSelectors([".messaging", "nav"]);
}

function updateLastMessage(html) {
    setSelectors([".conversations", "nav"], html);

    loadedView = html.find("#messageView > *").not(".older_messages");
    loadedView.appendTo(messageView);
    groupMessages.stopSpinners();

    // Scroll message list to the bottom
    messageView.animate({ scrollTop: messageView.prop("scrollHeight") });
}

// Use "on" instead of just "click"/"submit", so that new elements of that class added
// to the page using $.load() also respond to events

// Response submit event
pageSelector.on("submit", ".reply_form", function(event) {
    // Don't let the link change the web page,
    // AJAX will handle the click
    event.preventDefault();

    sendMessage($(this), function(msg, form) {
        html = $(msg.content);
        updateLastMessage(html);
        form[0].reset();
    }, false);
});

// Discussion create event
pageSelector.on("submit", ".compose_form", function(event) {
    event.preventDefault();

    sendMessage($(this), function(msg) {
        redirect(msg.id);
    });
});

// Group click event
pageSelector.on("click", ".chats a", function(event) {
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
 * Perform an AJAX request to send a message
 */
function sendMessage(form, onSuccess, spinners) {
    if (typeof(Ladda) !== "undefined") {
        var l = Ladda.create( form.find('button').get()[0] );
        l.start();
    }

    if (spinners !== undefined && spinners) {
        groupMessages.startSpinners();
    }

    $.ajax({
        type: form.attr('method'),
        url: form.attr('action') + "?end=" + groupMessages.find("li").last().attr('data-id'),
        data: form.serialize() + "&format=json",
        dataType: "json"
    }).done(function( msg ) {
        // Find the notification type
        var type = msg.success ? "success" : "error";

        notify(msg.message, type);
        if (msg.success) {
            onSuccess(msg, form);
        } else {
            groupMessages.stopSpinners();
        }
    }).error(function( jqXHR, textStatus, errorThrown ) {
        var message = (errorThrown === "") ? textStatus : errorThrown;
        // stopSpinners();
        notify(message, "error");
    }).complete(function() {
        if (l)
            l.stop();
        // Don't stop the spinners - wait until the AJAX call to reload the page
        // is complete
    });
}

function redirect(groupTo) {
    var groupId = typeof groupTo !== 'undefined' ? groupTo : null;
    var stateObj = { group: groupId };
    var url = baseURLNoHost + "/messages";

    url += (groupId) ? "/"+groupId : "";

    history.pushState(stateObj, "", url);

    updatePage();
}
