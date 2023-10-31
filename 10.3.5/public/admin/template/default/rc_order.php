{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/rc_order.css">
<div id="content" class="re-order " v-cloak>
  <div class="top-card">
    <div class="com-h-box">
      <ul class="common-tab">
        <li class="active">
          <a>{{lang.caravan_text1}}</a>
        </li>
        <li>
          <a href="rc_order_config.htm">{{lang.caravan_text2}}</a>
        </li>
      </ul>
    </div>
    <div class="top-statistics">
      <div class="statistics-item">
        <div class="l">
          <span class="num">{{staticsticsData.sale_total}}</span>
          <span class="text">{{lang.caravan_text36}}({{currency_suffix}})</span>
        </div>
        <div class="r">
          <img :src="`${urlPath}/img/rc/statistics-01.png`" alt="">
        </div>
      </div>
      <div class="statistics-item">
        <div class="l">
          <span class="num">{{staticsticsData.arrived_total}}</span>
          <span class="text">{{lang.caravan_text37}}({{currency_suffix}})</span>
        </div>
        <div class="r">
          <img :src="`${urlPath}/img/rc/statistics-02.png`" alt="">
        </div>
      </div>
      <div class="statistics-item">
        <div class="l">
          <span class="num">{{staticsticsData.final_total}}</span>
          <span class="text">{{lang.caravan_text38}}({{currency_suffix}})</span>
        </div>
        <div class="r">
          <img :src="`${urlPath}/img/rc/statistics-03.png`" alt="">
        </div>
      </div>
    </div>
  </div>
  <t-card class="list-card-container table">
    <div class="top-search">
      <t-input clearable class="key-input" v-model="params.keywords" :placeholder="lang.caravan_text39">
      </t-input>
      <t-select clearable :loading="loading" :options="options" :on-search="remoteMethod" filterable class="client-select" v-model="params.client_id" :placeholder="lang.caravan_text40">
      </t-select>
      <t-button class="search-btn" @click="search">{{lang.caravan_text41}}</t-button>
    </div>
    <t-table row-key="id" :data="dataList" :loading="dataLoading" size="medium" :columns="columns" @row-click="rowClick">
      <template #buy_amount="{row}">
        {{currency_prefix}}{{row.buy_amount}}
      </template>
      <template #create_time="{row}">
        {{moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm')}}
      </template>
      <template #cycle_min="{row}">
        {{row.cycle_min}}-{{row.cycle_max}}{{lang.caravan_text9}}
        <span v-if="(row.status != 'Unpaid') && (row.status != 'Production') && (row.status != 'Ordered')">({{lang.caravan_text42}})</span>
      </template>
      <template #distribution="{row}">
        <div class="distribution" @click="stopPop">
          <div v-if="row.distribution" class="text" :title="row.distribution">{{row.distribution}}</div>
          <t-icon v-if="row.distribution" class="copy-icon" name="file-copy" @click="copyMsg(row.distribution)"></t-icon>
          <span v-if="!row.distribution">--</span>
        </div>
      </template>
      <template #status="{row}">
        <span v-if="row.status != 'Unpaid' && row.status != 'FinalUnpaid'" class="status" :class="row.status">{{stataus[row.status]}}</span>
        <span v-if="row.status == 'Unpaid'" class="status" :class="row.status">{{stataus[row.status]}}({{currency_prefix}}{{row.amount}})</span>
        <span v-if="row.status == 'FinalUnpaid'" class="status" :class="row.status">{{stataus[row.status]}}({{currency_prefix}}{{row.final_amount}})</span>
      </template>
      <template #operation="{row}">
        <div @click="stopPop">
          <t-tooltip :content="lang.caravan_text18" :show-arrow="false" theme="light">
            <img v-if="row.status == 'Ordered'" class="operation-icon" :src="`${urlPath}/img/rc/operation1.png`" alt="" @click="showProduction('production',row)">
          </t-tooltip>
          <t-tooltip :content="lang.caravan_text19" :show-arrow="false" theme="light">
            <img v-if="row.status == 'Production'" class="operation-icon" :src="`${urlPath}/img/rc/operation2.png`" alt="" @click="showSure('finish',row.id)">
          </t-tooltip>
          <t-tooltip :content="lang.caravan_text20" :show-arrow="false" theme="light">
            <img v-if="row.status == 'Production'" class="operation-icon" :src="`${urlPath}/img/rc/operation3.png`" alt="" @click="showProduction('edit',row)">
          </t-tooltip>
          <t-tooltip :content="lang.caravan_text21" :show-arrow="false" theme="light">
            <img v-if="row.status == 'Delivery'" class="operation-icon" :src="`${urlPath}/img/rc/operation4.png`" alt="" @click="showDelivery(row.id)">
          </t-tooltip>
          <t-tooltip :content="lang.caravan_text22" :show-arrow="false" theme="light">
            <img v-if="row.status == 'FinalUnpaid'" class="operation-icon" :src="`${urlPath}/img/rc/operation5.png`" alt="" @click="showSure('failPaid',row.id)">
          </t-tooltip>
          <t-tooltip :content="lang.caravan_text23" :show-arrow="false" theme="light">
            <img v-if="row.status == 'Unpaid'" class="operation-icon" :src="`${urlPath}/img/rc/operation6.png`" alt="" @click="showSure('paid',row.id)">
          </t-tooltip>
          <span v-if="row.status == 'Delivered' || row.status == 'Cancelled'">--</span>
        </div>
      </template>
    </t-table>
    <t-pagination show-jumper :total="total" :page-size="params.limit" :current="params.page" :page-size-options="pageSizeOptions" @change="changePage" />
  </t-card>

  <!-- 二次确认弹窗 -->
  <t-dialog theme="warning" :header="header" :visible.sync="visible">
    <template slot="footer">
      <t-button theme="primary" @click="sure">{{lang.sure}}</t-button>
      <t-button theme="default" @click="visible=false">{{lang.cancel}}</t-button>
    </template>
  </t-dialog>

  <!-- 开始生产弹窗 -->
  <t-dialog :header="productionHead" :visible.sync="productionVisible" :footer="false">
    <div class="dialog-main">
      <t-form :data="productionForm" label-align="left" :rules="productionRules" @submit="productionSub">
        <t-form-item name="cycle" :label="lang.caravan_text32">
          <div class="cycle-item">
            <t-input-number v-model="productionForm.cycle_min" theme="normal" :min="0"></t-input-number>
            -
            <t-input-number v-model="productionForm.cycle_max" theme="normal" :min="0"></t-input-number>
          </div>
        </t-form-item>
        <t-form-item>
          <div class="dialog-footer">
            <t-button theme="primary" type="submit">{{lang.sure}}</t-button>
            <t-button theme="default" @click="productionVisible=false">{{lang.cancel}}</t-button>
          </div>
        </t-form-item>
      </t-form>
    </div>
  </t-dialog>

  <!-- 交付商品 -->
  <t-dialog :header="lang.caravan_text33" :visible.sync="deliveryVisible" :footer="false">
    <div class="dialog-main">
      <t-form :data="deliveryForm" label-align="left" :rules="deliveryRules" @submit="deliverySub">
        <t-form-item name="logistic" :label="lang.caravan_text34">
          <t-textarea v-model="deliveryForm.logistic" :placeholder="lang.caravan_text35" :autosize="{ minRows: 3, maxRows: 5 }" />
        </t-form-item>
        <t-form-item>
          <div class="dialog-footer">
            <t-button theme="primary" type="submit">{{lang.sure}}</t-button>
            <t-button theme="default" @click="deliveryVisible=false">{{lang.cancel}}</t-button>
          </div>
        </t-form-item>
      </t-form>
    </div>
  </t-dialog>

</div>
<!-- =======内容区域======= -->
<script src="/{$template_catalog}/template/{$themes}/api/rc.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/rc_order.js"></script>
{include file="footer"}
