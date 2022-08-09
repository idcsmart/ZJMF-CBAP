{include file="header"}
<!-- =======内容区域======= -->
<div id="content" class="log table hasCrumb" v-cloak>
  <div class="com-crumb">
    <span>{{lang.user_manage}}</span>
    <t-icon name="chevron-right"></t-icon>
    <a href="client.html">{{lang.user_list}}</a>
    <t-icon name="chevron-right"></t-icon>
    <span class="cur">{{lang.log}}</span>
  </div>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li>
        <a :href="`client_detail.html?id=${id}`">{{lang.personal}}</a>
      </li>
      <li>
        <a :href="`client_host.html?id=${id}`">{{lang.product_info}}</a>
      </li>
      <li>
        <a :href="`client_order.html?id=${id}`">{{lang.order_manage}}</a>
      </li>
      <li>
        <a :href="`client_transaction.html?id=${id}`">{{lang.flow}}</a>
      </li>
      <li class="active">
        <a href="javascript:;">{{lang.log}}</a>
      </li>
      <li>
        <a :href="`client_notice_sms.html?id=${id}`">{{lang.notice_log}}</a>
      </li>
    </ul>
    <t-table row-key="1" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" :hide-sort-tips="true" :max-height="maxHeight" @sort-change="sortChange">
      <template slot="sortIcon">
        <t-icon name="caret-down-small"></t-icon>
      </template>
      <template #create_time="{row}">
        {{moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm')}}
      </template>
    </t-table>
    <t-pagination v-if="total" :total="total" :page-size="params.limit" :page-size-options="pageSizeOptions" :on-change="changePage" />
  </t-card>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/client_log.js"></script>
{include file="footer"}