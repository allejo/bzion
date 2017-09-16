module.exports = function() {
    $(".select2-compatible").each(function() {
        var input = $(this);

        // Hide the text boxes for manual input, as they will be replaced by the
        // select2 list
        input.parent().children('div').hide();

        var select = $('<select />')
            .attr('id', input.attr('id') + '-select')
            .attr('disabled', input.attr('disabled'))
            .attr('multiple', input.attr('data-multiple'))
            .css('width', '100%')
            .insertAfter(input);

        var label = $('<label />')
            .text(input.data('label'))
            .attr('for', select.attr('id'))
            .click(function () {
                // Since select2 doesn't seem to support labels, expand the list
                // when the user clicks the label

                select.select2("open");
            });

        if (input.attr('data-required')) {
            label.addClass('required');
        }

        label.insertAfter(input);

        var types = input.parent().attr('data-types');
        var multipleTypes = types.indexOf(",") > -1;

        var format = function (item) {
            if (item.name === undefined) {
                return item.text;
            }

            var escaped = $('<div/>').text(item.name).html();

            if (multipleTypes) {
                var icon = 'circle-o';

                switch (item.type) {
                    case 'Player':
                        icon = 'user';
                        break;
                    case 'Team':
                        icon = 'users';
                        break;
                }

                escaped = '<i class="fa fa-' + icon + '"></i> ' + escaped;
            }

            if (item.outdated) {
                escaped = '<small style="float: right;">(outdated)</small> ' + escaped;
            }

            return $('<span>' + escaped + '</span>');
        };

        var data = JSON.parse(input.val()).data;
        var value = [];
        var items = [];

        for (var i in data) {
            var id = data[i].type + '#' + data[i].id;
            value.push(id);
            var item = {
                id: id,
                dbid: data[i].id,
                name: data[i].name,
                type: data[i].type
            };
            items.push(item);
        }

        select.select2({
            data: items,
            ajax: {
                url: baseURLNoHost + "/search",
                dataType: 'json',
                data: function (params) {
                    var data = {
                        format: 'json',
                        types: types,
                        startsWith: params.term,
                        exclude: input.data('exclude')
                    };

                    return data;
                },
                processResults: function (data, page) {
                    var results = [];
                    var i = 0;

                    for (var key in data.results) {
                        var item = data.results[key];

                        // Use a unique ID for objects so that select2 can identify
                        // individual results
                        item.dbid = item.id;
                        item.id = item.type + "#" + item.id;
                        item.text = item.name;

                        results.push(item);
                    }

                    return {
                        results: results
                    };
                },
                cache: false
            },
            minimumInputLength: 1,
            templateSelection: format,
            templateResult: format,
        });

        select.val(items.map(function (item) {
            return item.id;
        })).trigger("change");

        input.parents('form').submit(function (e) {
            var selected = select.val();
            var value = [];

            if (!(selected instanceof Array)) {
                // Make sure we are manipulating an array, because that's what the
                // server will accept

                selected = [selected];
            }

            for (var i in selected) {
                if (selected[i] === null) {
                    continue;
                }

                var id = selected[i].split('#');

                value.push({
                    id: id[1],
                    type: id[0]
                });
            }


            value = JSON.stringify({
                data: value,

                // The modified key lets PHP know that the ID field was handled by
                // javascript and that the content of the text inputs should be ignored
                modified: true
            });

            input.val(value);
        });
    });
};
