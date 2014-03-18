function initializeChosen() {
    $(".chosen-select").chosen();
    $(".chosen-container, .chosen-container input").css("width", "100%");
    $(".chosen-container input").css("height", "25px");
}

function initPage() {
    if ($("#groupMessages").attr("data-id")) {
        // Scroll message list to the bottom
        $(".group_message_scroll").each(function() {
            this.scrollTop = this.scrollHeight;
        });
    } else {
        initializeChosen();
    }
}

function updatePage() {
    $(".messaging").load(window.location.pathname + " .messaging > *", function() {
        initPage();
    });
}

$(document).ready(function() {
    initPage();
});

// Use "on" instead of just "click"/"submit", so that new elements of that class added
// to the page using $.load() also respond to events

// Response submit event
$(".page").on("submit", ".alt_compose_form", function(event) {
    // Don't let the link change the web page,
    // AJAX will handle the click
    event.preventDefault();

    sendResponse();
});

// Discussion create event
$(".page").on("submit", ".compose_form", function(event) {
    event.preventDefault();
    sendMessage();
});

// Group click event
$(".page").on("click", ".chats a", function(event) {
    event.preventDefault();
    redirect($(this).attr("data-id"));
});
$(".page").on("click", ".compose-link", function(event) {
    event.preventDefault();
    redirect();
});


/**
 * Perform an AJAX request to send a response to a message group
 */
function sendResponse() {
    // If the Ladda class exists, use it to style the button
    if (typeof(Ladda) !== "undefined") {
        var l = Ladda.create( document.querySelector( '#composeButton' ) );
        l.start();
    }

    groupId = $("#groupMessages").attr("data-id");

    $.ajax({
        type: "POST",
        dataType: "json",
        url: baseURLNoHost + "/ajax/sendMessage.php",
        data: {
            group_to: groupId,
            content: $("#composeArea").val()
        }
    }).done(function( msg ) {
        if (l)
            l.stop();

        // Find the notification type
        type = msg.success ? "success" : "error";

        notify(msg.message, type);
        if (msg.success)
            updatePage();
    });
};

/**
 * Perform an AJAX request to create a new message group
 */
function sendMessage() {
    if (typeof(Ladda) !== "undefined") {
        var l = Ladda.create( document.querySelector( '#composeButton' ) );
        l.start();
    }

    // Generate a comma-separated list of recipients which the AJAX script will read
    recipients = ""

    if ($("#compose_recipients").val())
        recipients = $("#compose_recipients").val().join(',')

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
        type = msg.success ? "success" : "error";

        notify(msg.message, type);

        if (msg.success) {
            redirect(msg.id);
        }
    });
};

function redirect(groupId=null) {
    var stateObj = { group: groupId };

    url = baseURLNoHost + "/messages";
    url += (groupId) ? "/"+groupId : "";

    history.pushState(stateObj, "", url);

    updatePage();
}
