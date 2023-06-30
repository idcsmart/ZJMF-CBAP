{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/upstream_order.css">
<div id="content" class="upstream_goods" v-cloak>
  <t-card class="list-card-container b-table">
    <div class="common-header">
      <div class="btn-box">
        <t-button @click="addGroup">{{lang.upstream_text43}}</t-button>
        <t-button theme="default" class="add-btn" @click="addProduct">{{lang.add_other_product}}</t-button>
      </div>
      <div class="client-search">
        <t-input v-model="params.keywords" :placeholder="lang.upstream_text44" @keyup.enter.native="seacrh">
          <span slot="suffix-icon">
            <t-icon name="search" style="cursor: pointer;" @click="seacrh" />
        </t-input>
      </div>
    </div>
    <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" display-type="fixed-width">
      <template #name="{row}">
        <span>{{row.name}}({{row.supplier_name}})</span>
      </template>
      <template #group="{row}">
        <span>{{row.product_group_name_first}} - {{row.product_group_name_second}}</span>
      </template>
      <template #profit_percent="{row}">
        <span>{{row.profit_percent}}%</span>
      </template>
      <template #price="{row}">
        <span>{{currency_prefix}}{{row.price}}<span v-if="row.cycle">/{{row.cycle}}</span> 起</span>
      </template>
      <template #hidden="{row}">
        <t-switch size="large" :custom-value="[0,1]" v-model="row.hidden" @change="onChange(row)"></t-switch>
      </template>
      <template #op="{row}">
        <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
          <t-icon name="edit" size="18px" class="common-look" @click="editGoods(row)"></t-icon>
        </t-tooltip>
        <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
          <t-icon name="delete" size="18px" @click="deleteHandler(row.id)" class="common-look"></t-icon>
        </t-tooltip>
      </template>
    </t-table>
    <t-pagination :total="total" :page-size="params.limit" :current="params.page" :page-size-options="pageSizeOptions" @change="changePage" v-if="total" />
  </t-card>
  <!-- 新建分组 -->
  <t-dialog :header="lang.create_group" :visible.sync="groupModel" :footer="false" @close="closeGroup">
    <t-form :data="formData" ref="groupForm" @submit="onSubmit" :rules="rules" reset-type="initial">
      <t-form-item :label="lang.group_name" name="name">
        <t-input v-model="formData.name" :placeholder="lang.group_name"></t-input>
      </t-form-item>
      <t-form-item :label="lang.belong_group" name="id">
        <t-select v-model="formData.id" :placeholder="lang.belong_group" clearable>
          <t-option v-for="item in firstGroup" :value="item.id" :label="item.name" :key="item.id">
          </t-option>
        </t-select>
      </t-form-item>
      <div class="com-f-btn">
        <t-button theme="primary" type="submit">{{lang.hold}}</t-button>
        <t-button theme="default" variant="base" @click="closeGroup">{{lang.cancel}}</t-button>
      </div>
    </t-form>
  </t-dialog>
  <!-- 编辑商品 -->
  <t-dialog :header="isEdit ? lang.edit_goods : lang.add_other_product" :visible.sync="productModel" :footer="false" @close="closeProduct">
    <t-form :data="productData" ref="productForm" @submit="submitProduct" :rules="productRules" reset-type="initial">
      <t-form-item :label="lang.upstream_text6" name="supplier_id">
        <t-select v-model="productData.supplier_id" :placeholder="lang.upstream_text6" filterable :scroll="{ type: 'virtual' }" :popup-props="{ overlayStyle: { height: '200px' } }" @change="supplierChange">
          <t-option v-for="item in supplierOption" :value="item.id" :label="item.name" :key="item.id">
          </t-option>
        </t-select>
      </t-form-item>
      <t-form-item :label="lang.product" name="upstream_product_id">
        <t-select v-model="productData.upstream_product_id" :placeholder="lang.product" filterable :scroll="{ type: 'virtual' }" :popup-props="{ overlayStyle: { height: '200px' } }">
          <t-option v-for="item in goodsOption" :value="item.id" :label="item.name" :key="item.id">
          </t-option>
        </t-select>
      </t-form-item>
      <t-form-item :label="lang.product_name" name="name">
        <t-input v-model="productData.name" :placeholder="lang.product_name"></t-input>
      </t-form-item>
      <t-form-item :label="lang.upstream_text18" name="profit_percent">
        <t-input v-model="productData.profit_percent" :placeholder="lang.upstream_text45" suffix="%"></t-input>
        <t-tooltip :content="lang.upstream_text20" :show-arrow="false" theme="light">
          <t-icon name="help-circle" size="18px" style="color: #0052D9; cursor: pointer; margin-left: 10px;"></t-icon>
        </t-tooltip>
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
          <t-icon name="help-circle" size="18px" style="color: #0052D9; cursor: pointer; margin-left: 10px;"></t-icon>
        </t-tooltip>
        <div class="tips-div">{{lang.upstream_text26}}</div>
      </t-form-item>
      <t-form-item :label="lang.upstream_text46" name="sync" v-if="calcType" class="no-flex">
        <t-tooltip :content="lang.upstream_text47" :show-arrow="false" theme="light">
          <t-switch size="large" :custom-value="[1,0]" v-model="productData.sync" disabled></t-switch>
        </t-tooltip>
        <div class="tips-div">{{lang.upstream_text48}}(可能导致代理商品过多)。利润配置与本商品保持一致。</div>
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
        <t-button theme="primary" type="submit">{{lang.hold}}</t-button>
        <t-button theme="default" variant="base" @click="closeProduct">{{lang.cancel}}</t-button>
      </div>
    </t-form>
  </t-dialog>
  <!-- 删除弹窗 -->
  <t-dialog theme="warning" :header="lang.sureDelete" :visible.sync="delVisible">
    <template slot="footer">
      <t-button theme="primary" @click="sureDel">{{lang.sure}}</t-button>
      <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
    </template>
  </t-dialog>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/upstream.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/upstream_goods.js"></script>
{include file="footer"}