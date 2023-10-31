{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<!-- =======内容区域======= -->
<div id="content" class="create-order" v-cloak>
  <t-card class="list-card-container">
    <template>
      <p class="com-h-tit">{{lang.create_order}}</p>
      <div class="box">
        <t-form :data="formData" :label-width="80" ref="userDialog" label-width="200" @submit="onSubmit" label-align="top" :rules="rules">
          <div class="top-select">
            <div class="item">
              <t-form-item :label="lang.name" name="client_id">
                <t-select v-model="formData.client_id" filterable :placeholder="lang.example" :loading="searchLoading" @search="remoteMethod" @clear="clearKey" :show-arrow="false" :filter="filterMethod" :popup-props="popupProps" class="user-select" @change="chooseUser">
                  <t-option v-for="item in userList" :value="item.id" :label="item.username" :key="item.id" class="com-custom">
                    <div>
                      <p>{{item.id}}-{{item.username}}</p>
                      <p v-if="item.phone" class="tel">+{{item.phone_code}}-{{item.phone}}</p>
                      <p v-else class="tel">{{item.email}}</p>
                    </div>
                  </t-option>
                </t-select>
              </t-form-item>
            </div>
            <div class="item">
              <t-form-item :label="lang.order_type" name="type">
                <t-select v-model="formData.type" :placeholder="lang.select+lang.order_type" :disabled="formData.client_id ? false : true" @change="changeType">
                  <t-option v-for="item in orderType" :value="item.type" :label="item.name" :key="item.type">
                  </t-option>
                </t-select>
              </t-form-item>
            </div>
            <div class="item" v-if="formData.type==='new' && formData.client_id ">
              <t-form-item :label="lang.order_text87" name="newProductName">
                <t-popup placement="bottom-left" :destroy-on-close="true" :attach="`#myPopup`" :visible="visibleTreeObj[0].visibleTree">
                  <div :id="`myPopup`" class="productPop">
                    <t-input v-model="formData.newProductName" :placeholder='lang.tailorism' @click.native="focusHandler(0)" clearable @clear="clearId">
                      <template slot="suffix">
                        <t-icon name="chevron-down" size="18px" :class="{active:visibleTreeObj[0]?.visibleTree}">
                        </t-icon>
                      </template>
                    </t-input>
                  </div>
                  <template slot="content" class="test">
                    <t-tree :data="productList" :activable="true" :expand-on-click-node="true" ref="tree" @click="onClick" hover :label="getLabel" :expand-mutex="true" id="product-tree">
                    </t-tree>
                  </template>
                </t-popup>
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
                  <t-button type="submit">{{lang.submit_order}}</t-button>
                </div>
              </t-col>
            </t-row>
          </div>
          <!-- 新订单 -->
          <div class="new-order" v-if="formData.type==='new' && formData.client_id && formData.newProductId">
            <iframe ref="Iframe" class="iframe-body" :src="newOrderIframeUrl" frameborder="0" width="100%" height="1000"></iframe>
            <div class="edit-price">
              <span>{{lang.order_text83}}</span>
              <t-switch v-model="newBuyData.isEditPrice"></t-switch>
              <t-input-number v-if="newBuyData.isEditPrice" v-model="newBuyData.price" theme="normal" :min="0" :placeholder="lang.order_text84"></t-input-number>
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
                      <t-option v-for="item in clientShopList" :value="item.id" :label="item.product_name+'('+item.name+')'" :key="item.id">
                      </t-option>
                    </t-select>
                  </t-form-item>
                  <div class="config-area">
                    <div class="config-item" :class="selectRenewIndex===index ? 'active' : ''" v-for="(item,index) in renewHostList" :key="index" @click="selectRenew(index)">
                      <div>{{item.billing_cycle}}</div>
                      <div>{{currency_prefix}}<span class="font-price">{{item.price}}</span></div>
                    </div>
                  </div>
                  <t-form-item :label="lang.order_text86" v-if="renewHostList.length > 0">
                    <t-input-number :label="currency_prefix" v-model="renewsParms.customfield.custom_amount" theme="normal" :min="0" :step="0.01" :decimal-places="2"></t-input-number>
                  </t-form-item>
                </div>
              </t-col>
            </t-row>
            <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 90 }">
              <t-col :xs="12" :xl="6">
                <div class="item bot">
                  <t-form-item :label="lang.total_price">
                    <t-input placeholder="" disabled v-model="renewsParms.customfield.custom_amount" :label="currency_prefix" />
                  </t-form-item>
                  <t-button type="submit" style="margin-top: 20px;">{{lang.submit_order}}</t-button>
                </div>
              </t-col>
            </t-row>
          </div>
        </t-form>
      </div>
    </template>
  </t-card>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/create_order.js"></script>
{include file="footer"}