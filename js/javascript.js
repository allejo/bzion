var compose_modal;
var response_group = 0;

/**
function showComposeModal(object_id, group_id) {
    compose_modal = Nifty.modal({
        content: $("#"+object_id).html(),
        background: "#e74c3c",
        effect: 1,
    });
    response_group = group_id;
}
*/

$(".group_link").click(function(event) {
    // Don't let the link change the web page,
    // AJAX will handle the click
    event.preventDefault();

    url = $(this).attr("href");
    id  = $(this).attr("data-id");

    document.location.hash = id;
    $("#group_messages").load(url + " #group_messages");


});

function sendResponse() {
    var l = Ladda.create( document.querySelector( '#composeButton' ) );
    l.start();
    $.ajax({
        type: "POST",
        dataType: "json",
        url: baseURL + "/ajax/sendMessage.php",
        data: { group_to: response_group, content: $("#composeArea").val() }
        }).done(function( msg ) {
            l.stop();
            compose_modal.hide(function() { alert(1) } );

            // Find the notification type
            type = msg.success ? "success" : "error";

            notify(msg.message, type);
            $("#group_messages").load(" #group_messages");
        });
};
notify("Test");
function notify(message, type) {

    // Default to "success" if the caller hasn't specified a type
    type = typeof type !== 'undefined' ? type : 'success';

    not = $(".notification");

    $(".notification").css("top", "-" + $(".notification").outerHeight( true ) + "px");
    $(".notification").attr("class", "notification notification-" + type);
    // Position element in the center
    $(".notification").css("left", Math.max(0, (($(window).width() - $(".notification").outerWidth()) / 2) + $(window).scrollLeft()) + "px");

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

    //alert("-" + $(".notification").outerHeight( true ));
    $( ".notification" ).animate({
        top: "0"
        }, 500, function() {
            //notify(message + "," + message);
    });
}

function hideNotification() {

}

