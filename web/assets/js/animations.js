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
        var saveStatus = $target.data("savestatus");

        if (saveStatus) {
            createCookie("collapsible:" + $this.data("close"), true, 30);
        }

        $target.css("height", 0);
    });

    $(".collapsible-entity").each(function() {
        var $this = $(this);
        var entityID = $this.attr('id');

        if ($this.data("savestatus") && readCookie("collapsible:#" + entityID)) {
            $this.hide();
        }
    });

    var $collapsibleOpen = $(".collapsible-entity--open");
        $collapsibleOpen.css("height", $collapsibleOpen[0].scrollHeight);
});