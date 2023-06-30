{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<!-- =======内容区域======= -->
<div id="content" class="order-details hasCrumb" v-cloak>
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
      <li>
        <a :href="`order_details.htm?id=${id}`">{{lang.create_order_detail}}</a>
      </li>
      <li>
        <a :href="`order_refund.htm?id=${id}`">{{lang.refund_record}}</a>
      </li>
      <li>
        <a :href="`order_flow.htm?id=${id}`">{{lang.flow}}</a>
      </li>
      <li class="active">
        <a>{{lang.notes}}</a>
      </li>
    </ul>
    <t-form :data="formData" ref="userInfo" @submit="onSubmit" :rules="rules">
      <t-form-item :label="lang.notes" label-align="top" name="notes">
        <t-textarea v-model="formData.notes" :placeholder="`${lang.notes}`"></t-textarea>
      </t-form-item>
      <t-form-item>
        <t-button theme="primary" type="submit" :loading="loading">{{lang.hold}}</t-button>
      </t-form-item>
    </t-form>
  </t-card>
</div>
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/order_notes.js"></script>
{include file="footer"}