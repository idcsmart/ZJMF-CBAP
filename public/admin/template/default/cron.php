{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="cron" v-cloak>
  <t-card class="list-card-container">
    <!-- <p class="com-h-tit">{{lang.automation}}</p> -->
    <div class="box">
      <p class="com-tit"><span>{{lang.automation}}</span></p>
      <t-form :data="formData" :label-width="150" ref="formValidatorStatus" label-align="left" @submit="onSubmit" label-width="200" :rules="rules">
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
        <p class="com-tit"><span>{{lang.automation_switch}}</span></p>
        <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
          <t-col :xs="12" :xl="6">
            <t-form-item :label="lang.host_renewal_one" name="cron_due_day_distance_1">
              <t-switch size="medium" :custom-value="[1,0]" v-model="formData.cron_due_day_distance_1_switch"></t-switch>
              <span class="tip">{{lang.before_due}}</span>
              <t-input v-model="formData.cron_due_day_distance_1"></t-input>
              <span>{{lang.day_remind}}</span>
            </t-form-item>
            <t-form-item :label="lang.host_renewal_two" name="cron_due_day_distance_2">
              <t-switch size="medium" :custom-value="[1,0]" v-model="formData.cron_due_day_distance_2_switch"></t-switch>
              <span>{{lang.before_due}}</span>
              <t-input v-model="formData.cron_due_day_distance_2"></t-input>
              <span>{{lang.day_remind}}</span>
            </t-form-item>
            <t-form-item :label="lang.host_renewal_three" name="cron_due_day_distance_3">
              <t-switch size="medium" :custom-value="[1,0]" v-model="formData.cron_due_day_distance_3_switch"></t-switch>
              <span>{{lang.after_due}}</span>
              <t-input v-model="formData.cron_due_day_distance_3"></t-input>
              <span>{{lang.day_remind}}</span>
            </t-form-item>
          </t-col>
        </t-row>
        <p class="com-tit" style="margin-top: 30px;"><span>{{lang.module_tasks}}</span></p>
        <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
          <t-col :xs="12" :xl="6">
            <t-form-item :label="lang.host_suspend" name="cron_due_day_already_suspend">
              <t-switch size="medium" :custom-value="[1,0]" v-model="formData.cron_due_day_already_suspend_switch"></t-switch>
              <span>{{lang.after_due}}</span>
              <t-input v-model="formData.cron_due_day_already_suspend"></t-input>
              <span>{{lang.day_remind}}</span>
            </t-form-item>
            <t-form-item :label="lang.host_terminate" name="cron_due_day_already_terminate">
              <t-switch size="medium" :custom-value="[1,0]" v-model="formData.cron_due_day_already_terminate_switch"></t-switch>
              <span>{{lang.after_due}}</span>
              <t-input v-model="formData.cron_due_day_already_terminate"></t-input>
              <span>{{lang.day_remind}}</span>
            </t-form-item>
          </t-col>
        </t-row>
        <t-form-item class="f-btn">
          <t-button theme="primary" type="submit">{{lang.hold}}</t-button>
          <!-- <t-button theme="default" variant="base">{{lang.close}}</t-button> -->
        </t-form-item>
      </t-form>
    </div>
  </t-card>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/manage.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/cron.js"></script>
{include file="footer"}