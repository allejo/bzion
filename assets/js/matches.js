function format(item) { return item.username; }

$(document).ready(function() {
    $( ".score" ).on("click", function() {
        var currentMatch = $("#match-" + $(this).find(".more_details").attr("rel"));
        currentMatch.slideToggle();

        $(".match_details").not(currentMatch).slideUp();
    });


    $(".match-team").each(function() {
        var teams = { a: [], b: [] };

        $(this).find(".team-select").css('width', '800px').select2().on('change', function(e) {
            console.log(e.val);
            team = e.val;

            $.ajax({
                url: baseURLNoHost + "/teams/" + team + "/members",
                data: { format: "json" }
            }).done(function( msg ) {
                teams.a = msg.players;
            });
        });
        $(this).find(".player-select").css('width', '400px').select2({
            allowClear: true,
            multiple: true,
            data:function() { return { text:'username', results: teams.a }; },

            formatSelection: format,
            formatResult: format,
            });

        $(this).find(".player-select-type").attr('value', '0');
    });
});
