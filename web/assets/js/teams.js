var pieChartCanvas = $("#teamMatchStats")[0].getContext("2d");

if (typeof teamWins !== 'undefined' && typeof teamLosses !== 'undefined' && typeof teamDraws !== 'undefined')
{
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
        segmentStrokeWidth : 1,
        animation : false
    };

    new Chart(pieChartCanvas).Pie(pieData, pieOptions);
}
else
{
    console.log("The follow variables were not defined on your page: teamWins, teamLosses, and teamDraws");
}
