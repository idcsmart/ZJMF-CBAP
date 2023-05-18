(function () {
  document.documentElement.setAttribute('theme-mode', localStorage.getItem('theme-mode') || 'light')
  document.documentElement.setAttribute('theme-color', localStorage.getItem('theme-color') || 'default')

  if (localStorage.getItem('backLang') == null) {
    document.writeln(`<script src="${url}/lang/zh-cn.js"><\/script>`)
  } else {
    document.writeln(`<script src="${url}/lang/${localStorage.getItem('backLang')}.js"><\/script>`)
  }
}())