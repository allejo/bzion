module.exports = function() {
    var $tab = $('[role="tab"]');

    $tab.click(function() {
        var $this = $(this);

        $tab.attr('aria-selected', 'false'); // deselect all the tabs
        $this.attr('aria-selected', 'true');  // select this tab

        var tabPanelID = $this.attr('aria-controls'); // find out what tab panel this tab controls
        var $tabpan = $('#' + tabPanelID);

        $('[role="tabpanel"]').attr('aria-hidden', 'true'); // hide all the panels

        $tabpan.attr('aria-hidden', 'false');  // show our panel

        $this.trigger('shown.bzion.tab', $tabpan);
    });

    $tab.keydown(function(e) {
        if (e.which === 13) {
            $(this).click();
        }
    })
};
