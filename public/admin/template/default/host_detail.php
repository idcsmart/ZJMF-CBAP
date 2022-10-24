{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<!-- =======内容区域======= -->
<div id="content" class="host-detail table hasCrumb" v-cloak>
  <!-- crumb -->
  <div class="com-crumb">
    <span>{{lang.user_manage}}</span>
    <t-icon name="chevron-right"></t-icon>
    <a href="client.html">{{lang.user_list}}</a>
    <t-icon name="chevron-right"></t-icon>
    <span class="cur">{{lang.product_info}}</span>
  </div>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li>
        <a :href="`client_detail.html?id=${client_id}`">{{lang.personal}}</a>
      </li>
      <li class="active">
        <a :href="`client_host.html?id=${client_id}`">{{lang.product_info}}</a>
      </li>
      <li>
        <a :href="`client_order.html?id=${client_id}`">{{lang.order_manage}}</a>
      </li>
      <li>
        <a :href="`client_transaction.html?id=${client_id}`">{{lang.flow}}</a>
      </li>
      <li>
        <a :href="`client_log.html?id=${client_id}`">{{lang.log}}</a>
      </li>
      <li>
        <a :href="`client_notice_sms.html?id=${id}`">{{lang.notice_log}}</a>
      </li>
    </ul>
    <div class="box scrollbar">
      <t-form :data="formData" :rules="rules" ref="userInfo" label-align="top">
        <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 90 }">
          <t-col :xs="12" :xl="6">
            <p class="com-tit"><span>{{ lang.basic_info }}</span></p>
            <div class="item">
              <t-form-item :label="lang.product" name="product_id">
                <t-select v-model="formData.product_id" :popup-props="popupProps">
                  <t-option v-for="item in proList" :value="item.id" :label="item.name" :key="item.id">
                  </t-option>
                </t-select>
              </t-form-item>
              <t-form-item :label="lang.interface" v-if="done">
                <t-select v-model="formData.server_id" :popup-props="popupProps">
                  <t-option v-for="item in serverList" :value="item.id" :label="item.name" :key="item.id">
                  </t-option>
                </t-select>
              </t-form-item>
            </div>
            <div class="item">
              <t-form-item :label="lang.host_name" name="name">
                <t-input v-model="formData.name" :placeholder="lang.input+lang.host_name"></t-input>
              </t-form-item>
            </div>
            <t-form-item :label="lang.admin_notes" name="notes">
              <t-textarea v-model="formData.notes" :placeholder="lang.input+lang.admin_notes"></t-textarea>
            </t-form-item>
          </t-col>
          <t-col :xs="12" :xl="6">
            <p class="com-tit"><span>{{lang.financial_info}}</span></p>
            <!-- 续费 -->
            <t-button theme="primary" class="renew-btn" @click="renewDialog" v-if="(curStatus === 'Active' || curStatus === 'Suspended') && hasPlugin">{{lang.renew}}</t-button>
            <div class="item">
              <t-form-item :label="lang.buy_amount" name="first_payment_amount">
                <t-input v-model="formData.first_payment_amount" :placeholder="lang.input+lang.buy_amount">
                </t-input>
              </t-form-item>
              <t-form-item :label="lang.renew_amount" name="renew_amount">
                <t-input v-model="formData.renew_amount" :placeholder="lang.input+lang.renew_amount"></t-input>
              </t-form-item>
              <!-- <t-form-item :label="lang.discount">
                <t-select v-model="formData.rel_id">
                  <t-option v-for="item in curList" :value="item.id" :label="item.name" :key="item.id">
                  </t-option>
                </t-select>
              </t-form-item> -->
              <t-form-item :label="lang.billing_way">
                <t-select v-model="formData.billing_cycle" :popup-props="popupProps">
                  <t-option v-for="item in cycleList" :value="item.value" :label="item.label" :key="item.value">
                  </t-option>
                </t-select>
              </t-form-item>
              <t-form-item :label="lang.billing_cycle">
                <t-input v-model="formData.billing_cycle_name" disabled></t-input>
              </t-form-item>
              <t-form-item :label="lang.open_time" name="active_time" :rules="[{ validator: checkTime}]">
                <t-date-picker mode="date" format="YYYY-MM-DD HH:mm:ss" enable-time-picker v-model="formData.active_time" @change="changeActive" />
              </t-form-item>
              <t-form-item :label="lang.due_time" name="due_time" :rules="[{ validator: checkTime1}]">
                <t-date-picker mode="date" format="YYYY-MM-DD HH:mm:ss" enable-time-picker v-model="formData.due_time" @change="changeActive" :disabled="disabled" />
              </t-form-item>
              <t-form-item :label="lang.status">
                <t-select v-model="formData.status" :popup-props="popupProps">
                  <t-option v-for="item in status" :value="item.value" :label="item.label" :key="item.value">
                  </t-option>
                </t-select>
              </t-form-item>
              <!-- <t-form-item>
                <t-button @click="renew">续费</t-button>
              </t-form-item> -->
            </div>
          </t-col>
          <t-col :xs="12" :xl="6">
            <div class="config-area" v-html="config"></div>
          </t-col>
        </t-row>
      </t-form>
    </div>
    <div class="footer-btn">
      <t-button theme="primary" type="submit" @click="updateUserInfo">{{lang.hold}}</t-button>
      <t-button theme="default" variant="base" @click="back">{{lang.delete}}</t-button>
    </div>
  </t-card>
  <!-- 删除 -->
  <t-dialog theme="warning" :header="lang.sureDelete" :close-btn="false" :visible.sync="delVisible">
    <template slot="footer">
      <div class="common-dialog">
        <t-button @click="onConfirm">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </div>
    </template>
  </t-dialog>
  <!-- 续费弹窗 -->
  <t-dialog :header="lang.renew" :visible.sync="renewVisible" class="renew-dialog" :footer="false">
    <div class="swiper" v-if="renewList.length >0 ">
      <div class="l-btn" @click="subIndex">
        <t-icon name="chevron-left"></t-icon>
      </div>
      <div class="m-box">
        <div class="swiper-item" v-for="(item,index) in renewList" :key="item.id" :class="{card: item.id === showId[0] || item.id === showId[1] || item.id === showId[2], active: item.id === curId}" @click="checkCur(item)">
          <p class="cycle">{{item.billing_cycle}}</p>
          <p class="price"><span>{{currency_prefix}}</span>{{item.price}}</p>
        </div>
      </div>
      <div class="r-btn" @click="addIndex">
        <t-icon name="chevron-right"></t-icon>
      </div>
    </div>
    <div class="com-f-btn">
      <div class="total">{{lang.total}}：<span class="price"><span class="symbol">{{currency_prefix}}</span>{{curRenew.price}}</span></div>
      <div>
        <t-checkbox v-model="pay">{{lang.mark_Paid}}</t-checkbox>
      </div>
      <t-button theme="primary" @click="submitRenew" :loading="submitLoading">{{lang.sure_renew}}</t-button>
    </div>
  </t-dialog>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/host_detail.js"></script>
{include file="footer"}