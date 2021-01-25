
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
  curVideoDOM.ontimeupdate = function(){
    $("#custom-seekbar").value = curVideoDOM.currentTime;
  };

  $("#custom-seekbar").on("input", function(e){
    curVideoDOM.currentTime = this.value;
  });
  // SEEK BAR - END


  // AUDIO BAR - START
  curVideoDOM.onvolumechange = function(){
    var percentage = curVideoDOM.volume * 100;
    $("#custom-audiobar").value = percentage;
  };

  $("#custom-audiobar").on("input", function(e){
    curVideoDOM.volume = this.value / 100;
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
