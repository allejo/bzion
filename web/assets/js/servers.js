function initPage() {
    // Hide any dimmers that might have been left
    $(".dimmer, .spinner").fadeOut('fast');

    var dimmer  = $("<div/>").hide().addClass("dimmer");
    var spinner = $("<div/>").hide().addClass("spinner").text("Loading...");

    // Add an invisible dimmer to elements that don't have one yet
    $(".js-dimmable").prepend(dimmer).prepend(spinner);

    $(".c-server").each(function() {
        var url = baseURLNoHost + "/servers/" + $(this).attr("data-id");
        $(this).updateServer(url);
    });
}

$(document).ready(function() {
    initPage();
});

$(".app-body").on("click", ".js-refresh", function(event) {
    event.preventDefault();

    $(this).parents(".c-server").updateServer($(this).attr("href"));
});

$.fn.updateServer = function(url) {
    var server = $(this);

    server.startSpinners().find(".js-server__info").load(url, function() {
        server.stopSpinners();
    });
};

$.fn.startSpinners = function() {
    $(this).children(".js-dimmable").children(".dimmer, .spinner").fadeIn('fast');
    return this;
};

$.fn.stopSpinners = function() {
    this.children(".js-dimmable").children(".dimmer, .spinner").fadeOut('fast');
    return this;
};
