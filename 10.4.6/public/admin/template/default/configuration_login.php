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
        <!-- <li
          v-if="$checkPermission('auth_system_configuration_system_configuration_web_configuration') && !hasController">
          <a href="info_config.htm">{{lang.info_config}}</a>
        </li> -->
        <li v-permission="'auth_system_configuration_system_configuration_oss_management'">
          <a href="configuration_oss.htm">{{lang.oss_setting}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_user_api_management'">
          <a href="configuration_api.htm">{{lang.user_api_text1}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_system_info_view'">
          <a style="display: flex; align-items: center;" href="configuration_upgrade.htm">{{lang.system_upgrade}}
            <img v-if="isCanUpdata" style="width: 20px; height: 20px; margin-left: 5px;"
              src="/{$template_catalog}/template/{$themes}/img/upgrade.svg">
          </a>
        </li>
      </ul>
      <div class="box">
        <t-form :data="formData" :required-mark="false" :label-width="80" label-align="top" ref="formValidatorStatus"
          :rules="rules" @submit="onSubmit">
          <p class="com-tit"><span>{{ lang.setting_text1 }}</span></p>
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="lang_admin" :label="lang.setting_text3">
                <t-radio-group name="register_phone" v-model="formData.register_phone">
                  <t-radio :value="1">{{lang.yes}}</t-radio>
                  <t-radio :value="0">{{lang.login_no}}</t-radio>
                </t-radio-group>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="lang_admin" :label="lang.setting_text4">
                <t-radio-group name="code_client_phone_register" v-model="formData.code_client_phone_register">
                  <t-radio :value="1">{{lang.yes}}</t-radio>
                  <t-radio :value="0">{{lang.login_no}}</t-radio>
                </t-radio-group>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="lang_admin" :label="lang.setting_text8">
                <t-radio-group name="login_phone_verify" v-model="formData.login_phone_verify">
                  <t-radio :value="1">{{lang.yes}}</t-radio>
                  <t-radio :value="0">{{lang.login_no}}</t-radio>
                </t-radio-group>
              </t-form-item>
            </t-col>
          </t-row>
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">

            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="lang_admin" :label="lang.setting_text5">
                <t-radio-group name="register_email" v-model="formData.register_email">
                  <t-radio :value="1">{{lang.yes}}</t-radio>
                  <t-radio :value="0">{{lang.login_no}}</t-radio>
                </t-radio-group>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="lang_admin" :label="lang.setting_text6">
                <t-radio-group name="code_client_email_register" v-model="formData.code_client_email_register">
                  <t-radio :value="1">{{lang.yes}}</t-radio>
                  <t-radio :value="0">{{lang.login_no}}</t-radio>
                </t-radio-group>
              </t-form-item>
            </t-col>
            <t-col :xs="24" :xl="6" :md="12">
              <t-form-item name="lang_admin" :label="lang.support_email_type">
                <t-radio-group name="limit_email_suffix" v-model="formData.limit_email_suffix">
                  <t-radio :value="1">{{lang.yes}}</t-radio>
                  <t-radio :value="0">{{lang.login_no}}</t-radio>
                </t-radio-group>
                <t-input style="width: 400px;" v-if="formData.limit_email_suffix == '1'" v-model="formData.email_suffix"
                  :placeholder="lang.support_email_tip"></t-input>
              </t-form-item>
            </t-col>
          </t-row>
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">


            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="home_login_check_ip">
                <div slot="label" class="custom-label">
                  <span class="label">{{lang.setting_text17}}</span>
                  <t-tooltip placement="top-right" :content="lang.setting_text18" :show-arrow="false" theme="light">
                    <t-icon name="help-circle" size="18px" />
                  </t-tooltip>
                </div>
                <t-radio-group name="home_login_check_ip" v-model="formData.home_login_check_ip">
                  <t-radio :value="1">{{lang.yes}}</t-radio>
                  <t-radio :value="0">{{lang.login_no}}</t-radio>
                </t-radio-group>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="home_login_check_common_ip" :label="lang.setting_text7">
                <t-radio-group name="home_login_check_common_ip" v-model="formData.home_login_check_common_ip">
                  <t-radio :value="1">{{lang.yes}}</t-radio>
                  <t-radio :value="0">{{lang.login_no}}</t-radio>
                </t-radio-group>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="home_enforce_safe_method">
                <div slot="label" class="custom-label">
                  <span class="label">{{lang.setting_text19}}</span>
                  <t-tooltip placement="top-right" :content="lang.setting_text20" :show-arrow="false" theme="light">
                    <t-icon name="help-circle" size="18px" />
                  </t-tooltip>
                </div>
                <t-select style="width: 100%;" v-model="formData.home_enforce_safe_method" :min-collapsed-num="2"
                  multiple clearable>
                  <t-option v-for="item in homeSafeMethodList" :value="item.value" :label="item.label"
                    :key="item.value">
                  </t-option>
                </t-select>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="home_login_ip_exception_verify">
                <div slot="label" class="custom-label">
                  <span class="label">{{lang.setting_text9}}</span>
                  <t-tooltip placement="top-right" :content="lang.setting_text10" :show-arrow="false" theme="light">
                    <t-icon name="help-circle" size="18px" />
                  </t-tooltip>
                </div>
                <t-select style="width: 100%;" v-model="formData.home_login_ip_exception_verify" :min-collapsed-num="2"
                  multiple clearable>
                  <t-option v-for="item in homeVerifyList" :value="item.value" :label="item.label" :key="item.value">
                  </t-option>
                </t-select>
              </t-form-item>
            </t-col>
          </t-row>
          <p class="com-tit"><span>{{ lang.setting_text2 }}</span></p>
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="admin_login_check_ip">
                <div slot="label" class="custom-label">
                  <span class="label">{{lang.setting_text21}}</span>
                  <t-tooltip placement="top-right" :content="lang.setting_text18" :show-arrow="false" theme="light">
                    <t-icon name="help-circle" size="18px" />
                  </t-tooltip>
                </div>
                <t-radio-group name="admin_login_check_ip" v-model="formData.admin_login_check_ip">
                  <t-radio :value="1">{{lang.yes}}</t-radio>
                  <t-radio :value="0">{{lang.login_no}}</t-radio>
                </t-radio-group>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="admin_allow_remember_account" :label="lang.setting_text22">
                <t-radio-group name="admin_allow_remember_account" v-model="formData.admin_allow_remember_account">
                  <t-radio :value="1">{{lang.yes}}</t-radio>
                  <t-radio :value="0">{{lang.login_no}}</t-radio>
                </t-radio-group>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="admin_enforce_safe_method">
                <div slot="label" class="custom-label">
                  <span class="label">{{lang.setting_text19}}</span>
                  <t-tooltip placement="top-right" :content="lang.setting_text23" :show-arrow="false" theme="light">
                    <t-icon name="help-circle" size="18px" />
                  </t-tooltip>
                </div>
                <t-select style="width: 100%;" v-model="formData.admin_enforce_safe_method" :min-collapsed-num="2"
                  multiple clearable>
                  <t-option v-for="item in adminMethodList" :value="item.value" :label="item.label" :key="item.value">
                  </t-option>
                </t-select>
              </t-form-item>
            </t-col>
          </t-row>

          <t-form-item class="btn">
            <t-button theme="primary" type="submit" :loading="submitLoading"
              v-permission="'auth_system_configuration_system_configuration_access_configuration_save_configuration'">{{lang.hold}}</t-button>
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
