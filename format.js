// Popover.
require(['jquery'], function($) {
    require(['theme_boost/popover'], function() {
        var counter;
        $('[data-toggle="etask-popover"]').popover({
            html: true,
            container: 'body',
            placement: 'bottom',
            trigger: 'manual',
            sanitize: false,
            template: '<div class="popover etask-popover" role="tooltip"><div class="arrow"></div><div class="popover-body"></div></div>',
        }).on('click mouseenter', function () {
            var _this = this; // Represents [data-toggle="etask-popover"].
            clearTimeout(counter); // Clear the counter.
            $('[data-toggle="etask-popover"]').not(_this).popover('hide'); // Close all other popovers.

            // Start new timeout to show popover.
            counter = setTimeout(function() {
                if($(_this).is(':hover')) {
                    $(_this).popover('show');
                }

                $('.popover').on('mouseleave', function () {
                    $('[data-toggle="etask-popover"]').popover('hide');
                });
            }, 500);
        }).on('mouseleave', function () {
            var _this = this;
            setTimeout(function () {
                if (!$('.popover:hover').length) {
                    if(!$(_this).is(':hover')) { // Change $(this) to $(_this).
                        $(_this).popover('hide');
                    }
                }
            }, 100);
        });
    });
});
