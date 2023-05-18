{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/manage.css">
<div id="content" class="log-notice-email hasCrumb" v-cloak>
  <div class="com-crumb">
    <span>{{lang.user_manage}}</span>
    <t-icon name="chevron-right"></t-icon>
    <a href="client.html">{{lang.user_list}}</a>
    <t-icon name="chevron-right"></t-icon>
    <span class="cur">{{lang.notice_log}}</span>
  </div>
  <t-card class="list-card-container">
    <div class="com-h-box">
      <ul class="common-tab">
        <li>
          <a :href="`${baseUrl}/client_detail.html?id=${id}`">{{lang.personal}}</a>
        </li>
        <li>
          <a :href="`${baseUrl}/client_host.html?id=${id}`">{{lang.product_info}}</a>
        </li>
        <li>
          <a :href="`${baseUrl}/client_order.html?id=${id}`">{{lang.order_manage}}</a>
        </li>
        <li>
          <a :href="`${baseUrl}/client_transaction.html?id=${id}`">{{lang.flow}}</a>
        </li>
        <li>
          <a :href="`${baseUrl}/client_log.html?id=${id}`">{{lang.operation}}{{lang.log}}</a>
        </li>
        <li class="active">
          <a>{{lang.notice_log}}</a>
        </li>
        <li v-if="hasTicket && authList.includes('TicketController::ticketList')">
          <a :href="`${baseUrl}/plugin/idcsmart_ticket/client_ticket.html?id=${id}`">{{lang.auto_order}}</a>
        </li>
        <li>
          <a :href="`${baseUrl}/client_records.html?id=${id}`">{{lang.info_records}}</a>
        </li>
      </ul>
      <t-select class="user" v-if="this.clientList" v-model="id" :popup-props="popupProps" filterable :filter="filterMethod" @change="changeUser" :loading="searchLoading" reserve-keyword :on-search="remoteMethod">
        <t-option :key="clientDetail.id" :value="clientDetail.id" :label="calcShow(clientDetail)" v-if="isExist">
          #{{clientDetail.id}}-{{clientDetail.username ? clientDetail.username : (clientDetail.phone? clientDetail.phone: clientDetail.email)}}
          <span v-if="clientDetail.company">({{clientDetail.company}})</span>
        </t-option>
        <t-option v-for="item in clientList" :value="item.id" :label="calcShow(item)" :key="item.id">
          #{{item.id}}-{{item.username ? item.username : (item.phone? item.phone: item.email)}}
          <span v-if="item.company">({{item.company}})</span>
        </t-option>
      </t-select>
    </div>
    <div class="common-header">
      <div class="left">
        <t-button theme="default" @click="jump">{{lang.sms_notice}}</t-button>
        <t-button>{{lang.email_notice}}</t-button>
      </div>
      <div class="com-search">
        <t-input v-model="params.keywords" class="search-input" :placeholder="`${lang.description}`" @keyup.enter.native="seacrh" :on-clear="clearKey" clearable>
        </t-input>
        <t-icon size="20px" name="search" @click="seacrh" class="com-search-btn" />
      </div>
    </div>
    <t-table row-key="id" :data="data" size="medium" :hide-sort-tips="true" :columns="columns" :hover="hover" 
    :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange">
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
    <t-pagination v-if="total" :total="total" :page-size="params.limit" :page-size-options="pageSizeOptions" :on-change="changePage" :current="params.page" />
  </t-card>
  <t-dialog :visible.sync="messageVisable" width="80%" :footer="false" class="messagePop" :close-btn="false">
    <div slot="header" class="messageHeader">
      <span>{{emailTitle}}</span>
      <t-icon name="close" @click="messageVisable=false"></t-icon>
    </div>
    <div v-html="messagePop"></div>
  </t-dialog>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/manage.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/client_notice_email.js"></script>
{include file="footer"}