module.exports = function () {
    var showTableButtons = $('[data-role="show-sr-table"]');

    showTableButtons.click(function () {
        var $this = $(this);
        var elemID = $this.data('target');
        var $target = $('#' + elemID);

        $target.toggleClass('disable-sr-only');
    });
};
