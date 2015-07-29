var Groups = {
    init: function() {
        Groups.$buttons = {
            buttons: $('.groups button'),
            groups: $('.groups button.group'),
            all: $('.groups button.all'),
            add: $('.groups button.add')
        };
        Groups.$table = $('table.spikers');

        Groups.list();
    },

    list: function() {
        Groups.$buttons.buttons.click(function () {
            if (!$(this).hasClass('add')) {
                Groups.$buttons.buttons.removeClass('btn-primary').addClass('btn-default');
                $(this).removeClass('btn-default').addClass('btn-primary');
            }
        });

        Groups.$table.find('tr.spiker').show();
        Groups.$buttons.all.removeClass('btn-default').addClass('btn-primary');

        Groups.$buttons.all.click(function() {
            Groups.$table.find('tr.spiker').show();
            $('.spiker-numbers .group').html(Groups.$table.find('tr.spiker').length);
        });
        Groups.$buttons.groups.click(function() {
            var id = $(this).attr('id').replace('group-','');
            Groups.$table.find('tr.spiker').hide();
            Groups.$table.find('tr.group-'+id.toString()).show();
            $('.spiker-numbers .group').html(Groups.$table.find('tr.group-'+id.toString()).length);
        });
        Groups.$buttons.add.click(function() {
            if (confirm("Are you sure you want to add another group?\nYou will not be able to remove it once it is created.") == true) {
                window.location.replace(Routing.generate('group_new'));
            }
        });
    }
};