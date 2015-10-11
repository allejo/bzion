function formatCountry(country) {
    if (!country.id) {
        return country.text;
    }

    return $(
        '<div class="c-flag c-flag--' + country.element.value.toLowerCase() + '"></div> <span>' + country.text + '</span>'
    );
}

$(document).ready(function() {
    $(".js-select__country").select2({
        templateResult: formatCountry,
        templateSelection: formatCountry
    });
});
