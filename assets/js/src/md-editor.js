module.exports = function() {
    if (window.markdownit === undefined) {
        return;
    }

    $('#mde__toolbar__preview').click(function() {
        var markdown = $('#form_content').val();
        var result = md.render(markdown);

        $('#mde__preview').html(result);
    });
};
