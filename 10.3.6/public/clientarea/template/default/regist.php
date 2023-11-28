<!DOCTYPE html>
<html lang="en" theme-color="default" theme-mode>

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
  <title></title>
  <!-- <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css"> -->
  <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common/element.css">
  <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/regist.css">
  <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common/common.css">
  <script>
    const url = "/{$template_catalog}/template/{$themes}/"
  </script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/lang.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/common.js"></script>
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
    <div id="regist">
      <!-- 验证码 -->
      <captcha-dialog ref="captcha" :is-show-captcha="isShowCaptcha"></captcha-dialog>
      <div class="login-container">
        <div class="container-back">
          <div class="back-line1"></div>
          <div class="back-line2"></div>
          <div class="back-line3"></div>
          <div class="back-text">
            <div class="text-welcome">
              WELCOME
            </div>
            <div class="text-title">
              <!-- {{lang.login_welcome}} -->
              {{lang.login_welcome}}{{commonData.website_name}}{{lang.login_vip}}
            </div>
            <div class="text-level">
              {{lang.login_level}}
            </div>
          </div>
        </div>
        <div class="container-before">
          <div class="login">
            <div class="login-text">
              <div class="login-text-title">{{lang.regist}}</div>
              <div class="login-text-regist">
                {{lang.regist_yes_account}}<a @click="toLogin">{{lang.regist_login_text}}</a>
              </div>
            </div>
            <div class="login-form">
              <div class="login-top">
                <div v-if="commonData.register_email == 1" class="login-email" :class="isEmailOrPhone? 'active':null" @click="changeType(true)">{{lang.login_email}}
                </div>
                <div v-if="commonData.register_phone == 1" class="login-phone" :class="!isEmailOrPhone? 'active':null" @click="changeType(false)">{{lang.login_phone}}
                </div>
              </div>
              <div class="form-main" v-if="commonData.register_email == 1 || commonData.register_phone == 1">
                <div class="form-item">
                  <el-input v-if="isEmailOrPhone" v-model="formData.email" :placeholder="lang.login_email"></el-input>
                  <el-input v-else class="input-with-select select-input" v-model="formData.phone" :placeholder="lang.login_phone">
                    <el-select filterable slot="prepend" v-model="formData.countryCode">
                      <el-option v-for="item in countryList" :key="item.name" :value="item.phone_code" :label="item.name_zh + '+' + item.phone_code"></el-option>
                    </el-select>
                  </el-input>
                </div>
                <template v-if="commonData.code_client_phone_register == 1 && !isEmailOrPhone">
                  <div class="form-item code-item" v-if="!(commonData.code_client_email_register==0 && isEmailOrPhone)">
                    <!-- 手机验证码 -->
                    <el-input v-if="!isEmailOrPhone" v-model="formData.phoneCode" :placeholder="lang.login_phone_code">
                    </el-input>
                    <count-down-button ref="phoneCodebtn" @click.native="sendPhoneCode" v-if="!isEmailOrPhone" my-class="code-btn"></count-down-button>
                  </div>
                </template>
                <template v-if="commonData.code_client_email_register == 1 && isEmailOrPhone">
                  <div class="form-item code-item">
                    <!-- 邮箱验证码 -->
                    <el-input v-if="isEmailOrPhone" v-model="formData.emailCode" :placeholder="lang.email_code">
                    </el-input>
                    <count-down-button ref="emailCodebtn" @click.native="sendEmailCode" v-if="isEmailOrPhone" my-class="code-btn"></count-down-button>
                  </div>
                </template>

                <div class="form-item">
                  <el-input :placeholder="lang.tip1" v-model="formData.password" type="password">
                  </el-input>
                </div>
                <div class="form-item">
                  <el-input :placeholder="lang.tip2" v-model="formData.repassword" type="password"></el-input>
                </div>
                <div class="form-item read-item">
                  <!-- <el-checkbox v-model="checked">
                                        
                                    </el-checkbox>
                                    {{lang.tip3}}<a @click="toRead">{{lang.login_list}}</a> -->
                  <el-checkbox v-model="checked" class="check-div">
                    {{lang.tip3}}<a @click="goHelpUrl('terms_service_url')">{{lang.read_service}}</a>{{lang.read_and}}<a @click="goHelpUrl('terms_privacy_url')">{{lang.read_privacy}}</a>
                  </el-checkbox>
                </div>
                {foreach $addons as $addon}
                {if ($addon.name=='IdcsmartSale')}
                <div class="form-item read-item">
                  <el-checkbox v-model="checked1">{{lang.tip4}}</el-checkbox>
                </div>
                <div class="read-item" v-if="checked1">
                  <el-input :placeholder="lang.tip5" v-model="customfield.sale_number"></el-input>
                </div>
                {/if}
                {/foreach}
                <div class="read-item" v-if="errorText.length !== 0">
                  <el-alert :title="errorText" type="error" show-icon :closable="false">
                  </el-alert>
                </div>
                <div class="form-item">
                  <el-button type="primary" class="login-btn" @click="doRegist">{{lang.regist_to_login}}</el-button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!-- =======公共======= -->
  <script src="/{$template_catalog}/template/{$themes}/js/common/vue.js"></script>
  <!-- <script src="https://unpkg.com/element-ui/lib/index.js"></script> -->
  <script src="/{$template_catalog}/template/{$themes}/js/common/element.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/axios.min.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/request.js"></script>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/countDownButton/countDownButton.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/regist.js"></script>
  <script src="https://cdn.bootcss.com/crypto-js/3.1.9-1/crypto-js.min.js"></script>
  <script src="https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/captchaDialog/captchaDialog.js"></script>

</body>

</html>