module.exports = function () {
    $('[data-role="site-theme-selector"]').change(function () {
        var $this = $(this);
        var $html = $('html');
        var theme = $this.val();

        $html.removeClass(function (index, className) {
            return (className.match (/(^|\s)t-\S+/g) || []).join(' ');
        });
        $html.addClass('t-' + theme);
    });
};
