var Chartist = require('chartist');

function buildPieCharts() {
    var $pies = $('div[data-graph="pie"]');

    $pies.each(function () {
        var $this = $(this);
        var customOpts = $this.data('chart-options');
        var options = {};

        if (typeof customOpts !== 'undefined') {
            options = $.extend(options, customOpts);
        }

        new Chartist.Pie(this, $this.data('chart'), options);
    });
}

function buildLineCharts() {
    var $lines = $('div[data-graph="line"]');

    $lines.each(function() {
        var $this = $(this);
        var customOpts = $this.data('chart-options');
        var options = {
            axisY: {
                onlyInteger: true
            },
            showPoint: false
        };

        if (typeof customOpts !== 'undefined') {
            options = $.extend(options, customOpts);
        }

        new Chartist.Line(this, $this.data('chart'), options);
    });
}

module.exports = function() {
    // Due to hidden DOM objects not having a height and width, if the chart's parent is `display: none`, the chart won't
    // display. In this case, our charts may reside in tab panels, so we have to trigger a chart update when the tab is
    // clicked.
    //
    // see: https://github.com/gionkunz/chartist-js/issues/119

    $('[role="tab"]').on('shown.bzion.tab', function (event, tab) {
        var $tab = $(tab);

        $tab.find('.ct-chart').each(function (i, e) {
            e.__chartist__.update();
        });
    });

    // Start building all of the generic charts that we support
    buildPieCharts();
    buildLineCharts();
};
