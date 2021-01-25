
function openFullscreen(fullElem) {
  if (fullElem.requestFullscreen) {
    fullElem.requestFullscreen();
  } else if (fullElem.webkitRequestFullscreen) { /* Safari */
    fullElem.webkitRequestFullscreen();
  } else if (fullElem.msRequestFullscreen) { /* IE11 */
    fullElem.msRequestFullscreen();
  }
}

function closeFullscreen() {
  if (document.exitFullscreen) {
    document.exitFullscreen();
  } else if (document.webkitExitFullscreen) { /* Safari */
    document.webkitExitFullscreen();
  } else if (document.msExitFullscreen) { /* IE11 */
    document.msExitFullscreen();
  }
}
