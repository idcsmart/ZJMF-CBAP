$(function () {
    // 点击class creat-btn 的时候 弹出购买域名的弹窗
    $('.creat-btn').click(function () {
        $('body').css('overflow', 'hidden')
        $('html').css('overflow', 'hidden')
        $('.creat-template-box').show()
        $('.creat-form').animate({ width: '1400px' }, 300)
    })
    // 关闭弹窗函数
    function closeDia() {
        $('.creat-form').animate({ width: '0px' }, 300)
        // 带动画的关闭弹窗
        setTimeout(function () {
            $('.creat-template-box').hide()
            // 关闭弹窗后恢复滚动条
            $('body').css('overflow', 'auto')
            $('html').css('overflow', 'auto')
        }, 300)
    }



    // 点击creat-template-box 和 close-dia-btn 的时候 弹窗消失
    $('.creat-template-box,.close-dia-btn').click(function () {
        closeDia()
    })

    // 点击creat-form 的时候 弹窗不消失
    $('.creat-form').click(function (e) {
        e.stopPropagation()
    })
    // 点击 user-item 添加 active 类
    $('.user-item').click(function () {
        $('.user-item').removeClass('active')
        $(this).addClass('active')
    })
    // 当鼠标移入form-value 的时候 判断是否有 show-tip这个自定义属性 
    $('.form-value').mouseenter(function () {
        // 如果有 show-tip 这个自定义属性
        if ($(this).attr('show-tip')) {
            $('.no-tips').hide()
            // 把这个值赋值给 input-tip 的内容 解析为html
            $('.input-tip').html($(this).attr('show-tip'))
            $('.input-tip').show()
        } else {
            $('.input-tip').hide()
            $('.no-tips').show()
        }

    })
})
