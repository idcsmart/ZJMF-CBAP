$(function () {
  // 设置首页函数
  function setIndexData() {
    const commentObj = JSON.parse(sessionStorage.commentData)
    if (commentObj.honor.length > 0) {
      commentObj.honor.forEach((item) => {
        $('#certBox').append(`<div class="cert-item">
          <img src=${item.img} alt="">
          <p class="mt-20">${item.name}</p>
        </div>`)
      })
    }
    if (commentObj.partner.length > 0 && commentObj.partner.length <= 3) {
      commentObj.partner.forEach((item) => {
        $('#practiceBox').append(`<div class="practice-box">
      <img src="${item.img}" alt="">
      <div class="mt-10">${item.description}</div>
      <p class="tr font-grey mt-20 font12">${item.name}</p>
      </div>`)
      })
    } else if (commentObj.partner.length > 3) {
      const arr1 = commentObj.partner.slice(0, 3)
      const arr2 = commentObj.partner.slice(3)
      arr1.forEach((item) => {
        $('#practiceBox').append(`<div class="practice-box">
      <img src="${item.img}" alt="">
      <div class="mt-10">${item.description}</div>
      <p class="tr font-grey mt-20 font12">${item.name}</p>
      </div>`)
      })
      $('#morPracticeBox').attr('style', 'display: flex;')
      arr2.forEach((item) => {
        $('#morPracticeBox').append(` <div class="brand-box">
        <img src="${item.img}" alt="">
      </div>`)
      })
    }
  }
  // 获取通用配置信息
  function getCommentInfo() {
    $.ajax({
      url: "/console/v1/common",
      method: 'get',
      headers: {
        'Authorization': "Bearer" + " " + localStorage.jwt
      },
      success: function (res) {
        sessionStorage.commentData = JSON.stringify(res.data)
        setIndexData()
      }
    });
  }
  // 获取首页数据
  getCommentInfo()

  const mySwiper = new Swiper('.swiper', {
    loop: true, // 循环模式选项
    autoplay: true,
    // 如果需要分页器
    pagination: {
      el: '.swiper-pagination',
      clickable: true,
    },

  })
  function formateTimeFun(time) {
    const date = new Date(time * 1000);
    Y = date.getFullYear() + '-';
    M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
    D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate());
    return (Y + M + D);
  }
  // 公告列表
  function getAnnounceList() {
    $.ajax({
      url: "/console/v1/announcement",
      method: 'get',
      data: {
        page: 1,
        limit: 5
      },
      success: function (res) {
        const announceList = res.data.list
        announceList.forEach((item, index) => {
          $('#announceList').append(`      
          <div class="news-item">
          <div class="fboxRow Ycenter">
            <div class="number">${index + 1}</div>
            <a href="./announce-details.html?id=${item.id}">
              <div class="title font-ell1">${item.title}</div>
            </a>
          </div>
          <div class="time">${formateTimeFun(item.create_time)}</div>
        </div>
           `)
        })
      }
    });
  }
  getAnnounceList()
  // 新闻列表
  function getNewsList() {
    $.ajax({
      url: "/console/v1/news",
      method: 'get',
      data: {
        page: 1,
        limit: 5
      },
      success: function (res) {
        const announceList = res.data.list
        announceList.forEach((item, index) => {
          $('#newsList').append(`      
          <div class="news-item">
          <div class="fboxRow Ycenter">
            <div class="number">${index + 1}</div>
            <a href="./news-details.html?id=${item.id}">
              <div class="title font-ell1">${item.title}</div>
            </a>
          </div>
          <div class="time">${formateTimeFun(item.create_time)}</div>
        </div>
           `)
        })
      }
    });
  }
  getNewsList()

  $('#myTabs a').click(function (e) {
    e.preventDefault()
    $(this).tab('show')
  })
  // 跳转函数
  function goOtherPage(url) {
    location.href = url
  }
  $('#cloud-box').click(function () {
    location.href = 'cloud.html'
  })
  $('#domain-box').click(function () {
    location.href = 'domain.html'
  })
  $('#recomment-box').click(function () {
    location.href = '/home.htm'
  })
  $('#logon-box').click(function () {
    location.href = '/regist.htm'
  })
  $('#cps-box').click(function () {
    location.href = 'partner/cps.html'
  })
})