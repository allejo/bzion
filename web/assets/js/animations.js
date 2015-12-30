// This file contains code to toggle CSS transition classes based on triggers/targets

$(function () {
    $(".collapsible-trigger").click(function () {
        var $this = $(this);
        var $target = $($this.data("toggle"));

        $target.slideToggle();
    });
});