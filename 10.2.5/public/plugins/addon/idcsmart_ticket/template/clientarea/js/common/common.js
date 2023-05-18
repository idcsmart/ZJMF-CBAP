(function () {
    // 动态计算根元素的fontsize
    let sizeWidth =  document.documentElement.clientWidth;  // 初始宽宽度
    function setRootFontSize() {
        let rem, rootWidth;
        let rootHtml = document.documentElement;
        if(sizeWidth>rootHtml.clientWidth){
            if((sizeWidth > 750) && (rootHtml.clientWidth <= 750)){
                window.location.reload();
            }
        }else{
            if((sizeWidth <= 750) && (rootHtml.clientWidth > 750)){
                window.location.reload();
            }
            // 大于750时 刷新页面
        }
        sizeWidth = rootHtml.clientWidth
        if(rootHtml.clientWidth > 750){
            //限制展现页面的最小宽度
            rootWidth = rootHtml.clientWidth < 1200 ? 1200 : rootHtml.clientWidth > 1920 ? 1920 : rootHtml.clientWidth;
            // rootWidth = rootHtml.clientWidth;
            // 19.2 = 设计图尺寸宽 / 100（ 设计图的rem = 100 ）
            rem = rootWidth / 19.2;
            // 动态写入样式
            rootHtml.style.fontSize = `${rem}px`;
        }else{
            rootWidth = rootHtml.clientWidth
            rem = rootWidth / 7.5
            rootHtml.style.fontSize = `${rem}px`;
        }

    }
    setRootFontSize();
    window.addEventListener("resize", setRootFontSize, false);
})();



// (function (doc, win) {
//     var docEl = doc.documentElement,
//       resizeEvt = "orientationchange" in window ? "orientationchange" : "resize",
//       recalc = function () {
//         function getScrollbarWidth () {
//           var odiv = document.createElement('div'),
//             styles = {
//               width: '100px',
//               height: '100px',
//               overflowY: 'scroll'
//             },
//             i, scrollbarWidth;
//           for (i in styles) odiv.style[i] = styles[i];
//           document.body.appendChild(odiv);
//           scrollbarWidth = odiv.offsetWidth - odiv.clientWidth;
//           var odivParent = odiv.parentNode;
//           odivParent.removeChild(odiv);
//           return scrollbarWidth;
//         };
//         var result = window.matchMedia('(min-width:1440px)');
//         var resultWAP = window.matchMedia('(max-width:768px)');
  
//         var scrollbarWidth = getScrollbarWidth();
//         var clientWidth = docEl.clientWidth - scrollbarWidth;
//         if (!clientWidth) return;
  
//         if (result.matches) {
//           docEl.style.fontSize = "100px";
//         } else if (resultWAP.matches) {
//           docEl.style.fontSize = 100 * (clientWidth / (750 - scrollbarWidth)) + "px";
//         } else {
//           docEl.style.fontSize = 100 * (clientWidth / (1440 - scrollbarWidth)) + "px";
//         }
//       };
//     if (!doc.addEventListener) return;
//     win.addEventListener(resizeEvt, recalc, false);
//     doc.addEventListener('DOMContentLoaded', recalc, false);
//   })(document, window);