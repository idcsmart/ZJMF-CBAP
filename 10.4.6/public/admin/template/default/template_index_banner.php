{include file="header"}

<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/template_controller.css" />
<!-- =======内容区域======= -->
<div id="content" class="template template_index_banner" v-cloak>
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
          <div class="left">{{lang.tem_banner}}</div>
          <div class="client-search">
            <t-button @click="addBanner">{{lang.tem_add}}</t-button>
          </div>
        </div>
        <!-- banner -->
        <div class="banner-table">
          <t-table row-key="id" :columns="bannerColumns" :data="tempBanner" :loading="loading" drag-sort="row-handler"
            @drag-sort="onDragSort">
            <template #drag="{row}">
              <t-icon name="move"></t-icon>
            </template>
            <template #img="{row}">
              <img :src="row.img" alt="" class="b-img" v-if="!row.edit" />
              <t-upload v-model="editItem.img" :action="uploadUrl" :headers="uploadHeaders"
                :format-response="formatImgResponse" :placeholder="lang.upload_tip" theme="image" accept="image/*"
                :auto-upload="true" @drop="onDrop" :allow-upload-duplicate-file="false" v-else>
              </t-upload>
            </template>
            <template #url="{row}">
              <span v-if="!row.edit">{{row.url}}</span>
              <t-input v-else v-model="editItem.url" :placeholder="lang.jump_link"></t-input>
            </template>
            <template #time="{row}">
              <template v-if="!row.edit">
                {{moment(row.start_time *
                1000).format('YYYY-MM-DD')}}&nbsp;{{lang.to}}&nbsp;{{moment(row.end_time *
                1000).format('YYYY-MM-DD')}}
              </template>
              <t-date-range-picker allow-input clearable v-else v-model="editItem.timeRange" format="YYYY-MM-DD" />
            </template>
            <template #show="{row}">
              <t-switch v-model="row.show" :custom-value="[1,0]" @change="changeShow($event,row)"></t-switch>
            </template>
            <template #notes="{row}">
              <span v-if="!row.edit">{{row.notes}}</span>
              <t-input v-else v-model="editItem.notes" :placeholder="lang.notes"></t-input>
            </template>
            <template #op="{row, rowIndex}">
              <template v-if="row.edit">
                <t-tooltip :content="lang.cancel" :show-arrow="false" theme="light">
                  <t-icon name="close" class="common-look" @click="cancelItem(row, rowIndex)"></t-icon>
                </t-tooltip>
                <t-tooltip :content="lang.hold" :show-arrow="false" theme="light">
                  <t-icon name="save" class="common-look" @click="saveItem(row,rowIndex)"></t-icon>
                </t-tooltip>
              </template>
              <template v-else>
                <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
                  <t-icon name="edit" size="18px" @click="handlerEdit(row)" class="common-look"></t-icon>
                </t-tooltip>
                <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
                  <t-icon name="delete" class="common-look" @click="delteItem(row)"></t-icon>
                </t-tooltip>
              </template>
            </template>
          </t-table>
        </div>
      </div>
    </t-card>
    <!-- 删除提示框 -->
    <t-dialog theme="warning" :header="lang.temp_sure_delete" :close-btn="false" :visible.sync="delVisible">
      <template slot="footer">
        <t-button theme="primary" @click="sureDelete" :loading="submitLoading">{{lang.tem_sure}}</t-button>
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
<script src="/{$template_catalog}/template/{$themes}/js/template_index_banner.js"></script>
{include file="footer"}
