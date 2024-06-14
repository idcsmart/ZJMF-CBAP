{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<!-- =======内容区域======= -->
<div id="content" class="client" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <div class="common-header">
        <div class="flex">
          <t-button @click="addUser" class="add" v-if="$checkPermission('auth_user_list_create_user')">
            {{lang.create_user}}
          </t-button>
        </div>
        <div class="client-search">
          <t-select v-model="curLevelId" :placeholder="lang.clinet_level" clearable v-if="hasPlugin">
            <t-option v-for="item in levelList" :value="item.id" :label="item.name" :key="item.name">
            </t-option>
          </t-select>
          <t-select v-model="params.type" class="client-type">
            <t-option v-for="item in typeOption" :value="item.value" :label="item.label" :key="item.value"></t-option>
          </t-select>
          <t-input v-model="params.keywords" @keypress.enter.native="search" :placeholder="lang.input" :on-clear="clearKey"
            clearable>
          </t-input>
          <t-button @click="search">{{lang.query}}</t-button>
          <com-view-filed view="client" @changefield="changeField"></com-view-filed>
        </div>
      </div>
      <t-table row-key="id" :data="calcList" size="medium" :columns="columns" :hover="hover" :loading="loading"
        :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange" display-type="fixed-width"
        :hide-sort-tips="true" resizable @row-click="handleClickDetail">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #id="{row}">
          <a :href="`client_detail.htm?client_id=${row.id}`" class="aHover" v-if="showDetails">{{row.id}}</a>
          <span v-else>{{row.id}}</span>
        </template>
        <template #certification="{row}">
          <t-tooltip :show-arrow="false" theme="light">
            <span slot="content">{{!row.certification ? lang.real_tip8 : row.certification_type === 'person' ?
                      lang.real_tip9 : lang.real_tip10}}</span>
            <t-icon :class="row.certification ? 'green-icon' : ''"
              :name="!row.certification ? 'user-clear': row.certification_type === 'person' ? 'user' : 'usergroup'" />
          </t-tooltip>
        </template>
        <template #e-mail="{row}">
          <a :href="`client_detail.htm?client_id=${row.id}`" class="aHover" v-if="showDetails">{{row.email ||
                    '--'}}</a>
          <span v-else>{{row.email || '--'}}</span>
        </template>
        <template #username_company="{row}">
          <t-tooltip :content="filterName(row.custom_field)" :show-arrow="false" theme="light"
            :disabled="row.custom_field.length === 0 || !hasPlugin">
            <a :href="`client_detail.htm?client_id=${row.id}`" class="aHover"
              :class="{bg:row.custom_field.length > 0 && hasPlugin}"
              :style="{'background-color': filterColor(row.custom_field)}" v-if="showDetails">
              {{row.username}}
              <span v-if="row.company">({{row.company}})</span>
            </a>
            <span v-else>{{row.username}}<span v-if="row.company">({{row.company}})</span></span>
            <t-tooltip v-show="row.parent_id" :show-arrow="false" theme="light">
              <span @click="goDetail(row.parent_id)" slot="content" style="cursor: pointer">
                #{{row.parent_id}} {{row.parent_name}}
              </span>
              <t-tag>{{lang.user_text17}}</t-tag>
            </t-tooltip>
        </template>
        <template #host_active_num_host_num="{row}">
          {{row.host_active_num}}({{row.host_num}})
        </template>
        <template #phone="{row}">
          <template v-if="showDetails">
            <a :href="`client_detail.htm?client_id=${row.id}`" class="aHover"
              v-if="row.phone">+{{row.phone_code}}&nbsp;-&nbsp;{{row.phone}}</a>
            <a :href="`client_detail.htm?client_id=${row.id}`" class="aHover" v-else>--</a>
          </template>
          <template v-else>
            <a v-if="row.phone">+{{row.phone_code}}&nbsp;-&nbsp;{{row.phone}}</a>
            <a v-else>--</a>
          </template>
        </template>
        <template #reg_time="{row}">
          {{row.reg_time ? moment(row.reg_time * 1000).format('YYYY-MM-DD HH:mm') : ''}}
        </template>
        <template #client_credit="{row}">
          {{currency_prefix}}{{row.credit | filterMoney}}
        </template>
        <template #cost_price="{row}">
          {{currency_prefix}}{{row.cost_price | filterMoney}}
        </template>
        <template #refund_price="{row}">
          {{currency_prefix}}{{row.refund_price | filterMoney}}
        </template>
        <template #withdraw_price="{row}">
          {{currency_prefix}}{{row.withdraw_price | filterMoney}}
        </template>
        <template #client_status="{row}">
          <t-tag theme="success" class="com-status" v-if="row.status" variant="light">{{lang.enable}}</t-tag>
          <t-tag theme="danger" class="com-status" v-else variant="light">{{lang.deactivate}}</t-tag>
        </template>
        <template #op="{row}">
          <a class="common-look" :href="`client_detail.htm?client_id=${row.id}`">{{lang.look}}</a>
          <a class="common-look" @click="changeStatus(row)">{{row.status ? lang.deactivate : lang.enable}}</a>
          <a class="common-look" @click="deleteUser(row)">{{lang.delete}}</a>
        </template>
      </t-table>
      <t-pagination show-jumper :total="total" :page-size="params.limit" :current="params.page"
        :page-size-options="pageSizeOptions" @change="changePage" />
    </t-card>
    <!-- 添加用户弹窗 -->
    <t-dialog :visible.sync="visible" :header="lang.create_user" :on-close="close" :footer="false" width="600">
      <t-form :rules="rules" :data="formData" ref="userDialog" @submit="onSubmit" name="clientForm">
        <t-form-item :label="lang.name">
          <t-input :placeholder="lang.name" v-model="formData.username" />
        </t-form-item>
        <t-form-item :label="lang.phone" name="phone" :rules="formData.email ?
              [{ required: false},{pattern: /^\d{0,11}$/, message: lang.verify11 }]:
              [{ required: true,message: lang.input + lang.phone, type: 'error' },
              {pattern: /^\d{0,11}$/, message: lang.verify11,type: 'warning' }]">
          <t-select v-model="formData.phone_code" filterable style="width: 100px" :placeholder="lang.phone_code">
            <t-option v-for="item in country" :value="item.phone_code" :label="item.name_zh + '+' + item.phone_code"
              :key="item.name">
            </t-option>
          </t-select>
          <t-input :placeholder="lang.phone" v-model="formData.phone" @change="cancelEmail" />
        </t-form-item>
        <t-form-item :label="lang.email" name="email" class="email" :rules="formData.phone ?
                [{ required: false },
                {pattern: /^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z_])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{1,9})$/,
                message: lang.email_tip, type: 'warning' }]:
                [{ required: true,message: lang.input + lang.email, type: 'error'},
                {pattern: /^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z_])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{1,9})$/,
                message: lang.email_tip, type: 'warning' }
                ]">
          <t-input :placeholder="lang.email" v-model="formData.email" @change="cancelPhone"></t-input>
          <p class="tip" v-show="!formData.phone && !formData.email">{{lang.user_tip}}</p>
        </t-form-item>
        <t-form-item :label="lang.password" name="password">
          <t-input :placeholder="lang.password" :type="formData.password ? 'password' : 'text'"
            v-model="formData.password" autocomplete="off" />
        </t-form-item>
        <t-form-item :label="lang.surePassword" name="repassword">
          <t-input :placeholder="lang.surePassword" :type="formData.repassword ? 'password' : 'text'"
            v-model="formData.repassword" autocomplete="off" />
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="close">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/components/comViewFiled/comViewFiled.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/client.js"></script>
{include file="footer"}
