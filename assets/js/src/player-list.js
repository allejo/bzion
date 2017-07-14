var _ = require('lodash');

/** @type {Node} */
var crel = require('crel');
var tinysort = require('tinysort');

module.exports = function () {
    var playerNodeList = $('.js-player');
    var playerListCanvas = $('#player-list-canvas');

    // We don't have a player canvas on this page, so don't bother continuing
    if (!playerNodeList.length || !playerListCanvas.length) {
        return;
    }

    $('.js-group-by').click(function () {
        var canvas = crel('div');
        var groupByAttribute = $(this).data('group-by');
        var grouped = _.groupBy(playerNodeList, function (element) {
            return element.getAttribute('data-' + groupByAttribute);
        });

        _.forEach(grouped, function (value, key) {
            var sectionContainer = crel('section',
                { 'data-sortable': (key || ' ') },
                crel('h2', { 'class': 'mb2' }, (key || crel('em', 'Teamless')))
            );
            var playerContainer = crel('div', { 'class': 'row js-player-list' });

            _.forEach(value, function(playerWidget) {
                playerContainer.appendChild(playerWidget);
            });

            sectionContainer.appendChild(playerContainer);
            canvas.appendChild(sectionContainer);
        });

        playerListCanvas.html(canvas.innerHTML);

        tinysort('div#player-list-canvas>section', { attr: 'data-sortable' });
    });
};
