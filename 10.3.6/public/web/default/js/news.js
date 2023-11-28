$(function () {
    var params = {
        addon_idcsmart_news_type_id: '',
        page: 1, // 当前页数
        limit: 6
    }
    // 获取新闻分类
    function getTypelist() {
        $.ajax({
            url: "/console/v1/news/type",
            method: 'get',
            success: function (res) {
                const typeList = res.data.list
                typeList.forEach((item, index) => {
                    if (index === 0) {
                        $('#leftNews-title').text(item.name)
                        $('#leftNews-type').attr('href', `news-classify.html?id=${item.id}&title=${item.name}`)
                    } else {
                        $('#new-type-list').append(`
                            <div class="news-box bottom-news">
                                <div class="news-box-head fboxRow Xbetween">
                                    <div class="news-title">${item.name}</div>
                                    <a href="news-classify.html?id=${item.id}&title=${item.name}" class="font-grey">更多<span class="font18 iconfont icon-arrow-right"></span></a>
                                </div>
                                <div class="news-list" id='newstype${index}'>
                                </div>
                            </div>
                        `)

                    }
                    getTitleList(item.id, index)
                })
            }
        });
    }
    getTypelist()
    // 新闻列表
    function getTitleList(id, index) {
        params.addon_idcsmart_news_type_id = id
        if (index === 0) {
            params.limit = 6
        } else {
            params.limit = 3
        }
        $.ajax({
            url: "/console/v1/news",
            method: 'get',
            data: params,
            success: function (res) {
                const titleList = res.data.list
                if (index === 0) {
                    const firstNews = res.data.list[0]
                    const arr = res.data.list.slice(1)
                    if (firstNews) {
                        $('#first-news').attr('href', `news-details.html?id=${firstNews.id}`)
                        $('#first-news').append(`   
                            <div class="news-index-banner" style="background: url(${firstNews.img}) no-repeat; background-size: 100% 100%;">
                                <div class="news-banner-filter"></div>
                                <div class="news-banner-cont">
                                <h5 class="font18">${firstNews.title}</h5>
                                </div>
                            </div>
                         `)
                    }
                    arr.forEach((item, i) => {
                        $('#left-news').append(`      
                            <div class="news-item">
                                <div class="news-number">${i + 1}</div>
                                <a class="font-ell1" href="news-details.html?id=${item.id}">
                                    <span class="news-text link-hover">${item.title}</span>
                                    <span class="news-time">${formateTimeFun(item.create_time)}</span>
                                </a>
                            </div>
                        `)
                    })
                } else {
                    titleList.forEach((item, i) => {
                        $(`#newstype${index}`).append(`      
                            <div class="news-item">
                                <div class="news-number">${i + 1}</div>
                                <a class="font-ell1" href="news-details.html?id=${item.id}">
                                <span class="news-text link-hover">${item.title}</span>
                                <span class="news-time">${formateTimeFun(item.create_time)}</span>
                                </a>
                            </div>
                        `)
                    })

                }
            }
        });
    }

    function formateTimeFun(time) {
        const date = new Date(time * 1000);
        Y = date.getFullYear() + '年';
        M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '月';
        D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + '日';
        return (Y + M + D);
    }
})
