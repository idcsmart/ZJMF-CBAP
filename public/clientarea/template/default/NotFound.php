<!DOCTYPE html>
<html lang="en" theme-color="default">
<?php $template_catalog='clientarea';$themes=configuration('clientarea_theme');?>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <title></title>
    <!-- element 样式 -->
    <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
    <!-- 模板样式 -->
    <link rel="stylesheet" href="/<?php echo $template_catalog?>/template/<?php echo $themes?>/css/common/common.css">
    <link rel="stylesheet" href="/upload/common/iconfont/iconfont.css">
    <!-- 公共 -->
    <script>
        const url = "/<?php echo $template_catalog?>/template/<?php echo $themes?>/"
    </script>
    <script src="/<?php echo $template_catalog?>/template/<?php echo $themes?>/js/common/lang.js"></script>
    <script src="/<?php echo $template_catalog?>/template/<?php echo $themes?>/js/common/common.js"></script>
    <script src="/<?php echo $template_catalog?>/template/<?php echo $themes?>/js/common/layout.js"></script>
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/<?php echo $template_catalog?>/template/<?php echo $themes?>/css/NotFound.css">
</head>

<body>
    <div class="template">
        <el-container>
            <aside-menu></aside-menu>
            <el-container>
                <top-menu></top-menu>
                <el-main>
                    <!-- 自己的东西 -->
                    <div class="main-card">
                        <div class="content-box">
                            <div class="img-box">
                                <img src="/<?php echo $template_catalog?>/template/<?php echo $themes?>/img/common/404.png" alt="">
                            </div>
                            <div class="tips-box">
                                {{lang.status_text1}}
                            <p class="tran-again" @click="goBack">{{lang.status_text2}}</p>
                            </div>
                        </div>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/<?php echo $template_catalog?>/template/<?php echo $themes?>/api/common.js"></script>
    <script src="/<?php echo $template_catalog?>/template/<?php echo $themes?>/js/NotFound.js"></script>
    <script src="/<?php echo $template_catalog?>/template/<?php echo $themes?>/utils/util.js"></script>
    <script src="/<?php echo $template_catalog?>/template/<?php echo $themes?>/js/common/vue.js"></script>
    <script src="https://unpkg.com/element-ui/lib/index.js"></script>
    <script src="/<?php echo $template_catalog?>/template/<?php echo $themes?>/js/common/axios.min.js"></script>
    <script src="/<?php echo $template_catalog?>/template/<?php echo $themes?>/utils/request.js"></script>
    <script src="/<?php echo $template_catalog?>/template/<?php echo $themes?>/api/common.js"></script>
    <script src="/<?php echo $template_catalog?>/template/<?php echo $themes?>/components/asideMenu/aliAsideMenu.js"></script>
    <script src="/<?php echo $template_catalog?>/template/<?php echo $themes?>/components/asideMenu/asideMenu.js"></script>
    <script src="/<?php echo $template_catalog?>/template/<?php echo $themes?>/components/topMenu/topMenu.js"></script>
</body>

</html>