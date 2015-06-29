function formatCountry(country) {
    if (!country.id) {
        return country.text;
    }

    var $country = $(
        '<div class="c-flag c-flag--' + country.element.value.toLowerCase() + '"></div> <span>' + country.text + '</span>'
    );
    return $country;
}

function formatRole(role) {
    if (!role.id || role.element === undefined) {
        return role.text;
    }

    var icon = role.element.getAttribute('data-icon');

    if (icon === null) {
        return role.text;
    }

    return $(
        '<i class="fa ' + icon + '"></i> <span>' + role.text + '</span>'
    );
}

$(document).ready(function() {
    $("#form_country").select2({
        templateResult: formatCountry,
        templateSelection: formatCountry,
    });

    $("#form_timezone").select2();

    $(".role-select").select2({
        templateResult: formatRole,
        templateSelection: formatRole,
        width: '100%'
    });
});
