$(function () {
    // 提交
    function subConsult() {
        $.ajax({
            url: "/console/v1/consult",
            method: 'POST',
            data: {
                contact: $('#inputName').val(),
                company: $('#inputCompany').val(),
                phone: $('#inputPhone').val(),
                email: $('#inputEmail').val(),
                matter: $('#inputQuestion').val()
            },
            success: function (res) {
                showMessage('success', '提交成功！', 2000); // 显示 3 秒钟的成功消息
                $('#inputName').val("")
                $('#inputCompany').val("")
                $('#inputPhone').val("")
                $('#inputEmail').val("")
                $('#inputQuestion').val("")
            }
        });
    }

    function showMessage(type, message, duration) {
        const alertClass = 'alert-' + type;
        const html = '<div class="alert ' + alertClass + ' show alert-dismissible" role="alert">' +
            message +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span>' +
            '</button>' +
            '</div>';
        const $alert = $(html).appendTo('#alert-container');
        setTimeout(function () {
            $alert.alert('close');
        }, duration);
        // 清空表单
    }

    // 表单元素必填验证函数
    function validateRequired(input) {
        if (input.val().trim() === '') {
            input.attr('style', 'border: 1px solid #FF6739;')
            input.focus();
            return false;
        }
        input.attr('style', 'border: 1px solid #E6EAED;')
        return true;
    }

    $('form').submit(function (event) {
        event.preventDefault(); // 防止表单提交默认行为
        const name = $("#inputName")
        const company = $("#inputCompany")
        const phone = $("#inputPhone")
        const email = $("#inputEmail")
        const question = $("#inputQuestion")

        // 验证表单元素
        if (!validateRequired(name)) {
            return;
        }
        if (phone.val().trim().length === 0 && email.val().trim().length === 0) {
            phone.attr('style', 'border: 1px solid #FF6739;')
            phone.focus();
            return
        }
        if (phone.val().trim().length !== 0 || email.val().trim().length !== 0) {
            const validPhone = /^\d{11}$/.test(phone.val().trim());
            const validEmail = /^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$/.test(email.val().trim());
            if (phone.val().trim().length > 0 && !validPhone) {
                phone.attr('style', 'border: 1px solid #FF6739;')
                phone.focus();
                email.attr('style', 'border: 1px solid #E6EAED;')
                return
            }
            if (email.val().trim().length > 0 && !validEmail) {
                email.attr('style', 'border: 1px solid #FF6739;')
                email.focus();
                phone.attr('style', 'border: 1px solid #E6EAED;')
                return
            }
            email.attr('style', 'border: 1px solid #E6EAED;')
            phone.attr('style', 'border: 1px solid #E6EAED;')
        }
        if (!validateRequired(question)) {
            return;
        }
        subConsult()
    });

})