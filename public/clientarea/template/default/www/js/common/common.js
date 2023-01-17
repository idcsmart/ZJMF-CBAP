(function () {
    // 动态计算根元素的fontsize
    let sizeWidth =  document.documentElement.clientWidth;  // 初始宽宽度
    function setRootFontSize() {
        let rem, rootWidth;
        let rootHtml = document.documentElement;

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