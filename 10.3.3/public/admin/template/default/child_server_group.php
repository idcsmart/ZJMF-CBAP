{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<!-- =======内容区域======= -->
<div id="content" class="server-group child-server" v-cloak>
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
      <li>
        <a href="child_server.htm">{{lang.child_interface}}</a>
      </li>
      <li class="active">
        <a href="javascript:;">{{lang.child_interface}}{{lang.group}}</a>
      </li>
      <li>
    </ul>
    <div class="common-header">
      <t-button @click="addUser" class="add">{{lang.create_group}}</t-button>
      <!-- <div class="com-search">
                <t-input v-model="params.keywords" class="search-input"
                  :placeholder="`${lang.please_search}${lang.nickname}`" @keyup.enter.native="seacrh"
                  :on-clear="clearKey" clearable>
                </t-input>
                <t-icon size="20px" name="search" @click="seacrh" class="com-search-btn" />
              </div> -->
    </div>
    <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange" :hide-sort-tips="hideSortTips">
      <template slot="sortIcon">
        <t-icon name="caret-down-small"></t-icon>
      </template>
      <template #title-slot-name>
        {{lang.interface_group_num}}
        <t-tooltip :content="lang.child_used_total" placement="top-right" theme="light" :show-arrow="false">
          <t-icon name="help-circle-filled" class="help"></t-icon>
        </t-tooltip>
      </template>
      <template #mode="{row}">
        {{row.mode === 1 ? lang.child_mode1 : lang.child_mode2}}
      </template>
      <template #pro_name="{row}">
        {{row.used}}/{{row.num}}
      </template>
      <template #op="{row}">
        <t-tooltip :content="lang.update_interface" :show-arrow="false" theme="light">
          <t-icon name="edit-1" @click="updateHandler(row)" class="common-look"></t-icon>
        </t-tooltip>
        <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
          <t-icon name="delete" @click="deleteUser(row)" class="common-look"></t-icon>
        </t-tooltip>
      </template>
    </t-table>
    <t-pagination show-jumper :total="total" :page-size="params.limit" :page-size-options="pageSizeOptions" :on-change="changePage" :current="params.page" />
  </t-card>

  <!-- 新建分组 -->
  <t-dialog :visible.sync="visible" :header="title" :on-close="close" :footer="false" width="600" placement="center">
    <t-form :rules="rules" :data="formData" ref="userDialog" @submit="onSubmit" class="child-group-dialog">
      <t-form-item :label="lang.group_name" name="name">
        <t-input :placeholder="lang.group_name" v-model="formData.name" />
      </t-form-item>
      <t-form-item :label="lang.interface" name="server_ids">
        <t-select v-model="formData.server_ids" :placeholder="lang.interface" multiple :max-collapsed-num="5" :popup-props="popupProps">
          <t-option v-for="item in createList" :value="item.id" :label="item.name" :key="item.id">
          </t-option>
        </t-select>
      </t-form-item>
      <t-form-item :label="lang.distribution" name="mode">
        <t-radio-group v-model="formData.mode">
          <t-radio :value="item.value" v-for="(item,index) in options" :key="index">
            {{item.label}}
            <span class="tip">{{item.tip}}</span>
          </t-radio>
        </t-radio-group>
      </t-form-item>
      <div class="com-f-btn">
        <t-button theme="primary" type="submit" style="margin-right: 10px">{{lang.hold}}</t-button>
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
<script src="/{$template_catalog}/template/{$themes}/js/child_server_group.js"></script>
{include file="footer"}
