{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<div id="content" class="order" v-cloak>
  <t-card class="list-card-container">
    <div class="common-header">
      <div class="left">
        <t-button @click="addOrder" class="add">{{lang.create_order}}</t-button>
        <t-button @click="batchDel" class="add">{{lang.batch_dele}}</t-button>
      </div>
      <!-- 右侧搜索 -->
      <div class="right-search">
        <template v-if="!isAdvance">
          <t-select v-model="params.status" :placeholder="lang.box_title3" clearable>
            <t-option v-for="item in orderStatus" :value="item.value" :label="item.label" :key="item.value">
            </t-option>
          </t-select>
          <div class="com-search">
            <t-input v-model="params.keywords" class="search-input" :placeholder="`ID、${lang.username}、${lang.email}、${lang.phone}、${lang.product_name}`" @keyup.enter.native="seacrh" :on-clear="clearKey" clearable>
            </t-input>
            <!-- <t-icon size="20px" name="search" @click="seacrh" class="com-search-btn" /> -->
          </div>
          <t-button @click="seacrh" class="seacrh" style="margin-right: 20px;">{{lang.query}}</t-button>
        </template>
        <t-button @click="changeAdvance">{{isAdvance ? lang.pack_up : lang.advanced_filter}}</t-button>
      </div>
    </div>
    <!-- 高级搜索 -->
    <div class="advanced" v-show="isAdvance">
      <div class="search">
        <t-input v-model="params.keywords" class="search-input" :placeholder="`${lang.order}ID、${lang.username}、${lang.email}、${lang.phone}、${lang.product_name}`" @keyup.enter.native="seacrh" :on-clear="clearKey" clearable>
        </t-input>
        <t-input :placeholder="lang.money" v-model="params.amount" @keyup.enter.native="seacrh"></t-input>
        <t-date-range-picker allow-input clearable v-model="range" :placeholder="[`${lang.order_date}`,`${lang.order_date}`]"></t-date-range-picker>
        <t-select v-model="params.type" :placeholder="lang.order_type" clearable>
          <t-option v-for="item in orderTypes" :value="item.value" :label="item.label" :key="item.value">
          </t-option>
        </t-select>
        <t-select v-model="params.status" :placeholder="lang.box_title3" clearable>
          <t-option v-for="item in orderStatus" :value="item.value" :label="item.label" :key="item.value">
          </t-option>
        </t-select>
        <t-select v-model="params.gateway" :placeholder="lang.pay_way" clearable>
          <t-option v-for="item in payWays" :value="item.name" :label="item.title" :key="item.name">
          </t-option>
        </t-select>
      </div>
      <t-button @click="seacrh">{{lang.query}}</t-button>
    </div>
    <!-- 高级搜索 end -->
    <t-enhanced-table ref="table" row-key="id" drag-sort="row-handler" :data="data" :columns="columns" :tree="{ childrenKey: 'list', treeNodeColumnIndex: 0 }" :loading="loading" :key="new Date().getTime()" :hover="hover" :tree-expand-and-fold-icon="treeExpandAndFoldIconRender" @sort-change="sortChange" class="user-order" :hide-sort-tips="true" @select-change="rehandleSelectChange" :selected-row-keys="checkId">
      <template slot="sortIcon">
        <t-icon name="caret-down-small"></t-icon>
      </template>
      <template #id="{row}">
        <!--  @click="itemClick(row)"  order-id  :class="{'com-no-child': row.order_item_count <= 1}"-->
        <span @click="lookDetail(row)" v-if="row.type" class="aHover">
          <!-- <t-icon :name="row.isExpand ? 'caret-up-small' : 'caret-down-small'" v-if="row.order_item_count > 1">
          </t-icon> -->
          {{row.id}}
        </span>
        <span v-else class="child">-</span>
      </template>
      <template #type="{row}">
        {{lang[row.type]}}
      </template>
      <template #client_name="{row}">
        <a :href="`client_detail.htm?client_id=${row.client_id}`" class="aHover">
          <template>
            <span v-if="row.client_name">{{row.client_name}}</span>
            <span v-else-if="row.phone">+{{row.phone_code}}-{{row.phone}}</span>
            <span v-else="row.email">{{row.email}}</span>
          </template>
          <span v-if="row.company">({{row.company}})</span>
        </a>
      </template>
      <template #create_time="{row}">
        {{row.type ? moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm') : ''}}
      </template>
      <template #icon="{row}">
        <template v-if="row.type">
          <t-tooltip :content="lang[row.type]" theme="light" :show-arrow="false" placement="top-right">
            <img :src="`${rootRul}img/icon/${row.type}.png`" alt="" style="position: relative; top: 3px;">
          </t-tooltip>
        </template>
      </template>
      <template #product_names={row}>
        <template v-if="row.product_names">
          <template v-if="row.description">
            <t-tooltip theme="light" :show-arrow="false" placement="top-right">
              <div slot="content" class="tool-content">{{row.description}}</div>
              <!--  <span @click="itemClick(row)" class="hover">{{row.product_names[0]}}</span> -->
              <span class="aHover" @click="lookDetail(row)">{{row.product_names[0]}}</span>
              <span v-if="row.product_names.length>1" @click="itemClick(row)" class="hover">{{lang.wait}}{{row.product_names.length}}{{lang.products}}</span>
            </t-tooltip>
          </template>
          <template v-else>
            <!--  @click="itemClick(row)" -->
            <span class="aHover" @click="lookDetail(row)">{{row.product_names[0]}}</span>
            <span v-if="row.product_names.length>1" @click="itemClick(row)" class="hover">{{lang.wait}}{{row.product_names.length}}{{lang.products}}</span>
          </template>
        </template>
        <span v-else class="child-name">
          <t-tooltip theme="light" :show-arrow="false" placement="top-right">
            <div slot="content" class="tool-content">{{row.description}}</div>
            <!-- <span @click="childItemClick(row)">{{row.product_name ? row.product_name : row.description}}
              <span class="host-name" v-if="row.host_name">({{row.host_name}})</span>
            </span> -->
            <a :href="row.host_id ? `host_detail.htm?client_id=${father_client_id}&id=${row.host_id}` : 'javascript:;'" class="aHover">{{row.product_name ? row.product_name : row.description}}
              <span class="host-name" v-if="row.host_name">({{row.host_name}})</span>
            </a>
          </t-tooltip>
        </span>
      </template>
      <template #amount="{row}">
        {{currency_prefix}}&nbsp;{{row.amount}}
        <!-- 升降机为退款时不显示周期 -->
        <span v-if="row.billing_cycle && Number(row.amount) >= 0 && row.type!=='upgrade'">/{{row.billing_cycle}}</span>
      </template>
      <template #status="{row}">
        <t-tag theme="default" variant="light" v-if="(row.status || row.host_status)==='Cancelled'" class="canceled order-canceled">{{lang.canceled}}
        </t-tag>
        <t-tag theme="default" variant="light" v-if="(row.status || row.host_status)==='Refunded'" class="canceled order-refunded">{{lang.refunded}}
        </t-tag>
        <t-tag theme="warning" variant="light" v-if="(row.status || row.host_status)==='Unpaid'" class="order-unpaid">{{lang.Unpaid}}
        </t-tag>
        <t-tag theme="primary" variant="light" v-if="row.status==='Paid'" class="order-paid">{{lang.Paid}}
        </t-tag>
        <t-tag theme="primary" variant="light" v-if="row.host_status === 'Pending'">
          {{lang.Pending}}
        </t-tag>
        <t-tag theme="success" variant="light" v-if="(row.status || row.host_status)==='Active'">{{lang.Active}}
        </t-tag>
        <t-tag theme="danger" variant="light" v-if="(row.status || row.host_status)==='Failed'">{{lang.Failed}}
        </t-tag>
        <t-tag theme="default" variant="light" v-if="(row.status || row.host_status)==='Suspended'">
          {{lang.Suspended}}
        </t-tag>
        <t-tag theme="default" variant="light" v-if="(row.status || row.host_status)==='Deleted'" class="delted">{{lang.Deleted}}
        </t-tag>
      </template>
      <template #gateway="{row}">
        <!-- 其他支付方式 -->
        <template v-if="row.credit == 0">
          {{row.gateway}}
        </template>
        <!-- 混合支付 -->
        <template v-if="row.credit * 1 >0 && row.credit * 1 < row.amount * 1">
          <t-tooltip :content="currency_prefix+row.credit" theme="light" placement="bottom-right">
            <span class="theme-color">{{lang.credit}}</span>
          </t-tooltip>
          <span>{{row.gateway ? '+ ' + row.gateway: '' }}</span>
        </template>
        <template v-if="row.amount*1 != 0 && row.credit==row.amount">
          <!-- <t-tooltip :content="currency_prefix+row.credit" theme="light" placement="bottom-right">
              <span>{{lang.credit}}</span>
            </t-tooltip> -->
          <span>{{lang.credit}}</span>
        </template>
      </template>
      <template #op="{row}">
        <template v-if="row.type">
          <t-tooltip :content="`${lang.look}${lang.detail}`" :show-arrow="false" theme="light">
            <t-icon name="view-module" class="common-look" @click="lookDetail(row)"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.update_price" :show-arrow="false" theme="light">
            <t-icon name="money-circle" class="common-look" @click="updatePrice(row, 'order')" v-if="row.status!=='Paid' && row.status!=='Cancelled' && row.status!=='Refunded'"></t-icon>
          </t-tooltip>
          <!-- <t-tooltip :content="lang.sign_pay" :show-arrow="false" theme="light" v-show="row.status!=='Paid' && row.status!=='Cancelled' && row.status!=='Refunded'">
            <t-icon name="discount" class="common-look" :class="{disable:row.status==='Paid'}" @click="signPay(row)"></t-icon>
          </t-tooltip> -->
          <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
            <t-icon name="delete" class="common-look" @click="delteOrder(row)"></t-icon>
          </t-tooltip>
        </template>
        <template v-else>
          <t-tooltip :content="lang.edit" :show-arrow="false" theme="light" v-if="row.edit">
            <t-icon name="edit" size="18px" @click="updatePrice(row, 'sub')" class="common-look"></t-icon>
          </t-tooltip>
        </template>
      </template>
    </t-enhanced-table>
    <t-pagination v-if="total" :total="total" :page-size="params.limit" :current="params.page" :page-size-options="pageSizeOptions" :on-change="changePage" />
  </t-card>
  <!-- 标记支付 -->
  <t-dialog :header="lang.sign_pay" :visible.sync="payVisible" width="600" class="sign_pay">
    <template slot="body">
      <t-form :data="signForm">
        <t-form-item :label="lang.order_amount">
          <t-input :label="currency_prefix" v-model="signForm.amount" disabled />
        </t-form-item>
        <t-form-item :label="lang.balance_paid">
          <t-input :label="currency_prefix" v-model="signForm.credit" disabled />
        </t-form-item>
        <t-form-item :label="lang.no_paid">
          <t-input :label="currency_prefix" v-model="(signForm.amount * 1).toFixed(2)" disabled />
        </t-form-item>
        <t-checkbox v-model="use_credit" class="checkDelete">{{lang.use_credit}}</t-checkbox>
      </t-form>
    </template>
    <template slot="footer">
      <div class="common-dialog">
        <t-button @click="sureSign">{{lang.sure}}</t-button>
        <t-button theme="default" @click="payVisible=false">{{lang.cancel}}</t-button>
      </div>
    </template>
  </t-dialog>
  <!-- 调整价格 -->
  <t-dialog :header="lang.update_price" :visible.sync="priceModel" :footer="false">
    <t-form :data="formData" ref="update_price" @submit="onSubmit" :rules="rules">
      <t-form-item :label="lang.change_money" name="amount">
        <t-input v-model="formData.amount" type="tel" :label="currency_prefix" :placeholder="lang.money"></t-input>
      </t-form-item>
      <t-form-item :label="lang.description" name="description">
        <t-textarea :placeholder="lang.description" v-model="formData.description" />
      </t-form-item>
      <div class="com-f-btn">
        <t-button theme="primary" type="submit">{{lang.sure}}</t-button>
        <t-button theme="default" variant="base" @click="priceModel=false">{{lang.cancel}}</t-button>
      </div>
    </t-form>
  </t-dialog>
  <!-- 删除 -->
  <t-dialog :header="deleteTit" :visible.sync="delVisible" class="delDialog" width="600">
    <template slot="body">
      <p>
        <t-icon name="error-circle" size="18" style="color:var(--td-warning-color);"></t-icon>
        &nbsp;&nbsp;{{lang.sureDelete}}
      </p>
      <div class="check">
        <t-checkbox v-model="delete_host"></t-checkbox>
        <div class="tips">
          <p class="tit">{{lang.deleteOrderTip1}}</p>
          <p class="tip">({{lang.deleteOrderTip2}})</p>
        </div>
      </div>
    </template>
    <template slot="footer">
      <div class="common-dialog">
        <t-button @click="onConfirm">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </div>
    </template>
  </t-dialog>
</div>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/order.js"></script>
{include file="footer"}
