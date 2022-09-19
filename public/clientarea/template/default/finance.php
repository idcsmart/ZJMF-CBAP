{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/finance.css">
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
    <div class="template" id="finance">
        <el-container>
            <aside-menu></aside-menu>
            <el-container>
                <top-menu></top-menu>
                <el-main>
                    <!-- 订单列表 -->
                    <div class="finance main-card" v-if="!isDetail">
                        <div class="top">
                            <div class="top-l">财务</div>
                            <div class="top-r" v-if="activeIndex == 3">
                                <div class="item-balance">
                                    <div class="money">
                                        {{ commonData.currency_prefix + balance }}
                                        <el-button class="btn-cz" @click="showCz">充值</el-button>
                                        <el-button class="btn-tx" @click="showTx">提现</el-button>
                                    </div>
                                    <div class="text">当前余额</div>
                                </div>
                                <div class="item-unbalance">
                                    <div class="money">
                                        {{ commonData.currency_prefix + unAmount}}
                                    </div>
                                    <div class="text">待退款金额</div>
                                </div>
                            </div>
                        </div>
                        <div class="content_box">
                            <div class="content_tab">
                                <el-tabs v-model="activeIndex" @tab-click="handleClick">
                                    <el-tab-pane label="订单记录" name="1">
                                        <div class="content_table">
                                            <div class="content_searchbar">
                                                <div class="left_tips">
                                                    <div v-for="(item,index) in tipslist1" class="tips_item" :key="index">
                                                        <span class="dot" :style="{'background':item.color}"></span>
                                                        <span>{{item.name}}</span>
                                                    </div>
                                                </div>
                                                <div class="searchbar com-search">
                                                    <el-input v-model="params1.keywords" style="width: 3.2rem;margin-left: .2rem;" :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange1" clearable @clear="getorderList">
                                                        <i class="el-icon-search input-search" slot="suffix" @Click="inputChange1"></i>
                                                    </el-input>
                                                </div>
                                            </div>
                                            <div class="tabledata">
                                                <el-table v-loading="loading1" :data="dataList1" style="width: 100%;margin-bottom: 20px;" row-key="id" lazy :load="load" :tree-props="{children: 'children', hasChildren: 'hasChildren'}">
                                                    <el-table-column prop="id" label="ID" width="150" align="left">
                                                        <template slot-scope="scope">
                                                            <span>
                                                                {{scope.row.product_names?scope.row.id:'--'}}
                                                            </span>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="product_names" label="商品名称" min-width="400" :show-overflow-tooltip="true">
                                                        <template slot-scope="scope">
                                                            <span class="dot" :class="scope.row.type">
                                                            </span>
                                                            <span>{{scope.row.product_name}}</span>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="billing_cycle" label="金额/周期" width="300">
                                                        <template slot-scope="scope">
                                                            <span>{{ commonData.currency_prefix + scope.row.amount}}</span>
                                                            <span v-if="scope.row.billing_cycle">/{{scope.row.billing_cycle}}</span>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="create_time" label="时间" width="250">
                                                        <template slot-scope="scope">
                                                            <span>{{scope.row.create_time | formateTime}}</span>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="status" label="状态" width="150">
                                                        <template slot-scope="scope">
                                                            <el-tag v-if="scope.row.status" :class="scope.row.status=='Unpaid'?'Unpaid':scope.row.status=='Paid'?'Paid':''">
                                                                {{scope.row.status=='Unpaid'?'未付款':scope.row.status=='Paid'?'已付款':''}}
                                                            </el-tag>
                                                            {{scope.row.host_status?status[scope.row.host_status]:null}}
                                                            {{scope.row.host_status||scope.row.status ? null : '--'}}
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="gateway" label="支付方式" width="150">
                                                        <template slot-scope="scope">
                                                            <!-- 存在支付状态 父 -->
                                                            <div v-if="scope.row.status">
                                                                <!-- 已支付 -->
                                                                <div v-if="scope.row.status === 'Paid'">
                                                                    <!-- 使用余额 -->
                                                                    <div v-if="scope.row.credit > 0">
                                                                        <!-- 全部使用余额 -->
                                                                        <div v-if="scope.row.credit == scope.row.amount">
                                                                            <span>余额</span>
                                                                        </div>
                                                                        <!-- 部分使用余额 -->
                                                                        <div v-else>
                                                                            <el-popover placement="top" trigger="hover" popper-class="tooltip">
                                                                                <i class="el-icon-s-finance" style="color: #F99600;font-size: 0.35rem;"></i>
                                                                                <span style="color: #F99600;"> {{commonData.currency_prefix
                                                                            + scope.row.credit +
                                                                            commonData.currency_suffix}}</span>
                                                                                <span slot="reference" class='gateway-pay'>余额</span>
                                                                            </el-popover>
                                                                            <span> + {{scope.row.gateway}}</span>
                                                                        </div>
                                                                    </div>
                                                                    <!-- 未使用余额 -->
                                                                    <span v-else>{{scope.row.gateway}}</span>

                                                                </div>
                                                                <!-- 未支付 -->
                                                                <a v-else class='gateway-pay' @click="showPayDialog(scope.row)">去支付</a>
                                                            </div>

                                                        </template>
                                                    </el-table-column>
                                                </el-table>
                                                <pagination :page-data="params1" @sizechange="sizeChange1" @currentchange="currentChange1"></pagination>
                                            </div>


                                            <!-- 移动端显示表格开始 -->
                                            <div class="mobel">
                                                <div class="mob-searchbar mob-com-search">
                                                    <el-input class="mob-search-input" v-model="params1.keywords" :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange1" clearable @clear="getorderList">
                                                        <i class="el-icon-search input-search" slot="suffix" @Click="inputChange1"></i>
                                                    </el-input>
                                                </div>
                                                <div class="mob-tabledata">
                                                    <div class="mob-tabledata-item" v-for="item in dataList1" :key="item.id">
                                                        <div class="mob-item-row mob-item-row1">
                                                            <span>{{item.id}}</span>
                                                            <span>
                                                                <el-tag v-if="item.status" :class="item.status=='Unpaid'?'Unpaid':item.status=='Paid'?'Paid':''">
                                                                    {{item.status=='Unpaid'?'未付款':item.status=='Paid'?'已付款':''}}
                                                                </el-tag>
                                                            </span>
                                                        </div>
                                                        <div class="mob-item-row mob-item-row2">
                                                            <span class="mob-item-row2-name" :title="item.product_name">
                                                                <span class="dot" :class="item.type"></span>
                                                                <span class="row2-name-text">{{item.product_name}}</span>
                                                            </span>
                                                            <span>
                                                                <span>{{ commonData.currency_prefix + item.amount}}</span>
                                                                <span v-if="item.billing_cycle">/{{item.billing_cycle}}</span>
                                                            </span>
                                                        </div>
                                                        <div class="mob-item-row mob-item-row3">
                                                            <span>{{item.create_time | formateTime}}</span>
                                                            <div v-if="item.status">
                                                                <!-- 已支付 -->
                                                                <div v-if="item.status === 'Paid'">
                                                                    <!-- 使用余额 -->
                                                                    <div v-if="item.credit > 0">
                                                                        <!-- 全部使用余额 -->
                                                                        <div v-if="item.credit == item.amount">
                                                                            <span>余额</span>
                                                                        </div>
                                                                        <!-- 部分使用余额 -->
                                                                        <div v-else>
                                                                            <el-popover placement="top" trigger="hover" popper-class="tooltip">
                                                                                <i class="el-icon-s-finance" style="color: #F99600;font-size: 0.35rem;"></i>
                                                                                <span style="color: #F99600;"> {{commonData.currency_prefix
                                                                            + item.credit +
                                                                            commonData.currency_suffix}}</span>
                                                                                <span slot="reference" class='gateway-pay'>余额</span>
                                                                            </el-popover>
                                                                            <span> + {{item.gateway}}</span>
                                                                        </div>
                                                                    </div>
                                                                    <!-- 未使用余额 -->
                                                                    <span v-else>{{item.gateway}}</span>
                                                                </div>
                                                                <!-- 未支付 -->
                                                                <a v-else class='gateway-pay' @click="showPayDialog(item)">去支付</a>
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
                                    </el-tab-pane>
                                    <el-tab-pane label="交易记录" name="2">
                                        <div class="content_table">
                                            <div class="content_searchbar">
                                                <div class="left_tips">
                                                    <div v-for="(item,index) in tipslist1" class="tips_item" :key="index">
                                                        <span class="dot" :style="{'background':item.color}"></span>
                                                        <span>{{item.name}}</span>
                                                    </div>
                                                </div>
                                                <div class="searchbar com-search">
                                                    <el-input v-model="params2.keywords" style="width: 3.2rem;margin-left: .2rem;" :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange2" clearable @clear="getTransactionList">
                                                        <i class="el-icon-search input-search" slot="suffix" @Click="inputChange2"></i>
                                                    </el-input>
                                                </div>
                                            </div>
                                            <div class="tabledata">
                                                <el-table v-loading="loading2" :data="dataList2" style="width: 100%;margin-bottom: .2rem;">
                                                    <el-table-column prop="id" label="ID" width="100" align="left">
                                                    </el-table-column>
                                                    <el-table-column prop="order_id" width="130" label="订单ID" align="left">
                                                        <template slot-scope="scope">
                                                            <div class="order_id">
                                                                <span class="dot" :class="scope.row.type">
                                                                </span>
                                                                <a v-if="scope.row.order_id !== '--'" class="orderid_a" @click="rowClick(scope.row.order_id)">{{scope.row.order_id}}</a>
                                                                <span v-else>{{scope.row.order_id}}</span>
                                                            </div>

                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="amount" min-width="250" label="金额" align="left">
                                                        <template slot-scope="scope">
                                                            <span>{{ commonData.currency_prefix + scope.row.amount }}
                                                            </span>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="create_time" width="200" label="时间" align="left">
                                                        <template slot-scope="scope">
                                                            <span>{{scope.row.create_time | formateTime}}</span>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="gateway" width="400" label="支付方式" align="left"></el-table-column>
                                                    <el-table-column prop="transaction_number" width="280" label="交易流水号" align="left" :show-overflow-tooltip="true"></el-table-column>
                                                </el-table>
                                                <pagination :page-data="params2" @sizechange="sizeChange2" @currentchange="currentChange2"></pagination>
                                            </div>

                                            <!-- 移动端显示表格开始 -->
                                            <div class="mobel">
                                                <div class="mob-searchbar mob-com-search">
                                                    <el-input class="mob-search-input" v-model="params2.keywords" :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange2" clearable @clear="getTransactionList">
                                                        <i class="el-icon-search input-search" slot="suffix" @Click="inputChange2"></i>
                                                    </el-input>
                                                </div>
                                                <div class="mob-tabledata">
                                                    <div class="mob-tabledata-item" v-for="item in dataList2" :key="item.id">
                                                        <div class="mob-item-row mob-item-row1">
                                                            <span>{{item.id}}</span>
                                                            <span>
                                                                {{item.transaction_number}}
                                                            </span>
                                                        </div>
                                                        <div class="mob-item-row mob-item-row2">
                                                            <span class="mob-item-row2-name" :title="item.product_name">
                                                                <span class="dot" :class="item.type"></span>
                                                                <span class="row2-name-text">
                                                                    <a v-if="item.order_id !== '--'" class="orderid_a" @click="rowClick(item.order_id)">{{item.order_id}}</a>
                                                                    <span v-else>{{item.order_id}}</span>
                                                                </span>
                                                            </span>
                                                            <span>
                                                                <span>{{ commonData.currency_prefix + item.amount}}</span>
                                                                <!-- <span v-if="item.billing_cycle">/{{item.billing_cycle}}</span> -->
                                                            </span>
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
                                    </el-tab-pane>
                                    <el-tab-pane label="余额记录" name="3">
                                        <div class="content_table">
                                            <div class="content_searchbar">
                                                <div class="left_tips">

                                                </div>
                                                <div class="searchbar com-search">
                                                    <!-- <el-input suffix-icon="el-input__icon el-icon-search" @input="inputChange2" v-model="params2.keywords" style="width: 3.2rem;margin-left: .2rem;" :placeholder="lang.cloud_tip_2"></el-input>
                                                    </el-input> -->
                                                    <el-input v-model="params3.keywords" style="width: 3.2rem;margin-left: .2rem;" :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange3" clearable @clear="getCreditList">
                                                        <i class="el-icon-search input-search" slot="suffix" @Click="inputChange3"></i>
                                                    </el-input>
                                                </div>
                                            </div>
                                            <div class="tabledata">
                                                <el-table v-loading="loading3" :data="dataList3" style="width: 100%;margin-bottom: .2rem;">
                                                    <el-table-column prop="id" label="ID" width="100" align="left">
                                                    </el-table-column>
                                                    <el-table-column prop="amount" width="150" label="金额" align="left">
                                                        <template slot-scope="scope">
                                                            <span>{{ commonData.currency_prefix + scope.row.amount}}
                                                            </span>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="notes" label="备注" min-width="800" align="left" :show-overflow-tooltip="true">
                                                    </el-table-column>
                                                    <el-table-column prop="type" label="类型" width="150" align="left">
                                                        <template slot-scope="scope">
                                                            <span class="balance-tag" :class="scope.row.type">{{balanceType[scope.row.type].text}}</span>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="create_time" width="200" label="时间" align="left">
                                                        <template slot-scope="scope">
                                                            <span>{{scope.row.create_time | formateTime}}</span>
                                                        </template>
                                                    </el-table-column>
                                                </el-table>
                                                <pagination :page-data="params3" @sizechange="sizeChange3" @currentchange="currentChange3"></pagination>
                                            </div>

                                            <!-- 移动端显示表格开始 -->
                                            <div class="mobel">
                                                <div class="mob-searchbar mob-com-search">
                                                    <el-input class="mob-search-input" v-model="params3.keywords" :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange3" clearable @clear="getCreditList">
                                                        <i class="el-icon-search input-search" slot="suffix" @Click="inputChange3"></i>
                                                    </el-input>
                                                </div>
                                                <div class="mob-tabledata">
                                                    <div class="mob-tabledata-item" v-for="item in dataList3" :key="item.id">
                                                        <div class="mob-item-row mob-item-row1">
                                                            <span>{{item.id}}</span>
                                                            <span>
                                                                <span class="balance-tag" :class="item.type">{{balanceType[item.type].text}}</span>
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
                                    </el-tab-pane>
                                </el-tabs>
                            </div>
                        </div>
                    </div>
                    <!-- 订单详情 -->
                    <div class="main-card" v-else>
                        <div class="top">
                            <div class="top-l"> <img src="/{$template_catalog}/template/{$themes}/img/finance/back.png" class="top-img" @click="isDetail=false"> 订单详情</div>
                        </div>
                        <div class="top-line"></div>
                        <div class="main_table">
                            <el-table v-loading="loading4" :data="dataList4" style="width: 100%;" :tree-props="{children:'items'}" row-key="id" :default-expand-all="true">
                                <el-table-column prop="product_name" label="商品名称" min-width="500" align="left">
                                    <template slot-scope="scope">
                                        <span>
                                            {{
                                            scope.row.product_name?scope.row.product_name:'--'
                                            }}
                                        </span>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="create_time" label="时间" width="250" align="left">
                                    <template slot-scope="scope">
                                        <span>{{scope.row.create_time | formateTime}}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="host_name" label="标识" width="350" align="left">
                                    <template slot-scope="scope">
                                        <span>{{scope.row.host_name?scope.row.host_name:'--'}}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="amount" label="金额/周期" width="150" align="left">
                                    <template slot-scope="scope">
                                        <span>
                                            {{scope.row.amount? commonData.currency_prefix + scope.row.amount + commonData.currency_suffix :
                                            null}}
                                            {{ scope.row.billing_cycle&&scope.row.amount? '/' + scope.row.billing_cycle
                                            : null}}
                                        </span>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="host_status" label="状态" width="150" align="left">
                                    <template slot-scope="scope">

                                        <el-tag v-if="scope.row.status" :class="scope.row.status=='Unpaid'?'Unpaid':scope.row.status=='Paid'?'Paid':''">
                                            {{scope.row.status=='Unpaid'?'未付款':scope.row.status=='Paid'?'已付款':''}}
                                        </el-tag>
                                        {{scope.row.host_status?status[scope.row.host_status]:null}}
                                        {{scope.row.host_status||scope.row.status ? null : '--'}}
                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                        <!-- 移动端开始 -->
                        <div class="mobel">
                            <div class="mob-tabledata order-detail-table">
                                <div class="mob-tabledata-item" v-for="item in dataList4" :key="item.id">
                                    <div class="mob-item-row mob-item-row1">
                                        <span></span>
                                        <span>
                                            <el-tag v-if="item.status" :class="item.status=='Unpaid'?'Unpaid':item.status=='Paid'?'Paid':''">
                                                {{item.status=='Unpaid'?'未付款':item.status=='Paid'?'已付款':''}}
                                            </el-tag>
                                        </span>
                                    </div>
                                    <div class="mob-item-row mob-item-row2">

                                        <span class="mob-item-row2-name">
                                            <span class="dot" :class="item.type"></span>
                                            <span>{{ item.product_name?item.product_name:'--'}}</span>
                                        </span>
                                        <span>
                                            {{item.amount? commonData.currency_prefix + item.amount + commonData.currency_suffix :
                                            null}}
                                            {{ item.billing_cycle&&item.amount? '/' + item.billing_cycle
                                            : null}}
                                        </span> 
                                    </div>
                                    <div class="mob-item-row mob-item-row-child">
                                        <div class="child-row" v-for="child in item.items" :key="child.id">
                                            <span class="child-row-name">{{ child.product_name?child.product_name:'--'}}</span>
                                            <span>
                                                {{child.amount? commonData.currency_prefix + child.amount + commonData.currency_suffix :
                                            null}}
                                                {{ child.billing_cycle&&child.amount? '/' + child.billing_cycle
                                            : null}}
                                            </span>
                                            <span>{{child.host_status?status[child.host_status]:null}}
                                                {{child.host_status||child.status ? null : '--'}}</span>
                                        </div>
                                    </div>
                                    <div class="mob-item-row mob-item-row3">
                                        <span>{{item.create_time | formateTime}}</span>
                                        <div>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- 申请提现 dialog -->
                    <div class="tx-dialog">
                        <el-dialog width="6.8rem" :visible.sync="isShowTx" :show-close=false :close-on-click-modal=false>
                            <div class="dialog-title">
                                申请提现
                            </div>
                            <div class="dialog-form">
                                <el-form :model="txData" label-position="top">
                                    <el-form-item label="提现方式">
                                        <el-select v-model="txData.method">

                                            <el-option v-for="item in ruleData.method" :label="item=='alipay'?'支付宝':'银行卡'" :value="item"></el-option>
                                            <!-- <el-option label="银行卡" value="bank"></el-option> -->
                                        </el-select>
                                    </el-form-item>
                                    <el-form-item v-if="txData.method==='alipay'" label="支付宝账号">
                                        <el-input v-model="txData.account"></el-input>
                                    </el-form-item>
                                    <el-form-item v-if="txData.method==='bank'" label="银行卡号">
                                        <el-input v-model="txData.card_number"></el-input>
                                    </el-form-item>
                                    <el-form-item v-if="txData.method==='bank'" label="姓名">
                                        <el-input v-model="txData.name"></el-input>
                                    </el-form-item>
                                    <el-form-item label="提现金额">
                                        <el-input @keyup.native="txData.amount=oninput(txData.amount)" v-model="txData.amount" :placeholder="'可提现'+ commonData.currency_prefix + balance + commonData.currency_suffix">
                                            <el-button type="text" slot="suffix" @click="txData.amount=balance">全部
                                            </el-button>
                                        </el-input>
                                    </el-form-item>
                                    <el-form-item v-if="errText">
                                        <el-alert :title="errText" type="error" :closable="false" show-icon>
                                        </el-alert>
                                    </el-form-item>
                                </el-form>
                            </div>
                            <div class="dialog-footer">
                                <el-button class="btn-ok" @click="doCredit">提交</el-button>
                                <el-button class="btn-no" @click="isShowTx = false">取消</el-button>
                            </div>
                        </el-dialog>
                    </div>
                    <!-- 充值 dialog -->
                    <div class="cz-dialog">
                        <el-dialog width="6.8rem" :visible.sync="isShowCz" :show-close=false @close="czClose">
                            <div class="dialog-title">
                                充值
                            </div>
                            <div class="dialog-form">
                                <el-form :model="czData" label-position="top">
                                    <el-form-item label="充值方式">
                                        <el-select v-model="czData.gateway" @change="czSelectChange">
                                            <el-option v-for="item in gatewayList" :key="item.id" :label="item.title" :value="item.name"></el-option>
                                        </el-select>
                                    </el-form-item>
                                    <el-form-item label="充值金额" @keyup.native="czData.amount=oninput(czData.amount)">
                                        <div class="cz-input">
                                            <el-input v-model="czData.amount">
                                            </el-input>
                                            <el-button class="btn-ok" @click="czInputChange">提交</el-button>
                                        </div>
                                    </el-form-item>
                                    <el-form-item v-if="errText">
                                        <el-alert :title="errText" type="error" :closable="false" show-icon>
                                        </el-alert>
                                    </el-form-item>
                                    <el-form-item v-loading="payLoading1">
                                        <div class="pay-html" v-show="isShowimg1" v-html="payHtml"></div>
                                    </el-form-item>
                                </el-form>
                            </div>
                        </el-dialog>
                    </div>
                    <!-- 支付 dialog -->
                    <!-- <pay-dialog :is-show-zf="isShowZf" :order-id="orderId" :amount="amount"></pay-dialog> -->
                    <div class="zf-dialog">
                        <el-dialog width="6.8rem" :visible.sync="isShowZf" :show-close=false @close="zfClose">
                            <div class="dialog-title">
                                支付
                            </div>
                            <div class="dialog-form">
                                <el-row>
                                    <el-col :span="4">支付方式</el-col>
                                    <el-col :span="20">
                                        <el-select @change="zfSelectChange" v-model="zfData.gateway">
                                            <el-option v-for="item in gatewayList" :key="item.id" :label="item.title" :value="item.name"></el-option>
                                        </el-select>
                                    </el-col>
                                </el-row>
                                <el-row>
                                    <el-col :span="4"><span>&nbsp;</span></el-col>
                                    <el-col :span="20">
                                        <el-checkbox v-model="zfData.checked" @change="useBalance">使用余额</el-checkbox>
                                    </el-col>
                                </el-row>
                                <el-row>
                                    <el-col :span="4">订单金额</el-col>
                                    <el-col :span="20">
                                        <div>{{ commonData.currency_prefix }}
                                            {{ Number(zfData.amount).toFixed(2)}} {{ commonData.currency_suffix }}
                                        </div>
                                    </el-col>
                                </el-row>
                                <el-row>
                                    <el-col :span="4">支付金额</el-col>
                                    <el-col :span="20">
                                        <div class="true-money">{{ currency_prefix}}
                                            {{
                                                zfData.checked?(zfData.amount-balance <=0 ? 0:
                                                zfData.amount-balance).toFixed(2):Number(zfData.amount).toFixed(2)}} {{
                                                    commonData.currency_suffix }}
                                        </div>
                                    </el-col>
                                </el-row>
                                <el-row v-show="!isShowPay">
                                    <el-col :span="4"><span>&nbsp;</span></el-col>
                                    <el-col :span="20">
                                        <div class="true-money">
                                            -{{ currency_prefix }}
                                            {{zfData.checked?zfData.amount>=balance?Number(balance).toFixed(2):Number(zfData.amount).toFixed(2):null}}
                                            {{commonData.currency_suffix }}
                                        </div>
                                    </el-col>
                                </el-row>
                                <el-row v-show="errText">
                                    <el-col :span="24">
                                        <el-alert :title="errText" type="error" :closable="false" show-icon>
                                        </el-alert>
                                    </el-col>
                                </el-row>

                                <el-row v-show="isShowPay" v-loading="payLoading">
                                    <el-col :span="24">
                                        <div class="pay-html" v-show="isShowimg" v-html="payHtml"></div>
                                    </el-col>
                                </el-row>
                                <el-row v-show="!isShowPay">
                                    <el-col :span="24">
                                        <div class="form-footer">
                                            <el-button class="btn-ok" @click="handleOk">确认支付</el-button>
                                            <el-button class="btn-no" @click="zfClose">取消</el-button>
                                        </div>
                                    </el-col>
                                </el-row>
                            </div>
                        </el-dialog>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/finance.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/finance.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/payDIalog/payDIalog.js"></script>
    {include file="footer"}