
{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="configuration-system" v-cloak>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li class="active">
        <a href="javascript:;">{{lang.system_setting}}</a>
      </li>
      <li>
        <a :href="`configuration_login.html`">{{lang.login_setting}}</a>
      </li>
    </ul>
    <div class="box">
      <t-form :data="formData" :rules="rules" :label-width="80" ref="formValidatorStatus" label-align="top"
       @submit="onSubmit">
        <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
          <t-col :xs="12" :xl="3" :md="6">
            <t-form-item name="lang_admin" :label="lang.font_language">
              <t-select v-model="formData.lang_home">
                <t-option v-for="item in homeArr"  :value="item.display_lang" :label="item.display_name"
                :key="item.display_lang"></t-option>
              </t-select>
            </t-form-item>
          </t-col>
          <t-col :xs="12" :xl="3" :md="6">
            <t-form-item name="website_name" :label="lang.site_name">
              <t-input v-model="formData.website_name" :placeholder="lang.input+lang.site_name"></t-input>
            </t-form-item>
          </t-col>
          <t-col :xs="12" :xl="3" :md="6">
            <t-form-item name="website_url" :label="lang.domain">
              <t-input v-model="formData.website_url" :placeholder="lang.input+lang.domain"></t-input>
            </t-form-item>
          </t-col>
          <t-col :xs="12" :xl="3" :md="6">
            <t-form-item name="lang_admin" :label="lang.back_language">
              <t-select v-model="formData.lang_admin">
                <t-option v-for="item in adminArr"  :value="item.display_lang" :label="item.display_name"
                :key="item.display_lang"></t-option>
              </t-select>
            </t-form-item>
          </t-col>
          <t-col :xs="12" :xl="3" :md="6">
            <t-form-item name="terms_service_url" :label="lang.service_address">
              <t-input v-model="formData.terms_service_url" :placeholder="lang.input+lang.service_address"></t-input>
            </t-form-item>
          </t-col>
          <t-col :xs="12" :xl="3" :md="6">
            <t-form-item name="lang_admin" :label="lang.isAllowChooseLan">
              <t-radio-group name="creating_notice_sms" v-model="formData.lang_home_open">
                <t-radio value="1">{{lang.allow}}</t-radio>
                <t-radio value="0">{{lang.prohibit}}</t-radio>
              </t-radio-group>
            </t-form-item>
          </t-col>
          <t-col :xs="12" :xl="3" :md="6" class="service">
            <t-form-item name="lang_admin" :label="lang.maintenance_mode">
              <t-radio-group name="maintenance_mode" v-model="formData.maintenance_mode">
                <t-radio value="1">{{lang.open}}</t-radio>
                <t-radio value="0">{{lang.close}}</t-radio>
              </t-radio-group>
            </t-form-item>
          </t-col>
          <t-col :xs="12" :xl="3" :md="6">
            <t-form-item v-if="formData.maintenance_mode == '1'" :label="lang.maintenance_mode_info" name="maintenance_mode_message">
              <t-textarea :placeholder="lang.input+lang.maintenance_mode_info" v-model="formData.maintenance_mode_message" />
            </t-form-item>
          </t-col>
        </t-row>
        <t-form-item>
          <t-button theme="primary" type="submit">{{lang.hold}}</t-button>
          <!-- <t-button theme="default" variant="base">{{lang.close}}</t-button> -->
        </t-form-item>
      </t-form>
    </div>
  </t-card>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/configuration_system.js"></script>
{include file="footer"}