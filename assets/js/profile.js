/**
 * Perform an AJAX request to update a player's profile
 */
function updateProfile() {

    $.ajax({
        type: "POST",
        dataType: "json",
        url: baseURLNoHost + "/ajax/updateProfile.php",
        data: {
            avatar: $(".profile_avatar").val(),
            description: $(".profile_description").val(),
            timezone: $(".profile_timezone").val()
        }
    }).done(function(msg) {
        // Find the notification type
        type = msg.success ? "success" : "error";

        notify(msg.message, type);
    });

};
