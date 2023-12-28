{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/shoppingCar.css">
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/loginDialog.css">
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
      <aside-menu></aside-menu>
      <el-container>
        <top-menu ref="topMenu"></top-menu>
        <el-main>
          <!-- 自己的东西 -->
          <div class="main-card">
            <div class="main-title">{{lang.shoppingCar_title}}</div>
            <div class="search-box">
              <el-input :placeholder="lang.shoppingCar_tip_text" suffix-icon="el-icon-search" @change="searchValChange"
                v-model="searchVal">
              </el-input>
            </div>
            <div class="goods-box" v-loading="listLoding">
              <div class="goods-item" v-if="showList.length !== 0 || listLoding">
                <el-checkbox-group v-model="checkedCities" @change="handleCheckedCitiesChange">
                  <div v-for="(item,index) in nowList" :key="index" class="shopping-goods" v-loading="item.isLoading">
                    <div class="table-name">
                      <el-checkbox :label="item.position" v-if="item.info">
                        <span class="goods-name">{{item.name}}</span>
                      </el-checkbox>
                      <span class="goods-name mar-left-24" v-else>{{item.name}}</span>
                      <el-link type="primary" class="edit-goods" v-if="item.info"
                        @click="goGoods(item)">{{lang.shoppingCar_editGoods}}</el-link>
                    </div>
                    <div class="table-content">
                      <table class="goods-table">
                        <thead>
                          <th class="description-box">{{lang.shoppingCar_goodsInfo}}</th>
                          <th class="price-box">{{lang.shoppingCar_goodsPrice}}</th>
                          <th class="num-box">{{lang.shoppingCar_goodsNums}}</th>
                          <th class="total-box">{{lang.shoppingCar_goodsTotalPrice}}</th>
                          <th class="action-box">{{lang.shoppingCar_goodsAction}}</th>
                        </thead>
                        <tbody>
                          <tr v-if="item.info">
                            <td>
                              <div class="info-box">
                                <div class="goods-info" v-if="false">
                                  <span class="goodsInfo-type">
                                    <span class="goodsInfo-name">{{item.name}}</span>
                                    <span class="goodsInfo-val"></span>
                                  </span>
                                  <span
                                    class="goodsInfo-price">{{commonData.currency_prefix}}{{item.info.base_price}}</span>
                                </div>
                                <div v-for="(infoItem,value,index) in item.preview" :key="index" class="goods-info">
                                  <span class="goodsInfo-type">
                                    <span class="goodsInfo-name">{{infoItem.name}}</span>
                                    <span class="goodsInfo-val">：{{infoItem.value}}</span>
                                  </span>
                                  <span class="goodsInfo-price">{{commonData.currency_prefix}}{{infoItem.price}}</span>
                                </div>
                              </div>
                            </td>
                            <td class="item-price">{{commonData.currency_prefix}}{{Number(item.price).toFixed(2)}}
                              <span v-if="item.info.billing_cycle">/ {{item.info.billing_cycle}}</span>
                            </td>
                            <td>
                              <el-input-number v-model="item.qty" size="small"
                                @change="(currentValue,oldValue)=>handleChange(currentValue,oldValue,item,index)"
                                :min="1" :max="item.stock_control === 1 ? item.stock_qty : 9999"></el-input-number>
                              <p v-if="item.stock_control === 1" :class="item.stock_qty === 0 ? 'red-text':''"
                                class="qty-num">{{lang.shoppingCar_goods_tock_qty}}：{{ item.stock_qty }}</p>
                              <p v-if="item.isShowTips" class="qty-tips">{{lang.shoppingCar_tock_qty_tip}}</p>
                            </td>
                            <td class="item-total" v-loading="item.priceLoading">
                              <span>{{commonData.currency_prefix}} {{calcItemPrice(item)}}</span>
                              <el-popover placement="top-start" width="200" trigger="hover"
                                v-if="isShowLevel || (isShowPromo && item.isUseDiscountCode)">
                                <div class="show-config-list">
                                  <p v-if="isShowLevel">{{lang.shoppingCar_tip_text2}}：{{commonData.currency_prefix}} {{
                                    (item.level_discount * (item.hasCalc ? 1: item.qty)).toFixed(2) | filterMoney }}</p>
                                  <p v-if="isShowPromo && item.isUseDiscountCode">
                                    {{lang.shoppingCar_tip_text4}}：{{commonData.currency_prefix}} {{ item.code_discount
                                    | filterMoney }}</p>
                                  <p v-if="item.eventDiscount">{{lang.goods_text4}}：{{commonData.currency_prefix}} {{
                                    item.eventDiscount | filterMoney }}</p>
                                </div>
                                <i class="el-icon-warning-outline total-icon" slot="reference"></i>
                              </el-popover>
                              <p class="original-price"
                                v-if="item.code_discount != 0 || (item.level_discount * (item.hasCalc ? 1: item.qty)) != 0 || item.eventDiscount != 0 ">
                                {{commonData.currency_prefix}} {{(item.price * item.qty).toFixed(2)}}</p>
                              <div class="discount-box">
                                <discount-code v-if="item.customfield && !item.customfield.promo_code && isShowPromo"
                                  @get-discount="getDiscount(arguments)" scene='new' :product_id='item.product_id'
                                  :qty="item.qty" :amount="item.price" :billing_cycle_time="item.info.duration"
                                  :shopping_index="item.position">
                                </discount-code>
                                <div v-if="item.customfield && item.customfield.promo_code" class="discount-codeNumber">
                                  {{ item.customfield.promo_code }}
                                  <i class="el-icon-circle-close remove-discountCode"
                                    @click="removeDiscountCode(item)"></i>
                                </div>
                                <event-code v-if="item.info && item.info.duration !=='' && isShowFull"
                                  :id="item.customfield.event_promotion" :product_id='item.product_id' :qty="item.qty"
                                  :amount="item.price" :billing_cycle_time="item.info.duration"
                                  @change="(price) => changeEventCode(price,item)">
                                </event-code>
                              </div>

                            </td>
                            <td class="delete-btn" @click="handelDeleteGoods(item,index)">
                              <i class="el-icon-delete"></i>
                            </td>
                          </tr>
                          <tr v-else>
                            <td></td>
                            <td class="no-goods-td">
                              <span class="no-goods-tips">{{lang.shoppingCar_no_goods_tip}}</span>
                              <el-button class="buy-again-btn"
                                @click="goGoods(item)">{{lang.shoppingCar_buy_again}}</el-button>
                            </td>
                            <td></td>
                            <td></td>
                            <td class="delete-btn" @click="handelDeleteGoods(item,index)">
                              <i class="el-icon-delete"></i>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </el-checkbox-group>
              </div>
              <div v-else>
                <el-empty :description="lang.shoppingCar_no_goods_text"></el-empty>
              </div>
            </div>
          </div>
        </el-main>
        <el-footer>
          <div class="footer-box">
            <div class="footer-left">
              <el-checkbox class="all-check" v-model="checkAll"
                @change="handleCheckAllChange">{{lang.shoppingCar_select_all}}</el-checkbox>
              <span class="delect-btn">
                <el-link type="danger" class="delect-goods"
                  @click="deleteCheckGoods">{{lang.shoppingCar_delete_select}}</el-link>
              </span>
              <span>{{lang.shoppingCar_selected}}<span
                  class="text-red">{{checkedCities.length}}</span>{{lang.shoppingCar_goods_text}}</span>
            </div>
            <div class="footer-right">
              <p>{{lang.shoppingCar_tip_text3}}：<span
                  class="total-price">{{commonData.currency_prefix}}{{Number(totalPrice).toFixed(2)}}</span></p>
              <el-button type="primary" class="buy-btn" @click="goSettle">{{lang.shoppingCar_buy_text}}</el-button>
            </div>
          </div>
        </el-footer>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/shopping.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/discountCode/discountCode.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/eventCode/eventCode.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/shoppingCar.js"></script>
  {include file="footer"}