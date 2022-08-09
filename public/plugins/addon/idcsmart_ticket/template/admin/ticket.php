<link rel="stylesheet" href="/plugins/addon/idcsmart_ticket/template/admin/css/order.css" />
<!-- =======内容区域======= -->

<div id="content" class="template" v-cloak>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li class="active">
        <a>{{lang.user_work_order}}</a>
      </li>
      <li>
        <a href="ticket_internal.html">{{lang.inside_work_order}}</a>
      </li>
    </ul>
    <div class="order-search-wrapper">
      <t-input v-model="params.keywords" :placeholder="lang.please_search_order" clearable @keyup.enter.native="doUserOrderSearch" @clear="doUserOrderClear">
        <template #prefix-icon>
          <t-icon name="search" size="20px" @click="doUserOrderSearch"></t-icon>
        </template>
      </t-input>
    </div>
    <t-table :data="userOrderData" :columns="userOrderColumns" :loading="userOrderTableloading" row-key="id" :max-height="tableHeight" size="small" table-layout="fixed" table-content-width="fixed" resizable>
      <template #status="slotProps">
        <t-tag theme="primary" variant="light" v-if="slotProps.row.status==='Pending'">{{lang.order_pending}}</t-tag>
        <t-tag theme="warning" variant="light" v-if="slotProps.row.status==='Handling'">{{lang.order_handling}}</t-tag>
        <t-tag theme="success" variant="light" v-if="slotProps.row.status==='Resolved'">{{lang.order_resolved}}</t-tag>
        <t-tag theme="danger" variant="light" v-if="slotProps.row.status==='Closed'">{{lang.order_closed}}</t-tag>
        <t-tag theme="primary" variant="light" v-if="slotProps.row.status==='Reply'">{{lang.order_reply}}</t-tag>
        <t-tag theme="success" variant="light" v-if="slotProps.row.status==='Replied'">{{lang.order_replied}}</t-tag>
        <span class="table-status-span" v-if="slotProps.row.internal_status==='Pending'">{{lang.order_pending}}</span>
        <span class="table-status-span" v-if="slotProps.row.internal_status==='Handling'">{{lang.order_handling}}</span>
        <span class="table-status-span" v-if="slotProps.row.internal_status==='Resolved'">{{lang.order_resolved}}</span>
        <span class="table-status-span" v-if="slotProps.row.internal_status==='Closed'">{{lang.order_closed}}</span>
        <span class="table-status-span" v-if="slotProps.row.internal_status==='Reply'">{{lang.order_reply}}</span>
        <span class="table-status-span" v-if="slotProps.row.internal_status==='Replied'">{{lang.order_replied}}</span>
      </template>
      <template #post_time="slotProps">
        {{ formatDate(slotProps.row.post_time) }}
      </template>
      <template #hosts="slotProps">
        <span v-for="(item,index) in slotProps.row.hosts" :key="index">{{item}} </span>
      </template>
      <template #operation="slotProps">
        <!-- 接收 -->
        <t-button :title="lang.receive" v-if="slotProps.row.status==='Pending'" shape="circle" variant="text" @click="userOrderReceive(slotProps.row)">
          <t-icon name="download" size="small" style="color:#0052D9" />
        </t-button>
        <!-- 回复 -->
        <t-button :title="lang.reply" shape="circle" variant="text" class="icon-reply" @click="userOrderReply(slotProps.row)">
          <!-- <t-icon name="chat" size="small" style="color:#0052D9" /> -->
        </t-button>
        <!-- 转内部 -->
        <t-button :title="lang.turn_inside" v-if="slotProps.row.status!=='Pending'" class="icon-forward" shape="circle" variant="text" @click="userOrderTurnInside(slotProps.row)">
          <!-- <t-icon name="enter" size="small" style="color:#0052D9" /> -->
        </t-button>
        <!-- 已解决-->
        <t-button :title="lang.order_resolved" v-if="slotProps.row.status!=='Resolved'&&slotProps.row.status!=='Pending'" shape="circle" variant="text" @click="userOrderResolved(slotProps.row)">
          <t-icon name="check" size="small" style="color:#0052D9" />
        </t-button>
      </template>
    </t-table>
    <t-pagination v-if="total" :total="total" :page-size="params.limit" :current="params.page" :page-size-options="pageSizeOptions" @change="changePage" />
  </t-card>
  <!-- 转内部弹窗 -->
  <t-dialog :header="lang.order_turn_inside" :footer="false" placement="center" width="600px" :visible.sync="turnInsideDialogVisible" destroy-on-close>
    <t-form :data="turnInsideFormData" :rules="turnInsideFormRules" ref="turnInsideForm" label-align="left" :label-width="80" @submit="turnInsideFormSubmit">
      <!-- 标题 -->
      <t-form-item :label="lang.order_title" name="title">
        <t-input v-model="turnInsideFormData.title" clearable></t-input>
      </t-form-item>
      <!-- 工单类型 -->
      <t-form-item :label="lang.order_name" name="ticket_type_id">
        <t-select v-model="turnInsideFormData.ticket_type_id" @change="orderTypeChange" clearable filterable style="width:100%">
          <t-option v-for="(item, index) in orderTypeOptions" :value="item.id" :label="item.name" :key="index">
            {{ item.name }}
          </t-option>
        </t-select>
      </t-form-item>
      <!-- 紧急程度 -->
      <t-form-item :label="lang.order_priority" name="priority">
        <t-select v-model="turnInsideFormData.priority" clearable filterable style="width:100%">
          <t-option v-for="(item, index) in priorityOptions" :value="item.id" :label="item.name" :key="index">
            {{ item.name }}
          </t-option>
        </t-select>
      </t-form-item>
      <!-- 关联用户-->
      <t-form-item :label="lang.order_client" name="client_id">
        <t-select v-model="turnInsideFormData.client_id" @change="clientChange" clearable filterable style="width:100%">
          <t-option v-for="(item, index) in clientOptions" :value="item.id" :label="item.username" :key="index">
            {{ item.username }}
          </t-option>
        </t-select>
      </t-form-item>
      <!-- 关联产品 -->
      <t-form-item :label="lang.order_hosts" name="host_ids">
        <t-select v-model="turnInsideFormData.host_ids" @change="hostChange" clearable multiple style="width:100%">
          <t-option v-for="(item, index) in hostOptions" :value="item.id" :label="item.product_name" :key="index">
            {{ item.product_name }}
          </t-option>
        </t-select>
      </t-form-item>
      <!-- 指定部门 -->
      <t-form-item :label="lang.order_designated_department" name="admin_role_id">
        <t-select v-model="turnInsideFormData.admin_role_id" @change="departmentChange" clearable filterable style="width:100%">
          <t-option v-for="(item, index) in departmentOptions" :value="item.id" :label="item.name" :key="index">
            {{ item.name }}
          </t-option>
        </t-select>
      </t-form-item>
      <!-- 指定人员 -->
      <t-form-item :label="lang.order_designated_person" name="admin_id">
        <t-select v-model="turnInsideFormData.admin_id" @change="adminChange" clearable filterable style="width:100%">
          <t-option v-for="(item, index) in adminsOptions" :value="item.id" :label="item.name" :key="index">
            {{ item.name }}
          </t-option>
        </t-select>
      </t-form-item>
      <!-- 问题描述 -->
      <t-form-item :label="lang.order_content" name="content">
        <t-textarea v-model="turnInsideFormData.content"></t-textarea>
      </t-form-item>
      <!-- 上传附件 -->
      <t-form-item class="order-upload-wrapper" :label="lang.order_attachment" name="attachment">
        <t-upload theme="custom" v-model="turnInsideFormData.attachment" action="http://101.35.248.14/admin/v1/upload" :headers="uploadHeaders" :format-response="uploadFormatResponse" show-upload-progress @progress="uploadProgress" @success="uploadSuccess" multiple :max="0">
          <t-button theme="default" class="upload-btn">
            <t-icon name="upload" size="small" style="color:#999999"></t-icon>
            <span>{{lang.attachment}}</span>
          </t-button>
          <span>{{uploadTip}}</span>
        </t-upload>
        <div class='list-custom'>
          <span v-for="(item, index) in turnInsideFormData.attachment" :key="index" style="margin:10px">
            {{ item.name }}
            <t-icon name="close-circle-filled" @click="removeAttachment(item, index)"></t-icon>
          </span>
        </div>
      </t-form-item>

      <t-form-item class="turn-inside-dialog-footer">
        <t-button theme="primary" type="submit">{{lang.hold}}</t-button>
        <t-button theme="default" type="reset" @click="turnInsideDialogClose">{{lang.cancel}}</t-button>
      </t-form-item>
    </t-form>
  </t-dialog>
</div>


<script src="/plugins/addon/idcsmart_ticket/template/admin/api/order.js"></script>
<script src="/plugins/addon/idcsmart_ticket/template/admin/js/userOrder.js"></script>