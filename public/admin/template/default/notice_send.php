{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="notice-send table" v-cloak>
  <t-card class="list-card-container">
    <!-- <ul class="common-tab">
      <li>
        <a href="notice_sms.html">{{lang.sms_interface}}</a>
      </li>
      <li>
        <a href="notice_email.html">{{lang.email_interface}}</a>
      </li>
      <li class="active">
        <a href="javascript:;">{{lang.send_manage}}</a>
      </li>
    </ul> -->
    <div class="common-header">
      <div class="header-left">
        <t-button @click="save" class="add">{{lang.hold}}</t-button>
      </div>
    </div>
    <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading"
      :table-layout="tableLayout ? 'auto' : 'fixed'" :max-height="maxHeight">
      <template #name="{row}">
        {{row.name_lang}}
      </template>
      <template #sms_global_name="{row}">
        <t-select v-model="formData[row.name].sms_global_name">
          <t-option v-for="item in smsInterList" :value="item.name" :label="item.title" :key="item.id"></t-option>
        </t-select>
      </template>
      <template #sms_global_template="{row}">
        <t-select v-model="formData[row.name].sms_global_template" :disabled="!row.sms_global_name">
          <!-- 国际短信模板 -->
          <t-option v-for="item in interTempObj[row.sms_global_name+'_interTemp']" :value="item.id" :label="item.title" :key="item.id"></t-option>
        </t-select>
      </template>
      <template #sms_name="{row}">
        <t-select v-model="formData[row.name].sms_name">
          <t-option v-for="item in smsList" :value="item.id" :label="item.title" :key="item.id"></t-option>
        </t-select>
      </template>
      <template #sms_template="{row}">
        <t-select v-model="formData[row.name].sms_template" :disabled="!row.sms_name">
          <t-option v-for="item in tempObj[row.sms_name+'_temp']" :value="item.id" :label="item.title" :key="item.id"></t-option>
        </t-select>
      </template>
      <template #sms_enable="{row}">
        <t-switch size="large" v-model="formData[row.name].sms_enable" :custom-value="[1,0]"></t-switch>
      </template>
      <template #email_enable="{row}">
        <t-switch size="large" v-model="formData[row.name].email_enable" :custom-value="[1,0]"></t-switch>
      </template>
      <template #sms_name="{row}">
        <t-select v-model="formData[row.name].sms_name" >
          <t-option v-for="item in smsList" :value="item.name" :label="item.title" :key="item.id"></t-option>
        </t-select>
      </template>
      <template #email_name="{row}">
        <t-select v-model="formData[row.name].email_name" >
          <t-option v-for="item in emailList" :value="item.name" :label="item.title" :key="item.id"></t-option>
        </t-select>
      </template>
      <template #email_template="{row}">
        <t-select v-model="formData[row.name].email_template" :disabled="!row.email_name">
          <t-option v-for="item in emailTemplateList" :value="item.id" :label="item.subject" :key="item.id"></t-option>
        </t-select>
      </template>
    </t-table>
  </t-card>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/notice_send.js"></script>
{include file="footer"}