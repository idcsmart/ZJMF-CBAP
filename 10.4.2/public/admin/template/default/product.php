{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/product.css">
<div id="content" class="product" v-cloak>
  <com-config>
    <t-card class="list-card-container" :class="{stop: data.status===0}">
      <div class="common-header">
        <div class="left">
          <t-button @click="addGroup" class="add" v-permission="'auth_product_management_create_group'">{{lang.create_group}}</t-button>
          <t-button theme="default" @click="addProduct" class="add" v-permission="'auth_product_management_create_product'">{{lang.create_product}}</t-button>
          <t-button theme="default" v-if="hasBaidu" @click="addBaiduProduct" class="add">{{lang.baidu_create}}</t-button>
          <t-button theme="default" @click="addAgency" class="add" v-permission="'auth_product_management_agent_product'">{{lang.immediate_agency}}</t-button>
          <t-button theme="default" class="add anget-btn" @click="handelAngenBtn" v-permission="'auth_product_management_agentable_product'">{{lang.manage_agency}}</t-button>
        </div>
        <div class="com-search">
          <t-input v-model="params.keywords" class="search-input" :placeholder="`${lang.product}`" @keyup.enter.native="seacrh" :on-clear="clearKey" clearable>
          </t-input>
          <t-icon size="20px" name="search" @click="seacrh" class="com-search-btn" />
        </div>
      </div>
      <t-enhanced-table ref="table" row-key="key" drag-sort="row-handler" :data="data" :columns="columns" :key="new Date().getTime()"
      :tree="{ treeNodeColumnIndex: $checkPermission('auth_product_management_list_order') ? 1 : 0}" :loading="loading" :hover="hover" :tree-expand-and-fold-icon="treeExpandAndFoldIconRender" :before-drag-sort="beforeDragSort" @abnormal-drag-sort="onAbnormalDragSort" @drag-sort="changeSort" class="product-table" :row-class-name="rowName">
        <template #drag="{row}">
          <t-icon name="move"></t-icon>
        </template>
        <template #product_group_name="{row}">
          <span v-if="row.name && !row.product_group_name_first && !row.parent_id" class="first-name">
            {{row.name}}
          </span>
          <span v-else-if="row.parent_id" class="second-name">{{row.name}}</span>
        </template>
        <template #name="{row}">
          <a :href="calcHref(row)" v-if="row.qty !== undefined && $checkPermission('auth_product_detail_basic_info_view')" class="product-name" style="cursor: pointer;" @click="editHandler(row)">{{row.name}}</a>
          <span v-if="row.qty !== undefined && !$checkPermission('auth_product_detail_basic_info_view')">{{row.name}}</span>
          <!-- <template v-else>
          <t-icon :name="row.isExpand ? 'caret-up-small' : 'caret-down-small'"></t-icon>
        </template> -->
        </template>
        <template #host_num="{row}">
          <a :href="lookHost(row)" class="host_num" :class="{num: row.host_num > 0}">{{row.host_num}}</a>
        </template>
        <template #qty="{row}">
          {{row.stock_control === 0 ? '-' : row.qty}}
        </template>
        <template #hidden="{row}">
          <t-switch size="large" :custom-value="[0,1]" v-model="row.hidden" @change="onChange(row)" :disabled="!$checkPermission('auth_product_management_product_show_hide')"></t-switch>
        </template>
        <template #op="{row}">
          <t-tooltip :content="lang.copy" :show-arrow="false" theme="light" v-if="row.key.indexOf('t') !== -1 && $checkPermission('auth_product_management_product_copy')">
            <t-icon name="file-copy" size="18px" @click="copyHandler(row)" class="common-look"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
            <t-icon name="edit-1" size="18px" @click="editHandler(row)" class="common-look" v-if="row.key.indexOf('t') !== -1 ?
             $checkPermission('auth_product_management_update_product') : $checkPermission('auth_product_management_update_group')"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
            <t-icon name="delete" size="18px" @click="deleteHandler(row)" class="common-look" v-if="row.key.indexOf('t') !== -1 ?
             $checkPermission('auth_product_management_delete_product') : $checkPermission('auth_product_management_delete_group')"></t-icon>
          </t-tooltip>
        </template>
      </t-enhanced-table>
    </t-card>


    <!-- 可代理商品弹窗 -->
    <t-dialog :visible.sync="agentVisble" :header="lang.can_be_agency" :on-close="closeAgentDia" :footer="false" width="600" class="auth-dialog" placement="center">
      <div class="opt">
        <span>
          <t-checkbox v-model="checkExpand" @change="expandAll">{{lang.isExpand}}</t-checkbox>
          <t-checkbox v-model="checkAll" @change="chooseAll" :disabled="formData.id===1">{{lang.isCheckAll}}</t-checkbox>
        </span>
      </div>
      <div class="auth">
        <t-tree :data="authArr" checkable activable :line="true" :expand-on-click-node="false" :active-multiple="false" v-model="authList" value-mode="all" :expanded="expandArr" :keys="{value: 'key', label:'name', children:'children'}" ref="tree" :expand-all="checkExpand" @click="clickNode" :expand-on-click-node="false" :indeterminate="true"></t-tree>
      </div>
      <div class="com-f-btn">
        <t-button theme="primary" @click="handelAgentable" :loading="submitLoading">{{lang.hold}}</t-button>
        <t-button theme="default" variant="base" @click="closeAgentDia">{{lang.cancel}}</t-button>
      </div>
    </t-dialog>
    <!-- 修改分组名 -->
    <t-dialog :header="updateNameTip" :visible.sync="updateNameVisble" :footer="false">
      <t-form :data="updataData" ref="groupForm" @submit="submitUpdateName" :rules="rules">
        <t-form-item :label="lang.group_name" name="name">
          <t-input v-model="updataData.name" :placeholder="lang.group_name"></t-input>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="updateNameVisble=false">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 新建分组 -->
    <t-dialog :header="lang.create_group" :visible.sync="groupModel" :footer="false" @close="closeGroup">
      <t-form :data="formData" ref="groupForm" @submit="onSubmit" :rules="rules">
        <t-form-item :label="lang.group_name" name="name">
          <t-input v-model="formData.name" :placeholder="lang.group_name"></t-input>
        </t-form-item>
        <t-form-item :label="lang.belong_group" name="id">
          <t-select v-model="formData.id" :placeholder="lang.belong_group" clearable>
            <t-option v-for="item in firstGroup" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="closeGroup">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 新建商品 -->
    <t-dialog :header="lang.create_product" :visible.sync="productModel" :footer="false"
    class="product-dialog" @close="closeProduct">
      <t-form :data="productData" ref="productForm" @submit="submitProduct" :rules="productRules">
        <t-form-item :label="lang.product_name" name="name">
          <t-input v-model="productData.name" :placeholder="lang.product_name"></t-input>
        </t-form-item>
        <t-form-item :label="lang.first_group" name="firstId">
          <t-select v-model="productData.firstId" :placeholder="lang.first_group" @change="changeFirId">
            <t-option v-for="item in firstGroup" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.second_group" name="product_group_id">
          <t-select v-model="productData.product_group_id" :placeholder="lang.second_group">
            <t-option v-for="item in secondGroup" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="closeProduct">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 删除分组下面有商品的时候 -->
    <t-dialog :visible.sync="delHasPro" :footer="false" class="connect-group" :on-close="closeMove">
      <template slot="header">
        <b>{{lang.delete_group}}</b>
        <t-icon name="error-circle" size="16px"></t-icon>
        <span>{{lang.tip8}}</span>
      </template>
      <template slot="body">
        <t-form :data="moveProductForm" :rules="rules" ref="groupDialog" @submit="moveProduct" v-if="delHasPro">
          <t-form-item :label="lang.product_name" name="fail">
            <t-input disabled v-model="concat_shop"></t-input>
          </t-form-item>
          <t-form-item :label="lang.choose_group" name="target_product_group_id">
            <t-select v-model="moveProductForm.target_product_group_id" :placeholder="lang.select +lang.product_group" :popup-props="popupProps">
              <t-option-group v-for="(list, index) in tempGroup" :key="list.key" :label="typeof list.name === 'object' ? list.group.label : list.name" divider>
                <t-option v-for="item in list.children" :value="item.id" :label="item.name" :key="item.id">
                  {{ item.name }}
                </t-option>
              </t-option-group>
            </t-select>
          </t-form-item>
          <div class="com-f-btn">
            <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
            <t-button theme="default" variant="base" @click="closeMove">{{lang.cancel}}</t-button>
          </div>
        </t-form>
      </template>
    </t-dialog>
    <!-- 删除弹窗 -->
    <t-dialog theme="warning" :header="lang.sureDelete" :visible.sync="delVisible">
      <template slot="footer">
        <t-button theme="primary" @click="sureDel" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
    <!-- 复制弹窗 -->
    <t-dialog theme="warning" :header="copyTitle" :visible.sync="copyVisble">
      <template slot="footer">
        <t-button theme="primary" @click="sureCopy" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="copyVisble=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/product.js"></script>
{include file="footer"}
