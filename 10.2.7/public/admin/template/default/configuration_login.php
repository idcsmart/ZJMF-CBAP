{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="configuration-system configuration-login" v-cloak>
<t-card class="list-card-container">
  <ul class="common-tab">
    <li>
      <a href="configuration_system.htm">{{lang.system_setting}}</a>
    </li>
    <li class="active">
      <a href="javascript:;">{{lang.login_setting}}</a>
    </li>
    <li>
      <a href="configuration_theme.htm">{{lang.theme_setting}}</a>
    </li>
    <li>
        <a href="configuration_upgrade.htm">{{lang.system_upgrade}}</a>
    </li>
  </ul>
  <div class="box">
    <t-form :data="formData" :label-width="80" label-align="top"
    ref="formValidatorStatus" @submit="onSubmit">
      <p class="com-tit"><span>{{ lang.phone_login }}</span></p>
      <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
        <t-col :xs="12" :xl="3" :md="6">
          <t-form-item name="lang_admin" :label="lang.support_register">
            <t-radio-group name="register_phone" v-model="formData.register_phone">
              <t-radio value="1">{{lang.yes}}</t-radio>
              <t-radio value="0">{{lang.login_no}}</t-radio>
            </t-radio-group>
          </t-form-item>
          <t-form-item name="lang_admin" :label="lang.support_no_password">
            <t-radio-group name="login_phone_verify" v-model="formData.login_phone_verify">
              <t-radio value="1">{{lang.yes}}</t-radio>
              <t-radio value="0">{{lang.login_no}}</t-radio>
            </t-radio-group>
          </t-form-item>
        </t-col>
      </t-row>
      <p class="com-tit"><span>{{ lang.email_login }}</span></p>
      <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
        <t-col :xs="12" :xl="3" :md="6">
          <t-form-item name="lang_admin" :label="lang.support_register">
            <t-radio-group name="register_email" v-model="formData.register_email">
              <t-radio value="1">{{lang.yes}}</t-radio>
              <t-radio value="0">{{lang.login_no}}</t-radio>
            </t-radio-group>
          </t-form-item>
        </t-col>
      </t-row>
      <p class="com-tit"><span>{{ lang.ip_check }}</span></p>
      <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
        <t-col :xs="12" :xl="3" :md="6">
          <t-form-item name="lang_admin" :label="lang.ip_check_home">
            <t-radio-group name="register_phone" v-model="formData.home_login_check_ip">
              <t-radio value="1">{{lang.yes}}</t-radio>
              <t-radio value="0">{{lang.login_no}}</t-radio>
            </t-radio-group>
          </t-form-item>
          <t-form-item name="lang_admin" :label="lang.ip_check_admin">
            <t-radio-group name="login_phone_verify" v-model="formData.admin_login_check_ip">
              <t-radio value="1">{{lang.yes}}</t-radio>
              <t-radio value="0">{{lang.login_no}}</t-radio>
            </t-radio-group>
          </t-form-item>
        </t-col>
      </t-row>
      <t-form-item class="btn">
        <t-button theme="primary" type="submit">{{lang.hold}}</t-button>
        <!-- <t-button theme="default" variant="base">{{lang.close}}</t-button> -->
      </t-form-item>
    </t-form>
  </div>
</t-card>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/configuration_login.js"></script>
{include file="footer"}