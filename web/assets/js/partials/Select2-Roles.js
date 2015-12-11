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
    if (!$.fn.select2) {
        return;
    }

    $(".js-select__role").select2({
        templateResult: formatRole,
        templateSelection: formatRole,
        width: '100%'
    });
});
