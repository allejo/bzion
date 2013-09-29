var response_group = 0;

function initializeChosen() {
    $(".chosen-select").chosen();
    $(".chosen-container, .chosen-container input").css("width", "100%");
    $(".chosen-container input").css("height", "25px");
}

function updatePage() {
    // Load the page if just the hash is provided in the URL, example:
    // http://bzion.com/messages#21
    //
    // TODO: Fix for IE
    if (document.location.hash) {
        var hash = document.location.hash.substring(1);
        url = baseURL + "/messages/";
        if (hash != "new") {
            url += hash;
            $(".groups").load(url + " .groups > *");
            $("#groupMessages").load(url + " #groupMessages > *", function() {
                // Scroll message list to the bottom
                $(".group_message_scroll").each(function() {
                    this.scrollTop = this.scrollHeight;
                });
            });
        } else {
            $("#groupMessages").load(url + " #groupMessages > *", function() {
                initializeChosen()
            });
        }
    } else {
        initializeChosen();
    }
}

$(document).ready(function() {
    updatePage();
});

// Use "on" instead of just "click", so that new elements of that class added
// to the page using $.load() also respond to events
$(".content").on("click", ".group_link", function(event) {
    // Don't let the link change the web page,
    // AJAX will handle the click
    event.preventDefault();

    url = $(this).attr("href");
    id  = $(this).attr("data-id");

    document.location.hash = id;
    response_group = id;

    updatePage();

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

    $.ajax({
        type: "POST",
        dataType: "json",
        url: baseURL + "/ajax/sendMessage.php",
        data: {
            group_to: response_group,
            content: $("#composeArea").val()
        }
    }).done(function( msg ) {
        if (l)
            l.stop();

        // Find the notification type
        type = msg.success ? "success" : "error";

        notify(msg.message, type);
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

    // Generate a comma-separated of recipients which the AJAX script will read
    recipients = ""

    if ($("#compose_recipients").val())
        recipients = $("#compose_recipients").val().join(',')

    $.ajax({
        type: "POST",
        dataType: "json",
        url: baseURL + "/ajax/sendMessage.php",
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
            document.location.hash = msg.id;
            updatePage();
        }
    });
};


/**
 * Show a notification
 * @todo Convert this to a class
 * @todo Style the notification
 * @param message string The message to display
 * @param type string Can be "success" or "error"
 */
function notify(message, type) {

    // Default to "success" if the caller hasn't specified a type
    type = typeof type !== 'undefined' ? type : 'success';

    not = $(".notification");

    not.css("top", "-" + not.outerHeight( true ) + "px");
    not.attr("class", "notification notification-" + type);
    // Position element in the center
    not.css("left", Math.max(0, (($(window).width() - not.outerWidth()) / 2) + $(window).scrollLeft()) + "px");

    // Determine which icon should be used
    switch(type) {
        case "success":
          icon = "ok"
          break;
        case "error":
          icon = "exclamation-sign"
          break;
        default:
          icon = "question"
        }

    $(".notification i").attr("class", "icon-"+icon);

    $(".notification span").html(message);

    not.animate({
        top: "0"
        }, 500, function() {
    });
}

function hideNotification() {

}

