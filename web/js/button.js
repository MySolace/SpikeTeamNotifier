var Button = {
    init: function() {
        $("#button.enabled").find("button").unwrap();
        $("#button").click(function(){
            var groupId = $('.group-select select').val();
            console.log(groupId);
            var days = {
                1: 'Sunday',
                2: 'Monday',
                3: 'Tuesday',
                4: 'Wednesday',
                5: 'Thursday',
                6: 'Friday',
                7: 'Saturday'
            };
            if ($(this).is(".enabled")) {
                if (confirm(
                        "Are you sure you want to notify Group " + groupId + " (" + days[groupId] + ") of the Spike Team?*\n\n"
                        + "Group 1 = Sunday\n"
                        + "Group 2 = Monday\n"
                        + "Group 3 = Tuesday\n"
                        + "Group 4 = Wednesday\n"
                        + "Group 5 = Thursday\n"
                        + "Group 6 = Friday\n"
                        + "Group 7 = Saturday\n\n"
                        + "* (10pm EST till 6am EST of the following day)"
                    ) == true) {
                    $(this).removeClass("enabled").addClass("disabled");
                    Button.goCallback(groupId);
                }
            }
            else if ($(this).is(".disabled")) {
                alert("Sorry, you cannot alert the Spike Team yet.");            
            }
        });
    },

    goCallback: function(id) {
        $.get(Routing.generate('goteamgo', {gid:id}), function (data) {
            var $select = $('.group-select select');
            $('.latest .latest-group').html(data.id);
            $('.latest .latest-time').html(data.time);
        });
    }
}



