{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/manage.css">
<div id="content" class="log-notice-sms hasCrumb" v-cloak>
  <com-config>
    <div class="com-crumb">
      <span>{{lang.user_manage}}</span>
      <t-icon name="chevron-right"></t-icon>
      <a href="client.htm">{{lang.user_list}}</a>
      <t-icon name="chevron-right"></t-icon>
      <span class="cur">{{lang.notice_log}}</span>
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
          <li>
            <a :href="`${baseUrl}/client_log.htm?id=${id}`">{{lang.operation}}{{lang.log}}</a>
          </li>
          <li class="active">
            <a href="javascript:;">{{lang.notice_log}}</a>
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
      <div class="common-header">
        <div class="left">
          <t-button>{{lang.sms_notice}}</t-button>
          <t-button theme="default" @click="jump">{{lang.email_notice}}</t-button>
        </div>
        <div class="com-search">
          <t-input v-model="params.keywords" class="search-input" :placeholder="`${lang.description}`" @keyup.enter.native="seacrh" :on-clear="clearKey" clearable>
          </t-input>
          <t-icon size="20px" name="search" @click="seacrh" class="com-search-btn" />
        </div>
      </div>
      <t-table row-key="id" :data="data" size="medium" :hide-sort-tips="true" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #content="{row}">
          <t-icon v-if="row.status === 1" name="check-circle-filled" style="color:#00a870;"></t-icon>
          <template v-else>
            <t-tooltip :content="row.fail_reason" theme="light" :show-arrow="false">
              <t-icon name="close-circle-filled" class="icon-error" style="color: #e34d59;"></t-icon>
            </t-tooltip>
          </template>
          {{row.content}}
        </template>
        <template #create_time="{row}">
          {{moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm')}}
        </template>
        <template #phone="{row}">
          +{{row.phone_code}}&nbsp;-&nbsp;{{row.phone}}
        </template>
      </t-table>
      <t-pagination show-jumper v-if="total" :total="total" :page-size="params.limit" :page-size-options="pageSizeOptions" :on-change="changePage" :current="params.page" />
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/components/comChooseUser/comChooseUser.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/manage.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/client_notice_sms.js"></script>
{include file="footer"}
