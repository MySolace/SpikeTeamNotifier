var Groups = {
    init: function() {
        Groups.$buttons = {
            buttons: $('.groups button').not('.shuffle'),
            groups: $('.groups button.group'),
            all: $('.groups button.all'),
            add: $('.groups button.add'),
            shuffle: $('.groups button.shuffle'),
        };
        Groups.$table = $('table.spikers');

        if (!Groups.$buttons.buttons.length) return false;

        Groups.bindClicks();
    },

    bindClicks: function() {
        Groups.$buttons.buttons.click(function () {
            if (!$(this).hasClass('add')) {
                Groups.$buttons.buttons.removeClass('btn-primary').addClass('btn-default');
                $(this).removeClass('btn-default').addClass('btn-primary');
            }
        });

        Groups.$buttons.all.click(function() {
            Groups.$buttons.shuffle.show();
            Groups.$table.find('tr.spiker').show();
            $('.spiker-numbers .group').html(Groups.$table.find('tr.spiker').length);
            $('.export-button a').attr('href', Routing.generate('spikers_export'));
            $.get(Routing.generate('group_emptiest_check'), function (data) {
                $('select#form_group').val(data.emptiest);
            })
            history.pushState({}, 'Spike Team Notifier', Routing.generate('spikers'));
        });

        Groups.$buttons.groups.click(function() {
            var id = $(this).attr('id').replace('group-','');
            Groups.$buttons.shuffle.hide();
            Groups.$table.find('tr.spiker').hide();
            Groups.$table.find('tr.group-'+id.toString()).show();
            $('.spiker-numbers .group').html(Groups.$table.find('tr.group-'+id.toString()).length);
            $('select#form_group').val(id);
            $('.export-button a').attr('href', Routing.generate('spikers_export', {gid: id}));
            history.pushState({}, 'Spike Team Notifier', Routing.generate('spikers', {group:id}));
        });

        Groups.$buttons.add.click(function() {
            if (confirm("Are you sure you want to add another group?\nYou will not be able to remove it once it is created.") == true) {
                window.location.replace(Routing.generate('group_new'));
            }
        });

        Groups.$buttons.shuffle.click(function() {
            if (confirm("Are you sure you want to shuffle all spikers?") == true) {
                if (confirm("Are you sure you sure you are sure? No turning back now!") == true) {
                    App.blockerAdd('Shuffling...');
                    $.get(Routing.generate('spikers_shuffle'), function (data) {
                        App.blockerRemove();
                        if (data) {
                            alert('All non-captain Spikers shuffled!');
                            location.reload();
                        }
                    });
                }
            }
        });

        $('.import-link').click(function() {
            if (confirm("Are you sure you would like to import the new Spikers from the URL specified in the settings?") == true) {
                App.blockerAdd('Importing...');
                $.get(Routing.generate('spikers_import'), function (data) {
                    App.blockerRemove();
                    if (data) {
                        alert('Congratulations, you just imported ' + data + ' new Spikers!');
                        location.reload();
                    } else {
                        alert('Unfortunately, no Spikers were imported.');
                    }
                });
            }
        });
    },
};
