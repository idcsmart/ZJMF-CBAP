{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/manage.css">
<div id="content" class="log-notice-sms table" v-cloak>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li>
        <a href="log_system.html">{{lang.system_log}}</a>
      </li>
      <li class="active">
        <a href="javascript:;">{{lang.notice_log}}</a>
      </li>
    </ul>
    <div class="common-header">
      <div class="left">
        <t-button>{{lang.sms_notice}}</t-button>
        <t-button theme="default" @click="jump">{{lang.email_notice}}</t-button>
      </div>
      <div class="com-search">
        <t-input v-model="params.keywords" class="search-input" :placeholder="`${lang.please_search}${lang.description}`" @keyup.enter.native="seacrh" :on-clear="clearKey" clearable>
        </t-input>
        <t-icon size="20px" name="search" @click="seacrh" class="com-search-btn" />
      </div>
    </div>
    <t-table row-key="id" :data="data" size="medium" :hide-sort-tips="true" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange" :max-height="maxHeight">
      <template slot="sortIcon">
        <t-icon name="caret-down-small"></t-icon>
      </template>
      <template #content="{row}">
        <t-icon v-if="row.status === 1" name="check-circle-filled" style="color:#00a870;"></t-icon>
        <template v-else>
          <t-tooltip :content="row.fail_reason" theme="light" :show-arrow="false">
            <t-icon name="close-circle-filled" class="icon-error" style="color: #e34d59;"></t-icon>
          </t-tooltip>
        </template>
        {{row.content}}
      </template>
      <template #create_time="{row}">
        {{moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm')}}
      </template>
      <template #phone="{row}">
        +{{row.phone_code}}&nbsp;-&nbsp;{{row.phone}}
      </template>
    </t-table>
    <t-pagination v-if="total" :total="total" :page-size="params.limit" :page-size-options="pageSizeOptions" :on-change="changePage" :current="params.page" />
  </t-card>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/manage.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/log_notice_sms.js"></script>
{include file="footer"}