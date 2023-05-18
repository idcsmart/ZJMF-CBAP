// // 获取系统版本
// function version() {
//     return Axios.get('/system/version')
// }
// // 获取更新内容
// function content() {
//     return Axios.get('/system/upgrade_content')
// }

// // 开始更新
// function update1() {
//     return Axios.get('/system/upgrade_download')
// }
// // 获取下载进度
// function progress1() {
//     return Axios.get('/system/upgrade_download_progress')
// }


function update() {
    return installAxios.get('/upgrade/upgrade.php?action=upgrade')
}

// 获取下载进度
function progress() {
    return installAxios.get('/upgrade/upgrade.php?action=progress')
}

