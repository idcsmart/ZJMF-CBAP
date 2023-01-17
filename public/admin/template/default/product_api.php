{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/product.css">
<script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
<div id="content" class="product-api hasCrumb" v-cloak>
  <!-- crumb -->
  <div class="com-crumb">
    <span>{{lang.refund_commodit_management}}</span>
    <t-icon name="chevron-right"></t-icon>
    <a href="product.html">{{lang.product_list}}</a>
    <t-icon name="chevron-right"></t-icon>
    <span class="cur">{{lang.interface_manage}}</span>
  </div>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li>
        <a :href="`product_detail.html?id=${id}`">{{lang.basic_info}}</a>
      </li>
      <li class="active">
        <a href="javascript:;">{{lang.interface_manage}}</a>
      </li>
    </ul>
    <div class="box">
      <t-row>
        <p class="com-tit"><span>{{ lang.interface_manage }}</span></p>
        <t-col :xs="12" :xl="6">
          <t-form :data="formData" ref="userInfo" @submit="onSubmit">
            <div class="item">
              <t-form-item :label="lang.auto_setup">
                <t-radio-group name="creating_notice_sms" v-model="formData.auto_setup" :options="checkOptions">
                </t-radio-group>
              </t-form-item>
              <t-form-item :label="lang.choose_interface_type">
                <t-select v-model="formData.type" @change="changeType">
                  <t-option value="server" :label="lang.interface" key="server"></t-option>
                  <t-option value="server_group" :label="`${lang.interface}${lang.group}`" key="server_group"></t-option>
                </t-select>
              </t-form-item>
              <t-form-item :label="lang.choose_interface">
                <t-select v-model="formData.rel_id" :disabled="!formData.type" @change="chooseInterfaceId" :key="formData.rel_id">
                  <t-option v-for="item in curList" :value="item.id" :label="item.name" :key="item.id">
                  </t-option>
                </t-select>
              </t-form-item>
              <t-form-item>
                <t-button theme="primary" type="submit">{{lang.hold}}</t-button>
                <!-- <t-button theme="default" variant="base" type="reset" @click="back">{{lang.close}}</t-button> -->
              </t-form-item>
            </div>
          </t-form>
        </t-col>
      </t-row>
    </div>
  </t-card>
</div>
<!-- 后端渲染出来的配置页面 -->
<div class="config-box">
  <div class="content"></div>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/product_api.js"></script>
{include file="footer"}