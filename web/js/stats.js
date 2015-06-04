var Stats = {
    initialize: function () {
        $.ajax({
            type: "GET",
            url: "../stats.csv",
            dataType: "text",
            success: function(data) {
                this.data = data;
                this.setup();
            }.bind(this)
        });
    },

    setup: function () {
        this.data = this.data.split("\n").slice(0, -1).reverse();
        for (var i = 0; i < this.data.length; i++) {
            this.data[i] = this.data[i].split(",");
            var $option = $('<option>');
            $option.attr({'value': i}).text(this.data[i][0]);
            $('div.date-select select').append($option);
        }

        $("select").change(function(){
            this.update($("select").val());
        }.bind(this));

        this.update(0);
    },

    update: function (idx) {
        $('.issue').remove();
        for (var i = 0; i < $('div.stats li').length; i++) {
            $('div.stats li span').eq(i).text(this.data[idx][i+1]);
        }

        $('.right-list li span').append("%");

        if (this.data[idx].length > 10) {
            for (i = 10; i < this.data[idx].length; i += 2) {
                var $issue = $('<li>'),
                    issueName = this.data[idx][i].replace(/"/g, ""),
                    issuePercentage = Math.round(this.data[idx][i+1] /
                        this.data[idx][2] * 10000) / 100;
                $issue.html(issueName + ': <span>' + issuePercentage+ '%</span>');
                $issue.addClass('issue').css("text-transform","capitalize");
                $('.issues-list').append($issue);
            }
        } 
    }
};