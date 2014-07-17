// Event reactor for web socket event propagation
// TODO: Move to a separate JS file and include it on the minified main.js
function Event(name){
  this.name = name;
  this.callbacks = [];
}
Event.prototype.registerCallback = function(callback){
  this.callbacks.push(callback);
};

function Reactor(){
  this.events = {};
}

Reactor.prototype.registerEvent = function(eventName){
  var event = new Event(eventName);
  this.events[eventName] = event;
};

Reactor.prototype.dispatchEvent = function(eventName, eventArgs){
  this.events[eventName].callbacks.forEach(function(callback){
    callback(eventArgs);
  });
};

Reactor.prototype.addEventListener = function(eventName, callback){
  this.events[eventName].registerCallback(callback);
};

var reactor = new Reactor();
reactor.registerEvent("push-event");

$(document).ready(function() {
    if (config.websocket) {
        var conn = new WebSocket('ws://' + window.location.hostname + ':' + config.websocket.port);

        conn.onmessage = function(e) {
            var data = JSON.parse(e.data).event;

            if (data.message) {
                notify(data.message, 'success');
            }

            var notifications = (data.notification_count > 0) ? data.notification_count : '';
            var messages      = (data.message_count > 0) ? data.message_count : '';

            $(".unreadNotificationCount").text(notifications);
            $(".unreadMessageCount").text(messages);

            // Notify all the loaded subsystems about the event (e.g. to update messages)
            reactor.dispatchEvent("push-event", data);
        };
    }
});
