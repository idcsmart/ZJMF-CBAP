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
              <div class="main-card-title"><img src="/{$template_catalog}/template/{$themes}/img/finance/back.png" class="top-back-img" @click="goAccount">实名认证</div>
              <div class="top-line"></div>
            </div>
            <!-- 认证状态页面 -->
            <div class="status-box">
              <h3 class="status-title" v-if="userStatus === 20">恭喜，全部认证已完成！</h3>
              <h3 class="status-title" v-else-if="userStatus === 10">恭喜，个人认证已完成！</h3>
              <h3 class="status-title origin-color" v-else-if="userStatus === 15 || userStatus === 25">实名认证审核中，请耐心等待！</h3>
              <h3 class="status-title black-color" v-else-if="userStatus === 50">认证失败</h3>
              <p class="status-tips" v-if="userStatus === 10 && companyStatus !==1">还可以完成企业认证</p>
              <p class="status-tips" v-else-if="userStatus === 15">您可重新提交资料或升级为企业认证</p>
              <p class="status-tips" v-else-if="userStatus === 50">未完成指定认证操作，请重新认证</p>
              <div v-if="userStatus === 50">
                <img src="/{$template_catalog}/template/{$themes}/img/account/error.png" class="err-img" alt="">
              </div>
              <div class="status-info" v-else>
                <div>
                  <p><span class="info-key">用户：</span></p>
                  <p><span class="info-key">真实姓名：</span></p>
                  <p><span class="info-key">证件号：</span></p>
                  <p><span class="info-key" v-if="this.rzType === '2'">认证企业：</span></p>
                  <p><span class="info-key">提交时间：</span></p>
                </div>
                <div v-if="this.rzType === '2'">
                  <p><span class="info-value">{{certificationInfoObj.company.username}}</span></p>
                  <p><span class="info-value">{{certificationInfoObj.company.card_name}}</span></p>
                  <p><span class="info-value">{{certificationInfoObj.company.card_number}}</span></p>
                  <p ><span class="info-value">{{certificationInfoObj.company.company}}</span></p>
                  <p><span class="info-value">{{certificationInfoObj.company.create_time | formateTime}}</span></p>
                </div>
                <div v-else>
                  <p><span class="info-value">{{certificationInfoObj.person.username}}</span></p>
                  <p><span class="info-value">{{certificationInfoObj.person.card_name}}</span></p>
                  <p><span class="info-value">{{certificationInfoObj.person.card_number}}</span></p>
                  <p><span class="info-value">{{certificationInfoObj.person.create_time | formateTime}}</span></p>
                </div>
              </div>
              <div class="btn-box" v-if="userStatus === 15 || userStatus === 25">
                <el-button @click="backTicket">重新提交资料</el-button>
                <el-button v-if="userStatus === 15" @click="backTicket">升级为企业认证</el-button>
              </div>
              <div class="btn-box" v-if="userStatus === 10">
                <el-button  @click="backTicket">升级为企业认证</el-button>
              </div>
              <div class="btn-box" v-if="userStatus === 50">
                <el-button @click="submitAgan">重新认证</el-button>
                <el-link class="canleBtn" @click="goAccount">取消</el-link>
              </div>
            </div>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/certification.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/authenticationStatus.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  {include file="footer"}