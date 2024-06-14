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
        <li v-permission="'auth_system_configuration_system_configuration_oss_management'">
          <a href="configuration_oss.htm">{{lang.oss_setting}}</a>
        </li>
        <li class="active" v-permission="'auth_system_configuration_system_configuration_user_api_management'">
          <a href="javascript:;">{{lang.user_api_text1}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_system_info_view'">
          <a style="display: flex; align-items: center;" href="configuration_upgrade.htm">{{lang.system_upgrade}}
            <img v-if="isCanUpdata" style="width: 20px; height: 20px; margin-left: 5px;" src="/{$template_catalog}/template/{$themes}/img/upgrade.svg">
          </a>
        </li>
      </ul>
      <div class="api-box">
        <div class="api-switch">
          <span>{{lang.user_api_text2}}</span>
          <t-switch size="large" v-model="configData.client_create_api" :custom-value="[1,0]" @change="changeSwitch"></t-switch>
          <span class="tips-text">{{lang.user_api_text3}}</span>
        </div>
        <div class="api-radio" v-if="configData.client_create_api === 1">
          <t-radio-group v-model="configData.client_create_api_type" @change="typeChange">
            <t-radio :value="0">{{lang.user_api_text4}}</t-radio>
            <t-radio :value="1">{{lang.user_api_text5}}</t-radio>
            <t-radio :value="2">
              {{lang.user_api_text6}}
              <span style="color: var(--td-warning-color-5);">{{lang.user_api_text7}}</span>
              {{lang.user_api_text8}}
            </t-radio>
          </t-radio-group>
        </div>
        <template v-if="configData.client_create_api_type !== 0 && configData.client_create_api !== 0">
          <div class="add-user">
            <com-choose-user :check-id="client_id" :pre-placeholder="lang.user_api_text6" @changeuser="changeUser">
            </com-choose-user>
            <t-button theme="primary" @click="addUser" :loading="addLoading">{{lang.add}}</t-button>
          </div>
          <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" display-type="fixed-width" @sort-change="sortChange" :hide-sort-tips="true">
            <template slot="sortIcon">
              <t-icon name="caret-down-small"></t-icon>
            </template>
            <template #id="{row}">
              <a :href="`client_detail.htm?client_id=${row.id}`" class="aHover" v-if="showDetails">{{row.id}}</a>
              <span v-else>{{row.id}}</span>
            </template>
            <template #certification="{row}">
              <t-tooltip :show-arrow="false" theme="light">
                <span slot="content">{{!row.certification ? lang.real_tip8 : row.certification_type === 'person' ? lang.real_tip9 : lang.real_tip10}}</span>
                <t-icon :class="row.certification ? 'green-icon' : ''" :name="!row.certification ? 'user-clear': row.certification_type === 'person' ? 'user' : 'usergroup'" />
              </t-tooltip>
            </template>
            <template #e-mail="{row}">
              <a :href="`client_detail.htm?client_id=${row.id}`" class="aHover" v-if="showDetails">{{row.email || '--'}}</a>
              <span v-else>{{row.email || '--'}}</span>
            </template>
            <template #username="{row}">
              <t-tooltip :content="filterName(row.custom_field)" :show-arrow="false" theme="light" :disabled="row.custom_field.length === 0 || !hasPlugin">
                <a :href="`client_detail.htm?client_id=${row.id}`" class="aHover" :class="{bg:row.custom_field.length > 0 && hasPlugin}" :style="{'background-color': filterColor(row.custom_field)}" v-if="showDetails">
                  {{row.username}}</a>
                <span v-else>{{row.username}}</span>
                <t-tooltip v-show="row.parent_id" :show-arrow="false" theme="light">
                  <span @click="goDetail(row.parent_id)" slot="content" style="cursor: pointer">
                    #{{row.parent_id}} {{row.parent_name}}
                  </span>
                  <t-tag>{{lang.user_text17}}</t-tag>
                </t-tooltip>
            </template>
            <template #host_active_num="{row}">
              {{row.host_active_num}}({{row.host_num}})
            </template>
            <template #phone="{row}">
              <template v-if="showDetails">
                <a :href="`client_detail.htm?client_id=${row.id}`" class="aHover" v-if="row.phone">+{{row.phone_code}}&nbsp;-&nbsp;{{row.phone}}</a>
                <a :href="`client_detail.htm?client_id=${row.id}`" class="aHover" v-else>--</a>
              </template>
              <template v-else>
                <a v-if="row.phone">+{{row.phone_code}}&nbsp;-&nbsp;{{row.phone}}</a>
                <a v-else>--</a>
              </template>
            </template>
            <template #status="{row}">
              <t-tag theme="success" class="com-status" v-if="row.status" variant="light">{{lang.enable}}</t-tag>
              <t-tag theme="danger" class="com-status" v-else variant="light">{{lang.deactivate}}</t-tag>
            </template>
            <template #op="{row}">
              <t-tooltip :content="lang.user_api_text9" :show-arrow="false" theme="light">
                <t-icon @click="clickDel(row)" name="minus-circle" class="common-look">
                </t-icon>
              </t-tooltip>
            </template>
          </t-table>
          <t-pagination show-jumper :total="total" :page-size="params.limit" :current="params.page" :page-size-options="pageSizeOptions" @change="changePage">
          </t-pagination>
        </template>
      </div>
    </t-card>

    <!-- 启用/停用 -->
    <t-dialog theme="warning" :header="lang.sureDelete" :visible.sync="statusVisble">
      <template slot="footer">
        <t-button theme="primary" @click="changeStatus" :loading="submitLoading">{{lang.oss_text28}}</t-button>
        <t-button theme="default" @click="statusVisble = false">{{lang.oss_text20}}</t-button>
      </template>
    </t-dialog>




  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/configuration_api.js"></script>
<script src="/{$template_catalog}/template/{$themes}/components/comChooseUser/comChooseUser.js"></script>

{include file="footer"}
