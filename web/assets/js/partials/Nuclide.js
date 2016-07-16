;(function ( $, window, document, undefined ) {
    var pluginName = "nuclide",
        defaults = {
            filter: function() {},
            postFilter: function() {},
            itemSelector: '',
            target: ''
        };

    // The actual plugin constructor
    function Plugin(element, target, options) {
        this.element = element;
        this.$target = $(target);
        this.options = $.extend( {}, defaults, options) ;

        this._defaults = defaults;
        this._name = pluginName;

        this.init();
    }

    Plugin.prototype = {
        init: function() {
            return this;
        },
        checkFilter: function (el, $target, options) {
            var $elements = $target.find(options.itemSelector);

            $elements.each(function() {
                var $this = $(this);

                if (!options.filter($this)) {
                    $this.hide();
                } else {
                    $this.show();
                }
            });

            return this;
        },
        refresh: function () {
            this.checkFilter(this.element, this.$target, this.options);
            this.options.postFilter();

            return this;
        }
    };

    $.fn[pluginName] = function (target, options) {
        return this.each(function () {
            var plugin_name = "plugin_" + pluginName;

            if (!$.data(this, plugin_name)) {
                $.data(this, plugin_name,
                    new Plugin(this, target, options));
            } else {
                var plugin = $.data(this);
                plugin[plugin_name][target]();
            }
        });
    };
})(jQuery, window, document);