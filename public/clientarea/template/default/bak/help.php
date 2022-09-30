{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/help.css">
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
    <div class="template help">
        <el-container>
            <aside-menu></aside-menu>
            <el-container>
                <top-menu></top-menu>
                <el-main>
                    <!-- 自己的东西 -->
                    <div class="main-card">
                        <div class="main-card-title">{{lang.help_title}}</div>
                        <div class="main-card-top">
                            <ul class="top-menu">
                                <li class="top-menu-item top-menu-item-active">首页</li>
                                <li class="top-menu-item" @click="toHelpTotal">问题汇总</li>
                            </ul>
                            <div class="content_searchbar balance-searchbar">
                                <div class="left_tips">
                                </div>
                                <div class="searchbar com-search">
                                    <el-input v-model="params.keywords" style="width: 3.2rem;margin-left: .2rem;" :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange" clearable @clear="getHelpIndex">
                                        <i class="el-icon-search input-search" slot="suffix" @Click="inputChange"></i>
                                    </el-input>
                                </div>
                            </div>
                        </div>
                        <!-- 主体部分 -->
                        <div class="main-card-content">
                            <div class="content-item" v-for="item in helpIndexList" :key="item.id" v-if="item.id != null">
                                <div class="content-item-title">{{item.name}}</div>
                                <div class="content-item-link">
                                    <div class="link-item" :title="help.title" v-for="help in item.helps" :key="help.id" @click="toDetail(help.id)">
                                        {{help.title}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/help.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/help.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}