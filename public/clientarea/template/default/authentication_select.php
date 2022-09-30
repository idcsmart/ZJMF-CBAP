{include file="header"}
  <!-- 页面独有样式 -->
  <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/authentication.css">
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
              <div class="main-card-title"><img src="/{$template_catalog}/template/{$themes}/img/finance/back.png" class="top-back-img" @click="backTicket">实名认证</div>
              <div class="top-line"></div>
            </div>
            <!-- 选择认证方式页面 -->
            <div class="main-content">
              <div class="content-title">请选择实名认证类型</div>
              <div class="check-type">
                <div @click="clickType('1')">
                  <div v-if="certificationInfoObj.person.status === 2 || !certificationInfoObj.person.status">个人认证</div>
                  <div v-else>
                    <p class="green-text" v-if="certificationInfoObj.person.status === 1">已完成</p>
                    <p class="bule-text" v-if="certificationInfoObj.person.status === 3 || certificationInfoObj.person.status === 4">待人工复审</p>
                    <p class="font-14">个人认证</p>
                  </div>
                  <span class="checked-icon" v-show="authenticationType ==='1'"></span>
                </div>
                <div @click="clickType('2')">企业认证
                  <span class="checked-icon" v-show="authenticationType ==='2'"></span>
                </div>
              </div>
              <div class="check-mode">
                <div class="content-title">请选择实名认证方式</div>
                <div class="check-select">
                  <el-select v-model="checkedVlue" placeholder="系统默认方式"  @change="selectChange">
                    <el-option v-for="item in custom_fieldsList" :key="item.value" :label="item.label" :value="item.value">
                    </el-option>
                  </el-select>
                </div>
              </div>
              <div class="next-box">
                <el-button  @click="goUploadPage()">下一步</el-button>
              </div>
            </div>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/certification.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/authenticationSelect.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  {include file="footer"}