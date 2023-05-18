$(function () {
  // 获取列表
  function getHelpLlis () {
    $.ajax({
      url: "/console/v1/help",
      method: 'get',
      success: function (res) {
        res.data.list.forEach((item, index) => {
          $('#docement-list').append(`
                    <div class="document-box">
                        <div class="document-header">
                            <img src="./assets/img/document/group-${Math.ceil(Math.random() * 6)}.png" alt="">
                            <h5>${item.name}</h5>
                        </div>
                        <div class="document-cont mt-20" id="document-item${item.id}"></div>
                    </div>
                    `)
          if (item.helps.length !== 0) {
            item.helps.slice(0, 6).forEach((helps) => {
              $(`#document-item${item.id}`).append(`
                                <p class="font-el1"><a href="/plugin/26/helpTotal.html?id=${helps.id}">${helps.title}</a></p>
                            `)
            })
          }
        })
      }
    })
  }
  getHelpLlis()

  /* 文档搜索 */
  $('.banner-document .search-btn').click(function () {
    const val = $('#document-input').val()
    location.href = `document-result.html?search=${val}`
  })
})
