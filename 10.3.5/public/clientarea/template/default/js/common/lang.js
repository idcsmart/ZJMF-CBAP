(function () {
  const defaultLang = localStorage.getItem('lang') || 'zh-cn'
  localStorage.lang = defaultLang
  document.writeln(`<script src="${url}/lang/${localStorage.lang}.js"><\/script>`)
}())