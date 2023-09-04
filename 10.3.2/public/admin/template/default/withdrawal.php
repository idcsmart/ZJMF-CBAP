{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/withdrawal.css">
<div id="content" class="withdrawal table hasCrumb" v-cloak>
  <!-- crumb -->
  <div class="com-crumb">
    <span>{{lang.plugin}}</span>
    <t-icon name="chevron-right"></t-icon>
    <a href="#">{{lang.user_text8}}</a>
    <t-icon name="chevron-right"></t-icon>
    <span class="cur">{{lang.refund_apply_list}}</span>
  </div>
  <t-card class="list-card-container stop">
    <div class="com-h-box">
      <ul class="common-tab">
        <li class="active">
          <a>{{lang.refund_apply_list}}</a>
        </li>
        <li>
          <a href="#">{{lang.withdrawal_manage}}</a>
        </li>
      </ul>
    </div>
    <!-- 主体 -->
    <div class="box scrollbar">
      <!-- 搜索框开始 -->
      <div class="top-search">
        <div class="input-search">
          <t-input v-model="params.keywords" class="search-input" :placeholder="lang.search_placeholder" @keyup.enter.native="seacrh" :on-clear="clearKey" clearable>
            <t-icon size="20px" name="search" slot="suffix" @click="seacrh" class="com-search-btn" />
          </t-input>
        </div>
        <t-select class="select-search"></t-select>
      </div>
      <!-- 搜索框结束 -->
      <!-- 表格开始 -->
      <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="true" :loading="loading" table-layout="fixed" display-type="fixed-width" :hide-sort-tips="true">
        <template #id="{row}">
          <span>{{row.id}}</span>
        </template>

      </t-table>
      <!-- 表格结束 -->
      <!-- 分页开始 -->
      <t-pagination :total="total" :page-size="params.limit" :current="params.page" :page-size-options="pageSizeOptions" @change="changePage" />
      <!-- 分页结束 -->
    </div>

  </t-card>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/withdrawal.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/withdrawal.js"></script>
{include file="footer"}