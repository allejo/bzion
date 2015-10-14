function format(item) { return item.username; }

$(document).ready(function() {
    $( ".score" ).on("click", function() {
        var currentMatch = $("#match-" + $(this).find(".more_details").attr("rel"));
        currentMatch.slideToggle();

        $(".match_details").not(currentMatch).slideUp();
    });
});
