{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<div id="content" class="transaction table" v-cloak>
  <t-card class="list-card-container">
    <div class="common-header">
      <t-button @click="addFlow" class="add">{{lang.new_flow}}</t-button>
      <div class="com-search">
        <t-input v-model="params.keywords" class="search-input" :placeholder="`${lang.please_search}${lang.flow_number}、${lang.order}ID、${lang.username}、${lang.email}、${lang.phone}`" @keyup.enter.native="seacrh" :on-clear="clearSearch" clearable>
        </t-input>
        <t-icon size="20px" name="search" @click="seacrh" class="com-search-btn" />
      </div>
    </div>
    <t-table row-key="1" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange" :hide-sort-tips="true" :max-height="maxHeight">
      <template slot="sortIcon">
        <t-icon name="caret-down-small"></t-icon>
      </template>
      <template #client_name="{row}">
        <a :href="`client_detail.html?id=${row.client_id}`" class="aHover">
          <span>{{row.client_name}}</span>
          <span v-if="row.phone">(+{{row.phone_code}}-{{row.phone}})</span>
          <span v-else>({{row.email}})</span>
        </a>
      </template>
      <template #amount="{row}">
        {{currency_prefix}}&nbsp;{{row.amount}}<span v-if="row.billing_cycle">/</span>{{row.billing_cycle}}
      </template>
      <template #order_id="{row}">
        <span v-if="row.order_id" class="aHover" @click="rowClick(row)">{{row.order_id}}</span> 
      </template>
      <template #create_time="{row}">
        <span>{{moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm')}}</span>
      </template>
      <template #hosts="{row}">
        <!-- :href="`host_detail.html?client_id=${row.client_id}&id=${item.id}`"  -->
        <span v-for="(item,index) in row.hosts" class="aHover" @click="rowClick(row)">
          {{item.name}}
          <span v-if="row.hosts.length>1 && index !== row.hosts.length - 1">、</span>
        </span>
      </template>
      <template #op="{row}">
        <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
          <t-icon name="delete" size="18px" @click="delteFlow(row)" class="common-look"></t-icon>
        </t-tooltip>
      </template>
    </t-table>
    <t-pagination v-if="total" :total="total" :page-size="params.limit" :current="params.page" :page-size-options="pageSizeOptions" :on-change="changePage" />
  </t-card>
  <!-- 新增流水 -->
  <t-dialog :header="lang.new_flow" :visible.sync="flowModel" :footer="false">
    <t-form :data="formData" ref="form" @submit="onSubmit" :rules="rules">
      <t-form-item :label="lang.money" name="amount">
        <t-input v-model="formData.amount" type="tel" :label="currency_prefix" :placeholder="lang.input+lang.money"></t-input>
      </t-form-item>
      <t-form-item :label="lang.pay_way" name="gateway">
        <t-select v-model="formData.gateway" :placeholder="lang.select+lang.pay_way">
          <t-option v-for="item in payList" :value="item.name" :label="item.title" :key="item.name">
          </t-option>
        </t-select>
      </t-form-item>
      <t-form-item :label="lang.flow_number" name="transaction_number">
        <t-input v-model="formData.transaction_number" :placeholder="lang.input+lang.flow_number"></t-input>
      </t-form-item>
      <t-form-item :label="lang.user" name="client_id" class="user">
        <t-select v-model="formData.client_id" filterable :placeholder="lang.example" :loading="searchLoading" reserve-keyword :on-search="remoteMethod" clearable @clear="clearKey" :show-arrow="false">
          <t-option v-for="item in userList" :value="item.id" :label="item.username" :key="item.id" class="com-custom">
            <div>
              <p>{{item.username}}</p>
              <p v-if="item.phone" class="tel">+{{item.phone_code}}-{{item.phone}}</p>
              <p v-else class="tel">{{item.email}}</p>
            </div>
          </t-option>
        </t-select>
      </t-form-item>
      <div class="com-f-btn">
        <t-button theme="primary" type="submit" :loading="addLoading">{{lang.submit}}
        </t-button>
        <t-button theme="default" variant="base" @click="flowModel=false">{{lang.cancel}}</t-button>
      </div>
    </t-form>
  </t-dialog>
  <!-- 删除流水提示框 -->
  <t-dialog theme="warning" :header="lang.sureDelete" :close-btn="false" :visible.sync="delVisible">
    <template slot="footer">
      <t-button theme="primary" @click="sureDelUser">{{lang.sure}}</t-button>
      <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
    </template>
  </t-dialog>
  <!-- 交易流水详情 -->
  <t-dialog :header="lang.flow_detail" :visible.sync="orderVisible" :footer="false" width="1000">
    <t-enhanced-table ref="tableDialog" row-key="id" :data="orderDetail" :columns="orderColumns" :tree="{ childrenKey: 'items', treeNodeColumnIndex: 0}" :loading="detailLoading" :tree-expand-and-fold-icon="treeExpandAndFoldIconRender" class="user-order" :expandAll="true">
      <template #id="{row}">
        <span v-if="row.type">{{row.id}}</span>
        <!-- <span v-else class="child">-</span> -->
      </template>
      <template #type="{row}">
        {{lang[row.type]}}
      </template>
      <template #create_time="{row}">
        {{row.type ? moment(row.create_time * 1000).format('YYYY/MM/DD HH:mm') : ''}}
      </template>
      <template #product_names={row}>
        <div v-if="row.type">
          <span>{{row.product_names[0]}}</span>
          <span v-if="row.product_names.length>1">、{{row.product_names[1]}}</span>
          <span v-if="row.product_names.length>2">等{{row.product_names.length}}个产品</span>
        </div>
        <div v-else>
          <span>{{row.product_name || row.description}}</span>
        </div>
      </template>
      <template #amount="{row}">
        {{currency_prefix}}&nbsp;{{row.amount}}<span v-if="row.billing_cycle">/</span>{{row.billing_cycle}}
      </template>
      <template #status="{row}">
        <t-tag theme="warning" variant="light" v-if="(row.status || row.host_status)==='Unpaid'">{{lang.Unpaid}}
        </t-tag>
        <t-tag theme="primary" variant="light" v-if="row.status==='Paid'">{{lang.Paid}}
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
        <!-- 其他支付方式 -->
        <template v-if="row.credit == 0 && row.amount !=0">
          {{row.gateway}}
        </template>
        <!-- 混合支付 -->
        <template v-if="row.credit>0 && row.credit < row.amount">
          <t-tooltip :content="currency_prefix+row.credit" theme="light" placement="bottom-right">
            <span>{{lang.credit}}</span>
          </t-tooltip>
          <span>+{{row.gateway}}</span>
        </template>
        <template v-if="row.credit==row.amount">
          <t-tooltip :content="currency_prefix+row.credit" theme="light" placement="bottom-right">
            <span>{{lang.credit}}</span>
          </t-tooltip>
        </template>
      </template>
    </t-enhanced-table>
  </t-dialog>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/transaction.js"></script>
{include file="footer"}