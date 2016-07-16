$(function () {
    var $nuclideFilters = $('*[data-role="nuclide"]');

    $nuclideFilters.each(function () {
        var $this    = $(this);
        var $filters = $this.find('*[data-filter]');
        var target   = $this.data('target');

        $filters.each(function () {
            var $this  = $(this);
            var filter = $this.data('filter');

            $this.nuclide(target, {
                filter: function ($el) {
                    return ($el.data('matchtype') === filter || filter === '*');
                },
                postFilter: function () {
                    var $elements = $('.c-match-history__matches');

                    $elements.each(function () {
                        var $this = $(this);
                        $this.parent().show();

                        if ($this.height() === 0) {
                            $this.parent().hide();
                        }
                    });
                },
                itemSelector: '.c-match-history__match'
            });

            $this.click(function () {
                $this.nuclide('refresh');
            });
        });
    });
});
