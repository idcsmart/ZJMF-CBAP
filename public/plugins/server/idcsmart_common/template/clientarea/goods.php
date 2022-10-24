<link rel="stylesheet" href="/plugins/server/idcsmart_common/template/clientarea/css/common_config.css">
<div class="template common-config">
  <!-- 自己的东西 -->
  <div class="main-card">
    <div class="pro-tit">{{basicInfo.name}}</div>
    <div class="common-box">
      <div class="l-config">
        <div class="description" v-html="calStr" v-if="calStr"></div>
        <!-- 自定义配置项 -->
        <div class="config-item" v-for="item in configoptions" :key="item.id">
          <p class="config-tit">{{item.option_name}}</p>
          <!-- 配置项 -->
          <div class="config-way">
            <!-- 下拉单选/多选 -->
            <el-select v-model="configForm[item.id]" :placeholder="lang.please_select" v-if="item.option_type === 'select' || item.option_type ==='multi_select'" :multiple="item.option_type ==='multi_select'" collapse-tags @change="changeItem(item)">
              <el-option v-for="item in item.subs" :key="item.id" :label="item.option_name" :value="item.id">
              </el-option>
            </el-select>
            <!-- 是否 -->
            <el-switch v-model="configForm[item.id]" v-if="item.option_type ==='yes_no'" active-color="#0052D9" :active-value="calcSwitch(item,true)" :inactive-value="calcSwitch(item,false)" @change="changeConfig(false)">
            </el-switch>
            <!-- 数据输入 -->
            <el-input-number v-model="configForm[item.id]" :min="item.subs[0].qty_min" :max="item.subs[item.subs.length-1].qty_max" v-if="item.option_type ==='quantity'" @change="changeConfig(false)">
            </el-input-number>
            <!-- 数量拖动 -->
            <div class="slider" v-if="item.option_type ==='quantity_range'">
              <span class="min">{{item.subs[0].qty_min}}</span>
              <el-slider v-model="configForm[item.id][0]" @change="changeConfig(false)" :min="item.subs[0].qty_min" :max="item.subs[item.subs.length - 1].qty_max">
              </el-slider>
              <span class="max">{{item.subs[item.subs.length - 1].qty_max}}</span>
              <el-input v-model="configForm[item.id][0]" @input="changeNum($event,item.id)"></el-input>
            </div>

            <!-- 点击单选 -->
            <div class="click-select" v-if="item.option_type ==='radio'">
              <div class="item" v-for="el in item.subs" :key="el.id" :class="{'com-active': el.id === configForm[item.id]}" @click="changeClick(item.id, el)">
                {{el.option_name}}
                <i class="el-icon-check"></i>
              </div>
            </div>

            <!-- 区域选择 -->

            <div class="area-box" v-if="item.option_type ==='area'">
              <p class="tit">{{lang.account_label4}}</p>
              <div class="country">
                <div class="item" v-for="(el,index) in filterCountry[item.id]" :key="index" :class="{'com-active': index  === curCountry[item.id] }" @click="changeCountry(item.id,index)">
                  <img :src="`upload/common/country/${el[0].country}.png`" alt="">
                  <span>{{calcCountry(el[0].country)}}</span>
                  <i class="el-icon-check"></i>
                </div>
              </div>
              <p class="tit">{{lang.com_config.city}}</p>
              <div class="city">
                <div class="item" v-for="el in filterCountry[item.id][curCountry[item.id]]" :class="{'com-active': el.id  === configForm[item.id] }" @click="changeCity(el, item.id)">
                  <img :src="`upload/common/country/${el.country}.png`" alt="">
                  <span>{{el.option_name}}</span>
                  <i class="el-icon-check"></i>
                </div>
              </div>
            </div>


            <!-- 后缀单位 -->
            <span class="unit">{{item.unit}}</span>
          </div>
          <!-- 描述 -->
          <p class="des" v-if="item.option_type !== 'area' && item.description" v-html="calcDes(item.description)">
          </p>
        </div>
        <!-- 周期 -->
        <div class="config-item">
          <p class="config-tit">{{lang.com_config.cycle}}</p>
          <div class="onetime" v-if="basicInfo.pay_type === 'onetime'">
            <p>{{lang.product_onetime_free}}：{{commonData.currency_prefix}}{{onetime}}</p>
          </div>
          <div class="onetime" v-if="basicInfo.pay_type === 'free'">
            <p>{{lang.product_free}}</p>
          </div>
          <div class="cycle" v-if="basicInfo.pay_type === 'recurring_prepayment' || basicInfo.pay_type === 'recurring_postpaid'">
            <div class="item" v-for="(item,index) in custom_cycles" :key="item.id" @click="changeCycle(item,index)" :class="{'com-active': index === curCycle }">
              <p class="name">{{item.name}}</p>
              <p class="price">{{commonData.currency_prefix}}{{((item.cycle_amount !== '' ? item.cycle_amount : item.amount) * 1).toFixed(2) | filterMoney}}</p>
              <i class="el-icon-check"></i>
            </div>
          </div>
        </div>
      </div>
      <!-- 配置预览 -->
      <div class="order-right" style="justify-content: space-between;">
        <div class="right-main">
          <div class="right-title">
            {{lang.product_preview}}
          </div>
          <div class="info" v-loading="dataLoading">
            <p class="des">
              <span>{{basicInfo.name}}</span>
              <span v-if="base_price*1">{{commonData.currency_prefix}}{{ Number(base_price).toFixed(2) | filterMoney}}</span>
            </p>
            <p class="des" v-for="(item,index) in showInfo" :key="index">
              <span class="name">{{item.name}}</span>
              <span class="value">{{item.value}}</span>
              <span class="price">{{commonData.currency_prefix}}{{item.price | filterMoney}}</span>
            </p>
          </div>
          <div class="subtotal">
            <span class="name">{{lang.shoppingCar_goodsTotalPrice}}：</span>
            <span v-loading="dataLoading">{{commonData.currency_prefix }}{{((totalPrice * 1).toFixed(2)) | filterMoney }}</span>
          </div>
        </div>

        <div>
          <!-- 合计 优惠码 购买按钮 -->
          <div class="order-right-footer">
            <div class="order-right-item">
              <div class="row">
                <div class="label">{{lang.shoppingCar_goodsNums}}</div>
                <div class="value del-add">
                  <span class="del" @click="delQty" :class="{disabled: basicInfo.allow_qty === 0 }">-</span>
                  <el-input-number class="num" :controls="false" v-model="orderData.qty" :min="1" :disabled="basicInfo.allow_qty === 0">
                  </el-input-number>
                  <span class="add" @click="addQty" :class="{disabled: basicInfo.allow_qty === 0 }">+</span>
                </div>
              </div>
            </div>
            <div class="footer-total">
              <div class="left">{{lang.shoppingCar_tip_text3}}</div>
              <div class="right" v-loading="dataLoading">
                {foreach $addons as $addon}
                {if ($addon.name=='IdcsmartClientLevel')}
                <el-popover placement="top-start" width="100" trigger="hover" popper-class="discout">
                  <div class="show-config-list">
                    {{lang.shoppingCar_tip_text2}}：{{commonData.currency_prefix + discount}}
                  </div>
                  <i class="el-icon-warning-outline total-icon" slot="reference"></i>
                </el-popover>
                {/if}
                {/foreach}
                {{commonData.currency_prefix }}{{((totalPrice * orderData.qty - discount * 1 ).toFixed(2)) | filterMoney }}
              </div>
            </div>
            <!-- <div class="footer-code">
                    <div class="code-main">
                      <div class="left">使用优惠码<i class="el-icon-circle-plus-outline"></i></div>
                      <div class="right">-{{commonData.currency_prefix + '0'}}</div>
                    </div>
                    <div class="code-detail">
                      <div class="code-detail-item">
                        <span class="code">RD57</span>
                        <span class="num">-$500</span>
                        <i class="el-icon-circle-close btn"></i>
                      </div>
                      <div class="code-detail-item">
                        <span class="code">RD57</span>
                        <span class="num">-$500</span>
                        <i class="el-icon-circle-close btn"></i>
                      </div>
                      <div class="code-detail-item">
                        <span class="code">RD57</span>
                        <span class="num">-$500</span>
                        <i class="el-icon-circle-close btn"></i>
                      </div>
                    </div> -->
            <!-- <div class="read" v-if="!this.backfill.cycle">
              <el-checkbox v-model="orderData.isRead"></el-checkbox>&nbsp;&nbsp;已阅读并同意
              <a :href="commonData.terms_service_url" target="_blank">《服务协议》</a>和
              <a :href="commonData.terms_privacy_url" target="_blank">《隐私协议》</a>
            </div> -->
          </div>
          <!-- 需读 -->
          <!-- 购买按钮 -->
          <div class="f-btn">
            <template v-if="this.backfill.cycle">
              <el-button class="buy-btn" type="primary" @click="changeCart" :loading="submitLoading" style="width:100%">{{lang.product_sure_check}}</el-button>
            </template>
            <template v-else>
              <el-button class="cart" type="primary" plain @click="addCart" :loading="submitLoading" style="width:100%">{{lang.product_add_cart}}</el-button>
              <el-button class="buy-btn" type="primary" @click="buyNow" :loading="submitLoading" style="width:100%">{{lang.product_buy_now}}</el-button>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- 支付弹窗 -->
  <pay-dialog ref="payDialog" @payok="paySuccess" @paycancel="payCancel"></pay-dialog>
  <!-- 加入购物车成功弹窗 -->
  <el-dialog title="" :visible.sync="cartDialog" custom-class="cartDialog" :show-close="false">
    <span class="tit">{{lang.product_tip}}</span>
    <span slot="footer" class="dialog-footer">
      <el-button type="primary" @click="cartDialog = false">{{lang.product_continue}}</el-button>
      <el-button @click="goToCart">{{lang.product_settlement}}</el-button>
    </span>
  </el-dialog>

</div>
<!-- =======页面独有======= -->

<script src="/plugins/server/idcsmart_common/template/clientarea/api/common.js"></script>
<script src="/plugins/server/idcsmart_common/template/clientarea/api/common_product.js"></script>
<script src="/plugins/server/idcsmart_common/template/clientarea/js/common_product.js"></script>