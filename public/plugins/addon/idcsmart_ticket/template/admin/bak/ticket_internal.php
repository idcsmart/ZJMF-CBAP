<link rel="stylesheet" href="/plugins/addon/idcsmart_ticket/template/admin/css/order.css" />
<!-- =======内容区域======= -->
<div id="content" class="template" v-cloak>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <!-- <li>
        <a href="ticket.html">{{lang.user_work_order}}</a>
      </li>
      <li class="active">
        <a>{{lang.inside_work_order}}</a>
      </li> -->
    </ul>
    <div class="order-search-wrapper internal">
      <div>
        <t-button @click="newOrderDialogShow()">{{lang.order_new_rder}}</t-button>
        <t-button theme="default" style="margin-left: 20px" @click="orderTypeMgtDialogShow()">
          {{lang.order_manage_order_type}}
        </t-button>
      </div>
      <t-input v-model="params.keywords" :placeholder="lang.please_search_order" clearable @keyup.enter.native="doInternalOrderSearch" @clear="doInternalOrderClear">
        <template #prefix-icon>
          <t-icon name="search" size="20px" @click="doInternalOrderSearch"></t-icon>
        </template>
      </t-input>
    </div>
    <t-table :data="internalOrderData" :columns="internalOrderColumns" :loading="internalOrderTableloading" row-key="id" :max-height="tableHeight" size="small" table-layout="fixed" table-content-width="fixed" resizable>
      <template #status="slotProps">
        <t-tag theme="primary" variant="light" v-if="slotProps.row.status==='Pending'">{{lang.order_pending}}
        </t-tag>
        <t-tag theme="warning" variant="light" v-if="slotProps.row.status==='Handling'">
          {{lang.order_handling}}
        </t-tag>
        <t-tag theme="success" variant="light" v-if="slotProps.row.status==='Resolved'">
          {{lang.order_resolved}}
        </t-tag>
        <t-tag theme="danger" variant="light" v-if="slotProps.row.status==='Closed'">{{lang.order_closed}}
        </t-tag>
        <t-tag theme="primary" variant="light" v-if="slotProps.row.status==='Reply'">{{lang.order_reply}}
        </t-tag>
        <t-tag theme="success" variant="light" v-if="slotProps.row.status==='Replied'">{{lang.order_replied}}
        </t-tag>
        <span class="table-status-span" v-if="slotProps.row.ticket_status==='Pending'">{{lang.order_pending}}</span>
        <span class="table-status-span" v-if="slotProps.row.ticket_status==='Handling'">{{lang.order_handling}}</span>
        <span class="table-status-span" v-if="slotProps.row.ticket_status==='Resolved'">{{lang.order_resolved}}</span>
        <span class="table-status-span" v-if="slotProps.row.ticket_status==='Closed'">{{lang.order_closed}}</span>
        <span class="table-status-span" v-if="slotProps.row.ticket_status==='Reply'">{{lang.order_reply}}</span>
        <span class="table-status-span" v-if="slotProps.row.ticket_status==='Replied'">{{lang.order_replied}}</span>
      </template>
      <template #post_time="slotProps">
        {{ formatDate(slotProps.row.post_time) }}
      </template>
      <template #hosts="slotProps">
        <span v-for="(item,index) in slotProps.row.hosts" :key="index">{{item}} </span>
      </template>
      <template #priority="slotProps">
        <div v-for="(item,index) in priorityOptions" :key="index">
          <span v-if="item.id === slotProps.row.priority" :style="{color: slotProps.row.priority==='high'?'#E34D59':'#333333'}">
            {{ item.name }}
          </span>
        </div>
      </template>
      <template #operation="slotProps">
        <!-- 接收 -->
        <t-button :title="lang.receive" v-if="slotProps.row.status==='Pending'" shape="circle" variant="text" @click="internalOrderReceive(slotProps.row)">
          <t-icon name="download" size="small" style="color:#0052D9" />
        </t-button>
        <!-- 回复 -->
        <t-button :title="lang.reply" shape="circle" class="icon-reply" variant="text" @click="internalOrderReply(slotProps.row)">
          <!-- <t-icon name="chat" size="small" style="color:#0052D9" /> -->
        </t-button>
        <!-- 转发 -->
        <t-button :title="lang.forward" v-if="slotProps.row.status!=='Pending'" shape="circle" variant="text" @click="internalOrderForward(slotProps.row)">
          <t-icon name="rollback" size="small" style="color:#0052D9" />
        </t-button>
        <!-- 已解决-->
        <t-button :title="lang.order_resolved" v-if="slotProps.row.status!=='Resolved'&&slotProps.row.status!=='Pending'" shape="circle" variant="text" @click="internalOrderResolved(slotProps.row)">
          <t-icon name="check" size="small" style="color:#0052D9" />
        </t-button>
      </template>
    </t-table>
    <t-pagination v-if="total" :total="total" :page-size="params.limit" :current="params.page" :page-size-options="pageSizeOptions" @change="changePage" />
  </t-card>

  <!-- 转发弹窗 -->
  <t-dialog :header="lang.order_forward" :footer="false" :visible.sync="forwardDialogVisible" destroy-on-close>
    <t-form :data="forwardFormData" :rules="forwardFormRules" ref="forwardForm" label-align="left" :label-width="80" @submit="forwardFormSubmit">
      <!-- 指定部门 -->
      <t-form-item :label="lang.order_designated_department" name="admin_role_id">
        <t-select v-model="forwardFormData.admin_role_id" @change="departmentChange" clearable filterable style="width:100%">
          <t-option v-for="(item, index) in departmentOptions" :value="item.id" :label="item.name" :key="index">
            {{ item.name }}
          </t-option>
        </t-select>
      </t-form-item>
      <!-- 指定人员 -->
      <t-form-item :label="lang.order_designated_person" name="admin_id">
        <t-select v-model="forwardFormData.admin_id" @change="adminChange" clearable filterable style="width:100%">
          <t-option v-for="(item, index) in adminsOptions" :value="item.id" :label="item.name" :key="index">
            {{ item.name }}
          </t-option>
        </t-select>
      </t-form-item>

      <t-form-item class="turn-inside-dialog-footer">
        <t-button theme="primary" type="submit">{{lang.hold}}</t-button>
        <t-button theme="default" type="reset" @click="forwardDialogClose">{{lang.cancel}}</t-button>
      </t-form-item>
    </t-form>
  </t-dialog>

  <!-- 新建工单弹窗 -->
  <t-dialog :header="lang.order_new_rder" :footer="false" placement="center" width="600px" :visible.sync="addOrderDialogVisible" destroy-on-close>
    <t-form :data="addOrderFormData" :rules="addOrderFormRules" ref="addOrderForm" label-align="left" :label-width="80" @submit="addOrderFormSubmit">
      <!-- 标题 -->
      <t-form-item :label="lang.order_title" name="title">
        <t-input v-model="addOrderFormData.title" clearable></t-input>
      </t-form-item>
      <!-- 工单类型 -->
      <t-form-item :label="lang.order_name" name="ticket_type_id">
        <t-select v-model="addOrderFormData.ticket_type_id" @change="orderTypeChange" clearable filterable style="width:100%">
          <t-option v-for="(item, index) in orderTypeOptions" :value="item.id" :label="item.name" :key="index">
            {{ item.name }}
          </t-option>
        </t-select>
      </t-form-item>
      <!-- 紧急程度 -->
      <t-form-item :label="lang.order_priority" name="priority">
        <t-select v-model="addOrderFormData.priority" clearable filterable style="width:100%">
          <t-option v-for="(item, index) in priorityOptions" :value="item.id" :label="item.name" :key="index">
            {{ item.name }}
          </t-option>
        </t-select>
      </t-form-item>
      <!-- 关联用户-->
      <t-form-item :label="lang.order_client" name="client_id">
        <t-select v-model="addOrderFormData.client_id" @change="clientChange" clearable filterable style="width:100%">
          <t-option v-for="(item, index) in clientOptions" :value="item.id" :label="item.username" :key="index">
            {{ item.username }}
          </t-option>
        </t-select>
      </t-form-item>
      <!-- 关联产品 -->
      <t-form-item :label="lang.order_hosts" name="host_ids">
        <t-select v-model="addOrderFormData.host_ids" @change="hostChange" clearable multiple style="width:100%">
          <t-option v-for="(item, index) in hostOptions" :value="item.id" :label="item.product_name" :key="index">
            {{ item.product_name }}
          </t-option>
        </t-select>
      </t-form-item>
      <!-- 指定部门 -->
      <t-form-item :label="lang.order_designated_department" name="admin_role_id">
        <t-select v-model="addOrderFormData.admin_role_id" @change="departmentChange" clearable filterable style="width:100%">
          <t-option v-for="(item, index) in departmentOptions" :value="item.id" :label="item.name" :key="index">
            {{ item.name }}
          </t-option>
        </t-select>
      </t-form-item>
      <!-- 指定人员 -->
      <t-form-item :label="lang.order_designated_person" name="admin_id">
        <t-select v-model="addOrderFormData.admin_id" @change="adminChange" clearable filterable style="width:100%">
          <t-option v-for="(item, index) in adminsOptions" :value="item.id" :label="item.name" :key="index">
            {{ item.name }}
          </t-option>
        </t-select>
      </t-form-item>
      <!-- 问题描述 -->
      <t-form-item :label="lang.order_content" name="content">
        <t-textarea v-model="addOrderFormData.content"></t-textarea>
      </t-form-item>
      <!-- 上传附件 -->
      <t-form-item class="order-upload-wrapper" :label="lang.order_attachment" name="attachment">
        <t-upload theme="custom" v-model="addOrderFormData.attachment" action="http://101.35.248.14/admin/v1/upload" :headers="uploadHeaders" :format-response="uploadFormatResponse" show-upload-progress @progress="uploadProgress" @success="uploadSuccess" multiple :max="0">
          <t-button theme="default" class="upload-btn">
            <t-icon name="upload" size="small" style="color:#999999"></t-icon>
            <span>{{lang.attachment}}</span>
          </t-button>
          <span>{{uploadTip}}</span>
        </t-upload>
        <div class='list-custom'>
          <span v-for="(item, index) in addOrderFormData.attachment" :key="index" style="margin:10px">
            {{ item.name }}
            <t-icon name="close-circle-filled" @click="removeAttachment(item, index)"></t-icon>
          </span>
        </div>
      </t-form-item>

      <t-form-item class="turn-inside-dialog-footer">
        <t-button theme="primary" type="submit">{{lang.hold}}</t-button>
        <t-button theme="default" type="reset" @click="addOrderDialogClose">{{lang.cancel}}</t-button>
      </t-form-item>
    </t-form>
  </t-dialog>

  <!-- 工单类型管理弹窗 -->
  <t-dialog :header="lang.order_type_mgt" :footer="false" :visible.sync="orderTypeMgtDialogVisible" width="60%" @close="orderTypeMgtClose" :close-btn="false" :close-on-overlay-click="false" :close-on-esc-keydown="false">
    <t-table row-key="id" size="small" :loading="orderTypeMgtTableloading" :data="orderTypeMgtData" :columns="orderTypeMgtColumns" bordered>
      <template #index="slotProps">
        {{slotProps.rowIndex+1}}
      </template>
      <template #name="slotProps">
        <t-input v-model="slotProps.row.name" :bordered="false" clearable v-if="slotProps.row.status==='edit'||slotProps.row.status==='add'"></t-input>
        <span v-else>{{slotProps.row.name}}</span>
      </template>
      <template #role_name="slotProps">
        <t-select v-if="slotProps.row.status==='edit'||slotProps.row.status==='add'" v-model="slotProps.row.admin_role_id" clearable style="width:100%">
          <t-option v-for="(item, index) in departmentOptions" :value="item.id" :label="item.name" :key="index">
            {{ item.name }}
          </t-option>
        </t-select>
        <div v-else>
          <div v-for="(item, index) in departmentOptions" :key="index">
            <span v-if="item.id === slotProps.row.admin_role_id">{{ item.name }}</span>
          </div>
        </div>
      </template>
      <template #operation="slotProps">
        <div v-if="slotProps.row.status==='edit'||slotProps.row.status==='add'">
          <!-- 保存 -->
          <t-button shape="circle" class="icon-save" variant="text" @click="orderTypeMgtSave(slotProps.row)"></t-button>
          <!-- 取消 -->
          <t-button shape="circle" variant="text" @click="orderTypeMgtCancel(slotProps.row)">
            <t-icon name="close-rectangle" size="small" style="color:#0052D9" />
          </t-button>
        </div>
        <div v-else>
          <!-- 编辑 -->
          <t-button shape="circle" variant="text" @click="orderTypeMgtEdit(slotProps.row)">
            <t-icon name="edit-1" size="small" style="color:#0052D9" />
          </t-button>
          <!-- 删除 -->
          <t-button shape="circle" variant="text" @click="orderTypeMgtDelete(slotProps.row)">
            <t-icon name="delete" size="small" style="color:#0052D9" />
          </t-button>
        </div>
      </template>
    </t-table>
    <t-button variant="outline" class="order-type-add-btn" @click="newOrderType()">{{lang.order_new}}</t-button>
    <t-button theme="default" class="order-type-close-btn" @click="orderTypeMgtClose()">{{lang.close}}</t-button>
  </t-dialog>
</div>

<!-- =======页面独有======= -->
<script src="/plugins/addon/idcsmart_ticket/template/admin/api/common.js"></script>
<script src="/plugins/addon/idcsmart_ticket/template/admin/api/order.js"></script>
<script src="/plugins/addon/idcsmart_ticket/template/admin/js/internalOrder.js"></script>