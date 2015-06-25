function formatCountry(country) {
    if (!country.id) {
        return country.text;
    }

    var $country = $(
        '<div class="c-flag c-flag--' + country.element.value.toLowerCase() + '"></div> <span>' + country.text + '</span>'
    );
    return $country;
};

$(document).ready(function() {
    $("#form_country").select2({
        templateResult: formatCountry,
        templateSelection: formatCountry,
    });

    $("#form_timezone").select2();
});
