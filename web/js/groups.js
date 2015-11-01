var Groups = {
    init: function() {
        Groups.$buttons = {
            buttons: $('.groups button').not('.shuffle'),
            groups: $('.groups button.group'),
            all: $('.groups button.all'),
            add: $('.groups button.add'),
            onoff: $('.groups .onoffswitch'),
            shuffle: $('.groups button.shuffle'),
        };
        Groups.$table = $('table.spikers');

        if (!Groups.$buttons.buttons.length) return false;

        var $onoffbox = Groups.$buttons.onoff.find('input');
        var id = $onoffbox.attr('id').replace('onoff-','');
        if (id != null) {
            Groups.statusToggle($onoffbox, id);
        }

        $.get(Routing.generate('group_status_check'), function (data) {
            if (data.enabled) {
                $.each($('select#form_group option'), function (index, e) {
                    var val = $(e).val();
                    if (!parseInt(data.enabled[val])) {
                        $('select#form_group option[value="'+val+'"]').addClass('disabled');
                    }
                });
            }
        });

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
            Groups.$buttons.onoff.hide();
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
            $.get(Routing.generate('group_status_check', {id:id}), function (data) {
                if (data) {
                    var $onoffbox = Groups.$buttons.onoff.find('input');
                    if (data.enabled) {
                        $onoffbox.unbind().prop('checked', true);
                    } else {
                        $onoffbox.unbind().prop('checked', false);
                    }
                    Groups.statusToggle($onoffbox, id);
                }
            });
            Groups.$buttons.onoff.show();
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
                    $.get(Routing.generate('spikers_shuffle'), function (data) {
                        if (data) {
                            location.reload();
                        }
                    });
                }
            }
        });

        $('.import-link').click(function() {
            if (confirm("Are you sure you would like to import the new Spikers from the URL specified in the settings?") == true) {
                $.get(Routing.generate('spikers_import'), function (data) {
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

    statusToggle: function($onoffbox, id) {
        var nextStatus = ($onoffbox.prop('checked')) ? 0 : 1;
        $onoffbox.change(function() {
            $.get(Routing.generate('group_status_set', {id:id, status:nextStatus} ), function (data) {
                var $rows = Groups.$table.find('tr.group-'+id.toString());
                var $options = Groups.$table.find('tr option[value="'+id+'"]');
                if (nextStatus) {
                    $rows.removeClass('disabled');
                    $options.removeClass('disabled');
                } else {
                    $rows.addClass('disabled');
                    $options.addClass('disabled');
                }
            });
            $onoffbox.unbind();
            Groups.statusToggle($onoffbox, id);
        })
    }
};
