$(function() {
  const addons_js_arr = JSON.parse(
    document.querySelector("#addons_js").getAttribute("addons_js")
  );
  const arr = addons_js_arr.map((item) => {
    return item.name;
  });

  // 是否有域名插件
  const hasDomain = arr.includes("IdcsmartDomain");
  let jumpUrl = "";
  if (hasDomain) {
    // 默认配置
    $.ajax({
      url: "/console/v1/idcsmart_domain/config",
      method: "get",
      headers: {
        Authorization: "Bearer" + " " + localStorage.jwt,
      },
      success: function (res) {
        if (res.status === 200) {
          const suffix = res.data.default_search_domain || '.com';
          jumpUrl = res.data.domain_url;
          $('#default-suffix').html(suffix);
          $('.domain-url').attr('href', jumpUrl);
        }
      },
    });
    // 后缀
    $.ajax({
      url: "/console/v1/idcsmart_domain/domain_suffix",
      method: "get",
      headers: {
        Authorization: "Bearer" + " " + localStorage.jwt,
      },
      success: function (res) {
        if (res.status === 200) {
          const arr = res.data;
          arr.forEach((item, index) => {
            $("#suffix-box").append(
              `
               <div class="select-box-item">${item.suffix}</div>
              `
            )
          })
          $('#suffix-box .select-box-item').each((ind, el) => {
            $(el).removeClass('active');
            if($(el).text() === $('#default-suffix').text()){
              $(el).addClass('active')
            }
            $(el).click(function () {
              $(this).addClass('active').siblings().removeClass('active');
            })
          })
        } else {
        }
      },
    });
  } else {
    $('#default-suffix').html('.com');
    ['.com','.cn'].forEach((item, index) => {
      $("#suffix-box").append(
        `
         <div class="select-box-item">${item}</div>
        `
      )
    })
  }
  $('.search-btn').click(function() {
    if (hasDomain) {
      const domain = $('#domain-input').val();
      const suffix = $('#default-suffix').text();
      if (!domain) {
        return;
      }
      window.open(`${jumpUrl}&domain=${domain}&suffix=${suffix}`)
    } else {
      showMessage('error', '功能暂未开放！', 2000);
    }
  })

})
