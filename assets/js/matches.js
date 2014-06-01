function format(item) { return item.username; }

$(document).ready(function() {
    $( ".score" ).on("click", function() {
        var currentMatch = $("#match-" + $(this).find(".more_details").attr("rel"));
        currentMatch.slideToggle();

        $(".match_details").not(currentMatch).slideUp();
    });

    var members = [];
    var i = 0;

    $(".match-team").each(function() {
        members[i] = [];

        var teams =   $(this).find(".team-select");
        var players = $(this).find(".player-select");

        updateTeam = function(team) {
            $.ajax({
                url: baseURLNoHost + "/teams/" + team + "/members",
                data: { format: "json" }
            }).done(function( msg ) {
                members[i] = msg.players;
            });
        }

        teams.find("option").eq(0).removeAttr("value");

        teams.css('width', '800px')
            .select2()
            .on('change', function (e) {
                // Clear the memberlist when changing a team
                players.select2("val", "");
                updateTeam(e.val);
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
            updateTeam(teams.val());
        }

        if (players.val()) {
            players.select2("data", JSON.parse(players.val()));
        }

        $(this).find(".player-select-type").attr('value', '0');

        i++;
    });
});
