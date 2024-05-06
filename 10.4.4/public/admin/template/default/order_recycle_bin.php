{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<div id="content" class="order order-recyle" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <div class="common-header recyle-top">
        <div class="left">
          <span class="tit">{{lang.order_recycle_bin}}</span>
          <span class="back" @click="backOrder">
            <<{{lang.back}} </span>
        </div>
        <div class="right-search">
          <t-button class="add" @click="handleConfig" v-permission="'auth_business_order_recycle_bin_config'">{{lang.recycle_setting}}</t-button>
          <t-button class="add" @click="handleRecyle('clear')" :disabled="data.length === 0" v-permission="'auth_business_order_recycle_bin_clear'">{{lang.clear_recycle_bin}}</t-button>
        </div>
      </div>
      <div class="common-header">
        <div class="left"></div>
        <!-- 右侧搜索 -->
        <div class="right-search">
          <template v-if="!isAdvance">
            <t-date-range-picker allow-input clearable v-model="delRange" style="margin-right: 10px;" :placeholder="[`${lang.order_del_time}`,`${lang.order_del_time}`]">
            </t-date-range-picker>
            <t-select v-model="searchType" class="com-list-type" @change="changeType">
              <t-option v-for="item in typeOption" :value="item.value" :label="item.label" :key="item.value"></t-option>
            </t-select>
            <t-input v-model="params.keywords" class="search-input" :placeholder="lang.input" @keyup.native.enter="seacrh" clearable v-show="searchType !== 'product_id'">
            </t-input>
            <com-tree-select class="search-input" v-show="searchType === 'product_id'" :value="params.product_id" @choosepro="choosePro"></com-tree-select>
            <t-input v-model="params.username" class="search-input" :placeholder="`${lang.input}${lang.username}`"
            @keyup.native.enter="seacrh" clearable style="margin-right: 0;">
            </t-input>
            <t-button @click="seacrh" class="seacrh">{{lang.query}}</t-button>
          </template>
          <t-button @click="changeAdvance">{{isAdvance ? lang.pack_up : lang.advanced_filter}}</t-button>
        </div>
      </div>
      <!-- 高级搜索 -->
      <div class="advanced" v-show="isAdvance">
        <div class="search">
          <t-select v-model="searchType" class="com-list-type" @change="changeType">
            <t-option v-for="item in typeOption" :value="item.value" :label="item.label" :key="item.value"></t-option>
          </t-select>
          <t-input v-model="params.keywords" class="search-input" :placeholder="lang.input" @keyup.native.enter="seacrh" clearable v-show="searchType !== 'product_id'">
          </t-input>
          <com-tree-select class="search-input" v-show="searchType === 'product_id'" :value="params.product_id" @choosepro="choosePro"></com-tree-select>
          <t-input v-model="params.username" class="search-input" :placeholder="`${lang.input}${lang.username}`" @keyup.native.enter="seacrh" clearable>
          </t-input>
          <t-input :placeholder="lang.money" v-model="params.amount" @keyup.enter.native="seacrh"></t-input>
          <t-date-range-picker allow-input clearable v-model="range" :placeholder="[`${lang.order_date}`,`${lang.order_date}`]">
          </t-date-range-picker>
          <t-select v-model="params.type" :placeholder="lang.order_type" clearable>
            <t-option v-for="item in orderTypes" :value="item.value" :label="item.label" :key="item.value">
            </t-option>
          </t-select>
          <t-date-range-picker allow-input clearable v-model="delRange" :placeholder="[`${lang.order_del_time}`,`${lang.order_del_time}`]"
          style="margin-left: 0;">
          </t-date-range-picker>
          <t-select v-model="params.gateway" :placeholder="lang.pay_way" clearable>
            <t-option value="Credit" :label="lang.balance_pay" key="credit"></t-option>
            <t-option v-for="item in payWays" :value="item.name" :label="item.title" :key="item.name">
            </t-option>
          </t-select>
        </div>
        <t-button @click="seacrh">{{lang.query}}</t-button>
      </div>
      <!-- 高级搜索 end -->
      <t-enhanced-table ref="table" row-key="id" drag-sort="row-handler" :data="data" :columns="columns" :tree="{ childrenKey: 'list', treeNodeColumnIndex: 0 }" :loading="loading" :hover="hover" :tree-expand-and-fold-icon="treeExpandAndFoldIconRender" @sort-change="sortChange" class="user-order" :hide-sort-tips="true" @select-change="rehandleSelectChange" :selected-row-keys="checkId">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #id="{row}">
          <span @click="lookDetail(row)" v-if="row.type && $checkPermission('auth_business_order_check_order')" class="aHover">
            {{row.id}}
          </span>
          <span v-if="row.type && !$checkPermission('auth_business_order_check_order')">{{row.id}}</span>
          <span v-if="!row.type" class="child">-</span>
        </template>
        <template #type="{row}">
          {{lang[row.type]}}
        </template>
        <template #client_name="{row}">
          <a :href="`client_detail.htm?client_id=${row.client_id}`" class="aHover">
            <t-tooltip :content="lang.recycle_tip14" theme="light" :show-arrow="false" placement="top-left">
              <svg class="common-look lock" v-if="row.is_lock">
                <use :xlink:href="`${rootRul}img/icon/icons.svg#cus-lock`">
                </use>
              </svg>
            </t-tooltip>
            <template>
              <span v-if="row.client_name">{{row.client_name}}</span>
              <span v-else-if="row.phone">+{{row.phone_code}}-{{row.phone}}</span>
              <span v-else="row.email">{{row.email}}</span>
            </template>
            <span v-if="row.company">({{row.company}})</span>
          </a>
        </template>
        <template #create_time="{row}">
          {{row.type ? moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm') : ''}}
        </template>
        <template #recycle_time="{row}">
          {{row.type ? moment(row.recycle_time * 1000).format('YYYY-MM-DD HH:mm') : ''}}
        </template>
        <template #will_delete_time="{row}">
          {{calcDeleteTime(row.will_delete_time)}}
        </template>
        <template #icon="{row}">
          <template v-if="row.type">
            <t-tooltip :content="lang[row.type]" theme="light" :show-arrow="false" placement="top-right">
              <img :src="`${rootRul}img/icon/${row.type}.png`" alt="" style="position: relative; top: 3px;">
            </t-tooltip>
          </template>
        </template>
        <template #product_names={row}>
          <template v-if="row.product_names">
            <template v-if="row.description && $checkPermission('auth_business_order_check_order')">
              <t-tooltip theme="light" :show-arrow="false" placement="top-right">
                <div slot="content" class="tool-content">{{row.description}}</div>
                <!--  <span @click="itemClick(row)" class="hover">{{row.product_names[0]}}</span> -->
                <span class="aHover" @click="lookDetail(row)" v-if="$checkPermission('auth_business_order_check_order')">{{row.product_names[0]}}</span>
                <span v-else>{{row.product_names[0]}}</span>
                <span v-if="row.product_names.length>1 && $checkPermission('auth_business_order_check_order')" @click="lookDetail(row)" class="hover">{{lang.wait}}{{row.product_names.length}}{{lang.products}}</span>
                <span v-if="row.product_names.length>1 && !$checkPermission('auth_business_order_check_order')">{{lang.wait}}{{row.product_names.length}}{{lang.products}}</span>
              </t-tooltip>
            </template>
            <template v-else>
              <!--  @click="itemClick(row)" -->
              <span class="aHover" @click="lookDetail(row)" v-if="$checkPermission('auth_business_order_check_order')">{{row.product_names[0]}}</span>
              <span v-else>{{row.product_names[0]}}</span>
              <span v-if="row.product_names.length>1 && $checkPermission('auth_business_order_check_order')" @click="lookDetail(row)" class="hover">{{lang.wait}}{{row.product_names.length}}{{lang.products}}</span>
              <span v-if="row.product_names.length>1 && !$checkPermission('auth_business_order_check_order')">{{lang.wait}}{{row.product_names.length}}{{lang.products}}</span>
            </template>
          </template>
          <span v-else class="child-name">
            <t-tooltip theme="light" :show-arrow="false" placement="top-right">
              <div slot="content" class="tool-content">{{row.description}}</div>
              <a :href="row.host_id ? `host_detail.htm?client_id=${father_client_id}&id=${row.host_id}` : 'javascript:;'" class="aHover">{{row.product_name ? row.product_name : row.description}}
                <span class="host-name" v-if="row.host_name">({{row.host_name}})</span>
              </a>
            </t-tooltip>
          </span>
        </template>
        <template #amount="{row}">
          {{currency_prefix}}&nbsp;{{row.amount}}
          <!-- 升降机为退款时不显示周期 -->
          <span v-if="row.billing_cycle && Number(row.amount) >= 0 && row.type!=='upgrade'">/{{row.billing_cycle}}</span>
        </template>
        <template #status="{row}">
          <t-tag theme="default" variant="light" v-if="(row.status || row.host_status)==='Cancelled'" class="canceled order-canceled">{{lang.canceled}}
          </t-tag>
          <t-tag theme="default" variant="light" v-if="(row.status || row.host_status)==='Refunded'" class="canceled order-refunded">{{lang.refunded}}
          </t-tag>
          <t-tag theme="warning" variant="light" v-if="(row.status || row.host_status)==='Unpaid'" class="order-unpaid">{{lang.Unpaid}}
          </t-tag>
          <t-tag theme="primary" variant="light" v-if="row.status==='Paid'" class="order-paid">{{lang.Paid}}
          </t-tag>
          <t-tag theme="primary" variant="light" v-if="row.host_status === 'Pending'">
            {{lang.Pending}}
          </t-tag>
          <t-tag theme="success" variant="light" v-if="(row.status || row.host_status)==='Active'">{{lang.Active}}
          </t-tag>
          <t-tag theme="danger" variant="light" v-if="(row.status || row.host_status)==='Failed'">{{lang.Failed}}
          </t-tag>
          <t-tag theme="default" variant="light" v-if="(row.status || row.host_status)==='Suspended'">
            {{lang.Suspended}}
          </t-tag>
          <t-tag theme="default" variant="light" v-if="(row.status || row.host_status)==='Deleted'" class="delted">{{lang.Deleted}}
          </t-tag>
        </template>
        <template #gateway="{row}">
          <template v-if="row.status === 'Unpaid'">
            --
          </template>
          <template v-else>
            <!-- 其他支付方式 -->
            <template v-if="row.credit == 0">
              {{row.gateway}}
            </template>
            <!-- 混合支付 -->
            <template v-if="row.credit * 1 >0 && row.credit * 1 < row.amount * 1">
              <t-tooltip :content="currency_prefix+row.credit" theme="light" placement="bottom-right">
                <span class="theme-color">{{lang.balance_pay}}</span>
              </t-tooltip>
              <span>{{row.gateway ? '+ ' + row.gateway: '' }}</span>
            </template>
            <template v-if="row.amount*1 != 0 && row.credit==row.amount">
              <span>{{lang.balance_pay}}</span>
            </template>
          </template>
        </template>
        <template #op="{row}">
          <t-tooltip :content="`${lang.look}${lang.detail}`" :show-arrow="false" theme="light">
            <t-icon name="view-module" class="common-look" @click="lookDetail(row)" v-permission="'auth_business_order_recycle_bin_order_detail'"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.order_restore" :show-arrow="false" theme="light">
            <svg class="common-look" @click="handleRecyle('restore', false, row)" v-permission="'auth_business_order_recycle_bin_recover_order'">
              <use :xlink:href="`${rootRul}img/icon/icons.svg#cus-recover`">
              </use>
            </svg>
          </t-tooltip>
          <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
            <t-icon name="delete" class="common-look" @click="handleRecyle('delete', false, row)" v-permission="'auth_business_order_recycle_bin_delete_order'"></t-icon>
          </t-tooltip>
        </template>
      </t-enhanced-table>
      <t-pagination show-jumper v-if="total" :total="total" :page-size="params.limit" :current="params.page" :page-size-options="pageSizeOptions" :on-change="changePage">
      </t-pagination>

      <!-- bot-opt -->
      <div class="bot-opt">
        <t-button @click="handleRecyle('restore', true)" class="seacrh" v-permission="'auth_business_order_recycle_bin_recover_order'">{{lang.batch_restore}}</t-button>
        <t-button @click="handleRecyle('delete', true)" class="seacrh" v-permission="'auth_business_order_recycle_bin_delete_order'">{{lang.batch_delete}}</t-button>
        <t-button @click="handleRecyle('lock', true)" class="seacrh" v-permission="'auth_business_order_recycle_bin_lock_order'">{{lang.order_lock}}</t-button>
        <t-button @click="handleRecyle('unlock', true)" class="seacrh" v-permission="'auth_business_order_recycle_bin_unlock_order'">{{lang.order_unlock}}</t-button>
      </div>
    </t-card>

    <!-- 清空 | 恢复 | 删除 -->
    <t-dialog :header="optTitle" :visible.sync="recycleVisble" :footer="false" class="recycle-dialog">
      <div class="con">
        <div :class="`icon ${recycleType}`">
          <svg>
            <use :xlink:href="`${rootRul}img/icon/icons.svg#cus-${recycleType}`"></use>
          </svg>
        </div>
        <div class="text">
          <p class="tit">{{optTit}}</p>
          <p class="des">{{optDes}}</p>
        </div>
      </div>
      <div class="com-f-btn">
        <t-button theme="primary" :loading="submitLoading" @click="submitRecyle">{{lang.sure}}</t-button>
        <t-button theme="default" variant="base" @click="recycleVisble=false">{{lang.cancel}}</t-button>
      </div>
    </t-dialog>

    <!-- 回收设置 -->
    <t-dialog :header="lang.recycle_setting" :visible.sync="settingVisble" :footer="false" :width="isEn ? 700 : 480" class="recycleDialog">
      <t-form :data="recycleForm" ref="update_price" @submit="submitConfig" :rules="rules">
        <t-form-item :label="lang.recycle_function">
          <t-switch size="medium" :custom-value="[1,0]" v-model="recycleForm.order_recycle_bin">
          </t-switch>
        </t-form-item>
        <t-form-item :label="lang.storage_time" class="days">
          <span>{{lang.order_exceed}}</span>
          <t-input-number theme="normal" :min="0" :decimal-places="0" :max="999" v-model="recycleForm.order_recycle_bin_save_days" @blur="changeDays">
          </t-input-number>
          <span>{{lang.recycle_tip3}}</span>
        </t-form-item>
        <t-form-item label=" " class="empty-item">
          <span class="dis">{{lang.order_exceed}}</span>
          <span>{{lang.recycle_tip4}}</span>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="settingVisble=false">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>

    <!-- 锁定 | 解锁 -->
    <t-dialog theme="warning" :header="optTitle" :visible.sync="delVisible">
      <template slot="footer">
        <t-button theme="primary" @click="hanleStatus" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
  </com-config>
</div>
<script src="/{$template_catalog}/template/{$themes}/components/comTreeSelect/comTreeSelect.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/order_recycle_bin.js"></script>
{include file="footer"}
