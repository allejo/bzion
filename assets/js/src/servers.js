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

    this.updateCard = function (data) {
        this.$object.find('.js-server__last-update').html(data['last_update']);
        this.$object.find('.js-server__player-count').html(data['player_count']);
        this.$object.data('player-count', data['player_count']);
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
    this.updateServer = function () {
        var server = this;

        this.startSpinners();

        jQuery
            .post(this.url, {
                token: this.token
            })
            .done(function(data) {
                server.updateCard(data);
                server.stopSpinners();
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
