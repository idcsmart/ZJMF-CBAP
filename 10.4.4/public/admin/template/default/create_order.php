{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<!-- =======内容区域======= -->
<div id="content" class="create-order" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <template>
        <p class="com-h-tit">{{lang.create_order}}</p>
        <div class="box">
          <t-form :data="formData" :label-width="80" ref="userDialog" label-width="200" @submit="onSubmit"
            label-align="top" :rules="rules">
            <div class="top-select">
              <div class="item">
                <t-form-item :label="lang.name" name="client_id">
                  <!-- 选择用户 -->
                  <com-choose-user :pre-placeholder="lang.example" @changeuser="chooseUser">
                  </com-choose-user>
                </t-form-item>
              </div>
              <div class="item">
                <t-form-item :label="lang.order_type" name="type">
                  <t-select v-model="formData.type" :placeholder="lang.select+lang.order_type"
                    :disabled="formData.client_id ? false : true" @change="changeType">
                    <t-option v-for="item in orderType" :value="item.type" :label="item.name" :key="item.type">
                    </t-option>
                  </t-select>
                </t-form-item>
              </div>
              <div class="item" v-if="formData.type==='new' && formData.client_id ">
                <t-form-item :label="lang.order_text87" name="newProductId">
                  <!-- 选择商品 -->
                  <com-tree-select @choosepro="choosePro" :pre-placeholder='lang.tailorism'></com-tree-select>
                </t-form-item>
              </div>
            </div>
            <t-divider></t-divider>
            <p class="com-tit" v-if="formData.type"><span>{{ lang[formData.type] }}</span></p>
            <!-- 人工订单 -->
            <div class="artificial" v-if="formData.type==='artificial'">
              <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 90 }">
                <t-col :xs="12" :xl="6">
                  <t-card bordered shadow>
                    <t-form-item :label="lang.description" name="description">
                      <t-textarea :placeholder="lang.description" v-model="formData.description" />
                    </t-form-item>
                    <t-form-item :label="lang.price" name="amount">
                      <t-input :placeholder="lang.price" v-model="formData.amount" :label="currency_prefix" />
                    </t-form-item>
                  </t-card>
                </t-col>
              </t-row>
              <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 90 }">
                <t-col :xs="12" :xl="6">
                  <div class="item bot">
                    <t-form-item :label="lang.total_price">
                      <t-input placeholder="" disabled v-model="formData.amount" :label="currency_prefix" />
                    </t-form-item>
                    <t-button type="submit" :loading="submitLoading">{{lang.submit_order}}</t-button>
                  </div>
                </t-col>
              </t-row>
            </div>
            <!-- 新订单 -->
            <div class="new-order" v-if="formData.type==='new' && formData.client_id && formData.newProductId">
              <iframe ref="Iframe" class="iframe-body" :src="newOrderIframeUrl" frameborder="0" width="100%"
                height="1000"></iframe>
              <div class="edit-price">
                <span>{{lang.order_text83}}</span>
                <t-switch v-model="newBuyData.isEditPrice"></t-switch>
                <t-input-number v-if="newBuyData.isEditPrice" v-model="newBuyData.price" theme="normal" :min="0"
                  :placeholder="lang.order_text84"></t-input-number>
              </div>
              <div class="edit-price" style="border: none;">
                <span>{{lang.order_text88}}</span>
                <t-switch v-model="newBuyData.custom_renew_amount_switch"></t-switch>
                <t-input-number v-if="newBuyData.custom_renew_amount_switch" v-model="newBuyData.custom_renew_amount"
                  theme="normal" :min="0" :placeholder="lang.order_text89"></t-input-number>
              </div>
              <t-button type="submit" :loading="newBuyData.loading">{{lang.order_text85}}</t-button>
            </div>
            <!-- 续费订单 -->
            <div class="new-order" v-if="formData.type==='renew'">
              <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 90 }">
                <t-col :xs="12" :xl="6">
                  <div class="pro-item">
                    <!-- 选择已开通产品 -->
                    <t-form-item :label="lang.choose_product" class="price">
                      <t-select v-model="renewIds" :placeholder="lang.select+lang.tailorism" @change="chooseRenew">
                        <t-option v-for="item in clientShopList" :value="item.id"
                          :label="item.product_name+'('+item.name+')'" :key="item.id">
                        </t-option>
                      </t-select>
                    </t-form-item>
                    <div class="config-area">
                      <div class="config-item" :class="selectRenewIndex===index ? 'active' : ''"
                        v-for="(item,index) in renewHostList" :key="index" @click="selectRenew(index)">
                        <div>{{item.billing_cycle}}</div>
                        <div>{{currency_prefix}}<span class="font-price">{{item.price}}</span></div>
                      </div>
                    </div>
                    <t-form-item :label="lang.order_text86" v-if="renewHostList.length > 0">
                      <t-input-number :label="currency_prefix" v-model="renewsParms.customfield.custom_amount"
                        theme="normal" :min="0" :step="0.01" :decimal-places="2"></t-input-number>
                    </t-form-item>
                  </div>
                </t-col>
              </t-row>
              <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 90 }">
                <t-col :xs="12" :xl="6">
                  <div class="item bot">
                    <t-form-item :label="lang.total_price">
                      <t-input placeholder="" disabled v-model="renewsParms.customfield.custom_amount"
                        :label="currency_prefix" />
                    </t-form-item>
                    <t-button type="submit" style="margin-top: 20px;"
                      :loading="submitLoading">{{lang.submit_order}}</t-button>
                  </div>
                </t-col>
              </t-row>
            </div>
          </t-form>
        </div>
      </template>
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/components/comTreeSelect/comTreeSelect.js"></script>
<script src="/{$template_catalog}/template/{$themes}/components/comChooseUser/comChooseUser.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/create_order.js"></script>
{include file="footer"}