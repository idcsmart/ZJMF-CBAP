{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/helpTotal.css">
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
    <div class="template help-total">
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
                                <li class="top-menu-item" @click="toHelpIndex">首页</li>
                                <li class="top-menu-item top-menu-item-active">问题汇总</li>
                            </ul>
                            <div class="content_searchbar balance-searchbar">

                            </div>
                        </div>
                        <!-- 主体部分 -->
                        <div class="main-card-content">
                            <div class="content-left">
                                <div class="content-left-text">
                                    文档目录
                                </div>
                                <div class="content-left-menu">
                                    <el-menu @open="handleOpen" @close="handleClose" :default-active="activeId">
                                        <el-submenu :index="menu.id.toString()" v-for="menu in helpList" :key="menu.id">
                                            <template slot="title">{{menu.name}}</template>
                                            <el-menu-item :title="item.title" v-for="item in menu.helps" :index="item.id.toString()" @click="itemClick(item.id)">{{item.title}}</el-menu-item>
                                        </el-submenu>
                                    </el-menu>
                                </div>
                            </div>
                            <div class="content-right" v-show="detailData.id" v-loading="contentLoading">
                                <!-- 标题 -->
                                <div class="right-title">
                                    {{detailData.title}}
                                </div>
                                <!-- 更新时间 -->
                                <div class="right-keywords-time">
                                    <div class="right-time">
                                        更新时间：{{detailData.create_time | formateTime}}
                                    </div>
                                    <div class="right-keywords">
                                        关键字：{{detailData.keywords}}
                                    </div>
                                </div>

                                <!-- 主体内容 -->
                                <div class="right-content" v-html="detailData.content">
                                </div>
                                <!-- 附件 -->
                                <div class="right-attachment">
                                    附件：
                                    <div class="right-attachment-item" v-for="(f,i) in detailData.attachment" :key="i" @click="downloadfile(f)">
                                        <span :title="f.split('^')[1]">
                                            <i class="el-icon-tickets"></i><span>{{f.split('^')[1]}}</span>
                                        </span>
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
    <script src="/{$template_catalog}/template/{$themes}/js/helpTotal.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}