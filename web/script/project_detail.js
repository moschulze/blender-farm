$(document).ready(function() {
    var $table = $(".project-detail-table");
    if(projectStatus != "FINISHED" && $table.length > 0) {
        (function(){
            var callee = arguments.callee;
            $.ajax({
                url: statusUrl
            }).done(function(data){
                var allFinished = true;
                $("#status").text("Status: " + data.status);

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
                    $($children[2]).text(value.runtime+"s");
                    $($children[3]).text(value.remaining+"s");
                    var $progressBar = $($children[4]).find(".progress-bar");
                    var percent = Math.round(value.progress*100)+"%";
                    $progressBar.css("width", percent);
                    $progressBar.text(percent);

                    if(value.status == "FINISHED") {
                        $($children[5]).html('<a href="'+imageUrl.replace("42", value.id)+'" target="_blank">Show image</a>');
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