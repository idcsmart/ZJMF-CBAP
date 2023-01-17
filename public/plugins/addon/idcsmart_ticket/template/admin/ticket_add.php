<link rel="stylesheet" href="/plugins/addon/idcsmart_ticket/template/admin/css/addTicket.css" />
<!-- =======内容区域======= -->

<div id="content" class="template" v-cloak>
  <t-card class="list-card-container">
    <div class="page-title">{{lang.order_new_rder}}</div>
    <t-form label-align="top" :data="detialform" class="add_tform" ref="myform" :rules="requiredRules" layout="inline">
      <t-form-item :label="lang.order_name" name="ticket_type_id" class="form-item-left">
        <!-- <t-select class="select-240 mar-16" :placeholder="lang.order_text4" clearable v-model="detialform.admin_role_id" @change="departmentChange" props="ticket_type_id" :options="departmentList" :keys="{ label: 'name', value: 'admin_role_id'}">></t-select> -->
        <t-select class="select-496" :placeholder="lang.please_search_order_type" clearable v-model="detialform.ticket_type_id" :keys="{ label: 'name', value: 'id' }" :options="orderTypeOptions"></t-select>
      </t-form-item>
      <t-form-item :label="lang.order_text6" name="title">
        <t-input class="title-input" v-model="detialform.title" :placeholder="lang.order_text7" clearable></t-input>
      </t-form-item>
      <t-form-item :label="lang.order_text8" name="client_id" class="form-item-left">
        <t-select class="select-496" v-model="detialform.client_id" filterable :placeholder="lang.please_search_order_user" clearable :loading="searchLoading" reserve-keyword :on-search="remoteMethod" clearable @clear="clearKey" :show-arrow="false" :scroll="{ type: 'virtual',threshold:20 }" :popup-props="popupProps" @change="clientChange" class="user-select">
          <t-option v-for="item in clientOptions" :value="item.id" :label="item.username" :key="item.id" class="com-custom">
            <div>
              <p>{{item.username}}</p>
              <p v-if="item.phone" class="tel">+{{item.phone_code}}-{{item.phone}}</p>
              <p v-else class="tel">{{item.email}}</p>
            </div>
          </t-option>
        </t-select>
        <!-- <t-select :placeholder="" clearable filterable @change="clientChange" v-model="detialform.client_id" :options="clientOptions" :keys="{ label: 'username', value: 'id'}"></t-select> -->
      </t-form-item>
      <t-form-item :label="lang.order_text10" name="host_ids">
        <t-select class="select-496" @change="hostChange" :min-collapsed-num="2" clearable multiple :placeholder="lang.order_text11" clearable v-model="detialform.host_ids" :options="hostOptions" :keys="{ label: 'showName', value: 'id'}"></t-select>
      </t-form-item>
    </t-form>
    <div class="text-box">
      <div class="text-title">{{lang.order_text12}}</div>
      <textarea id="tiny1" name="content1">{{detialform.content}}</textarea>
    </div>
    <!-- <div class="text-box">
      <div class="text-title">备注情况</div>
      <textarea id="tiny2" name="content2">{{detialform.notes}}</textarea>
    </div> -->
    <div class="sub-btn">
      <t-button :loading="isSubmitIng" @click="addOrderFormSubmit()">{{lang.order_text13}}</t-button>
      <t-button theme="default" @click="goList()">{{lang.order_text14}}</t-button>
    </div>
  </t-card>
</div>
<script src="/plugins/addon/idcsmart_ticket/template/admin/api/order.js"></script>
<script src="/plugins/addon/idcsmart_ticket/template/admin/js/addTicket.js"></script>
<script src="/plugins/addon/idcsmart_ticket/template/admin/js/tinymce/tinymce.min.js"></script>