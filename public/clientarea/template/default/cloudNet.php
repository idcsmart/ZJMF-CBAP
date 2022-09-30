{include file="header"}
<!-- 统计图表 -->
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/cloudTop.css">
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/cloudNet.css">
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
                    <!-- 自己的东西 -->
                    <div class="main-card">
                        <cloud-top active-name="4"></cloud-top>
                        <div class="title">公网IP</div>
                        <div class="main_table">
                            <el-table v-loading="loading" :data="dataList" style="width: 100%;margin-bottom: .2rem;">
                                <el-table-column prop="ip" label="IP地址" min-width="200" align="left">
                                </el-table-column>
                                <el-table-column prop="gateway" width="400" label="网关" align="left">
                                </el-table-column>
                                <el-table-column prop="subnet_mask" width="400" label="掩码" align="left">
                                </el-table-column>
                            </el-table>
                            <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange">
                            </pagination>
                        </div>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/cloud.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/cloudNet.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/cloudTop/cloudTop.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    {include file="footer"}