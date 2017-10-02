var md = require('8fold-marked');

module.exports = function() {
    $('#mde__toolbar__preview').click(function() {
        var $this = $(this);
        var $parent = $('#mde');
        var formID = $this.data('textarea');
        var markdown = $('#' + formID).val();

        // Rendering options
        var sanitizeContent = $parent.data('sanitize');
        md.setOptions({
            sanitize: (typeof sanitizeContent === 'undefined') || sanitizeContent
        });

        // Set our rendered HTML into the preview area
        var html = md(markdown);
        $('#mde__preview').html(html);
    });
};
