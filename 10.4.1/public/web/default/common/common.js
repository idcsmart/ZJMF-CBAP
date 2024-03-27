// 消息弹窗
function showMessage(type, message, duration) {
  if ($("#alert-container").length <= 0) {
    $("body").append(`
    <div id="alert-container"></div>
    `);
  }
  const alertClass = "alert-" + type;
  const html =
    '<div class="alert ' +
    alertClass +
    ' show alert-dismissible" role="alert">' +
    message +
    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
    '<span aria-hidden="true">&times;</span>' +
    "</button>" +
    "</div>";
  const $alert = $(html).appendTo("#alert-container");
  setTimeout(function () {
    $alert.alert("close");
  }, duration);
  // 清空表单
}

$(function () {
  const headBgcList = [
    "#3699FF",
    "#57C3EA",
    "#5CC2D7",
    "#EF8BA2",
    "#C1DB81",
    "#F1978C",
    "#F08968",
  ];
  // 获取账户信息和实名认证信息
  const initData = () => {
    if (localStorage.jwt) {
      $.ajax({
        url: " /console/v1/account",
        method: "get",
        headers: {
          Authorization: "Bearer" + " " + localStorage.jwt,
        },
        success: function (res) {
          if (res.status === 200) {
            const obj = res.data.account;
            $(".no-login").attr("style", "display:none");
            $(".login-in").attr("style", "display:flex");
            $("#username").text(res.data.account.username);
            const reg = /^[a-zA-Z]+$/;
            if (reg.test(res.data.account.username.substring(0, 1))) {
              obj.firstName = res.data.account.username
                .substring(0, 1)
                .toUpperCase();
              $("#headImg").text(
                res.data.account.username.substring(0, 1).toUpperCase()
              );
            } else {
              obj.firstName = res.data.account.username.substring(0, 1);
              $("#headImg").text(res.data.account.username.substring(0, 1));
            }
            if (sessionStorage.headBgc) {
              $("#headImg").attr(
                "style",
                `background:${sessionStorage.headBgc}`
              );
            } else {
              const index = Math.round(
                Math.random() * (headBgcList.length - 1)
              );
              $("#headImg").attr("style", `background:${headBgcList[index]}`);
              sessionStorage.headBgc = headBgcList[index];
            }
            sessionStorage.accountInfo = JSON.stringify(obj);
          } else {
            $(".login-in").attr("style", "display:none");
            $(".no-login").attr("style", "display:block");
          }
        },
      });
      $.ajax({
        url: " /console/v1/certification/info",
        method: "get",
        headers: {
          Authorization: "Bearer" + " " + localStorage.jwt,
        },
        success: function (res) {
          if (res.status === 200) {
            if (res.data.is_certification) {
              $("#isCertification").attr("style", "display:inline-block");
              $("#noCertification").attr("style", "display:none");
              sessionStorage.is_certification = true;
            } else {
              $("#isCertification").attr("style", "display:none");
              $("#noCertification").attr("style", "display:inline-block");
              sessionStorage.is_certification = false;
            }
          } else {
            $(".login-in").attr("style", "display:none");
            $(".no-login").attr("style", "display:block");
          }
        },
      });
    } else {
      $(".login-in").attr("style", "display:none");
      $(".no-login").attr("style", "display:block");
    }
  };
  // 获取通用配置信息
  const getCommentInfo = () => {
    $.ajax({
      url: "/console/v1/common",
      method: "get",
      headers: {
        Authorization: "Bearer" + " " + localStorage.jwt,
      },
      success: function (res) {
        sessionStorage.commentData = JSON.stringify(res.data);
        setCommData();
      },
    });
  };
  // 设置通用信息函数
  const setCommData = () => {
    const commentObj = JSON.parse(sessionStorage.commentData);
    $("#enterprise_name").text(commentObj.enterprise_name);
    $(".com-contact-tel").text(commentObj.enterprise_telephone);
    $("#enterprise_telephone").text(
      `联系电话：${commentObj.enterprise_telephone}`
    );
    $("#enterprise_mailbox").text(`联系邮箱：${commentObj.enterprise_mailbox}`);
    $("#enterprise_qrcode").attr("src", commentObj.enterprise_qrcode);
    $("#logo").attr("src", commentObj.official_website_logo);
    if (commentObj.friendly_link.length > 0) {
      $("#index #footerLink").attr("style", "display: block;");
      commentObj.friendly_link.forEach((item) => {
        $("#footerLink").append(
          `<a href=${item.url} target="_blank" rel="nofollow">${item.name}</a>`
        );
      });
    } else {
      $("#footerLink").attr("style", "display: none;");
    }
    $("#footerRecord").prepend(`
     <div class='left-info'>
      <a href='${commentObj.icp_info_link}' target="_blank" rel="nofollow">${commentObj.icp_info}</a>
      <a href='${commentObj.public_security_network_preparation_link}' target="_blank" rel="nofollow">${commentObj.public_security_network_preparation}</a>
      <span>${commentObj.telecom_appreciation}</span>
     </div>
    `);
    $("#copyright_info").text(commentObj.copyright_info);
    $("#terms_service_url").click(function () {
      location.href = commentObj.terms_service_url;
    });
    $("#terms_privacy_url").click(function () {
      location.href = commentObj.terms_privacy_url;
    });

    $(".line-server-btn").click(function () {
      window.open(commentObj.online_customer_service_link);
    });
    $(".buy-cloud").click(function () {
      location.href = commentObj.cloud_product_link;
    });
    $(".buy-dcim-btn").click(function () {
      location.href = commentObj.dcim_product_link;
    });
  };
  // 跳转函数
  const goOtherPage = (url) => {
    sessionStorage.redirectUrl = location.href;
    location.href = url;
  };
  function initHeader() {
    let showIndex = 0;
    $(".nav-menu .nav-item").hover(
      function () {
        const index = $(".nav-menu .nav-item").index($(this));
        $(".nav-cont .nav-cont-menu")
          .eq(index)
          .attr("style", "display: block;");

        // $('.nav-cont').attr('style','display: block;')
        if (index != 0) {
          const height = $(".nav-cont .nav-cont-menu").eq(index).height();
          $(".nav-cont").attr("style", "height: 440px;");
        }
        showIndex = index;
      },
      function () {
        const index = $(".nav-menu .nav-item").index($(this));
        $(".nav-cont ").eq(index).attr("style", "display: none;");
        $(".nav-cont .nav-cont-menu").eq(index).attr("style", "display: none;");
        $(".nav-cont").attr("style", "height:0");
      }
    );

    $(".nav-cont").hover(
      function () {
        //$('.nav-cont ').attr('style','display: block;')
        $(".nav-cont .nav-cont-menu")
          .eq(showIndex)
          .attr("style", "display: block;");
        if (showIndex != 0) {
          const height = $(".nav-cont .nav-cont-menu").eq(showIndex).height();
          $(".nav-cont").attr("style", "height: 440px;");
        }
      },
      function () {
        //$('.nav-cont ').attr('style','display: none;')
        $(".nav-cont .nav-cont-menu")
          .eq(showIndex)
          .attr("style", "display: none;");
        $(".nav-cont").attr("style", "height:0");
      }
    );
    if (localStorage.jwt) {
      if (sessionStorage.accountInfo) {
        const obj = JSON.parse(sessionStorage.accountInfo);
        $(".no-login").attr("style", "display:none");
        $(".login-in").attr("style", "display:flex");
        $("#username").text(obj.username);
        $("#headImg").text(obj.firstName);
        $("#headImg").attr("style", `background:${sessionStorage.headBgc}`);
        if (sessionStorage.is_certification == true) {
          $("#isCertification").attr("style", "display:inline-block");
          $("#noCertification").attr("style", "display:none");
        } else {
          $("#isCertification").attr("style", "display:none");
          $("#noCertification").attr("style", "display:inline-block");
        }
      }
      initData();
    } else {
      $(".login-in").attr("style", "display:none");
      $(".no-login").attr("style", "display:block");
    }
    // 退出登录
    $("#logout").click(function () {
      localStorage.removeItem("jwt");
      initData();
    });
    // 点击登录
    $("#loginBtn").click(function () {
      goOtherPage("/login.htm");
    });
    // 点击注册
    $("#registBtn").click(function () {
      goOtherPage("/regist.htm");
    });
    // 点击账户信息
    $("#accountBtn").click(function () {
      location.href = "/account.htm";
    });
    // 未付款订单
    $("#financeBtn").click(function () {
      location.href = "/finance.htm";
    });
    // 我的工单
    $("#ticketBtn").click(function () {
      location.href = "/plugin/27/ticket.htm";
    });
    // 购物车
    $("#shopping-cart").click(function () {
      location.href = "/cart/shoppingCar.htm";
    });
  }
  function initFooter() {
    if (!sessionStorage.commentData) {
      getCommentInfo();
    } else {
      setCommData();
    }
  }
  // 首页渲染
  $("#header").load("/web/default/public/header.html", function () {
    initHeader();
  });
  // 底部渲染
  $("#footer").load("/web/default/public/footer.html", function () {
    initFooter();
  });

  const resize = function () {
    const width = $(window).width();
    const num = width / 1400;
    if (1000 < width && width < 1440) {
      $("section").attr(
        "style",
        "width:1400px;transform: scaleX(" + num + ");transform-origin: 0 0;"
      );
    } else {
      $("section").attr("style", "");
    }
  };
  resize();
  window.addEventListener("resize", resize);

  $(".input-search-s ").click(function () {
    $(".input-search-select  .select-box").toggle();
  });

  $(".input-search-r").click(function () {
    $(".input-search-select  .select-box").toggle();
  });

  $(".input-search-select").on("click", ".select-box-item", function () {
    $(".input-search-text").text($(this).text());
    $(".input-search-select  .select-box").toggle();
  });

  /* 招聘tab切换 */
  $(".recuit-btn-group a").each(function (ind, el) {
    $(el).click(function () {
      $(this).addClass("active").siblings().removeClass("active");
      $(".recuit-content .recuit-box").eq(ind).show().siblings().hide();
    });
  });
  /* 常见问题toggle */
  $(".agent-question .item").eq(0).addClass("active");
  $(".agent-question .item").eq(0).find(".des").slideDown(300);
  $(".agent-question .item").each(function (ind, el) {
    $(el)
      .find(".open")
      .click(function () {
        $(this).parent(".title").parent(".item").find(".des").slideDown(300);
        $(this)
          .parent(".title")
          .parent(".item")
          .addClass("active")
          .siblings()
          .removeClass("active")
          .find(".des")
          .slideUp(300);
      });
    $(el)
      .find(".down")
      .click(function () {
        $(this)
          .parent(".des")
          .slideUp(300)
          .parent(".item")
          .removeClass("active");
      });
  });

  $(".pro-tip-btn").click(function () {
    showMessage("warning", "正在优化功能，敬请期待!", 3000); // 显示 3 秒钟的成功消息
  });

  $("#documentBtn").click(function () {
    location.href = "./document.html";
  });
  $(".go-ticket-btn").click(function () {
    location.href = "/plugin/27/ticket.htm";
  });
});
