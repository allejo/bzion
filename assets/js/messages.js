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

function initPage() {
    // Hide any dimmers that might have been left
    stopSpinners();

    var dimmer  = $("<div/>").hide().addClass("dimmer");
    var spinner = $("<div/>").hide().addClass("spinner").text("Loading...");

    // Add an invisible dimmer to elements that don't have one yet
    $(".dimmable").each(function() {
        if ($(this).find('.dimmer').length == 0) {
            $(this).prepend(dimmer).prepend(spinner);
        }
    });

    if ($("#groupMessages").attr("data-id")) {
        // Scroll message list to the bottom
        var messageView = $("#messageView");
        messageView.scrollTop(messageView.prop("scrollHeight"));
    } else {
        initializeSelect();
    }
}

$(document).ready(function() {
    initPage();
});

var pageSelector = $(".messaging");

function stopSpinners() {
    $(".dimmer, .spinner").fadeOut('fast');
}

function updateSelector(selector) {
    $(selector).load(window.location.pathname + " " + selector + " > *", function() {
        initPage();
    });
}

function updateSelectors(selectors) {
    $.get(window.location.pathname, function(data) {
        $.each(selectors, function(i, key) {
            var selector = $(data).closest(key);
            if (!selector.length)
                selector = $(data).find(key);

            $(key).html(selector.html());
        });
        initPage();
    }, 'html');
}

function updatePage() {
    return updateSelectors([".messaging", "nav"]);
}

function updateMessages() {
    return updateSelectors([".scrollable_messages", ".conversations", "nav"]);
}

// Use "on" instead of just "click"/"submit", so that new elements of that class added
// to the page using $.load() also respond to events

// Response submit event
pageSelector.on("submit", ".reply_form", function(event) {
    // Don't let the link change the web page,
    // AJAX will handle the click
    event.preventDefault();

    sendMessage($(this), function(msg, form) {
        updateMessages();
        console.log(form[0].reset());
    });
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
function sendMessage(form, onSuccess) {
    if (typeof(Ladda) !== "undefined") {
        var l = Ladda.create( form.find('button').get()[0] );
        l.start();
    }

    $(".dimmer, .spinner").fadeIn('fast');

    $.ajax({
        type: form.attr('method'),
        url: form.attr('action'),
        data: form.serialize() + "&format=json",
        dataType: "json"
    }).done(function( msg ) {
        // Find the notification type
        var type = msg.success ? "success" : "error";

        notify(msg.message, type);
        if (msg.success) {
            onSuccess(msg, form);
        } else {
            stopSpinners();
        }
    }).error(function( jqXHR, textStatus, errorThrown ) {
        var message = (errorThrown === "") ? textStatus : errorThrown;
        stopSpinners();
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
