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
                                <el-select placeholder="是否已使用" clearable @change="getMyServeTable" v-model="params.used">
                                    <el-option :value="0" label="否">否</el-option>
                                    <el-option :value="1" label="是">是</el-option>
                                </el-select>
                                <el-select placeholder="请选择授权码" clearable @change="getMyServeTable" v-model="params.host_id">
                                    <el-option v-for="item in authorizeList" :value="item.id" :label="item.license" ></el-option>
                                </el-select>
                                <el-input placeholder="请输入名称" v-model="params.keywords"></el-input>
                                <el-button class="search-btn" @click="getMyServeTable">查询</el-button>
                            </div>
                        </div>
                        <div class="content-table">
                            <el-table v-loading="loading1" :data="data1" style="width: 100%;margin-bottom: .2rem;">
                                <el-table-column prop="id" label="序号" width="100" :show-overflow-tooltip="true" >
                                </el-table-column>
                                <el-table-column prop="name" label="服务名称"  :show-overflow-tooltip="true" >
                                </el-table-column>
                                <el-table-column label="购买时间"  :show-overflow-tooltip="true" >
                                    <template slot-scope="{row}">
                                        {{ row.create_time | formateTime }}
                                    </template>
                                </el-table-column>
                                <el-table-column  label="到期时间"  :show-overflow-tooltip="true" >
                                    <template slot-scope="{row}">
                                        {{ row.due_time | formateTime }}
                                    </template>
                                </el-table-column>
                                <el-table-column prop="str" label="购买金额"  :show-overflow-tooltip="true" >
                                </el-table-column>
                                <el-table-column  label="是否已使用"  :show-overflow-tooltip="true" >
                                    <template slot-scope="{row}">
                                        {{ row.used == 0 ? '否': '是' }}
                                    </template>
                                </el-table-column>
                                <el-table-column label="关联域名"  :show-overflow-tooltip="true" >
                                    <template slot-scope="{row}">
                                        <el-tooltip :content="row.license" placement="top-start">
                                           <span>{{row.domain ? row.domain : '--'}}</span>
                                        </el-tooltip>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="operation" label="操作" width="100" align="left">
                                    <template slot-scope="{row}">
                                        <div class="caozuo">
                                            <el-popover placement="top-start" trigger="hover">
                                                <div class="operation-box">
                                                    <div slot="reference" @click="goShop(row)" v-if="row.pay_type === 'onetime' && row.status !=='Unpaid'" class="operation-item">再次购买</div>
                                                    <div slot="reference" @click="goPay(row)" v-if="row.status ==='Unpaid'" class="operation-item">去支付</div>
                                                    <div class="operation-item" @click="goShop_client(row)" v-if="row.pay_type !== 'onetime' && row.status !=='Unpaid'" class="operation-item">续费</div>
                                                </div>
                                                <i slot="reference" style="color: #0058FF"class="el-icon-more"></i>
                                            </el-popover>
                                        </div>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange">
                            </pagination>
                        </div>
                    </div>
                </el-main>
                 <!-- 支付弹窗 -->
                <pay-dialog ref="payDialog" @payok="paySuccess" @paycancel="payCancel"></pay-dialog>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/js/myServe.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/market.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}