var md = require('markdown').markdown;

module.exports = function() {
    $('#mde__toolbar__preview').click(function() {
        var formID = $(this).data('textarea');
        var markdown = $('#' + formID).val();
        var result = md.toHTML(markdown);

        $('#mde__preview').html(result);
    });
};
