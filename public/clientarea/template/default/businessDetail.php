{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/businessDetail.css">
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
                    <div class="businessDetail-centent">
                        <header>
                            <img src="/{$template_catalog}/template/{$themes}/img/invoice/路径 5684.png" alt="" @click="goBack">
                            应用详情
                        </header>
                        <main>
                            <div class="main-info">
                                <p>基本信息</p>
                                <div class="info-box">
                                    <div class="info-item">
                                        <p>授权码</p>
                                        <p>****************</p>
                                    </div>
                                    <div class="info-item">
                                        <p>关联域名</p>
                                        <p>demo.demo.com</p>
                                    </div>
                                    <div class="info-item">
                                        <p>IP地址</p>
                                        <p>68.121.187.54</p>
                                    </div>
                                </div>
                            </div>

                            <el-tabs v-model="activeName">
                                <el-tab-pane label="应用列表" name="1" :key="1">
                                    <div class="table-box">
                                        <el-table v-loading="loading1" :data="data1" style="width: 100%;margin-bottom: .2rem;" :key="1">
                                            <el-table-column prop="id" label="ID" width="100" :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="id" label="应用名称"  :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="id" label="应用类别"  :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="id" label="购买时间"  :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="id" label="到期时间"  :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="id" label="购买金额"  :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="id" label="操作" width="100" :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                        </el-table>
                                        <pagination :page-data="params1" @sizechange="sizeChange1" @currentchange="currentChange1">
                                        </pagination>
                                    </div>
                                </el-tab-pane>
                                <el-tab-pane label="服务列表" name="2" :key="2">
                                    <div class="table-box">
                                        <el-table v-loading="loading2" :data="data2" style="width: 100%;margin-bottom: .2rem;" :key="2">
                                            <el-table-column prop="id" label="ID" width="100" :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="id" label="应用名称"  :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="id" label="购买时间"  :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="id" label="到期时间"  :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="id" label="购买金额"  :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="id" label="是否已使用"  :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="id" label="操作" width="100" :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                        </el-table>
                                        <pagination :page-data="params2" @sizechange="sizeChange2" @currentchange="currentChange2">
                                        </pagination>
                                    </div>
                                </el-tab-pane>
                                
                                <el-tab-pane label="文件下载" name="3" :key="3">
                                    <div class="table-box">
                                        <p>DCIM文件下载</p>
                                        <el-table v-loading="loading3" :data="data3" style="width: 100%;margin-bottom: .2rem;" :key="3">
                                            <el-table-column prop="id" label="文件名"  :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="id" label="上传日期"  :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="id" label="文件类型"  :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="id" label="大小"  :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="id" label="操作" width="100" :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                        </el-table>
                                        <pagination :page-data="params3" @sizechange="sizeChange3" @currentchange="currentChange3">
                                        </pagination>
                                    </div>
                                </el-tab-pane>
                            </el-tabs>
                        </main>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/js/businessDetail.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}