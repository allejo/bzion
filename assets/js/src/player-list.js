var _ = require('lodash');

/** @type {Node} */
var crel = require('crel');
var tinysort = require('tinysort');

var utils = require('./utilities');

// State variables for sorting
var sortBy  = utils.getURLParameter('sortBy') || 'callsign';
var orderBy = utils.getURLParameter('sortOrder') || 'ASC';

/**
 * Sort the players in each .js-player-list by the options defined in `sortBy` and `orderBy`
 */
function sortPlayers() {
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
    if (!getParamValue) {
        history.pushState(null, null, utils.removeURLParameter(getParam));
        return;
    }

    history.pushState(null, null, utils.setURLParameter(getParam, getParamValue));
}

module.exports = function () {
    var $playerNodeList = $('.js-player');
    var $playerListCanvas = $('#player-list-canvas');
    var $groupByClearBtn = $('.js-clear-group-by');

    // We don't have a player canvas on this page, so don't bother continuing
    if (!$playerNodeList.length || !$playerListCanvas.length) {
        return;
    }

    // Grouping functionality

    $groupByClearBtn.click(function (event) {
        event.preventDefault();

        var $this = $(this);
        var playerContainer = crel('div', { 'class': 'row js-player-list' });

        $playerNodeList.each(function () {
            playerContainer.append(this);
        });

        $playerListCanvas.html(playerContainer);
        updateURL('groupBy');
        sortPlayers();

        // We're no longer grouping by anything, so hide the 'Clear' button
        $this.addClass('u-hide');
    });

    $('.js-group-by').click(function (event) {
        event.preventDefault();

        var canvas = crel('div');
        var groupByAttribute = $(this).data('group-by');
        var grouped = _.groupBy($playerNodeList, function (element) {
            var sortValue = element.getAttribute('data-' + groupByAttribute);

            if (groupByAttribute === 'activity') {
                return (sortValue > 0) ? 'Active' : 'Inactive';
            }

            return sortValue;
        });

        _.forEach(grouped, function (value, key) {
            var sectionContainer = crel(
                'section', { 'data-sortable': (key || ' ') },
                    crel('h2', { 'class': 'mb2' },
                        (key || crel('em', 'Teamless'))
                    )
            );
            var playerContainer = crel('div', { 'class': 'row js-player-list' });

            _.forEach(value, function(playerWidget) {
                playerContainer.appendChild(playerWidget);
            });

            sectionContainer.appendChild(playerContainer);
            canvas.appendChild(sectionContainer);
        });

        $playerListCanvas.html(canvas.innerHTML);

        tinysort('div#player-list-canvas>section', { attr: 'data-sortable' });
        updateURL('groupBy', groupByAttribute);

        // We're grouping, so add the 'Clear' button
        $groupByClearBtn.removeClass('u-hide');
    });

    // Sorting functionality

    $('.js-sort-by').click(function (event) {
        event.preventDefault();

        sortBy = $(this).data('sort-by');

        sortPlayers();
        updateURL('sortBy', sortBy);
    });

    $('.js-order-by').click(function (event) {
        event.preventDefault();

        orderBy = $(this).data('order-by');

        sortPlayers();
        updateURL('sortOrder', orderBy.toUpperCase());
    });
};
