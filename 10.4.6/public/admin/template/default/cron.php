{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="cron" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <!-- <p class="com-h-tit">{{lang.automation}}</p> -->
      <div class="box">
        <p class="com-tit"><span>{{lang.automation}}</span></p>
        <t-form :data="formData" :label-width="150" ref="formValidatorStatus" label-align="left" @submit="onSubmit"
          label-width="200" :rules="rules">
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
            <t-col :xs="12" :xl="6">
              <div class="item">
                <p class="tit">{{lang.task_queue_commands}}</p>
                <t-textarea v-model="formData.cron_task_shell" disabled />
              </div>
              <div class="item">
                <p class="tit">{{lang.task_queue_status}}</p>
                <t-alert v-if="formData.cron_task_status === 'success'" theme="success">
                  <template slot="message">
                    <span>{{lang.task_queue_normal}}</span>
                  </template>
                </t-alert>
                <t-alert v-if="formData.cron_task_status === 'error'" theme="error">
                  <template slot="message">
                    <span>{{lang.task_queue_abnormal}}</span>
                  </template>
                </t-alert>
              </div>
            </t-col>
          </t-row>
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
            <t-col :xs="12" :xl="6">
              <div class="item">
                <p class="tit">{{lang.automation_scripts}}</p>
                <t-textarea v-model="formData.cron_shell" disabled />
              </div>
              <div class="item">
                <p class="tit">{{lang.automation_status}}</p>
                <t-alert v-if="formData.cron_status === 'success'" theme="success">
                  <template slot="message">
                    <span>{{lang.automation_normal}}</span>
                  </template>
                </t-alert>
                <t-alert v-if="formData.cron_status === 'error'" theme="error">
                  <template slot="message">
                    <span>{{lang.automation_abnormal}}</span>
                  </template>
                </t-alert>
              </div>
            </t-col>
          </t-row>
          <div class="item-box">
            <p class="com-tit"><span>{{lang.time_setting}}</span></p>
            <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
              <t-col :xs="12" :xl="6">
                <t-form-item :label="lang.task_execution_time">
                  <t-select v-model="formData.cron_day_start_time" :placeholder="lang.select" style="width: 182px;">
                    <t-option v-for="item in timeArr" :value="item.value" :label="item.label" :key="item.value">
                    </t-option>
                  </t-select>
                </t-form-item>
              </t-col>
            </t-row>
          </div>
          <div class="item-box">
            <p class="com-tit"><span>{{lang.module}}</span></p>
            <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
              <t-col :xs="12" :xl="6">
                <t-form-item :label="lang.product_suspend">
                  <t-switch size="medium" :custom-value="[1,0]" v-model="formData.cron_due_suspend_swhitch">
                  </t-switch>
                  <span class="tip">{{lang.after_due}}</span>
                  <t-input-number v-model="formData.cron_due_suspend_day" theme="normal"></t-input-number>
                  <span>{{lang.tip12}}</span>
                </t-form-item>
                <t-form-item :label="lang.product_relieve_suspend">
                  <t-switch size="medium" :custom-value="[1,0]" v-model="formData.cron_due_unsuspend_swhitch">
                  </t-switch>
                  <span>{{lang.tip13}}</span>
                </t-form-item>
                <t-form-item :label="lang.product_delete">
                  <t-switch size="medium" :custom-value="[1,0]" v-model="formData.cron_due_terminate_swhitch">
                  </t-switch>
                  <span>{{lang.after_due}}</span>
                  <t-input-number v-model="formData.cron_due_terminate_day" theme="normal"></t-input-number>
                  <span>{{lang.tip14}}</span>
                </t-form-item>
              </t-col>
            </t-row>
          </div>
          <div class="item-box">
            <p class="com-tit"><span>{{lang.financial}}</span></p>
            <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
              <t-col :xs="12" :xl="6">
                <t-form-item :label="lang.order_unpaid_notice" name="cron_due_renewal_first_day">
                  <t-switch size="medium" :custom-value="[1,0]" v-model="formData.cron_order_overdue_swhitch ">
                  </t-switch>
                  <span class="tip">{{lang.after_orders}}</span>
                  <t-input-number v-model="formData.cron_order_overdue_day" theme="normal"></t-input-number>
                  <span>{{lang.day_remind}}</span>
                </t-form-item>
                <t-form-item :label="lang.order_auto_del" name="cron_order_unpaid_delete_swhitch">
                  <t-switch size="medium" :custom-value="[1,0]" v-model="formData.cron_order_unpaid_delete_swhitch ">
                  </t-switch>
                  <span class="tip">{{lang.no_pay}}</span>
                  <t-input-number v-model="formData.cron_order_unpaid_delete_day" theme="normal"></t-input-number>
                  <span>{{lang.day_del}}</span>
                </t-form-item>
                <t-form-item :label="lang.host_renewal_one" name="cron_due_renewal_first_day">
                  <t-switch size="medium" :custom-value="[1,0]" v-model="formData.cron_due_renewal_first_swhitch ">
                  </t-switch>
                  <span class="tip">{{lang.before_due}}</span>
                  <t-input-number v-model="formData.cron_due_renewal_first_day" theme="normal"></t-input-number>
                  <span>{{lang.day_remind}}</span>
                </t-form-item>
                <t-form-item :label="lang.host_renewal_two">
                  <t-switch size="medium" :custom-value="[1,0]" v-model="formData.cron_due_renewal_second_swhitch">
                  </t-switch>
                  <span>{{lang.before_due}}</span>
                  <t-input-number v-model="formData.cron_due_renewal_second_day" theme="normal"></t-input-number>
                  <span>{{lang.day_remind}}</span>
                </t-form-item>
                <t-form-item :label="lang.host_overdue_one">
                  <t-switch size="medium" :custom-value="[1,0]" v-model="formData.cron_overdue_first_swhitch">
                  </t-switch>
                  <span>{{lang.after_due}}</span>
                  <t-input-number v-model="formData.cron_overdue_first_day" theme="normal"></t-input-number>
                  <span>{{lang.day_remind}}</span>
                </t-form-item>
                <t-form-item :label="lang.host_overdue_two">
                  <t-switch size="medium" :custom-value="[1,0]" v-model="formData.cron_overdue_second_swhitch">
                  </t-switch>
                  <span>{{lang.after_due}}</span>
                  <t-input-number v-model="formData.cron_overdue_second_day" theme="normal"></t-input-number>
                  <span>{{lang.day_remind}}</span>
                </t-form-item>
                <t-form-item :label="lang.host_overdue_three">
                  <t-switch size="medium" :custom-value="[1,0]" v-model="formData.cron_overdue_third_swhitch">
                  </t-switch>
                  <span>{{lang.after_due}}</span>
                  <t-input-number v-model="formData.cron_overdue_third_day" theme="normal"></t-input-number>
                  <span>{{lang.day_remind}}</span>
                </t-form-item>
              </t-col>
            </t-row>
          </div>
          <!-- <div class="item-box">
            <p class="com-tit"><span>{{lang.auto_order}}</span></p>
            <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
              <t-col :xs="12" :xl="6">
                <t-form-item :label="lang.replied">
                  <t-switch size="medium" :custom-value="[1,0]" v-model="formData.cron_ticket_close_swhitch"></t-switch>
                  <span>{{lang.replied}}</span>
                  <t-input-number v-model="formData.cron_ticket_close_day" theme="normal"></t-input-number>
                  <span>{{lang.tip15}}</span>
                </t-form-item>
              </t-col>
            </t-row>
          </div> -->
          <!-- <div class="item-box">
            <p class="com-tit"><span>{{lang.promote}}</span></p>
            <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
              <t-col :xs="12" :xl="6">
                <t-form-item :label="lang.promotion_results">
                  <t-switch size="medium" :custom-value="[1,0]" v-model="formData.cron_aff_swhitch"></t-switch>
                  <span>{{lang.tip16}}</span>
                </t-form-item>
              </t-col>
            </t-row>
          </div> -->
          <t-form-item class="f-btn">
            <t-button theme="primary" type="submit" :loading="submitLoading"
              v-permission="'auth_management_cron_save_cron'">{{lang.hold}}</t-button>
            <!-- <t-button theme="default" variant="base">{{lang.close}}</t-button> -->
          </t-form-item>
        </t-form>
      </div>
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/manage.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/cron.js"></script>
{include file="footer"}
