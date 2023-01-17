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
      <li>
        <a href="configuration_theme.html">{{lang.theme_setting}}</a>
      </li>
      <li>
        <a href="configuration_upgrade.html">{{lang.system_upgrade}}</a>
      </li>
    </ul>
    <div class="box">
      <div class="box-title">
        <span class="box-title-text">基础设置</span>
        <div class="box-title-line"></div>
      </div>
      <t-form :data="formData" :rules="rules" :label-width="80" ref="formValidatorStatus" label-align="top" @submit="onSubmit">
        <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
          <t-col :xs="12" :xl="3" :md="6">
            <t-form-item name="lang_admin" :label="lang.font_language">
              <t-select v-model="formData.lang_home">
                <t-option v-for="item in homeArr" :value="item.display_lang" :label="item.display_name" :key="item.display_lang"></t-option>
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
                <t-option v-for="item in adminArr" :value="item.display_lang" :label="item.display_name" :key="item.display_lang"></t-option>
              </t-select>
            </t-form-item>
          </t-col>
          <t-col :xs="12" :xl="3" :md="6">
            <t-form-item name="terms_service_url" :label="lang.service_address">
              <t-input v-model="formData.terms_service_url" :placeholder="lang.input+lang.service_address"></t-input>
            </t-form-item>
          </t-col>
          <!-- 新增 -->
          <t-col :xs="12" :xl="3" :md="6">
            <t-form-item name="terms_privacy_url" :label="lang.privacy_clause_address">
              <t-input v-model="formData.terms_privacy_url" :placeholder="lang.input+lang.privacy_clause_address"></t-input>
            </t-form-item>
          </t-col>
          <t-col :xs="12" :xl="6" :md="6">
            <t-form-item name="system_logo" :label="lang.member_center + 'LOGO'">
              <t-upload ref="uploadRef3" :size-limit="{ size: 2, unit: 'MB' }" :action="uploadUrl" v-model="formData.system_logo" :auto-upload="true" @fail="handleFail" theme="custom" :headers="uploadHeaders" accept="image/*" :format-response="formatImgResponse">
                <div class="upload">
                  <t-icon name="upload"></t-icon>
                  <span class="txt">{{lang.attachment + 'logo'}}</span>
                </div>
                <div class="up-tip">
                  <p>{{lang.size + '：' + lang.width + '130px；' + lang.height + '28px'}}</p>
                  <p>{{lang.logo_size + '：≤2M'}}</p>
                </div>
              </t-upload>
              <div class="logo" v-if="formData.system_logo[0]?.url">
                <div class="box">
                  <img :src="formData.system_logo[0]?.url" alt="">
                  <div class="hover" @click="deleteLogo" v-if="formData.system_logo[0]?.url">
                    <t-icon name="delete"></t-icon>
                  </div>
                </div>
                <span class="name">{{formData.system_logo[0]?.url.split('^')[1]}}</span>
              </div>
            </t-form-item>
          </t-col>
          <t-col :xs="12" :xl="3" :md="6" class="special">
            <t-form-item name="lang_admin" :label="lang.isAllowChooseLan">
              <t-radio-group name="creating_notice_sms" v-model="formData.lang_home_open">
                <t-radio value="1">{{lang.allow}}</t-radio>
                <t-radio value="0">{{lang.prohibit}}</t-radio>
              </t-radio-group>
            </t-form-item>
          </t-col>
          <t-col :xs="12" :xl="3" :md="6" class="service special">
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