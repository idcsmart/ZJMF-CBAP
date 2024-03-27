{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<!-- =======内容区域======= -->
<div id="content" class="host" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <t-tabs theme="card" v-model="params.tab" theme="card" class="host-tab" @change="changeHostTab">
        <t-tab-panel value="using" :label="lang.host_using"></t-tab-panel>
        <t-tab-panel value="expiring" :label="`${lang.host_expiring}(${expiring_count})`"></t-tab-panel>
        <t-tab-panel value="overdue" :label="lang.host_overdue"></t-tab-panel>
        <t-tab-panel value="deleted" :label="lang.host_deleted"></t-tab-panel>
        <t-tab-panel value="" :label="lang.auth_all"></t-tab-panel>
      </t-tabs>
      <div class="common-header">
        <div></div>
        <div class="right-search">
          <div class="flex" v-show="!isAdvance">
              <t-select v-model="searchType" class="com-list-type" @change="changeType">
                <t-option v-for="item in typeOption" :value="item.value" :label="item.label" :key="item.value"></t-option>
              </t-select>
              <t-input v-model="params.keywords" class="search-input" :placeholder="lang.input"
            @keyup.native.enter="seacrh" clearable v-show="searchType !== 'product_id'">
            </t-input>
            <com-tree-select v-show="searchType === 'product_id'" :value="params.product_id" @choosepro="choosePro"></com-tree-select>
            <t-button @click="seacrh">{{lang.query}}</t-button>
          </div>
          <t-button @click="changeAdvance" style="margin-left: 20px;">{{isAdvance ? lang.pack_up : lang.advanced_filter}}</t-button>
        </div>
      </div>
      <div class="advanced" v-show="isAdvance">
        <div class="search">
          <t-select v-model="searchType" class="com-list-type"  @change="changeType">
            <t-option v-for="item in typeOption" :value="item.value" :label="item.label" :key="item.value"></t-option>
          </t-select>
          <t-input v-model="params.keywords" class="search-input" :placeholder="lang.input"
           @keyup.native.enter="seacrh" clearable v-show="searchType !== 'product_id'">
          </t-input>
          <com-tree-select class="search-input" v-show="searchType === 'product_id'" :value="params.product_id" @choosepro="choosePro"></com-tree-select>
          <t-input v-model="params.username" class="search-input" :placeholder="`${lang.input}${lang.username}`" @keyup.native.enter="seacrh" clearable>
          </t-input>
          <t-input v-model="params.billing_cycle" class="search-input" :placeholder="`${lang.input}${lang.payment_cycle}`" @keyup.native.enter="seacrh" clearable>
          </t-input>
          <t-select v-model="params.status" :placeholder="lang.client_care_label29" clearable v-show="params.tab === 'using' || params.tab === 'overdue' || params.tab === ''">
            <t-option v-for="item in calcStatus(productStatus)" :value="item.value" :label="item.label" :key="item.value">
            </t-option>
          </t-select>
          <t-select v-model="params.server_id" :placeholder="lang.interface" clearable >
            <t-option v-for="item in serverList" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
          <t-date-range-picker allow-input clearable v-model="range" :placeholder="[`${lang.due_time}`,`${lang.due_time}`]"></t-date-range-picker>
          <t-button @click="seacrh">{{lang.query}}</t-button>
        </div>
      </div>
      <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" :hide-sort-tips="true" @sort-change="sortChange">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #client_id="{row}">
          <a :href="`client_detail.htm?client_id=${row?.client_id}`" class="aHover">
            <template>
              <span v-if="row.client_name">{{row.client_name}}</span>
              <span v-else-if="row.phone">+{{row.phone_code}}-{{row.phone}}</span>
              <span v-else="row.email">{{row.email}}</span>
            </template>
            <span v-if="row.company">({{row.company}})</span>
          </a>
        </template>
        <template #renew_amount="{row}">
          <template v-if="row.billing_cycle">
            {{currency_prefix}}&nbsp;{{row.renew_amount}}<span>/</span>{{calcCycle(row.billing_cycle)}}
          </template>
          <template v-else>
            {{currency_prefix}}&nbsp;{{row.first_payment_amount}}/{{lang.onetime}}
          </template>
        </template>
        <template #product_name="{row}">
          <span class="aHover" @click="goHostDetail(row)" v-if="$checkPermission('auth_business_host_check_host_detail')">{{row.product_name}}</span>
          <span v-else>{{row.product_name}}</span>
          <t-tag theme="default" variant="light" v-if="row.status==='Cancelled'" class="canceled">{{lang.canceled}}</t-tag>
          <t-tag theme="warning" variant="light" v-if="row.status==='Unpaid'">{{lang.Unpaid}}</t-tag>
          <t-tag theme="primary" variant="light" v-if="row.status==='Pending'">{{lang.Pending}}</t-tag>
          <t-tag theme="success" variant="light" v-if="row.status==='Active'">{{lang.Active}}</t-tag>
          <t-tag theme="danger" variant="light" v-if="row.status==='Failed'">{{lang.Failed}}</t-tag>
          <t-tag theme="default" variant="light" v-if="row.status==='Suspended'">{{lang.Suspended}}</t-tag>
          <t-tag theme="default" variant="light" v-if="row.status==='Deleted'" class="delted">{{lang.Deleted}}
          </t-tag>
        </template>
        <template #name="{row}">
          {{row.name}}
          <span v-if="row.ip_num > 1 && $checkPermission('auth_business_host_check_host_detail')" class="showIp" @click="showIp(row.id)">({{row.ip_num}})</span>
          <span v-if="row.ip_num > 1 && !$checkPermission('auth_business_host_check_host_detail')" class="showIp" style="cursor: inherit;">({{row.ip_num}})</span>
        </template>
        <template #id="{row}">
          <span class="aHover" @click="goHostDetail(row)" v-if="$checkPermission('auth_business_host_check_host_detail')">{{row.id}}</span>
          <span v-else>{{row.id}}</span>
        </template>
        <template #active_time="{row}">
          <span>{{row.active_time ===0 ? '-' : moment(row.active_time * 1000).format('YYYY/MM/DD HH:mm')}}</span>
        </template>
        <template #due_time="{row}">
          <span>{{row?.due_time ===0 ? '-' : moment(row?.due_time * 1000).format('YYYY/MM/DD HH:mm')}}</span>
        </template>
        <template #op="{row}">
          <a class="common-look" @click="deltePro(row)">{{lang.delete}}</a>
        </template>
      </t-table>
      <t-pagination show-jumper :total="total" v-if="total" :current="params.page" :page-size="params.limit" :page-size-options="pageSizeOptions" :on-change="changePage" />
    </t-card>
    <!-- 所有IP -->
    <t-dialog :header="lang.finance_search_text23" @close="ipLoading = false" :footer="false" :visible.sync="ipLoading" class="ip-dialog">
      <div class="ips">
        <p v-for="(item,index) in allIp" :key="index">{{item}}</p>
      </div>
    </t-dialog>
  </com-config>
</div>
<script src="/{$template_catalog}/template/{$themes}/components/comTreeSelect/comTreeSelect.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/host.js"></script>
{include file="footer"}
