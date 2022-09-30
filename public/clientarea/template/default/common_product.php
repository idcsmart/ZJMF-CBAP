{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common_config.css">
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
  <div class="template common-config">
    <el-container>
      <aside-menu></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <el-main>
          <!-- 自己的东西 -->
          <div class="main-card">
            <div class="pro-tit">商品名称</div>
            <div class="common-box">
              <div class="l-config">
                <div class="description" v-html="calStr"></div>
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
                    <el-switch v-model="configForm[item.id]" v-if="item.option_type ==='yes_no'" active-color="#0052D9" :active-value="calcSwitch(item,true)" :inactive-value="calcSwitch(item,false)" @change="changeConfig">
                    </el-switch>
                    <!-- 数据输入 -->
                    <el-input-number v-model="configForm[item.id]" :min="item.subs[0].qty_min" :max="item.subs[item.subs.length-1].qty_max" v-if="item.option_type ==='quantity'" @change="changeConfig">
                    </el-input-number>
                    <!-- 数量拖动 -->
                    <div class="slider" v-if="item.option_type ==='quantity_range'">
                      <span class="min">{{item.subs[0].qty_min}}</span>
                      <el-slider v-model="configForm[item.id][0]" @change="changeConfig" :min="item.subs[0].qty_min" :max="item.subs[item.subs.length - 1].qty_max">
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
                          <img :src="`http://kfc.idcsmart.com/upload/common/country/${el[0].country}.png`" alt="">
                          <span>{{calcCountry(el[0].country)}}</span>
                          <i class="el-icon-check"></i>
                        </div>
                      </div>
                      <p class="tit">{{lang.com_config.city}}</p>
                      <div class="city">
                        <div class="item" v-for="el in filterCountry[item.id][curCountry[item.id]]" :class="{'com-active': el.id  === configForm[item.id] }" @click="changeCity(el, item.id)">
                          <img :src="`http://kfc.idcsmart.com/upload/common/country/${el.country}.png`" alt="">
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
                    <p>一次性费用：{{commonData.currency_prefix}}{{onetime}}</p>
                  </div>
                  <div class="onetime" v-if="basicInfo.pay_type === 'free'">
                    <p>免费</p>
                  </div>
                  <div class="cycle" v-if="basicInfo.pay_type === 'recurring_prepayment' || basicInfo.pay_type === 'recurring_postpaid'">
                    <div class="item" v-for="(item,index) in custom_cycles" :key="item.id" @click="changeCycle(item,index)" :class="{'com-active': index === curCycle }">
                      <p class="name">{{item.name}}</p>
                      <p class="price">{{commonData.currency_prefix}}{{item.amount}}</p>
                      <i class="el-icon-check"></i>
                    </div>
                  </div>
                </div>
              </div>
              <!-- 配置预览 -->
              <div class="order-right" style="justify-content: flex-start;">
                <div class="right-main">
                  <div class="right-title">
                    配置预览
                  </div>
                  <div class="info">
                    <p class="des" v-for="(item,index) in showInfo" :key="index">
                      <span>{{item.name}}</span>
                      <span>{{item.value}}</span>
                    </p>
                  </div>
                  <div class="order-right-item">
                    <div class="row">
                      <div class="label">数量</div>
                      <div class="value del-add">
                        <span class="del" @click="delQty">-</span>
                        <el-input-number class="num" :controls="false" v-model="orderData.qty" :min="1">
                        </el-input-number>
                        <span class="add" @click="addQty">+</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- 合计 优惠码 购买按钮 -->
                <div class="order-right-footer">
                  <div class="footer-total">
                    <div class="left">合计</div>
                    <div class="right">{{commonData.currency_prefix + totalPrice * orderData.qty}}</div>
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
                  <div class="read">
                    <el-checkbox v-model="orderData.isRead">已阅读并同意</el-checkbox>
                    <a href="#">《服务协议》和《隐私协议》</a>
                  </div>
                </div>
                <!-- 需读 -->
                <!-- 购买按钮 -->
                <el-button class="buy-btn" type="primary" @click="addCart" :loading="submitLoading" style="width:100%">立即购买</el-button>
              </div>
            </div>
          </div>
          <!-- 支付弹窗 -->
          <pay-dialog ref="payDialog" @payok="paySuccess" @paycancel="payCancel"></pay-dialog>
  </div>
  </el-main>
  </el-container>
  </el-container>
  </div>
  <!-- =======页面独有======= -->

  <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/common_product.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/common_product.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
  {include file="footer"}