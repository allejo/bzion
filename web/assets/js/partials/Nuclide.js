;(function ( $, window, document, undefined ) {
    var pluginName = "nuclide",
        defaults = {
            filter: '*',
            postFilter: function() {},
            itemSelector: ''
        };

    // The actual plugin constructor
    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend( {}, defaults, options);

        this._defaults = defaults;
        this._name = pluginName;

        this.init();
    }

    Plugin.prototype = {
        init: function() {
            var $el      = $(this.element);
            var $filters = $el.find('*[data-filter]');
            this.target  = $el.data('target');

            $filters.each(function () {
                $(this).click(function () {
                    var $this = $(this);
                    var filter = $this.data('filter');

                    $this.parent().nuclide('refresh', filter);
                });
            });

            return this;
        },
        checkFilter: function ($target, options, filter) {
            var $elements = $target.find(options.itemSelector);

            $elements.each(function() {
                var $this = $(this);

                if ($this.data(options.filter) !== filter && filter !== '*') {
                    $this.hide();
                } else {
                    $this.show();
                }
            });

            return this;
        },
        refresh: function (param) {
            this.checkFilter($(this.target), this.options, param);
            this.options.postFilter();

            return this;
        }
    };

    $.fn[pluginName] = function (options, params) {
        return this.each(function () {
            var plugin_name = "plugin_" + pluginName;

            if (!$.data(this, plugin_name)) {
                $.data(this, plugin_name, new Plugin(this, options));
            } else {
                var plugin = $.data(this);
                plugin[plugin_name][options](params);
            }
        });
    };
})(jQuery, window, document);