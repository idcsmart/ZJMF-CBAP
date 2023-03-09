(function () {
  if (localStorage.getItem('lang') == "undefined" || localStorage.getItem('lang') == null || localStorage.getItem('lang') == undefined) {
    document.writeln(`<script src="${url}/lang/zh-cn.js"><\/script>`)
  } else {
    document.writeln(`<script src="${url}/lang/${localStorage.getItem('lang')}.js"><\/script>`)
  }
}())