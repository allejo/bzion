/**
 * Show a notification
 * @todo Convert this to a class?
 * @todo Make danger notifications stay on screen longer
 * @param message string The message to display
 * @param type string Can be "success" or "error"
 */
function notify(message, type) {
    // Default to "success" if the caller hasn't specified a type
    type = typeof type !== 'undefined' ? type : 'success';

    var $not = $('<p><i/> <span/></p>')
        .addClass('c-flashbag__item')
        .addClass("c-alert")
        .addClass("c-alert--" + type)
        .hide();

    // Determine which icon should be used
    switch(type) {
        case "success":
            icon = "check";
            break;
        case "danger":
            icon = "exclamation-triangle";
            break;
        default:
            icon = "question";
            break;
    }

    $not.find("i").attr("class", "fa fa-"+icon);
    $not.find("span").html(message);

    $not.prependTo(".c-flashbag")
        .fadeIn(400, function() {
            $(this).delay(3000).fadeOut(1000,function() {
                $(this).remove();
            });
        });
}

$(function () {
    $(window).resize(function () {
        if ($(window).width() >= 992) {
            $(".pages").show();
        }
    });

    $("#mobile-menu").click(function() {
        $("#menu-pages").slideToggle();
    });
});
