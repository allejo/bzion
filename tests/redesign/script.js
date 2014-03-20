$(document).ready(function() {
    var first = $(".link:first-child");
    first.addClass("active");
    $(".page").hide();
    $("." + first.data("color")).show();

    $(".link").on("click", function() {
        $(this).addClass("active");
        $(".link").not($(this)).removeClass("active");
        var color = $(this).data("color");
        var page = $(".pages").find("." + color);
        page.show();
        $(".page").not(page).hide();
    });
});