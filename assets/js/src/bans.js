const $ = require('jquery');
require('jquery-depends-on');

module.exports = function() {
    var $expirationDate = $('#ban_expiration_date');

    if ($expirationDate.length) {
        $expirationDate.dependsOn({
            '#form_is_permanent': {
                checked: false
            }
        });
    }
};
