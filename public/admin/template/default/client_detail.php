{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<div id="content" class="client-detail table hasCrumb" v-cloak>
  <!-- crumb -->
  <div class="com-crumb">
    <span>{{lang.user_manage}}</span>
    <t-icon name="chevron-right"></t-icon>
    <a href="client.html">{{lang.user_list}}</a>
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
          <a :href="`client_host.html?id=${id}`">{{lang.product_info}}</a>
        </li>
        <li>
          <a :href="`client_order.html?id=${id}`">{{lang.order_manage}}</a>
        </li>
        <li>
          <a :href="`client_transaction.html?id=${id}`">{{lang.flow}}</a>
        </li>
        <li>
          <a :href="`client_log.html?id=${id}`">{{lang.log}}</a>
        </li>
        <li>
          <a :href="`client_notice_sms.html?id=${id}`">{{lang.notice_log}}</a>
        </li>
      </ul>
      <t-select class="user" v-if="this.clientList" v-model="id" :popup-props="popupProps" filterable @change="changeUser">
        <t-option v-for="item in clientList" :value="item.id" :label="item.username?item.username:(item.phone?item.phone:item.email)" :key="item.id">
          #{{item.id}}-{{item.username ? item.username : (item.phone? item.phone: item.email)}}
          <span v-if="item.company">({{item.company}})</span>
        </t-option>
      </t-select>
    </div>
    <div class="box scrollbar">
      <t-row :gutter="{ xs: 0, sm: 20, md: 40, lg: 60, xl: 80, xxl: 100 }">
        <!-- 个人中心左侧 -->
        <t-col :xs="12" :xl="6">
          <p class="com-tit"><span>{{ lang.basic_info }}</span></p>
          <t-form :data="formData" :rules="rules" ref="userInfo" :class="{ stop: data.status === 0 }">
            <div class="item">
              <t-form-item :label="lang.name" name="username">
                <t-input v-model="formData.username" :placeholder="lang.input+lang.name"></t-input>
              </t-form-item>
              <t-form-item :label="lang.phone" name="phone" :rules="formData.email ? 
            [{ required: false},{pattern: /^\d{0,11}$/, message: lang.verify11 }]: 
            [{ required: true,message: lang.input + lang.phone, type: 'error' },
            {pattern: /^\d{0,11}$/, message: lang.verify11 }]">
                <t-select v-model="formData.phone_code" filterable style="width: 100px" :placeholder="lang.phone_code">
                  <t-option v-for="item in country" :value="item.phone_code" :label="item.name_zh + '+' + item.phone_code" :key="item.name">
                  </t-option>
                </t-select>
                <t-input :placeholder="lang.input+lang.phone" v-model="formData.phone" style="width: calc(100% - 100px);"/>
              </t-form-item>
            </div>
            <div class="item">
              <t-form-item :label="lang.email" name="email" :rules="formData.phone ? 
                [{ required: false },
                {pattern: /^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{1,9})$/,
                message: lang.email_tip, type: 'warning' }]: 
                [{ required: true,message: lang.input + lang.email, type: 'error'},
                {pattern: /^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{1,9})$/,
                message: lang.email_tip, type: 'warning' }
                ]">
                <t-input v-model="formData.email" :placeholder="lang.input+lang.email"></t-input>
              </t-form-item>
              <t-form-item :label="lang.country" name="country">
                <t-select v-model="formData.country" filterable style="width: 100%" :placeholder="lang.country">
                  <t-option v-for="item in country" :value="item.name" :label="item.name_zh" :key="item.name">
                  </t-option>
                </t-select>
              </t-form-item>
            </div>
            <div class="item">
              <t-form-item :label="lang.address" name="address">
                <t-input v-model="formData.address" :placeholder="lang.input+lang.address"></t-input>
              </t-form-item>
              <t-form-item :label="lang.company" name="company">
                <t-input v-model="formData.company" :placeholder="lang.input+lang.company"></t-input>
              </t-form-item>
            </div>
            <div class="item">
              <t-form-item :label="lang.language" name="language">
                <t-select v-model="formData.language" :placeholder="lang.select+lang.language">
                  <t-option v-for="item in langList" :value="item.display_lang" :label="item.display_name" :key="item.display_lang">
                  </t-option>
                </t-select>
              </t-form-item>
              <t-form-item :label="lang.password" name="password">
                <t-input type="password" v-model="formData.password" :placeholder="lang.input+lang.password"></t-input>
              </t-form-item>
            </div>
            <t-form-item :label="lang.notes" name="notes" class="textarea">
              <t-textarea :placeholder="lang.input+lang.notes" v-model="formData.notes" />
            </t-form-item>
          </t-form>
        </t-col>
        <!-- 个人中心右侧 -->
        <t-col :xs="12" :xl="6">
          <p class="com-tit"><span>{{lang.financial_info}}</span></p>
          <div class="header-btn">
            <!-- <template v-if="authList.includes('ClientCreditController::update')">
              <t-button theme="primary" @click="changeMoney('recharge')">{{lang.add_money}}</t-button>
              <t-button theme="default" @click="changeMoney('deduction')">{{lang.sub_money}}</t-button>
            </template> -->
            <!-- 充值按钮 -->
            <t-button theme="primary" @click="showRecharge">{{lang.Recharge}}</t-button>
            <!-- 强制变更 -->
            <template v-if="authList.includes('ClientCreditController::update')">
              <t-button theme="primary" @click="changeMoney('recharge')">{{lang.force_change}}</t-button>
            </template>
            <div class="com-transparent change_log" @click="changeLog" v-if="authList.includes('ClientCreditController::clientCreditList')">
              <t-button theme="primary">{{lang.change_log}}</t-button>
              <span class="txt">{{lang.change_log}}</span>
            </div>
          </div>
          <t-row :gutter="{ xs: 0, xxl: 30 }" class="dis-box">
            <t-col :xs="12" :xl="3">
              <p>{{lang.credit}}</p>
              <t-input disabled v-model="data.credit" />
            </t-col>
            <t-col :xs="12" :xl="3">
              <p>{{lang.consume}}</p>
              <t-input disabled v-model="data.consume" />
            </t-col>
            <t-col :xs="12" :xl="3">
              <p>{{lang.Refund}}</p>
              <t-input disabled v-model="refundAmount" />
            </t-col>
            <t-col :xs="12" :xl="3">
              <p>{{lang.withdraw}}</p>
              <t-input disabled v-model="data.withdraw" />
            </t-col>
          </t-row>
          <p class="com-tit"><span>{{lang.product_info}}</span></p>
          <t-row :gutter="{ xs: 0, xxl: 30 }" class="dis-box">
            <t-col :xs="12" :xl="3">
              <p>{{lang.host_num}}</p>
              <t-input disabled v-model="data.host_num" />
            </t-col>
            <t-col :xs="12" :xl="3">
              <p>{{lang.host_active_num}}</p>
              <t-input disabled v-model="data.host_active_num" />
            </t-col>
          </t-row>
          <p class="com-tit"><span>{{lang.other_info}}</span></p>
          <t-row :gutter="{ xs: 0, xxl: 30 }" class="dis-box">
            <t-col :xs="12" :xl="4">
              <p>{{lang.register_time}}</p>
              <t-input disabled v-model="moment(data.register_time * 1000).format('YYYY-MM-DD HH:mm:ss')" />
            </t-col>
            <t-col :xs="12" :xl="4">
              <p>{{lang.last_login_time}}</p>
              <t-input disabled v-model="data.last_login_time === 0 ? '-' :moment(data.last_login_time * 1000).format('YYYY-MM-DD HH:mm:ss')" />
            </t-col>
            <t-col :xs="12" :xl="4">
              <p>{{lang.last_login_ip}}</p>
              <t-input disabled v-model="data.last_login_ip" />
            </t-col>
          </t-row>
        </t-col>
      </t-row>
      <!-- 登录日志 -->
      <div class="login-log">
        <p class="com-tit"><span>{{lang.login_record}}</span></p>
        <t-table row-key="1" :data="data.login_logs" size="medium" :bordered="true" :columns="logColumns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'">
          <template #login_time="{row}">
            {{ moment(data.register_time * 1000).format('YYYY-MM-DD HH:mm:ss') }}
          </template>
        </t-table>
        <t-pagination v-if="total" :total="total" :page-size="params.limit" :page-size-options="logSizeOptions" :on-change="changePage" />
      </div>
    </div>
    <!-- 底部操作按钮 -->
    <div class="footer-btn">
      <t-button theme="primary" @click="updateUserInfo" type="submit" v-if="authList.includes('ClientController::update')">{{lang.hold}}</t-button>
      <div class="com-transparent" @click="changeStatus" v-if="authList.includes('ClientController::status')">
        <t-button theme="primary" variant="base">
          <span>{{data.status===0 ? lang.enable :lang.deactivate}}</span>
        </t-button>
        <span class="txt">{{data.status===0 ? lang.enable : lang.deactivate}}</span>
      </div>
      <t-button theme="default" variant="base" @click="deleteUser" v-if="authList.includes('ClientController::delete')">{{lang.delete}}</t-button>
      <t-button theme="primary" type="submit" @click="loginByUser">{{lang.login_as_user}}</t-button>
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
        <t-input v-model="rechargeData.amount" :placeholder="lang.input+lang.Recharge+lang.money" :label="currency_prefix">
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
        <t-input v-model="moneyData.amount" :placeholder="lang.input+lang.money" :label="inputLabel">
        </t-input>
      </t-form-item>
      <t-form-item :label="lang.notes" name="notes">
        <t-textarea v-model="moneyData.notes" :placeholder="lang.input+lang.notes" />
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