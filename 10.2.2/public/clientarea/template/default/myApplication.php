
{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/myApplication.css">
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
                      <header>购买的应用</header>
                        <div class="top-search">
                            <div class="search-left">
                            </div>
                            <div class="search-right">
                                <el-select placeholder="请选择类别" clearable @change="getmyAppTable" v-model="params.type">
                                    <el-option v-for="(item,index) in typeList" :key="index" :value="item.value" :label="item.label"></el-option>
                                </el-select>
                                <el-select placeholder="请选择授权码" clearable @change="getmyAppTable" v-model="params.host_id">
                                    <el-option v-for="(item,index) in authorizeList" :key="index" :value="item.id" :label="item.license" ></el-option>
                                </el-select>
                                <el-input placeholder="请输入名称" clearable  v-model="params.keywords"></el-input>
                                <el-button class="search-btn" @click="getmyAppTable">查询</el-button>
                            </div>
                        </div>
                        <div class="content-table">
                            <el-table v-loading="loading1" :data="myAppTable" style="width: 100%;margin-bottom: .2rem;">
                                <el-table-column prop="id" label="序号" width="100" :show-overflow-tooltip="true" >
                                </el-table-column>
                                <el-table-column prop="name" label="服务名称"  :show-overflow-tooltip="true" >
                                </el-table-column>
                                <el-table-column prop="name" label="应用类别"  :show-overflow-tooltip="true" >
                                    <template slot-scope="{row}">
                                        {{ typeList.find(item => item.value == row.type).label}}
                                    </template>
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
                                <el-table-column prop="domain" label="关联域名"  :show-overflow-tooltip="true" >
                                </el-table-column>
                                <el-table-column prop="operation" label="操作" width="100" :show-overflow-tooltip="true" align="left">
                                    <template slot-scope="{row}">
                                        <div class="caozuo">
                                            <el-popover placement="top-start" trigger="hover">
                                                <div class="operation-box">
                                                    <div slot="reference"  v-if="row.status != 'Unpaid' "  class="operation-item" @click="download(row.product_id)">
                                                        下载安装包 
                                                    </div>
                                                    <div slot="reference" class="operation-item" @click="handelPay(row)"  v-if="row.status == 'Unpaid' ">
                                                        去支付
                                                    </div>
                                                    <div class="operation-item" @click="goShop_client(row)" v-if="row.pay_type !== 'onetime' && row.status !=='Unpaid'">续费</div>
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
                 <!-- 支付弹窗 -->
                <pay-dialog ref="payDialog" @payok="paySuccess" @paycancel="payCancel"></pay-dialog>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/js/myApplication.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/market.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}