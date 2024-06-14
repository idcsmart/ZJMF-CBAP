// 处理数字千分法
function thousandth(num) {
  if (!num) {
    num = 0.0;
  }
  let str = num.toString(); // 数字转字符串
  let str2 = null;
  // 如果带小数点
  if (str.indexOf(".") !== -1) {
    // 带小数点只需要处理小数点左边的
    const strArr = str.split("."); // 根据小数点切割字符串
    str = strArr[0]; // 小数点左边
    str2 = strArr[1]; // 小数点右边
    //如12345.678  str=12345，str2=678
  }
  let result = ""; // 结果
  while (str.length > 3) {
    // while循环 字符串长度大于3就得添加千分位
    // 切割法 ，从后往前切割字符串 ⬇️
    result = "," + str.slice(str.length - 3, str.length) + result;
    // 切割str最后三位，用逗号拼接 比如12345 切割为 ,345
    // 用result接收，并拼接上一次循环得到的result
    str = str.slice(0, str.length - 3); // str字符串剥离上面切割的后三位，比如 12345 剥离成 12
  }

  if (str.length <= 3 && str.length > 0) {
    // 长度小于等于3 且长度大于0，直接拼接到result
    // 为什么可以等于3 因为上面result 拼接时候在前面带上了‘,’
    // 相当于123456 上一步处理完之后 result=',456' str='123'
    result = str + result;
  }
  // 最后判断是否带小数点（str2是小数点右边的数字）
  // 如果带了小数点就拼接小数点右边的str2 ⬇️
  str2 ? (result = result + "." + str2) : "";
  return result;
}

// 解析url
// locationSearch:location.search   return:res:{id:xxx}
function getQuery(locationSearch) {
  const url = window.location.href;
  // 判断是否有参数
  if (url.indexOf("?") === -1) {
    return {};
  }
  const params = url.split("?")[1];
  const paramsArr = params.split("&");
  const paramsObj = {};
  paramsArr.forEach((item) => {
    const key = item.split("=")[0];
    const value = item.split("=")[1];
    // 解析中文
    paramsObj[key] = decodeURIComponent(value);
  });
  return paramsObj;
}

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
  let m = "";
  if (hasNum == 0 && hasChar == 0 && hasSymbol == 0) return m;
  for (let i = length; i >= 0; i--) {
    let num = Math.floor(Math.random() * 94 + 33);
    if (
      (hasNum == 0 && num >= 48 && num <= 57) ||
      (hasChar == 0 &&
        ((num >= 65 && num <= 90) || (num >= 97 && num <= 122))) ||
      (hasSymbol == 0 &&
        ((num >= 33 && num <= 47) ||
          (num >= 58 && num <= 64) ||
          (num >= 91 && num <= 96) ||
          (num >= 123 && num <= 127)))
    ) {
      i++;
      continue;
    }
    m += String.fromCharCode(num);
  }
  if (caseSense == "0") {
    m = lowerCase == "0" ? m.toUpperCase() : m.toLowerCase();
  }
  return m;
}

/**
 *
 * @param {Number} n 返回n个随机字母字符串
 * @returns
 */
function randomCoding(n) {
  //创建26个字母数组
  const arr = [
    "A",
    "B",
    "C",
    "D",
    "E",
    "F",
    "G",
    "H",
    "I",
    "J",
    "K",
    "L",
    "M",
    "N",
    "O",
    "P",
    "Q",
    "R",
    "S",
    "T",
    "U",
    "V",
    "W",
    "X",
    "Y",
    "Z",
  ];
  let idvalue = "";
  for (let i = 0; i < n; i++) {
    idvalue += arr[Math.floor(Math.random() * 26)];
  }
  return idvalue;
}
