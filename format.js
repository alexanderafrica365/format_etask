// Popover.
require(['jquery', 'theme_boost/tether'], function($, Tether) {
    window.jQuery = $;
    window.Tether = Tether;
    require(['theme_boost/popover'], function() {
        $('[data-toggle="popover"]').popover({
            html: true,
            container: 'body',
            placement: 'bottom',
            trigger: 'hover',
            sanitize: false,
            delay: { "show": 500, "hide": 1000000 },
        });
    });
});

// Dialog grade settings.
require(['jquery', 'core/modal_factory'], function($, ModalFactory) {
    var elements = $('.grade-item-dialog');
    $.each(elements, function(index, element) {
        var trigger = $('#' + element.id);
        var gradesettings = $('#grade-settings-' + element.id);
        var title = $(gradesettings).find('.title');
        var body = $(gradesettings).find('.grade-settings-form');

        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: title.text(),
            body: body.html()
        }, trigger).done(function(modal) {
            $('.grade-item-dialog').css('opacity', '1');
            var select = $(modal.body).find('select');
            var savebutton = $(modal.footer).find('.btn-primary');
            $(savebutton).click(function() {
                var gradeitemid = $(element).attr('id').match(/\d+/);
                $('select[name=gradepass' + gradeitemid + ']').val($(select).val());
                $('#grade-pass-form_' + gradeitemid).submit();
            });
        });
    });
});
