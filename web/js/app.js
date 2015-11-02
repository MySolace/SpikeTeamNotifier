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
    },

    $blocker: $('<div class="blocker"><div class="background"></div><div class="message"></div></div>'),

    blockerAdd: function (message) {
        $('#body-wrapper').append(this.$blocker);
        $('#body-wrapper').find('.blocker .message').html(message);
    },

    blockerRemove: function () {
        $('#body-wrapper').find('.blocker').remove();
    },
}
