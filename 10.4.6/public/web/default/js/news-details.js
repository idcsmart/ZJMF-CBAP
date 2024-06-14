$(function () {
  let url = window.location.href;
  let getqyinfo = url.split("?")[1];
  let getqys = new URLSearchParams("?" + getqyinfo);
  const id = getqys.get("id");
  var news = {};
  function decodeHTML(html) {
    var doc = new DOMParser().parseFromString(html, "text/html");
    return doc.documentElement.textContent;
  }
  // 获取新闻详情
  function getDetail() {
    $.ajax({
      url: `/console/v1/news/${id}`,
      method: "get",
      success: function (res) {
        news = res.data.news;
        $("#announce-name").text(`${news.title}`);
        $(".announce-title").text(`${news.title}`);
        $("#announce-type").text(`${news.type}`);
        $("#announce-type").attr(
          "href",
          `./news-classify.html?id=${news.addon_idcsmart_news_type_id}&title=${news.type}`
        );
        $(".announce-details-time").text(`${formateTimeFun(news.create_time)}`);
        // $('.announce-details-cont').html(decodeHTML(`${news.content.replace(/amp;/g, '')}`))
        $(".announce-details-cont").html(news.content);
        if (news.prev?.id) {
          $("#page-box").append(`
                     <div class="announce-details-page">上一篇：<a href="./news-details.html?id=${news.prev?.id}">${news.prev?.title}</a></div>
                    `);
        }
        if (news.next?.id) {
          $("#page-box").append(`
                     <div class="announce-details-page">下一篇：<a href="./news-details.html?id=${news.next?.id}">${news.next?.title}</a></div>
                    `);
        }
      },
    });
  }
  getDetail();
  getNewList();
  function getNewList() {
    $.ajax({
      url: "/console/v1/news",
      method: "get",
      data: {
        addon_idcsmart_news_type_id: "",
        page: 1, // 当前页数
        limit: 5,
      },
      success: function (res) {
        const titleList = res.data.list;
        titleList.forEach((item, i) => {
          $(`#newsBox`).append(`
                        <div class="news-item">
                            <div class="news-number">${i + 1}</div>
                            <a class="font-ell1 link-hover" href="news-details.html?id=${
                              item.id
                            }">${item.title}</a>
                        </div>
                    `);
        });
      },
    });
  }

  function formateTimeFun(time) {
    const date = new Date(time * 1000);
    Y = date.getFullYear() + "-";
    M =
      (date.getMonth() + 1 < 10
        ? "0" + (date.getMonth() + 1)
        : date.getMonth() + 1) + "-";
    D = (date.getDate() < 10 ? "0" + date.getDate() : date.getDate()) + "";
    return Y + M + D;
  }
});
