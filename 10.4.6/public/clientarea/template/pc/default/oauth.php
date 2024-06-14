{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/login.css" />
<style>
  [v-cloak] {
    display: none !important;
  }
</style>
</head>

<body>
  <div id="mainLoading">
    <div class="ddr ddr1"></div>
    <div class="ddr ddr2"></div>
    <div class="ddr ddr3"></div>
    <div class="ddr ddr4"></div>
    <div class="ddr ddr5"></div>
  </div>
  <div class="template">
    <div id="login" v-cloak>
      <div class="login-container">
        <div class="container-back">
          <div class="back-line1"></div>
          <div class="back-line2"></div>
          <div class="back-line3"></div>
          <div class="back-text">
            <div class="text-welcome">WELCOME</div>
            <div class="text-title">
              {{ lang.login_welcome }}
              {{ commonData.website_name }}{{ lang.login_vip }}
            </div>
            <div class="text-level">
              {{ lang.login_level }}
            </div>
          </div>
        </div>
        <div class="container-before">
          <div class="login">
            <div class="login-text">
              <div class="login-text-title">{{ lang.oauth_text1 }}</div>
              <div class="login-text-regist">
                <p>{{ lang.oauth_text2 }}</p>
                <p>{{ lang.oauth_text3 }}</p>
              </div>
            </div>
            <div class="login-form">
              <div class="login-top">
                <div class="login-email" :class="isEmailOrPhone ? 'active' : null" @click="isEmailOrPhone = true">
                  {{ lang.login_email }}
                </div>
                <div class="login-phone" :class="!isEmailOrPhone? 'active' : null " @click="isEmailOrPhone = false">
                  {{ lang.login_phone }}
                </div>
              </div>
              <div class="form-main">
                <div class="form-item">
                  <el-input v-if="isEmailOrPhone" v-model="formData.email"
                    :placeholder="lang.login_placeholder_pre + lang.login_email"></el-input>
                  <el-input v-else class="input-with-select select-input" v-model="formData.phone"
                    :placeholder="lang.login_placeholder_pre + lang.login_phone">
                    <el-select filterable slot="prepend" v-model="formData.countryCode">
                      <el-option v-for="item in countryList" :key="item.name" :value="item.phone_code"
                        :label="item.name_zh + '+' + item.phone_code"></el-option>
                    </el-select>
                  </el-input>
                </div>
                <div v-if="isPassOrCode" class="form-item">
                  <el-input :placeholder="lang.login_pass" v-model="formData.password" type="password"></el-input>
                </div>
                <div v-else class="form-item code-item">
                  <!-- 邮箱验证码 -->
                  <el-input v-if="isEmailOrPhone" v-model="formData.emailCode"
                    :placeholder="lang.email_code"></el-input>
                  <count-down-button ref="emailCodebtn" @click.native="sendEmailCode" v-if="isEmailOrPhone"
                    my-class="code-btn"></count-down-button>
                  <!-- 手机验证码 -->
                  <el-input v-if="!isEmailOrPhone" v-model="formData.phoneCode"
                    :placeholder="lang.login_phone_code"></el-input>
                  <count-down-button ref="phoneCodebtn" @click.native="sendPhoneCode" v-if="!isEmailOrPhone"
                    my-class="code-btn"></count-down-button>
                </div>
                <div class="form-item rember-item">
                  <!-- 1-31 取消原有的记住密码 -->
                  <el-checkbox v-model="checked">
                    {{ lang.login_read
                      }}<a @click="goHelpUrl('terms_service_url')">{{
                        lang.read_service
                      }}</a>{{ lang.read_and
                      }}<a @click="goHelpUrl('terms_privacy_url')">{{
                        lang.read_privacy
                      }}</a>
                  </el-checkbox>
                </div>
                <div class="read-item" v-if="errorText.length !== 0">
                  <el-alert :title="errorText" type="error" show-icon :closable="false">
                  </el-alert>
                </div>
                <div class="form-item">
                  <el-button type="primary" class="login-btn" @click="doLogin">{{ lang.oauth_text4 }}</el-button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- 验证码 -->
      <captcha-dialog :is-show-captcha="isShowCaptcha" ref="captcha"></captcha-dialog>
    </div>
  </div>


  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/components/captchaDialog/captchaDialog.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/countDownButton/countDownButton.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/crypto-js.min.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/jquery.mini.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/oauth.js"></script>
  {include file="footer"}