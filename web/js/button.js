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
        $("button.token-button").click(function(){
            $(".token-para").toggle();
            $(this).toggleClass("token-showing");
            if ($(this).hasClass("token-showing")) {
                $(this).find(".replace").text("hide");
            } else {
                $(this).find(".replace").text("view");
            }
        });
    },

    goCallback: function(id) {
        $.ajax(Routing.generate('goteamgo', {gid:id}));
    }
}



