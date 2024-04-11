{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/product.css">
<div id="content" class="product-detail hasCrumb" v-cloak>
  <com-config>
    <!-- crumb -->
    <div class="com-crumb">
      <span>{{lang.refund_commodit_management}}</span>
      <t-icon name="chevron-right"></t-icon>
      <a href="product.htm">{{lang.product_list}}</a>
      <t-icon name="chevron-right"></t-icon>
      <span class="cur">{{lang.basic_info}}</span>
    </div>
    <t-card class="product-container">
      <ul class="common-tab">
        <li class="active" v-permission="'auth_product_detail_basic_info_view'">
          <a>{{lang.basic_info}}</a>
        </li>
        <li v-permission="'auth_product_detail_server_view'">
          <a :href="`product_api.htm?id=${id}`">{{lang.interface_manage}}</a>
        </li>
        <li v-permission="'auth_product_detail_custom_field_view'">
          <a :href="`product_self_field.htm?id=${id}`">{{lang.product_selef_text1}}</a>
        </li>
      </ul>
      <div class="box">
        <t-form :data="formData" :rules="rules" ref="userInfo">
          <t-row :gutter="{ xs: 0, sm: 20, md: 40, lg: 60, xl: 80, xxl: 100 }">
            <!-- 个人中心左侧 -->
            <t-col :xs="12" :xl="6">
              <p class="com-tit"><span>{{ lang.basic_info }}</span></p>
              <div class="item">
                <t-form-item :label="lang.product_name" name="name">
                  <t-input v-model="formData.name" :placeholder="lang.product_name"></t-input>
                </t-form-item>
                <t-form-item :label="lang.product_group" name="product_group_id">
                  <t-select v-model="formData.product_group_id" :popup-props="popupProps" :placeholder="lang.group">
                    <t-option v-for="item in secondGroup" :value="item.id" :label="item.name" :key="item.id">
                    </t-option>
                  </t-select>
                </t-form-item>
              </div>
              <div class="item">
                <t-form-item :label="lang.inventory" name="qty">
                  <t-input v-model="formData.qty" :placeholder="lang.inventory"></t-input>
                  <t-switch size="medium" :custom-value="[1,0]" v-model="formData.stock_control"></t-switch>
                </t-form-item>
                <t-form-item :label="lang.hidden" name="hidden">
                  <t-select v-model="formData.hidden">
                    <t-option :value="1" :label="lang.open" :key="1"></t-option>
                    <t-option :value="0" :label="lang.close" :key="0"></t-option>
                  </t-select>
                </t-form-item>
              </div>
              <div class="item">
                <t-form-item :label="lang.start_price" style="margin-bottom: 24px;">
                  <t-input-number v-model="formData.price" theme="normal" :min="0" :decimal-places="2" :placeholder="lang.start_price_tip">
                  </t-input-number>
                </t-form-item>
              </div>
              <t-form-item :label="lang.product_descript" name="description" class="textarea">
                <t-textarea :placeholder="lang.product_descript" v-model="formData.description" />
              </t-form-item>
              <p class="com-tit connect"><span>{{ lang.connect }}</span></p>
              <div class="item">
                <t-form-item :label="lang.connect_goods" name="address">
                  <t-select v-model="formData.product_id" clearable :disabled="formData?.plugin_custom_fields?.is_link" :placeholder="lang.connect_goods">
                    <t-option v-for="item in relationList" :key="item.id" :value="item.id" :label="item.name">
                    </t-option>
                  </t-select>
                </t-form-item>
              </div>
              <p class="com-tit connect"><span>{{ lang.upAndDown }}</span></p>
              <div class="item">
                <t-form-item :label="lang.demote_range" name="language">
                  <t-select v-model="formData.upgrade" multiple :min-collapsed-num="1" :popup-props="popupProps">
                    <t-option v-for="item in relationList" :value="item.id" :label="item.name" :key="item.id">
                    </t-option>
                  </t-select>
                </t-form-item>
              </div>
            </t-col>
            <!-- 个人中心右侧 -->
            <t-col :xs="12" :xl="6" class="r-box">
              <p class="com-tit"><span>{{lang.product_notice}}</span></p>
              <t-row :gutter="{ xs: 0, xxl: 30 }" class="dis-box">
                <t-col :xs="12" :xl="6">
                  <p class="small-tit">{{lang.sms_notice}}</p>
                  <t-form-item :label="lang.open_notice">
                    <t-radio-group name="creating_notice_sms" v-model="formData.creating_notice_sms" :options="checkOptions">
                    </t-radio-group>
                  </t-form-item>
                </t-col>
                <t-col :xs="12" :xl="6">
                  <p class="small-tit">{{lang.email_notice}}</p>
                  <t-form-item :label="lang.open_notice">
                    <t-radio-group name="creating_notice_sms" v-model="formData.creating_notice_mail" :options="checkOptions">
                    </t-radio-group>
                  </t-form-item>
                </t-col>
                <t-col :xs="12" :xl="6" class="half">
                  <t-form-item :label="lang.sms_interface">
                    <t-select v-model="formData.creating_notice_sms_api" @change="changeSmsInterface">
                      <t-option v-for="item in smsInterList" :value="item.id" :label="item.title" :key="item.id">
                      </t-option>
                    </t-select>
                  </t-form-item>
                  <t-form-item :label="lang.choose_template">
                    <t-select v-model="formData.creating_notice_sms_api_template">
                      <t-option v-for="item in smsTempList[creatingName]" :value="item.id" :label="item.title" :key="item.id">
                      </t-option>
                    </t-select>
                  </t-form-item>
                </t-col>
                <t-col :xs="12" :xl="6" class="half">
                  <t-form-item :label="lang.email_interface" name="hidden">
                    <t-select v-model="formData.creating_notice_mail_api">
                      <t-option v-for="item in emailInterList" :value="item.id" :label="item.title" :key="item.name">
                      </t-option>
                    </t-select>
                  </t-form-item>
                  <t-form-item :label="lang.email_temp">
                    <t-select v-model="formData.creating_notice_mail_template">
                      <t-option v-for="item in emailInterTemp" :value="item.id" :label="item.name" :key="item.id">
                      </t-option>
                    </t-select>
                  </t-form-item>
                </t-col>
                <t-col :xs="12" :xl="6">
                  <t-form-item :label="lang.opened_notice">
                    <t-radio-group v-model="formData.created_notice_sms" :options="checkOptions">
                    </t-radio-group>
                  </t-form-item>
                </t-col>
                <t-col :xs="12" :xl="6">
                  <t-form-item :label="lang.opened_notice">
                    <t-radio-group v-model="formData.created_notice_mail" :options="checkOptions">
                    </t-radio-group>
                  </t-form-item>
                </t-col>
                <t-col :xs="12" :xl="6" class="half">
                  <t-form-item :label="lang.sms_interface">
                    <t-select v-model="formData.created_notice_sms_api" @change="changeCreated">
                      <t-option v-for="item in smsInterList" :value="item.id" :label="item.title" :key="item.id">
                      </t-option>
                    </t-select>
                  </t-form-item>
                  <t-form-item :label="lang.choose_template">
                    <t-select v-model="formData.created_notice_sms_api_template">
                      <t-option v-for="item in smsTempList[createdName]" :value="item.id" :label="item.title" :key="item.id">
                      </t-option>
                    </t-select>
                  </t-form-item>
                </t-col>
                <t-col :xs="12" :xl="6" class="half">
                  <t-form-item :label="lang.email_interface" name="">
                    <t-select v-model="formData.created_notice_mail_api">
                      <t-option v-for="item in emailInterList" :value="item.id" :label="item.title" :key="item.id">
                      </t-option>
                    </t-select>
                  </t-form-item>
                  <t-form-item :label="lang.email_temp" name="">
                    <t-select v-model="formData.created_notice_mail_template">
                      <t-option v-for="item in emailInterTemp" :value="item.id" :label="item.name" :key="item.id">
                      </t-option>
                    </t-select>
                  </t-form-item>
                </t-col>
              </t-row>
              <p class="com-tit free" style="margin-top: 70px;"><span>{{lang.cost}}</span></p>
              <!-- 费用类型 -->
              <t-row :gutter="{ xs: 0, xxl: 30 }" class="dis-box">
                <t-col :xs="12" :xl="6">
                  <p>{{lang.cost_type}}</p>
                  <t-popconfirm :visible="visibleFree" theme="warning" @confirm="confirmChange" @cancel="visibleFree = false" :content="lang.free_type_tip + '\n' + lang.free_type_tip1">
                    <t-select :value="formData.pay_type" @change="changeFreeType">
                      <t-option v-for="item in payType" :value="item.value" :label="item.label" :key="item.value">
                      </t-option>
                    </t-select>
                  </t-popconfirm>
                </t-col>
              </t-row>
            </t-col>
          </t-row>
        </t-form>
        <!-- 底部操作按钮 -->
        <div class="footer-btn">
          <t-button theme="primary" @click="updateUserInfo" type="submit" :loading="submitLoading" v-permission="'auth_product_detail_basic_info_save_info'">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="back">{{lang.close}}</t-button>
        </div>
      </div>
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/product_detail.js"></script>
{include file="footer"}
