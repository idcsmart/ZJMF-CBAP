{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/upstream_order.css">
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<div id="content" class="upstream_order order" v-cloak>
  <t-card class="list-card-container top-card">
    <div class="money-box">
      <div class="item">
        <div class="l-text">
          <p class="num">{{currency_prefix}}{{money.total | filterMoney}}</p>
          <p class="txt">{{lang.total_sales}}</p>
        </div>
        <img src="/{$template_catalog}/template/{$themes}/img/upstream/app_01.png" alt="">
      </div>
      <div class="item">
        <div class="l-text">
          <p class="num">{{currency_prefix}}{{money.profit | filterMoney}}</p>
          <p class="txt">{{lang.total_profit}}</p>
        </div>
        <img src="/{$template_catalog}/template/{$themes}/img/upstream/app_02.png" alt="">
      </div>
      <div class="item">
        <div class="l-text">
          <p class="num">{{money.product_count}}</p>
          <p class="txt">{{lang.total_goods}}</p>
          <p class="btn" @click="goAgentList">{{lang.upstream_text4}}</p>
        </div>
        <img src="/{$template_catalog}/template/{$themes}/img/upstream/app_03.png" alt="">
      </div>
      <div class="item">
        <div class="l-text">
          <p class="num">{{money.host_count }}</p>
          <p class="txt">{{lang.user_text3}}</p>
        </div>
        <img src="/{$template_catalog}/template/{$themes}/img/upstream/app_04.png" alt="">
      </div>
    </div>
  </t-card>
  <t-card class="list-card-container bottom-card b-table">
    <div class="common-header">
      <span></span>
      <div class="client-search">
        <t-input v-model="params.keywords" :placeholder="lang.user_text21" @keyup.enter.native="seacrh">
          <span slot="suffix-icon">
            <t-icon style="cursor: pointer;" @click="seacrh" name="search" />
        </t-input>
      </div>
    </div>
    <t-enhanced-table ref="table" row-key="id" drag-sort="row-handler" :data="data" :columns="columns" :tree="{ childrenKey: 'list', treeNodeColumnIndex: 0 }" :loading="loading" :key="new Date().getTime()" :hover="hover" :tree-expand-and-fold-icon="treeExpandAndFoldIconRender" @sort-change="sortChange" class="user-order" :hide-sort-tips="true">
      <template slot="sortIcon">
        <t-icon name="caret-down-small"></t-icon>
      </template>
      <template slot="empty">
        <div style="text-align: center;">
          <h2>{{lang.user_text22}}</h2>
          <t-button @click="goAgentList">{{lang.upstream_text4}}</t-button>
        </div>
      </template>
      <template #id="{row}">
        <span v-if="row.type" @click="itemClick(row)" class="order-id" :class="{'com-no-child': row.order_item_count <= 1}">
          <t-icon :name="row.isExpand ? 'caret-up-small' : 'caret-down-small'" v-if="row.order_item_count > 1">
          </t-icon>
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
          <div v-if="row.description">
            <t-tooltip theme="light" :show-arrow="false" placement="top-right">
              <div slot="content" class="tool-content">{{row.description}}</div>
              <!--  <span @click="itemClick(row)" class="hover">{{row.product_names[0]}}</span> -->
              <a class="aHover" :href="`host_detail.htm?client_id=${row.client_id}&id=${row.host_id}`">{{row.product_names[0]}}</a>
              <span v-if="row.product_names.length>1" @click="itemClick(row)" class="hover">{{lang.wait}}{{row.product_names.length}}{{lang.products}}</span>
            </t-tooltip>
          </div>
          <div v-else>
            <span @click="itemClick(row)" class="hover">{{row.product_names[0]}}</span>
            <span v-if="row.product_names.length>1" @click="itemClick(row)" class="hover">{{lang.wait}}{{row.product_names.length}}{{lang.products}}</span>
          </div>
        </template>
        <span v-else class="child-name">
          <t-tooltip theme="light" :show-arrow="false" placement="top-right">
            <div slot="content" class="tool-content">{{row.description}}</div>
            <!-- <span @click="childItemClick(row)">{{row.product_name ? row.product_name : row.description}}
              <span class="host-name" v-if="row.host_name">({{row.host_name}})</span>
            </span> -->
            <a :href="`host_detail.htm?client_id=${father_client_id}&id=${row.host_id}`" class="aHover">{{row.product_name ? row.product_name : row.description}}
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
      <template #profit="{row}">
        {{currency_prefix}}&nbsp;{{row.profit}}
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
    <t-pagination show-jumper :total="total" :page-size="params.limit" :current="params.page" :page-size-options="pageSizeOptions" @change="changePage" v-if="total" />
  </t-card>
  <!-- 删除 -->
  <t-dialog :header="lang.deleteOrder" :visible.sync="delVisible" class="delDialog" width="600">
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
  <!-- 调整价格 -->
  <t-dialog :header="lang.update_price" :visible.sync="priceModel" :footer="false">
    <t-form :data="formData" ref="update_price" @submit="onSubmit" :rules="rules" reset-type="initial">
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
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/upstream.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/upstream_order.js"></script>
{include file="footer"}
