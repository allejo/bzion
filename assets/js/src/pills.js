module.exports = function () {
    $('.c-pill-group__pill').click(function (e) {
        e.preventDefault();

        var $this = $(this);
        var $parent = $this.parent();

        $parent.find('.active').removeClass('active');
        $this.addClass('active');
    });
};
