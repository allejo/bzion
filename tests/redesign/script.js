$(document).ready(function() {
    $(".page").hide();

    var first = $(".link:first-child");
    first.addClass("active");
    $("." + first.data("color")).show();

    $(".link").on("click", function() {
        $(this).addClass("active");
        $(".link").not($(this)).removeClass("active");
        
        var page = $(".pages").find("." + $(this).data("color"));
        page.show();
        $(".page").not(page).hide();
    });
});