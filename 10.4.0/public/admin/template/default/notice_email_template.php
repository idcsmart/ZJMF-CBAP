{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="notice-email-template hasCrumb table" v-cloak>
  <com-config>
    <!-- crumb -->
    <div class="com-crumb">
      <span>{{lang.notice_interface}}</span>
      <t-icon name="chevron-right"></t-icon>
      <a href="notice_email.htm">{{lang.email_notice}}</a>
      <t-icon name="chevron-right"></t-icon>
      <span class="cur">{{lang.template_manage}}</span>
    </div>
    <t-card class="list-card-container">
      <!-- <ul class="common-tab">
      <li>
        <a href="notice_sms.htm">{{lang.sms_interface}}</a>
      </li>
      <li class="active">
        <a href="javascript:;">{{lang.email_interface}}</a>
      </li>
      <li>
        <a href="notice_send.htm">{{lang.send_manage}}</a>
      </li>
    </ul> -->
      <div class="common-header">
        <div class="header-left">
          <div class="com-transparent change_log" @click="jump">
            <t-button theme="primary">{{lang.create_template}}</t-button>
            <span class="txt">{{lang.create_template}}</span>
          </div>
          <!-- <t-button theme="default" class="add">{{lang.get_more_interface}}</t-button> -->
          <t-button theme="default" @click="back" class="add">{{lang.back}}</t-button>
        </div>
        <!-- <div class="search">
        <t-input v-model="params.keywords" class="search-input" :placeholder="lang.search_placeholder" clearable
          :on-enter="seacrh" :on-clear="clearKey">
          <template #suffix-icon>
            <t-icon size="20px" name="search" />
          </template>
        </t-input>
      </div> -->
      </div>
      <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange" :hide-sort-tips="hideSortTips">
        <template #type="{row}">
          <span>{{ row.type === 1 ? lang.international : lang.domestic }}</span>
        </template>
        <template #status="{row}">
          <t-tag theme="warning" class="com-status" v-if="row.status===0" variant="light">{{lang.no_submit}}
          </t-tag>
          <t-tag theme="primary" class="com-status" v-if="row.status===1" variant="light">{{lang.under_review}}
          </t-tag>
          <t-tag theme="success" class="com-status" v-if="row.status===2" variant="light">{{lang.pass}}</t-tag>
          <t-tag theme="danger" class="com-status" v-if="row.status===3" variant="light">{{lang.fail}}</t-tag>
        </template>
        <template #op="{row}">
          <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
            <t-icon name="edit-1" class="common-look" @click="updateHandler(row)">
            </t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.test" :show-arrow="false" theme="light">
            <a class="common-look" @click="testHandler(row)">
              <svg class="common-look">
                <use xlink:href="/{$template_catalog}/template/{$themes}/img/icon/icons.svg#cus-retry">
                </use>
              </svg>
            </a>
          </t-tooltip>
          <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
            <t-icon name="delete" class="common-look" @click="deleteHandler(row)">
            </t-icon>
          </t-tooltip>
        </template>
      </t-table>
    </t-card>

    <!-- 添加管理员 -->
    <t-dialog :visible.sync="visible" :header="addTip" :on-close="close" :footer="false" width="600">
      <t-form :rules="rules" :data="formData" ref="userDialog" @submit="onSubmit">
        <t-form-item :label="lang.choose_area" name="type">
          <t-radio-group v-model="formData.type">
            <t-radio value="0">{{lang.domestic}}</t-radio>
            <t-radio value="1">{{lang.international}}</t-radio>
          </t-radio-group>
        </t-form-item>
        <t-form-item :label="lang.template+'ID'" name="template_id">
          <t-input :placeholder="lang.template+'ID'" v-model="formData.template_id" />
        </t-form-item>
        <t-form-item :label="lang.template+lang.status" name="status">
          <t-select v-model="formData.status" :placeholder="lang.select+lang.template+lang.status">
            <t-option key="0" :label="lang.no_submit_review" value="0"></t-option>
            <t-option key="2" :label="lang.pass_review" value="2"></t-option>
            <t-option key="3" :label="lang.fail_review" value="3"></t-option>
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.title" name="title">
          <t-input :placeholder="lang.title" v-model="formData.title" />
        </t-form-item>
        <t-form-item :label="lang.content" name="content">
          <t-textarea :placeholder="lang.content" v-model="formData.content" />
        </t-form-item>
        <t-form-item :label="lang.notes" name="notes">
          <t-textarea :placeholder="lang.notes" v-model="formData.notes" />
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

    <!-- 测试 -->
    <t-dialog :header="lang.email_test" :visible.sync="statusVisble" :footer="false" width="600">
      <t-form :rules="rules" :data="testForm" ref="userDialog" @submit="testSubmit">
        <t-form-item :label="lang.email" name="email">
          <t-input :placeholder="lang.email" v-model="testForm.email" />
        </t-form-item>
        <t-form-item :label="lang.email_interface" name="name">
          <t-select v-model="testForm.name" :placeholder="lang.email_interface">
            <t-option v-for="item in emailList" :value="item.name" :label="item.title" :key="item.name">
            </t-option>
          </t-select>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.send}}</t-button>
          <t-button theme="default" variant="base" @click="closeTest">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/notice_email_template.js"></script>
{include file="footer"}
