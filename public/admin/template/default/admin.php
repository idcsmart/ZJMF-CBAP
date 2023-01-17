{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="admin table" v-cloak>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li class="active">
        <a href="javascript:;">{{lang.admin_setting}}</a>
      </li>
      <li>
        <a href="admin_role.html">{{lang.group_setting}}</a>
      </li>
    </ul>
    <div class="common-header">
      <t-button @click="addUser" class="add">{{lang.add}}</t-button>
      <div class="com-search">
        <t-input v-model="params.keywords" class="search-input" :placeholder="`${lang.please_search}ID、${lang.nickname}、${lang.username}、${lang.email}`" @keyup.enter.native="seacrh" :on-clear="clearKey" clearable>
        </t-input>
        <t-icon size="20px" name="search" @click="seacrh" class="com-search-btn" />
      </div>
    </div>
    <t-table row-key="1" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange" :hide-sort-tips="hideSortTips" :max-height="maxHeight">
      <template slot="sortIcon">
        <t-icon name="caret-down-small"></t-icon>
      </template>
      <template #phone="{row}">
        <span v-if="row.phone">+{{row.phone_code}}&nbsp;-&nbsp;{{row.phone}}</span>
      </template>
      <template #status="{row}">
        <t-tag theme="success" class="status" v-if="row.status" variant="light">{{lang.enable}}</t-tag>
        <t-tag theme="danger" class="status" v-else variant="light">{{lang.disable}}</t-tag>
      </template>
      <template #op="{row}">
        <t-tooltip :content="row.status ? lang.disable : lang.enable" :show-arrow="false" theme="light">
          <t-icon :name="row.status ? 'minus-circle' : 'play-circle-stroke'" class="common-look" @click="changeStatus(row)" :class="{disable: row.id===1, rotate: row.status}">
          </t-icon>
        </t-tooltip>
        <t-tooltip :content="lang.update" :show-arrow="false" theme="light">
          <t-icon name="edit-1" class="common-look" @click="updateAdmin(row)">
          </t-icon>
        </t-tooltip>
        <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
          <t-icon name="delete" class="common-look" @click="deleteUser(row)" :class="{disable: row.id===1}">
          </t-icon>
        </t-tooltip>
      </template>
    </t-table>
    <t-pagination :total="total" :page-size="params.limit" :page-size-options="pageSizeOptions" :on-change="changePage" :current="params.page" />
  </t-card>

  <!-- 添加管理员 -->
  <t-dialog :visible.sync="visible" :header="addTip" :on-close="close" :footer="false" width="600">
    <t-form :rules="rules" :data="formData" ref="userDialog" @submit="onSubmit">
      <t-form-item :label="lang.username" name="name">
        <t-input :placeholder="lang.please_enter_name" v-model="formData.name" />
      </t-form-item>
      <t-form-item :label="lang.password" name="password" :rules="[
          { required: optType === 'create' ? true : false , message: lang.input + lang.password, type: 'error' },
          { pattern: /^[\w@!#$%^&*()+-_]{6,32}$/, message: lang.verify8 + '，' + lang.verify14 + '6~32', type: 'warning' }
        ]">
        <t-input :placeholder="lang.input+lang.password" type="password" v-model="formData.password" />
      </t-form-item>
      <t-form-item :label="lang.surePassword" name="repassword" :rules="[
      { required:  optType === 'create' ? true : false, message: lang.input + lang.surePassword, type: 'error' },
      { validator: checkPwd, trigger: 'blur' }
    ]">
        <t-input :placeholder="lang.input+lang.surePassword" type="password" v-model="formData.repassword" />
      </t-form-item>
      <t-form-item :label="lang.phone" name="phone">
        <t-select v-model="formData.phone_code" filterable style="width: 100px" :placeholder="lang.phone_code">
          <t-option v-for="item in country" :value="item.phone_code" :label="item.name_zh + '+' + item.phone_code" :key="item.name">
          </t-option>
        </t-select>
        <t-input :placeholder="lang.input+lang.phone" v-model="formData.phone" style="width: calc(100% - 100px);" />
      </t-form-item>
      <t-form-item :label="lang.email" name="email">
        <t-input :placeholder="lang.input+lang.email" v-model="formData.email" />
      </t-form-item>
      <t-form-item :label="lang.nickname" name="nickname">
        <t-input :placeholder="lang.input+lang.nickname" v-model="formData.nickname" />
      </t-form-item>
      <t-form-item :label="lang.group" name="role_id">
        <t-select v-model="formData.role_id" :placeholder="lang.select+lang.group" :disabled="formData.id===1" :popup-props="popupProps">
          <t-option v-for="item in roleList" :value="item.id" :label="item.name" :key="item.id">
          </t-option>
        </t-select>
      </t-form-item>
      <div class="f-btn">
        <t-button theme="primary" type="submit">{{lang.hold}}</t-button>
        <t-button theme="default" variant="base" @click="close">{{lang.cancel}}</t-button>
      </div>
    </t-form>
  </t-dialog>

  <!-- 删除弹窗 -->
  <t-dialog theme="warning" :header="lang.sureDelete" :visible.sync="delVisible">
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
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/admin.js"></script>
{include file="footer"}