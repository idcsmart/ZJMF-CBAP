$(function () {
  // 搜索结果页
  function getSearchList (keyword) {
    const val = keyword
    $('#result-input').val(val)
    $('.keyword').text(`“${val}”`)
    $.ajax({
      url: `/console/v1/help?keywords=${val}`,
      method: 'get',
      success: function (res) {
        const temp = res.data.list.reduce((all, cur) => {
          const helps = cur.helps.filter(fil => fil.search)
          all.push(...helps)
          return all
        }, [])
        $('.search-result').html('')
        if (temp.length > 0) {
          temp.forEach((item, index) => {
            $('.keyword-box').show()
            $('.search-result').append(
              `
              <div class="document-details-list">
                <div class="document-details-item">
                  <a href="/plugin/26/helpTotal.htm?id=${item.id}">
                    <h5 class="font16">${item.title}</h5>
                  </a>
                  <p>
                    ${item.des || ''}
                  </p>
                  <div class="mt-10"><span class="font-grey">来自:</span> <span>产品 > 帮助中心</span></div>
                </div>
              </div>
              `
            )
          })
        } else {
          $('.search-result').append(
            `
            <div class="common-empty">
              <img src="./assets/img/empty/empty_08.png" alt="">
              <p class="des">抱歉，没有搜索到，请重新搜索</p>
            </div>
            `
          )
          $('.keyword-box').hide()
        }
      }
    })
  }
  getSearchList(decodeURI(location.search.split('?')[1]?.split('=')[1]));
  $('#search-btn').click(function () {
    const keyword = $('#result-input').val()
    getSearchList(keyword)
  })

})
