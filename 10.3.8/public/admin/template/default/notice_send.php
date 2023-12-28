{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="notice-send table" v-cloak>
  <t-card class="list-card-container">
    <!-- <ul class="common-tab">
      <li>
        <a href="notice_sms.htm">{{lang.sms_interface}}</a>
      </li>
      <li>
        <a href="notice_email.htm">{{lang.email_interface}}</a>
      </li>
      <li class="active">
        <a href="javascript:;">{{lang.send_manage}}</a>
      </li>
    </ul> -->
    <div class="common-header">
      <div class="header-left">
        <span class="tit">{{lang.system_default_setting}}</span>
        <t-form layout="inline" label-align="left" :data="formData" :rules="rules" ref="sendForm">
          <t-form-item :label="lang.sms_global_name" name="configuration.send_sms_global">
            <t-select v-model="formData.configuration.send_sms_global" class="demo-select-base" clearable 
            :placeholder="lang.sms_global_name">
              <t-option v-for="item in smsInterList" :value="item.name" :label="item.title" :key="item.id">
              </t-option>
            </t-select>
          </t-form-item>
          <t-form-item :label="lang.home_sms_interface" name="configuration.send_sms">
            <t-select v-model="formData.configuration.send_sms" class="demo-select-base" clearable
            :placeholder="lang.home_sms_interface">
              <t-option v-for="item in smsList" :value="item.name" :label="item.title" :key="item.id"></t-option>
            </t-select>
          </t-form-item>
          <t-form-item :label="lang.email_interface" name="configuration.send_email">
            <t-select v-model="formData.configuration.send_email" class="demo-select-base" clearable
            :placeholder="lang.email_interface">
              <t-option v-for="item in emailList" :value="item.name" :label="item.title" :key="item.id">
              </t-option>
            </t-select>
          </t-form-item>
        </t-form>
      </div>
    </div>
    <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" >
      <template #name="{row}">
        {{row.name_lang}}
      </template>
      <template #sms_global_name="{row,rowIndex}">
        <div :id="`sms_global_name${rowIndex}`">
          <t-select v-model="formData[row.name].sms_global_name" clearable @change="changeInter(row)" :popup-props="{ attach: `#sms_global_name${rowIndex}` }">
            <t-option v-for="item in smsInterList" :value="item.name" :label="item.title" :key="item.id"></t-option>
          </t-select>
        </div>
      </template>
      <!-- 国际短信模板 -->
      <template #sms_global_template="{row,rowIndex}">
        <div :id="`sms_global_template${rowIndex}`" :class="{required: formData[row.name].sms_global_name && !formData[row.name].sms_global_template , none_pop: !formData[row.name].sms_global_name }">
          <t-select v-model="formData[row.name].sms_global_template" clearable :disabled="!row.sms_global_name" ref='sms_global_template'
           :popup-props="{ attach: `#sms_global_template${rowIndex}` }">
            <t-option v-for="item in interTempObj[row.sms_global_name+'_interTemp']" :value="item.id" :label="item.title" :key="item.id"></t-option>
          </t-select>
        </div>
      </template>
      <template #sms_name="{row,rowIndex}">
        <div :id="`sms_name${rowIndex}`">
          <t-select v-model="formData[row.name].sms_name" clearable @change="changeHome(row)" :popup-props="{ attach: `#sms_name${rowIndex}` }">
            <t-option v-for="item in smsList" :value="item.name" :label="item.title" :key="item.id"></t-option>
          </t-select>
        </div>
      </template>
      <template #sms_template="{row,rowIndex}">
        <div :id="`sms_template${rowIndex}`" :class="{required: formData[row.name].sms_name && !formData[row.name].sms_template, none_pop: !formData[row.name].sms_name  }">
          <t-select v-model="formData[row.name].sms_template" clearable :disabled="!row.sms_name" :popup-props="{ attach: `#sms_template${rowIndex}` }">
            <t-option v-for="item in tempObj[row.sms_name+'_temp']" :value="item.id" :label="item.title" :key="item.id"></t-option>
          </t-select>
        </div>
      </template>
      <template #sms_enable="{row}">
        <t-switch size="large" v-model="formData[row.name].sms_enable" :custom-value="[1,0]"></t-switch>
      </template>
      <template #email_enable="{row}">
        <t-switch size="large" v-model="formData[row.name].email_enable" :custom-value="[1,0]"></t-switch>
      </template>
      <template #email_name="{row, rowIndex}">
        <div :id="`email_name${rowIndex}`">
          <t-select v-model="formData[row.name].email_name" clearable :popup-props="{ attach: `#email_name${rowIndex}` }">
            <t-option v-for="item in emailList" :value="item.name" :label="item.title" :key="item.id"></t-option>
          </t-select>
        </div>
      </template>
      <template #email_template="{row, rowIndex}">
        <div :id="`email_template${rowIndex}`" :class="{required: formData[row.name].email_name && !formData[row.name].email_template }">
          <t-select v-model="formData[row.name].email_template" clearable :disabled="!row.email_name" :popup-props="{ attach: `#email_template${rowIndex}` }">
            <t-option v-for="item in emailTemplateList" :value="item.id" :label="item.name" :key="item.id"></t-option>
          </t-select>
        </div>
      </template>
    </t-table>
    <t-button @click="save" class="add" :loading="submitLoading">{{lang.hold}}</t-button>
  </t-card>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/notice_send.js"></script>
{include file="footer"}