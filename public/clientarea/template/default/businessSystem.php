{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/businessSystem.css">
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
                    <div class="businessSystem-content">
                        <header>业务系统 V10</header>
                        <main>
                            <el-table v-loading="loading1" :data="tableData" style="width: 100%;margin-bottom: .2rem;">
                                <el-table-column prop="id" label="序号" width="100" :show-overflow-tooltip="true" >
                                </el-table-column>
                                <el-table-column prop="id" label="授权码"  :show-overflow-tooltip="true" >
                                </el-table-column>
                                <el-table-column prop="id" label="关联域名"  :show-overflow-tooltip="true" >
                                </el-table-column>
                                <el-table-column prop="id" label="插件数量" width="100" :show-overflow-tooltip="true" >
                                </el-table-column>
                            </el-table>
                            <pagination :page-data="params1" @sizechange="sizeChange1" @currentchange="currentChange1">
                            </pagination>
                        </main>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/js/businessSystem.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}