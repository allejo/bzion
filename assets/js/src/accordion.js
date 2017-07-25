function toggleBooleanAttribute ($elem, attribute) {
    var cValue = $elem.attr(attribute);
    var value = (cValue === 'true') ? 'false' : 'true';

    $elem.attr(attribute, value);
}

module.exports = function () {
    var $accordionHeadings = $('[data-role="accordion-heading"]');
    var $accordionBodies = $('[data-role="accordion-body"]');

    $accordionBodies.each(function () {
        var $this = $(this);
        var $heading = $('#' + $this.attr('aria-labelledby'));

        if ($heading.is(':visible')) {
            $this.attr('aria-expanded', 'false');
        }
    });

    $accordionHeadings.click(function () {
        var $this = $(this);

        var accordionPanelID = $this.attr('aria-controls');
        var $accordionPanel = $('#' + accordionPanelID);

        toggleBooleanAttribute($accordionPanel, 'aria-expanded');
    });
};
