{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/manage.css">
<div id="content" class="log-notice-email hasCrumb" v-cloak>
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
          <li v-permission="'auth_user_detail_personal_information_view'">
            <a :href="`${baseUrl}/client_detail.htm?id=${id}`">{{lang.personal}}</a>
          </li>
          <li v-permission="'auth_user_detail_host_info_view'">
            <a :href="`${baseUrl}/client_host.htm?id=${id}`">{{lang.product_info}}</a>
          </li>
          <li v-permission="'auth_user_detail_order_view'">
            <a :href="`${baseUrl}/client_order.htm?id=${id}`">{{lang.order_manage}}</a>
          </li>
          <li v-permission="'auth_user_detail_transaction_view'">
            <a :href="`${baseUrl}/client_transaction.htm?id=${id}`">{{lang.flow}}</a>
          </li>
          <li v-permission="'auth_user_detail_operation_log'">
            <a :href="`${baseUrl}/client_log.htm?id=${id}`">{{lang.operation}}{{lang.log}}</a>
          </li>
          <li class="active" v-if="$checkPermission('auth_user_detail_notification_log_sms_notification') || $checkPermission('auth_user_detail_notification_log_email_notification')">
            <a href="javascript:;">{{lang.notice_log}}</a>
          </li>
          <li v-if="hasTicket && $checkPermission('auth_user_detail_ticket_view')">
            <a :href="`${baseUrl}/plugin/idcsmart_ticket/client_ticket.htm?id=${id}`">{{lang.auto_order}}</a>
          </li>
          <li v-permission="'auth_user_detail_info_record_view'">
            <a :href="`${baseUrl}/client_records.htm?id=${id}`">{{lang.info_records}}</a>
          </li>
        </ul>
        <!-- 顶部右侧选择用户 -->
        <com-choose-user :cur-info="clientDetail" @changeuser="changeUser" class="com-clinet-choose">
        </com-choose-user>
      </div>
      <div class="common-header">
        <div class="left">
          <t-button theme="default" @click="jump" v-permission="'auth_user_detail_notification_log_sms_notification'">{{lang.sms_notice}}</t-button>
          <t-button>{{lang.email_notice}}</t-button>
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
        <template #subject="{row}">
          <t-icon v-if="row.status === 1" name="check-circle-filled" style="color:#00a870;"></t-icon>
          <template v-else>
            <t-tooltip :content="row.fail_reason" theme="light" :show-arrow="false">
              <t-icon name="close-circle-filled" class="icon-error" style="color: #e34d59;"></t-icon>
            </t-tooltip>
          </template>
          <a class="aHover" @click="showMessage(row)">{{row.subject}}</a>
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
    <t-dialog :visible.sync="messageVisable" width="80%" :footer="false" class="messagePop" :close-btn="false">
      <div slot="header" class="messageHeader">
        <span>{{emailTitle}}</span>
        <t-icon name="close" @click="messageVisable=false"></t-icon>
      </div>
      <div v-html="messagePop"></div>
    </t-dialog>
  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/components/comChooseUser/comChooseUser.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/manage.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/client_notice_email.js"></script>
{include file="footer"}
