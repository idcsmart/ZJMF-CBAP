{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="configuration-system configuration-login" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li v-permission="'auth_system_configuration_system_configuration_system_configuration_view'">
          <a href="configuration_system.htm">{{lang.system_setting}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_debug'">
          <a href="configuration_debug.htm">{{lang.debug_setting}}</a>
        </li>
        <li class="active" v-permission="'auth_system_configuration_system_configuration_access_configuration_view'">
          <a href="javascript:;">{{lang.login_setting}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_theme_configuration_view'">
          <a href="configuration_theme.htm">{{lang.theme_setting}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_web_configuration'">
          <a href="info_config.htm">{{lang.info_config}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_system_info_view'">
          <a style="display: flex; align-items: center;" href="configuration_upgrade.htm">{{lang.system_upgrade}}
            <img v-if="isCanUpdata" style="width: 20px; height: 20px; margin-left: 5px;" src="/{$template_catalog}/template/{$themes}/img/upgrade.svg">
          </a>
        </li>
      </ul>
      <div class="box">
        <t-form :data="formData" :label-width="80" label-align="top" ref="formValidatorStatus" @submit="onSubmit">
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
          <p class="com-tit"><span>{{ lang.open_number_code }}</span></p>
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
            <t-col>
              <t-form-item class="code">
                <div>
                  <div>
                    <t-checkbox v-model="formData.code_client_email_register">{{lang.email_register}}</t-checkbox>
                    <t-checkbox v-model="formData.code_client_phone_register" style="margin-left: 15px;">{{lang.phone_register}}</t-checkbox>
                  </div>
                  <div class="tip">
                    <t-icon name="error-circle" size="18"></t-icon>
                    <div>
                      <p>{{lang.tip11}}</p>
                    </div>
                  </div>
                </div>

              </t-form-item>
            </t-col>
          </t-row>
          <t-form-item class="btn">
            <t-button theme="primary" type="submit" :loading="submitLoading"  v-permission="'auth_system_configuration_system_configuration_access_configuration_save_configuration'">{{lang.hold}}</t-button>
          </t-form-item>
        </t-form>
      </div>
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/configuration_login.js"></script>
{include file="footer"}
