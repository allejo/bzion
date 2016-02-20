$(document).ready(function() {
    $( ".score" ).on("click", function() {
        var currentMatch = $("#match-" + $(this).find(".more_details").attr("rel"));
        currentMatch.slideToggle();

        $(".match_details").not(currentMatch).slideUp();
    });

    if (typeof(Ladda) === "undefined") {
        return;
    }

    var $button = $("#confirm_form_confirm");
    if ($button[0] === undefined) {
        return;
    }
    var $form = $("form");

    $button.attr('data-style', 'expand-left');
    var l = Ladda.create($button[0]);

    $button.click(function(e) {
        e.preventDefault();

        var formData = new FormData( $form[0] );

        // Simulate clicking the "confirm" button
        formData.append('confirm_form[confirm]', 'confirm');

        var count;

        // Perform a streamed HTTP request
        httpRequest = new XMLHttpRequest();
        httpRequest.onreadystatechange = function() {
            var text = httpRequest.responseText.split("\n");

            if (count === undefined) {
                lines = parseInt(text[0]);
                if (!isNaN(lines)) {
                    count = lines;
                }
            }

            if (text[1] !== undefined) {
                // Count the number of "m"s
                var done = text[1].length;
                l.setProgress(done / count);
            }

            if (httpRequest.readyState === XMLHttpRequest.DONE) {
                l.stop();

                if (text.length !== 5 || text[3] !== "Calculation successful") {
                    notify("An error occurred.", "danger");
                } else {
                    window.location.replace(baseURLNoHost + "/matches");
                }
            }
        };

        l.start();
        httpRequest.open($form.attr('method'), "", true);
        httpRequest.send(formData);
    });
});
