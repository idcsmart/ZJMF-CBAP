$(function () {
    var SysSecond = 20000
    var InterValObj = null
    SetRemainTime()
    InterValObj = window.setInterval(SetRemainTime, 1000); //间隔函数，1秒执行
    //将时间减去1秒，计算天、时、分、秒
    function SetRemainTime() {
        if (SysSecond > 0) {
            SysSecond = SysSecond - 1;
            const minite = Math.floor((SysSecond / 60) % 60);      //计算分 
            const hour = Math.floor((SysSecond / 3600) % 24);      //计算小时
            const day = Math.floor((SysSecond / 3600) / 24);       //计算天 
            $("#day1").text(day >= 10 ? Math.floor((day / 10)) : 0)
            $("#day2").text(day >= 10 ? (day - Math.floor((day / 10)) * 10) : day)
            $("#hour1").text(hour >= 10 ? Math.floor((hour / 10)) : 0)
            $("#hour2").text(hour >= 10 ? (hour - Math.floor((hour / 10) * 10)) : hour)
            $("#min1").text(minite >= 10 ? Math.floor((minite / 10)) : 0)
            $("#min2").text(minite >= 10 ? (minite - Math.floor((minite / 10)) * 10) : minite)
        } else {//剩余时间小于或等于0的时候，就停止间隔函数
            //这里可以添加倒计时时间为0后需要执行的事件
            InterValObj = null
            location.go(0)
        }
    }

    // 打开实名认证弹窗
    function openAuthenticationDialog() {
        $("#authentication-modal").fadeIn().addClass('show');
        $(".modal-content").fadeIn().addClass('show');
    }
    // 当用户点击关闭按钮或在弹窗外部点击时，隐藏弹窗
    $(".dia-close, .dialog-box").click(function () {
        $(".dialog-box").fadeOut().removeClass('show');
        $('html').removeClass('no-scroll');
        $('body').removeClass('no-scroll');
    });


    $(".buy-btn").click(function () {
        $("#authentication-modal").fadeIn().addClass('show');
        $(".modal-content").fadeIn().addClass('show');
    })

    $(".cloud-buy-btn").click(function () {
        $("#buyCloud-modal").fadeIn().addClass('show');
        $(".modal-content").fadeIn().addClass('show');
    })
    // 防止在弹窗内部点击时弹窗被关闭
    $(".modal-content").click(function (e) {
        e.stopPropagation();
    });

    // 加按钮事件处理程序
    $('.plus-btn').click(function () {
        const quantity = parseInt($('#quantity').val());
        $('#quantity').val(quantity + 1);
    });

    // 减按钮事件处理程序
    $('.minus-btn').click(function () {
        const quantity = parseInt($('#quantity').val());
        if (quantity > 1) {
            $('#quantity').val(quantity - 1);
        }
    });
    // 输入事件处理程序
    $('#quantity').on('input', function () {
        const quantity = parseInt($('#quantity').val());
        if (isNaN(quantity) || quantity < 1) {
            $('#quantity').val(1);
        }
    });
    // 给选项卡元素添加点击事件监听器
    $('.tab-item').click(function () {
        // 切换选项卡的 active 样式
        $('.tab-item').removeClass('active-tab');
        $(this).addClass('active-tab');

        // 切换对应内容的显示状态

    });
    $('.cloud-price-box').click(function () {
        // 切换选项卡的 active 样式
        $(this).siblings().removeClass('active');
        $(this).addClass('active');
        // 切换对应内容的显示状态

    });

})