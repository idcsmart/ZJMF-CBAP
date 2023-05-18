{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<!-- =======内容区域======= -->
<div id="content" class="order-details hasCrumb" v-cloak>
  <div class="com-crumb">
    <span>{{lang.business_manage}}</span>
    <t-icon name="chevron-right"></t-icon>
    <a href="order.htm">{{lang.order_manage}}</a>
    <t-icon name="chevron-right"></t-icon>
    <span class="cur">{{lang.create_order_detail}}</span>
  </div>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li class="active">
        <a>{{lang.create_order_detail}}</a>
      </li>
      <li>
        <a :href="`order_refund.htm?id=${id}`">{{lang.refund_record}}</a>
      </li>
      <li>
        <a :href="`order_flow.htm?id=${id}`">{{lang.flow}}</a>
      </li>
      <li>
        <a :href="`order_notes.htm?id=${id}`">{{lang.notes}}</a>
      </li>
    </ul>
    <!-- 基础信息 -->
    <div class="top-info">
      <div class="left-box">
        <div class="item">
          <span class="txt">{{lang.order_number}}：</span>
          <span>{{id}}</span>
        </div>
        <div class="item">
          <span class="txt">{{lang.order_type}}：</span>
          <span>{{lang[orderDetail.type]}}</span>
        </div>
        <div class="item">
          <span class="txt">{{lang.user}}：</span>
          <a :href="`client_detail.htm?client_id=${orderDetail.client_id}`" class="info aHover">{{orderDetail.client_name}}</a>
        </div>
        <div class="item">
          <span class="txt">{{lang.order + lang.time}}：</span>
          <span class="info">
            {{moment(orderDetail.create_time * 1000).format('YYYY-MM-DD HH:mm')}}
          </span>
        </div>
        <div class="item">
          <span class="txt">{{lang.order}}{{lang.money}}：</span>
          <span class="info">{{currency_prefix}}&nbsp;{{orderDetail.amount}}</span>
        </div>
        <div class="item">
          <span class="txt">{{lang.balance_used}}：</span>
          <span class="info">{{currency_prefix}}&nbsp;{{orderDetail.credit}}</span>
          <span class="btn" @click="changeCredit('add')" v-if="(orderDetail.amount * 1 !== orderDetail.credit * 1) && orderDetail.apply_credit_amount * 1 > 0">{{lang.app}}{{lang.credit}}</span>
          <span class="btn" @click="changeCredit('sub')" v-if="orderDetail.credit * 1 !== 0">{{lang.deduct}}{{lang.credit}}</span>
        </div>
        <div class="item">
          <span class="txt">{{lang.refunded}}：</span>
          <span class="info">{{currency_prefix}}&nbsp;{{orderDetail.refund_amount}}</span>
          <span class="btn" @click="changeLog">{{lang.change_log}}</span>
        </div>
        <div class="item" v-if="orderDetail.type === 'artificial'">
          <span class="txt">{{lang.operator}}：</span>
          <span class="info">{{orderDetail.admin_name || '--'}}</span>
        </div>
      </div>
      <div class="r-box">
        <div class="con">
          <t-tag theme="default" variant="light" class="order-canceled" v-if="orderDetail.status === 'Cancelled'">{{lang.canceled}}</t-tag>
          <t-tag theme="default" variant="light" class="order-paid" v-if="orderDetail.status === 'Paid'">{{lang.Paid}}</t-tag>
          <t-tag theme="default" variant="light" class="order-refunded" v-if="orderDetail.status === 'Refunded'">{{lang.refunded}}</t-tag>
          <t-tag theme="default" variant="light" class="order-unpaid" v-if="orderDetail.status === 'Unpaid'">{{lang.Unpaid}}</t-tag>
          <template v-if="orderDetail.status === 'Unpaid'">
            <t-select v-model="gateway" :placeholder="lang.pay_way" class="order-pay" @change="changePay">
              <t-option v-for="item in payList" :value="item.name" :label="item.title" :key="item.name">
              </t-option>
            </t-select>
            <P class="signPay" @click="signPay">{{lang.sign_pay}}</P>
          </template>
          <p class="time">
            <template v-if="orderDetail.status === 'Paid'">
              {{moment(orderDetail.pay_time * 1000).format('YYYY-MM-DD HH:mm')}}
            </template>
            <template v-else>
              {{moment(orderDetail.create_time * 1000).format('YYYY-MM-DD HH:mm')}}
            </template>
          </p>
          <p class="gateway">{{orderDetail.gateway}}</p>
        </div>
      </div>
    </div>
    <!-- 底部描述 -->
    <t-table row-key="id" :data="orderDetail.items" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" :hide-sort-tips="true">
      <template slot="sortIcon">
        <t-icon name="caret-down-small"></t-icon>
      </template>
      <template #description="{row}">
        <t-input v-model="row.description" v-if="row.edit"></t-input>
        <template v-else>
          <a :href="`host_detail.htm?client_id=${orderDetail.client_id}&id=${row.host_id}`" class="aHover" v-if="row.host_id">{{row.description}}</a>
          <span v-else>{{row.description}}</span>
        </template>

      </template>
      <template #amount="{row}">
        <t-input v-model="row.amount" :label="currency_prefix" v-if="row.edit"></t-input>
        <span v-else>{{currency_prefix}}{{row.amount}}</span>
      </template>
      <template #op="{row, rowIndex}">
        <template v-if="orderDetail.status === 'Unpaid'">
          <t-tooltip :content="lang.delete" :show-arrow="false" theme="light" v-if="row.edit">
            <t-icon name="delete" size="18px" @click="delteFlow(row, rowIndex)" class="common-look"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.hold" :show-arrow="false" theme="light" v-if="row.edit">
            <t-icon name="save" size="18px" @click="saveFlow(row)" class="common-look"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.add" :show-arrow="false" theme="light" v-if="rowIndex === orderDetail.items.length -1">
            <t-icon name="add-circle" size="18px" @click="addSubItem(row)" class="common-look"></t-icon>
          </t-tooltip>
        </template>
      </template>
    </t-table>
    <!-- 应用/扣除余额 -->
    <t-dialog :visible.sync="visible" :header="title" :on-close="close" :footer="false" width="600" :close-on-overlay-click="false">
      <t-form :rules="rules" :data="formData" ref="userDialog" @submit="onSubmit" v-if="visible" label-width="200">
        <t-form-item :label="`${lang.order_tip1}`" name="amount" v-if="type === 'add'">
          <t-input :placeholder="`${lang.input}${lang.money}`" v-model="formData.amount" @blur="changeAdd" />
        </t-form-item>
        <t-form-item :label="`${lang.order_tip2}`" name="amount" v-if="type === 'sub'">
          <t-input :placeholder="`${lang.input}${lang.money}`" v-model="formData.amount" @blur="changeSub" />
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="close">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 标记支付 -->
    <t-dialog :header="lang.sign_pay" :visible.sync="payVisible" width="600" class="sign_pay">
      <template slot="body">
        <t-form :data="signForm">
          <t-form-item :label="lang.order_amount">
            <t-input :label="currency_prefix" v-model="signForm.amount" disabled />
          </t-form-item>
          <!-- <t-form-item :label="lang.balance_paid">
            <t-input :label="currency_prefix" v-model="signForm.credit" disabled />
          </t-form-item> -->
          <t-form-item :label="lang.no_paid">
            <t-input :label="currency_prefix" v-model="(signForm.credit * 1).toFixed(2)" disabled />
          </t-form-item>
          <!-- <t-checkbox v-model="use_credit" class="checkDelete">{{lang.use_credit}}</t-checkbox> -->
        </t-form>
      </template>
      <template slot="footer">
        <div class="common-dialog" style="margin-top: 20px;">
          <t-button @click="sureSign" :loading="payLoading">{{lang.sure}}</t-button>
          <t-button theme="default" @click="payVisible=false">{{lang.cancel}}</t-button>
        </div>
      </template>
    </t-dialog>
    <!-- 删除提示框 -->
    <t-dialog theme="warning" :header="lang.sureDelete" :close-btn="false" :visible.sync="delVisible">
      <template slot="footer">
        <t-button theme="primary" @click="sureDelUser" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
    <!-- 变更记录 -->
    <t-dialog :visible="visibleLog" :header="lang.change_log" :footer="false" :on-close="closeLog" width="1000">
      <div slot="body">
        <t-table row-key="change_log" :data="logData" size="medium" :columns="logColumns" :hover="hover" :loading="moneyLoading" table-layout="fixed" max-height="350">
          <template #type="{row}">
            {{lang[row.type]}}
          </template>
          <template #amount="{row}">
            <span>
              <span v-if="row.amount * 1 > 0">+</span>{{row.amount}}
            </span>
          </template>
          <template #create_time="{row}">
            {{moment(row.create_time * 1000).format('YYYY/MM/DD HH:mm')}}
          </template>
          <template #admin_name="{row}">
            {{row.admin_name ? row.admin_name : formData.username}}
          </template>
        </t-table>
        <t-pagination v-if="logCunt" :total="logCunt" :page-size="moneyPage.limit" :page-size-options="pageSizeOptions" :on-change="changePage" />
      </div>
    </t-dialog>
  </t-card>
</div>
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/order_details.js"></script>
{include file="footer"}