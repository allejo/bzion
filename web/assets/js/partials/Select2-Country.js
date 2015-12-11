function formatCountry(country) {
    if (!country.id || country.element === undefined) {
        return country.text;
    }

    var icon = country.element.getAttribute('data-iso');

    if (icon === null) {
        return role.text;
    }

    return $(
        '<div class="c-flag c-flag--' + icon.toLowerCase() + '"></div> <span>' + country.text + '</span>'
    );
}

$(document).ready(function() {
    if (!$.fn.select2) {
        return;
    }

    $(".js-select__country").select2({
        templateResult: formatCountry,
        templateSelection: formatCountry
    });
});
