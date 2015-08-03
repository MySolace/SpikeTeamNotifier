var Stats = {
    initialize: function () {
        $.ajax({
            type: "GET",
            url: "/data/stats.csv",
            dataType: "text",
            success: function(data) {
                this.data = data;
                this.setup();
            }.bind(this)
        });
    },

    setup: function () {
        this.data = this.data.split("\n").slice(0, -1);
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
        $('.messages-count span').text(this.data[idx][1]);
        $('.conversations-count span').text(this.data[idx][2]);
        $('.texters-count span').text(this.data[idx][3]);
        $('.counselors-count span').text(this.data[idx][4]);
        $('.active-rescues-count span').text(this.data[idx][5]);
        $('.five-minute-wait-rate span').text(this.data[idx][6] + '%');
        $('.engaged-rate span').text(this.data[idx][7] + '%');
        $('.quality-rating span').text(this.data[idx][8] + '%');
        $('.new-texters-rate span').text(this.data[idx][9] + '%');

        if (this.data[idx].length > 10) {
            for (i = 10; i < this.data[idx].length; i += 2) {
                var $issue = $('<li>'),
                    issueName = this.data[idx][i].replace(/"/g, ""), //remove quotes
                    issuePercentage = Math.round(this.data[idx][i+1] /
                        this.data[idx][2] * 10000) / 100;

                issueName = issueName.replace(/_/g, " "); //remove underscores
                $issue.html(issueName + ': <span>' + issuePercentage+ '%</span>');
                $issue.addClass('issue').css("text-transform","capitalize");
                $('.issues-list').append($issue);
            }
        }

        //sort issues list in descending percentages
        var $issuesList = $('ul.issues-list'),
            $issues = $issuesList.children('li');

        $issues.sort(function(x,y){
            var xVar = parseFloat($(x).children('span').text()),
                yVar = parseFloat($(y).children('span').text());

            if (xVar > yVar) {
                return -1;
            } else if (xVar < yVar) {
                return 1;
            } else {
                return 0;
            }
        });

        $issues.detach().appendTo($issuesList);
    }
};
