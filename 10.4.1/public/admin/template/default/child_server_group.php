{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<!-- =======内容区域======= -->
<div id="content" class="server-group child-server" v-cloak>
  <com-config>
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
        <li v-permission="'auth_product_server_sub_server_sub_server_view'">
          <a href="child_server.htm">{{lang.child_interface}}</a>
        </li>
        <li class="active" v-permission="'auth_product_server_sub_server_group_view'">
          <a href="javascript:;">{{lang.child_interface}}{{lang.group}}</a>
        </li>
      </ul>
      <div class="common-header">
        <t-button @click="addUser" class="add"  v-permission="'auth_product_server_sub_server_group_create_group'">{{lang.create_group}}</t-button>
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
            <t-icon name="edit-1" @click="updateHandler(row)" class="common-look" v-permission="'auth_product_server_sub_server_group_update_group'"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
            <t-icon name="delete" @click="deleteUser(row)" class="common-look" v-permission="'auth_product_server_sub_server_group_delete_group'"></t-icon>
          </t-tooltip>
        </template>
      </t-table>
      <t-pagination show-jumper v-if="total" :total="total" :page-size="params.limit" :page-size-options="pageSizeOptions" :on-change="changePage" :current="params.page" />
    </t-card>

    <!-- 新建分组 -->
    <t-dialog :visible.sync="visible" :header="title" :on-close="close"
    :footer="false" width="600" placement="center" class="child-group">
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
          <t-button theme="primary" type="submit" style="margin-right: 10px" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="close">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>

    <!-- 删除弹窗 -->
    <t-dialog theme="warning" :header="lang.sureDelete" :visible.sync="delVisible">
      <template slot="footer">
        <t-button theme="primary" @click="sureDel" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/manage.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/child_server_group.js"></script>
{include file="footer"}
