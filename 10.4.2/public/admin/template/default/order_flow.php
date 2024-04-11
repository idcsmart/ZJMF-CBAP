{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<!-- =======内容区域======= -->
<div id="content" class="order-details hasCrumb" v-cloak>
  <com-config>
    <div class="com-crumb">
      <span>{{lang.business_manage}}</span>
      <t-icon name="chevron-right"></t-icon>
      <span style="cursor: pointer;" @click="goOrder">{{lang.order_manage}}</span>
      <t-icon name="chevron-right"></t-icon>
      <span class="cur">{{lang.flow}}</span>
      <span class="back-text" @click="goBack">
        <t-icon name="chevron-left-double"></t-icon>{{lang.back}}
      </span>
    </div>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li v-permission="'auth_business_order_detail_order_detail_view'">
          <a :href="`order_details.htm?id=${id}`">{{lang.create_order_detail}}</a>
        </li>
        <li v-permission="'auth_business_order_detail_refund_record_view'">
          <a :href="`order_refund.htm?id=${id}`">{{lang.refund_record}}</a>
        </li>
        <li class="active" v-permission="'auth_business_order_detail_transaction'">
          <a>{{lang.flow}}</a>
        </li>
        <li v-if="hasCostPlugin" v-permission="'auth_addon_cost_pay_show_tab'">
          <a :href="`plugin/cost_pay/order_cost.htm?id=${id}`">{{lang.piece_cost}}</a>
        </li>
        <li v-permission="'auth_business_order_detail_notes_view'">
          <a :href="`order_notes.htm?id=${id}`">{{lang.notes}}</a>
        </li>
      </ul>
      <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange" :hide-sort-tips="true">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #transaction_number="{row}">
          {{row.transaction_number || '--'}}
        </template>
        <template #amount="{row}">
          {{currency_prefix}}&nbsp;{{row.amount}}<span v-if="row.billing_cycle">/</span>{{row.billing_cycle}}
        </template>
        <template #create_time="{row}">
          <span>{{moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm')}}</span>
        </template>
      </t-table>
      <t-pagination show-jumper v-if="total" :total="total" :page-size="params.limit" :current="params.page" :page-size-options="pageSizeOptions" :on-change="changePage" />
    </t-card>
    <div class="deleted-svg">
      <img :src="`${rootRul}img/deleted.svg`" alt="" v-show="orderDetail.is_recycle">
    </div>
  </com-config>
</div>

<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/order_flow.js"></script>
{include file="footer"}
