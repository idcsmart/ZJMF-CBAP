{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<!-- =======内容区域======= -->
<div id="content" class="order-details hasCrumb" v-cloak>
  <com-config>
    <div class="com-crumb">
      <span>{{lang.business_manage}}</span>
      <t-icon name="chevron-right"></t-icon>
      <span style="cursor: pointer;" @click="goOrder">{{lang.order_manage}}</span>
      <t-icon name="chevron-right"></t-icon>
      <span class="cur">{{lang.refund_record}}</span>
      <span class="back-text" @click="goBack">
        <t-icon name="chevron-left-double"></t-icon>{{lang.back}}
      </span>
    </div>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li v-permission="'auth_business_order_detail_order_detail_view'">
          <a :href="`order_details.htm?id=${id}`">{{lang.create_order_detail}}</a>
        </li>
        <li class="active" v-permission="'auth_business_order_detail_refund_record_view'">
          <a>{{lang.refund_record}}</a>
        </li>
        <li v-permission="'auth_business_order_detail_transaction'">
          <a :href="`order_flow.htm?id=${id}`">{{lang.flow}}</a>
        </li>
        <li v-if="hasCostPlugin" v-permission="'auth_addon_cost_pay_show_tab'">
          <a :href="`plugin/cost_pay/order_cost.htm?id=${id}`">{{lang.piece_cost}}</a>
        </li>
        <li v-permission="'auth_business_order_detail_notes_view'">
          <a :href="`order_notes.htm?id=${id}`">{{lang.notes}}</a>
        </li>
      </ul>
      <t-button class="initiate_refund" @click="initiateRefund" v-if="(orderDetail.status === 'Paid' || orderDetail.status === 'Refunded') && orderDetail.refundable_amount * 1 > 0
         && $checkPermission('auth_business_order_detail_refund_record_refund') && orderDetail.is_recycle === 0">
        {{lang.initiate_refund}}
      </t-button>
      <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange" :hide-sort-tips="true">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #amount="{row}">
          {{currency_prefix}}&nbsp;{{row.amount}}<span v-if="row.billing_cycle">/</span>{{row.billing_cycle}}
        </template>
        <template #gateway="{row}">
          <!-- 其他支付方式 -->
          <template v-if="row.credit == 0 && row.amount !=0">
            {{row.gateway}}
          </template>
          <!-- 混合支付 -->
          <template v-if="row.credit>0 && row.credit < row.amount">
            <t-tooltip :content="currency_prefix+row.credit" theme="light" placement="bottom-right">
              <span>{{lang.credit}}</span>
            </t-tooltip>
            <span>+{{row.gateway}}</span>
          </template>
          <template v-if="row.credit==row.amount">
            <t-tooltip :content="currency_prefix+row.credit" theme="light" placement="bottom-right">
              <span>{{lang.credit}}</span>
            </t-tooltip>
          </template>
        </template>
        <template #create_time="{row}">
          <span>{{moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm')}}</span>
        </template>
        <template #op="{row}">
          <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
            <t-icon name="delete" size="18px" @click="delteFlow(row)" class="common-look"
             v-if="$checkPermission('auth_business_order_detail_refund_record_delete_record') && orderDetail.is_recycle === 0">
            </t-icon>
          </t-tooltip>
        </template>
      </t-table>
      <t-pagination show-jumper v-if="total" :total="total" :page-size="params.limit" :current="params.page" :page-size-options="pageSizeOptions" :on-change="changePage" />
    </t-card>
    <!-- 发起退款 -->
    <t-dialog :visible.sync="visible" :header="lang.Refund" :on-close="close" :footer="false" width="600" :close-on-overlay-click="false">
      <t-form :rules="rules" :data="formData" ref="userDialog" @submit="onSubmit" v-if="visible">
        <t-form-item :label="`${lang.Refund}${lang.money}`" name="amount">
          <t-input :placeholder="`${lang.Refund}${lang.money}`" v-model="formData.amount" />
        </t-form-item>
        <t-form-item :label="lang.refund_to" name="type">
          <t-select v-model="formData.type" :placeholder="`${lang.select}${lang.type}`">
            <t-option value="credit" :label="lang.account_balance" key="credit"></t-option>
            <t-option value="transaction" :label="lang.gateway" key="transaction"></t-option>
          </t-select>
        </t-form-item>
        <template v-if="formData.type === 'transaction'">
          <t-form-item :label="lang.gateway" name="gateway">
            <t-select v-model="formData.gateway" :placeholder="lang.select+lang.gateway">
              <t-option v-for="item in payList" :value="item.name" :label="item.title" :key="item.name">
              </t-option>
            </t-select>
          </t-form-item>
          <t-form-item :label="lang.flow_number" name="transaction_number">
            <t-input v-model="formData.transaction_number" :placeholder="`${lang.flow_number}`"></t-input>
          </t-form-item>
        </template>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" style="margin-right: 10px" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="close">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 删除提示框 -->
    <t-dialog theme="warning" :header="lang.sureDelete" :close-btn="false" :visible.sync="delVisible">
      <template slot="footer">
        <t-button theme="primary" @click="sureDelUser" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
    <div class="deleted-svg">
      <img :src="`${rootRul}img/deleted.svg`" alt="" v-show="orderDetail.is_recycle">
    </div>
  </com-config>
</div>
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/order_refund.js"></script>
{include file="footer"}
