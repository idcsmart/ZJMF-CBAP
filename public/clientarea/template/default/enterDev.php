{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/enterDev.css">
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
                    <div class="enterDev-content">
                      <header>开发者管理</header>
                      <div class="enterDev-main">
                        <div class="enterDev-left" style="height: 500px;">
                            <el-steps direction="vertical" :active="1">
                              <el-step>
                                <template slot="icon">
                                  
                                  <div class="text">
                                    1.入驻申请
                                  </div>
                                </template>
                              </el-step>
                              <el-step title="步骤 2"></el-step>
                              <el-step title="步骤 3" description="这是一段很长很长很长的描述性文字"></el-step>
                            </el-steps>
                        </div>
                        <div class="enterDev-right">
                          <img src="/{$template_catalog}/template/{$themes}/img/invoice/蒙版组 714.png" alt="">
                          <h1>入驻申请</h1>
                          <p>正在审核您的入驻申请，请耐心等候</p>
                          <button>重新提交资料</button>
                        </div>
                      </div>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/js/enterDev.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}