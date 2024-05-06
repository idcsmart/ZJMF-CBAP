$(function () {
    let url = window.location.href
    let getqyinfo = url.split('?')[1]
    let getqys = new URLSearchParams('?' + getqyinfo)
    const id = getqys.get('id')
    const title = getqys.get('title')
    $('#newType').text(title)
    $('#newsText').text(title)
    var params = {
        addon_idcsmart_news_type_id: id,
        page: 1, // 当前页数
        limit: 10 // 一页几条
    }
    var totalPages = 0;  // 总页数
    var visiblePages = 5; // 显示的页码数
    var pagination = $('.pagination');



    // 新闻列表
    function getTitleList() {
        $.ajax({
            url: "/console/v1/news",
            method: 'get',
            data: params,
            success: function (res) {
                const titleList = res.data.list
                $('#totalText').text(`共${res.data.count}项数据`)
                totalPages = Math.ceil(res.data.count / params.limit)
                $('.announce-list').empty()
                titleList.forEach((item) => {
                    $('.announce-list').append(`      
                        <div class="announce-item">
                        <p class="announce-item-title font-ell1"><a href="news-details.html?id=${item.id}">${item.title}</a></p>
                        <p class="announce-item-time">${formateTimeFun(item.create_time)}</p>
                        </div>
                     `)
                })
                renderPagination();
            }
        });
    }
    getTitleList()
    // 渲染分页器
    function renderPagination() {
        // 清空分页器
        pagination.empty();
        // 添加上一页按钮
        pagination.append('<li><a href="javascript:;" aria-label="Previous"><span aria-hidden="true">&lt;</span></a></li>');

        // 添加第一页
        pagination.append('<li><a href="javascript:;" class="page-number">1</a></li>');
        // 添加省略号
        if (params.page > visiblePages) {
            pagination.append('<li><a href="javascript:;" class="page-ellipsis">...</a></li>');
        }

        // 添加中间的页码
        for (let i = params.page - Math.floor(visiblePages / 2); i <= params.page + Math.floor(visiblePages / 2); i++) {
            if (i > 1 && i < totalPages) {
                pagination.append('<li><a href="javascript:;" class="page-number">' + i + '</a></li>');
            }
        }
        // 添加省略号
        if (params.page < totalPages - visiblePages + 1) {
            pagination.append('<li><a href="javascript:;" class="page-ellipsis">...</a></li>');
        }
        if (totalPages > 1) {
            // 添加最后一页
            pagination.append('<li><a href="javascript:;" class="page-number">' + totalPages + '</a></li>');
        }
        // 添加下一页按钮
        pagination.append('<li><a href="javascript:;" aria-label="Next"><span aria-hidden="true">&gt;</span></a></li>');
        // Add active class to current page
        pagination.find('.page-number').removeClass('active');
        pagination.find('.page-number').filter(function () {
            return parseInt($(this).text()) == params.page;
        }).addClass('active');
    }

    // 点击页码时切换页面
    pagination.on('click', '.page-number', function () {
        params.page = parseInt($(this).text());
        getTitleList()
    });

    // 点击上一页按钮时切换到上一页
    pagination.on('click', '[aria-label="Previous"]', function () {
        if (params.page > 1) {
            params.page--;
            getTitleList()

        }
    });

    // 点击下一页按钮时切换到下一页
    pagination.on('click', '[aria-label="Next"]', function () {
        if (params.page < totalPages) {
            params.page++;
            getTitleList()
        }
    });

    function formateTimeFun(time) {
        const date = new Date(time * 1000);
        Y = date.getFullYear() + '年';
        M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '月';
        D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + '日';
        return (Y + M + D);
    }
})
