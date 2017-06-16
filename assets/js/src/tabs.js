module.exports = function() {
    var $tabRole = $('li[role="tab"]');

    $tabRole.click(function() {
        var $this = $(this);

        $tabRole.attr('aria-selected', 'false'); // deselect all the tabs
        $this.attr('aria-selected', 'true');  // select this tab

        var tabpanid = $this.attr('aria-controls'); // find out what tab panel this tab controls
        var tabpan = $('#' + tabpanid);

        $('div[role="tabpanel"]').attr('aria-hidden', 'true'); // hide all the panels

        tabpan.attr('aria-hidden','false');  // show our panel
    });

    $tabRole.keydown(function(e) {
        if (e.which === 13) {
            $(this).click();
        }
    })
};
