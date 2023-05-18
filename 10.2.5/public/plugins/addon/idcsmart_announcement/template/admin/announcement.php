<link rel="stylesheet" href="/plugins/addon/idcsmart_announcement/template/admin/css/new.css" />
<link rel="stylesheet" href="/plugins/addon/idcsmart_announcement/template/admin/css/common/reset.css" />
<!-- =======内容区域======= -->
<div id="content" class="help news" v-cloak>
  <t-card class="list-card-container">
    <div class="help_card">
      <div class="help_tabs flex">
        <div class="tabs flex">
          <div class="tabs_item active" @click="changetabs(1)">新增文档</div>
          <div class="tabs_item" @click="changetabs(3)">分类管理</div>
        </div>
        <div class="searchbar com-search">
          <t-input v-model="params.keywords" @change="onEnter" class="search-input" placeholder="请输入你需要搜索的内容" clearable>
          </t-input>
          <t-icon size="20px" name="search" @click="getlist(1)" class="com-search-btn" />
        </div>
        <!-- <div class="searchbar">
                  <t-input placeholder="请输入你需要搜索的内容" v-model="params.keywords" @Enter="onEnter" clearable>
                    <t-icon name="search" slot="suffixIcon" @click="getlist"></t-icon>
                  </t-input>
                </div> -->
      </div>
      <div class="help_table">
        <t-table hover row-key="index" :max-height="maxHeight" :pagination="pagination" :data="list" :columns="columns" @Change="changepages" max-Height="600px">
          <template #pushorback="slotProps">
            <t-switch v-model="slotProps.row.hidden?false:true" @change="onswitch(slotProps.row,$event)" />
          </template>
          <template #create_time="slotProps">
            <span v-if="slotProps.row.create_time" style="width: 200px;">{{
                      getLocalTime(slotProps.row.create_time)
                      }}</span>
          </template>
          <template #op="slotProps">
            <t-icon name="edit-1" color="#0052D9" style="margin-right: 10px;" @click="edit(slotProps.row.id)">
            </t-icon>
            <t-popconfirm theme="warning" content="确认要删除吗？" @Confirm="deletes(slotProps.row.id)">
              <t-icon name="delete" color="#0052D9"></t-icon>
            </t-popconfirm>
          </template>
        </t-table>
      </div>
      <div class="help_pages"></div>
    </div>
  </t-card>
  <t-dialog header="新闻分类管理" placement="center" :visible.sync="visible" :onCancel="onCancel" :onEscKeydown="onKeydownEsc" :onCloseBtnClick="onClickCloseBtn" :onClose="close" width="70%" :confirm-btn='null' cancel-btn="关闭">
    <t-table :key="key" bordered row-key="index" :maxHeight="140" :data="typelist" :columns="columns2" maxHeight="80%">
      <template #name="slotProps">
        <t-input placeholder="请输入" v-model="slotProps.row.name" :disabled="!slotProps.row.isedit" style="width: 250px;" />
      </template>
      <template #time="slotProps">
        <span v-if="slotProps.row.update_time" style="width: 200px;">{{ getLocalTime(slotProps.row.update_time)
                  }}</span>
      </template>
      <template #op="slotProps">
        <div v-if="slotProps.row.id">
          <t-icon v-if="slotProps.row.isedit" name="save" color="#0052D9" style="margin-right: 10px;" @click="edithelptypeform(slotProps.row.name,slotProps.row.id)"></t-icon>
          <t-icon v-if="slotProps.row.isedit" name="close-rectangle" color="#0052D9" @click="canceledit()">
          </t-icon>
          <t-icon v-if="!slotProps.row.isedit" name="edit-1" color="#0052D9" style="margin-right: 10px;" @click="edithandleClickOp(slotProps.row.id)"></t-icon>
          <t-icon v-if="!slotProps.row.isedit" name="delete" color="#0052D9" @click="deleteClickOp(slotProps.row.id)"></t-icon>
        </div>
        <div v-else>
          <t-icon name="save" color="#0052D9" style="margin-right: 10px;" @click="savehandleClickadd(slotProps.row.name)"></t-icon>
          <t-icon name="close-rectangle" color="#0052D9" @click="deleteClickadd(slotProps.row.name)"></t-icon>
        </div>
      </template>
    </t-table>
    <div class="addtype" @click="addtype">新增</div>
  </t-dialog>
</div>
<script src="/plugins/addon/idcsmart_announcement/template/admin/api/new.js"></script>
<script src="/plugins/addon/idcsmart_announcement/template/admin/js/new.js"></script>