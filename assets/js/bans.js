function format(item) { return item.username; }

$(document).ready(function() {
    var players = $(".player-select");

    players.attr('placeholder', 'Enter player...')
        .css('width', '400px')
        .select2({
            ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
                url: baseURLNoHost + "/players",
                dataType: 'json',
                data: function (term, page) {
                    return {
                        format: 'json',
                        exceptMe: null,
                        startsWith: term, // search term
                    };
                },
                results: function (data, page) {
                    return {results: data.players};
                },
            },
            formatSelection: format,
            formatResult: format,
        });

    // Make sure that PHP knows we are sending player IDs, not usernames
    $("#form_player_ListUsernames").attr('value', '0');

    if (players.attr('data-value') !== undefined) {
        players.select2("data", JSON.parse(players.attr('data-value')));
    }
});
