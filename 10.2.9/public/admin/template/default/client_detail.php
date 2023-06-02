{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<style>
  .t-popup {
    white-space: pre-wrap;
  }
</style>
<div id="content" class="client-detail hasCrumb" v-cloak>
  <!-- crumb -->
  <div class="com-crumb">
    <span>{{lang.user_manage}}</span>
    <t-icon name="chevron-right"></t-icon>
    <a href="client.htm">{{lang.user_list}}</a>
    <t-icon name="chevron-right"></t-icon>
    <span class="cur">{{lang.personal}}</span>
  </div>
  <t-card class="list-card-container" :class="{ stop: data.status===0}">
    <div class="com-h-box">
      <ul class="common-tab">
        <li class="active">
          <a>{{lang.personal}}</a>
        </li>
        <li>
          <a :href="`${baseUrl}/client_host.htm?id=${id}`">{{lang.product_info}}</a>
        </li>
        <li>
          <a :href="`${baseUrl}/client_order.htm?id=${id}`">{{lang.order_manage}}</a>
        </li>
        <li>
          <a :href="`${baseUrl}/client_transaction.htm?id=${id}`">{{lang.flow}}</a>
        </li>
        <li>
          <a :href="`${baseUrl}/client_log.htm?id=${id}`">{{lang.operation}}{{lang.log}}</a>
        </li>
        <li>
          <a :href="`${baseUrl}/client_notice_sms.htm?id=${id}`">{{lang.notice_log}}</a>
        </li>
        <li v-if="hasTicket && authList.includes('TicketController::ticketList')">
          <a :href="`${baseUrl}/plugin/idcsmart_ticket/client_ticket.htm?id=${id}`">{{lang.auto_order}}</a>
        </li>
        <li>
          <a :href="`${baseUrl}/client_records.htm?id=${id}`">{{lang.info_records}}</a>
        </li>
      </ul>
      <t-select class="user" v-if="this.clientList" v-model="id" :popup-props="popupProps" filterable @change="changeUser" :loading="searchLoading" reserve-keyword :on-search="remoteMethod" :filter="filterMethod">
        <t-option :key="data.id" :value="data.id" :label="calcShow(data)" v-if="isExist">
          #{{data.id}}-{{data.username ? data.username : (data.phone? data.phone: data.email)}}
          <span v-if="data.company">({{data.company}})</span>
        </t-option>
        <t-option v-for="item in clientList" :value="item.id" :label="calcShow(item)" :key="item.id">
          #{{item.id}}-{{item.username ? item.username : (item.phone? item.phone: item.email)}}
          <span v-if="item.company">({{item.company}})</span>
        </t-option>
      </t-select>
    </div>
  </t-card>

  <div class="info-card">
    <t-card>
      <h3>账户信息</h3>
      <div class="header-btn">
        <div class="left">
          <!-- 充值按钮 -->
          <t-button theme="primary" @click="showRecharge">{{lang.Recharge}}</t-button>
          <!-- 强制变更 -->
          <template v-if="authList.includes('ClientCreditController::update')">
            <t-button theme="default" @click="changeMoney('recharge')">{{lang.force_change}}</t-button>
          </template>
          <div class="com-transparent change_log" @click="changeLog" v-if="authList.includes('ClientCreditController::clientCreditList')">
            <t-button theme="primary">{{lang.change_log}}</t-button>
            <span class="txt">{{lang.change_log}}</span>
          </div>
        </div>
        <t-button theme="primary" type="submit" @click="loginByUser">{{lang.login_as_user}}</t-button>
      </div>
      <div class="info-box">
        <t-row align="middle">
          <t-col :span="6">
            <span>余额</span>{{thousandth(data.credit)}}{{currency_suffix}}
          </t-col>
          <t-col :span="6">
            <span>产品总数</span>{{data.host_num}}个
          </t-col>
        </t-row>
        <t-row align="middle">
          <t-col :span="6">
            <span>消费</span>{{thousandth(data.consume)}}{{currency_suffix}}
          </t-col>
          <t-col :span="6">
            <span>有效数量</span>{{data.host_active_num}}个
          </t-col>
        </t-row>
        <t-row align="middle">
          <t-col :span="6">
            <span>退款</span>{{thousandth(calcRefund)}}{{currency_suffix}}
          </t-col>
          <t-col :span="6">
            <span>注册时间</span>{{moment(data.register_time * 1000).format('YYYY-MM-DD HH:mm:ss')}}
          </t-col>
        </t-row>
        <t-row align="middle">
          <t-col :span="6">
            <span>提现</span>{{thousandth(data.withdraw)}}{{currency_suffix}}
          </t-col>
          <t-col :span="6">
            <template v-if="hasCertification">
              <span>实名状态</span>
              <div v-if="data.certification === false">
                <t-tooltip content="未实名" theme="light" :show-arrow="false" placement="top-right">
                  <span style="display: flex; align-items: center;">未实名<img src="/{$template_catalog}/template/{$themes}/img/icon/no_authentication.png" alt=""></span>
                </t-tooltip>
              </div>
              <div v-else-if="data.certification && data.certification_detail && data.certification_detail.company?.status === 1">
                <t-tooltip content="企业认证" theme="light" :show-arrow="false" placement="top-right">
                  <span style="display: flex; align-items: center;">{{data.username}}({{data.certification_detail.company.company}})<img src="/{$template_catalog}/template/{$themes}/img/icon/enterprise_authentication.png" alt=""></span>
                </t-tooltip>
              </div>
              <div v-else-if="data.certification && data.certification_detail && data.certification_detail.person?.status === 1">
                <t-tooltip content="个人认证" theme="light" :show-arrow="false" placement="top-right">
                  <span style="display: flex; align-items: center;">{{data.certification_detail.person.card_name}}<img src="/{$template_catalog}/template/{$themes}/img/icon/personal_authentication.png" alt=""></span>
                </t-tooltip>
              </div>
            </template>
          </t-col>
        </t-row>
      </div>
    </t-card>
    <t-card>
      <h3>登录记录</h3>
      <t-table row-key="1" class="ip-table" :data="data.login_logs" size="medium" :bordered="true" :columns="logColumns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'">
        <template #login_time="slotProps">
          {{ moment(slotProps.row.login_time * 1000).format('YYYY-MM-DD HH:mm:ss') }}<span v-if="slotProps.rowIndex === 0">(最近)</span>
        </template>
        <template #ip="slotProps">
          {{ slotProps.row.ip }}<span v-if="slotProps.rowIndex === 0">(最近)</span>
        </template>
      </t-table>
    </t-card>
  </div>

  <t-card class="user-info">
    <h3>基础资料</h3>
    <t-form :data="formData" label-align="top" layout="inline" :rules="rules" ref="userInfo" :class="{ stop: data.status === 0 }">
      <t-form-item :label="lang.name" name="username">
        <t-input v-model="formData.username" :placeholder="lang.name"></t-input>
      </t-form-item>
      <t-form-item :label="lang.clinet_level" name="username" v-if="hasPlugin">
        <t-select v-model="formData.level_id" :placeholder="lang.clinet_level" clearable>
          <t-option v-for="item in levelList" :value="item.id" :label="item.name" :key="item.name">
          </t-option>
        </t-select>
      </t-form-item>
      <t-form-item :label="lang.phone" name="phone" :rules="formData.email ? 
          [{ required: false},{pattern: /^\d{0,11}$/, message: lang.verify11 }]: 
          [{ required: true,message: lang.input + lang.phone, type: 'error' },
          {pattern: /^\d{0,11}$/, message: lang.verify11 }]">
        <t-select v-model="formData.phone_code" filterable style="width: 100px" :placeholder="lang.phone_code">
          <t-option v-for="item in country" :value="item.phone_code" :label="item.name_zh + '+' + item.phone_code" :key="item.name">
          </t-option>
        </t-select>
        <t-input :placeholder="lang.phone" v-model="formData.phone" style="width: calc(100% - 100px);" />
      </t-form-item>
      <t-form-item :label="lang.email" name="email" :rules="formData.phone ? 
              [{ required: false },
              {pattern: /^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z_])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{1,9})$/,
              message: lang.email_tip, type: 'warning' }]: 
              [{ required: true,message: lang.input + lang.email, type: 'error'},
              {pattern: /^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z_])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{1,9})$/,
              message: lang.email_tip, type: 'warning' }
              ]">
        <t-input v-model="formData.email" :placeholder="lang.email"></t-input>
      </t-form-item>
      <t-form-item :label="lang.country" name="country">
        <t-select v-model="formData.country" filterable style="width: 100%" :placeholder="lang.country">
          <t-option v-for="item in country" :value="item.name" :label="item.name_zh" :key="item.name">
          </t-option>
        </t-select>
      </t-form-item>
      <t-form-item :label="lang.address" name="address">
        <t-input v-model="formData.address" :placeholder="lang.address"></t-input>
      </t-form-item>
      <t-form-item :label="lang.company" name="company">
        <t-input v-model="formData.company" :placeholder="lang.company"></t-input>
      </t-form-item>
      <t-form-item :label="lang.language" name="language">
        <t-select v-model="formData.language" :placeholder="lang.select+lang.language">
          <t-option v-for="item in langList" :value="item.display_lang" :label="item.display_name" :key="item.display_lang">
          </t-option>
        </t-select>
      </t-form-item>
      <t-form-item :label="lang.password" name="password">
        <t-input type="password" v-model="formData.password" :placeholder="lang.password"></t-input>
      </t-form-item>
      <template v-for="item in clientCustomList">
        <t-form-item :label="item.name">
          <t-input v-model="item.value" :placeholder="item.name" v-if="item.type === 'text'"></t-input>
          <t-select v-model="item.value" :placeholder="item.name" v-if="item.type === 'dropdown'">
            <t-option v-for="items in item.options" :value="items" :label="items" :key="items">
            </t-option>
          </t-select>
        </t-form-item>
      </template>
      <t-form-item :label="lang.notes" name="notes" class="textarea">
        <t-textarea :placeholder="lang.notes" v-model="formData.notes" />
      </t-form-item>
    </t-form>
    <!-- 底部操作按钮 -->
    <div class="footer-btn">
      <t-button theme="primary" :loading="submitLoading" @click="updateUserInfo" type="submit" v-if="authList.includes('ClientController::update')">{{lang.hold}}</t-button>
      <div class="com-transparent" @click="changeStatus" v-if="authList.includes('ClientController::status')">
        <t-button theme="primary" variant="base">
          <span>{{data.status===0 ? lang.enable :lang.deactivate}}</span>
        </t-button>
        <span class="txt">{{data.status===0 ? lang.enable : lang.deactivate}}</span>
      </div>
      <t-button theme="default" variant="base" @click="deleteUser" v-if="authList.includes('ClientController::delete')">{{lang.delete}}</t-button>
      <!-- <t-button theme="primary" type="submit" @click="loginByUser">{{lang.login_as_user}}</t-button> -->
    </div>
  </t-card>

  <t-card class="user-info" v-show="childList.length > 0">
    <h3>子账户</h3>
    <!-- 子账户 -->
    <div class="login-log chlid-box" style="margin-bottom:40px">
      <t-table row-key="1" :data="childList" size="medium" :bordered="true" :columns="childColumns" :hover="hover" :loading="loading" table-layout="auto">
        <template #last_action_time="{row}">
          {{ row.last_action_time>0 ? moment(row.last_action_time * 1000).format('YYYY-MM-DD HH:mm:ss') : '--'}}
        </template>
        <template #caozuo="{row}">
          <span class="edit-text" @click="goEdit(row.id)">编辑</span>
        </template>
      </t-table>
      <t-pagination v-if="total" :total="total" :page-size="params.limit" :page-size-options="logSizeOptions" :on-change="changePage" />
    </div>
  </t-card>

  <!-- 充值弹窗 -->
  <t-dialog :visible.sync="visibleRecharge" :header="lang.Recharge" :footer="false" @close="closeRechorge">
    <t-form :data="rechargeData" :rules="rechargeRules" ref="rechargeRef" :label-width="80" @submit="confirmRecharge" v-if="visibleRecharge">
      <!-- 支付方式 -->
      <t-form-item :label="lang.pay_way" name="gateway">
        <t-select v-model="rechargeData.gateway" filterable style="width: 100%" :placeholder="lang.select+lang.pay_way">
          <t-option v-for="item in gatewayList" :value="item.name" :label="item.title" :key="item.id">
          </t-option>
        </t-select>
      </t-form-item>
      <!-- 充值金额 -->
      <t-form-item :label="lang.Recharge+lang.money" name="amount">
        <t-input v-model="rechargeData.amount" :placeholder="lang.Recharge+lang.money" :label="currency_prefix">
        </t-input>
      </t-form-item>
      <div class="submit-btn">
        <t-button theme="primary" type="submit">{{lang.sure+lang.Recharge}}</t-button>
        <t-button theme="default" variant="base" @click="closeRechorge">{{lang.cancel}}</t-button>
      </div>
    </t-form>
  </t-dialog>
  <!-- 充值/扣费弹窗 -->
  <t-dialog :header="lang.force_change + lang.money" :visible.sync="visibleMoney" :footer="false" @close="closeMoney">
    <t-form :data="moneyData" :rules="moneyRules" ref="moneyRef" :label-width="80" @submit="confirmMoney" v-if="visibleMoney">
      <t-form-item :label="lang.type" name="amount">
        <t-select v-model="moneyData.type" :placeholder="lang.select+lang.type">
          <t-option value="recharge" :label="lang.add_money" key="recharge"></t-option>
          <t-option value="deduction" :label="lang.sub_money" key="deduction"></t-option>
        </t-select>
      </t-form-item>
      <t-form-item :label="lang.money" name="amount">
        <t-input v-model="moneyData.amount" :placeholder="lang.money" :label="inputLabel">
        </t-input>
      </t-form-item>
      <t-form-item :label="lang.notes">
        <t-textarea v-model="moneyData.notes" :placeholder="lang.notes" />
      </t-form-item>
      <div class="submit-btn">
        <t-button theme="primary" type="submit">{{lang.submit}}</t-button>
        <t-button theme="default" variant="base" @click="closeMoney">{{lang.cancel}}</t-button>
      </div>
    </t-form>
  </t-dialog>
  <!-- 变更记录 -->
  <t-dialog :visible="visibleLog" :header="lang.change_log" :footer="false" :on-close="closeLog" width="1000">
    <div slot="body">
      <t-table row-key="change_log" :data="logData" size="medium" :columns="columns" :hover="hover" :loading="moneyLoading" table-layout="fixed" max-height="350">
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
  <!-- 删除弹窗 -->
  <t-dialog theme="warning" :header="lang.sureDelete" :visible.sync="delVisible">
    <template slot="footer">
      <t-button theme="primary" @click="sureDelUser">{{lang.sure}}</t-button>
      <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
    </template>
  </t-dialog>
  <!-- 启用/停用 -->
  <t-dialog theme="warning" :header="statusTip" :visible.sync="statusVisble">
    <template slot="footer">
      <t-button theme="primary" @click="sureChange">{{lang.sure}}</t-button>
      <t-button theme="default" @click="statusVisble=false">{{lang.cancel}}</t-button>
    </template>
  </t-dialog>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/client_detail.js"></script>
{include file="footer"}