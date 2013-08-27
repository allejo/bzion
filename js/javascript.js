var compose_modal;
var response_group = 0;


$(".group_link").click(function(event) {
    // Don't let the link change the web page,
    // AJAX will handle the click
    event.preventDefault();

    url = $(this).attr("href");
    id  = $(this).attr("data-id");

    document.location.hash = id;
    response_group = id;

    $("#groupMessages").load(url + " #groupMessages > *");

});

/**
 * Perform an AJAX request to send a response to a message group
 */
function sendResponse() {

    // If the Ladda class exists, use it to style the button
    if (typeof(Ladda) != "undefined") {
        var l = Ladda.create( document.querySelector( '#composeButton' ) );
        l.start();
    }

    $.ajax({
        type: "POST",
        dataType: "json",
        url: baseURL + "/ajax/sendMessage.php",
        data: { group_to: response_group, content: $("#composeArea").val() }
        }).done(function( msg ) {
            if (l)
                l.stop();

            // Find the notification type
            type = msg.success ? "success" : "error";

            notify(msg.message, type);
            $("#groupMessages").load(url + " #groupMessages > *");
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

