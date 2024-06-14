{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/template_controller.css" />
<!-- =======内容区域======= -->
<div id="content" class="template template_nav_config template_bottom_nav" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <div class="top-box">
        <h2 class="top-back">{{lang.temp_controller}}
          <a :href="backUrl" class="template-back">&lt;&lt;{{lang.temp_back}}</a>
        </h2>
        <div class="top-btn">
          <t-button @click="handleUpgrade" v-if="themeInfo.upgrade === 1">{{lang.upgrade_plugin}}</t-button>
          <t-button theme="danger" @click="handleDelete">{{lang.tem_delete}}</t-button>
        </div>
      </div>
      <t-tabs v-model="tab" class="controller-tab" @change="changeTab">
        <t-tab-panel v-for="item in tabList" :value="item.url" :key="item.name" :label="item.title">
        </t-tab-panel>
      </t-tabs>
      <div class="box">
        <div class="common-header">
          <div class="left"></div>
          <div class="client-search">
            <t-button @click="handleGroup">{{lang.temp_group_manage}}</t-button>
            <t-button @click="handleAdd">{{lang.tem_add}}</t-button>
          </div>
        </div>
        <!-- table -->
        <t-enhanced-table row-key="key" ref="navTable" :data="data" size="medium" :columns="columns" :hover="hover"
          :loading="loading" :hide-sort-tips="true" drag-sort="row-handler" @drag-sort="onDragSort"
          :before-drag-sort="beforeDragSort" @expanded-tree-nodes-change="changeExpand"
          :tree="{ treeNodeColumnIndex: 1 }" :expanded-tree-nodes="expandedRowKeys">
          <template #drag="{row}">
            <t-icon name="move" style="cursor: move;"></t-icon>
          </template>
          <template #name="{row}">
            <span v-if="!row.group_id" class="first_name">{{row.name}}</span>
          </template>
          <template #second="{row}">
            <span v-if="row.group_id">{{row.name}}</span>
          </template>
          <template #show="{row}">
            <t-switch size="large" v-model="row.show" :custom-value="[1,0]" v-if="row.group_id"
              @change="(val)=>changeStatus(val,row)">
            </t-switch>
          </template>
          <template #op="{row}">
            <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
              <t-icon name="edit-1" @click="editHandler(row)" class="common-look" v-if="row.group_id"></t-icon>
            </t-tooltip>
            <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
              <t-icon name="delete" @click="deleteHandler(row)" class="common-look"></t-icon>
            </t-tooltip>
          </template>
        </t-enhanced-table>
      </div>
    </t-card>
    <!-- 弹窗 -->
    <t-dialog :header="optTitle" :visible.sync="visible" :footer="false" width="500" @closed="visible = false"
      placement="center">
      <t-form :rules="rules" ref="comDialog" :data="formData" @submit="onSubmit" :label-width="120" reset-type="initial"
        label-align="top">
        <div class="form-box">
          <t-form-item :label="lang.temp_nav_name" name="name" style="margin-bottom: 24px;">
            <t-input v-model="formData.name" :placeholder="lang.tem_input"></t-input>
          </t-form-item>
          <t-form-item :label="lang.tem_show" label-align="left" class="show" v-if="formData.group_id">
            <t-switch size="medium" v-model="formData.show" :custom-value="[1,0]"></t-switch>
          </t-form-item>
        </div>
        <t-form-item :label="lang.temp_belong_group" name="group_id">
          <t-select v-model="formData.group_id" :placeholder="lang.tem_select" clearable>
            <t-option v-for="item in groupList" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.temp_jump_url" name="url" class="file_address">
          <t-input v-model="formData.url" :placeholder="lang.tem_input"></t-input>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="visible = false">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 分组管理 -->
    <t-dialog :header="optTitle" :visible.sync="groupVisble" :footer="false" width="500" @closed="groupVisble = false"
      placement="center" class="group_dialog">
      <t-form :rules="rules" ref="groupDialog" :data="formData" @submit="submitGroup" :label-width="120"
        reset-type="initial" label-align="top">
        <t-form-item :label="lang.temp_group_name" name="name">
          <t-input v-model="formData.name" :placeholder="lang.tem_input"></t-input>
          <t-button theme="primary" type="submit"
            :loading="submitLoading">{{optType === 'add' ? lang.tem_add : lang.hold}}</t-button>
        </t-form-item>
      </t-form>
      <t-table row-key="id" :data="groupList" size="medium" :hide-sort-tips="true" :columns="groupColumns"
        :hover="hover" :loading="groupLoading" :table-layout="tableLayout ? 'auto' : 'fixed'">
        <template #op="{row}">
          <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
            <t-icon name="edit-1" @click="editGroup(row)" class="common-look"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
            <t-icon name="delete" @click="deleteGroup(row)" class="common-look"></t-icon>
          </t-tooltip>
        </template>
      </t-table>
    </t-dialog>
    <!-- 删除提示框 -->
    <t-dialog theme="warning" :header="lang.temp_sure_delete" :close-btn="false" :visible.sync="delVisible">
      <template slot="footer">
        <t-button theme="primary" @click="sureDelete" :loading="delLoading">{{lang.tem_sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.tem_cancel}}</t-button>
      </template>
    </t-dialog>

    <!-- 删除主题弹窗 -->
    <t-dialog theme="warning" :header="lang.tem_tip14" :close-btn="false" :visible.sync="delDialog">
      <div class="del-tip">
        <div class="del-tip-text">
          <h3>{{lang.tem_tip15}} <span style="color: var(--td-brand-color);"> {{theme}} </span></h3>
          <p>{{lang.tem_tip16}}</p>
        </div>
      </div>
      <template slot="footer">
        <t-button theme="primary" @click="sureDel" :loading="delLoading">{{lang.tem_sure}}</t-button>
        <t-button theme="default" @click="delDialog=false">{{lang.tem_cancel}}</t-button>
      </template>
    </t-dialog>

    <!-- 升级主题弹窗 -->
    <t-dialog :header="lang.tem_tip17" :visible.sync="upgradeDialog" :footer="false" width="500"
      @closed="upgradeDialog = false" placement="center">
      <div class="tem-upgrade-box" style="padding-left: 120px;">
        <p>{{lang.tem_tip18}}：{{themeInfo.old_version}}</p>
        <p>{{lang.tem_tip19}}：{{themeInfo.version}}</p>
        <p>{{lang.tem_tip20}}: {{themeInfo.description || '--'}}</p>
      </div>
      <div class="com-f-btn">
        <t-button theme="primary" @click="sureUpgrade" :loading="submitLoading">{{lang.tem_sure}}</t-button>
        <t-button theme="default" variant="base" @click="upgradeDialog = false">{{lang.cancel}}</t-button>
      </div>
    </t-dialog>

  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/template_controller.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/template_bottom_nav.js"></script>
{include file="footer"}
