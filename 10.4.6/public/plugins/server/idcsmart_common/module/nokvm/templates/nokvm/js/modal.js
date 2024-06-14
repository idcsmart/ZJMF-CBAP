

var code = document.getElementById('secondVerifyCode');
// 二次验证弹窗
var tempFn = null
function getModalConfirm (text, callback) {
    $('#confirmModal').modal('show')
    $('#confirmBody').html(text)
    $(document).on('click', '#confirmSureBtn', function () {
        $('#confirmSureBtn').attr('disabled', true)
        setTimeout(function () {
            $('#confirmSureBtn').attr('disabled', false)
        }, 3000);
        callback();
        $(document).off("click")
    });
}
$('#secondVerifyModal').on('hide.bs.modal', function () {
    $('#secondVerifyCode').val('')
    code.classList.remove("is-invalid");
    code.classList.remove("is-valid");
})
// 二次验证
function getModal (action, title, text, data, callback) {
    if (!title) {
        title = '提示';
    }
    if (text) {
        content = '<div class="d-flex align-items-center"><i class="fas fa-exclamation-circle fs-20 text-warning mr-2"></i> ' + text + '</div>';
        area = ['420px'];
    } else {
        content = $('.' + action).html();
        area = ['500px'];
    }

    $('#customModal').modal('show')
    $('#customBody').html(content)
    $(document).on('click', '#customSureBtn', function () {
        if (data && !$('#customBody').find('form').eq(0).serialize()) {
            data = data;
        } else {
            data = $('#customBody').find('form').eq(0).serialize();
        }

        let _this = $(this)
        let text = $(this).text()
        if ($(this).find('.bx-loader').length < 1) {
            $(this).prepend('<i class="bx bx-loader bx-spin font-size-16 align-middle mr-2"></i>')
        }

        $.ajax({
            url: setting_web_url + '/' + action,
            type: 'POST',
            data: data,
            dataType: 'json',
            beforeSend: function () {
            },
            success: function (data) {
                _this.html(text);
                if (data.status == '200') {
                    toastr.success(data.msg);
                    //layer.closeAll();
                    setTimeout(function () {
                        if (action == 'modify_password') {
                            location.href = setting_web_url + 'login';
                        } else if (callback) {
                            callback()
                        }
                        else {
                            location.reload();
                        }
                    }, 2000);
                } else {
                    toastr.error(data.msg);
                }
            },
            error: function () {
                _this.html(text);
            }
        });
    });
}

// 是否需要二次验证
function isNeedSecond (action) {
    var second, actions;
    if (action != 'login') {
        second = Userinfo_allow_second_verify == '1' && Userinfo_user_second_verify == '1'
        actions = Userinfo_second_verify_action_home;
    } else { // 登录页二次验证
        second = Login_allow_second_verify == '1'
        actions = Login_second_verify_action_home;
    }

    return second && actions.includes(action)
}

function getSecondModal (action, fn, username, password) {
    // 登录页 二次验证
    if (action == 'login') {
        loginSecondPage(username, password)
    }
    tempFn = fn
    $('#secondVerifyModal').modal('show')
    $('#getCodeBox').html(`<button class="btn btn-secondary" id="secondCode" onclick="getSecurityCode('${action}', '${username}', '${password}')" type="button">获取验证码</button>`)
}

function secondVerifySubmitBtn (_this) {
    if (code.value == '') {
        code.classList.remove("is-valid"); //清除合法状态
        code.classList.add("is-invalid"); //添加非法状态
        return
    } else {
        code.classList.remove("is-invalid");
        code.classList.add("is-valid");
    }
    tempFn($("#secondVerifyType").val(), $('#secondVerifyCode').val())
}

// 获取验证码
function getSecurityCode (action, username, password) {
    /*var time = 60
    timer = setInterval( function(){
        $('#secondCode').text('剩余' + time-- + 's')
        $('#secondCode').attr('disabled', 'disabled')
        if (time <= 0) {
            $('#secondCode').text('获取验证码')
            $('#secondCode').removeAttr('disabled')
            timer = undefined
            clearInterval(timer)
        }

    }, 1000)*/
    console.log('执行到这一步了')
    if ($('#secondCode').data("disabled")) return false;
    $('#secondCode').data("disabled", true);
    $('#secondCode').attr('disabled', 'disabled');
    // 登录页 二次验证
    var url = ''
    if (action == 'login') {
        url = '/login/second_verify_send'
    } else {
        url = '/second_verify_send'
    }
    $.ajax({
        url: setting_web_url + url,
        type: 'POST',
        data: {
            action: action || '',
            type: $("#secondVerifyType").val(),
            username: username,
            password: password
        },
        dataType: 'json',
        success: function (data) {
           
            if (data.status == '200') {
                toastr.success(data.msg);
                setCutdown('#secondCode');
               
            } else {
                toastr.error(data.msg);
                $('#secondCode').removeAttr('disabled')
                $('#secondCode').removeData('disabled')
            }
        }
    });
}

// 登录页 二次验证
function loginSecondPage (username, password) {
    $.ajax({
        type: "get",
        url: setting_web_url + '/login/second_verify_page',
        data: {
            username: username,
            password: password
        },
        dataType: "json",
        success: function (data) {
            var secondeTypeHtml = ''
            data.data.allow_type.forEach(function (item) {
                secondeTypeHtml += '<option value="${item.name}" selected>${item.name_zh}: ${item.account}</option>'
            })
            $('#secondVerifyType').html(secondeTypeHtml)
        }
    });
}
