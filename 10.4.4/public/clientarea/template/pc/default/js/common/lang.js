(function () {
  const Element_lang = {
    "en-us": "en",
    "zh-cn": "zhCN",
    "zh-hk": "zhTW",
  };
  const DEFAULT_LANG = localStorage.getItem("lang") || "zh-cn";
  document.writeln(
    `<script src="${url}lang/${DEFAULT_LANG}/element-lang.js"><\/script>`
  );
  document.writeln(
    `<script src="${url}lang/${DEFAULT_LANG}/index.js"><\/script>`
  );
  document.writeln(
    `<script>ELEMENT.locale(ELEMENT.lang.${Element_lang[DEFAULT_LANG]}) <\/script>`
  );
})();
