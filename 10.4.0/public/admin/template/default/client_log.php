{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<!-- =======内容区域======= -->
<div id="content" class="log hasCrumb" v-cloak>
  <com-config>
    <div class="com-crumb">
      <span>{{lang.user_manage}}</span>
      <t-icon name="chevron-right"></t-icon>
      <a href="client.htm">{{lang.user_list}}</a>
      <t-icon name="chevron-right"></t-icon>
      <span class="cur">{{lang.operation}}{{lang.log}}</span>
    </div>
    <t-card class="list-card-container">
      <div class="com-h-box">
        <ul class="common-tab">
          <li>
            <a :href="`${baseUrl}/client_detail.htm?id=${id}`">{{lang.personal}}</a>
          </li>
          <li>
            <a :href="`${baseUrl}/client_host.htm?id=${id}`">{{lang.product_info}}</a>
          </li>
          <li>
            <a :href="`${baseUrl}/client_order.htm?id=${id}`">{{lang.order_manage}}</a>
          </li>
          <li>
            <a :href="`${baseUrl}/client_transaction.htm?id=${id}`">{{lang.flow}}</a>
          </li>
          <li class="active">
            <a href="javascript:;">{{lang.operation}}{{lang.log}}</a>
          </li>
          <li>
            <a :href="`${baseUrl}/client_notice_sms.htm?id=${id}`">{{lang.notice_log}}</a>
          </li>
          <li v-if="hasTicket && authList.includes('TicketController::ticketList')">
            <a :href="`${baseUrl}/plugin/idcsmart_ticket/client_ticket.htm?id=${id}`">{{lang.auto_order}}</a>
          </li>
          <li>
            <a :href="`${baseUrl}/client_records.htm?id=${id}`">{{lang.info_records}}</a>
          </li>
        </ul>
        <!-- 顶部右侧选择用户 -->
        <com-choose-user :cur-info="clientDetail" :check-id="id"  @changeuser="changeUser" class="com-clinet-choose">
        </com-choose-user>
      </div>
      <t-table row-key="1" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" :hide-sort-tips="true" @sort-change="sortChange">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #description="{row}">
          <span v-html="row.description"></span>
        </template>
        <template #create_time="{row}">
          {{moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm')}}
        </template>
      </t-table>
      <t-pagination show-jumper v-if="total" :total="total" :page-size="params.limit" :page-size-options="pageSizeOptions" :on-change="changePage" />
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/components/comChooseUser/comChooseUser.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/client_log.js"></script>
{include file="footer"}
