var crel = require('crel');
var tinysort = require('tinysort');

var $servers = $('.js-server');

/**
 * The JS object for servers
 *
 * @constructor
 * @param object {Node}
 */
function Server(object) {
    this.$object = $(object);
    this.url = baseURLNoHost + '/servers/status/' + this.$object.data('id');
    this.token = this.$object.data('token');

    // Alias for this object for callback functions
    var server = this;

    // Set up the 'force refresh' button
    this.$object.find('.js-refresh').click(function (event) {
        event.preventDefault();

        var $this = $(this);

        $this.addClass('fa-spin');
        server.updateServer(true, function () {
            $this.removeClass('fa-spin');
        });
    });

    this.updateCard = function (data) {
        this.$object.find('.js-server__last-update').html(data['last_update']);
        this.$object.find('.js-server__player-count').html(data['player_count']);
        this.$object.attr('data-player-count', data['player_count']);

        // Statuses
        var status = (data['player_count'] > 0) ? 'active' : data['status'];
        this.$object.find('.js-server__status').attr('data-status', status);
    };

    /**
     * Enable the spinner element for the server card
     * @returns {Server}
     */
    this.startSpinners = function () {
        this.$object
            .find('.js-dimmable')
            .find('.dimmer, .spinner')
            .fadeIn('fast')
        ;

        return this;
    };

    /**
     * Hide the spinner element
     * @returns {Server}
     */
    this.stopSpinners = function () {
        this.$object
            .find('.js-dimmable')
            .find('.dimmer, .spinner')
            .fadeOut('fast')
        ;

        return this;
    };

    /**
     * Update the server card with information we're pulling via AJAX
     */
    this.updateServer = function (forceUpdate, onDoneCallback) {
        this.startSpinners();

        var urlCall = this.url;
        forceUpdate = (typeof forceUpdate === 'undefined') ? false : forceUpdate;

        if (forceUpdate) {
            urlCall += '?forced=1';
        }

        jQuery
            .post(urlCall, {
                token: this.token
            })
            .done(function(data) {
                server.updateCard(data);
                server.stopSpinners();

                (typeof onDoneCallback === 'function') && onDoneCallback();
            })
        ;
    }
}

function initialize() {
    // Hide any dimmers that might have been left
    $('.dimmer, .spinner').fadeOut('fast');

    var dimmer  = crel('div', { 'class': 'dimmer' });
    var spinner = crel('div', { 'class': 'spinner' }, 'Loading...');

    $('.js-dimmable')
        .prepend(dimmer)
        .prepend(spinner)
    ;

    loadAllServers();
}

function loadAllServers() {
    $servers.each(function () {
        new Server(this).updateServer();
    });
}

function sortServers() {
    var servers = $('.js-server');

    tinysort(servers, { attr: 'data-player-count', order: 'ASC' });
}

module.exports = function () {
    // If this page has no .js-server components, then no need to proceed
    if (!$servers.length) {
        return;
    }

    initialize();
    sortServers();
};
