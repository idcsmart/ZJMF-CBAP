{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<div id="content" class="transaction table hasCrumb" v-cloak>
  <div class="com-crumb">
    <span>{{lang.user_manage}}</span>
    <t-icon name="chevron-right"></t-icon>
    <a href="client.html">{{lang.user_list}}</a>
    <t-icon name="chevron-right"></t-icon>
    <span class="cur">{{lang.flow}}</span>
  </div>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li>
        <a :href="`client_detail.html?id=${id}`">{{lang.personal}}</a>
      </li>
      <li>
        <a :href="`client_host.html?id=${id}`">{{lang.product_info}}</a>
      </li>
      <li>
        <a :href="`client_order.html?id=${id}`">{{lang.order_manage}}</a>
      </li>
      <li class="active">
        <a href="javascript:;">{{lang.flow}}</a>
      </li>
      <li>
        <a :href="`client_log.html?id=${id}`">{{lang.log}}</a>
      </li>
      <li>
        <a :href="`client_notice_sms.html?id=${id}`">{{lang.notice_log}}</a>
      </li>
    </ul>
    <div class="common-header">
      <t-button @click="addFlow" class="add">{{lang.new_flow}}</t-button>
    </div>
    <t-table row-key="1" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" :max-height="maxHeight" @sort-change="sortChange" :hide-sort-tips="true">
      <template slot="sortIcon">
        <t-icon name="caret-down-small"></t-icon>
      </template>
      <template #amount="{row}">
        {{currency_prefix}}&nbsp;{{row.amount}}<span v-if="row.billing_cycle">/</span>{{row.billing_cycle}}
      </template>
      <template #hosts="{row}">
        <a v-for="(item,index) in row.hosts" :href="`host_detail.html?client_id=${row.client_id}&id=${item.id}`" class="aHover">
          {{item.name}}#-{{item.id}}
          <span v-if="row.hosts.length>1 && index !== row.hosts.length - 1">、</span>
        </a>
      </template>
      <template #create_time="{row}">
        {{moment(row.create_time * 1000).format('YYYY/MM/DD HH:mm')}}
      </template>
      <template #op="{row}">
        <a class="common-look" @click="delteFlow(row)">{{lang.delete}}</a>
      </template>
    </t-table>
    <t-pagination v-if="total" :total="total" :page-size="params.limit" :page-size-options="pageSizeOptions" :on-change="changePage" />
  </t-card>
  <!-- 新增流水 -->
  <t-dialog :header="lang.new_flow" :visible.sync="flowModel" :footer="false">
    <t-form :data="formData" ref="form" @submit="onSubmit" :rules="rules">
      <t-form-item :label="lang.money" name="amount">
        <t-input v-model="formData.amount" type="tel" :label="currency_prefix" :placeholder="lang.input+lang.money"></t-input>
      </t-form-item>
      <t-form-item :label="lang.pay_way" name="gateway">
        <t-select v-model="formData.gateway" :placeholder="lang.select+lang.pay_way">
          <t-option v-for="item in payList" :value="item.name" :label="item.title" :key="item.name">
          </t-option>
        </t-select>
      </t-form-item>
      <t-form-item :label="lang.flow_number" name="transaction_number">
        <t-input v-model="formData.transaction_number" :placeholder="lang.input+lang.flow_number"></t-input>
      </t-form-item>
      <t-form-item :label="lang.user" name="client_id" class="user">
        <t-input v-model="client_name" disabled :placeholder="lang.select+lang.user"></t-input>
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
      <t-button theme="primary" @click="sureDelUser">{{lang.sure}}</t-button>
      <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
    </template>
  </t-dialog>
</div>
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/client_transaction.js"></script>
{include file="footer"}