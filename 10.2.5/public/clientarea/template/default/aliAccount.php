{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/aliAccount.css">
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
    <div id="account" class="template">
        <el-container>
            <ali-aside-menu :menu-active-id="1"></ali-aside-menu>
            <el-container>
                <top-menu></top-menu>
                <el-main>
                    <div id="ali-home" class="ali-home">
                        <div class="main-card">
                            <div class="top">
                                <div class="top-l"><img @click="goBack" src="/{$template_catalog}/template/{$themes}/img/finance/back.png" class="top-img"> {{lang.ali_title7}}
                                </div>
                            </div>
                            <div class="top-line"></div>
                            <div class="content-table">
                                <div class="searchbar com-search">
                                    <el-input v-model="params.keywords" style="width: 3.2rem;margin-left: .2rem;" :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange" clearable @clear="getList">
                                        <i class="el-icon-search input-search" slot="suffix" @Click="inputChange"></i>
                                    </el-input>
                                </div>
                                <div class="main_table">
                                    <el-table v-loading="loading1" :data="dataList1" style="width: 100%;">
                                        <el-table-column prop="id" label="ID" width="400" align="left">
                                        </el-table-column>
                                        <el-table-column prop="amount" :label="lang.ali_label2" width="150" align="left">
                                            <template slot-scope="scope">
                                                {{scope.row.amount? commonData.currency_prefix + scope.row.amount +
                                        commonData.currency_suffix :
                                        null}}

                                            </template>
                                        </el-table-column>
                                        <el-table-column prop="gateway" :label="lang.ali_label3" width="150" align="left">

                                        </el-table-column>
                                        <el-table-column prop="transaction_number" :label="lang.ali_label4" min-width="300" align="left">

                                        </el-table-column>
                                        <el-table-column prop="create_time" :label="lang.ali_label5" width="200" align="left">
                                            <template slot-scope="scope">
                                                <span>{{scope.row.create_time | formateTime}}</span>
                                            </template>
                                        </el-table-column>
                                    </el-table>
                                    <div class="page">
                                        <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange" />
                                    </div>
                                </div>
                            </div>

                            <div class="mobel">
                                <div class="mob-searchbar mob-com-search">
                                    <el-input class="mob-search-input" v-model="params.keywords" :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange" clearable @clear="getList">
                                        <i class="el-icon-search input-search" slot="suffix" @Click="inputChange"></i>
                                    </el-input>
                                </div>
                                <div class="mob-tabledata">
                                    <div class="mob-tabledata-item" v-for="item in dataList1" :key="item.id">
                                        <div class="mob-item-row mob-item-row1">
                                            <span>{{item.id}}</span>
                                            <span>
                                                {{item.transaction_number}}
                                            </span>
                                        </div>
                                        <div class="mob-item-row mob-item-row2">
                                            <span class="mob-item-row2-name">
                                                <span>{{ commonData.currency_prefix + item.amount}}</span>
                                            </span>
                                            <span>
                                            </span>
                                        </div>
                                        <div class="mob-item-row mob-item-row-notes">
                                            <span>{{item.notes}}</span>
                                        </div>
                                        <div class="mob-item-row mob-item-row3">
                                            <span>{{item.create_time | formateTime}}</span>
                                            <div>
                                                {{item.gateway}}
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="bottom-text">
                                    <span v-show="isEnd">已经到底啦~</span>
                                    <span v-loading=isShowMore></span>
                                </div>
                                <img v-show="isShowBackTop" class="back-top-img" @click="goBackTop" src="/{$template_catalog}/template/{$themes}/img/common/toTop.png">
                            </div>

                        </div>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/aliHome.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/aliAccount.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}