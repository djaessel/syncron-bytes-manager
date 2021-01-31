
$(function () {

  // CONTROLS - START
  var fullElem = document.documentElement;

  function toggleExpandButton() {
    $('#expandButtonIcon').toggleClass("fa-expand");
    $('#expandButtonIcon').toggleClass("fa-compress");
  }

  var curVideo = $('#curVideo');
  var curVideoDOM = document.getElementById("curVideo");

  $('body').on('click', '#curVideo', function(e) {
    if (e.target.paused == false) {
      curVideoDOM.pause();
    } else {
      curVideoDOM.play();
      showMenu();
    }
  });

  $('#playButton').on('click', function(){
    if ($('#playButtonIcon').hasClass("fa-play")){
        curVideoDOM.play();
    } else {
        curVideoDOM.pause();
    }
  });

  $('#expandButton').on('click', function(){
    if ($('#expandButtonIcon').hasClass("fa-expand")){
        openFullscreen(fullElem);
    } else {
        closeFullscreen();
    }

    toggleExpandButton();
  });

  $('select').change(function () {
    var optionSelected = $(this).find("option:selected");
    var url = optionSelected.data("url");
    $.post(url, {lang: optionSelected.val()}, function(data, status){
      if (data == "success") {
        location.reload(); // later just change player url
      }
    });
  });

  $('.language-option a').click(function () {
    var url = $(this).data("url");
    $.post(url, {lang: $(this).data("lang")}, function(data, status){
      if (data == "success") {
        location.reload(); // later just change player url
      }
    });
  });
  // CONTROLS - END


  // VIDEO EVENTS - START
  function tooglePlayButton() {
    $('#playButtonIcon').toggleClass("fa-play");
    $('#playButtonIcon').toggleClass("fa-pause");
  }

  curVideo.on('play', function(e) {
    tooglePlayButton();
    $("#videoTime").attr("max", curVideoDOM.duration);
  });

  curVideo.on('pause', function(e) {
    tooglePlayButton();
  });
  // VIDEO EVENTS - END

  // NEXT VIDEO - START
  curVideo.on('ended', function(e) {
    var url = $("#nextVid").data("url");
    // FIXME: find better solution later + fix instant jump (sometimes)
    // check controller code as well
    window.location = url;
  });
  // NEXT VIDEO - START


  // SEEK BAR - START
  function formatTime(timeVal) {
    if (timeVal < 10) {
      timeVal = "0" + timeVal;
    }
    return timeVal;
  }

  function updateTimeDisplay(time) {
    var minutes = Math.round(time / 60);
    var seconds = Math.round(time % 60);
    var displayedTime = "- " + formatTime(minutes) + ":" + formatTime(seconds);
    $("#videoTimeDisplay").html(displayedTime);
  }

  curVideoDOM.ontimeupdate = function(){
    $("#videoTime").val(curVideoDOM.currentTime);

    var duration = curVideoDOM.duration;
    var maxWidth = $("#videoTime").width();

    // played time
    var playedTimePercentage = curVideoDOM.currentTime / duration;
    var newWidth = maxWidth * playedTimePercentage;
    $("#playedTime").width(newWidth);

    // buffer bar
    var currentBuffer = curVideoDOM.buffered.end(0);
    var percentage = 100 * currentBuffer / duration;
    var shownBuffer = maxWidth * percentage;
    $('#bufferBar').width(shownBuffer);

    var playableTime = duration - curVideoDOM.currentTime;
    updateTimeDisplay(playableTime);
  };

  $("#videoTime").on("input", function(e){
    curVideoDOM.currentTime = $(this).val();
    if ($("#videoTime").attr("max") == "1000") {
      $("#videoTime").attr("max", curVideoDOM.duration);
    }
  });

  $('#videoTime').hover(function() {
    $('#playedTime').css('opacity', '1.0');
  }, function() {
    // on mouseout
    $('#playedTime').css('opacity', '0.5');
  });
  // SEEK BAR - END


  // AUDIO BAR - START
  curVideoDOM.onvolumechange = function(){
    var percentage = curVideoDOM.volume * 100;
    $("#videoVolume").val(percentage);
  };

  $("#videoVolume").on("input", function(e){
    curVideoDOM.volume = $(this).val() * 0.01;
  });
  // AUDIO BAR - END

  // MENU SHOW HIDE - START
  function showMenu() {
    $(".video-bar").show();
    $("#videoPlayerContainer").css('opacity', '0.6');
    document.documentElement.style.cursor = 'auto';
  }

  function hideMenu() {
    if (curVideoDOM.paused == false) {
      $('.video-bar').fadeOut();
      $("#videoPlayerContainer").css('opacity', '1.0');
      //clearTimeout(t);
      document.documentElement.style.cursor = 'none';
    }
  }

  $(document).mousemove(function(e){
    showMenu();

    lastTimeMouseMoved = new Date().getTime();
    var t = setTimeout(function(){
      var currentTime = new Date().getTime();
      if(currentTime - lastTimeMouseMoved > 4000){
        hideMenu();
      }
    }, 5000);
  });
  // MENU SHOW HIDE - END

});
