<link rel="stylesheet" href="/plugins/addon/idcsmart_ticket/template/admin/css/ticket_setting.css" />
<!-- =======内容区域======= -->

<div id="content" class="template" v-cloak>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li>
        <a href="index.html">{{lang.work_list}}</a>
      </li>
      <li class="active">
        <a>{{lang.order__configuration}}</a>
      </li>
    </ul>
    <div class="conten-box">
      <div>
          <div class="title-text mar-10">工单类型</div>
          <t-table bordered row-key="id" :data="orderTypeData" :columns="columns">
            <template #name="slotProps">
              <div v-if="!slotProps.row.isedit && !slotProps.row.isAdd">{{slotProps.row.name}}</div>
              <!-- <t-select v-else placeholder="请选择或输入工单类型"  filterable  clearable v-model="slotProps.row.name" :keys="{ label: 'name', value: 'name' }" :options="orderTypeOptions"></t-select> -->
              <t-select-input  v-else :value="slotProps.row.name" :popup-visible="popupVisible" style="width: 300px" placeholder="请选择或输入工单类型" clearable allow-input  @input-change="(val)=> onInputChange(val,slotProps.row)" @popup-visible-change="onPopupVisibleChange" @clear="onClear(slotProps.row)">
                  <template #panel>
                    <ul class="select-ul-div">
                      <li v-for="(item,index) in orderTypeOptions" :key="index" @click="() => onOptionClick(item.name,slotProps.row)">{{ item.name }}</li>
                    </ul>
                  </template>
                  <template #suffixIcon>
                    <chevron-down-icon />
                  </template>
               </t-select-input>
            </template>
            <template #department="slotProps">
              <div v-if="!slotProps.row.isedit && !slotProps.row.isAdd">{{slotProps.row.role_name}}</div>
              <t-select v-else placeholder="请选择处理部门" @change="departmentChange"  filterable  clearable v-model="slotProps.row.admin_role_id" :keys="{ label: 'name', value: 'id' }" :options="departmentOptions"></t-select>
            </template>
            <template #op="slotProps">
              <div v-if="slotProps.row.id">
                <t-icon class="btn-icon" v-if="slotProps.row.isedit" name="save" color="#0052D9" style="margin-right: 10px;" @click="orderTypeMgtSave(slotProps.row)"></t-icon>
                <t-icon class="btn-icon" v-if="slotProps.row.isedit" name="close-rectangle" color="#0052D9" @click="canceledit()">
                </t-icon>
                <t-icon class="btn-icon" v-if="!slotProps.row.isedit" name="edit-1" color="#0052D9" style="margin-right: 10px;" @click="edithandleClickOp(slotProps.row.id)"></t-icon>
                <t-icon class="btn-icon" v-if="!slotProps.row.isedit" name="delete" color="#0052D9" @click="orderTypeMgtDelete(slotProps.row)"></t-icon>
              </div>
              <div v-else>
                <t-icon class="btn-icon" name="save" color="#0052D9" style="margin-right: 10px;" @click="orderTypeMgtSave(slotProps.row)"></t-icon>
                <t-icon class="btn-icon" name="close-rectangle" color="#0052D9" @click="deleteClickadd()"></t-icon>
              </div>
            </template>
            <template #footer-summary>
              <div class="add-tpye-box" @click="appendToRoot">新增 ＋</div>
            </template>
          </t-table>
      </div>
      <div class="status-box">
          <div class="status-title">
            <span class="title-text">工单状态</span>
            <span class="add-stauts-btn" @click="appendStatus">新增</span>
          </div>
          <t-table bordered row-key="id" :data="orderStatusData" :columns="columns2">
            <template #index="slotProps">
              <div>{{slotProps.row.index}}</div>
            </template>
            <template #name="slotProps">
              <div v-if="!slotProps.row.isedit && !slotProps.row.isAdd">{{slotProps.row.name}}</div>
              <t-input v-else placeholder="请输入工单状态" clearable v-model="slotProps.row.name"></t-input>
            </template>
            <template #color="slotProps">
              <div v-if="!slotProps.row.isedit && !slotProps.row.isAdd" :style={background:slotProps.row.color} class="color-box"></div>
              <div v-else class="tdesign-demo-block-row">
                <t-color-picker v-model="slotProps.row.color" />
              </div>
            </template>
            <template #status="slotProps">
              <div v-if="!slotProps.row.isedit && !slotProps.row.isAdd">{{slotProps.row.statusText}}</div>
              <t-select v-else placeholder="请选择工单完结状态" filterable clearable v-model="slotProps.row.status" :keys="{ label: 'statusText', value: 'status' }" :options="statusOpitons"></t-select>
            </template>
            <template #op="slotProps">
              <div v-if="slotProps.row.id">
                <t-icon class="btn-icon" v-if="slotProps.row.isedit" name="save" color="#0052D9" style="margin-right: 10px;" @click="orderStatustSave(slotProps.row)"></t-icon>
                <t-icon class="btn-icon" v-if="slotProps.row.isedit" name="close-rectangle" color="#0052D9" @click="cancelStatusEdit()">
                </t-icon>
                <t-icon class="btn-icon" v-if="!slotProps.row.isedit" name="edit-1" color="#0052D9" style="margin-right: 10px;" @click="editStatus(slotProps.row)"></t-icon>
                <t-icon class="btn-icon" v-if="!slotProps.row.isedit" name="delete" color="#0052D9" @click="orderStatusMgtDelete(slotProps.row)"></t-icon>
              </div>
              <div v-else>
                <t-icon class="btn-icon" name="save" color="#0052D9" style="margin-right: 10px;" @click="orderStatustSave(slotProps.row)"></t-icon>
                <t-icon class="btn-icon" name="close-rectangle" color="#0052D9" @click="deleteStatusadd()"></t-icon>
              </div>
            </template>
            <template #footer-summary>
              <div class="tip-box">
                <span>*</span>
                <span> 待接单、待回复、已回复、已关闭为默认状态，无法修改</span>
              </div>
            </template>
          </t-table>
      </div>
      <div class="prplay-box">
          <div class="title-text mar-10">预设回复</div>
          <t-table bordered row-key="id" :data="prereplyList" :columns="columns3">
            <template #content="slotProps">
              <div v-html="slotProps.row.content" class="repaly-content"></div>
            </template>
            <template #op="slotProps">
              <div>
                <t-icon class="btn-icon" name="edit-1" color="#0052D9" style="margin-right: 10px;" @click="editPrereply(slotProps.row)"></t-icon>
                <t-icon class="btn-icon" name="delete" color="#0052D9" @click="deletePrereply(slotProps.row)"></t-icon>
              </div>
            </template>
          </t-table>
          <div class="input-box">
            <textarea textarea id="tiny" name="content" v-model="prereplyContent"></textarea>
          </div>
          <div class="save-replay-btn">
            <t-button @click="savePreReplay" :loading="saveLoading">保存预设回复</t-button>
          </div>
      </div>
    </div>
    <t-dialog
      :visible.sync="deleteVisible"
      header="提示"
      body="您还未保存正在编辑的内容，是否保存？"
      confirm-btn="保存"
      cancel-btn="不保存"
      @confirm="handelDelete"
      @close="deleteVisible = false"
    >
    </t-dialog>
  </t-card>
</div>

<script src="/plugins/addon/idcsmart_ticket/template/admin/api/order.js"></script>
<script src="/plugins/addon/idcsmart_ticket/template/admin/js/ticket_setting.js"></script>
<script src="/plugins/addon/idcsmart_ticket/template/admin/js/tinymce/tinymce.min.js"></script>