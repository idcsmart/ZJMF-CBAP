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
                    <div class="btn-cz" @click="showCz" v-if="commonData.recharge_open == 1">{{lang.finance_btn1}}</div>
                    {foreach $addons as $addon}
                    {if $addon['name']=='IdcsmartWithdraw'}
                    <div class="btn-tx" @click="showTx" v-if="isOpenwithdraw">{{lang.finance_btn2}}</div>

                    {/if}
                    {/foreach}
                    <template>
                      {foreach $addons as $addon}
                      {if $addon['name']=='IdcsmartWithdraw'}
                      <div class="tx-list" @click="goWithdrawal" v-if="isOpenwithdraw">
                        <el-badge :is-dot="isdot" class="tx-list">{{lang.finance_btn9}}</el-badge>
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
                          <el-button class="all-pay" @click="handelAllPay" :loading="allLoading"
                            v-if="isShowCombine">{{lang.finance_btn10}}</el-button>
                          <div v-for="(item,index) in tipslist1" class="tips_item" :key="index">
                            <span class="dot" :style="{'background':item.color}"></span>
                            <span>{{item.name}}</span>
                          </div>
                        </div>
                        <div class="searchbar com-search">
                          <el-input v-model="params1.keywords" style="width: 3.2rem;margin-left: .2rem;"
                            :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange1" clearable
                            @clear="getorderList">
                            <i class="el-icon-search input-search" slot="suffix" @Click="inputChange1"></i>
                          </el-input>
                        </div>
                      </div>
                      <div class="tabledata">
                        <el-table v-loading="loading1" @selection-change="handleSelectionChange" :data="dataList1"
                          style="width: 100%;margin-bottom: 20px;" :row-key="getRowKey" lazy :load="load"
                          :tree-props="{children: 'children', hasChildren: 'hasChildren'}">
                          <el-table-column type="selection" width="80" v-if="isShowCombine"
                            :reserve-selection="true"></el-table-column>
                          <el-table-column prop="id" label="ID" width="100" align="left">
                            <template slot-scope="scope">
                              <span class="a-text" @click="goOrderDetail(scope.row.id)">
                                {{scope.row.product_names ? scope.row.id : '--'}}
                              </span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="product_names" :label="lang.finance_label1" min-width="300"
                            :show-overflow-tooltip="true">
                            <template slot-scope="scope">
                              <span class="dot" :class="scope.row.type"></span>
                              <span v-if="scope.row.product_names" class="a-text"
                                @click="goOrderDetail(scope.row.id)">{{scope.row.product_name}}</span>
                              <span v-else>{{scope.row.product_name}}</span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="billing_cycle" :label="lang.finance_label2" width="200">
                            <template slot-scope="scope">
                              <span v-if="scope.row.status=='Unpaid'" @click="showPayDialog(scope.row)"
                                style="cursor: pointer;">
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
                              <el-tag v-if="scope.row.status && scope.row.status=='Unpaid'"
                                @click="showPayDialog(scope.row)" style="cursor: pointer;" class="Unpaid">
                                {{lang.finance_text3}}
                              </el-tag>
                              <!-- 已付款 -->
                              <el-tag v-if="scope.row.status && scope.row.status=='Paid'" class="Paid">
                                {{lang.finance_text4}}
                              </el-tag>
                              <!-- 已完成 -->
                              <el-tag v-if="scope.row.status && scope.row.status=='Refunded'" class="Refunded">
                                {{lang.finance_text17}}
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
                                  <div slot="reference" class="operation-item"
                                    @click="openDeletaDialog(scope.row,scope.$index)">{{lang.finance_btn4}}</div>
                                  <div class="operation-item" @click="showPayDialog(scope.row)">{{lang.finance_btn3}}
                                  </div>
                                </div>
                                <i slot="reference" class="el-icon-more"></i>
                              </el-popover>
                            </template>
                          </el-table-column>
                        </el-table>
                        <pagination :page-data="params1" @sizechange="sizeChange1" @currentchange="currentChange1">
                        </pagination>
                      </div>


                      <!-- 移动端显示表格开始 -->
                      <div class="mobel">
                        <div class="mob-searchbar mob-com-search">
                          <el-input class="mob-search-input" v-model="params1.keywords" :placeholder="lang.cloud_tip_2"
                            @keyup.enter.native="inputChange1" clearable @clear="getorderList">
                            <i class="el-icon-search input-search" slot="suffix" @Click="inputChange1"></i>
                          </el-input>
                        </div>
                        <div class="mob-tabledata">
                          <div class="mob-tabledata-item" v-for="item in dataList1" :key="item.id"
                            @click="showItem(item)">
                            <div class="mob-item-row mob-item-row1" @click="goOrderDetail(scope.row.id)">
                              <span>{{item.id}}</span>
                              <span>
                                <el-tag v-if="item.status"
                                  :class="item.status=='Unpaid'?'Unpaid':item.status=='Paid'?'Paid':''">
                                  {{item.status=='Unpaid'?lang.finance_text3:item.status=='Paid'?lang.finance_text4:''}}
                                </el-tag>
                              </span>
                            </div>
                            <div class="mob-item-row mob-item-row2" @click="goOrderDetail(scope.row.id)">
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
                        <img v-show="isShowBackTop" class="back-top-img" @click="goBackTop"
                          src="/{$template_catalog}/template/{$themes}/img/common/toTop.png">
                      </div>

                    </div>
                  </el-tab-pane>
                  <el-tab-pane :label="lang.finance_tab2" name="2" v-if="isShowTransactionController">
                    <div class="content_table">
                      <div class="content_searchbar">
                        <div class="left_tips">
                          <!--
                          <div v-for="(item,index) in tipslist1" class="tips_item" :key="index">
                            <span class="dot" :style="{'background':item.color}"></span>
                            <span>{{item.name}}</span>
                          </div> -->
                        </div>
                        <div class="searchbar com-search">
                          <el-input v-model="params2.keywords" style="width: 3.2rem;margin-left: .2rem;"
                            :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange2" clearable
                            @clear="getTransactionList">
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
                                <a v-if="scope.row.order_id !== '--'" class="a-text"
                                  @click="goOrderDetail(scope.row.order_id)">{{scope.row.order_id}}</a>
                                <span v-else>{{scope.row.order_id}}</span>
                              </div>
                            </template>
                          </el-table-column>
                          <el-table-column prop="type" width="150" :label="lang.finance_label22" align="left">
                            <template slot-scope="scope">
                              <span>{{orderTypeObj[scope.row.type] || '--'}}</span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="amount" min-width="150" :label="lang.finance_label8" align="left">
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
                          <el-table-column prop="gateway" width="300" :label="lang.finance_label5"
                            align="left"></el-table-column>
                          <el-table-column prop="transaction_number" width="300" :label="lang.finance_label9"
                            align="center" :show-overflow-tooltip="true">
                            <template slot-scope="scope">
                              <span>{{scope.row.transaction_number || '--'}}</span>
                            </template>
                          </el-table-column>
                        </el-table>
                        <pagination :page-data="params2" @sizechange="sizeChange2" @currentchange="currentChange2">
                        </pagination>
                      </div>

                      <!-- 移动端显示表格开始 -->
                      <div class="mobel">
                        <div class="mob-searchbar mob-com-search">
                          <el-input class="mob-search-input" v-model="params2.keywords" :placeholder="lang.cloud_tip_2"
                            @keyup.enter.native="inputChange2" clearable @clear="getTransactionList">
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
                                  <a v-if="item.order_id !== '--'" class="a-text"
                                    @click="goOrderDetail(item.order_id)">{{item.order_id}}</a>
                                  <span v-else class="a-text"
                                    @click="goOrderDetail(item.order_id)">{{item.order_id}}</span>
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
                        <img v-show="isShowBackTop" class="back-top-img" @click="goBackTop"
                          src="/{$template_catalog}/template/{$themes}/img/common/toTop.png">
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
                        <div class="box" style="display:flex; overflow-y: auto;">
                          <el-date-picker @change="inputChange3" v-model="date" type="daterange"
                            :range-separator=lang.finance_text18 style="width:3.5rem;margin-right:0.14rem"
                            :start-placeholder="lang.finance_text19" value-format="timestamp" align="center"
                            :end-placeholder="lang.finance_text20">
                          </el-date-picker>
                          <el-select v-model="params3.type" :placeholder="lang.finance_text21" style="width:2rem"
                            @change="inputChange3">
                            <el-option :label="balanceType.Recharge.text"
                              value="Recharge">{{balanceType.Recharge.text}}</el-option>
                            <el-option :label="balanceType.Applied.text"
                              value="Applied">{{balanceType.Applied.text}}</el-option>
                            <el-option :label="balanceType.Refund.text"
                              value="Refund">{{balanceType.Refund.text}}</el-option>
                            <el-option :label="balanceType.Withdraw.text"
                              value="Withdraw">{{balanceType.Withdraw.text}}</el-option>
                          </el-select>
                          <el-input v-model="params3.keywords" style="width: 3.2rem;margin-left: .2rem;"
                            :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange3" clearable
                            @clear="getCreditList">
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
                          <el-table-column prop="notes" :label="lang.finance_label10" align="left"
                            :show-overflow-tooltip="true">
                          </el-table-column>
                          <el-table-column prop="type" :label="lang.finance_label11" width="150" align="left">
                            <template slot-scope="scope">
                              <span class="balance-tag"
                                :class="scope.row.type">{{balanceType[scope.row.type]?.text || '--'}}</span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="create_time" width="200" :label="lang.finance_label3" align="left">
                            <template slot-scope="scope">
                              <span>{{scope.row.create_time | formateTime}}</span>
                            </template>
                          </el-table-column>
                        </el-table>
                        <pagination :page-data="params3" @sizechange="sizeChange3" @currentchange="currentChange3">
                        </pagination>
                      </div>

                      <!-- 移动端显示表格开始 -->
                      <div class="mobel">
                        <div class="mob-searchbar mob-com-search">
                          <el-input class="mob-search-input" v-model="params3.keywords" :placeholder="lang.cloud_tip_2"
                            @keyup.enter.native="inputChange3" clearable @clear="getCreditList">
                            <i class="el-icon-search input-search" slot="suffix" @Click="inputChange3"></i>
                          </el-input>
                        </div>
                        <div class="mob-tabledata">
                          <div class="mob-tabledata-item" v-for="item in dataList3" :key="item.id">
                            <div class="mob-item-row mob-item-row1">
                              <span>{{item.id}}</span>
                              <span>
                                <span class="balance-tag"
                                  :class="item.type">{{balanceType[item.type]?.text || '--'}}</span>
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
                        <img v-show="isShowBackTop" class="back-top-img" @click="goBackTop"
                          src="/{$template_catalog}/template/{$themes}/img/common/toTop.png">
                      </div>
                    </div>
                  </el-tab-pane>
                  <el-tab-pane :label="lang.finance_text22" name="4" v-if="isShowCash">
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
                                  <div class="price"
                                    :class="{used: item.status === 'used',overdue: item.status === 'expired' }">
                                    <span>{{commonData.currency_prefix}}</span>
                                    <span class="num">{{item.price}}</span>
                                    <p class="des">{{lang.voucher_min}}：{{commonData.currency_prefix}}{{item.min_price}}
                                    </p>
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
                                <div class="bg"
                                  :class="{used: item.status === 'used', overdue: item.status === 'expired'}"></div>
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
                                    <p class="des">{{lang.voucher_min}}：{{commonData.currency_prefix}}{{item.min_price}}
                                    </p>
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

                      <pagination :page-data="vParams" v-if="vParams.total" @sizechange="sizeChange"
                        @currentchange="currentChange" class="voucher-page">
                      </pagination>
                    </div>
                  </el-tab-pane>
                  <el-tab-pane :label="lang.finance_text23" name="5" v-if="isShowContract">
                    <div class="content_table">
                      <div class="content_searchbar balance-searchbar">
                        <div class="left_tips">
                          <el-button @click="handeInfo"
                            style="color: #fff; background-color: rgba(0, 88, 255, 1);">{{lang.finance_text24}}</el-button>
                          <el-button @click="handelApplyOrder"
                            style="color: rgba(0, 88, 255, 1);  border: 0.01rem solid #0058FF;">{{lang.finance_text25}}</el-button>
                        </div>
                        <div class="box" style="display:flex; overflow-y: auto;">
                          <el-input v-model="params4.keywords" style="width: 3.2rem;margin-left: .2rem;"
                            :placeholder="lang.finance_text26" @keyup.enter.native="inputChange4" clearable
                            @clear="getContractList">
                          </el-input>
                          <el-button style="margin-left: 0.1rem; color: #fff; background-color: rgba(0, 88, 255, 1);"
                            @Click="inputChange4">{{lang.finance_text27}}</el-button>
                        </div>
                      </div>
                      <div class="tabledata">
                        <el-table v-loading="loading5" :data="dataList5" style="width: 100%;margin-bottom: .2rem;">
                          <el-table-column prop="id" :label="lang.finance_text28" width="200" align="left">
                          </el-table-column>
                          <el-table-column :label="lang.finance_text29" align="left" :show-overflow-tooltip="true">
                            <template slot-scope="scope">
                              <span v-if="scope.row.base_contract === 1">{{lang.finance_text30}}</span>
                              <span v-else>{{handelHostName(scope.row.host)}}</span>
                            </template>
                          </el-table-column>
                          <el-table-column :label="lang.finance_text31" width="200" align="left">
                            <template slot-scope="scope">
                              <div style="display: flex; align-items: center;">
                                <span :class="scope.row.status === 'no_sign'  ? 'has-border': '' "
                                  class="contract-status"
                                  :style="{'color':contractStatusObj[scope.row.status].color,'background':contractStatusObj[scope.row.status].background}">{{contractStatusObj[scope.row.status].label}}</span>
                                <el-popover placement="top-start" trigger="hover">
                                  <div slot="reference" style="display: flex; align-items: center;">
                                    <svg v-if="scope.row.status === 'reject' || scope.row.status === 'cancel'"
                                      t="1681982176042" class="help-icon" viewBox="0 0 1024 1024" version="1.1"
                                      xmlns="http://www.w3.org/2000/svg" p-id="8315" width="18" height="18">
                                      <path
                                        d="M511.333 63.333c-247.424 0-448 200.576-448 448s200.576 448 448 448 448-200.576 448-448-200.576-448-448-448z m271.529 719.529c-35.286 35.287-76.359 62.983-122.078 82.321-47.3 20.006-97.583 30.15-149.451 30.15-51.868 0-102.15-10.144-149.451-30.15-45.719-19.337-86.792-47.034-122.078-82.321-35.287-35.286-62.983-76.359-82.321-122.078-20.006-47.3-30.15-97.583-30.15-149.451s10.144-102.15 30.15-149.451c19.337-45.719 47.034-86.792 82.321-122.078 35.286-35.287 76.359-62.983 122.078-82.321 47.3-20.006 97.583-30.15 149.451-30.15 51.868 0 102.15 10.144 149.451 30.15 45.719 19.337 86.792 47.034 122.078 82.321 35.287 35.286 62.983 76.359 82.321 122.078 20.006 47.3 30.15 97.583 30.15 149.451s-10.144 102.15-30.15 149.451c-19.337 45.719-47.034 86.792-82.321 122.078z"
                                        fill="#0058FF" p-id="8316"></path>
                                      <path
                                        d="M642.045 285.629c-26.482-39.772-72.632-61.676-129.945-61.676-45.43 0-81.73 14.938-107.891 44.4-21.679 24.415-34.958 57.378-39.469 97.974-3.153 28.378-0.747 50.163-0.462 52.553 2.091 17.549 18.02 30.084 35.56 27.991 17.549-2.09 30.081-18.011 27.991-35.56-0.019-0.161-1.845-16.636 0.52-37.916 2.077-18.688 7.877-44.708 23.717-62.547 13.679-15.406 33.317-22.895 60.034-22.895 35.722 0 62.235 11.462 76.675 33.147 15.268 22.93 14.215 52.064 6.398 70.765-4.475 10.704-25.708 30.276-42.77 46.002-35.924 33.111-73.07 67.349-73.07 108.723v61.457c0 17.673 14.327 32 32 32s32-14.327 32-32V546.59c0-0.684 0.354-7.103 12.607-21.925 10.498-12.696 25.413-26.444 39.837-39.739 24.981-23.025 48.576-44.774 58.443-68.379 8.323-19.912 11.834-42.319 10.153-64.799-1.794-24.014-9.516-46.877-22.328-66.119z"
                                        fill="#0058FF" p-id="8317"></path>
                                      <path d="M512.099 702.965m-40 0a40 40 0 1 0 80 0 40 40 0 1 0-80 0Z" fill="#0058FF"
                                        p-id="8318"></path>
                                    </svg>
                                  </div>
                                  <span v-if="scope.row.status === 'reject'">{{scope.row.reason}}</span>
                                </el-popover>
                                <img v-if="scope.row.status === 'complete' && scope.row.post_number" class="help-icon"
                                  @click="handelRec(scope.row)" style="width: 0.18rem;height: 0.18rem;"
                                  src="/{$template_catalog}/template/{$themes}/img/finance/icon_1.png"
                                  :alt="lang.finance_text32">
                              </div>
                            </template>
                          </el-table-column>
                          <el-table-column :label="lang.finance_label6" width="100" align="left">
                            <template slot-scope="scope">
                              <el-popover placement="top-start" trigger="hover">
                                <i slot="reference" class="el-icon-more"></i>
                                <div class="operation-box">
                                  <div class="operation-item" @click="handelSign(scope.row.order_id)"
                                    v-if="scope.row.status === 'no_sign'">{{lang.finance_text33}}</div>
                                  <div class="operation-item" @click="handelDetail(scope.row.id)"
                                    v-if="scope.row.status === 'review'">{{lang.finance_text34}}</div>
                                  <div @click="handelCancel(scope.row.id)" class="operation-item"
                                    v-if="scope.row.status === 'review'">{{lang.finance_text35}}</div>
                                  <div class="operation-item" @click="handelPreview(scope.row.id)"
                                    v-if="scope.row.status === 'complete' || scope.row.status === 'wait_mail'">
                                    {{lang.invoice_text41}}
                                  </div>
                                  <div @click="handelDownload(scope.row.id)" class="operation-item"
                                    v-if="scope.row.status === 'complete' || scope.row.status === 'wait_mail'">
                                    {{lang.finance_text36}}
                                  </div>
                                  <div @click="handelMail(scope.row.id)" class="operation-item"
                                    v-if="scope.row.status === 'complete' && !scope.row.post_number">
                                    {{lang.finance_text37}}
                                  </div>
                                  <div class="operation-item"
                                    v-if="scope.row.status === 'reject' || scope.row.status === 'cancel'">--</div>
                                </div>
                              </el-popover>
                            </template>
                          </el-table-column>
                        </el-table>
                        <pagination :page-data="params4" @sizechange="sizeChange4" @currentchange="currentChange4">
                        </pagination>
                      </div>
                    </div>
                  </el-tab-pane>
                  <el-tab-pane :label="lang.finance_text38" name="6"
                    v-if="isShowCredit && creditData?.credit_limit*1 > 0">
                    <div class="credit-content">
                      <div class="credit-top">
                        <div class="credit-item">
                          <div class="item-top">
                            <div class="item-l">{{lang.finance_text39}}({{commonData.currency_suffix}})</div>
                            <div class="item-r">{{creditData.end_time | formateTime3}}{{lang.finance_text40}}</div>
                          </div>
                          <div class="item-bottom">
                            <div class="item-bl">
                              {{commonData.currency_prefix}}{{creditData.credit_limit | formatNumber}}
                            </div>
                          </div>
                        </div>
                        <div class="credit-item">
                          <div class="item-top">
                            <div class="item-l">{{lang.finance_text41}}({{commonData.currency_suffix}})</div>
                            <div class="item-r">
                              <span class="label-box"
                                :class="creditData.status !== 'Active' ? 'no-active' : 'is-active'">{{credit_status[creditData.status]}}</span>
                            </div>
                          </div>
                          <div class="item-bottom">
                            <div class="item-bl">
                              {{commonData.currency_prefix}}{{creditData.remaining_amount | formatNumber}}
                            </div>
                          </div>
                        </div>
                        <div class="credit-item">
                          <div class="item-top" style="align-items: start;">
                            <div class="item-l">{{lang.finance_text42}}({{commonData.currency_suffix}})</div>
                            <div class="item-r" style="text-align: right;">
                              <div>{{lang.finance_text43}}：{{commonData.currency_prefix}}{{creditData.used |
                                formatNumber}}</div>
                              <div v-if="creditData.account?.repayment_time">
                                {{lang.finance_text44}}：{{creditData.account?.repayment_time | formateTime3}}
                              </div>
                            </div>
                          </div>
                          <div class="item-bottom flex-bottom">
                            <div class="item-bl">
                              {{commonData.currency_prefix}}{{creditData.account?.amount | formatNumber}}
                            </div>
                            <el-button class="no-btn"
                              v-if="creditData.account?.status === 'Outstanding'">{{lang.finance_text45}}</el-button>
                            <el-button class="credit-btn"
                              v-if="creditData.account?.status !== 'Repaid' &&  creditData.account?.status !== 'Outstanding'"
                              @click="handelPayCredit(creditData.account?.order_id)">{{lang.finance_text46}}</el-button>
                          </div>
                        </div>
                      </div>
                      <div class="tabledata">
                        <el-table v-loading="loading6" :data="dataList6" style="width: 100%;margin-bottom: .2rem;">
                          <el-table-column :label="lang.finance_text47" width="500" align="left">
                            <template slot-scope="scope">
                              {{scope.row.start_time | formateTime2}}- {{scope.row.end_time | formateTime2}}
                            </template>
                          </el-table-column>
                          <el-table-column :label="lang.finance_text48" align="left" :show-overflow-tooltip="true">
                            <template slot-scope="scope">
                              <span>{{commonData.currency_prefix}}{{scope.row.amount | formatNumber}}</span>
                            </template>
                          </el-table-column>
                          <el-table-column :label="lang.finance_label4" width="200" align="left">
                            <template slot-scope="scope">
                              <span class="contract-status"
                                :style="{'color':creditStatusObj[scope.row.status].color,'background':creditStatusObj[scope.row.status].background}">{{creditStatusObj[scope.row.status].label}}</span>
                            </template>
                          </el-table-column>
                          <el-table-column :label="lang.finance_label6" width="100" align="left">
                            <template slot-scope="scope">
                              <el-popover placement="top-start" trigger="hover">
                                <i slot="reference" class="el-icon-more"></i>
                                <div class="operation-box">
                                  <div class="operation-item" @click="handelCredit(scope.row.id)">
                                    {{lang.finance_text49}}
                                  </div>
                                  <div class="operation-item" @click="handelPayCredit(scope.row?.order_id)"
                                    v-if="scope.row.status === 'Disbursed' || scope.row.status === 'Overdue'">
                                    {{lang.finance_text50}}
                                  </div>
                                </div>
                              </el-popover>
                            </template>
                          </el-table-column>
                        </el-table>
                        <pagination :page-data="params6" @sizechange="sizeChange6" @currentchange="currentChange6">
                        </pagination>
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
              <div class="top-l"> <img src="/{$template_catalog}/template/{$themes}/img/finance/back.png"
                  class="top-img" @click="isDetail=false"> {{lang.finance_title2}}</div>
            </div>
            <div class="top-line"></div>
            <div class="main_table">
              <el-table v-loading="loading4" :data="dataList4" style="width: 100%;" :tree-props="{children:'items'}"
                row-key="id" :default-expand-all="true">
                <el-table-column prop="product_name" :label="lang.finance_label1" min-width="500" align="left">
                  <template slot-scope="scope">
                    <span>{{ scope.row.product_name?scope.row.product_name:'--'}}</span>
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
                    <span>{{scope.row.amount? commonData.currency_prefix + scope.row.amount + commonData.currency_suffix :null}}{{ scope.row.billing_cycle&&scope.row.amount? '/' + scope.row.billing_cycle: null}}</span>
                  </template>
                </el-table-column>
                <el-table-column prop="host_status" :label="lang.finance_label4" width="150" align="left">
                  <template slot-scope="scope">
                    <el-tag v-if="scope.row.status"
                      :class="scope.row.status=='Unpaid'?'Unpaid':scope.row.status=='Paid'?'Paid':''">
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
                    <span>{{item.amount? commonData.currency_prefix + item.amount + commonData.currency_suffix : null}}{{ item.billing_cycle&&item.amount? '/' + item.billing_cycle : null}}</span>
                  </div>
                  <div class="mob-item-row mob-item-row-child">
                    <div class="child-row" v-for="child in item.items" :key="child.id">
                      <span class="child-row-name">{{ child.product_name?child.product_name:'--'}}</span>
                      <span>{{child.amount? commonData.currency_prefix + child.amount + commonData.currency_suffix : null}}{{ child.billing_cycle&&child.amount? '/' + child.billing_cycle: null}}</span>
                      <span>{{child.host_status?status[child.host_status]:null}}{{child.host_status||child.status ? null : '--'}}</span>
                    </div>
                  </div>
                  <div class="mob-item-row mob-item-row3">
                    <span>{{item.create_time | formateTime}}</span>
                    <div></div>
                  </div>
                </div>
              </div>
            </div>

          </div>
          <!-- 申请提现 dialog -->
          <div class="tx-dialog">
            <el-dialog width="6.8rem" :visible.sync="isShowTx" :show-close="false" :close-on-click-modal=false>
              <div class="dialog-title">
                {{lang.finance_title3}}
              </div>
              <div class="dialog-form">
                <el-form :model="txData" label-position="top">
                  <el-form-item :label="lang.finance_label13">
                    <el-select v-model="txData.method_id">
                      <el-option v-for="item in ruleData.method" :key="item.id" :label="item.name"
                        :value="item.id"></el-option>
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
                    <el-input @keyup.native="txData.amount=oninput(txData.amount)" v-model="txData.amount"
                      :placeholder="'可提现'+ commonData.currency_prefix + balance + commonData.currency_suffix">
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
            <el-dialog width="6.8rem" :visible.sync="isShowCz" @close="czClose">
              <div class="dialog-title">{{lang.finance_title4}}</div>
              <div class="dialog-form">
                <el-form :model="czData" label-position="top" @submit.native.prevent>
                  <el-form-item :label="lang.finance_label19" @keyup.native="czData.amount=oninput(czData.amount)"
                    prop="amount">
                    <div class="cz-input">
                      <el-input v-model="czData.amount"></el-input>
                      <el-button class="btn-ok" @click="czInputChange" type="button">{{lang.finance_btn6}}</el-button>
                    </div>
                  </el-form-item>
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
        <!-- 快递信息弹窗 -->
        <el-dialog width="6.8rem" :visible.sync="isShowKd" :show-close="false" @close="kdClose" class="kd-dialog">
          <div class="dialog-title">{{lang.finance_text51}}</div>
          <div class="dialog-dec">{{lang.finance_text52}}</div>
          <div class="dialog-box">
            <div class="kd-item"><span class="kd-label">{{lang.finance_text53}}:</span><span
                class="kd-value">{{recData.courier_company}}</span></div>
            <div class="kd-item"><span class="kd-label">{{lang.finance_text54}}:</span><span
                class="kd-value">{{recData.post_number}}</span></div>
            <div class="kd-item"><span class="kd-label">{{lang.finance_text55}}:</span><span
                class="kd-value">{{recData.rec_address}}</span></div>
            <div class="kd-item"><span class="kd-label">{{lang.finance_text56}}:</span><span
                class="kd-value">{{recData.rec_phone}}</span></div>
            <div class="kd-item"><span class="kd-label">{{lang.finance_text57}}:</span><span
                class="kd-value">{{recData.rec_person}}</span></div>
          </div>
          <div class="dialog-fotter">
            <el-button @click="kdClose">{{lang.finance_text58}}</el-button>
          </div>
        </el-dialog>
        <!-- 甲方信息管理弹窗 -->
        <el-dialog width="6.8rem" :visible.sync="isShowInfoDia" :show-close="false" @close="infoClose"
          class="info-dialog">
          <div class="dialog-title">{{lang.finance_text59}}</div>
          <div class="dialog-dec">
            <p>{{lang.finance_text60}}</p>
            <p>{{lang.finance_text61}}</p>
          </div>
          <div class="dialog-box">
            <el-form :model="infoFormData" class="info-form" :rules="infoRules" ref="infoForm" label-position="top">
              <!-- <div class="certification-info" v-if="false">
                <div class="kd-item"><span class="kd-label">{{lang.finance_text62}}:</span>
                  <span class="kd-value" v-if="certificationObj.company.status === 1">{{certificationObj.company.certification_company}}</span>
                  <span class="kd-value" v-else-if="certificationObj.person.status === 1">{{certificationObj.person.card_name}}</span>
                </div>
                <div class="kd-item"><span class="kd-label">{{lang.finance_text63}}:</span>
                  <span class="kd-value" v-if="certificationObj.company.status === 1">{{certificationObj.company.company_organ_code}}</span>
                  <span class="kd-value" v-else-if="certificationObj.person.status === 1">{{certificationObj.person.card_number}}</span>
                </div>
              </div> -->
              <el-form-item :label="lang.finance_text64" prop="name">
                <el-input v-model="infoFormData.name" :placeholder="lang.finance_text65"></el-input>
              </el-form-item>
              <el-form-item :label="lang.finance_text66" prop="id_number">
                <el-input v-model="infoFormData.id_number" :placeholder="lang.finance_text65"></el-input>
              </el-form-item>
              <el-form-item :label="lang.finance_text67" prop="contact_phone">
                <el-input v-model="infoFormData.contact_phone" :placeholder="lang.finance_text65"></el-input>
              </el-form-item>
              <el-form-item :label="lang.finance_text68" prop="contact_email">
                <el-input v-model="infoFormData.contact_email" :placeholder="lang.finance_text65"></el-input>
              </el-form-item>
              <el-form-item :label="lang.finance_text69" prop="contact_address">
                <el-input v-model="infoFormData.contact_address" :placeholder="lang.finance_text65"></el-input>
              </el-form-item>
            </el-form>
          </div>
          <div class="dialog-fotter">
            <el-button class="save-btn" @click="saveInfoData">{{lang.finance_text70}}</el-button>
            <el-button class="cancel-btn" @click="infoClose">{{lang.finance_text71}}</el-button>
          </div>
        </el-dialog>
        <!-- 取消申请弹窗 -->
        <el-dialog width="4.5rem" :visible.sync="isShowCancel" :show-close="false" @close="cancelClose"
          class="cancel-dialog">
          <div class="dialog-title">{{lang.finance_text72}}</div>
          <div class="dialog-dec">{{lang.finance_text73}}</div>
          <div class="dialog-fotter">
            <el-button class="save-btn" @click="saveCancel">{{lang.finance_text74}}</el-button>
            <el-button class="cancel-btn" @click="cancelClose">{{lang.finance_text75}}</el-button>
          </div>
        </el-dialog>
        <!-- 申请纸质合同弹窗 -->
        <el-dialog width="6.8rem" :visible.sync="isShowMailDia" :show-close="false" @close="MailClose"
          class="mail-dialog">
          <div class="dialog-title">{{lang.finance_text76}}</div>
          <div class="dialog-dec">
            {{lang.finance_text77}}
          </div>
          <div class="dialog-box">
            <el-form :model="mailFormData" class="info-form" :rules="mailRules" ref="mailForm" label-position="top">
              <el-form-item :label="lang.finance_text78" prop="rec_person">
                <el-input v-model="mailFormData.rec_person" :placeholder="lang.finance_text65"></el-input>
              </el-form-item>
              <el-form-item :label="lang.finance_text79" prop="rec_address">
                <el-input v-model="mailFormData.rec_address" :placeholder="lang.finance_text65"></el-input>
              </el-form-item>
              <el-form-item :label="lang.finance_text80" prop="rec_phone">
                <el-input v-model="mailFormData.rec_phone" :placeholder="lang.finance_text65"></el-input>
              </el-form-item>
            </el-form>
          </div>
          <div class="dialog-fotter">
            <div class="fotter-left">
              {{lang.finance_text81}}：<span class="price-blue">￥20.00</span>
            </div>
            <div style="display: flex;">
              <el-button class="save-btn" @click="saveMailData">{{lang.finance_text82}}</el-button>
              <el-button class="cancel-btn" @click="MailClose">{{lang.finance_text83}}</el-button>
            </div>
          </div>
        </el-dialog>
        <!-- 消费记录弹窗 -->
        <el-dialog width="12rem" :visible.sync="isShowCreditDia" :show-close="false" @close="creditClose"
          class="mail-dialog creat-dia">
          <div class="dialog-title">{{lang.finance_text84}}</div>
          <div class="dialog-tips dialog-dec">
            <div v-for="(item,index) in tipslist1" class="tips_item" :key="index">
              <span class="dot" :style="{'background':item.color}"></span>
              <span>{{item.name}}</span>
            </div>
          </div>
          <div class="dialog-box" style="margin-top: 0.2rem;">
            <el-table v-loading="loading7" :data="dataList7" style="width: 100%;margin-bottom: 20px;"
              :row-key="getRowKey" lazy :load="load" :tree-props="{children: 'children', hasChildren: 'hasChildren'}">
              <el-table-column prop="id" label="ID" width="90" align="left">
                <template slot-scope="scope">
                  <span class="a-text" @click="goOrderDetail(scope.row.id)">
                    {{scope.row.product_names ? scope.row.id : '--'}}</span>
                </template>
              </el-table-column>
              <el-table-column prop="product_names" :label="lang.finance_label1" min-width="250"
                :show-overflow-tooltip="true">
                <template slot-scope="scope">
                  <span class="dot" :class="scope.row.type"></span>
                  <el-tooltip placement="top" v-if="scope.row.description">
                    <div slot="content">{{scope.row.description}}</div>
                    <span style="cursor: pointer;" class="a-text"
                      @click="goOrderDetail(scope.row.id)">{{scope.row.product_name}}</span>
                  </el-tooltip>
                  <span v-else class="a-text" @click="goOrderDetail(scope.row.id)">{{scope.row.product_name}}</span>
                </template>
              </el-table-column>
              <el-table-column prop="billing_cycle" :label="lang.finance_label2" width="150">
                <template slot-scope="scope">
                  <span>{{ commonData.currency_prefix + scope.row.amount}}</span>
                  <span v-if="scope.row.billing_cycle">/{{scope.row.billing_cycle}}</span>
                </template>
              </el-table-column>
              <el-table-column prop="pay_time" :label="lang.finance_text85" width="200">
                <template slot-scope="scope">
                  <span>{{scope.row.pay_time | formateTime}}</span>
                </template>
              </el-table-column>
              <el-table-column prop="status" :label="lang.finance_label4" width="150">
                <template slot-scope="scope">
                  <!-- 未付款 -->
                  <el-tag v-if="scope.row.status && scope.row.status=='Unpaid'" style="cursor: pointer;" class="Unpaid">
                    {{lang.finance_text3}}
                  </el-tag>
                  <!-- 已付款 -->
                  <el-tag v-if="scope.row.status && scope.row.status=='Paid'" class="Paid">
                    {{lang.finance_text4}}
                  </el-tag>
                  <!-- 已完成 -->
                  <el-tag v-if="scope.row.status && scope.row.status=='Refunded'" class="Refunded">
                    {{lang.finance_text17}}
                  </el-tag>
                  {{scope.row.host_status?status[scope.row.host_status]:null}}
                  {{scope.row.host_status || scope.row.status ? null : '--'}}
                </template>
              </el-table-column>
              <el-table-column prop="gateway" :label="lang.finance_label5" width="180">
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
                            <span style="color: #F99600;">
                              {{commonData.currency_prefix + scope.row.credit + commonData.currency_suffix}}</span>
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
            </el-table>
            <pagination :page-data="params7" @sizechange="sizeChange7" @currentchange="currentChange7"></pagination>
          </div>
          <div class="dialog-fotter">
            <div></div>
            <el-button class="save-btn" @click="creditClose">{{lang.finance_text86}}</el-button>
          </div>
        </el-dialog>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/finance.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/finance.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/withdrawDialog/withdrawDialog.js"></script>

  {include file="footer"}