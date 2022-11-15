{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/myServe.css">
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
                    <div class="myserve-content">
                      <header>购买的服务</header>
                        <div class="top-search">
                            <div class="search-left">
                              
                            </div>
                            <div class="search-right">
                                <el-input placeholder="请输入名称/购买用户"></el-input>
                                <el-select placeholder="请选择商品类型"></el-select>
                                <el-button class="search-btn">查询</el-button>
                            </div>
                        </div>
                        <div class="content-table">
                            <el-table v-loading="loading1" :data="data1" style="width: 100%;margin-bottom: .2rem;">
                                <el-table-column prop="id" label="序号" width="100" :show-overflow-tooltip="true" >
                                </el-table-column>
                                <el-table-column prop="id" label="应用名称"  :show-overflow-tooltip="true" >
                                </el-table-column>
                                <el-table-column prop="id" label="应用类别"  :show-overflow-tooltip="true" >
                                </el-table-column>
                                <el-table-column prop="id" label="购买时间"  :show-overflow-tooltip="true" >
                                </el-table-column>
                                <el-table-column prop="id" label="到期时间"  :show-overflow-tooltip="true" >
                                </el-table-column>
                                <el-table-column prop="id" label="购买金额"  :show-overflow-tooltip="true" >
                                </el-table-column>
                                <el-table-column prop="id" label="关联域名"  :show-overflow-tooltip="true" >
                                </el-table-column>
                                <el-table-column prop="operation" label="操作" width="100" :show-overflow-tooltip="true" align="left">
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
    <script src="/{$template_catalog}/template/{$themes}/js/myServe.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}