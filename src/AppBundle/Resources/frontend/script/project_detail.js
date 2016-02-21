$(document).ready(function() {
    var formatSeconds = function(inputSeconds) {
        var hours = Math.floor(inputSeconds / 3600);
        var minutes = Math.floor((inputSeconds - (hours * 3600)) / 60);
        var seconds = Math.floor(inputSeconds - (hours * 3600) - (minutes * 60));

        return ("0" + hours).slice(-2) + ':' + ("0" + minutes).slice(-2) + ':' + ("0" + seconds).slice(-2);
    };

    var $table = $(".project-detail-table");
    if($table.length > 0 && projectStatus != "FINISHED") {
        (function(){
            var callee = arguments.callee;
            $.ajax({
                url: statusUrl
            }).done(function(data){
                var allFinished = true;
                $("#status").text("Status: " + data.status);
                $("#projectProgress").text("Progress: " + Math.round(data.progress * 100) + "%");

                $.each(data.tasks, function(index, value) {
                    var $row = $("#"+value.id);
                    var $children = $row.children();
                    var backgroundClass = "";
                    if(value.status == "RENDERING") {
                        backgroundClass = "info";
                    } else if(value.status == "FINISHED") {
                        backgroundClass = "success";
                    }
                    $row.removeClass("info");
                    $row.removeClass("success");
                    $row.addClass(backgroundClass);

                    $($children[1]).text(value.status);
                    $($children[2]).text(formatSeconds(value.runtime));
                    $($children[3]).text(formatSeconds(value.remaining));
                    var $progressBar = $($children[4]).find(".progress-bar");
                    var $noProgressBar = $($children[4]).find(".no-progress-bar");
                    var percent = Math.round(value.progress*100)+"%";
                    $progressBar.css("width", percent);
                    $progressBar.text(percent);
                    $noProgressBar.text(percent);

                    if(value.status == "FINISHED") {
                        $($children[1]).html('<a href="'+imageUrl.replace("42", value.id)+'" target="_blank">' + value.status + '</a>');
                    } else {
                        allFinished = false;
                    }
                });

                if(allFinished) {
                    $table.parent().before('<a class="btn btn-primary" href="' + downloadUrl + '">Download result</a>');
                    return;
                }

                setTimeout(callee, 5000);
            });
        })();
    }
});