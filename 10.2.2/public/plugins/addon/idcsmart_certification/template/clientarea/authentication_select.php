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
            <div class="main-content">
              <div class="content-title">认证类型</div>
              <div class="check-type">
                <div @click="clickType('1')" class="person-box" :class="authenticationType === '1' ? 'select-type' : ''">
                  <div class="type-img">
                    <img src="/plugins/addon/idcsmart_certification/template/clientarea/img/account/personal_icon.png" alt="">
                  </div>
                  <p class="type-title font-18">个人认证</p>
                  <div class="type-tips">个人实名认证适用于个人用户，账号归属个人，认证时请提供真实姓名、证件号码、证件照等个人资料，并确保提供的资料真实有效。</div>
                  <div class="presonl-status person-success" v-if="certificationInfoObj.person.status === 1"><i class="el-icon-success"></i> 已认证</div>
                  <div class="presonl-status person-loading" v-else-if="certificationInfoObj.person.status === 3 || certificationInfoObj.person.status === 4">待人工复审</div>
                  <span class="checked-icon" v-show="authenticationType ==='1'"></span>
                </div>
                <div @click="clickType('2')" :class="authenticationType === '2' ? 'select-type' : ''">
                  <div class="type-img">
                      <img src="/plugins/addon/idcsmart_certification/template/clientarea/img/account/compony_icon.png" alt="">
                    </div>
                    <p class="type-title font-18">企业认证</p>
                    <div class="type-tips">用于企业、个体工商户申请，需提供企业全称、统一社会信用代码、认证人实名信息以及营业执照等资料。</div>
                    <span class="checked-icon" v-show="authenticationType ==='2'"></span>
                </div>
              </div>
              <div class="check-mode">
                <div class="content-title">认证方式</div>
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
  <script src="/plugins/addon/idcsmart_certification/template/clientarea/api/certification.js"></script>
  <script src="/plugins/addon/idcsmart_certification/template/clientarea/js/authenticationSelect.js"></script>
  <script src="/plugins/addon/idcsmart_certification/template/clientarea/utils/util.js"></script>