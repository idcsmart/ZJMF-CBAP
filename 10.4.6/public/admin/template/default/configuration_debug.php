{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="configuration-system configuration-debug" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li v-permission="'auth_system_configuration_system_configuration_system_configuration_view'">
          <a href="configuration_system.htm">{{lang.system_setting}}</a>
        </li>
        <li class="active" v-permission="'auth_system_configuration_system_configuration_debug'">
          <a href="javascript:;">{{lang.debug_setting}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_access_configuration_view'">
          <a href="configuration_login.htm">{{lang.login_setting}}</a>
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
        <t-form :data="formData" :label-width="80" label-align="top" ref="formValidatorStatus">
          <t-form-item :label="lang.debug_setting">
            <t-switch :custom-value="[1,0]" v-model="formData.debug_model" @change="changeSwitch"></t-switch>
            <span class="s-tip">{{lang.debug_setting_tip}}</span>
          </t-form-item>
          <div class="debug-con" v-show="formData.debug_model">
            <div class="left">
              <textarea id="debug" v-model="formData.debug_model_auth"></textarea>
              <p class="des" v-if="duration > 0">
                {{lang.debug_setting_tip1}}{{countdownText}}{{lang.debug_setting_tip2}}
              </p>
            </div>
            <t-button @click="copyHandler('debug')" :loading="submitLoading"
              :disabled="!formData.debug_model_auth">{{lang.copy}}</t-button>
          </div>
        </t-form>
      </div>
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/js/common/moment.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/configuration_debug.js"></script>
{include file="footer"}
