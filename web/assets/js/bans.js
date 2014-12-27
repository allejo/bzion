$(document).ready(function() {
    var checkbox   = $("#form_automatic_expiration");
    var expiration = $('#form_expiration').parent();

    var clickAction = function() {
        if (checkbox.prop('checked') === true) {
            expiration.show();
        } else {
            expiration.hide();
        }
    };

    checkbox.click(clickAction);

    clickAction();

    $("#form_player").playerlist({
        exceptMe: true
    });
});
