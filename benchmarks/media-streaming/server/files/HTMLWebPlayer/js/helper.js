

function populateVideosList(selectedList) {
    var size = Math.min(selectedList.length, 10)
    for (i = 0; i < size; ++i) {
        $('#videolist').append($('<option>', {
            value: selectedList[i],
            text: "video" + (i + 1).toString()
        }));
    }
}

$(document).ready(function() {
    $.getScript("/test_videos.js")
        .done(function(script, textStatus) {
            console.log(textStatus);

            populateVideosList(videos240p)
            var videoElm = videojs("#my-video");
            videoElm.play();

        })
        .fail(function(jqxhr, settings, exception) {
            $("div.log").text("Triggered ajaxError handler.");
        });

});

$('#resolution').on('change', function() {
    var optVal = $("#resolution option:selected").val();
    $("#videolist").empty();
    var selectedList;
    if (optVal == "240p")
        selectedList = videos240p
    else if (optVal == "360p")
        selectedList = videos360p
    else if (optVal == "480p")
        selectedList = videos480p
    else
        selectedList = videos720p

    populateVideosList(selectedList)

})


function changeVideo(videoSource, type) {
    var videoElm = videojs("#my-video");
    if (!videoElm.paused) {
        videoElm.pause();
    }
    videoElm.src(videoSource)
    videoElm.load();
    videoElm.play();
}

$('#videolist').on('change', function() {
    var optVal = $("#videolist option:selected").val();
    type = "video/mp4"
    optVal = "/" + optVal
    console.log(optVal)
    changeVideo(optVal, type)
})
