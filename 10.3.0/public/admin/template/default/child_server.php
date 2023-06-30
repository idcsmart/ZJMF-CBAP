{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<!-- =======内容区域======= -->
<div id="content" class="server child-server admin" v-cloak>
  <t-card class="list-card-container">
    <div class="child-top">
      <h3>
        {{lang.child_module}}
        <a href="server.htm" class="back">
          <t-icon name="chevron-left-double"></t-icon>{{lang.back}}
        </a>
      </h3>
      <span>{{lang.child_tip}}</span>
    </div>
    <ul class="common-tab">
      <li class="active">
        <a href="javascript:;">{{lang.child_interface}}</a>
      </li>
      <li>
        <a href="child_server_group.htm">{{lang.child_interface}}{{lang.group}}</a>
      </li>
      <li>
    </ul>
    <div class="common-header">
      <t-button @click="addUser" class="add">{{lang.create_interface}}</t-button>
      <div class="right-search">
        <t-select v-model="params.status" :placeholder="lang.interface_status" clearable>
          <t-option v-for="item in adminStatus" :value="item.value" :label="item.label" :key="item.value">
          </t-option>
        </t-select>
        <div class="com-search">
          <t-input v-model="params.keywords" class="search-input" :placeholder="lang.nickname" @keyup.enter.native="seacrh" :on-clear="clearKey" clearable>
          </t-input>
        </div>
        <t-button @click="seacrh">{{lang.query}}</t-button>
      </div>
    </div>
    <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange" :hide-sort-tips="hideSortTips" :max-height="maxHeight">
      <template slot="sortIcon">
        <t-icon name="caret-down-small"></t-icon>
      </template>
      <template #host_active_num="{row}">
        {{row.host_num}}({{row.host_active_num}})
      </template>
      <template #name="{row}">
        <t-icon v-if="row.linkStatus === null" name="loading"></t-icon>
        <span @click="getSingleStatus(row)" style="cursor: pointer;" v-else>
          <t-icon v-if="row.linkStatus === 1" name="check-circle-filled" style="color:#00a870;"></t-icon>
          <template v-else>
            <t-tooltip :content="row.fail_reason" theme="light" :show-arrow="false" :disabled="!row.fail_reason">
              <t-icon name="close-circle-filled" class="icon-error" style="color: #e34d59;"></t-icon>
            </t-tooltip>
          </template>
        </span>
        {{row.name}}
      </template>
      <template #title-slot-name>
        {{lang.tailorism}}{{lang.auth_num}}
        <t-tooltip :content="lang.child_used_total" placement="top-right" theme="light" :show-arrow="false">
          <t-icon name="help-circle-filled" class="help"></t-icon>
        </t-tooltip>
      </template>
      <template #pro_name="{row}">
        {{row.used}}/{{row.max_accounts}}
      </template>
      <template #link="{row}">
        <div v-if="row.linkStatus">
          <t-tag theme="success" class="status" variant="light" v-if="">{{row.linkStatus}}</t-tag>
        </div>
      </template>
      <template #module="{row}">
        <span>{{calcName(row.module)}}</span>
      </template>
      <template #status="{row}">
        <t-tag theme="success" class="status" v-if="row.disabled" variant="light">{{lang.enable}}</t-tag>
        <t-tag theme="danger" class="status" v-else variant="light">{{lang.deactivate}}</t-tag>
      </template>
      <template #op="{row}">
        <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
          <t-icon name="edit-1" @click="updateHandler(row)" class="common-look"></t-icon>
        </t-tooltip>
        <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
          <t-icon name="delete" @click="deleteUser(row)" class="common-look"></t-icon>
        </t-tooltip>
      </template>
    </t-table>
    <t-pagination :total="total" :page-size="params.limit" :current="params.page" :page-size-options="pageSizeOptions" :on-change="changePage" />
  </t-card>

  <!-- 添加子接口弹窗 -->
  <t-dialog :visible.sync="visible" :header="title" :on-close="close" :footer="false" width="600" placement="center">
    <t-form :rules="rules" :data="formData" ref="form" @submit="onSubmit">
      <t-form-item :label="lang.nickname" name="name">
        <t-input :placeholder="lang.nickname" v-model="formData.name" />
      </t-form-item>
      <t-form-item :label="`IP${lang.address}`" name="ip_address">
        <t-input :placeholder="`IP${lang.address}`" v-model="formData.ip_address" />
      </t-form-item>
      <t-form-item :label="lang.server_module" name="type" class="child-module">
        <t-select v-model="formData.type" :placeholder="lang.server_module">
          <t-option v-for="item in typeList" :value="item.value" :label="item.name" :key="item.value">
          </t-option>
        </t-select>
        <t-button class="more-module" @click="jumpStore">{{lang.child_tip1}}</t-button>
      </t-form-item>
      <t-form-item name="max_accounts" class="tip-item">
        <template slot="label">
          {{lang.interface_capacity}}
          <t-tooltip :content="lang.child_tip2" placement="top-left" theme="light" :show-arrow="false">
            <t-icon name="help-circle-filled" class="help"></t-icon>
          </t-tooltip>
        </template>
        <t-input :placeholder="lang.interface_capacity" v-model="formData.max_accounts" />
      </t-form-item>
      <t-form-item :label="lang.child_host_name" name="hostname">
        <t-input :placeholder="lang.child_host_name" v-model="formData.hostname" />
      </t-form-item>
      <t-form-item :label="lang.interface_group_name" name="gid">
        <t-select v-model="formData.gid" :placeholder="lang.interface_group_name">
          <t-option v-for="item in groupList" :value="item.id" :label="item.name" :key="item.id">
          </t-option>
        </t-select>
      </t-form-item>
      <t-form-item :label="lang.username" name="username">
        <t-input :placeholder="lang.username" v-model="formData.username" />
      </t-form-item>
      <t-form-item :label="lang.password" name="password">
        <t-input :placeholder="lang.password" type="password" v-model="formData.password" />
      </t-form-item>
      <t-form-item :label="lang.auth_port" name="port">
        <t-input :placeholder="lang.auth_port" v-model="formData.port" />
      </t-form-item>
      <t-form-item label="hash" name="hash">
        <t-textarea placeholder="hash" v-model="formData.hash" />
      </t-form-item>
      <t-form-item :label="lang.ssl_link_mode" name="secure">
        <t-radio-group v-model="formData.secure" :options="options"></t-radio-group>
      </t-form-item>
      <t-form-item :label="lang.isOpen" name="disabled">
        <t-radio-group v-model="formData.disabled" :options="options"></t-radio-group>
      </t-form-item>
      <div class="com-f-btn">
        <t-button theme="primary" type="submit">{{lang.hold}}</t-button>
        <t-button theme="default" variant="base" @click="close">{{lang.cancel}}</t-button>
      </div>
    </t-form>
  </t-dialog>


  <!-- 删除弹窗 -->
  <t-dialog theme="warning" :header="lang.sureDelete" :visible.sync="delVisible">
    <template slot="footer">
      <t-button theme="primary" @click="sureDel">{{lang.sure}}</t-button>
      <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
    </template>
  </t-dialog>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/manage.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/child_server.js"></script>
{include file="footer"}