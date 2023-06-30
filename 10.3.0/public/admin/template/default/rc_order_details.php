{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/rc_order_details.css">
<div id="content" class="re-order-details " v-cloak>
  <t-card class="list-card-container table">
    <div class="top">
      <span>{{data.product_name}}</span>
    </div>
    <div class="content">
      <div class="left">
        <div class="item">
          <div class="item-title">
            <div class="text">{{lang.caravan_text17}}</div>
            <div class="row-bottom-line"></div>
          </div>
          <div class="item-content">
            <div class="status-item">
              <span>{{lang.caravan_text17}}：</span>
              <span class="status">{{stataus[data.status]}}</span>
              <t-button class="operation-btn" v-if="data.status == 'Ordered'" @click="showProduction('production',data)">{{lang.caravan_text18}}</t-button>
              <t-button class="operation-btn" v-if="data.status == 'Production'" @click="showSure('finish',data.id)">{{lang.caravan_text19}}</t-button>
              <t-button class="operation-btn" v-if="data.status == 'Production'" @click="showProduction('edit',data)">{{lang.caravan_text20}}</t-button>
              <t-button class="operation-btn" v-if="data.status == 'Delivery'" @click="showDelivery(data.id)">{{lang.caravan_text21}}</t-button>
              <t-button class="operation-btn" v-if="data.status == 'FinalUnpaid'" @click="showSure('failPaid',data.id)">{{lang.caravan_text22}}</t-button>
              <t-button class="operation-btn" v-if="data.status == 'Unpaid'" @click="showSure('paid',data.id)">{{lang.caravan_text23}}</t-button>
              <span v-if="data.status == 'Delivered'">{{moment(data.delivery_time * 1000).format('YYYY-MM-DD HH:mm')}}</span>
            </div>
            <div class="pay-time-item">
              <span>{{lang.caravan_text24}}：</span>
              <span>{{moment(data.create_time * 1000).format('YYYY-MM-DD HH:mm')}}</span>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="item-title">
            <div class="text">{{lang.caravan_text25}}</div>
            <div class="row-bottom-line"></div>
          </div>
          <div class="item-content">
            <div class="buy-item">
              <span>{{data.username}}</span>
              <span>{{data.phone}}</span>
              <span>{{data.email}}</span>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="item-title">
            <div class="text">{{lang.caravan_text26}}</div>
            <div class="row-bottom-line"></div>
          </div>
          <div class="item-content">
            <div style="white-space:pre-wrap;" v-html="data.logistic"></div>
          </div>
        </div>
        <div class="item">
          <div class="item-title">
            <div class="text">{{lang.caravan_text27}}</div>
            <div class="row-bottom-line"></div>
          </div>
          <div class="item-content">
            <div style="white-space:pre-wrap;" v-html="data.distribution"></div>
          </div>
        </div>
      </div>
      <div class="right">
        <div class="top-config">
          <div class="item-title">
            <div class="text">{{lang.caravan_text28}}</div>
            <div class="row-bottom-line"></div>
          </div>
          <div class="item-name">
            <span class="product-name">{{data.product_name}}</span>
            <span class="code">{{data.code}}</span>
          </div>
          <div class="item-config" v-for="(item,index) in data.newDescription" :key="index">
            <div class="item-left">
              <span class="l-name">{{item.name}}</span>
              <span calss="l-weight">{{item.weight}}</span>
            </div>
            <div class="item-right">
              {{item.price?item.price:'--'}}
            </div>
          </div>
        </div>

        <div class="bottom-money">
          <div class="bottom-row">
            <div class="item">{{lang.caravan_text29}}</div>
            <div class="item"></div>
            <div class="item total-item" v-if="data.buy_amount">{{currency_prefix}}{{data.buy_amount}}</div>
            <div class="item total-item" v-else>--</div>
          </div>
          <div class="bottom-row">
            <div class="item">{{lang.caravan_text30}}</div>
            <div class="item" v-if="data.amount">
              <span>{{currency_prefix}}{{data.amount}}</span>
              <img v-if="data.status != 'Unpaid' && data.status != 'Cancelled'" src="/plugins/addon/room_box/template/admin/img/rc/pay.png" alt="">
              <img v-if="data.status == 'Unpaid'" src="/plugins/addon/room_box/template/admin/img/rc/un-pay.png" alt="">
            </div>
            <div class="item" v-else>--</div>
          </div>
          <div class="bottom-row">
            <div class="item">{{lang.caravan_text31}}</div>
            <div class="item"></div>
            <div class="item" v-if="data.final_amount">
              <span>{{currency_prefix}}{{data.final_amount}}</span>
              <img v-if="data.status == 'Delivery' || data.status == 'Delivered'" src="/plugins/addon/room_box/template/admin/img/rc/pay.png" alt="">
              <img v-if="data.status != 'Cancelled' && data.status != 'Delivered' && data.status != 'Delivery'" src="/plugins/addon/room_box/template/admin/img/rc/un-pay.png" alt="">
            </div>
            <div class="item" v-else>--</div>
          </div>
        </div>
      </div>
    </div>
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
            {{lang.caravan_text9}}
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
<script src="/{$template_catalog}/template/{$themes}/js/rc_order_details.js"></script>
{include file="footer"}