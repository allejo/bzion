/**
 * Show a notification
 * @todo Convert this to a class
 * @param message string The message to display
 * @param type string Can be "success" or "error"
 */
function notify(message, type) {
    // Default to "success" if the caller hasn't specified a type
    type = typeof type !== 'undefined' ? type : 'success';

    var not = $(".notification");

    not.show();
    not.css("top", "-" + not.outerHeight( true ) + "px");
    not.attr("class", "notification " + type);
    // Position element in the center
    not.css("left", Math.max(0, (($(window).width() - not.outerWidth()) / 2) + $(window).scrollLeft()) + "px");

    // Determine which icon should be used
    switch(type) {
        case "success":
            icon = "check";
            break;
        case "error":
            icon = "exclamation";
            break;
        default:
            icon = "question";
            break;
    }

    $(".notification").find("i").attr("class", "fa fa-"+icon);

    $(".notification").find("span").html(message);

    not.animate({
        top: "0"
    }, 500);

    not.delay(2000).fadeOut(1000);
}

$(function () {
    $("#mobile-menu").click(function() {
        $("#menu-pages").slideToggle();
    });
});

(function( $ ){
    var format = function(item) {
        return item.username;
    };

    $.fn.playerlist = function(opts) {
        var players = this.find(".player-select");
        var selectType = this.find(".player-select-type");

        var options = $.extend({
            exceptMe: false
        }, opts);

        players.attr('placeholder', 'Enter player...')
            .css('width', '400px')
            .select2({
                ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
                    url: baseURLNoHost + "/players",
                    dataType: 'json',
                    data: function (term, page) {
                        var data = {
                            format: 'json',
                            startsWith: term, // search term
                        };

                        if (options.exceptMe) {
                            data.exceptMe = null;
                        }

                        return data;
                    },
                    results: function (data, page) {
                        return {results: data.players};
                    },
                },
                formatSelection: format,
                formatResult: format,
        });

        // Make sure that PHP knows we are sending player IDs, not usernames
        selectType.attr('value', '0');

        if (players.attr('data-value') !== undefined) {
            players.select2("data", JSON.parse(players.attr('data-value')));
        }

        return this;
    };
})( jQuery );
