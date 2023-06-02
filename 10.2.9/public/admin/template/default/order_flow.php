{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<!-- =======内容区域======= -->
<div id="content" class="order-details hasCrumb" v-cloak>
  <div class="com-crumb">
    <span>{{lang.business_manage}}</span>
    <t-icon name="chevron-right"></t-icon>
    <span style="cursor: pointer;" @click="goOrder">{{lang.order_manage}}</span>
    <t-icon name="chevron-right"></t-icon>
    <span class="cur">{{lang.flow}}</span>
    <span class="back-text" @click="goBack">
      <t-icon name="chevron-left-double"></t-icon>返回
    </span>
  </div>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li>
        <a :href="`order_details.htm?id=${id}`">{{lang.create_order_detail}}</a>
      </li>
      <li>
        <a :href="`order_refund.htm?id=${id}`">{{lang.refund_record}}</a>
      </li>
      <li class="active">
        <a>{{lang.flow}}</a>
      </li>
      <li>
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
      <!-- <template #gateway="{row}">
        <template v-if="row.credit == 0 && row.amount !=0">
          {{row.gateway}}
        </template>
        <template v-if="row.credit>0 && row.credit < row.amount">
          <t-tooltip :content="currency_prefix+row.credit" theme="light" placement="bottom-right">
            <span>{{lang.credit}}</span>
          </t-tooltip>
          <span>+{{row.gateway}}</span>
        </template>
        <template v-if="row.credit==row.amount">
          <t-tooltip :content="currency_prefix+row.credit" theme="light" placement="bottom-right">
            <span>{{lang.credit}}</span>
          </t-tooltip>
        </template>
      </template> -->
      <template #create_time="{row}">
        <span>{{moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm')}}</span>
      </template>
    </t-table>
    <t-pagination v-if="total" :total="total" :page-size="params.limit" :current="params.page" :page-size-options="pageSizeOptions" :on-change="changePage" />
  </t-card>
</div>

<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/order_flow.js"></script>
{include file="footer"}