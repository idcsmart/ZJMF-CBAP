{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/system.css">
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common/viewer.min.css">
<div id="content" class="template feedback" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li class="active">
          <a href="javascript:;">{{lang.feedback}}</a>
        </li>
        <li>
          <a href="consult.htm">{{lang.guidance}}</a>
        </li>
      </ul>
      <div class="box">
        <div class="com-top">
          <p class="tit"></p>
          <div class="add-btn" @click='classManage'>{{lang.type_manage}}</div>
        </div>
        <t-table row-key="id" :data="list" size="medium" :columns="typeColumns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" display-type="fixed-width" :hide-sort-tips="true">
          <template slot="sortIcon">
            <t-icon name="caret-down-small"></t-icon>
          </template>
          <template #unit="{row}">
            <span v-if="row.num">{{row.num}}</span>{{lang[row.unit]}}
          </template>
          <template #username="{row}">
            {{row.username || '--'}}
          </template>
          <template #contact="{row}">
            {{row.contact || '--'}}
          </template>
          <template #create_time="{row}">
            {{row.create_time ? moment(row.create_time * 1000).format('YYYY/MM/DD HH:mm') : '--'}}
          </template>
          <template #op="{row}">
            <t-tooltip :content="`${lang.look}${lang.detail}`" :show-arrow="false" theme="light">
              <t-icon name="view-module" class="common-look" @click="lookDetail(row)"></t-icon>
            </t-tooltip>
          </template>
        </t-table>
        <t-pagination show-jumper :total="total" v-if="total" :page-size="params.limit" :page-size-options="pageSizeOptions" :on-change="changePage" :current="params.page" />
      </div>

    </t-card>
    <!-- 新增/编辑 类型管理-->
    <t-dialog :header="`${lang.type_manage}`" :visible.sync="classModel" :footer="false" width="600" :close-on-overlay-click="false" class="class-dialog">
      <t-form :data="classParams" ref="classForm" @submit="submitSystemGroup" :rules="typeRules" class="cycle-form" v-if="classModel">
        <t-form-item :label="lang.feedback_type" name="name">
          <t-input v-model="classParams.name" :placeholder="`${lang.feedback_type}`"></t-input>
        </t-form-item>
        <t-form-item :label="`${lang.type}${lang.description}`" name="description">
          <t-input v-model="classParams.description" :placeholder="`${lang.type}${lang.description}`"></t-input>
          <t-button theme="primary" type="submit" :loading="submitLoading" style="margin-left: 14px;">{{lang.hold}}
          </t-button>
        </t-form-item>
      </t-form>
      <!-- 分类表格 -->
      <t-table row-key="id" :data="systemGroup" size="medium" :columns="groupColumns" :hover="hover" :loading="typeLoading" table-layout="auto" display-type="fixed-width" :hide-sort-tips="true" max-height="450px" class="type-table">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #image_group_name="{row}">
          <span class="class-name">
            <!-- <img :src="`./img/mf_cloud/${row.icon}.svg`" alt="" class="icon"> -->
            {{row.name}}
          </span>
        </template>
        <template #op="{row}">
          <div class="com-opt">
            <t-icon name="edit-1" @click="editGroup(row)"></t-icon>
            <t-icon name="delete" @click="deleteGroup(row)"></t-icon>
          </div>
        </template>
      </t-table>
    </t-dialog>
    <!-- 删除提示框 -->
    <t-dialog theme="warning" :header="lang.sureDelete" :close-btn="false" :visible.sync="delVisible" class="deleteDialog">
      <template slot="footer">
        <t-button theme="primary" @click="sureDelete" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
    <!-- 反馈详情 -->
    <t-dialog :header="`${lang.feedback_detail}`" :visible.sync="detailModel" :footer="false" width="600" :close-on-overlay-click="false" class="class-dialog">
      <p class="des">
        {{detailObj.description}}
      </p>
      <div class="attachment" v-if="detailObj.attachment.length > 0">
        {{lang.enclosure}}：
        <div class="down">
          <p v-for="(item,index) in detailObj.attachment" :key="index">
            <span @click="downloadFile(item)">{{item.split('^')[1]}}</span>
          </p>
        </div>
      </div>
    </t-dialog>
  </com-config>
  <!-- 图片预览 -->
  <div>
    <img id="viewer" :src="preImg" alt="">
  </div>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/js/common/viewer.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/system.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/feedback.js"></script>
{include file="footer"}
