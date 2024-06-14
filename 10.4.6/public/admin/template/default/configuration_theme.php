{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="configuration-theme" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li v-permission="'auth_system_configuration_system_configuration_system_configuration_view'">
          <a href="configuration_system.htm">{{lang.system_setting}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_debug'">
          <a href="configuration_debug.htm">{{lang.debug_setting}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_access_configuration_view'">
          <a :href="`configuration_login.htm`">{{lang.login_setting}}</a>
        </li>
        <li class="active" v-permission="'auth_system_configuration_system_configuration_theme_configuration_view'">
          <a href="javascript:;">{{lang.theme_setting}}</a>
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
        <t-form :data="formData" :rules="rules" label-align="top" :label-width="80" ref="formValidatorStatus"
          @submit="onSubmit">
          <t-tabs v-model="value" placement="left">
            <t-tab-panel value="admin_theme" :label="lang.back_manage">
              <t-form-item :label="lang.back_manage">
                <ul class="theme-box">
                  <li class="item" v-for="item in admin_theme" :key="item.name"
                    :class="{active: item.name === formData.admin_theme}" @click="chooseAdmin(item)">
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
            </t-tab-panel>
            <t-tab-panel value="clientarea_theme" :label="lang.member_center">
              <t-tabs v-model="clientarea_type" class="chiled-tabs">
                <t-tab-panel value="pc" label="PC">
                  <t-form-item name="lang_admin" :label="lang.member_center">
                    <ul class="theme-box">
                      <li class="item" v-for="item in clientarea_theme" :key="item.name"
                        :class="{active: item.name === formData.clientarea_theme}" @click="chooseTheme(item)">
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
                </t-tab-panel>
                <t-tab-panel value="mobile" :label="lang.finance_search_text7">
                  <div>
                    <span>{{lang.member_m_center}}</span>
                    <t-switch v-model="formData.clientarea_theme_mobile_switch " :custom-value="['1','0']"></t-switch>
                  </div>
                  <t-form-item v-show="formData.clientarea_theme_mobile_switch  === '1'">
                    <ul class="theme-box">
                      <li class="item" v-for="item in clientarea_theme_mobile_list" :key="item.name"
                        :class="{active: item.name === formData.clientarea_theme_mobile}"
                        @click="chooseMobileTheme(item)">
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
                </t-tab-panel>
              </t-tabs>
            </t-tab-panel>
            <t-tab-panel value="cart_theme" :label="lang.cart_theme">
              <t-tabs v-model="cart_type" class="chiled-tabs">
                <t-tab-panel value="pc" label="PC">
                  <t-form-item name="cart_admin" :label="lang.cart_theme">
                    <ul class="theme-box">
                      <li class="item" v-for="item in cart_theme_list" :key="item.name"
                        :class="{active: item.name === formData.cart_theme}" @click="cartTheme(item)">
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
                </t-tab-panel>
                <t-tab-panel value="mobile" :label="lang.finance_search_text7">
                  <div>
                    <span>{{lang.cart_theme_mobile}}</span>
                    <t-switch v-model="formData.clientarea_theme_mobile_switch" :custom-value="['1','0']"></t-switch>
                  </div>
                  <t-form-item name="cart_admin" v-show="formData.clientarea_theme_mobile_switch  === '1'">
                    <ul class="theme-box">
                      <li class="item" v-for="item in cart_theme_mobile_list" :key="item.name"
                        :class="{active: item.name === formData.cart_theme_mobile}" @click="cartMobileTheme(item)">
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
                </t-tab-panel>
              </t-tabs>
              <div style="display: flex; column-gap: 20px; margin-top: 30px;">
                <t-form-item name="first_navigation" :label="lang.nav_text16">
                  <t-input style="width: 300px;" v-model="formData.first_navigation" :placeholder="lang.nav_text16">
                  </t-input>
                </t-form-item>
                <t-form-item name="second_navigation" :label="lang.nav_text17">
                  <t-input style="width: 300px;" v-model="formData.second_navigation" :placeholder="lang.nav_text17">
                  </t-input>
                </t-form-item>
              </div>
            </t-tab-panel>
            <t-tab-panel value="web_switch" :label="lang.official_theme">
              <div>
                <span>{{lang.official_theme}}</span>
                <t-switch v-model="formData.web_switch" :custom-value="['1','0']"></t-switch>
              </div>
              <t-form-item v-show="formData.web_switch === '1'">
                <ul class="theme-box">
                  <li class="item" v-for="item in web_theme_list" :key="item.name"
                    :class="{active: item.name === formData.web_theme}" @click="chooseWebTheme(item)">
                    <div class="icon">
                      <t-icon name="check"></t-icon>
                    </div>
                    <div class="img">
                      <img :src="item.img" alt="">
                      <t-button @click="jumpController(item)" class="jump_controller">
                        {{lang.theme_controller}}
                        <t-tooltip :content="lang.theme_controller_tip" overlay-class-name="theme_controller_tip"
                          :show-arrow="false" theme="light" placement="top-left">
                          <t-icon name="help-circle"></t-icon>
                        </t-tooltip>
                      </t-button>
                    </div>
                    <p class="text">{{item.name}}</p>
                  </li>
                </ul>
              </t-form-item>
            </t-tab-panel>
            <t-form-item style="margin-top: 50px;">
              <t-button theme="primary" type="submit" :loading="submitLoading"
                v-permission="'auth_system_configuration_system_configuration_theme_configuration_save_configuration'">{{lang.hold}}</t-button>
            </t-form-item>
          </t-tabs>
        </t-form>
      </div>
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/configuration_theme.js"></script>
{include file="footer"}
