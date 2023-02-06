<link rel="stylesheet" href="/plugins/addon/idcsmart_certification/template/admin/css/real_name.css" />
<div id="content" class="real_name_setting" v-cloak>

  <t-card class="list-card-container">

    <ul class="common-tab">
      <li>
        <a href="index.html">{{lang.real_name_approval}}</a>
      </li>
      <li class="active">
        <a href="javascript:;">{{lang.real_name_setting}}</a>
      </li>
      <li>
        <a href="real_name_interface.html">{{lang.interface_manage}}</a>
      </li>
    </ul>
    <t-form :data="formData" ref="userDialog" @submit="onSubmit" :label-width="200">
      <t-form-item :label="lang.auth_real_name">
        <t-switch :custom-value="[1,0]" v-model="formData.certification_open" :label="[lang.switch_open, lang.switch_close]">
        </t-switch>
      </t-form-item>
      <t-form-item :label="lang.manual_review">
        <t-switch :custom-value="[1,0]" v-model="formData.certification_approval" :label="[lang.switch_open, lang.switch_close]">
        </t-switch>
        <span>{{lang.real_tip1}}</span>
      </t-form-item>
      <t-form-item :label="lang.notice_user">
        <t-switch :custom-value="[1,0]" v-model="formData.certification_notice" :label="[lang.switch_open, lang.switch_close]">
        </t-switch>
      </t-form-item>
      <t-form-item :label="lang.auto_update">
        <t-switch :custom-value="[1,0]" v-model="formData.certification_update_client_name" :label="[lang.switch_open, lang.switch_close]">
        </t-switch>
        <span>{{lang.real_tip2}}</span>
      </t-form-item>
      <t-form-item :label="lang.upload_img">
        <t-switch :custom-value="[1,0]" v-model="formData.certification_upload" :label="[lang.switch_open, lang.switch_close]">
        </t-switch>
        <span>{{lang.real_tip3}}</span>
      </t-form-item>
      <t-form-item :label="lang.phone_uniformity">
        <t-switch :custom-value="[1,0]" v-model="formData.certification_update_client_phone" :label="[lang.switch_open, lang.switch_close]">
        </t-switch>
        <span>{{lang.real_tip4}}</span>
      </t-form-item>
      <t-form-item :label="lang.product_stop">
        <t-switch :custom-value="[1,0]" v-model="formData.certification_uncertified_cannot_buy_product" :label="[lang.switch_open, lang.switch_close]">
        </t-switch>
        <span>{{lang.real_tip5}}</span>
      </t-form-item>
      <div class="f-btn">
        <t-button theme="primary" type="submit" :loading="loading">{{lang.hold}}</t-button>
      </div>
    </t-form>
  </t-card>

</div>

<script src="/plugins/addon/idcsmart_certification/template/admin/api/real_name.js"></script>
<script src="/plugins/addon/idcsmart_certification/template/admin/js/real_name_setting.js"></script>