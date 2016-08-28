;(function ( $, window, document, undefined ) {
    var pluginName = "TabSelector",
        defaults = {
            itemSelector: '',
            activeClass: ''
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
            var $el   = $(this.element);
            var $tabs = $el.find(this.options.itemSelector);

            $tabs.click({ options: this.options }, function (e) {
                var $active = $el.find("." + e.data.options.activeClass);
                $active.removeClass(e.data.options.activeClass);

                var $this = $(this);
                $this.addClass(e.data.options.activeClass);
            });

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
