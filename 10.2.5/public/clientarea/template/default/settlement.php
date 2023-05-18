{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/settlement.css">
</head>

<body>
  <!-- mounted之前显示 -->
  <div id="mainLoading">
    <div class="ddr ddr1"></div>
    <div class="ddr ddr2"></div>
    <div class="ddr ddr3"></div>
    <div class="ddr ddr4"></div>
    <div class="ddr ddr5"></div>
  </div>
  <div class="template">
    <el-container>
      <aside-menu @getruleslist="getRule"></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <el-main>
          <!-- 自己的东西 -->
          <div class="main-card">
            <div class="main-title">{{lang.settlement_title}}</div>
            <div class="goods-box">
              <div class="goods-item" v-loading="listLoading">
                <div v-for="(item,index) in showGoodsList" :key="index" class="shopping-goods" v-loading="item.isLoading">
                  <div class="table-name">
                    <span class="goods-name">{{item.name}}</span>
                  </div>
                  <div class="table-content">
                    <table class="goods-table">
                      <thead>
                        <th class="description-box">{{lang.settlement_goodsInfo}}</th>
                        <th class="price-box">{{lang.settlement_goodsPrice}}</th>
                        <th class="num-box">{{lang.settlement_goodsNums}}</th>
                        <th class="total-box">{{lang.settlement_goodsTotalPrice}}</th>
                      </thead>
                      <tbody>
                        <tr>
                          <td>
                            <div class="info-box" v-if="item.info">
                              <div class="goods-info" v-if="item.info.base_price != '0'">
                                <span class="goodsInfo-type">
                                  <span class="goodsInfo-name">{{item.name}}</span>
                                  <span class="goodsInfo-val"></span>
                                </span>
                                <span class="goodsInfo-price">{{commonData.currency_prefix}}{{item.info.base_price}}</span>
                              </div>
                              <div v-for="(infoItem,value,index) in item.preview" :key="index" class="goods-info">
                                <span class="goodsInfo-type">
                                  <span class="goodsInfo-name">{{infoItem.name}}</span>
                                  <span class="goodsInfo-val">：{{infoItem.value}}</span>
                                </span>
                                <span class="goodsInfo-price">{{commonData.currency_prefix}}{{infoItem.price}}</span>
                              </div>
                            </div>
                            <div v-else></div>
                          </td>
                          <td class="item-price">
                            {{commonData.currency_prefix}}{{Number(item.price).toFixed(2)}}
                            <span v-if="item.info && item.info.billing_cycle">/ {{item.info.billing_cycle}}</span>
                          </td>
                          <td>
                            {{item.qty}}
                          </td>
                          <td class="item-total">
                            <span>{{commonData.currency_prefix}} {{(((item.price * item.qty)*1000 - item.code_discount*1000 - item.level_discount*1000) / 1000) > 0 ? (((item.price * item.qty)*1000 - item.code_discount*1000 - item.level_discount*1000) / 1000).toFixed(2) : 0.00}}</span>
                            <el-popover placement="top-start" width="180" trigger="hover" v-if="isShowLevel || (isShowPromo && item.isUseDiscountCode)">
                              <div class="show-config-list">
                                <p v-if="isShowLevel">{{lang.shoppingCar_tip_text2}}：{{commonData.currency_prefix}} {{ item.level_discount | filterMoney }}</p>
                                <p v-if="isShowPromo && item.isUseDiscountCode">{{lang.shoppingCar_tip_text4}}：{{commonData.currency_prefix}} {{ item.code_discount | filterMoney }}</p>
                              </div>
                              <i class="el-icon-warning-outline total-icon" slot="reference"></i>
                            </el-popover>
                            <p class="original-price" v-if="item.level_discount != 0 || item.code_discount != 0">{{commonData.currency_prefix}} {{(item.price * item.qty).toFixed(2)}}</p>
                            <div v-show="item.customfield.promo_code" class="discount-codeNumber">
                              {{ item.customfield.promo_code }}
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </el-main>
        <el-footer v-if="!isNaN(Number(totalPrice).toFixed(2))" style="min-height: 1.6rem; height:auto;">
          <div class="footer-box">
            <div class="footer-left">
              <span class="pay-text">{{lang.settlement_tip1}}</span>
              <el-radio-group v-model="payType" class="radio-box" v-if="payTypeList.length!==0">
                <el-radio :label="item.name" v-for="item in payTypeList" :key="item.id">{{item.title}}</el-radio>
              </el-radio-group>
            </div>
            <div class="footer-right">
              <div class="totalprice-box" v-loading="totalPriceLoading">
                <div>
                  {{lang.settlement_tip2}}：<span class="total-price">{{commonData.currency_prefix}}{{finallyPrice | filterMoney}}</span>
                  <el-popover placement="top-start" width="180" trigger="hover" v-if="isShowLevel || (isShowPromo && isUseDiscountCode) || isShowCash">
                    <div class="show-config-list">
                      <p v-if="isShowLevel">{{lang.shoppingCar_tip_text2}}：{{commonData.currency_prefix}} {{ totalLevelDiscount | filterMoney }}</p>
                      <p v-if="isShowPromo && isUseDiscountCode">{{lang.shoppingCar_tip_text4}}：{{commonData.currency_prefix}} {{ totalCodelDiscount | filterMoney }}</p>
                      <p v-if="isShowCash && cashObj.code">代金券抵扣金额：{{commonData.currency_prefix}} {{ cashPrice | filterMoney }}</p>
                    </div>
                    <i class="el-icon-warning-outline total-icon" slot="reference"></i>
                  </el-popover>
                  <div class="cash-codeNumber" v-show="cashObj.code && isShowCash">
                    {{ cashObj.code }}<i class="el-icon-circle-close remove-discountCode" @click="reRemoveCashCode()"></i>
                  </div>
                </div>
                <div class="cash-box">
                  <cash-coupon ref="cashRef" v-show="!cashObj.code && isShowCash" :currency_prefix="commonData.currency_prefix" @use-cash="useCash" scene='new' :product_id="goodIdList" :price="orginPrice"></cash-coupon>
                </div>
              </div>
              <div class="btn-box" v-if="showPayBtn">
                <el-button type="primary" class="buy-btn" @click="goPay" :loading="subBtnLoading">{{lang.settlement_tip3}}</el-button>
                <div class="check-box">
                  <el-checkbox v-model="checked"></el-checkbox>
                  {{lang.settlement_tip4}}
                  <span class="bule-text" @click="goHelpUrl('2')">{{lang.read_service}}</span>
                  {{lang.settlement_tip6}}
                  <span class="bule-text" @click="goHelpUrl('1')">{{lang.read_privacy}}</span>
                </div>
              </div>
            </div>
          </div>
        </el-footer>
        <!-- 支付弹窗 -->
        <pay-dialog ref="payDialog" @payok="paySuccess" @paycancel="payCancel"></pay-dialog>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/shopping.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/settlement.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/cashCoupon/cashCoupon.js"></script>
  {include file="footer"}