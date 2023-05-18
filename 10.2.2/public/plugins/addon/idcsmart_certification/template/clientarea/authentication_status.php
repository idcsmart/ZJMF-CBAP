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
                <div class="main-card-title"><img src="/plugins/addon/idcsmart_certification/template/clientarea/img/finance/back.png" class="top-back-img" @click="goAccount">实名认证</div>
                <div class="top-line"></div>
              </div>
              <!-- 认证状态页面 -->
              <div class="status-box">
                <!-- 个人实名认证审核中 -->
                <div class="personl-ing" v-if="userStatus === 15">
                  <h3 class="title-blue">个人实名认证审核中！</h3>
                  <div class="updata-btn">
                    <el-button @click="backTicket">升级为企业认证</el-button>
                  </div>
                  <table class="table-box" rules="all" cellpadding="0" cellspacing="0">
                    <tr>
                      <td>认证用户：{{certificationInfoObj.person.username}}</td>
                      <td>认证证件号：{{certificationInfoObj.person.card_number}}</td>
                    </tr>
                    <tr>
                      <td>真实姓名：{{certificationInfoObj.person.card_name}}</td>
                      <td>认证时间：{{certificationInfoObj.person.create_time | formateTime}}</td>
                    </tr>
                  </table>
                  <div class="again-btn">
                    <el-button @click="backTicket">重新提交资料</el-button>
                  </div>
                </div>
                <!-- 个人认证已完成 -->
                <div class="personl-ing" v-else-if="userStatus === 10">
                  <div class="status-img-box">
                    <img src="/plugins/addon/idcsmart_certification/template/clientarea/img/account/success.png" alt="">
                  </div>
                  <h3 class="title-green">恭喜，个人认证已完成！</h3>
                  <table class="table-box mar-top-42" rules="all">
                    <tr>
                      <td>认证用户：{{certificationInfoObj.person.username}}</td>
                      <td>认证证件号：{{certificationInfoObj.person.card_number}}</td>
                    </tr>
                    <tr>
                      <td>真实姓名：{{certificationInfoObj.person.card_name}}</td>
                      <td>认证时间：{{certificationInfoObj.person.create_time | formateTime}}</td>
                    </tr>
                  </table>
                  <div class="updata-btn mar-top-114">
                    <el-button @click="backTicket">升级为企业认证</el-button>
                  </div>
                </div>
                <!-- 企业认证审核中 -->
                <div class="personl-ing" v-if="userStatus === 25">
                  <h3 class="title-blue">企业实名认证审核中！</h3>
                  <table class="table-box mar-top-42" rules="all">
                    <tr>
                      <td>认证用户：{{certificationInfoObj.company.username}}</td>
                      <td>认证企业：{{certificationInfoObj.company.company}}</td>
                      <!-- <td>认证证件号：{{certificationInfoObj.company.card_number}}</td> -->
                    </tr>
                    <tr>
                      <td>统一社会信用代码：{{ certificationInfoObj.company.company_organ_code}}</td>
                      <!-- <td>真实姓名：{{certificationInfoObj.company.card_name}}</td> -->
                      <td>认证时间：{{certificationInfoObj.company.create_time | formateTime}}</td>
                    </tr>
                    <tr>
                      <!-- <td>认证企业：{{certificationInfoObj.company.company}}</td> -->
                    </tr>
                  </table>
                  <div class="again-btn">
                    <el-button @click="backTicket">重新提交资料</el-button>
                  </div>
                </div>
                <!-- 企业认证已完成 -->
                <div class="personl-ing" v-else-if="userStatus === 20">
                  <div class="status-img-box">
                    <img src="/plugins/addon/idcsmart_certification/template/clientarea/img/account/success.png" alt="">
                  </div>
                  <h3 class="title-green">恭喜，认证已完成！</h3>
                  <table class="table-box mar-top-42" rules="all">
                    <tr>
                      <td>认证用户：{{certificationInfoObj.company.username || certificationInfoObj.person.username}}</td>
                      <td>认证证件号：{{certificationInfoObj.company.card_number || certificationInfoObj.person.card_number}}</td>
                    </tr>
                    <tr>
                      <td>真实姓名：{{certificationInfoObj.company.card_name || certificationInfoObj.person.card_name}}</td>
                      <td>认证时间：{{certificationInfoObj.company.create_time | formateTime}}</td>
                    </tr>
                    <tr>
                      <td>认证企业：{{certificationInfoObj.company.company || certificationInfoObj.company.certification_company}}</td>
                      <td></td>
                    </tr>
                  </table>
                </div>
                <!-- 认证失败 -->
                <div class="personl-ing" v-else-if="userStatus === 50">
                  <div class="status-img-box">
                    <img src="/plugins/addon/idcsmart_certification/template/clientarea/img/account/error.png" alt="">
                  </div>
                  <h3 class="status-title black-color">认证失败</h3>
                  <p class="status-tips">未完成指定认证操作，请重新认证</p>
                  <div class="btn-box">
                    <el-button @click="submitAgan">重新认证</el-button>
                    <el-link class="canleBtn" @click="goAccount">取消</el-link>
                  </div>
                </div>
              </div>
            </div>
          </el-main>
        </el-container>
      </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/plugins/addon/idcsmart_certification/template/clientarea/api/certification.js"></script>
    <script src="/plugins/addon/idcsmart_certification/template/clientarea/js/authenticationStatus.js"></script>
    <script src="/plugins/addon/idcsmart_certification/template/clientarea/utils/util.js"></script>