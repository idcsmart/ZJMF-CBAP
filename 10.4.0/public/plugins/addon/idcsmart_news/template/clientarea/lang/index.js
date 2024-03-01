(function () {
  const plugin_lang = {
    "zh-cn": {
      // 新闻中心
      news_text1: "新闻中心",
      news_text2: "分类",
      news_text3: "关键字",
      news_text4: "新闻详情",
      news_text5: "全部",
      news_text6: "更新时间",
      news_text7: "暂无数据",
      news_text8: "附件",
      news_text9: "上一篇",
      news_text10: "下一篇",
      news_text11: "资源中心",
      news_text12: "请输入你需要搜索的内容",
      news_text13: "帮助中心",
      news_text14: "新闻中心",
      news_text15: "文件下载",
    },
    "en-us": {
      news_text1: "News Center",
      news_text2: "Classification",
      news_text3: "Keywords",
      news_text4: "News Details",
      news_text5: "All",
      news_text6: "Update time",
      news_text7: "No data available at the moment",
      news_text8: "Attachment",
      news_text9: "Previous",
      news_text10: "Next article",
      news_text11: "Resource Center",
      news_text12: "Please enter the content you need to search for",
      news_text13: "Help Center",
      news_text14: "News Center",
      news_text15: "File Download",
    },
    "zh-hk": {
      news_text1: "新聞中心",
      news_text2: "分類",
      news_text3: "關鍵字",
      news_text4: "新聞詳情",
      news_text5: "全部",
      news_text6: "更新時間",
      news_text7: "暫無數據",
      news_text8: "附件",
      news_text9: "上一篇",
      news_text10: "下一篇",
      news_text11: "資源中心",
      news_text12: "請輸入你需要蒐索的內容",
      news_text13: "幫助中心",
      news_text14: "新聞中心",
      news_text15: "文件下載",
    },
  };
  const DEFAULT_LANG = localStorage.getItem("lang") || "zh-cn";
  checkLangFun(plugin_lang);
  window.plugin_lang = plugin_lang[DEFAULT_LANG];
})();
