// This file contains code to toggle CSS transition classes based on triggers/targets

$(function () {
    $(".collapsible-trigger").click(function () {
        var $this = $(this);
        var $target = $($this.data("toggle"));

        $target.css("height", ($target.height() ? 0 : $target[0].scrollHeight));
        $target.toggleClass("open");
    });
});