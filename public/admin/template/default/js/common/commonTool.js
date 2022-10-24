// 处理数字千分法
function thousandth(num) {
    let str = num.toString() // 数字转字符串
    let str2 = null
    // 如果带小数点
    if (str.indexOf('.') !== -1) { // 带小数点只需要处理小数点左边的
        const strArr = str.split('.') // 根据小数点切割字符串
        str = strArr[0] // 小数点左边
        str2 = strArr[1] // 小数点右边
        //如12345.678  str=12345，str2=678
    }
    let result = '' // 结果
    while (str.length > 3) { // while循环 字符串长度大于3就得添加千分位
        // 切割法 ，从后往前切割字符串 ⬇️
        result = ',' + str.slice(str.length - 3, str.length) + result
        // 切割str最后三位，用逗号拼接 比如12345 切割为 ,345
        // 用result接收，并拼接上一次循环得到的result
        str = str.slice(0, str.length - 3) // str字符串剥离上面切割的后三位，比如 12345 剥离成 12
    }

    if (str.length <= 3 && str.length > 0) {
        // 长度小于等于3 且长度大于0，直接拼接到result
        // 为什么可以等于3 因为上面result 拼接时候在前面带上了‘,’
        // 相当于123456 上一步处理完之后 result=',456' str='123'
        result = str + result
    }
    // 最后判断是否带小数点（str2是小数点右边的数字）
    // 如果带了小数点就拼接小数点右边的str2 ⬇️
    str2 ? result = result + '.' + str2 : ''
    return result
}