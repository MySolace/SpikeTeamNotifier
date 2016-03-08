var Groups = {
    init: function() {
        Groups.$buttons = {
            buttons: $('.groups button'),
            groups: $('.groups button.group'),
            all: $('.groups button.all'),
        };
        Groups.$table = $('table.spikers');

        if (!Groups.$buttons.buttons.length) return false;

        Groups.bindClicks();
    },

    bindClicks: function() {
        Groups.$buttons.buttons.click(function () {
            Groups.$buttons.buttons.removeClass('btn-primary').addClass('btn-default');
            $(this).removeClass('btn-default').addClass('btn-primary');
        });

        Groups.$buttons.all.click(function() {
            Groups.$table.find('tr.spiker').show();
            $('.spiker-numbers .group').html(Groups.$table.find('tr.spiker').length);
            $('.export-button a').attr('href', Routing.generate('spikers_export'));
            $.get(Routing.generate('group_emptiest_check'), function (data) {
                $('select#form_group').val(data.emptiest);
            });
            history.pushState({}, 'Spike Team Notifier', Routing.generate('spikers'));
        });

        Groups.$buttons.groups.click(function() {
            var id = $(this).attr('id').replace('group-','');
            Groups.$table.find('tr.spiker').hide();
            Groups.$table.find('tr.group-'+id.toString()).show();
            $('.spiker-numbers .group').html(Groups.$table.find('tr.group-'+id.toString()).length);
            $('select#form_group').val(id);
            $('.export-button a').attr('href', Routing.generate('spikers_export', {gid: id}));
            history.pushState({}, 'Spike Team Notifier', Routing.generate('spikers', {group:id}));
        });
    },
};
