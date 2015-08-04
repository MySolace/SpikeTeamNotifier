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
            console.log($('body.section-'+val).find('li.'+val));
            $('body.section-'+val).find('li.'+val).addClass('active');
        });
    }
}
