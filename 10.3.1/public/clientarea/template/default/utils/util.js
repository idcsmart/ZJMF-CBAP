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
    const date = new Date(time);
    Y = date.getFullYear() + '-';
    M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
    D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + ' ';
    h = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':';
    m = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes();
    return (Y + M + D + h + m);
};
/**
 * @param  timestamp 时间戳
 * @returns YY.MM.DD
 */
function formateDate1(time) {
    const date = new Date(time);
    Y = date.getFullYear() + '.';
    M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '.';
    D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + ' ';
    h = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':';
    m = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes();
    return (Y + M + D);
};
/**
 * @param  timestamp 时间戳
 * @returns YY年MM月DD日
 */
function formateDate2(time) {
    const date = new Date(time);
    Y = date.getFullYear() + '年';
    M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '月';
    D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + '日';
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

/**
 * 
 * @param num 需要处理的三分数字
 * @param fixed 保留小数位数
 * @param separator 货币分隔符
 * @returns 1,000.00
 */
function formatMoneyNumber(num, fixed = 2, separator = ',') {
    // 判断数字是否为 null 或非数字类型，并默认设置为 0.00
    if (num == null || isNaN(num)) {
        num = 0.00;
    }

    // 将数字转换为字符串，并将负号单独提取出来
    let str = String(Math.abs(Number(num)).toFixed(fixed));
    let [integer, decimal] = str.split('.');

    // 在整数部分添加千位分隔符
    let result = '';
    while (integer.length > 3) {
        result = separator + integer.slice(-3) + result;
        integer = integer.slice(0, -3);
    }
    result = integer + result;

    // 如果原始数字是负数，则在最终结果中添加负号
    if (num < 0) {
        result = '-' + result;
    }

    // 如果有小数部分，则在最终结果中添加小数点和小数部分
    if (decimal != null) {
        result += `.${decimal}`;
    }
    return result;
}

/**
 * 判断是否有某个插件
 * @param pluginName 插件名称 string
 * @returns Boolean
 */
function havePlugin(pluginName) {
    const addonsDom = document.querySelector('#addons_js')
    let addonsArr = [];
    let arr = [];
    if (addonsDom) {
        addonsArr = JSON.parse(addonsDom.getAttribute('addons_js')) // 插件列表
        arr = addonsArr.map((item) => {
            return item.name
        })
    }
    return arr.includes(pluginName)
}
/**
 * 根据插件名称获取插件Id
 * @param pluginName 插件名称 string
 * @returns id
 */
function getPluginId(pluginName) {
    const addonsDom = document.querySelector('#addons_js')
    if (addonsDom) {
        const addonsArr = JSON.parse(addonsDom.getAttribute('addons_js')) // 插件列表
        for (let index = 0; index < addonsArr.length; index++) {
            const element = addonsArr[index];
            if (pluginName === element.name) {
                return element.id
            }
        }

    } else {
        throw new Error('请检查页面是否有插件dom')
    }
}

/**
 * 获取url参数
 * @returns Object
 */
function getUrlParams() {
    const url = window.location.href;
    // 判断是否有参数
    if (url.indexOf('?') === -1) {
        return {}
    }
    const params = url.split('?')[1];
    const paramsArr = params.split('&');
    const paramsObj = {};
    paramsArr.forEach((item) => {
        const key = item.split('=')[0];
        const value = item.split('=')[1];
        paramsObj[key] = value;
    })
    return paramsObj;
}

