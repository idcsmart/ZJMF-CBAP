{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/template.css">
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
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/js/template.js"></script>
  {include file="footer"}