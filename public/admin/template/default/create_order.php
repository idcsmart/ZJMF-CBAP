{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
<!-- =======内容区域======= -->
<t-content class="area">
  <!-- =======内容区域======= -->
  <div id="content" class="create-order" v-cloak>
    <t-card class="list-card-container">
      <p class="com-h-tit">{{lang.create_order}}</p>
      <div class="box">
        <t-form :data="formData" :label-width="80" ref="userDialog" label-width="200" @submit="onSubmit" label-align="top" :rules="rules">
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 90 }">
            <t-col :xs="12" :xl="6">
              <div class="item">
                <t-form-item :label="lang.name" name="client_id">
                  <t-select v-model="formData.client_id" filterable :placeholder="lang.example" clearable :loading="searchLoading" reserve-keyword :on-search="remoteMethod" clearable @clear="clearKey" :show-arrow="false" :scroll="{ type: 'virtual',threshold:20 }" :popup-props="popupProps" class="user-select" @change="chooseUser">
                    <t-option v-for="item in userList" :value="item.id" :label="item.username" :key="item.id" class="com-custom">
                      <div>
                        <p>{{item.username}}</p>
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
            </t-col>
          </t-row>
          <t-divider></t-divider>
          <p class="com-tit" v-if="formData.type"><span>{{ lang[formData.type] }}</span></p>
          <!-- 人工订单 -->
          <div class="artificial" v-if="formData.type==='artificial'">
            <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 90 }">
              <t-col :xs="12" :xl="6">
                <t-card bordered shadow>
                  <t-form-item :label="lang.description" name="description">
                    <t-textarea :placeholder="lang.input+lang.description" v-model="formData.description" />
                  </t-form-item>
                  <t-form-item :label="lang.price" name="amount">
                    <t-input :placeholder="lang.input+lang.price" v-model="formData.amount" :label="currency_prefix" />
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
          <div class="new-order" v-if="formData.type==='new'">
            <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 90 }" v-if="formData.type==='new'">
              <t-col :xs="12" :xl="6" class="left-area">
                <div class="pro-item" v-for="(item,index) in formData.products" :key="item.key">
                  <div class="p-tit">
                    <span>{{item.product_name || lang.choose_shop}}</span>
                    <t-icon name="close" size="24px" @click="deltePro(index)" v-if="index !== 0">
                    </t-icon>
                  </div>
                  <t-form-item :label="lang.choose_shop" class="price item">
                    <!-- <t-tree-select :data="productList" v-model="item.product_id" clearable
                            placeholder="请选择" :tree-props="treeProps" @click.native="treeClick" @change="onBlurTrigger">
                            <template #valueDisplay="{ value }"> {{ value.label }}({{ value.value }}) </template>
                          </t-tree-select> -->
                    <!-- <t-select v-model="item.product_name" :placeholder="lang.select+lang.product"
                            class="tree-select" ref="reference"  @click.native="checkNum(index)">
                            <div slot="panelTopContent" class="no-empty">
                              <t-tree :data="productList" :activable="true" :expand-on-click-node="true" ref="tree"
                                @click="onClick" @active="onActive" hover :label="getLabel" :expand-mutex="true"
                                >
                              </t-tree>
                            </div>
                          </t-select> -->
                    <t-popup :attach="`#myPopup${index}`" placement="bottom-left" :visible="visibleTreeObj[index].visibleTree">
                      <div :id="`myPopup${index}`">
                        <t-input v-model="item.product_name" placeholder='请选择商品' @click.native="focusHandler(index)">
                          <template slot="suffix">
                            <t-icon name="chevron-down" size="18px" :class="{active:visibleTreeObj[index].visibleTree}"></t-icon>
                          </template>
                        </t-input>
                      </div>
                      <template slot="content" class="test">
                        <t-tree :data="productList" :activable="true" :expand-on-click-node="true" ref="tree" @click="onClick" hover :label="getLabel" :expand-mutex="true" id="product-tree">
                        </t-tree>
                      </template>
                    </t-popup>
                  </t-form-item>
                  <p class="com-tit" v-if="item.product_name"><span>{{lang.optional_config}}</span></p>
                  <div class="config-area"></div>
                </div>
                <t-button theme="primary" @click="addMore" class="add-more">
                  <t-icon name="add-circle"></t-icon>
                  {{lang.add_other_product}}
                </t-button>
              </t-col>
              <t-col :xs="12" :xl="6" class="config-show">
                <t-card bordered shadow>
                  <p class="com-tit"><span>{{ lang.create_order_detail }}</span></p>
                  <div class="box"></div>
                  <t-divider></t-divider>
                  <div class="total">
                    <p>{{lang.total}}</p>
                    <p>{{currency_prefix}}<span class="total-price">{{totalPrice.toFixed(2)}}</span>{{currency_code}}</p>
                  </div>
                  <t-button type="submit" class="submit-new">{{lang.submit_order}}</t-button>
                </t-card>
              </t-col>
            </t-row>
          </div>
          <!-- 升降级订单 -->
          <div class="new-order upgrade" v-if="formData.type==='upgrade'">
            <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 90 }" v-if="formData.type==='upgrade'">
              <t-col :xs="12" :xl="6">
                <div class="pro-item">
                  <!-- 选择已开通产品 -->
                  <t-form-item :label="lang.choose_product" class="price">
                    <t-select v-model="shopId" :placeholder="lang.select+lang.tailorism" @change="chooseActive">
                      <t-option v-for="item in clientShopList" :value="item.id" :label="item.product_name+'('+item.name+')'" :key="item.id"></t-option>
                    </t-select>
                  </t-form-item>
                  <!-- 升级至的商品选择 -->
                  <t-form-item :label="lang.upgrade_to" class="price">
                    <t-select v-model="formData.product.product_name" :placeholder="lang.select+lang.product" @change="chooseUpgrade">
                      <t-option v-for="item in upgradeList" :value="item.id" :label="item.name" :key="item.id">
                      </t-option>
                    </t-select>
                  </t-form-item>
                  <p class="com-tit" v-if="formData.product.product_name"><span>{{lang.optional_config}}</span>
                  </p>
                  <div class="config-area"></div>
                </div>
              </t-col>
              <t-col :xs="12" :xl="6" class="config-show">
                <t-card bordered shadow>
                  <p class="com-tit"><span>{{ lang.order_detail }}</span></p>
                  <div class="box"></div>
                  <t-divider></t-divider>

                  <div class="total">
                    <p>{{lang.original_refund}}</p>
                    <p>
                      {{currency_prefix}}<span class="total-price refund">0.00</span>
                      {{currency_code}}
                    </p>
                  </div>
                  <div class="total">
                    <p>{{lang.new_price}}</p>
                    <p>
                      {{currency_prefix}}<span class="total-price pay">0.00</span>
                      {{currency_code}}
                    </p>
                  </div>
                  <div class="total">
                    <p>{{lang.upgrade_price}}</p>
                    <p>
                      {{currency_prefix}}<span class="total-price amount">0.00</span>
                      {{currency_code}}
                    </p>
                  </div>
                  <t-button type="submit" class="submit-new">{{lang.submit_order}}</t-button>
                </t-card>
              </t-col>
            </t-row>
          </div>
          <!-- 续费订单 -->
          <div class="new-order" v-if="formData.type==='renew'">
            <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 90 }">
              <t-col :xs="12" :xl="6">
                <div class="pro-item">
                  <!-- 选择已开通产品 -->
                  <t-form-item :label="lang.choose_product" class="price">
                    <t-select v-model="renewIds" :placeholder="lang.select+lang.tailorism" multiple @change="chooseRenew" :min-collapsed-num="2">
                      <t-option v-for="item in clientShopList" :value="item.id" :label="item.product_name+'('+item.name+')'" :key="item.id">
                      </t-option>
                    </t-select>
                  </t-form-item>
                  <p class="com-tit" v-if="formData.product.product_name">
                    <span>{{lang.optional_config}}</span>
                  </p>
                  <div class="config-area"></div>
                </div>
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
        </t-form>
      </div>
    </t-card>
  </div>
  <!-- footer -->
  <t-footer id="footer" v-cloak>Copyright @ 2019-{{new Date().getFullYear()}}
  </t-footer>
</t-content>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/create_order.js"></script>
{include file="footer"}