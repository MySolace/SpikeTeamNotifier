var App = {
    init: function() {
        $("body.section-button").find("li.button").addClass("active");
        $("body.section-spikers").find("li.spikers").addClass("active");
        $("body.section-admin").find("li.admin").addClass("active");
        $("body.section-settings").find("li.settings").addClass("active");
        $("body.section-stats").find("li.stats").addClass("active");
    }
}
