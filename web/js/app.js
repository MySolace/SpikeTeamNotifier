$(document).ready(function(){
    $("body.section-button").find("li.button").addClass("active");
    $("body.section-spikers").find("li.spikers").addClass("active");
    $("body.section-admin").find("li.admin").addClass("active");
    $("body.section-settings").find("li.settings").addClass("active");
    $("#button.enabled").find("img").unwrap();
    $("#button.enabled").click(function(){
        if (confirm("Are you sure you want to notify the Spike Team?") == true) {
            $.ajax({url:"goteamgo"});
            $(this).removeClass("enabled").addClass("disabled");
        }
    });
    $("#button.disabled").click(function(){
        alert("Sorry, you cannot alert the Spike Team yet.");
    });
    $("button.token-button").click(function(){
        $(".token-para").toggle();
    });
});