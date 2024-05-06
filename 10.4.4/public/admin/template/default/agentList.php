{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/upstream_order.css">
<div id="content" class="agent-list" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <div class="common-header">
        <div></div>
        <div class="com-search">
          <t-input v-model="params.keywords" class="search-input" :placeholder="lang.upstream_text1" @keyup.native.enter="seacrh" :on-clear="clearKey" clearable>
          </t-input>
          <t-icon size="20px" name="search" @click="seacrh" class="com-search-btn" />
        </div>
      </div>
      <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'">
        <template #cpu="{row}">
          <span>{{row.cpu_min}} - {{row.cpu_max}}{{lang.upstream_text2}}</span>
        </template>
        <template #memory="{row}">
          <span>{{row.memory_min}} - {{row.memory_max}}GB</span>
        </template>
        <template #disk="{row}">
          <span>{{row.disk_min}} - {{row.disk_max}}GB</span>
        </template>
        <template #bandwidt="{row}">
          <span>{{row.bandwidth_min}} - {{row.bandwidth_max}}Mbps</span>
        </template>
        <template #flow="{row}">
          <span>{{row.flow_min}} - {{row.flow_max}}G</span>
        </template>
        <template #price="{row}">
          <span>{{currency_prefix}}{{row.price}}/{{row.cycle}} {{lang.upstream_text3}}</span>
        </template>
        <template #op="{row}">
          <t-tooltip :content="lang.upstream_text4" v-if="row.agent === 0" :show-arrow="false" theme="light">
            <t-icon name="swap" size="18px" @click="editGoods(row)" class="common-look"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.upstream_text5" v-if="row.agent === 1" :show-arrow="false" theme="light">
            <t-icon name="swap" size="18px" class="greey-color"></t-icon>
          </t-tooltip>
        </template>
      </t-table>
      <t-pagination show-jumper :total="total" v-if="total" :current="params.page" :page-size="params.limit" :page-size-options="pageSizeOptions" :on-change="changePage" />
    </t-card>
    <!-- 代理商品 -->
    <t-dialog :header="lang.upstream_text4" width="640" :visible.sync="productModel" :footer="false" @close="closeProduct">
      <div class="goods-info">
        <div>
          <div class="leble">{{lang.upstream_text6}}：</div>
          <div class="value">{{curObj.supplier_name}}</div>
        </div>
        <div>
          <div class="leble">{{lang.upstream_text7}}：</div>
          <div class="value">{{curObj.name}}</div>
        </div>
        <div>
          <div class="leble">{{lang.upstream_text8}}：</div>
          <div class="value">{{currency_prefix}}{{curObj.price}}/{{curObj.cycle}} {{lang.upstream_text3}}</div>
        </div>
        <div>
          <div class="leble">{{lang.upstream_text9}}：</div>
          <div class="value">{{curObj.description}}</div>
        </div>
      </div>
      <t-form :data="productData" ref="productForm" @submit="submitProduct" :rules="productRules" reset-type="initial">
        <t-form-item :label="lang.upstream_text10" name="username" class="first-item">
          <t-input v-model="productData.username" :placeholder="lang.upstream_text11"></t-input>
          <t-tooltip :content="lang.upstream_text12" :show-arrow="false" theme="light">
            <t-icon name="help-circle" size="18px" style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
          </t-tooltip>
          <p style="color: rgba(0, 0, 0, 0.40); cursor: pointer; margin-top: 4px;">
            {{curObj.login_url}}
            <a target="_blank" :href="curObj.login_url" style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;">
              {{lang.upstream_text13}}
            </a>
          </p>
        </t-form-item>
        <t-form-item :label="lang.upstream_text14" name="token">
          <t-input v-model="productData.token" :placeholder="lang.upstream_text15"></t-input>
        </t-form-item>
        <t-form-item :label="lang.upstream_text16" name="secret">
          <t-textarea v-model="productData.secret" :placeholder="lang.upstream_text17">
          </t-textarea>
        </t-form-item>
        <t-form-item :label="lang.product_name" name="name">
          <t-input v-model="productData.name" :placeholder="lang.product_name"></t-input>
        </t-form-item>
        <t-form-item :label="lang.upstream_text18" name="profit_percent">
          <t-input v-model="productData.profit_percent" :placeholder="lang.upstream_text19" suffix="%"></t-input>
          <t-tooltip :content="lang.upstream_text20" :show-arrow="false" theme="light">
            <t-icon name="help-circle" size="18px" style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
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
            <t-icon name="help-circle" size="18px" style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
          </t-tooltip>
          <div class="tips-div">{{lang.upstream_text26}}</div>
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
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.upstream_text27}}</t-button>
          <t-button theme="default" variant="base" @click="closeProduct">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/upstream.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/agentList.js"></script>
{include file="footer"}
