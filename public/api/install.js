// 环境检测
function step_1() {
    return installAxios.get('/install/install.php?action=step_1')
}
// 数据库检测
function step_2(params) {
    return installAxios.get('/install/install.php?action=step_2', {params})
}

// 网站配置
function step_3(params) {
    return installAxios.get('/install/install.php?action=step_3', {params})
}
// 写入config
function step_4() {
    return installAxios.get('/install/install.php?action=step_4')
}
// 数据库安装
function step_5() {
    return installAxios.get('/install/install.php?action=step_5')
}

// 写入数据
function step_6() {
    return installAxios.get('/install/install.php?action=step_6')
}
// 完成步骤检测
function step_7() {
    return installAxios.get('/install/install.php?action=step_7')
}

