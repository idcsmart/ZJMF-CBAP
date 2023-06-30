$(function () {
    // 点击 country-item 时，给当前元素添加 active 类，移除其他元素的 active 类
    $('.country-item').click(function () {
        $(this).addClass('active').siblings().removeClass('active')
       

    })
})
