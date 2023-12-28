{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/system.css">
<div id="content" class="template feedback" v-cloak>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li>
        <a href="template.htm">{{lang.feedback}}</a>
      </li>
      <li class="active">
        <a href="javascript:;">{{lang.guidance}}</a>
      </li>
    </ul>
    <div class="box">
      <t-table row-key="id" :data="list" size="medium" :columns="typeColumns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" display-type="fixed-width" :hide-sort-tips="true">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #username="{row}">
          {{row.username || '--'}}
        </template>
        <template #company="{row}">
          {{row.company || '--'}}
        </template>
        <template #phone="{row}">
          {{row.phone || '--'}}
        </template>
        <template #email="{row}">
          {{row.email || '--'}}
        </template>
      </t-table>
      <t-pagination show-jumper :total="total" v-if="total" :page-size="params.limit" :page-size-options="pageSizeOptions" :on-change="changePage" :current="params.page" />
    </div>
  </t-card>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/system.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/consult.js"></script>
{include file="footer"}
