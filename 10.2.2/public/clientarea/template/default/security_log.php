{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/security_log.css">
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
            <aside-menu @getruleslist="getRule"></aside-menu>
            <el-container>
                <top-menu></top-menu>
                <el-main>
                    <!-- 自己的东西 -->
                    <div class="main-card">
                        <div class="main-card-title">{{lang.security_title}}</div>
                        <el-tabs v-model="activeName" @tab-click="handleClick">
                            <el-tab-pane label="API" name="1" v-if="isShowAPI"></el-tab-pane>
                            {foreach $addons as $addon}
                            {if ($addon.name=='IdcsmartSshKey')}
                            <el-tab-pane :label="lang.security_tab1" name="2">
                            </el-tab-pane>
                            {/if}
                            {/foreach}
                            <el-tab-pane :label="lang.security_tab2" name="3" v-if="isShowAPILog">
                                <div class="content-table">
                                    <div class="content_searchbar">
                                        <div>
                                            <!-- 占位 -->
                                        </div>
                                        <div class="searchbar com-search">
                                            <el-input v-model="params.keywords" style="width: 3.2rem;margin-left: .2rem;" :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange" clearable @clear="getLogList">
                                                <i class="el-icon-search input-search" slot="suffix" @Click="inputChange"></i>
                                            </el-input>
                                        </div>
                                    </div>
                                    <div class="tabledata">
                                        <el-table v-loading="loading" :data="dataList" style="width: 100%;margin-bottom: .2rem;">
                                            <el-table-column prop="id" label="ID" width="150" align="left">
                                            </el-table-column>
                                            <el-table-column prop="description" :label="lang.security_label8" min-width="200" :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="create_time" :label="lang.account_label10" min-width="200" align="left">
                                                <template slot-scope="scope">
                                                    <span>{{scope.row.create_time | formateTime}}</span>
                                                </template>
                                            </el-table-column>
                                            <el-table-column prop="ip" label="IP" width="200" :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                        </el-table>
                                        <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange"></pagination>
                                    </div>
                                </div>
                            </el-tab-pane>
                            <el-tab-pane :label="lang.security_group" name="4"></el-tab-pane>
                        </el-tabs>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/security.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/security_log.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}