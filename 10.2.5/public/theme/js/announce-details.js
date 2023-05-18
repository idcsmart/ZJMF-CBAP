$(function () {
    let url = window.location.href
    let getqyinfo = url.split('?')[1]
    let getqys = new URLSearchParams('?' + getqyinfo)
    const id = getqys.get('id')
    var announcementData
    // 获取公告详情
    function getDetail() {
        $.ajax({
            url: `/console/v1/announcement/${id}`,
            method: 'get',
            success: function (res) {
                announcementData = res.data.announcement
                $('#announce-name').text(`${announcementData.title}`)
                $('#announce-type').text(`${announcementData.type}`)
                $('#announce-type').attr('href', `./announce.html?id=${announcementData.addon_idcsmart_announcement_type_id}`)
                $('.announce-title').text(`${announcementData.title}`)
                $('.announce-details-time').text(`${formateTimeFun(announcementData.create_time)}`)
                $('.announce-details-cont').html(`${announcementData.content}`)
                if (announcementData.prev?.id) {
                    $('#nextAnnounce').append(` 
                     <div class="announce-details-page">上一篇：<a href="./announce-details.html?id=${announcementData.prev?.id}">${announcementData.prev?.title}</a></div>
                    `)
                } else {
                    $('#nextAnnounce').append(` 
                    <div class="announce-details-page"></div>
                   `)
                }
                if (announcementData.next?.id) {
                    $('#nextAnnounce').append(` 
                     <div class="announce-details-page">下一篇：<a href="./announce-details.html?id=${announcementData.next?.id}">${announcementData.next?.title}</a></div>
                    `)
                }
            }
        });
    }
    getDetail()



    function formateTimeFun(time) {
        const date = new Date(time * 1000);
        Y = date.getFullYear() + '-';
        M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
        D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + '';
        return (Y + M + D);
    }
})
