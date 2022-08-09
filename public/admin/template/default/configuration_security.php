{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="configuration-security" v-cloak>

  <t-card class="list-card-container">
    <!-- <p class="com-h-tit">{{lang.safe_setting}}</p> -->
    <div class="box">
      <t-form :data="formData" :label-width="80" label-align="top" :rules="rules" ref="formValidatorStatus" @submit="onSubmit">
        <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
          <t-col>
            <t-form-item :label="lang.enable_code" class="code">
              <t-checkbox v-model="formData.captcha_client_register">{{lang.user_register}}</t-checkbox>
              <t-checkbox v-model="formData.captcha_client_login">{{lang.user_login}}</t-checkbox>
              <t-checkbox v-model="formData.captcha_admin_login">{{lang.admin_login}}</t-checkbox>
            </t-form-item>
            <div class="tip">
              <t-icon name="error-circle" size="18"></t-icon>
              <div>
                <p>{{lang.tip1}}</p>
                <p>{{lang.tip2}}</p>
              </div>
            </div>
            <t-divider></t-divider>
          </t-col>
        </t-row>
        <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
          <t-col>
            <t-form-item :label="lang.error_choose_code">
              <t-radio-group v-model="formData.captcha_client_login_error" :disabled="!formData.captcha_client_login">
                <t-radio value="0">{{lang.always_show}}</t-radio>
                <t-radio value="1">{{lang.fail_three_show}}</t-radio>
              </t-radio-group>
            </t-form-item>
            <div class="tip">
              <t-icon name="error-circle" size="18"></t-icon>
              <div>
                <p>{{lang.tip3}}</p>
                <p>{{lang.tip4}}</p>
                <p>{{lang.tip5}}</p>
              </div>
            </div>
          </t-col>
        </t-row>
        <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
          <t-col :xs="12" :xl="6" class="f-item">
            <div class="item">
              <t-form-item name="captcha_width" :label="lang.image_width">
                <t-input v-model="formData.captcha_width" :placeholder="lang.input+lang.image_width" @change="getCode">
                </t-input>
              </t-form-item>
              <t-form-item name="captcha_length" :label="lang.image_num">
                <t-input v-model="formData.captcha_length" :placeholder="lang.input+lang.image_width" @change="getCode">
                </t-input>
              </t-form-item>
            </div>
            <div class="item">
              <t-form-item name="captcha_height" :label="lang.image_heigt">
                <t-input v-model="formData.captcha_height" :placeholder="lang.input+lang.image_width" @change="getCode">
                </t-input>
              </t-form-item>
              <t-form-item :label="lang.image_preview">
                <img :src="codeUrl" alt="" class="codeUrl">
              </t-form-item>
            </div>
          </t-col>
        </t-row>
        <t-form-item>
          <t-button theme="primary" type="submit" style="margin-right: 10px">{{lang.hold}}</t-button>
          <!-- <t-button theme="default" variant="base">{{lang.close}}</t-button> -->
        </t-form-item>
      </t-form>
    </div>
  </t-card>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/configuration_security.js"></script>
{include file="footer"}