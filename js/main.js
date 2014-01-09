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
