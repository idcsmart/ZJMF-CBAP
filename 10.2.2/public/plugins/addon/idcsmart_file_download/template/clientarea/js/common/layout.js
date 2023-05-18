(function () {
    // 判断登录
    if (!localStorage.getItem('jwt')) {
        location.href = 'login.html'
    }
})();