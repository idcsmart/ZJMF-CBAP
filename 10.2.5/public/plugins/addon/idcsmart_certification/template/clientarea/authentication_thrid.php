  <!-- 页面独有样式 -->
  <link rel="stylesheet" href="/plugins/addon/idcsmart_certification/template/clientarea/css/authentication.css">
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
          <div class="main-card">
            <div class="main-top">
              <div class="main-card-title"><img src="/plugins/addon/idcsmart_certification/template/clientarea/img/finance/back.png" class="top-back-img" @click="backTicket">实名认证</div>
              <div class="top-line"></div>
            </div>
            <!-- 选择认证方式页面 -->
            <div class="third-box" id="third-box"></div>
            <div class="go-Back-Btn">
              <el-button  @click="goSelect" class="back-btn">上一步</el-button>
            </div>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/plugins/addon/idcsmart_certification/template/clientarea/api/certification.js"></script>
  <script src="/plugins/addon/idcsmart_certification/template/clientarea/js/authenticationThrid.js"></script>
  <script src="/plugins/addon/idcsmart_certification/template/clientarea/utils/util.js"></script>
  <script src="https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js"></script>