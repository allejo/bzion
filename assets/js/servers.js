function initPage() {
    // Hide any dimmers that might have been left
    $(".dimmer, .spinner").fadeOut('fast');

    var dimmer  = $("<div/>").hide().addClass("dimmer");
    var spinner = $("<div/>").hide().addClass("spinner").text("Loading...");

    // Add an invisible dimmer to elements that don't have one yet
    $(".dimmable").prepend(dimmer).prepend(spinner);

    $(".server").startSpinners().each(function() {
        var serverUrl = baseURLNoHost + "/servers/" + $(this).attr("data-id");
        var server    = $(this);

        server.find(".server_info").load(serverUrl, function() {
            server.stopSpinners();
        });
    });
}

$(document).ready(function() {
    initPage();
});

$.fn.startSpinners = function() {
    this.each(function() {
        $(this).children(".dimmable").children(".dimmer, .spinner").fadeIn('fast');
    });
    return this;
};

$.fn.stopSpinners = function() {
    this.children(".dimmable").children(".dimmer, .spinner").fadeOut('fast');
    return this;
};
