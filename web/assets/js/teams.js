var pieChartCanvas = $("#teamMatchStats")[0].getContext("2d");
var lineChartCanvas = $("#teamActivityStats")[0].getContext("2d");

if (typeof teamWins !== 'undefined' && typeof teamLosses !== 'undefined' && typeof teamDraws !== 'undefined') {
    var pieData = [
        {
            value: teamWins,
            color: $('#winsColor').css('background-color'),
            label: "Wins"
        },
        {
            value: teamLosses,
            color: $('#lossColor').css('background-color'),
            label: "Losses"
        },
        {
            value: teamDraws,
            color: $('#drawColor').css('background-color'),
            label: "Draws"
        }
    ];

    var pieOptions = {
        segmentStrokeWidth: 1,
        animation: false
    };

    new Chart(pieChartCanvas).Pie(pieData, pieOptions);
}
else {
    console.log("The follow variables were not defined on your page: teamWins, teamLosses, and teamDraws");
}

if (typeof matchDates !== 'undefined' && typeof matchCounts !== 'undefined' && typeof winCounts !== 'undefined') {
    // TODO: Better colour management
    var lineData = {
        labels: matchDates,
        datasets: [
            {
                data: matchCounts,
                label: "Matches",
                fillColor: "rgba(70,130,205,0.2)",
                strokeColor: "rgba(70,130,205,1)",
                pointColor: "rgba(70,130,205,1)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(70,130,205,1)"
            },
            {
                data: winCounts,
                label: "Wins",
                fillColor: "rgba(3,167,71,0.3)",
                strokeColor: "rgba(3,167,71,1)",
                pointColor: "rgba(3,167,71,1)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(3,167,71,1)"
            }
        ]
    };

    var lineOptions = {
        animation: false,
        showTooltips: false,
        pointDot : false,
        scaleShowVerticalLines: false,
        scaleFontSize: 10
    };

    var chart = new Chart(lineChartCanvas).Line(lineData, lineOptions);
} else {
    console.log("The following variables were not defined on your page: matchDates, matchCounts, winCounts");
}
