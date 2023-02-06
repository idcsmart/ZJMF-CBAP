{include file="header"}
<!-- =======内容区域======= -->
<div id="content" class="server table" v-cloak>
  <t-card class="list-card-container">
    <div class="common-header">
      <t-button @click="addUser" class="add">{{lang.create_interface}}</t-button>
      <div class="com-search">
        <t-input v-model="params.keywords" class="search-input" :placeholder="`${lang.please_search}ID、${lang.nickname}、${lang.interface_group_name}`" @keyup.enter.native="seacrh" :on-clear="clearKey" clearable>
        </t-input>
        <t-icon size="20px" name="search" @click="seacrh" class="com-search-btn" />
      </div>
    </div>
    <t-table row-key="1" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange" :hide-sort-tips="hideSortTips" >
      <template slot="sortIcon">
        <t-icon name="caret-down-small"></t-icon>
      </template>
      <template #host_active_num="{row}">
        {{row.host_num}}({{row.host_active_num}})
      </template>
      <template #name="{row}">
        <span @click="getSingleStatus(row.id)" style="cursor: pointer;">
          <t-icon v-if="row.linkStatus === 200" name="check-circle-filled" style="color:#00a870;"></t-icon>
          <template v-else>
            <t-tooltip :content="row.fail_reason" theme="light" :show-arrow="false" :disabled="!row.fail_reason">
              <t-icon name="close-circle-filled" class="icon-error" style="color: #e34d59;"></t-icon>
            </t-tooltip>
          </template>
        </span>
        {{row.name}}
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
        <t-tag theme="success" class="status" v-if="row.status" variant="light">{{lang.enable}}</t-tag>
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

  <!-- 添加用户弹窗 -->
  <t-dialog :visible.sync="visible" :header="title" :on-close="close" :footer="false" width="600">
    <t-form :rules="rules" :data="formData" ref="userDialog" @submit="onSubmit" v-if="visible">
      <t-form-item :label="lang.interface_name" name="name">
        <t-input :placeholder="lang.input+lang.interface_name" v-model="formData.name" />
      </t-form-item>
      <t-form-item :label="lang.template_type" name="module">
        <t-select v-model="formData.module" :placeholder="lang.select+lang.template_type">
          <t-option v-for="item in typeList" :value="item.name" :label="item.display_name" :key="item.name">
          </t-option>
        </t-select>
      </t-form-item>
      <t-form-item :label="lang.address" name="url">
        <t-input :placeholder="lang.tip7" v-model="formData.url" />
      </t-form-item>
      <t-form-item :label="lang.username" name="username">
        <t-input :placeholder="lang.input+lang.username" v-model="formData.username" />
      </t-form-item>
      <t-form-item :label="lang.password" name="password">
        <t-input :placeholder="lang.input+lang.password" type="password" v-model="formData.password" />
      </t-form-item>
      <t-form-item label="hash" name="hash">
        <t-textarea :placeholder="lang.input+'hash'" v-model="formData.hash" />
      </t-form-item>
      <t-form-item :label="lang.isOpen" name="status">
        <t-radio-group v-model="formData.status" :options="options" @change="changeStatus"></t-radio-group>
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
<script src="/{$template_catalog}/template/{$themes}/js/server.js"></script>
{include file="footer"}