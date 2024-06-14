{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/upstream_order.css">
<div id="content" class="upstream_goods" v-cloak>
  <com-config>
    <t-card class="list-card-container b-table">
      <ul class="common-tab">
        <li v-permission="'auth_upstream_downstream_supplier_detail_order_list_view'">
          <a :href="`supplier_order.htm?id=${supplier_id}`">{{lang.box_title1}}</a>
        </li>
        <li class="active" v-permission="'auth_upstream_downstream_supplier_detail_product_list_view'">
          <a href="javascript:;">{{lang.product_list}}</a>
        </li>
        <li v-permission="'auth_upstream_downstream_supplier_detail_host_list_view'">
          <a :href="`supplier_product.htm?id=${supplier_id}`">{{lang.goods_list}}</a>
        </li>
      </ul>
      <div class="common-header">
        <div>
        </div>
        <div class="client-search">
          <t-input v-model="params.keywords" :placeholder="lang.upstream_text1" clearable @keypress.enter.native="search"
          @clear="clearKey">
            <span slot="suffix-icon">
              <t-icon name="search" style="cursor: pointer;" @click="search" />
          </t-input>
        </div>
      </div>
      <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading"
        :table-layout="tableLayout ? 'auto' : 'fixed'" display-type="fixed-width">
        <template #name="{row}">
          <span>{{row.name}}({{row.supplier_name}})</span>
        </template>
        <template #group="{row}">
          <span>{{row.product_group_name_first}} - {{row.product_group_name_second}}</span>
        </template>
        <template #profit_percent="{row}">
          <span v-if="row.profit_type === 0">{{row.profit_percent}}%</span>
          <span v-if="row.profit_type === 1">{{currency_prefix}}{{row.profit_percent}}</span>
        </template>
        <template #renew_profit_type="{row}">
          <span v-if="row.renew_profit_type === 0">{{row.renew_profit_percent}}%</span>
          <span v-if="row.renew_profit_type === 1">{{currency_prefix}}{{row.renew_profit_percent}}</span>
        </template>
        <template #upgrade_profit_percent="{row}">
          <span v-if="row.upgrade_profit_type === 0">{{row.upgrade_profit_percent}}%</span>
          <span v-if="row.upgrade_profit_type === 1">{{currency_prefix}}{{row.upgrade_profit_percent}}</span>
        </template>
        <template #price="{row}">
          <span>{{currency_prefix}}{{row.price}}<span v-if="row.cycle">/{{row.cycle}}</span> {{lang.rise}}</span>
        </template>
        <template #hidden="{row}">
          <t-switch size="large" :custom-value="[0,1]" v-model="row.hidden" @change="onChange(row)"
            :disabled="!$checkPermission('auth_upstream_downstream_upstream_product_show_hide')"></t-switch>
        </template>
        <template #op="{row}">
          <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
            <t-icon name="edit" size="18px" class="common-look" @click="editGoods(row)"
              v-permission="'auth_upstream_downstream_upstream_product_update_upstream_product'"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
            <t-icon name="delete" size="18px" @click="deleteHandler(row.id)" class="common-look"
              v-permission="'auth_upstream_downstream_upstream_product_delete_upstream_product'"></t-icon>
          </t-tooltip>
        </template>
      </t-table>
      <t-pagination show-jumper :total="total" :page-size="params.limit" :current="params.page"
        :page-size-options="pageSizeOptions" @change="changePage" v-if="total" />

    </t-card>
    <!-- 编辑商品 -->
    <t-dialog :header="lang.edit_goods" :visible.sync="productModel" :width="isEn ? 750 : 620" :footer="false"
      @close="closeProduct">
      <t-form :data="productData" ref="productForm" @submit="submitProduct" :rules="productRules" reset-type="initial">
        <t-form-item :label="lang.upstream_text6" name="supplier_id">
          <t-select v-model="productData.supplier_id" :placeholder="lang.upstream_text6" filterable
            :scroll="{ type: 'virtual' }" :popup-props="{ overlayInnerStyle: { height: '200px' } }"
            @change="supplierChange">
            <t-option v-for="item in supplierOption" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.product" name="upstream_product_id">
          <t-select v-model="productData.upstream_product_id" :placeholder="lang.product" filterable
            :scroll="{ type: 'virtual' }" :popup-props="{ overlayInnerStyle: { height: '200px' } }">
            <t-option v-for="item in goodsOption" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.product_name" name="name">
          <t-input v-model="productData.name" :placeholder="lang.product_name"></t-input>
        </t-form-item>
        <!-- 利润方式改为三种：新购，续费，升级 -->
        <t-form-item :label="lang.upstream_text84" class="required">
          <t-form-item name="profit_type">
            <t-radio-group v-model="productData.profit_type" @change="changeWay('profit_percent')">
              <t-radio :value="0">{{lang.percent}}</t-radio>
              <t-radio :value="1">{{lang.fixed}}{{lang.upstream_text73}}</t-radio>
            </t-radio-group>
          </t-form-item>
          <t-form-item name="profit_percent" class="profit-input" v-if="productData.profit_type === 0">
            <t-input-number v-model="productData.profit_percent" theme="normal" :decimal-places="2"
              :placeholder="lang.input" suffix="%"></t-input-number>
            <t-tooltip :content="lang.upstream_text20" :show-arrow="false" theme="light">
              <t-icon name="help-circle" size="18px"
                style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
            </t-tooltip>
          </t-form-item>
          <t-form-item name="profit_percent" v-else class="profit-input">
            <t-input-number v-model="productData.profit_percent" theme="normal" :decimal-places="2"
              :placeholder="lang.input"></t-input-number>
          </t-form-item>
        </t-form-item>
        <!--  续费 -->
        <t-form-item :label="lang.upstream_text85" class="required">
          <t-form-item name="renew_profit_type">
            <t-radio-group v-model="productData.renew_profit_type" @change="changeWay('renew_profit_percent')">
              <t-radio :value="0">{{lang.percent}}</t-radio>
              <t-radio :value="1">{{lang.fixed}}{{lang.upstream_text73}}</t-radio>
            </t-radio-group>
          </t-form-item>
          <t-form-item name="renew_profit_percent" v-if="productData.renew_profit_type === 0" class="profit-input">
            <t-input-number v-model="productData.renew_profit_percent" :placeholder="lang.input" theme="normal"
              :decimal-places="2" suffix="%"></t-input-number>
            <t-tooltip :content="lang.upstream_text20" :show-arrow="false" theme="light">
              <t-icon name="help-circle" size="18px"
                style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
            </t-tooltip>
          </t-form-item>
          <t-form-item name="renew_profit_percent" v-else class="profit-input">
            <t-input-number v-model="productData.renew_profit_percent" :placeholder="lang.input" theme="normal"
              :decimal-places="2"></t-input-number>
          </t-form-item>
        </t-form-item>
        <!-- 升级 -->
        <t-form-item :label="lang.upstream_text86" class="required">
          <t-form-item name="upgrade_profit_type">
            <t-radio-group v-model="productData.upgrade_profit_type" @change="changeWay('upgrade_profit_percent')">
              <t-radio :value="0">{{lang.percent}}</t-radio>
              <t-radio :value="1">{{lang.fixed}}{{lang.upstream_text73}}</t-radio>
            </t-radio-group>
          </t-form-item>
          <t-form-item name="upgrade_profit_percent" v-if="productData.upgrade_profit_type === 0" class="profit-input">
            <t-input-number v-model="productData.upgrade_profit_percent" :placeholder="lang.input" suffix="%"
              theme="normal" :decimal-places="2"></t-input-number>
            <t-tooltip :content="lang.upstream_text20" :show-arrow="false" theme="light">
              <t-icon name="help-circle" size="18px"
                style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
            </t-tooltip>
          </t-form-item>
          <t-form-item name="upgrade_profit_percent" v-else class="profit-input">
            <t-input-number v-model="productData.upgrade_profit_percent" :placeholder="lang.input" theme="normal"
              :decimal-places="2"></t-input-number>
          </t-form-item>
        </t-form-item>
        <t-form-item label=" " class="empty-item">
          <span class="tip">{{lang.upstream_text87}}</span>
        </t-form-item>

        <t-form-item :label="lang.upstream_text21" name="dec">
          <t-textarea v-model="productData.description" :placeholder="lang.upstream_text22">
          </t-textarea>
        </t-form-item>
        <t-form-item :label="lang.upstream_text23" name="auto_setup">
          <t-switch size="large" :custom-value="[1,0]" v-model="productData.auto_setup"></t-switch>
        </t-form-item>
        <t-form-item :label="lang.upstream_text24" name="certification" class="no-flex">
          <t-switch size="large" :custom-value="[1,0]" v-model="productData.certification"></t-switch>
          <t-tooltip :content="lang.upstream_text25" :show-arrow="false" theme="light">
            <t-icon name="help-circle" size="18px"
              style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
          </t-tooltip>
          <div class="tips-div">{{lang.upstream_text26}}</div>
        </t-form-item>
        <t-form-item :label="lang.upstream_text46" name="sync" v-if="calcType" class="no-flex">
          <t-tooltip :content="lang.upstream_text47" :show-arrow="false" theme="light">
            <t-switch size="large" :custom-value="[1,0]" v-model="productData.sync" disabled></t-switch>
          </t-tooltip>
          <div class="tips-div">{{lang.upstream_text48}}{{lang.upstream_text75}}</div>
        </t-form-item>
        <!-- <t-form-item label="上游实名方式" name="certification_method">
        <t-select v-model="productData.certification_method" placeholder="上游实名方式">
          <t-option v-for="item in methodOption" :value="item.id" :label="item.name" :key="item.id">
          </t-option>
        </t-select>
      </t-form-item> -->
        <t-form-item :label="lang.first_group" name="firstId">
          <t-select v-model="productData.firstId" :placeholder="lang.group_name" @change="changeFirId">
            <t-option v-for="item in firstGroup" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.second_group" name="product_group_id">
          <t-select v-model="productData.product_group_id" :placeholder="lang.group_name">
            <t-option v-for="item in tempSecondGroup" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="closeProduct">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 新建分组 -->
    <t-dialog :header="lang.create_group" :visible.sync="groupModel" :footer="false" @close="closeGroup">
      <t-form :data="formData" ref="groupForm" @submit="onSubmit" :rules="rules" reset-type="initial">
        <t-form-item :label="lang.group_name" name="name">
          <t-input v-model="formData.name" :placeholder="lang.group_name"></t-input>
        </t-form-item>
        <t-form-item :label="lang.belong_group" name="id">
          <t-select v-model="formData.id" :placeholder="lang.select+lang.group_name" clearable>
            <t-option v-for="item in firstGroup" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="closeGroup">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 删除弹窗 -->
    <t-dialog theme="warning" :header="lang.sureDelete" :visible.sync="delVisible">
      <template slot="footer">
        <t-button theme="primary" @click="sureDel" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/upstream.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/supplier_goods.js"></script>
{include file="footer"}
