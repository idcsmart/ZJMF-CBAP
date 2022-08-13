{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="configuration-currency" v-cloak>
  <t-card class="list-card-container">
    <!-- <p class="com-h-tit">{{lang.currency_setting}}</p> -->
    <div class="box">
      <t-form :data="formData" :rules="rules" :label-width="80" label-align="top" ref="formValidatorStatus" @submit="onSubmit">
        <p class="com-tit"><span>{{ lang.currency_setting }}</span></p>
        <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
          <t-col :xs="12" :xl="3" :md="6">
            <t-form-item name="currency_code" :label="lang.currency_code">
              <t-input v-model="formData.currency_code" :placeholder="lang.input">
              </t-input>
            </t-form-item>
          </t-col>
          <t-col :xs="12" :xl="3" :md="6">
            <t-form-item name="currency_prefix" :label="lang.currency_prefix">
              <t-input v-model="formData.currency_prefix" :placeholder="lang.input">
              </t-input>
            </t-form-item>
          </t-col>
        </t-row>
        <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
          <t-col :xs="12" :xl="3" :md="6">
            <t-form-item name="currency_suffix" :label="lang.currency_suffix">
              <t-input v-model="formData.currency_suffix" :placeholder="lang.input+lang.currency_code">
              </t-input>
            </t-form-item>
          </t-col>
        </t-row>
        <p class="com-tit"><span>{{ lang.currency_recharge }}</span></p>
        <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
          <t-col :xs="12" :xl="3" :md="6">
            <t-form-item name="recharge_open" :label="lang.recharge_open">
              <t-switch size="medium" :custom-value="[1,0]" v-model="formData.recharge_open"></t-switch>
              <t-icon name="error-circle" size="18"></t-icon>
              <span class="tip">{{lang.tip6}}</span>
            </t-form-item>
          </t-col>
          <t-col :xs="12" :xl="3" :md="6">
            <t-form-item name="recharge_min" :label="lang.recharge_min">
              <t-input v-model="formData.recharge_min" :placeholder="lang.input+lang.recharge_min">
              </t-input>
            </t-form-item>
          </t-col>
        </t-row>
        <div class="f-btn">
          <t-button theme="primary" type="submit">{{lang.hold}}</t-button>
          <!-- <t-button theme="default" variant="base">{{lang.close}}</t-button> -->
        </div>
      </t-form>
    </div>
  </t-card>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/configuration_currency.js"></script>
{include file="footer"}