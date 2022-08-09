{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/addon.css">
<!-- =======内容区域======= -->
<div id="content" class="addon table" v-cloak>
  <t-card class="list-card-container">
    <div class="common-header">
      <t-button class="add">{{lang.more_plugins}}</t-button>
      <div class="com-search">
        <t-input v-model="params.keywords" class="search-input"
          :placeholder="`${lang.please_search}${lang.plug_name}`"
          @keyup.enter.native="seacrh" :on-clear="clearKey" clearable>
        </t-input>
        <t-icon size="20px" name="search" @click="seacrh" class="com-search-btn" />
      </div>
    </div>
    <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading"
      :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange" display-type="fixed-width"
      :hide-sort-tips="true" :max-height="maxHeight">
      <template slot="sortIcon">
        <t-icon name="caret-down-small"></t-icon>
      </template>
      <template #status="{row}">
        <t-tag theme="success" class="com-status" v-if="row.status" variant="light">{{lang.enable}}</t-tag>
        <t-tag theme="danger" class="com-status" v-else variant="light">{{lang.deactivate}}</t-tag>
      </template>
      <template #op="{row}">
        <a class="common-look" @click="changeStatus(row)">
          <img v-if="row.status === 0" src='img/icon/enable.png' alt="">
          <img v-else-if="row.status === 1" src='img/icon/disable.png' alt="">
        </a>
        <a class="common-look" @click="installHandler(row)">
          <img v-if="row.status === 3" src='img/icon/install.png' alt="">
          <img v-else-if="row.status !== 3" src='img/icon/uninstall.png' alt="">
        </a>
      </template>
    </t-table>
    <!-- <t-pagination :total="total" :page-size="params.limit" :current="params.page"
      :page-size-options="pageSizeOptions" @change="changePage" /> -->
  </t-card>

  <!-- 添加用户弹窗 -->
  <!-- <t-dialog :visible.sync="visible" :header="lang.create_user" :on-close="close" :footer="false" width="600">
    <t-form :rules="rules" :data="formData" ref="userDialog" @submit="onSubmit">
      <t-form-item :label="lang.name">
        <t-input :placeholder="lang.please_enter_name" v-model="formData.username" />
      </t-form-item>
      <t-form-item :label="lang.phone" name="phone" :rules="formData.email ? 
      [{ required: false},{pattern: /^\d{0,11}$/, message: lang.verify11 }]: 
      [{ required: true,message: lang.input + lang.phone, type: 'error' },
      {pattern: /^\d{0,11}$/, message: lang.verify11,type: 'warning' }]">
        <t-select v-model="formData.phone_code" filterable style="width: 100px" :placeholder="lang.phone_code">
          <t-option v-for="item in country" :value="item.phone_code"
            :label="item.name_zh + '+' + item.phone_code" :key="item.name">
          </t-option>
        </t-select>
        <t-input :placeholder="lang.input+lang.phone" v-model="formData.phone" @change="cancelEmail"/>
      </t-form-item>
      <t-form-item :label="lang.email" name="email" class="email" 
        :rules="formData.phone ? 
        [{ required: false }, 
        {pattern: /^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{1,9})$/,
        message: lang.email_tip, type: 'warning' }]: 
        [{ required: true,message: lang.input + lang.email, type: 'error'},
        {pattern: /^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{1,9})$/,
        message: lang.email_tip, type: 'warning' }
        ]">
        <t-input :placeholder="lang.input+lang.email" v-model="formData.email" @change="cancelPhone"></t-input>
        <p class="tip">{{lang.user_tip}}</p>
      </t-form-item>
      <t-form-item :label="lang.password" name="password">
        <t-input :placeholder="lang.input+lang.password" type="password" v-model="formData.password" />
      </t-form-item>
      <t-form-item :label="lang.surePassword" name="repassword">
        <t-input :placeholder="lang.input+lang.surePassword" type="password" v-model="formData.repassword" />
      </t-form-item>
      <div class="com-f-btn">
        <t-button theme="primary" type="submit">{{lang.hold}}</t-button>
        <t-button theme="default" variant="base" @click="close">{{lang.cancel}}</t-button>
      </div>
    </t-form>
  </t-dialog> -->

  <!-- 卸载/安装 -->
  <t-dialog theme="warning" :header="installTip" :visible.sync="delVisible">
    <template slot="footer">
      <t-button theme="primary" @click="sureDel">{{lang.sure}}</t-button>
      <t-button theme="default" @click="cancelDel">{{lang.cancel}}</t-button>
    </template>
  </t-dialog>

  <!-- 启用/停用 -->
  <t-dialog theme="warning" :header="statusTip" :visible.sync="statusVisble">
    <template slot="footer">
      <t-button theme="primary" @click="sureChange">{{lang.sure}}</t-button>
      <t-button theme="default" @click="closeDialog">{{lang.cancel}}</t-button>
    </template>
  </t-dialog>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/addon.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/addon.js"></script>
{include file="footer"}