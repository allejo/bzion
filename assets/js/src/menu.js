module.exports = function() {
    $('[data-option="clickable-label"]').keypress(function (event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);

        if (keycode === 13) {
            var checkboxID = $(this).attr('for');
            var $checkbox = $('#' + checkboxID);

            $checkbox.prop('checked', !$checkbox.prop('checked'));
        }

        event.stopPropagation();
    });
};
