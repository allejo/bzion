function initializeChosen() {
    $(".chosen-select").chosen();
    $(".chosen-container, .chosen-container input").css("width", "100%");
    $(".chosen-container input").css("height", "25px");
}

function initPage() {
    if ($("#groupMessages").attr("data-id")) {
        // Scroll message list to the bottom
        var messageView = $("#messageView");
        messageView.scrollTop(messageView.height());
    } else {
        initializeChosen();
    }
}

function updatePage() {
    $(".scrollable_messages").load(window.location.pathname + " .scrollable_messages > *", function() {
        initPage();
    });
}

$(document).ready(function() {
    initPage();
});

// Use "on" instead of just "click"/"submit", so that new elements of that class added
// to the page using $.load() also respond to events
var pageSelector = $(".messaging");

// Response submit event
pageSelector.on("submit", ".reply_form", function(event) {
    // Don't let the link change the web page,
    // AJAX will handle the click
    event.preventDefault();

    sendResponse($(this));
});

// Discussion create event
pageSelector.on("submit", ".compose_form", function(event) {
    event.preventDefault();
    sendMessage();
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
 * Perform an AJAX request to send a response to a message group
 */
function sendResponse(form) {
    // If the Ladda class exists, use it to style the button
    if (typeof(Ladda) !== "undefined") {
        var l = Ladda.create( document.querySelector( '#composeButton' ) );
        l.start();
    }

    groupId = $("#groupMessages").attr("data-id");

    $.ajax({
        type: form.attr('method'),
        url: form.attr('action'),
        data: form.serialize() + "&format=json",
        dataType: "json"
    }).done(function( msg ) {
        if (l)
            l.stop();

        // Find the notification type
        var type = msg.success ? "success" : "error";

        notify(msg.message, type);
        if (msg.success) {
            updatePage();
            form[0].reset();
        }
    });
}

/**
 * Perform an AJAX request to create a new message group
 */
function sendMessage() {
    if (typeof(Ladda) !== "undefined") {
        var l = Ladda.create( document.querySelector( '#composeButton' ) );
        l.start();
    }

    // Generate a comma-separated list of recipients which the AJAX script will read
    var recipients = "";
    var recipientSelector = $("#compose_recipients");

    if (recipientSelector.val())
        recipients = recipientSelector.val().join(',');

    $.ajax({
        type: "POST",
        dataType: "json",
        url: baseURLNoHost + "/ajax/sendMessage.php",
        data: {
            subject: $("#compose_subject").val(),
            to:  recipients,
            content: $("#composeArea").val()
        }
    }).done(function( msg ) {
        if (l)
            l.stop();

        // Find the notification type
        var type = msg.success ? "success" : "error";

        notify(msg.message, type);

        if (msg.success) {
            redirect(msg.id);
        }
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
