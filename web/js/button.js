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
            var next = 1;
            if (id != 'all') {
                var count = $select.find('option').length - 1;
                modulo = (parseInt(id) + 1) % count;
                next = (modulo) ? modulo : count;
            }
            $select.find('option').removeAttr('disabled');
            $select.find('option[value="' + id + '"]').attr('disabled', 'disabled');
            $select.val(next);
            $('.latest .latest-group').html(data.id);
            $('.latest .latest-time').html(data.time);
        });
    }
}



