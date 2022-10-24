{include file="header"}
<!-- 统计图表 -->
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/cloudTop.css">
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/cloudCharts.css">
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
                        <cloud-top active-name="1"></cloud-top>

                        <el-select class="time-select" v-model="selectValue" @change="selectChange">
                            <el-option value='1' label="过去24H"></el-option>
                            <el-option value='2' label="过去3天"></el-option>
                            <el-option value='3' label="过去7天"></el-option>
                        </el-select>

                        <div class="echart-main">
                            <!-- cpu用量图 -->
                            <div id="cpu-echart" class="my-echart" v-loading="loading1"></div>
                            <!-- 网络带宽 -->
                            <div id="bw-echart" class="my-echart" v-loading="loading2"></div>
                            <!-- 磁盘IO -->
                            <div id="disk-io-echart" class="my-echart" v-loading="loading3"></div>
                            <!-- 内存用量 -->
                            <div id="memory-echart" class="my-echart" v-loading="loading4"></div>
                        </div>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/cloud.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/echarts/5.3.3/echarts.min.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/cloudCharts.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/cloudTop/cloudTop.js"></script>
    {include file="footer"}