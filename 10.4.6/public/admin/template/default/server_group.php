{include file="header"}
<!-- =======内容区域======= -->
<div id="content" class="server-group table" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <div class="common-header">
        <div>
          <t-button @click="addUser" class="add" v-permission="'auth_product_server_group_create_group'">{{lang.create_group}}</t-button>
        </div>
        <div class="com-search">
          <t-input v-model="params.keywords" class="search-input" :placeholder="`${lang.nickname}`" @keypress.enter.native="search" :on-clear="clearKey" clearable>
          </t-input>
          <t-icon size="20px" name="search" @click="search" class="com-search-btn" />
        </div>
      </div>
      <t-table row-key="1" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange" :hide-sort-tips="hideSortTips">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #server="{row}">
          <div v-if="row.server.length>0">
            <span v-for="(item,index) in row.server">
              {{item.name}}<span v-if="index<row.server.length-1">；</span>
            </span>
          </div>
        </template>
        <template #create_time="{row}">
          {{row.create_time === 0 ? '-' : moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm')}}
        </template>
        <template #op="{row}">
          <t-tooltip :content="lang.update_interface" :show-arrow="false" theme="light">
            <t-icon name="edit-1" @click="updateHandler(row)" class="common-look" v-permission="'auth_product_server_group_update_group'"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
            <t-icon name="delete" @click="deleteUser(row)" class="common-look" v-permission="'auth_product_server_group_delete_group'"></t-icon>
          </t-tooltip>
        </template>
      </t-table>
      <t-pagination show-jumper :total="total" :page-size="params.limit" :page-size-options="pageSizeOptions" :on-change="changePage" :current="params.page" />
    </t-card>

    <!-- 新建分组 -->
    <t-dialog :visible.sync="visible" :header="title" :on-close="close" :footer="false" width="600">
      <t-form :rules="rules" :data="formData" ref="userDialog" @submit="onSubmit" v-if="visible">
        <t-form-item :label="lang.group_name" name="name">
          <t-input :placeholder="lang.group_name" v-model="formData.name" />
        </t-form-item>
        <t-form-item :label="lang.interface" name="server_id">
          <t-select v-model="formData.server_id" :placeholder="lang.interface" multiple :max-collapsed-num="5" :popup-props="popupProps">
            <t-option v-for="item in interfaceList" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
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
<script src="/{$template_catalog}/template/{$themes}/js/server_group.js"></script>
{include file="footer"}
