{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/template_controller.css" />
<!-- =======内容区域======= -->
<div id="content" class="template template_seo_manage template_side_manage" v-cloak>
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
            <t-button @click="handleAdd">{{lang.tem_add}}{{lang.tem_entry}}</t-button>
          </div>
        </div>
        <!-- table -->
        <t-table row-key="id" :data="data" size="medium" :hide-sort-tips="true" :columns="columns" :hover="hover"
          :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" drag-sort="row-handler"
          @drag-sort="onDragSort">
          <template slot="sortIcon">
            <t-icon name="caret-down-small"></t-icon>
          </template>
          <template #drag="{row}">
            <t-icon name="move"></t-icon>
          </template>
          <template #icon="{row}">
            <img :src="row.icon" alt="" class="icon" />
          </template>
          <template #op="{row}">
            <t-tooltip :content="lang.tem_edit" :show-arrow="false" theme="light">
              <t-icon name="edit-1" class="common-look" @click="editHandler(row)">
              </t-icon>
            </t-tooltip>
            </t-tooltip>
            <t-tooltip :content="lang.tem_delete" :show-arrow="false" theme="light">
              <t-icon name="delete" class="common-look" @click="deleteHandler(row)">
              </t-icon>
            </t-tooltip>
          </template>
        </t-table>
      </div>
    </t-card>
    <!-- 弹窗 -->
    <t-dialog :header="optTitle" :visible.sync="visible" :footer="false" width="650" @closed="visible = false"
      placement="center">
      <t-form :rules="rules" ref="comDialog" :data="formData" @submit="onSubmit" :label-width="120" reset-type="initial"
        label-align="top">
        <div class="form-box">
          <t-form-item :label="lang.tem_quick_entry" name="name">
            <t-input v-model="formData.name" :placeholder="lang.tem_input" :maxlength="10" show-limit-number></t-input>
          </t-form-item>
          <t-form-item name="icon" label=" " :required-mark="false">
            <t-upload ref="uploadRef3" :size-limit="{ size: 2, unit: 'MB' }" :action="uploadUrl" v-model="formData.icon"
              :auto-upload="true" @fail="handleFail" theme="custom" :headers="uploadHeaders" accept="image/*"
              :format-response="formatImgResponse">
              <div class="upload">
                <t-icon name="upload"></t-icon>
                <span class="txt">{{lang.tem_upload_icon}}</span>
              </div>
              <div class="up-tip">
                <p>{{lang.tem_tip3}}32px；{{lang.temp_height}}32px</p>
                <p>{{lang.tem_tip4}}</p>
              </div>
            </t-upload>
            <div class="logo tab" v-if="formData.icon[0]?.url">
              <div class="box">
                <img :src="formData.icon[0]?.url" alt="">
                <div class="hover" @click="deleteTabLogo" v-if="formData.icon[0]?.url">
                  <t-icon name="delete"></t-icon>
                </div>
              </div>
              <span class="name">{{formData.icon[0]?.url.split('^')[1]}}</span>
            </div>
          </t-form-item>
        </div>
        <t-form-item :label="lang.tem_hover_content" name="content">
          <t-textarea v-model="formData.content" :placeholder="lang.tem_tip5"></t-textarea>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="visible = false">{{lang.cancel}}</t-button>
        </div>
      </t-form>
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
<script src="/{$template_catalog}/template/{$themes}/js/template_side_manage.js"></script>
{include file="footer"}
