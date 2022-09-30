<link rel="stylesheet" href="/plugins/addon/idcsmart_refund/template/admin/css/refund.css" />
<!-- =======内容区域======= -->
<div id="content" class="template" v-cloak>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li class="active">
        <a href="refund.html">{{lang.refund_apply_list}}</a>
      </li>
      <li>
        <a href="refund.html">{{lang.refund_commodit_management}}</a>
      </li>
    </ul>
    <div class="order-search-wrapper">
      <t-input v-model="page.keywords" :placeholder="lang.refund_check_input" clearable @keyup.enter.native="Search" @clear="Clear">
        <template #prefix-icon>
          <t-icon name="search" size="20px" @click="Search"></t-icon>
        </template>
      </t-input>
    </div>
    <t-table row-key="id" :max-height="tableHeight" :pagination="pagination" :data="listData" :columns="columns" @page-change="onPageChange">
      <template #index="slotProps">
        {{slotProps.rowIndex+1}}
      </template>
      <template #price="slotProps">
        {{slotProps.row.amount!=-1?('￥'+slotProps.row.amount):"--"}}
      </template>
      <template #op-type>
        <p>{{lang.refundable_type}}</p>
      </template>
      <template #type="slotProps">
        <template v-if="slotProps.row.type=='Auto'">
          {{lang.automatic_refund}}
        </template>
        <template v-else-if="slotProps.row.type=='Artificial'">
          {{lang.manually_review}}
        </template>
        <template v-else-if="slotProps.row.type=='Expire'">
          {{lang.deactivated_due}}
        </template>
        <template v-else>
          {{lang.stop_sing}}
        </template>
      </template>
      <template #status="slotProps">
        <template v-if="slotProps.row.status=='Pending'">
          <t-tag theme="warning" variant="light">{{lang.to_audit}}</t-tag>
        </template>
        <template v-else-if="slotProps.row.status=='Suspending'">
          <t-tag>{{lang.to_stop_using}}</t-tag>
        </template>
        <template v-else-if="slotProps.row.status=='Suspend'">
          <t-tag>{{lang.stop_using_the}}</t-tag>
        </template>
        <template v-else-if="slotProps.row.status=='Suspended'">
          <t-tag style="color:#999">{{lang.has_been_discontinued}}</t-tag>
        </template>
        <template v-else-if="slotProps.row.status=='Refund'">
          <t-tag theme="success" variant="light">{{lang.refunded}}</t-tag>
        </template>
        <template v-else-if="slotProps.row.status=='Reject'">
          <t-tag theme="danger" variant="light">{{lang.review_the_rejected}}</t-tag>
        </template>
        <template v-else-if="slotProps.row.status=='Cancelled'">
          <t-tag>{{lang.canceled}}</t-tag>
        </template>
      </template>
      <template #op-column>
        <p>{{lang.operation}}</p>
      </template>
      <template #op="slotProps">
        <span class="refund-icon" :title="lang.get_approved" v-if="slotProps.row.status=='Pending'" @click="btn_OK(slotProps.row)">
          <t-icon name="check-circle" />
        </span>
        <span class="refund-icon" :title="lang.review_the_rejected" v-if="slotProps.row.status=='Pending'" @click="btn_NO(slotProps.row)">
          <t-icon name="file-excel" />
        </span>
        <!-- <span class="refund-icon" :title="lang.cancel" @click="btn_end(slotProps.row)" v-if="slotProps.row.status!='Suspended'&&slotProps.row.status!='Reject'&&slotProps.row.status!='Refund'&&slotProps.row.status!='Cancelled'">
          <t-icon name="close-rectangle" />
        </span> -->
        <span class="refund-icon" :title="lang.cancel" @click="btn_end(slotProps.row)" v-if="slotProps.row.status === 'Pending' || slotProps.row.status === 'Suspending'">
          <t-icon name="close-rectangle" />
        </span>
      </template>
    </t-table>
  </t-card>
  <div id='t-message-toggle'></div>
  <t-dialog :header="lang.dismiss_the_reason" @close="endVisible=false" @confirm="dismissConfirmation()" @close-btn-click="endVisible=false" width="40%" :visible="endVisible">
    <div class="reason-check">
      <span>{{lang.dismiss_the_reason}}</span>
      <t-input v-model="reject_reason" style="width: 300px" :placeholder="lang.dismiss_the_reason" />
    </div>
    <div id='t-message-toggles'></div>
  </t-dialog>
</div>
<!-- =======页面独有======= -->
<script src="/plugins/addon/idcsmart_refund/template/admin/api/refund.js"></script>
<script src="/plugins/addon/idcsmart_refund/template/admin/js/refundCheck.js"></script>