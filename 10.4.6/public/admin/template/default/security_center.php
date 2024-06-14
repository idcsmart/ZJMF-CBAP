{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="configuration-system configuration-login" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <div class="safe-box">
        <h3>{{lang.setting_text24}}</h3>
        <p class="com-tit"><span>{{ lang.setting_text25 }}</span></p>
        <t-form label-align="top" colon>
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="lang_admin" :label="lang.setting_text26" style="width: 100%;">
                <t-input default-value="*********" readonly>
                  <template #suffix-icon>
                    <t-icon name="edit" :style="{ cursor: 'pointer' }" @click="handelChangePass(1)"></t-icon>
                  </template>
                </t-input>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="lang_admin" :label="lang.setting_text27" style="width: 100%;">
                <t-input default-value="*********" readonly>
                  <template #suffix-icon>
                    <t-icon name="edit" :style="{ cursor: 'pointer' }" @click="handelChangePass(2)"></t-icon>
                  </template>
                </t-input>
              </t-form-item>
            </t-col>
          </t-row>
        </t-form>

      </div>
    </t-card>

    <!-- 修改密码弹窗开始 -->
    <t-dialog :visible.sync="editPassVisible"
      :header="type === 1 ? lang.setting_text35 : set_operate_password ? lang.setting_text36 : lang.setting_text37"
      :on-close="editPassClose" :footer="false" width="600">
      <t-form :data="editPassFormData" ref="userDialog" @submit="onSubmit" reset-type="initial">
        <t-form-item :label="lang.setting_text28" name="origin_password"
          v-if="(type === 2 && set_operate_password) || type === 1"
          :rules="[{ required: true , message: `${lang.input}${lang.setting_text28}`, type: 'error' }]">
          <t-input :placeholder="`${lang.input}${lang.setting_text28}`" type="password"
            v-model="editPassFormData.origin_password">
          </t-input>
        </t-form-item>
        <t-form-item :label="lang.setting_text30" name="password"
          :rules="type === 1 ? [{ required: true , message: `${lang.input}${lang.setting_text30}`, type: 'error' },{ pattern: /^[\w@!#$%^&*()+-_]{6,32}$/, message: lang.verify8 + '，' + lang.verify14 + '6~32', type: 'warning' }] : [{ required: true , message: `${lang.input}${lang.setting_text30}`, type: 'error' }]">
          <t-input :placeholder="`${lang.input}${lang.setting_text30}`" type="password"
            v-model="editPassFormData.password">
          </t-input>
        </t-form-item>
        <t-form-item :label="lang.surePassword" name="repassword"
          :rules="[{ required: true, message: `${lang.input}${lang.surePassword}`, type: 'error' },{ validator: checkPwd, trigger: 'blur' }]">
          <t-input :placeholder="`${lang.input}${lang.surePassword}`" type="password"
            v-model="editPassFormData.repassword">
          </t-input>
        </t-form-item>
        <div class="f-btn" style="text-align: right;">
          <t-button theme="primary" type="submit" :loading="loading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="editPassClose">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 修改密码弹窗结束 -->
  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/js/security_center.js"></script>
{include file="footer"}
