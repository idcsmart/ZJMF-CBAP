{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="configuration-system configuration-login" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li v-permission="'auth_system_configuration_system_configuration_system_configuration_view'">
          <a href="configuration_system.htm">{{lang.system_setting}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_debug'">
          <a href="configuration_debug.htm">{{lang.debug_setting}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_access_configuration_view'">
          <a href="configuration_login.htm">{{lang.login_setting}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_theme_configuration_view'">
          <a href="configuration_theme.htm">{{lang.theme_setting}}</a>
        </li>
        <!-- <li
          v-if="$checkPermission('auth_system_configuration_system_configuration_web_configuration') && !hasController">
          <a href="info_config.htm">{{lang.info_config}}</a>
        </li> -->
        <li class="active" v-permission="'auth_system_configuration_system_configuration_oss_management'">
          <a href="javascript:;">{{lang.oss_setting}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_user_api_management'">
          <a href="configuration_api.htm">{{lang.user_api_text1}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_system_info_view'">
          <a style="display: flex; align-items: center;" href="configuration_upgrade.htm">{{lang.system_upgrade}}
            <img v-if="isCanUpdata" style="width: 20px; height: 20px; margin-left: 5px;"
              src="/{$template_catalog}/template/{$themes}/img/upgrade.svg">
          </a>
        </li>
      </ul>
      <div class="oss-box">
        <t-table row-key="id" :data="data" :hover="true" :loading="loading" size="medium" :columns="columns"
          :lazy-load="true">
          <template #title="{row}">
            <span style="display: flex; align-items: center; column-gap: 2px;">
              {{row.title}}
              <!-- <t-tooltip :content="lang.oss_text9" :show-arrow="false" theme="light"> -->
              <img :src="`/{$template_catalog}/template/{$themes}/img/real-1.svg`" alt="" v-if="row.link">
              <!-- </t-tooltip> -->
              <!-- <t-tooltip :content="lang.oss_text25" :show-arrow="false" theme="light"> -->
              <img :src="`/{$template_catalog}/template/{$themes}/img/real-2.svg`" alt="" v-if="!row.link">
              <!-- </t-tooltip> -->
            </span>
          </template>
          <template #status="{row}">
            <t-tag theme="warning" v-if="row.status == 0">{{lang.oss_text25}}</t-tag>
            <t-tag theme="success" v-if="row.status == 1">{{lang.oss_text9}}</t-tag>
            <t-tag v-if="row.status == 3">{{lang.oss_text26}}</t-tag>
          </template>

          <template #have="{row}">
            <span>{{row.have ? lang.yes : '--'}}</span>
          </template>
          <template #op="{row}">
            <template v-if="row.status != 3">
              <t-tooltip :content="lang.oss_text7" :show-arrow="false" theme="light">
                <a :href="row.help_url" v-if="row.help_url" target="_blank">
                  <t-icon name="link" class="common-look"></t-icon>
                </a>
              </t-tooltip>
              <t-tooltip :content="lang.oss_text8" :show-arrow="false" theme="light">
                <t-icon name="tools" class="common-look" @click="handleConfig(row)"></t-icon>
              </t-tooltip>
              <t-tooltip v-if="originMethod != row.name" :content="row.status==1 ? lang.oss_text25 : lang.oss_text9"
                :show-arrow="false" theme="light">
                <t-icon @click="changeStatus(row)" :name="row.status==1 ? 'minus-circle' : 'play-circle-stroke'"
                  class="common-look" :class="{rotate: row.status== 1}">
                </t-icon>
              </t-tooltip>
              <t-tooltip v-if="originMethod != row.name" :content="lang.oss_text10" :show-arrow="false" theme="light">
                <svg class="common-look" @click="handelInstall(row)">
                  <use xlink:href="/{$template_catalog}/template/{$themes}/img/install-icon.svg#cus-uninstall">
                  </use>
                </svg>
              </t-tooltip>
            </template>
            <template v-else>
              <t-tooltip :content="lang.oss_text11" :show-arrow="false" theme="light">
                <span class="custom" @click="handelInstall(row)">
                  <svg class="common-look">
                    <use xlink:href="/{$template_catalog}/template/{$themes}/img/install-icon.svg#cus-install">
                    </use>
                  </svg>
                </span>
              </t-tooltip>
            </template>
          </template>
        </t-table>
        <div class="oss-setting">
          <div class="oss-title">{{lang.oss_text12}}：</div>
          <div class="oss-method">
            <t-select style="width: 360px;" v-model="ossPageData.oss_method" :options="calcOssMethod"
              :placeholder="lang.oss_text12">
            </t-select>
          </div>
          <div class="dia-tips">
            <div>{{lang.oss_text30}}
              <a href="https://wiki.idcsmart.com/web/#/p/e639c84495c5c78a58c89204f1662152" target="_blank">
                <span class="common-look">{{lang.oss_text16}}</span>
              </a>
            </div>
          </div>
        </div>
        <div class="oss-setting">
          <div class="oss-title">{{lang.oss_text21}}：</div>
          <div class="dia-tips">{{lang.oss_text22}}</div>
          <div class="oss-method">
            <t-select :keys="{ label: 'title', value: 'id' }" style="width: 360px;" :label="lang.oss_text31 + ':'"
              clearable v-model="ossPageData.oss_sms_plugin" @change="getTemList" :options="smsList"
              :placeholder="lang.oss_text31">
            </t-select>
            <t-select style="width: 360px;" :label="lang.oss_text32 + ':'" clearable
              v-model="ossPageData.oss_sms_plugin_template" :placeholder="lang.oss_text32">
              <t-option v-for="item in smsTemplateList" :value="item.id" :label="item.title" :key="item.id">
                {{item.title}}
              </t-option>
            </t-select>
            <t-tree-select style="width: 360px;" :label="lang.oss_text33 + ':'"
              v-model="ossPageData.oss_sms_plugin_admin" :data="adminList" multiple :min-collapsed-num="2" clearable
              :placeholder="lang.oss_text33" :tree-props="treeProps">
            </t-tree-select>
          </div>
          <div class="oss-method">
            <t-select :keys="{ label: 'title', value: 'id' }" style="width: 360px;" :label="lang.oss_text34 + ':'"
              clearable v-model="ossPageData.oss_mail_plugin" :options="emailList" :placeholder="lang.oss_text34">
            </t-select>
            <t-select :keys="{ label: 'name', value: 'id' }" style="width: 360px;" :label="lang.oss_text35 + ':'"
              clearable v-model="ossPageData.oss_mail_plugin_template" :options="emailTemplateList"
              :placeholder="lang.oss_text35">
            </t-select>
            <t-tree-select style="width: 360px;" :label="lang.oss_text33 + ':'"
              v-model="ossPageData.oss_mail_plugin_admin" :data="adminList" multiple :min-collapsed-num="2" clearable
              :placeholder="lang.oss_text33" :tree-props="treeProps">
            </t-tree-select>
          </div>
        </div>
        <t-button theme="primary" style="margin-top: 20px;" :loading="saveLoading"
          @click="handelSavePage">{{lang.oss_text13}}</t-button>
      </div>
    </t-card>

    <!-- 配置弹窗 -->
    <t-dialog :header="configTip" :visible.sync="configVisble" :footer="false" width="650">
      <t-form :rules="rules" ref="userDialog" @submit="onSubmit" :label-width="120" v-loading="formLoading">
        <t-form-item :label="item.title" v-for="item in configData" :key="item.title">
          <!-- text -->
          <t-input v-if="item.type==='text'" v-model="item.value"
            :placeholder="item.tip ? item.tip : item.title"></t-input>
          <!-- password -->
          <t-input v-if="item.type==='password'" type="password" v-model="item.value"
            :placeholder="item.tip ? item.tip :item.title"></t-input>
          <!-- textarea -->
          <t-textarea v-if="item.type==='textarea'" v-model="item.value" :placeholder="item.tip ? item.tip :item.title">
          </t-textarea>
          <!-- radio -->
          <t-radio-group v-if="item.type==='radio'" v-model="item.value" :options="computedOptions(item.options)">
          </t-radio-group>
          <!-- checkbox -->
          <t-checkbox-group v-if="item.type==='checkbox'" v-model="item.value" :options="item.options">
          </t-checkbox-group>
          <!-- select -->
          <t-select v-if="item.type==='select'" v-model="item.value" :placeholder="item.tip ? item.tip :item.title">
            <t-option v-for="ele in computedOptions(item.options)" :value="ele.value" :label="ele.label"
              :key="ele.value">
            </t-option>
          </t-select>
        </t-form-item>
        <t-form-item label=" ">
          <div class="dia-tips">
            <div>{{lang.oss_text23}}</div>
            <div>{{lang.oss_text24}}</div>
          </div>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="configVisble=false">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>

    <!-- 启用/停用 -->
    <t-dialog theme="warning" :header="statusTip" :visible.sync="statusVisble">
      <template slot="footer">
        <t-button theme="primary" @click="sureChange" :loading="submitLoading">{{lang.oss_text28}}</t-button>
        <t-button theme="default" @click="statusVisble = false">{{lang.oss_text20}}</t-button>
      </template>
    </t-dialog>


    <!-- 安装/卸载弹窗 -->
    <t-dialog :header="installTip" :visible.sync="installVisible">
      <template slot="footer">
        <t-button theme="primary" @click="sureInstall" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="installVisible = false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>

    <!-- 变更位置确认 -->
    <t-dialog :header="false" :visible.sync="localVisible" :footer="false" width="520">
      <div class="oss-dia-change">
        <div class="left-icon">
          <img src="/{$template_catalog}/template/{$themes}/img/oss-warning.svg" alt="">
        </div>
        <div class="right-main">
          <div class="dia-title">{{lang.oss_text14}}</div>
          <div class="dia-tip">
            {{lang.oss_text15}}
            <span style="color: var(--td-error-color);">{{calcNewName}}</span>
            {{lang.oss_text36}}
            <a href="https://wiki.idcsmart.com/web/#/p/e639c84495c5c78a58c89204f1662152" target="_blank">
              <span class="common-look">{{lang.oss_text16}}</span>
            </a>
            {{lang.oss_text17}}
          </div>
          <div class="dia-pass">
            <t-input type="password" name="" v-model="ossPageData.password" :placeholder="lang.oss_text18"
              oncopy="return false" oncut="return false" onpaste="return false" autocomplete="off">
            </t-input>
          </div>
        </div>
      </div>
      <div class="com-f-btn">
        <t-button theme="primary" :loading="submitLoading" @click="savePageConfig">{{lang.oss_text19}}</t-button>
        <t-button theme="default" variant="base" @click="cancelPageDia">{{lang.oss_text20}}</t-button>
      </div>
    </t-dialog>

  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/configuration_oss.js"></script>

{include file="footer"}
