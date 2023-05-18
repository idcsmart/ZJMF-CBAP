<link rel="stylesheet" href="/plugins/addon/idcsmart_withdraw/template/admin/css/withdrawal.css" />
<!-- =======内容区域======= -->

<div id="content" class="withdrawal_create" v-cloak>
  <t-card class="list-card-container">
    <p class="com-h-tit">{{lang.order_new + lang.withdraw}}</p>
    <div class="box">
      <t-form :data="formData" :rules="rules" :label-width="80" ref="formValidatorStatus" @submit="onSubmit">
        <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }" class="no-label">
          <t-col :xs="12" :xl="3" :md="6">
            <p class="s-tit">{{lang.withdrawal_source}}</p>
            <t-form-item name="source" label="">
              <t-select v-model="formData.source" :placeholder="lang.select+lang.withdrawal_source" :popup-props="popupProps">
                <t-option :value="item.name" :label="item.title" v-for="item in sourceList" :key="item.name">
                </t-option>
              </t-select>
            </t-form-item>
          </t-col>
          <t-col :xs="12" :xl="3" :md="6">
            <p class="s-tit">{{lang.withdrawal_way}}</p>
            <t-form-item name="method" label="">
              <t-select v-model="formData.method" :placeholder="lang.select+lang.withdrawal_way" multiple :popup-props="popupProps">
                <t-option v-for="item in ways" :value="item.value" :label="item.label" :key="item.value">
                </t-option>
              </t-select>
              <span class="end-tip">（{{lang.multiple}}）</span>
            </t-form-item>
          </t-col>
          <t-col :xs="12" :xl="3" :md="6">
            <p class="s-tit">{{lang.withdrawal_process}}</p>
            <t-form-item name="process" label="">
              <t-select v-model="formData.process" :placeholder="lang.select+lang.withdrawal_process" :popup-props="popupProps">
                <t-option :value="item.value" :label="item.label" v-for="item in process" :key="item.value">
                </t-option>
              </t-select>
            </t-form-item>
          </t-col>
        </t-row>
        <!-- 金额限制 -->
        <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }" class="no-label">
          <t-col :xs="12" :xl="3" :md="6">
            <p class="s-tit">{{lang.min_money_limit}}</p>
            <t-form-item name="min" label="" :rules="formData.min !== '' ||  formData.max ? [
                    { validator: checkMin},
                    {
                      pattern: /^\d+(\.\d{0,2})?$/, message: lang.verify10, type: 'warning'
                    },
                    {
                      validator: (val) => val > 0, message: lang.verify10, type: 'warning'
                    }
                    ]: []">
              <t-input v-model="formData.min" :placeholder="lang.input+lang.min_money_limit" @change="changeMoney">
              </t-input>
              <span class="no-limit">（{{lang.no_limit}}）</span>
            </t-form-item>
          </t-col>
        </t-row>
        <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }" class="no-label">
          <t-col :xs="12" :xl="3" :md="6">
            <p class="s-tit">{{lang.max_money_limit}}</p>
            <t-form-item name="max" label="" :rules="formData.max !== '' || formData.min ?[
                    { validator: checkMax},
                    {
                      pattern: /^\d+(\.\d{0,2})?$/, message: lang.verify10, type: 'warning'
                    },
                    {
                      validator: (val) => val > 0, message: lang.verify10, type: 'warning'
                    }
                    ]: []">
              <t-input v-model="formData.max" :placeholder="lang.input+lang.max_money_limit">
              </t-input @change="changeMoney">
              <span class="no-limit">（{{lang.no_limit}}）</span>
            </t-form-item>
          </t-col>
        </t-row>
        <!-- 提现周期 -->
        <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }" class="no-label">
          <t-col :xs="12" :xl="3" :md="6">
            <p class="s-tit">{{lang.withdrawal_cycle_limit}}</p>
            <t-form-item name="cycle" label="">
              <t-select v-model="formData.cycle" :placeholder="lang.select+lang.withdrawal_cycle_limit" :popup-props="popupProps">
                <t-option :value="item.value" :label="item.label" v-for="item in cycleList" :key="item.value">
                </t-option>
              </t-select>
            </t-form-item>
          </t-col>
          <t-col :xs="12" :xl="3" :md="6">
            <p class="s-tit"></p>
            <t-form-item name="cycle_limit" :label="lang.withdrawable" class="line-text">
              <t-input v-model="formData.cycle_limit" :placeholder="lang.input+lang.sequence">
              </t-input>
              <span style="margin-left: 5px;white-space: nowrap;">{{lang.sequence}}</span>
              <span class="no-limit">（{{lang.no_limit}}）</span>
            </t-form-item>
          </t-col>
        </t-row>
        <!-- 手续费 -->
        <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }" class="no-label">
          <t-col :xs="12" :xl="3" :md="6">
            <p class="s-tit">{{lang.commission}}</p>
            <t-form-item name="withdraw_fee_type" label="">
              <t-select v-model="formData.withdraw_fee_type" :placeholder="lang.select+lang.withdraw_fee_type" :popup-props="popupProps">
                <t-option :value="item.value" :label="item.label" v-for="item in withdraw_fee" :key="item.value">
                </t-option>
              </t-select>
            </t-form-item>
          </t-col>
          <template v-if="formData.withdraw_fee_type === 'fixed'">
            <t-col :xs="12" :xl="3" :md="6">
              <p class="s-tit"></p>
              <t-form-item name="withdraw_fee" :label="lang.cycle_sequence" class="line-text">
                <t-input v-model="formData.withdraw_fee" :placeholder="lang.input+lang.max_money_limit">
                </t-input>
                <span style="margin-left: 5px;">{{lang.refund_amount_yuan}}</span>
                <span class="no-limit">（{{lang.no_limit}}）</span>
              </t-form-item>
            </t-col>
          </template>
          <template v-else>
            <t-col :xs="12" :xl="3" :md="6">
              <p class="s-tit"></p>
              <t-form-item name="percent" :label="lang.cycle_sequence" class="line-text">
                <t-input v-model="formData.percent" :placeholder="lang.input+lang.max_money_limit">
                </t-input>
                <span style="margin-left: 5px;">%</span>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <p class="s-tit"></p>
              <t-form-item name="percent_min" :label="lang.minimum" class="line-text">
                <t-input v-model="formData.percent_min" :placeholder="lang.input+lang.minimum + lang.money">
                </t-input>
                <span style="margin-left: 5px;">{{lang.refund_amount_yuan}}</span>
              </t-form-item>
            </t-col>
          </template>
        </t-row>
        <div class="f-btn">
          <t-button theme="primary" type="submit" :loading="loading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="back">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </div>
  </t-card>
</div>


<script src="/plugins/addon/idcsmart_withdraw/template/admin/api/withdrawal.js"></script>
<script src="/plugins/addon/idcsmart_withdraw/template/admin/js/withdrawal_update.js"></script>