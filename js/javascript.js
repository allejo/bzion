var compose_modal;
var response_group = 0;

function showComposeModal(object_id, group_id) {
    compose_modal = Nifty.modal({
        content: $("#"+object_id).html(),
        background: "#e74c3c",
        effect: 1,
    });
    response_group = group_id;
}

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
            compose_modal.hide();
            alert(msg.message);
            $("#group_messages").load(" #group_messages");
        });
};