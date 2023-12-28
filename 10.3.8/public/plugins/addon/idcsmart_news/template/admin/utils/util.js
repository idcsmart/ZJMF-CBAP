// 防抖
debounce = (fn, ms) => {
  //fn:要防抖的函数 ms:时间
  let timerId;
  return function () {
    timerId && clearTimeout(timerId);

    timerId = setTimeout(() => {
      fn.apply(this, arguments);
    }, ms);
  };
};
