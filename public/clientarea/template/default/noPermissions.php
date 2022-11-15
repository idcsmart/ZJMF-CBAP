{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/noPermissions.css">
</head>

<body>
    <!-- mounted之前显示 -->
    <div id="mainLoading">
        <div class="ddr ddr1"></div>
        <div class="ddr ddr2"></div>
        <div class="ddr ddr3"></div>
        <div class="ddr ddr4"></div>
        <div class="ddr ddr5"></div>
    </div>
    <div class="template">
        <el-container>
            <aside-menu></aside-menu>
            <el-container>
                <top-menu></top-menu>
                <el-main>
                    <div class="box">
                      <!-- 自己的东西 -->
                      <img 
                        src="/{$template_catalog}/template/{$themes}/img/common/no_permission.png" 
                      alt="">
                      <p>你没有权限访问此页面</p>
                      <p>你可以通知管理员授予权限 <el-button style="margin-left:0.16rem" type="text" @click="back">返回</el-button></p>
                      <!-- <p> </p> -->
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>  
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/js/noPermissions.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}