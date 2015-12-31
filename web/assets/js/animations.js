// This file contains code to toggle CSS transition classes based on triggers/targets

$(function () {
    $(".collapsible-trigger").click(function () {
        var $this = $(this);
        var $target = $($this.data("toggle"));

        $target.css("height", ($target.height() ? 0 : $target[0].scrollHeight));
    });

    $(".collapsible-open").click(function () {
        var $this = $(this);
        var $target = $($this.data("open"));

        $target.css("height", $target[0].scrollHeight);
    });

    $(".collapsible-close").click(function () {
        var $this = $(this);
        var $target = $($this.data("close"));

        $target.css("height", 0);
    });
});