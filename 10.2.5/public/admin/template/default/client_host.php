{include file="header"}
<!-- =======内容区域======= -->
<div id="content" class="host hasCrumb" v-cloak>
  <!-- crumb -->
  <div class="com-crumb">
    <span>{{lang.user_manage}}</span>
    <t-icon name="chevron-right"></t-icon>
    <a href="client.html">{{lang.user_list}}</a>
    <t-icon name="chevron-right"></t-icon>
    <span class="cur">{{lang.product_info}}</span>
  </div>
  <t-card class="list-card-container">
    <div class="com-h-box">
      <ul class="common-tab">
        <li>
          <a :href="`${baseUrl}/client_detail.html?id=${id}`">{{lang.personal}}</a>
        </li>
        <li class="active">
          <a href="javascript:;">{{lang.product_info}}</a>
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
        <li>
          <a :href="`${baseUrl}/client_notice_sms.html?id=${id}`">{{lang.notice_log}}</a>
        </li>
        <li v-if="hasTicket && authList.includes('TicketController::ticketList')">
          <a :href="`${baseUrl}/plugin/idcsmart_ticket/client_ticket.html?id=${id}`">{{lang.auto_order}}</a>
        </li>
        <li>
          <a :href="`${baseUrl}/client_records.html?id=${id}`">{{lang.info_records}}</a>
        </li>
      </ul>
      <t-select class="user" v-if="this.clientList" v-model="id" :popup-props="popupProps" filterable :filter="filterMethod"
       @change="changeUser" :loading="searchLoading" reserve-keyword :on-search="remoteMethod">
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
    <t-button @click="batchRenew" class="add" style="margin-bottom: 20px ;" v-if="hasPlugin">{{lang.batch_renew}}</t-button>
    <t-button @click="batchDel" class="add" style="margin-bottom: 20px ; margin-left: 10px;">{{lang.batch_dele}}</t-button>
    <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" :hide-sort-tips="true" @sort-change="sortChange" @select-change="rehandleSelectChange" :selected-row-keys="checkId">
      <template slot="sortIcon">
        <t-icon name="caret-down-small"></t-icon>
      </template>
      <template #id="{row}">
        <a :href="`host_detail.html?client_id=${row.client_id}&id=${row.id}`" class="aHover">{{row.id}}</a>
      </template>
      <template #product_name="{row}">
        <a :href="`host_detail.html?client_id=${row.client_id}&id=${row.id}`" class="aHover">{{row.product_name}}</a>
      </template>
      <template #renew_amount="{row}">
        <template v-if="row.billing_cycle">
          {{currency_prefix}}&nbsp;{{row.renew_amount}}<span>/</span>{{row.billing_cycle}}
        </template>
        <template v-else>
          {{currency_prefix}}&nbsp;{{row.first_payment_amount}}/{{lang.onetime}}
        </template>
      </template>
      <template #status="{row}">
        <t-tag theme="default" variant="light" v-if="row.status==='Cancelled'" class="canceled">{{lang.canceled}}</t-tag>
        <t-tag theme="warning" variant="light" v-if="row.status==='Unpaid'">{{lang.Unpaid}}</t-tag>
        <t-tag theme="primary" variant="light" v-if="row.status==='Pending'">{{lang.Pending}}</t-tag>
        <t-tag theme="success" variant="light" v-if="row.status==='Active'">{{lang.Active}}</t-tag>
        <t-tag theme="danger" variant="light" v-if="row.status==='Failed'">{{lang.Failed}}</t-tag>
        <t-tag theme="default" variant="light" v-if="row.status==='Suspended'">{{lang.Suspended}}</t-tag>
        <t-tag theme="default" variant="light" v-if="row.status==='Deleted'" class="delted">{{lang.Deleted}}
        </t-tag>
      </template>
      <template #active_time="{row}">
        <span v-if="row.status !== 'Unpaid'">{{row.active_time ===0 ? '-' : moment(row.active_time * 1000).format('YYYY/MM/DD HH:mm')}}</span>
      </template>
      <template #due_time="{row}">
        <span v-if="row.status !== 'Unpaid'">{{row.due_time ===0 ? '-' : moment(row.due_time * 1000).format('YYYY/MM/DD HH:mm')}}</span>
      </template>
      <template #op="{row}">
        <a class="common-look" @click="deltePro(row)">{{lang.delete}}</a>
      </template>
    </t-table>
    <t-pagination :total="total" :page-size="params.limit" :page-size-options="pageSizeOptions" :on-change="changePage" />
  </t-card>
  <!-- 删除 -->
  <t-dialog theme="warning" :header="lang.delHostTips" :close-btn="false" :visible.sync="delVisible">
    <template slot="footer">
      <div class="common-dialog">
        <t-button @click="onConfirm">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </div>
    </template>
  </t-dialog>
  <!-- 批量续费弹窗 -->
  <t-dialog :header="lang.batch_renew" :close-btn="false" :visible.sync="renewVisible" :footer="false" placement="center" @close="cancelRenew" class="renew-dialog">
    <t-table row-key="1" :data="renewList" size="medium" :columns="renewColumns" :hover="hover" :table-layout="tableLayout ? 'auto' : 'fixed'" :loading="renewLoading" >
      <template slot="sortIcon">
        <t-icon name="caret-down-small"></t-icon>
      </template>
      <template #product_name="{row}">
        {{row.product_name}}({{row.name}})
      </template>
      <template #billing_cycles="{row}">
        <t-select v-model="row.curCycle" :popup-props="popupProps" v-if="row.billing_cycles.length > 0" @change="changeCycle(row)">
          <t-option v-for="(item,index) in row.billing_cycles" :value="item.billing_cycle" :key="index" :label="item.billing_cycle"></t-option>
        </t-select>
        <span v-else class="no-renew">{{lang.renew_tip}}</span>
      </template>
      <template #renew_amount="{row}">
        {{currency_prefix}}&nbsp;{{row.renew_amount}}
      </template>
    </t-table>

    <div class="com-f-btn">
      <div class="total">{{lang.total}}：<span class="price"><span class="symbol">{{currency_prefix}}</span>{{renewTotal}}</span></div>
      <div>
        <t-checkbox v-model="pay">{{lang.mark_Paid}}</t-checkbox>
      </div>
      <t-button theme="primary" @click="submitRenew" :loading="submitLoading" :disabled="renewList.length === 0">{{lang.sure_renew}}</t-button>
    </div>
  </t-dialog>
</div>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/client_host.js"></script>
{include file="footer"}