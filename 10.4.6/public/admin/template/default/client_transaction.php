{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<div id="content" class="transaction hasCrumb" v-cloak>
  <com-config>
    <div class="com-crumb">
      <span>{{lang.user_manage}}</span>
      <t-icon name="chevron-right"></t-icon>
      <a href="client.htm" v-permission="'auth_user_list_view'">{{lang.user_list}}</a>
      <t-icon name="chevron-right" v-permission="'auth_user_list_view'"></t-icon>
      <span class="cur">{{lang.flow}}</span>
    </div>
    <t-card class="list-card-container">
      <div class="com-h-box">
        <ul class="common-tab">
          <li v-permission="'auth_user_detail_personal_information_view'">
            <a :href="`client_detail.htm?id=${id}`">{{lang.personal}}</a>
          </li>
          <li v-permission="'auth_user_detail_host_info_view'">
            <a :href="`client_host.htm?id=${id}`">{{lang.product_info}}</a>
          </li>
          <li v-permission="'auth_user_detail_order_view'">
            <a :href="`client_order.htm?id=${id}`">{{lang.order_manage}}</a>
          </li>
          <li class="active" v-permission="'auth_user_detail_transaction_view'">
            <a href="javascript:;">{{lang.flow}}</a>
          </li>
          <li v-permission="'auth_user_detail_operation_log'">
            <a :href="`client_log.htm?id=${id}`">{{lang.operation}}{{lang.log}}</a>
          </li>
          <li
            v-if="$checkPermission('auth_user_detail_notification_log_sms_notification') || $checkPermission('auth_user_detail_notification_log_email_notification')">
            <a
              :href="`${baseUrl}/${($checkPermission('auth_user_detail_notification_log_sms_notification') ? 'client_notice_sms' : 'client_notice_email')}.htm?id=${id}`">{{lang.notice_log}}</a>
          </li>
          <li v-if="hasNewTicket && $checkPermission('auth_user_detail_ticket_premium_view')">
            <a :href="`${baseUrl}/plugin/ticket_premium/client_ticket.htm?id=${id}`">{{lang.auto_order}}</a>
          </li>
          <li v-if="!hasNewTicket && hasTicket && $checkPermission('auth_user_detail_ticket_view')">
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
        <t-button @click="addFlow" class="add"
          v-permission="'auth_user_detail_transaction_create_transaction'">{{lang.new_flow}}</t-button>
      </div>
      <t-table row-key="1" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading"
        :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange" :hide-sort-tips="true">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #amount="{row}">
          {{currency_prefix}}&nbsp;{{row.amount}}<span v-if="row.billing_cycle">/</span>{{row.billing_cycle}}
        </template>
        <template #hosts="{row}">
          <a v-for="(item,index) in row.hosts" :href="`host_detail.htm?client_id=${row.client_id}&id=${item.id}`"
            class="aHover">
            {{item.name}}#-{{item.id}}
            <span v-if="row.hosts.length>1 && index !== row.hosts.length - 1">、</span>
          </a>
        </template>
        <template #create_time="{row}">
          {{moment(row.create_time * 1000).format('YYYY/MM/DD HH:mm')}}
        </template>
        <template #op="{row}">
          <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
            <t-icon name="edit" size="18px" @click="updateFlow(row)" class="common-look"
              v-permission="'auth_user_detail_transaction_update_transaction'"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
            <t-icon name="delete" class="common-look" @click="delteFlow(row)"
              v-permission="'auth_user_detail_transaction_delete_transaction'"></t-icon>
          </t-tooltip>
        </template>
      </t-table>
      <t-pagination show-jumper v-if="total" :total="total" :page-size="params.limit"
        :page-size-options="pageSizeOptions" :on-change="changePage" />
    </t-card>
    <!-- 新增流水 -->
    <t-dialog :header="optTitle" :visible.sync="flowModel" :footer="false">
      <t-form :data="formData" ref="form" @submit="onSubmit" :rules="rules" v-if="flowModel">
        <t-form-item :label="lang.user" name="client_id" class="user">
          <t-input v-model="client_name" disabled :placeholder="lang.select+lang.user"></t-input>
        </t-form-item>
        <t-form-item :label="lang.money" name="amount">
          <t-input v-model="formData.amount" type="tel" :label="currency_prefix" :placeholder="lang.money"></t-input>
        </t-form-item>
        <t-form-item :label="lang.pay_way" name="gateway">
          <t-select v-model="formData.gateway" :placeholder="lang.select+lang.pay_way">
            <t-option v-for="item in payList" :value="item.name" :label="item.title" :key="item.name">
            </t-option>
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.flow_number" name="transaction_number">
          <t-input v-model="formData.transaction_number" :placeholder="lang.flow_number"></t-input>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="addLoading">{{lang.submit}}</t-button>
          <t-button theme="default" variant="base" @click="flowModel=false">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 删除流水提示框 -->
    <t-dialog theme="warning" :header="lang.sureDelete" :close-btn="false" :visible.sync="delVisible">
      <template slot="footer">
        <t-button theme="primary" @click="sureDelUser" :loading="addLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
  </com-config>
</div>

<script src="/{$template_catalog}/template/{$themes}/components/comChooseUser/comChooseUser.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/client_transaction.js"></script>
{include file="footer"}
