(function () {
  document.documentElement.setAttribute('theme-mode', localStorage.getItem('theme-mode') || 'light')
  document.documentElement.setAttribute('theme-color', localStorage.getItem('theme-color') || 'default')
  if (localStorage.getItem('lang') == null) {
    document.writeln('<script src="./lang/zh-cn.js"><\/script>')
  } else {
    document.writeln('<script src="./lang/' + localStorage.getItem('lang') + '.js"><\/script>')
  }
}())