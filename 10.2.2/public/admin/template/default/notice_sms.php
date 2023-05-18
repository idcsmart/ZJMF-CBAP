{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="notice-sms" v-cloak>
  <t-card class="list-card-container">
    <!-- <ul class="common-tab">
      <li class="active">
        <a href="javascript:;">{{lang.sms_interface}}</a>
      </li>
      <li>
        <a href="notice_email.html">{{lang.email_interface}}</a>
      </li>
      <li>
        <a href="notice_send.html">{{lang.send_manage}}</a>
      </li>
    </ul> -->
    <div class="common-header">
      <div class="left">
        <t-button theme="default" @click="getMore" class="add">{{lang.get_more_interface}}</t-button>
      </div>
    </div>
    <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange" :hide-sort-tips="hideSortTips">
      <template #sms_type="{row}">
        <span v-if="row.sms_type.indexOf(1)!==-1">{{lang.international}}&nbsp;/</span>
        <span v-if="row.sms_type.indexOf(0)!==-1">{{lang.domestic}}</span>
      </template>
      <template #version="{row}">
        {{row.version}}
        <t-tooltip :content="lang.upgrade_plugin" :show-arrow="false" theme="light" v-if="row.isUpdate">
          <span class="upgrade" @click="updatePlugin(row)">
            <img :src="`${urlPath}/img/upgrade.svg`" alt="">
          </span>
        </t-tooltip>
      </template>
      <template #status="{row}">
        <t-tag theme="success" class="status" v-if="row.status===1" variant="light">{{lang.enable}}</t-tag>
        <t-tag theme="warning" class="status" v-if="row.status===0" variant="light">{{lang.disable}}</t-tag>
        <t-tag theme="default" class="status" v-if="row.status===3" variant="light">{{lang.not_install}}</t-tag>
      </template>
      <template #op="{row}">
        <t-tooltip :content="enableTitle(row.status)" :show-arrow="false" theme="light">
          <a class="common-look" @click="changeStatus(row)" v-if="row.status !== 3">
            <img v-if="row.status === 0" :src='`${urlPath}/img/icon/enable.png`' alt="">
            <img v-else-if="row.status === 1" :src='`${urlPath}/img/icon/disable.png`' alt="">
          </a>
        </t-tooltip>
        <t-tooltip :content="lang.template_manage" :show-arrow="false" theme="light">
          <a class="common-look" @click="jump(row)" v-if="row.status!==3">
            <t-icon name="control-platform"></t-icon>
          </a>
        </t-tooltip>
        <t-tooltip :content="lang.apply_interface" :show-arrow="false" theme="light">
          <a class="common-look" v-if="row.help_url && row.status !== 3" :href="row.help_url" target="_blank">
            <t-icon name="link" size="20px"></t-icon>
          </a>
        </t-tooltip>
        <t-tooltip :content="lang.config" :show-arrow="false" theme="light">
          <a class="common-look" @click="handleConfig(row)" v-if="row.status!==3">
            <t-icon name="tools"></t-icon>
          </a>
        </t-tooltip>
        <t-tooltip :content="installTitle(row.status)" :show-arrow="false" theme="light">
          <a class="common-look" @click="installHandler(row)">
            <img v-if="row.status === 3" :src='`${urlPath}/img/icon/install.png`' alt="">
            <img v-else-if="row.status !== 3" :src='`${urlPath}/img/icon/uninstall.png`' alt="">
          </a>
        </t-tooltip>
      </template>
    </t-table>
  </t-card>

  <!-- 配置弹窗 -->
  <t-dialog :header="configTip" :visible.sync="configVisble" :footer="false" width="600">
    <t-form :rules="rules" ref="userDialog" @submit="onSubmit" :label-width="170">
      <t-form-item :label="item.title" v-for="item in configData" :key="item.title">
        <!-- text -->
        <t-input v-if="item.type==='text'" v-model="item.value" :placeholder="lang.input+item.title"></t-input>
        <!-- password -->
        <t-input v-if="item.type==='password'" type="password" v-model="item.value"></t-input>
        <!-- textarea -->
        <t-textarea v-if="item.type==='textarea'" v-model="item.value" :placeholder="lang.input+item.title">
        </t-textarea>
        <!-- radio -->
        <t-radio-group v-if="item.type==='radio'" v-model="item.value" :options="computedRadio(item.options)">
        </t-radio-group>
        <!-- checkbox -->
        <t-checkbox-group v-if="item.type==='checkbox'" v-model="item.value" :options="item.options">
        </t-checkbox-group>
        <!-- select -->
        <t-select v-if="item.type==='select'" v-model="item.value" :placeholder="lang.select+item.title">
          <t-option v-for="(value,key) in item.options" :value="value" :label="key" :key="key">
          </t-option>
        </t-select>
      </t-form-item>
      <div class="com-f-btn">
        <t-button theme="primary" type="submit">{{lang.hold}}</t-button>
        <t-button theme="default" variant="base" @click="configVisble=false">{{lang.cancel}}</t-button>
      </div>
    </t-form>
  </t-dialog>

  <!-- 删除弹窗 -->
  <t-dialog theme="warning" :header="installTip" :visible.sync="delVisible">
    <template slot="footer">
      <t-button theme="primary" @click="sureDel">{{lang.sure}}</t-button>
      <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
    </template>
  </t-dialog>

  <!-- 启用/停用 -->
  <t-dialog theme="warning" :header="statusTip" :visible.sync="statusVisble">
    <template slot="footer">
      <t-button theme="primary" @click="sureChange">{{lang.sure}}</t-button>
      <t-button theme="default" @click="closeDialog">{{lang.cancel}}</t-button>
    </template>
  </t-dialog>
  <!-- 升级弹窗 -->
  <t-dialog theme="warning" :header="`${lang.sure}${lang.upgrade_plugin}？`" :visible.sync="upVisible">
    <template slot="footer">
      <t-button theme="primary" @click="sureUpgrade" :loading="upLoading">{{lang.sure}}</t-button>
      <t-button theme="default" @click="upVisible=false">{{lang.cancel}}</t-button>
    </template>
  </t-dialog>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/notice_sms.js"></script>
{include file="footer"}