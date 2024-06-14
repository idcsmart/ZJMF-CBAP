{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<!-- =======内容区域======= -->
<div id="content" class="order-details hasCrumb" v-cloak>
  <com-config>
    <div class="com-crumb">
      <span>{{lang.business_manage}}</span>
      <t-icon name="chevron-right"></t-icon>
      <span style="cursor: pointer;" @click="goOrder">{{lang.order_manage}}</span>
      <t-icon name="chevron-right"></t-icon>
      <span class="cur">{{lang.notes}}</span>
      <span class="back-text" @click="goBack">
        <t-icon name="chevron-left-double"></t-icon>{{lang.back}}
      </span>
    </div>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li v-if="$checkPermission('auth_business_order_detail_order_detail_view') || $checkPermission('auth_user_detail_order_check_order')">
          <a :href="`order_details.htm?id=${id}`">{{lang.create_order_detail}}</a>
        </li>
        <li v-permission="'auth_business_order_detail_refund_record_view'">
          <a :href="`order_refund.htm?id=${id}`">{{lang.refund_record}}</a>
        </li>
        <li v-permission="'auth_business_order_detail_transaction'">
          <a :href="`order_flow.htm?id=${id}`">{{lang.flow}}</a>
        </li>
        <li v-if="hasCostPlugin" v-permission="'auth_addon_cost_pay_show_tab'">
          <a :href="`plugin/cost_pay/order_cost.htm?id=${id}`">{{lang.piece_cost}}</a>
        </li>
        <li class="active" v-permission="'auth_business_order_detail_notes_view'">
          <a>{{lang.notes}}</a>
        </li>
      </ul>
      <t-form :data="formData" ref="userInfo" @submit="onSubmit" :rules="rules">
        <t-form-item :label="lang.notes" label-align="top" name="notes">
          <t-textarea v-model="formData.notes" :placeholder="`${lang.notes}`" :disabled="formData.is_recycle === 1"></t-textarea>
        </t-form-item>
        <t-form-item>
          <t-button theme="primary" type="submit" :loading="loading"
          v-if="$checkPermission('auth_business_order_detail_notes_save_notes') && formData.is_recycle === 0"
          >{{lang.hold}}</t-button>
        </t-form-item>
      </t-form>
    </t-card>
    <div class="deleted-svg">
      <img :src="`${rootRul}img/deleted.svg`" alt="" v-show="formData.is_recycle">
    </div>
  </com-config>
</div>
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/order_notes.js"></script>
{include file="footer"}
