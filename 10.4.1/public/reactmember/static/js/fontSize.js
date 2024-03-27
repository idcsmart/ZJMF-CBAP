(function (doc, win) {
  var docEl = doc.documentElement,
    resizeEvt = "orientationchange" in window ? "orientationchange" : "resize",
    recalc = function () {
      function getScrollbarWidth () {
        var odiv = document.createElement('div'),
          styles = {
            width: '100px',
            height: '100px',
            overflowY: 'scroll'
          },
          i, scrollbarWidth;
        for (i in styles) odiv.style[i] = styles[i];
        document.body.appendChild(odiv);
        scrollbarWidth = odiv.offsetWidth - odiv.clientWidth;
        var odivParent = odiv.parentNode;
        odivParent.removeChild(odiv);
        return scrollbarWidth;
      };
      var result = window.matchMedia('(min-width:1440px)');
      var resultWAP = window.matchMedia('(max-width:768px)');

      var scrollbarWidth = getScrollbarWidth();
      var clientWidth = docEl.clientWidth - scrollbarWidth;
      if (!clientWidth) return;

      if (result.matches) {
        docEl.style.fontSize = "100px";
      } else if (resultWAP.matches) {
        docEl.style.fontSize = 100 * (clientWidth / (750 - scrollbarWidth)) + "px";
      } else {
        docEl.style.fontSize = 100 * (clientWidth / (1440 - scrollbarWidth)) + "px";
      }
    };
  if (!doc.addEventListener) return;
  win.addEventListener(resizeEvt, recalc, false);
  doc.addEventListener('DOMContentLoaded', recalc, false);
})(document, window);