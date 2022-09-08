{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="captcha" v-cloak>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li>
        <a :href="`configuration_security.html`">{{lang.captcha_setting}}</a>
      </li>
      <li class="active">
        <a href="javascript:;">{{lang.captcha_manage}}</a>
      </li>
    </ul>
    <div class="common-header">
      <t-button @click="addUser" class="add">{{lang.get_more}}</t-button>
      <!-- <div class="com-search">
                <t-input v-model="params.keywords" class="search-input" 
                  :placeholder="`${lang.please_search}ID`" 
                  @keyup.enter.native="seacrh" :on-clear="clearKey" clearable>
                </t-input>
                <t-icon size="20px" name="search" @click="seacrh" class="com-search-btn" />
              </div> -->
    </div>
    <t-table row-key="1" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" :hide-sort-tips="hideSortTips" :max-height="maxHeight">
      <template slot="sortIcon">
        <t-icon name="caret-down-small"></t-icon>
      </template>
      <template #host_active_num="{row}">
        {{row.host_num}}({{row.host_active_num}})
      </template>
      <template #phone="{row}">
        <span v-if="row.phone">+{{row.phone_code}}&nbsp;-&nbsp;{{row.phone}}</span>
      </template>
      <template #status="{row}">
        <t-tag theme="success" class="status" v-if="row.status===1" variant="light">{{lang.enable}}</t-tag>
        <t-tag theme="warning" class="status" v-if="row.status===0" variant="light">{{lang.disable}}</t-tag>
        <t-tag theme="default" class="status" v-if="row.status===3" variant="light">{{lang.not_install}}</t-tag>
      </template>
      <template #op="{row}">
        <t-tooltip :content="lang.apply_interface" :show-arrow="false" theme="light">
          <a :href="row.help_url" v-if="row.help_url">
            <t-icon name="link" class="common-look"></t-icon>
          </a>
        </t-tooltip>
        <t-tooltip :content="lang.config" :show-arrow="false" theme="light" v-if="row.status !== 3">
          <t-icon name="tools" class="common-look" @click="handleConfig(row)"></t-icon>
        </t-tooltip>
        <t-tooltip :content="row.status==1 ? lang.disable : lang.enable" :show-arrow="false" theme="light">
          <t-icon :name="row.status==1 ? 'minus-circle' : 'play-circle-stroke'" class="common-look" :class="{rotate: row.status== 1,'no-allow': row.name === 'TpCaptcha'}" @click="changeStatus(row)" v-if="row.status !== 3">
          </t-icon>
        </t-tooltip>
        <t-tooltip :content="row.status !== 3 ? lang.uninstall : lang.install" :show-arrow="false" theme="light">
          <a class="common-look" @click="deletePay(row)" :class="{'no-allow': row.name === 'TpCaptcha'}">
            <img :src="`${urlPath}/img/icon/uninstall.png`" alt="" v-if="row.status !== 3">
            <img :src="`${urlPath}/img/icon/install.png`" alt="" v-else>
        </t-tooltip>
      </template>
    </t-table>
    <t-pagination :total="total" :page-size="params.limit" :page-size-options="pageSizeOptions" :on-change="changePage" :current="params.page" />
  </t-card>

  <!-- 配置弹窗 -->
  <t-dialog :header="configTip" :visible.sync="configVisble" :footer="false" width="650">
    <t-form :rules="rules" ref="userDialog" @submit="onSubmit" :label-width="120">
      <t-form-item :label="item.title" v-for="item in configData" :key="item.title">
        <!-- text -->
        <t-input v-if="item.type==='text'" v-model="item.value" :placeholder="item.tip ? item.tip : lang.input+item.title"></t-input>
        <!-- password -->
        <t-input v-if="item.type==='password'" type="password" v-model="item.value" :placeholder="item.tip ? item.tip :lang.input+item.title"></t-input>
        <!-- textarea -->
        <t-textarea v-if="item.type==='textarea'" v-model="item.value" :placeholder="item.tip ? item.tip :lang.input+item.title">
        </t-textarea>
        <!-- radio -->
        <t-radio-group v-if="item.type==='radio'" v-model="item.value" :options="computedRadio(item.options)">
        </t-radio-group>
        <!-- checkbox -->
        <t-checkbox-group v-if="item.type==='checkbox'" v-model="item.value" :options="item.options">
        </t-checkbox-group>
        <!-- select -->
        <t-select v-if="item.type==='select'" v-model="item.value" :placeholder="item.tip ? item.tip :lang.input+item.title">
          <t-option v-for="ele in item.options" :value="ele.value" :label="ele.label" :key="ele.value">
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
<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/captcha.js"></script>
{include file="footer"}