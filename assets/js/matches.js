$(document).ready(function() {
    $( ".more_details" ).click(function() {
        var currentMatch = $("#match-" + $(this).attr("rel"));
        currentMatch.slideToggle();

        $(".match_details").not(currentMatch).slideUp();
    });
});