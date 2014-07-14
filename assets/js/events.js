$(document).ready(function() {

    if (config.websocket) {
        var conn = new WebSocket('ws://' + window.location.hostname + ':' + config.websocket.port);

        conn.onmessage = function(e) {
            var data = JSON.parse(e.data).event;
            notify(data.message, 'success');

            var notifications = (data.notification_count > 0) ? data.notification_count : '';
            var messages      = (data.message_count > 0) ? data.message_count : '';

            $(".unreadNotificationCount").text(notifications);
            $(".unreadMessageCount").text(messages);
        };
    }
});
