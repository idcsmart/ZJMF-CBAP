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
              <div class="top-title">{{lang.order_text1}}</div>

            </div>
            <div class="page-content" ref="orderPageRef">
              <div class="order-info">
                <div class="info-left">
                  <div class="order-user">{{orderData.client_name}}</div>
                  <div class="order-num">{{lang.order_text2}}<span class="num-text">{{orderData.id}}</span></div>
                  <div class="order-data order-num">{{lang.order_text3}}<span class="num-text">{{orderData.create_time | formateTime }}</span></div>
                </div>
                <div class="info-right">
                  <div class="order-status">
                    <span class="Unpaid-text pay-status" v-if="orderData.status === 'Unpaid'">{{lang.order_text4}}</span>
                    <span class="Paid-text pay-status" v-if="orderData.status === 'Paid'">{{lang.order_text5}}</span>
                    <span class="Refunded-text pay-status" v-if="orderData.status === 'Refunded'">{{lang.order_text6}}</span>
                  </div>
                  <div class="go-pay">
                    <div ref="payBtnRef" class="pay-text" @click="goPay" v-if="orderData.status === 'Unpaid'">{{lang.order_text7}}</div>
                    <div class="pay-info" v-if="orderData.pay_time">{{orderData.pay_time | formateTime}}</div>
                    <div class="pay-info" v-if="orderData.status !== 'Unpaid' && orderData.gateway">
                      <span v-if="orderData.credit === orderData.amount">{{lang.order_text8}}</span>
                      <span v-else-if="orderData.credit*1 > 0 && orderData.credit !== orderData.amount">{{lang.order_text9}} + {{orderData.gateway}}</span>
                      <span v-else>{{orderData.gateway}}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="order-table">
                <div class="table-title">{{lang.order_text1}}</div>
                <div class="table-content">
                  <div class="table-item title-item">
                    <div class="des">{{lang.order_text10}}</div>
                    <div class="des">{{lang.order_text11}}</div>
                  </div>
                  <div class="table-item order-item" v-for="item in orderData.items" :key="item.id">
                    <div class="des-text">{{item.description}}</div>
                    <div class="money-text">{{commonData.currency_prefix}}{{item.amount}}</div>
                  </div>
                  <div class="table-item">
                    <div></div>
                    <div>
                      <span class="total-money">{{lang.order_text12}}</span>
                      <span class="money-text">{{commonData.currency_prefix}}{{orderData.amount}}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="order-transaction">
                <div class="table-top">
                  <div class="w-200">{{lang.order_text13}}</div>
                  <div class="flex-1">{{lang.order_text14}}</div>
                  <div class="w-200 text-r">{{lang.order_text11}}</div>
                </div>
                <template v-if="transactionList.length >0">
                  <div v-for="item in transactionList" :key="item.id" class="table-bottom">
                    <div class="w-200">{{item.create_time | formateTime}}</div>
                    <div class="flex-1">{{item.transaction_number || '--'}}</div>
                    <div class="w-200 text-r">{{commonData.currency_prefix}}{{ item.amount }}</div>
                  </div>
                </template>
                <template v-else>
                  <div class="no-list">{{lang.order_text15}}</div>
                </template>
                <!-- <pagination style="margin-top: 15px;" v-if="params.total > 20" :page-data="params" @sizechange="sizeChange" @currentchange="currentChange"></pagination> -->
              </div>
            </div>
            <div class="down-pag">
              <el-button plain @click="handelPdf"><i class="el-icon-download el-icon--left"></i>{{lang.order_text16}}</el-button>
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