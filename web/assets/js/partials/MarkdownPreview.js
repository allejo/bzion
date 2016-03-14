function toggleToolbar() {
    $(".js-mdEditor").toggleClass('active');
    $(".js-mdPreview").toggleClass('active');

    $("#c-md-editor__canvas").toggleClass("c-md-editor__canvas--editor c-md-editor__canvas--preview");
}

if (window.markdownit !== undefined) {
    var md = window.markdownit();

    $(function () {
        $(".js-mdEditor").click(function () {
            toggleToolbar();
        });

        $(".js-mdPreview").click(function () {
            toggleToolbar();

            var markdown = $("#form_content").val();
            var result = md.render(markdown);

            $("#c-md-editor__preview").html(result);
        });
    });
}
