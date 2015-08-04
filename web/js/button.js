var Button = {
    init: function() {
        $("#button.enabled").find("button").unwrap();
        $("#button").click(function(){
            if ($(this).is(".enabled")) {
                if (confirm("Are you sure you want to notify the Spike Team?") == true) {
                    $(this).removeClass("enabled").addClass("disabled");
                    var urlId = $(this).find('button').attr('id');
                    urlId = $('.group-select select').val();
                    Button.goCallback(urlId);
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
            $select.find('option').removeAttr('disabled');
            $select.find('option[value="' + id + '"]').attr('disabled', 'disabled');
            $.each(data.enabled, function (group_id, group_enabled) {
                var $option = $select.find('option[value="' + group_id + '"]');
                if (!parseInt(group_enabled)) {
                    $option.not(':disabled').attr('disabled', 'disabled');
                }
            });
            $select.val(data.next);
            $('.latest .latest-group').html(data.id);
            $('.latest .latest-time').html(data.time);
        });
    }
}



