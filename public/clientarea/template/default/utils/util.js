//获取cookie、
function getCookie(name) {
    var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
    if (arr = document.cookie.match(reg))
        return (arr[2]);
    else
        return null;
}

//设置cookie
function setCookie(c_name, value, expiredays) {
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + expiredays);
    document.cookie = c_name + "=" + escape(value) + ((expiredays == null) ? "" : ";expires=" + exdate.toGMTString());
};

//删除cookie
function delCookie(name) {
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval = getCookie(name);
    if (cval != null)
        document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
};


// 时间戳转换
/**
 * @param  timestamp 时间戳
 * @returns YY-MM-DD hh:mm
 */
function formateDate(time) {
    var date = new Date(time);
    Y = date.getFullYear() + '-';
    M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
    D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + ' ';
    h = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':';
    m = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes();
    return (Y + M + D + h + m);
};
function formateDate1(time) {
  var date = new Date(time);
  Y = date.getFullYear() + '.';
  M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '.';
  D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + ' ';
  h = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':';
  m = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes();
  return (Y + M + D);
};
/**
 * 生成密码字符串
 * 33~47：!~/
 * 48~57：0~9
 * 58~64：:~@
 * 65~90：A~Z
 * 91~96：[~`
 * 97~122：a~z
 * 123~127：{~
 * @param length 长度  生成的长度是length
 * @param hasNum 是否包含数字 1-包含 0-不包含
 * @param hasChar 是否包含字母 1-包含 0-不包含
 * @param hasSymbol 是否包含其他符号 1-包含 0-不包含
 * @param caseSense 是否大小写敏感 1-敏感 0-不敏感
 * @param lowerCase 是否只需要小写，只有当hasChar为0且caseSense为1时起作用 1-全部小写 0-全部大写
 */


function genEnCode(length, hasNum, hasChar, hasSymbol, caseSense, lowerCase) {
    var m = ''
    if (hasNum == 0 && hasChar == 0 && hasSymbol == 0) return m
    for (var i = length; i >= 0; i--) {
        var num = Math.floor((Math.random() * 94) + 33)
        if (
            (
                (hasNum == 0) && ((num >= 48) && (num <= 57))
            ) || (
                (hasChar == 0) && ((
                    (num >= 65) && (num <= 90)
                ) || (
                        (num >= 97) && (num <= 122)
                    ))
            ) || (
                (hasSymbol == 0) && ((
                    (num >= 33) && (num <= 47)
                ) || (
                        (num >= 58) && (num <= 64)
                    ) || (
                        (num >= 91) && (num <= 96)
                    ) || (
                        (num >= 123) && (num <= 127)
                    ))
            )
        ) {
            i++
            continue
        }
        m += String.fromCharCode(num)
    }
    if (caseSense == '0') {
        m = (lowerCase == '0') ? m.toUpperCase() : m.toLowerCase()
    }
    return m
}

/**
 * 
 * @param {Number} n 返回n个随机字母字符串
 * @returns 
 */
function randomCoding(n) {
    //创建26个字母数组
    var arr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
    var idvalue = '';

    for (var i = 0; i < n; i++) {
        idvalue += arr[Math.floor(Math.random() * 26)];
    }
    return idvalue;
}

/**
 * 
 * @param fn 执行的函数
 * @param delay 防抖时间毫秒
 * @returns 
 */
function debounce(fn, delay) {
    var timeout = null;
    return function () {
        if (timeout) {
            clearTimeout(timeout);
        }
        timeout = setTimeout(fn, delay)
    }
}


