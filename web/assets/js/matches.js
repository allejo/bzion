function format(item) { return item.username; }

$(document).ready(function() {
    $( ".score" ).on("click", function() {
        var currentMatch = $("#match-" + $(this).find(".more_details").attr("rel"));
        currentMatch.slideToggle();

        $(".match_details").not(currentMatch).slideUp();
    });

    var members = [];
    var id = 0;

    var prepareTeam = function(elem, i) {
        members[i] = [];

        var teams =   elem.find(".team-select");
        var players = elem.find(".player-select");

        updateTeam = function(team, pos)  {
            $.ajax({
                url: baseURLNoHost + "/teams/" + team + "/members",
                data: { format: "json" }
            }).done(function( msg ) {
                members[pos] = msg.players;
            });
        };

        teams.find("option").eq(0).removeAttr("value");

        teams.css('width', '800px')
            .select2()
            .on('change', function (e) {
                // Clear the memberlist when changing a team
                players.select2("val", "");
                updateTeam(e.val, i);
            });

        players.attr('placeholder', 'Enter players...')
            .css('width', '400px')
            .select2({
                allowClear: true,
                multiple: true,
                data:function() { return { text:'username', results: members[i] }; },

                formatSelection: format,
                formatResult: format,
            });

        if (teams.val()) {
            updateTeam(teams.val(), i);
        }

        if (players.attr('data-value') !== undefined) {
            players.select2("data", JSON.parse(players.attr('data-value')));
        }

        elem.find(".player-select-type").attr('value', '0');
    };

    $(".match-team").each(function() {
        prepareTeam($(this), id);
        id++;
    });
});
