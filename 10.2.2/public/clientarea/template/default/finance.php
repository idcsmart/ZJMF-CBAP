{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/finance.css">
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/voucher.css">
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
      <aside-menu @getruleslist="getRule"></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <el-main>
          <!-- 订单列表 -->
          <div class="finance main-card" v-if="!isDetail">
            <div class="top">
              <div class="top-l">{{lang.finance_title}}</div>
              <!--  v-if="activeIndex == 3" -->
              <div class="top-r">
                <div class="item-balance">
                  <div class="money">
                    <div class="money-num">
                      {{ commonData.currency_prefix + balance }}
                      <div class="text">{{lang.finance_text1}}</div>
                    </div>
                    <div class="btn-cz" @click="showCz">{{lang.finance_btn1}}</div>
                    {foreach $addons as $addon}
                    {if $addon['name']=='IdcsmartWithdraw'}
                    <div class="btn-tx" @click="showTx">{{lang.finance_btn2}}</div>
                    {/if}
                    {/foreach}
                    <template>
                      {foreach $addons as $addon}
                      {if $addon['name']=='IdcsmartWithdraw'}
                      <div class="tx-list" @click="goWithdrawal">
                        <el-badge :is-dot="isdot" class="tx-list">提现记录</el-badge>
                      </div>
                      {/if}
                      {/foreach}
                    </template>
                  </div>
                </div>
                {foreach $addons as $addon}
                {if $addon['name']=='IdcsmartRefund'}
                <div class="item-unbalance">
                  <div class="money">
                    {{ commonData.currency_prefix + unAmount}}
                  </div>
                  <div class="text">{{lang.finance_text2}}</div>
                </div>
                {/if}
                {/foreach}

              </div>
            </div>

            <div class="content_box">
              <div class="content_tab">
                <el-tabs v-model="activeIndex" @tab-click="handleClick">
                  <el-tab-pane :label="lang.finance_tab1" name="1" v-if="isShowOrderController">
                    <div class="content_table">
                      <div class="content_searchbar">
                        <div class="left_tips">
                          <el-button class="all-pay" @click="handelAllPay" :loading="allLoading" v-if="isShowCombine">合并支付</el-button>
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
                        <el-table v-loading="loading1" @selection-change="handleSelectionChange" :data="dataList1" style="width: 100%;margin-bottom: 20px;" :row-key="getRowKey" lazy :load="load" :tree-props="{children: 'children', hasChildren: 'hasChildren'}">
                          <el-table-column type="selection" width="80" v-if="isShowCombine"></el-table-column>
                          <el-table-column prop="id" label="ID" width="100" align="left">
                            <template slot-scope="scope">
                              <span>
                                {{scope.row.product_names ? scope.row.id : '--'}}
                              </span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="product_names" :label="lang.finance_label1" min-width="300" :show-overflow-tooltip="true">
                            <template slot-scope="scope">
                              <span class="dot" :class="scope.row.type">
                              </span>
                              <el-tooltip placement="top" v-if="scope.row.description">
                                <div slot="content">{{scope.row.description}}</div>
                                <span style="cursor: pointer;">{{scope.row.product_name}}</span>
                              </el-tooltip>
                              <span v-else>{{scope.row.product_name}}</span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="billing_cycle" :label="lang.finance_label2" width="200">
                            <template slot-scope="scope">
                              <span v-if="scope.row.status=='Unpaid'" @click="showPayDialog(scope.row)" style="cursor: pointer;">
                                <span>{{ commonData.currency_prefix + scope.row.amount}}</span>
                                <span v-if="scope.row.billing_cycle">/{{scope.row.billing_cycle}}</span>
                              </span>
                              <span v-else>
                                <span>{{ commonData.currency_prefix + scope.row.amount}}</span>
                                <span v-if="scope.row.billing_cycle">/{{scope.row.billing_cycle}}</span>
                              </span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="create_time" :label="lang.finance_label3" width="200">
                            <template slot-scope="scope">
                              <span>{{scope.row.create_time | formateTime}}</span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="status" :label="lang.finance_label4" width="150">
                            <template slot-scope="scope">
                              <!-- 未付款 -->
                              <el-tag v-if="scope.row.status && scope.row.status=='Unpaid'" @click="showPayDialog(scope.row)" style="cursor: pointer;" class="Unpaid">
                                {{lang.finance_text3}}
                              </el-tag>
                              <!-- 已付款 -->
                              <el-tag v-if="scope.row.status && scope.row.status=='Paid'" class="Paid">
                                {{lang.finance_text4}}
                              </el-tag>
                              <!-- 已完成 -->
                              <el-tag v-if="scope.row.status && scope.row.status=='Refunded'" class="Refunded">
                                已退款
                              </el-tag>
                              {{scope.row.host_status?status[scope.row.host_status]:null}}
                              {{scope.row.host_status || scope.row.status ? null : '--'}}
                            </template>
                          </el-table-column>
                          <el-table-column prop="gateway" :label="lang.finance_label5" width="200">
                            <template slot-scope="scope">
                              <!-- 存在支付状态 父 -->
                              <div v-if="scope.row.status">
                                <!-- 已支付 -->
                                <div v-if="scope.row.gateway">
                                  <!-- 使用余额 -->
                                  <div v-if="scope.row.credit > 0">
                                    <!-- 全部使用余额 -->
                                    <div v-if="scope.row.credit == scope.row.amount">
                                      <span>{{lang.finance_text5}}</span>
                                    </div>
                                    <!-- 部分使用余额 -->
                                    <div v-else>
                                      <el-popover placement="top" trigger="hover" popper-class="tooltip">
                                        <i class="el-icon-s-finance" style="color: #F99600;font-size: 0.35rem;"></i>
                                        <span style="color: #F99600;"> {{commonData.currency_prefix
                                                                            + scope.row.credit +
                                                                            commonData.currency_suffix}}</span>
                                        <span slot="reference" class='gateway-pay'>{{lang.finance_text5}}</span>
                                      </el-popover>
                                      <span>{{scope.row.gateway ? '+'+scope.row.gateway:''}}</span>
                                    </div>
                                  </div>
                                  <!-- 未使用余额 -->
                                  <span v-else>{{scope.row.gateway}}</span>

                                </div>
                                <!-- 未支付 -->
                                <a v-else class='gateway-pay'>--</a>
                              </div>

                            </template>
                          </el-table-column>
                          <el-table-column :label="lang.finance_label6" prop="id" width="100" align="left">
                            <template slot-scope="scope" v-if="scope.row.status === 'Unpaid'">
                              <el-popover placement="top-start" trigger="hover">
                                <div class="operation-box">
                                  <div slot="reference" class="operation-item" @click="openDeletaDialog(scope.row,scope.$index)">{{lang.finance_btn4}}</div>
                                  <div class="operation-item" @click="showPayDialog(scope.row)">{{lang.finance_btn3}}</div>
                                </div>
                                <i slot="reference" class="el-icon-more"></i>
                              </el-popover>
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
                          <div class="mob-tabledata-item" v-for="item in dataList1" :key="item.id" @click="showItem(item)">
                            <div class="mob-item-row mob-item-row1">
                              <span>{{item.id}}</span>
                              <span>
                                <el-tag v-if="item.status" :class="item.status=='Unpaid'?'Unpaid':item.status=='Paid'?'Paid':''">
                                  {{item.status=='Unpaid'?lang.finance_text3:item.status=='Paid'?lang.finance_text4:''}}
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

                            <div class="mob-item-row mob-item-row-child">

                              <div class="child-row" v-for="child in item.data" :key="child.id">

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
                              <div v-if="item.status">
                                <!-- 已支付 -->
                                <div v-if="item.status === 'Paid'">
                                  <!-- 使用余额 -->
                                  <div v-if="item.credit > 0">
                                    <!-- 全部使用余额 -->
                                    <div v-if="item.credit == item.amount">
                                      <span>{{lang.finance_text5}}</span>
                                    </div>
                                    <!-- 部分使用余额 -->
                                    <div v-else>
                                      <el-popover placement="top" trigger="hover" popper-class="tooltip">
                                        <i class="el-icon-s-finance" style="color: #F99600;font-size: 0.35rem;"></i>
                                        <span style="color: #F99600;"> {{commonData.currency_prefix
                                                                            + item.credit +
                                                                            commonData.currency_suffix}}</span>
                                        <span slot="reference" class='gateway-pay'>{{lang.finance_text5}}</span>
                                      </el-popover>
                                      <span> + {{item.gateway}}</span>
                                    </div>
                                  </div>
                                  <!-- 未使用余额 -->
                                  <span v-else>{{item.gateway}}</span>
                                </div>
                                <!-- 未支付 -->
                                <a v-else class='gateway-pay' @click="showPayDialog(item)">{{lang.finance_btn3}}</a>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="bottom-text">

                          <span v-show="isEnd">{{lang.finance_text6}}</span>
                          <span v-loading=isShowMore></span>
                        </div>
                        <img v-show="isShowBackTop" class="back-top-img" @click="goBackTop" src="/{$template_catalog}/template/{$themes}/img/common/toTop.png">
                      </div>

                    </div>
                  </el-tab-pane>
                  <el-tab-pane :label="lang.finance_tab2" name="2" v-if="isShowTransactionController">
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
                          <el-table-column prop="order_id" width="130" :label="lang.finance_label7" align="left">
                            <template slot-scope="scope">
                              <div class="order_id">
                                <span class="dot" :class="scope.row.type">
                                </span>
                                <a v-if="scope.row.order_id !== '--'" class="orderid_a" @click="rowClick(scope.row.order_id)">{{scope.row.order_id}}</a>
                                <span v-else>{{scope.row.order_id}}</span>
                              </div>

                            </template>
                          </el-table-column>
                          <el-table-column prop="amount" min-width="250" :label="lang.finance_label8" align="left">
                            <template slot-scope="scope">
                              <span>{{ commonData.currency_prefix + scope.row.amount }}
                              </span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="create_time" width="200" :label="lang.finance_label3" align="left">
                            <template slot-scope="scope">
                              <span>{{scope.row.create_time | formateTime}}</span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="gateway" width="400" :label="lang.finance_label5" align="left"></el-table-column>
                          <el-table-column prop="transaction_number" width="280" :label="lang.finance_label9" align="left" :show-overflow-tooltip="true"></el-table-column>
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
                          <span v-show="isEnd">{{lang.finance_text6}}</span>
                          <span v-loading=isShowMore></span>
                        </div>
                        <img v-show="isShowBackTop" class="back-top-img" @click="goBackTop" src="/{$template_catalog}/template/{$themes}/img/common/toTop.png">
                      </div>


                    </div>
                  </el-tab-pane>
                  <el-tab-pane :label="lang.finance_tab3" name="3" v-if="isShowBalance">
                    <div class="content_table">
                      <div class="content_searchbar balance-searchbar">
                        <div class="left_tips">

                        </div>
                        <div class="searchbar com-search">

                        </div>
                        <div class="box" style="display:flex">
                          <el-date-picker @change="inputChange3" v-model="date" type="daterange" range-separator="至" style="width:3.5rem;margin-right:0.14rem" start-placeholder="开始日期" value-format="timestamp" align="center" end-placeholder="结束日期">
                          </el-date-picker>
                          <el-select v-model="params3.type" placeholder="请选择类型" style="width:2rem" @change="inputChange3">
                            <el-option :label="balanceType.Recharge.text" value="Recharge">{{balanceType.Recharge.text}}</el-option>
                            <el-option :label="balanceType.Applied.text" value="Applied">{{balanceType.Applied.text}}</el-option>
                            <el-option :label="balanceType.Refund.text" value="Refund">{{balanceType.Refund.text}}</el-option>
                            <el-option :label="balanceType.Withdraw.text" value="Withdraw">{{balanceType.Withdraw.text}}</el-option>
                          </el-select>
                          <el-input v-model="params3.keywords" style="width: 3.2rem;margin-left: .2rem;" :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange3" clearable @clear="getCreditList">
                            <i class="el-icon-search input-search" slot="suffix" @Click="inputChange3"></i>
                          </el-input>
                        </div>
                      </div>
                      <div class="tabledata">
                        <el-table v-loading="loading3" :data="dataList3" style="width: 100%;margin-bottom: .2rem;">
                          <el-table-column prop="id" label="ID" width="100" align="left">
                          </el-table-column>
                          <el-table-column prop="amount" width="150" :label="lang.finance_label8" align="left">
                            <template slot-scope="scope">
                              <span>{{ commonData.currency_prefix + scope.row.amount}}
                              </span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="notes" :label="lang.finance_label10" min-width="800" align="left" :show-overflow-tooltip="true">
                          </el-table-column>
                          <el-table-column prop="type" :label="lang.finance_label11" width="150" align="left">
                            <template slot-scope="scope">
                              <span class="balance-tag" :class="scope.row.type">{{balanceType[scope.row.type].text}}</span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="create_time" width="200" :label="lang.finance_label3" align="left">
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
                          <span v-show="isEnd">{{lang.finance_text6}}</span>
                          <span v-loading=isShowMore></span>
                        </div>
                        <img v-show="isShowBackTop" class="back-top-img" @click="goBackTop" src="/{$template_catalog}/template/{$themes}/img/common/toTop.png">
                      </div>


                    </div>
                  </el-tab-pane>
                  <el-tab-pane label="我的代金券" name="4" v-if="isShowCash">
                    <div class="voucher">
                      <!-- 代金券 -->
                      <div class="voucher-box">
                        <div class="get-voucher" @click="showVoucherDialog">
                          {{lang.voucher_get}}
                        </div>
                        <div class="voucher-content" v-loading="voucherLoading">
                          <ul>
                            <li class="item" v-for="item in voucherList" :key="item.id">
                              <div class="basic">
                                <div class="l-item">
                                  <div class="price" :class="{used: item.status === 'used',overdue: item.status === 'expired' }">
                                    <span>{{commonData.currency_prefix}}</span>
                                    <span class="num">{{item.price}}</span>
                                    <p class="des">{{lang.voucher_min}}：{{commonData.currency_prefix}}{{item.min_price}}</p>
                                  </div>
                                </div>
                                <div class="r-item">
                                  <p class="tit">{{item.code}}</p>
                                  <p class="time">{{item.start_time | formateTime1}}- {{item.end_time | formateTime1}}
                                  </p>
                                  <div class="bot">
                                    <p class="more" :class="{active: item.isShow}" @click="toggleVoucher(item)">
                                      {{lang.voucher_rule}}
                                      <img src="/{$template_catalog}/template/{$themes}/img/voucher/check.png" alt="">
                                    </p>
                                  </div>
                                </div>
                                <div class="bg" :class="{used: item.status === 'used', overdue: item.status === 'expired'}"></div>
                              </div>
                              <div class="detail" :class="{active: item.isShow}">
                                <p v-if="item.product.length > 0">
                                  {{lang.voucher_order_product}}：
                                  <span v-for="(el,index) in item.product" :key="el.id">{{el.name}}；
                                </p>
                                <p v-if="item.product_need.length > 0">
                                  {{lang.voucher_accout_product}}：
                                  <span v-for="(el,index) in item.product_need" :key="el.id">{{el.name}}；
                                </p>
                                <p v-if="item.user_type === 'no_host'">
                                  {{lang.voucher_no_product}}
                                </p>
                                <p v-if="item.user_type === 'need_active'">
                                  {{lang.voucher_active}}
                                </p>
                                <p v-if="item.onetime">{{lang.voucher_onetime}}</p>
                                <p v-if="item.upgrade_use">{{lang.voucher_upgrade}}</p>
                                <p v-if="item.renew_use">{{lang.voucher_renew}}</p>
                                <p v-if="!item.upgrade_use">{{lang.voucher_upgrade_no}}</p>
                                <p v-if="!item.renew_use">{{lang.voucher_renew_no}}</p>
                              </div>
                            </li>
                          </ul>
                        </div>
                      </div>

                      <!-- 领劵弹窗 -->
                      <el-dialog :title="lang.voucher_get" :visible.sync="dialogVisible" class="voucher-dialog">
                        <div class="voucher-content" v-loading="diaLoading">
                          <div class="empty" v-if="voucherAvailableList.length === 0">
                            <el-empty :description="lang.voucher_empty"></el-empty>
                          </div>
                          <ul v-else>
                            <li class="item" v-for="item in voucherAvailableList" :key="item.id">
                              <div class="basic">
                                <div class="l-item">
                                  <div class="price">
                                    <span>{{commonData.currency_prefix}}</span>
                                    <span class="num">{{item.price}}</span>
                                    <p class="des">{{lang.voucher_min}}：{{commonData.currency_prefix}}{{item.min_price}}</p>
                                  </div>
                                </div>
                                <div class="r-item">
                                  <p class="tit">{{item.code}}</p>
                                  <p class="time">{{item.start_time | formateTime1}}- {{item.end_time | formateTime1}}
                                    <div class="bot">
                                      <p class="more" :class="{active: item.isShow}" @click="toggleVoucher(item)">
                                        {{lang.voucher_rule}}
                                        <img src="/{$template_catalog}/template/{$themes}/img/voucher/check.png" alt="">
                                      </p>
                                      <p class="receive" @click="sureGet(item)" :class="{is_get: item.is_get}">
                                        {{item.is_get ?lang.voucher_has_get: lang.voucher_get_now}}
                                      </p>
                                    </div>
                                </div>

                              </div>
                              <div class="detail" :class="{active: item.isShow}">
                                <p v-if="item.product.length > 0">
                                  {{lang.voucher_order_product}}：
                                  <span v-for="(el,index) in item.product" :key="el.id">{{el.name}}；
                                </p>
                                <p v-if="item.product_need.length > 0">
                                  {{lang.voucher_accout_product}}：
                                  <span v-for="(el,index) in item.product_need" :key="el.id">{{el.name}}；
                                </p>
                                <p v-if="item.user_type === 'no_host'">
                                  {{lang.voucher_no_product}}
                                </p>
                                <p v-if="item.user_type === 'need_active'">
                                  {{lang.voucher_active}}
                                </p>
                                <p v-if="item.onetime">{{lang.voucher_onetime}}</p>
                                <p v-if="item.upgrade_use">{{lang.voucher_upgrade}}</p>
                                <p v-if="item.renew_use">{{lang.voucher_renew}}</p>
                                <p v-if="!item.upgrade_use">{{lang.voucher_upgrade_no}}</p>
                                <p v-if="!item.renew_use">{{lang.voucher_renew_no}}</p>

                              </div>
                            </li>
                          </ul>
                        </div>
                      </el-dialog>

                      <pagination :page-data="vParams" v-if="vParams.total" @sizechange="sizeChange" @currentchange="currentChange" class="voucher-page">
                      </pagination>
                    </div>
                  </el-tab-pane>
                </el-tabs>
              </div>
            </div>
          </div>
          <!-- 订单详情 -->
          <div class="main-card" v-else>
            <div class="top">
              <div class="top-l"> <img src="/{$template_catalog}/template/{$themes}/img/finance/back.png" class="top-img" @click="isDetail=false"> {{lang.finance_title2}}</div>
            </div>
            <div class="top-line"></div>
            <div class="main_table">
              <el-table v-loading="loading4" :data="dataList4" style="width: 100%;" :tree-props="{children:'items'}" row-key="id" :default-expand-all="true">
                <el-table-column prop="product_name" :label="lang.finance_label1" min-width="500" align="left">
                  <template slot-scope="scope">
                    <span>
                      {{
                                            scope.row.product_name?scope.row.product_name:'--'
                                            }}
                    </span>
                  </template>
                </el-table-column>
                <el-table-column prop="create_time" :label="lang.finance_label3" width="250" align="left">
                  <template slot-scope="scope">
                    <span>{{scope.row.create_time | formateTime}}</span>
                  </template>
                </el-table-column>
                <el-table-column prop="host_name" :label="lang.finance_label12" width="350" align="left">
                  <template slot-scope="scope">
                    <span>{{scope.row.host_name?scope.row.host_name:'--'}}</span>
                  </template>
                </el-table-column>
                <el-table-column prop="amount" :label="lang.finance_label2" width="150" align="left">
                  <template slot-scope="scope">
                    <span>
                      {{scope.row.amount? commonData.currency_prefix + scope.row.amount + commonData.currency_suffix :
                                            null}}
                      {{ scope.row.billing_cycle&&scope.row.amount? '/' + scope.row.billing_cycle
                                            : null}}
                    </span>
                  </template>
                </el-table-column>
                <el-table-column prop="host_status" :label="lang.finance_label4" width="150" align="left">
                  <template slot-scope="scope">

                    <el-tag v-if="scope.row.status" :class="scope.row.status=='Unpaid'?'Unpaid':scope.row.status=='Paid'?'Paid':''">
                      {{scope.row.status=='Unpaid'?lang.finance_text3:scope.row.status=='Paid'?lang.finance_text4:''}}
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
                        {{item.status=='Unpaid'?lang.finance_text3:item.status=='Paid'?lang.finance_text4:''}}
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
                {{lang.finance_title3}}
              </div>
              <div class="dialog-form">
                <el-form :model="txData" label-position="top">
                  <el-form-item :label="lang.finance_label13">
                    <el-select v-model="txData.method_id">
                      <el-option v-for="item in ruleData.method" :key="item.id" :label="item.name" :value="item.id"></el-option>
                    </el-select>
                  </el-form-item>
                  <el-form-item :label="lang.finance_label14" v-if="!showCard">
                    <el-input v-model="txData.account"></el-input>
                  </el-form-item>
                  <el-form-item :label="lang.finance_label15" v-if="showCard">
                    <el-input v-model="txData.card_number"></el-input>
                  </el-form-item>
                  <el-form-item :label="lang.finance_label16" v-if="showCard">
                    <el-input v-model="txData.name"></el-input>
                  </el-form-item>
                  <el-form-item :label="lang.finance_label17">
                    <el-input @keyup.native="txData.amount=oninput(txData.amount)" v-model="txData.amount" :placeholder="'可提现'+ commonData.currency_prefix + balance + commonData.currency_suffix">
                      <el-button type="text" slot="suffix" @click="txData.amount=balance">{{lang.finance_btn5}}
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
                <el-button class="btn-ok" @click="doCredit">{{lang.finance_btn6}}</el-button>
                <el-button class="btn-no" @click="isShowTx = false">{{lang.finance_btn7}}</el-button>
              </div>
            </el-dialog>
          </div>
          <!-- 充值 dialog -->
          <div class="cz-dialog">
            <el-dialog width="6.8rem" :visible.sync="isShowCz" :show-close=false @close="czClose">
              <div class="dialog-title">
                {{lang.finance_title4}}
              </div>
              <div class="dialog-form">
                <el-form :model="czData" label-position="top">
                  <!-- <el-form-item :label="lang.finance_label18">
                    <el-select v-model="czData.gateway" @change="czSelectChange">
                      <el-option v-for="item in gatewayList" :key="item.id" :label="item.title" :value="item.name"></el-option>
                    </el-select>
                  </el-form-item> -->
                  <el-form-item :label="lang.finance_label19" @keyup.native="czData.amount=oninput(czData.amount)" prop="amount">
                    <div class="cz-input">
                      <el-input v-model="czData.amount">
                      </el-input>
                      <el-button class="btn-ok" @click="czInputChange">{{lang.finance_btn6}}</el-button>
                    </div>
                  </el-form-item>
                  <!-- <el-form-item v-if="errText">
                    <el-alert :title="errText" type="error" :closable="false" show-icon>
                    </el-alert>
                  </el-form-item>
                  <el-form-item v-loading="payLoading1">
                    <div class="pay-html" v-show="isShowimg1" v-html="payHtml"></div>
                  </el-form-item> -->
                </el-form>
              </div>
            </el-dialog>
          </div>

          <!-- 删除确认 dialog -->
          <div class="delete-dialog">
            <el-dialog width="4.35rem" :visible.sync="isShowDeOrder" :show-close=false @close="isShowDeOrder=false">
              <div class="delete-box">
                <div class="delete-content">{{lang.finance_text7}}</div>
                <div class="delete-btn">
                  <span class="confirm-btn" @click=handelDeleteOrder>{{lang.finance_btn8}}</span>
                  <span class="cancel-btn" @click="isShowDeOrder=false">{{lang.finance_btn7}}</span>
                </div>
              </div>
            </el-dialog>
          </div>
          <pay-dialog ref="payDialog" @payok="paySuccess" @paycancel="payCancel"></pay-dialog>
        </el-main>
        <!-- 提现弹窗 -->
        <withdraw-dialog ref="withdrawDialog" @dowithdraw="dowithdraw"></withdraw-dialog>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/finance.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/finance.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/withdrawDialog/withdrawDialog.js"></script>

  {include file="footer"}