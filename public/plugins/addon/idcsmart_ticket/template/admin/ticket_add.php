<link rel="stylesheet" href="/plugins/addon/idcsmart_ticket/template/admin/css/addTicket.css" />
<!-- =======内容区域======= -->

<div id="content" class="template" v-cloak>
  <t-card class="list-card-container">
    <div class="page-title">新建工单</div>
    <t-form label-align="top" :data="detialform" class="add_tform" ref="myform" :rules="requiredRules" layout="inline">
      <t-form-item label="工单类型" name="ticket_type_id" class="form-item-left">
        <t-select class="select-240 mar-16" placeholder="请选择工单部门" clearable v-model="detialform.admin_role_id" @change="departmentChange" props="ticket_type_id" :options="departmentList" :keys="{ label: 'name', value: 'admin_role_id'}">></t-select>
        <t-select class="select-240"  :placeholder="lang.please_search_order_type" clearable v-model="detialform.ticket_type_id" :keys="{ label: 'name', value: 'id' }" :options="orderTypeOptions"></t-select>
      </t-form-item>
      <t-form-item label="工单标题" name="title">
        <t-input class="title-input" v-model="detialform.title" placeholder="请输入工单标题" clearable></t-input>
      </t-form-item>
      <t-form-item label="关联用户" name="client_id" class="form-item-left">
        <t-select class="select-496" placeholder="请选择用户" clearable filterable @change="clientChange"  v-model="detialform.client_id" :options="clientOptions" :keys="{ label: 'username', value: 'id'}"></t-select>
      </t-form-item>
      <t-form-item label="关联产品" name="host_ids">
        <t-select class="select-496" @change="hostChange" :min-collapsed-num="2" clearable multiple placeholder="请选择产品" clearable v-model="detialform.host_ids" :options="hostOptions" :keys="{ label: 'showName', value: 'id'}"></t-select>
      </t-form-item>
    </t-form>
    <div class="text-box">
      <div class="text-title">详细描述</div>
      <textarea id="tiny1" name="content1">{{detialform.content}}</textarea>
    </div>
    <!-- <div class="text-box">
      <div class="text-title">备注情况</div>
      <textarea id="tiny2" name="content2">{{detialform.notes}}</textarea>
    </div> -->
    <div class="sub-btn">
      <t-button :loading="isSubmitIng" @click="addOrderFormSubmit()">保存</t-button>
      <t-button theme="default" @click="goList()">关闭</t-button>
    </div>
  </t-card>
</div>

<script src="/plugins/addon/idcsmart_ticket/template/admin/api/order.js"></script>
<script src="/plugins/addon/idcsmart_ticket/template/admin/js/addTicket.js"></script>
<script src="/plugins/addon/idcsmart_ticket/template/admin/js/tinymce/tinymce.min.js"></script>