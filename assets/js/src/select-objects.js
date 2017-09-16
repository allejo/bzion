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

module.exports = function () {
    $(".js-select__country").select2({
        templateResult: formatCountry,
        templateSelection: formatCountry
    });
    $(".js-select__permission").select2();
    $('.js-select__role').select2({
        templateResult: formatRole,
        templateSelection: formatRole,
        width: '100%'
    });
    $('.js-select__timezone').select2();
};
