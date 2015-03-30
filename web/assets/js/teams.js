var pieChartCanvas = $("#teamMatchStats")[0].getContext("2d");

if (typeof teamWins !== 'undefined' && typeof teamLosses !== 'undefined' && typeof teamDraws !== 'undefined')
{
    var pieData = [
        {
            value: teamWins,
            color: "#EBEBEB",
            label: "Wins"
        },
        {
            value: teamLosses,
            color: "#B6D1D8",
            label: "Losses"
        },
        {
            value: teamDraws,
            color: "#8FDC00",
            label: "Draws"
        }
    ];

    var pieOptions = {
        segmentStrokeWidth : 1,
        animation : false
    };

    new Chart(pieChartCanvas).Pie(pieData, pieOptions);
}
else
{
    console.log("The follow variables were not defined on your page: teamWins, teamLosses, and teamDraws");
}
