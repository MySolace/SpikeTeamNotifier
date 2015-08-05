var App = {
    init: function() {
        var sections = [
            'button',
            'spikers',
            'admin',
            'settings',
            'stats',
        ];
        $.each(sections, function (index, val) {
            $('body.section-'+val).find('li.'+val).addClass('active');
        });
    }
}
