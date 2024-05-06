$(function () {
  // 点击 country-item 时，给当前元素添加 active 类，移除其他元素的 active 类
  $(".country-item").click(function () {
    $(this).addClass("active").siblings().removeClass("active");
    // 设置他父元素的兄弟元素的di index个为显示
    // 找到他父元素的兄弟元素
    $(this)
      .parent()
      .parent()
      .find(".hot-list")
      .eq($(this).index())
      .addClass("active")
      .siblings()
      .removeClass("active");
  });
});
