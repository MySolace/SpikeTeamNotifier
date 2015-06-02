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
        $('li.messages-count span').text(this.data[idx][1]);
        $('li.conversations-count span').text(this.data[idx][2]);
        $('li.unique-texters-count span').text(this.data[idx][3]);
        $('li.unique-counselors-count span').text(this.data[idx][4]);
        $('li.active-rescues-count span').text(this.data[idx][5]);
        $('li.five-minute-wait-rate span').text(this.data[idx][6]);
        $('li.engaged-rate span').text(this.data[idx][7]);
        $('li.satisfaction-rate span').text(this.data[idx][8]);
        $('li.new-texters-rate span').text(this.data[idx][9]);

        if (this.data[idx].length > 10) {
            for (var i = 10; i < this.data[idx].length; i += 2) {
                $issue = $('<li>');
                $issue.text(this.data[idx][i] + ': ' + this.data[idx][i+1]);
                $issue.addClass('issue');
                $('.issues-list').append($issue);
            }
        } 
    }
};