{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/upstream_order.css">
<div id="content" class="upstream_goods" v-cloak>
  <t-card class="list-card-container b-table">
    <ul class="common-tab">
      <li>
        <a :href=`supplier_order.html?id=${supplier_id}`>订单列表</a>
      </li>
      <li class="active">
        <a href="javascript:;">商品列表</a>
      </li>
      <li>
        <a :href=`supplier_product.html?id=${supplier_id}`>产品列表</a>
      </li>
    </ul>
    <div class="common-header">
      <div>
      </div>
      <div class="client-search">
        <t-input v-model="params.keywords" placeholder="请输入关键词搜索" clearable @keyup.enter.native="seacrh">
          <span slot="suffix-icon">
            <t-icon name="search" style="cursor: pointer;" @click="seacrh" />
        </t-input>
      </div>
    </div>
    <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" display-type="fixed-width">
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
  <!-- 编辑商品 -->
  <t-dialog header="编辑商品" :visible.sync="productModel" :footer="false" @close="closeProduct">
    <t-form :data="productData" ref="productForm" @submit="submitProduct" :rules="productRules" reset-type="initial">
      <t-form-item label="所属供应商" name="supplier_id">
        <t-select v-model="productData.supplier_id" @change="supplierChange" placeholder="请选择所属供应商" :scroll="{ type: 'virtual' }" :popup-props="{ overlayStyle: { height: '200px' } }">
          <t-option v-for="item in supplierOption" :value="item.id" :label="item.name" :key="item.id">
          </t-option>
        </t-select>
      </t-form-item>
      <t-form-item label="商品" name="upstream_product_id">
        <t-select v-model="productData.upstream_product_id" placeholder="请选择商品" :scroll="{ type: 'virtual' }" :popup-props="{ overlayStyle: { height: '200px' } }">
          <t-option v-for="item in goodsOption" :value="item.id" :label="item.name" :key="item.id">
          </t-option>
        </t-select>
      </t-form-item>
      <t-form-item :label="lang.product_name" name="name">
        <t-input v-model="productData.name" :placeholder="lang.input+lang.product_name"></t-input>
      </t-form-item>
      <t-form-item label="利润百分比" name="profit_percent">
        <t-input v-model="productData.profit_percent" placeholder="请输入大于0的数,最多两位小数" suffix="%"></t-input>
        <t-tooltip content="利润 =上游商品价格 * 利润百分比" :show-arrow="false" theme="light">
          <t-icon name="help-circle" size="18px" style="color: #0052D9; cursor: pointer; margin-left: 10px;"></t-icon>
        </t-tooltip>
      </t-form-item>
      <t-form-item label="自动开通" name="auto_setup">
        <t-switch size="large" :custom-value="[1,0]" v-model="productData.auto_setup"></t-switch>
      </t-form-item>
      <t-form-item label="本地实名购买" name="certification" class="no-flex">
        <t-switch size="large" :custom-value="[1,0]" v-model="productData.certification"></t-switch>
        <t-tooltip content="开启后，用户需要实名后才能购买" :show-arrow="false" theme="light">
          <t-icon name="help-circle" size="18px" style="color: #0052D9; cursor: pointer; margin-left: 10px;"></t-icon>
        </t-tooltip>
        <div class="tips-div">建议在供应商处实名后再代理商品，避免供应商开启购买实名要求后，无法购买商品。</div>
      </t-form-item>
      <!-- <t-form-item label="上游实名方式" name="certification_method">
        <t-select v-model="productData.certification_method" placeholder="请选择上游实名方式">
          <t-option v-for="item in methodOption" :value="item.id" :label="item.name" :key="item.id">
          </t-option>
        </t-select>
      </t-form-item> -->
      <t-form-item :label="lang.first_group" name="firstId">
        <t-select v-model="productData.firstId" :placeholder="lang.select+lang.group_name" @change="changeFirId">
          <t-option v-for="item in firstGroup" :value="item.id" :label="item.name" :key="item.id">
          </t-option>
        </t-select>
      </t-form-item>
      <t-form-item :label="lang.second_group" name="product_group_id">
        <t-select v-model="productData.product_group_id" :placeholder="lang.select+lang.group_name">
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
  <!-- 新建分组 -->
  <t-dialog :header="lang.create_group" :visible.sync="groupModel" :footer="false" @close="closeGroup">
    <t-form :data="formData" ref="groupForm" @submit="onSubmit" :rules="rules" reset-type="initial">
      <t-form-item :label="lang.group_name" name="name">
        <t-input v-model="formData.name" :placeholder="lang.input+lang.group_name"></t-input>
      </t-form-item>
      <t-form-item :label="lang.belong_group" name="id">
        <t-select v-model="formData.id" :placeholder="lang.select+lang.group_name" clearable>
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
<script src="/{$template_catalog}/template/{$themes}/js/supplier_goods.js"></script>
{include file="footer"}