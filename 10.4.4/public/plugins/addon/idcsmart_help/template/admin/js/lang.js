(function () {
  if (localStorage.getItem('backLang') == null) {
    document.writeln('<script src="/plugins/addon/idcsmart_help/template/admin/lang/zh-cn.js"><\/script>')
  } else {
    document.writeln('<script src="/plugins/addon/idcsmart_help/template/admin/lang/' + localStorage.getItem('backLang') + '.js"><\/script>')
  }
}())
