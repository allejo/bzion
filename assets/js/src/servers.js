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
    this.url = baseURLNoHost + '/servers/' + this.$object.data('id');

    this.startSpinners = function () {
        this.$object
            .find('.js-dimmable')
            .find('.dimmer, .spinner')
            .fadeIn('fast')
        ;

        return this;
    };

    this.stopSpinners = function () {
        this.$object
            .find('.js-dimmable')
            .find('.dimmer, .spinner')
            .fadeOut('fast')
        ;

        return this;
    };

    this.updateServer = function () {
        var server = this;

        this.startSpinners();
        this.$object
            .load(this.url, function () {
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
    $servers.each(function (event) {
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
