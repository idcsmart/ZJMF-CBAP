<link rel="stylesheet" href="/plugins/addon/idcsmart_refund/template/admin/css/refund.css" />
<!-- =======内容区域======= -->
<div id="content" class="template" v-cloak>
  <t-card class="list-card-container my-card-container">

    <t-divider class="add-refund-divider" align="left">{{lang.basic_info}}</t-divider>
    <!-- 选择商品 -->
    <span class="poduct-form-label">{{lang.product_name}}</span>
    <t-select v-model="fromData.product_id" @change="productChange" clearable filterable style="width:20%">
      <t-option v-for="(item, index) in productOptions" :value="item.id" :label="item.name" :key="index">
        {{ item.name }}
      </t-option>
    </t-select>
    <!-- 商品配置 -->
    <!-- <span class="poduct-form-label" style="margin-top:30px">{{lang.product_configuration}}</span>
    <t-card shadow class="table-card">
      <div v-html="fromData.config_option"></div>
    </t-card> -->

    <t-divider class="add-refund-divider" align="left">{{lang.refund_info}}</t-divider>




    <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 40 }">
      <!-- 退款类型 -->
      <t-col :span="3">
        <div>
          <span class="poduct-form-label">{{lang.refund_type}}</span>
          <t-select v-model="fromData.type" clearable style="width:100%">
            <t-option v-for="(item, index) in typeOptions" :value="item.id" :label="item.name" :key="index">
              {{ item.name }}
            </t-option>
          </t-select>
        </div>
      </t-col>
      <!-- 退款规则 -->
      <t-col :span="3">
        <div>
          <span class="poduct-form-label">{{lang.refund_rule}}</span>
          <t-select @change="ruleChange" v-model="fromData.rule" clearable style="width:100%">
            <t-option v-if="pay_type==='recurring_postpaid'||pay_type==='recurring_prepayment'" value="Day" :label="lang.refund_rule_day"></t-option>
            <t-option v-if="pay_type==='recurring_postpaid'||pay_type==='recurring_prepayment'" value="Month" :label="lang.refund_rule_month"></t-option>
            <t-option v-if="pay_type==='onetime'" value="Ratio" :label="lang.refund_rule_ratio"></t-option>
          </t-select>
        </div>
      </t-col>
      <!-- 退款比例 -->
      <t-col :span="3">
        <div v-if="fromData.rule==='Ratio'">
          <span class="poduct-form-label">{{lang.refund_rate}}</span>
          <t-input-number theme="normal" v-model="fromData.ratio_value" style="width:calc(100% - 22px);margin: 0 3px;" @change="changere_fundRate"></t-input-number>%

          <!--                    <template v-if="fromData.rule==='Day'||fromData.rule==='Month'">-->
          <!--                      <span class="poduct-form-label">{{lang.refund_amount}}</span>-->
          <!--                      <t-input v-model="fromData.amount" style="width:calc(100% - 22px);float:left;margin-right:3px"></t-input>-->
          <!--                      <span style="line-height: 30px;">{{lang.refund_amount_yuan}}</span>-->
          <!--                    </template>-->
        </div>
      </t-col>
      <t-col :span="3"></t-col>
      <!-- 退款要求 -->
      <t-col :span="6" style="margin-top:30px">
        <div class="add-refund-require">
          <span class="poduct-form-label">{{lang.refund_require}}</span>
          <div class="add-refund-require-content">
            <t-radio :allow-uncheck="true" @change="checkChange" :checked="fromData.require=='First'" value="First" style="display: block;margin-bottom: 10px;">{{lang.first_order}}</t-radio>
            <t-radio :allow-uncheck="true" @change="checkChange" :checked="fromData.require=='Same'" value="Same" style="display: block;margin-bottom: 10px;">{{lang.first_order_same}}</t-radio>
            <t-radio :allow-uncheck="true" @change="checkChange" :checked="fromData.require=='range'" value="range">
              <div class="require-content-range">
                {{lang.refund_range1}}
                <t-input-number theme="normal" v-model="fromData.range" :on-focus="checkRange" :min="0" style="width:100px;margin: 0 3px;"></t-input-number>
                {{lang.refund_range2}}
              </div>
            </t-radio>


          </div>
        </div>
      </t-col>
    </t-row>
    <div class="add-refund-btn">
      <t-button theme="primary" @click="addEdit">{{lang.submit}}</t-button>
      <t-button theme="default" @click="goback(true)">{{lang.cancel}}</t-button>
    </div>
  </t-card>
</div>
<!-- =======页面独有======= -->
<script src="/plugins/addon/idcsmart_refund/template/admin/api/refund.js"></script>
<script src="/plugins/addon/idcsmart_refund/template/admin/js/addEditrefund.js"></script>