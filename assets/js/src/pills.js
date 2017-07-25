module.exports = function () {
    $('.c-pill-group__pill').click(function (event) {
        var $this = $(this);

        if ($this.hasClass('js-allow-propagation')) {
            return;
        }

        event.preventDefault();

        var $parent = $this.parent();

        $parent.find('.active').removeClass('active');
        $this.addClass('active');
    });
};
