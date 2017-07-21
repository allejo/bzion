var _ = require('lodash');

/** @type {Node} */
var crel = require('crel');
var tinysort = require('tinysort');

var utils = require('./utilities');

/**
 * Sort the players in each .js-player-list by the specified options
 *
 * @param sortBy  {String} The `data-` attribute used to sort by (exclude the 'data-' part)
 * @param orderBy {String} Either 'asc' or 'desc'
 */
function sortPlayers(sortBy, orderBy) {
    $('.js-player-list').each(function () {
        var $this = $(this);

        tinysort($this.find('.js-player'), { attr: 'data-' + sortBy, order: orderBy });
    });
}

/**
 * Update a query parameter from the browser's URL without a refresh
 *
 * @param getParam      {String} The query parameter
 * @param getParamValue {String} The value the query parameter will take
 */
function updateURL(getParam, getParamValue) {
    history.pushState(null, null, utils.setURLParameter(getParam, getParamValue));
}

module.exports = function () {
    var playerNodeList = $('.js-player');
    var playerListCanvas = $('#player-list-canvas');

    // We don't have a player canvas on this page, so don't bother continuing
    if (!playerNodeList.length || !playerListCanvas.length) {
        return;
    }

    // Grouping functionality

    $('.js-group-by').click(function (event) {
        event.preventDefault();

        var canvas = crel('div');
        var groupByAttribute = $(this).data('group-by');
        var grouped = _.groupBy(playerNodeList, function (element) {
            var sortValue = element.getAttribute('data-' + groupByAttribute);

            if (groupByAttribute === 'activity') {
                return (sortValue > 0) ? 'Active' : 'Inactive';
            }

            return sortValue;
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
        updateURL('groupBy', groupByAttribute);
    });

    // Sorting functionality

    var sortBy = 'callsign';
    var orderBy = 'ASC';

    $('.js-sort-by').click(function (event) {
        event.preventDefault();

        sortBy = $(this).data('sort-by');

        sortPlayers(sortBy, orderBy);
        updateURL('sortBy', sortBy);
    });

    $('.js-order-by').click(function (event) {
        event.preventDefault();

        orderBy = $(this).data('order-by');

        sortPlayers(sortBy, orderBy);
        updateURL('sortOrder', orderBy.toUpperCase());
    });
};
