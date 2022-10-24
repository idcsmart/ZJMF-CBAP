    
{include file="header"}
    <!-- 页面独有样式 -->
    <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/aliHome.css">
    <script src="https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js"></script>
</head>

 <!-- mounted之前显示 -->
 <div id="mainLoading">
        <div class="ddr ddr1"></div>
        <div class="ddr ddr2"></div>
        <div class="ddr ddr3"></div>
        <div class="ddr ddr4"></div>
        <div class="ddr ddr5"></div>
    </div>
    <div id="account" class="template">
        <el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/aliHome.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/aliHome.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}