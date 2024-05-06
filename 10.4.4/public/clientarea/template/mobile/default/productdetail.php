{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/productdetail.css">
</head>

<body>
  <!-- mounted之前显示 -->
  <div class="product_detail template">
    <el-container>
      <aside-menu></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <el-main>
          <!-- 自己的东西 -->
          <!-- 后端渲染出来的配置页面 -->
          <div class="config-box" v-if="timeouted">
            <!-- 电子合同判断 -->
            <div class="contract-box" v-if="actStatus.includes('unable_access')">
              <div class="contract-top">
                <img @click="goBack" class="back-img"
                  src="/{$template_catalog}/template/{$themes}/img/finance/back.png" />
                <span class="top-product-name">{{hostData.product_name}}</span>
              </div>
              <div class="go-contract">
                <img class="contract-img" src="/{$template_catalog}/template/{$themes}/img/common/contract_img.png" />
                <div class="contract-text">{{lang.product_text1}}</div>
                <el-button class="contract-btn" @click="goContractDetail">{{lang.product_text2}}</el-button>
              </div>
            </div>
            <div class="content" v-else></div>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/js/common/jquery.mini.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/captchaDialog/captchaDialog.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/countDownButton/countDownButton.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/productdetail.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/discountCode/discountCode.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/cashCoupon/cashCoupon.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/cashBack/cashBack.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/flowPacket/flowPacket.js"></script>
  {include file="footer"}