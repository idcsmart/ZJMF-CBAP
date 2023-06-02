{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="configuration-theme" v-cloak>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li>
        <a href="configuration_system.htm">{{lang.system_setting}}</a>
      </li>
      <li>
        <a :href="`configuration_login.htm`">{{lang.login_setting}}</a>
      </li>
      <li class="active">
        <a href="javascript:;">{{lang.theme_setting}}</a>
      </li>
      <li>
        <a href="info_config.htm">{{lang.info_config}}</a>
      </li>
      <li>
        <a style="display: flex; align-items: center;" href="configuration_upgrade.htm">{{lang.system_upgrade}}
          <img v-if="isCanUpdata" style="width: 20px; height: 20px; margin-left: 5px;" src="/{$template_catalog}/template/{$themes}/img/upgrade.svg">
        </a>
      </li>
    </ul>
    <div class="box">
      <t-form :data="formData" :rules="rules" label-align="top" :label-width="80" ref="formValidatorStatus" @submit="onSubmit">
        <t-form-item :label="lang.back_manage">
          <t-select v-model="formData.admin_theme" :placeholder="lang.select+lang.theme" :popup-props="popupProps">
            <t-option v-for="item in admin_theme" :value="item.name" :label="item.name" :key="item.name">
            </t-option>
          </t-select>
        </t-form-item>
        <t-form-item name="lang_admin" :label="lang.member_center">
          <ul class="theme-box">
            <li class="item" v-for="item in clientarea_theme" :key="item.name" :class="{active: item.name === formData.clientarea_theme}" @click="chooseTheme(item)">
              <div class="icon">
                <t-icon name="check"></t-icon>
              </div>
              <div class="img">
                <img :src="item.img" alt="">
              </div>
              <p class="text">{{item.name}}</p>
            </li>
          </ul>
        </t-form-item>
        <t-form-item :label="lang.official_theme">
          <ul class="theme-box">
            <li class="item" v-for="item in web_theme_list" :key="item.name" :class="{active: item.name === formData.web_theme}" @click="chooseWebTheme(item)">
              <div class="icon">
                <t-icon name="check"></t-icon>
              </div>
              <div class="img">
                <img :src="item.img" alt="">
              </div>
              <p class="text">{{item.name}}</p>
            </li>
          </ul>
        </t-form-item>
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
<script src="/{$template_catalog}/template/{$themes}/js/configuration_theme.js"></script>
{include file="footer"}