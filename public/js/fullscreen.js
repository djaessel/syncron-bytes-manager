/**
 * created by https://www.sitepoint.com/html5-full-screen-api/
 */
$(function () {

  var pfx = ["webkit", "moz", "ms", "o", ""];

  function RunPrefixMethod(obj, method) {
    var p = 0, m, t;
    while (p < pfx.length && !obj[m]) {
      m = method;
      if (pfx[p] == "") {
        m = m.substr(0, 1).toLowerCase() + m.substr(1);
      }
      m = pfx[p] + m;
      t = typeof obj[m];
      if (t != "undefined") {
        pfx = [pfx[p]];
        return (t == "function" ? obj[m]() : obj[m]);
      }
      p++;
    }
  }

  $('.videoButton').on('click', function(e) {
    e.preventDefault();
    
    if (RunPrefixMethod(document, "FullScreen") || RunPrefixMethod(document, "IsFullScreen")) {
      RunPrefixMethod(document, "CancelFullScreen");
    }
    else {
      RunPrefixMethod($("#containerX"), "RequestFullScreen");
    }
  });

});
