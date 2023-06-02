$(function () {
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

    // 设置首页函数
    function setIndexData() {
        const commentObj = JSON.parse(sessionStorage.commentData)
        if (commentObj.honor.length > 0) {
            commentObj.honor.forEach((item) => {
                $('#honor-box').append(`
                    <div class="box-item">
                        <img src=${item.img} alt="">
                        <p class="font-16 mt-20">${item.name}</p>
                    </div>
                `)
            })
        }
        commentObj.partner.forEach((item) => {
            $('#partner-box').append(`      
                <div class="box-item">
                    <img src="${item.img}" alt="">
                    <h4 class="mt-30">${item.name}</h4>
                    <p class="mt-20">${item.description}</p>
                </div>
             `)
        })
    }
})
