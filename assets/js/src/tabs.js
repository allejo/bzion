/**
 * Check whether or not the tabs are visible
 *
 * @returns {boolean}
 */
function areTabsVisible() {
    return ($('[role="tablist"]:visible').length >= 1);
}

/**
 * Toggle the tabs' ARIA visibility based on whether or the tabs are visible via CSS
 */
function toggleTabsAriaVisible() {
    $('[role="tablist"]').attr('aria-hidden', (areTabsVisible()) ? 'false' : 'true');
}

/**
 * Toggle the tab panels' ARIA visibility based on whether or not tabs are visible
 */
function toggleAriaTabPanels() {
    var $activeTab = $('[aria-selected="true"]');

    $('[role="tabpanel"]').each(function () {
        var $this = $(this);

        // Don't hide the tab panel for the currently selected tab
        if ($this.attr('id') === $activeTab.attr('aria-controls')) {
            return;
        }

        $this.attr('aria-hidden', (areTabsVisible()) ? 'true' : 'false');
    });
}

// To support responsive behavior, listen for the window resize event and change the tabs' visibility
window.addEventListener('resize', function() {
    toggleTabsAriaVisible();
    toggleAriaTabPanels();
});

// Our main tabbing functionality. This should all be based on ARIA attributes
module.exports = function() {
    var $tab = $('[role="tab"]');

    toggleTabsAriaVisible();
    toggleAriaTabPanels();

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
