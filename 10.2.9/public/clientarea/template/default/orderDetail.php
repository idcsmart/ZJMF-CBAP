{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/orderDetail.css">
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
          <div class="order-detail">
            <div class="page-top">
              <div class="back-btn" @click="goBack">
                <img src="/{$template_catalog}/template/{$themes}/img/finance/back.png" alt="">
              </div>
              <div class="top-title">订单详情</div>

            </div>
            <div class="page-content" ref="orderPageRef">
              <div class="order-info">
                <div class="info-left">
                  <div class="order-user">{{orderData.client_name}}</div>
                  <div class="order-num">订单号：<span class="num-text">{{orderData.id}}</span></div>
                  <div class="order-data order-num">订单日期：<span class="num-text">{{orderData.create_time | formateTime }}</span></div>
                </div>
                <div class="info-right">
                  <div class="order-status">
                    <span class="Unpaid-text pay-status" v-if="orderData.status === 'Unpaid'">未付款</span>
                    <span class="Paid-text pay-status" v-if="orderData.status === 'Paid'">已支付</span>
                    <span class="Refunded-text pay-status" v-if="orderData.status === 'Refunded'">已退款</span>
                  </div>
                  <div class="go-pay">
                    <div ref="payBtnRef" class="pay-text" @click="goPay" v-if="orderData.status === 'Unpaid'">去支付</div>
                    <div class="pay-info" v-if="orderData.pay_time">{{orderData.pay_time | formateTime}}</div>
                    <div class="pay-info" v-if="orderData.status !== 'Unpaid' && orderData.gateway">
                      <span v-if="orderData.credit === orderData.amount">余额支付</span>
                      <span v-else-if="orderData.credit*1 > 0 && orderData.credit !== orderData.amount">余额 + {{orderData.gateway}}</span>
                      <span v-else>{{orderData.gateway}}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="order-table">
                <div class="table-title">订单详情</div>
                <div class="table-content">
                  <div class="table-item title-item">
                    <div class="des">描述</div>
                    <div class="des">金额</div>
                  </div>
                  <div class="table-item order-item" v-for="item in orderData.items" :key="item.id">
                    <div class="des-text">{{item.description}}</div>
                    <div class="money-text">{{commonData.currency_prefix}}{{item.amount}}</div>
                  </div>
                  <div class="table-item">
                    <div></div>
                    <div>
                      <span class="total-money">总额</span>
                      <span class="money-text">{{commonData.currency_prefix}}{{orderData.amount}}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="order-transaction">
                <!-- <el-table :data="transactionList">
                  <el-table-column prop="create_time" label="交易日期" width="200">
                    <template slot-scope="scope">{{ scope.row.create_time | formateTime}}</template>
                  </el-table-column>
                  <el-table-column prop="transaction_number" label="交易流水" align="center">
                    <template slot-scope="scope">{{ scope.row.transaction_number || '--'}}</template>
                  </el-table-column>
                  <el-table-column prop="amount" label="金额" width="200" align="right">
                    <template slot-scope="scope">{{commonData.currency_prefix}}{{ scope.row.amount }}</template>
                  </el-table-column>
                </el-table> -->
                <div class="table-top">
                  <div class="w-200">交易日期</div>
                  <div class="flex-1">交易流水</div>
                  <div class="w-200 text-r">金额</div>
                </div>
                <template v-if="transactionList.length >0">
                  <div v-for="item in transactionList" :key="item.id" class="table-bottom">
                    <div class="w-200">{{item.create_time | formateTime}}</div>
                    <div class="flex-1">{{item.transaction_number || '--'}}</div>
                    <div class="w-200 text-r">{{commonData.currency_prefix}}{{ item.amount }}</div>
                  </div>
                </template>
                <template v-else>
                  <div class="no-list">暂无数据</div>
                </template>
                <!-- <pagination style="margin-top: 15px;" v-if="params.total > 20" :page-data="params" @sizechange="sizeChange" @currentchange="currentChange"></pagination> -->
              </div>
            </div>
            <div class="down-pag">
              <el-button plain @click="handelPdf"><i class="el-icon-download el-icon--left"></i>下载</el-button>
            </div>
          </div>
        </el-main>
      </el-container>
    </el-container>
    <pay-dialog ref="payDialog" @payok="paySuccess" @paycancel="payCancel"></pay-dialog>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/orderDetail.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/html2pdf.bundle.min.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/orderDetail.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  {include file="footer"}