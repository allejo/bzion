// Debug live notifications
reactor.addEventListener("push-event", function(data) {
    console.log("Event received!", data);
});
