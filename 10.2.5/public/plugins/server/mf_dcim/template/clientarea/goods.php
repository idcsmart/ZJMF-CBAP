<!-- 页面独有样式 -->
<link rel="stylesheet" href="/plugins/server/mf_dcim/template/clientarea/css/mf_dcim.css">
<div class="template">
  <!-- 自己的东西 -->
  <h1 class="tit">{{tit}}</h1>
  <div class="main-card mf-dcim" v-loading="loadingPrice && isInit" :class="{'no-login': !isLogin}">
    <div class="con">
      <p class="com-tit">{{lang.basic_config}}</p>
      <el-form :model="params" :rules="rules" ref="orderForm" label-position="left" label-width="100px" hide-required-asterisk>
        <el-form-item :label="lang.common_cloud_label1">
          <el-tabs v-model="country" @tab-click="changeCountry" :class="{hide: dataList.length === 1}">
            <el-tab-pane :label="item.name" :name="String(item.id)" v-for="item in dataList" :key="item.id">
              <el-radio-group v-model="city" @input="changeCity($event,item.city)">
                <el-radio-button :label="c.name" v-for="(c,cInd) in item.city" :key="cInd">
                </el-radio-button>
              </el-radio-group>
            </el-tab-pane>
          </el-tabs>
          <p class="s-tip">{{lang.mf_tip1}}&nbsp;<span>{{lang.mf_tip2}}</span>{{lang.mf_tip3}}</p>
        </el-form-item>
        <!-- 可用区 -->
        <el-form-item :label="lang.usable_area">
          <el-radio-group v-model="area_name" @input="changeArea">
            <el-radio-button :label="c.name" v-for="(c,cInd) in calcAreaList" :key="cInd">
            </el-radio-button>
          </el-radio-group>
          <p class="s-tip">{{lang.mf_tip10}}</p>
        </el-form-item>
        <!-- 机型配置 -->
        <p class="com-tit">{{lang.model_config}}</p>
        <el-form-item :label="lang.mf_specs">
          <div class="top-select">
            <el-select v-model="curCpu" :placeholder="lang.choose_cpu" clearable>
              <el-option v-for="item in cpuSelect" :key="item.value" :label="item.label" :value="item.value">
              </el-option>
            </el-select>
            <el-select v-model="curMemory" :placeholder="lang.choose_memory" clearable>
              <el-option v-for="item in memorySelect" :key="item.value" :label="item.label" :value="item.value">
              </el-option>
            </el-select>
          </div>
          <el-table :data="calcModel" max-height="270" row-class-name="tableRowClassName" tooltip-effect="light">
            <el-table-column prop="" width="55">
              <template slot-scope="{row}">
                <el-radio v-model="params.model_config_id" :label="row.id"></el-radio>
              </template>
            </el-table-column>
            <el-table-column prop="name" :label="lang.mf_model" show-overflow-tooltip>
            </el-table-column>
            <el-table-column prop="cpu" :label="lang.mf_cpu" show-overflow-tooltip>
            </el-table-column>
            <el-table-column prop="cpu_param" :label="lang.mf_cpu_param" show-overflow-tooltip>
            </el-table-column>
            <el-table-column prop="memory" :label="lang.cloud_memery" show-overflow-tooltip>
            </el-table-column>
            <el-table-column prop="disk" :label="lang.mf_disk" show-overflow-tooltip>
            </el-table-column>
          </el-table>
        </el-form-item>
        <!-- 网络配置 -->
        <p class="com-tit">{{lang.net_config}}</p>
        <!-- 线路 -->
        <el-form-item :label="lang.mf_line">
          <el-radio-group v-model="lineName" @input="changeLine">
            <el-radio-button :label="c.name" v-for="(c,cInd) in calcLineList" :key="cInd">
            </el-radio-button>
          </el-radio-group>
        </el-form-item>
        <!-- 公网IP -->
        <el-form-item :label="lang.common_cloud_title3">
          <el-radio-group v-model="ipName" @input="changeIp">
            <el-radio-button :label="c.value + lang.mf_one" v-for="(c,cInd) in ipData" :key="cInd">
            </el-radio-button>
          </el-radio-group>
        </el-form-item>
        <!-- 带宽 -->
        <el-form-item :label="lang.mf_bw" v-if="lineDetail.bill_type === 'bw' && lineDetail.bw.length > 0">
          <!-- 单选 -->
          <el-radio-group v-model="bwName" v-if="bwType === 'radio'" @input="changeBw">
            <el-radio-button :label="c.value + 'M'" v-for="(c,cInd) in calcBwList" :key="cInd" :class="{'com-dis': c.disabled}">
            </el-radio-button>
          </el-radio-group>
          <!-- 拖动框 -->
          <el-tooltip effect="light" v-else :content="lang.mf_range + bwTip" placement="top-end">
            <el-slider v-model="params.bw" show-input :step="1" :show-tooltip="false" v-if="calcBwRange.length > 0" :min="calcBwRange[0] * 1" :max="calcBwRange[calcBwRange.length -1] * 1" :show-stops="false" @change="changeBwNum">
            </el-slider>
          </el-tooltip>
        </el-form-item>
        <el-form-item label=" " v-if="lineDetail.bw && lineDetail.bw[0].type !== 'radio'  && calcBwRange.length > 0">
          <div class="marks">
            <span class="item" v-for="(item,index) in Object.keys(bwMarks)">{{bwMarks[item]}}Mbps</span>
          </div>
        </el-form-item>
        <!-- 流量 -->
        <el-form-item :label="lang.mf_flow" v-if="lineDetail.bill_type === 'flow' && lineDetail.flow.length > 0">
          <el-radio-group v-model="flowName" @input="changeFlow">
            <el-radio-button :label="c.value > 0 ? (c.value + 'G') : lang.mf_tip28" v-for="(c,cInd) in calcFlowList" :key="cInd">
            </el-radio-button>
          </el-radio-group>
        </el-form-item>
        <!-- 防御 -->
        <el-form-item :label="lang.mf_defense" v-if="lineDetail.defence && lineDetail.defence.length >0">
          <el-radio-group v-model="defenseName">
            <el-radio-button :label="c.value + 'G'" v-for="(c,cInd) in lineDetail.defence" :key="cInd" @click.native="chooseDefence($event,c)">
            </el-radio-button>
          </el-radio-group>
        </el-form-item>
        <p class="com-tit">{{lang.system_config}}</p>
        <!-- 镜像 -->
        <el-form-item :label="lang.common_cloud_text5" class="image" id="image">
          <div class="image-box">
            <div class="image-ul">
              <div class="image-item" v-for="(item,index) in calcImageList" :key="item.id" :class="{active: curImage===index}" @click="changeImage(item,index)" @mouseenter="mouseenter(item.id)" @mouseleave="hover = false">
                <img :src="`/plugins/server/mf_dcim/template/clientarea/img/mf_dcim/${item.icon}.svg`" alt="" class="icon" />
                <div class="r-info">
                  <p class="name">{{item.name}}</p>
                  <p class="version">{{curImageId === item.id ? version:
                          lang.choose_version}}
                  </p>
                </div>
                <div class="version-select" v-show="(curImage === index) && hover">
                  <div class="v-item" :class="{active: ver.id === params.image_id}" v-for="(ver,v) in item.image" :key="ver.id" @click="chooseVersion(ver,item.id)">
                    <el-popover placement="right" trigger="hover" :disabled="ver.name.length < 20" popper-class="image-pup" :content="ver.name">
                      <span slot="reference">{{ver.name}}</span>
                    </el-popover>
                  </div>
                </div>
              </div>
            </div>
            <div class="empty-image" v-if="calcImageList.length === 4 && isHide" @click="isHide = false">
              <i class="el-icon-arrow-down"></i>
            </div>
          </div>
          <p class="s-tip" v-if="imageName">{{imageName && (imageName.indexOf('Win') !== -1 ? lang.mf_tip26 :
                    lang.mf_tip27)}}
          </p>
          <span class="error-tip" v-show="showImage">{{lang.mf_tip6}}</span>
        </el-form-item>
        <!-- 其他配置 -->
        <template v-if="isLogin">
          <p class="com-tit">{{lang.other_config}}</p>
          <el-form-item :label="lang.login_way">
            <el-radio-group v-model="login_way">
              <el-radio-button :label="lang.auto_create"></el-radio-button>
            </el-radio-group>
            <p class="s-tip" v-if="login_way === lang.auto_create">{{lang.mf_tip5}}</p>
          </el-form-item>
          <el-form-item class="optional">
            <template slot="label">
              {{lang.cloud_name}}
              <el-tooltip class="item" effect="light" :content="lang.mf_tip14" placement="top">
                <i class="el-icon-warning-outline"></i>
              </el-tooltip>
            </template>
            <el-input v-model="params.notes" :placeholder="lang.mf_tip15"></el-input>
          </el-form-item>
          <el-form-item :label="lang.auto_renew" class="renew">
            <el-checkbox v-model="params.auto_renew">{{lang.open_auto_renew}}</el-checkbox>
          </el-form-item>
        </template>
      </el-form>
    </div>
  </div>
  <!-- 底部 -->
  <div class="f-order">
    <div class="l-empty"></div>
    <div class="el-main">
      <div class="main-card">
        <div class="left">
          <div class="time">
            <span class="l-txt">{{lang.mf_time}}</span>
            <el-select v-model="params.duration_id" class="duration-select" popper-class="duration-pup" :visible-arrow="false" :placeholder="`${lang.placeholder_pre2}${lang.mf_duration}`" @change="changeDuration">
              <el-option v-for="item in cycleList" :key="item.id" :label="item.name" :value="item.id">
                <span class="txt">{{item.name}}</span>
                <span class="tip" v-if="item.discount">{{item.discount}}{{lang.mf_tip25}}</span>
              </el-option>
            </el-select>
          </div>
          <div class="num">
            <span class="l-txt">{{lang.shoppingCar_goodsNums}}</span>
            <el-input-number v-model="qty" :min="1" :max="999" @change="changQty"></el-input-number>
          </div>
        </div>
        <div class="mid">
          <el-popover placement="top" trigger="hover" popper-class="cur-content">
            <div class="content">
              <div class="tit">{{lang.mf_tip7}}</div>
              <div class="con">
                <p class="c-item">
                  <span class="l-txt">{{lang.cloud_table_head_1}}：</span>
                  {{calcArea}}
                </p>
                <p class="c-item">
                  <span class="l-txt">{{lang.usable_area}}：</span>
                  {{calcUsable}}
                </p>
                <p class="c-item">
                  <span class="l-txt">{{lang.mf_specs}}：</span>
                  {{calcSpecs}}
                </p>
                <p class="c-item">
                  <span class="l-txt">{{lang.common_cloud_title3}}：</span>
                  {{this.params.ip_num}}{{lang.mf_one}}
                </p>
                <p class="c-item">
                  <span class="l-txt">{{lang.ip_line}}：</span>
                  {{calcLine}}
                </p>
                <p class="c-item" v-if="lineType === 'bw'">
                  <span class="l-txt">{{lang.mf_bw}}：</span>
                  {{params.bw ? params.bw + 'Mbps': '--'}}
                </p>
                <p class="c-item">
                  <span class="l-txt">{{lang.cloud_menu_5}}：</span>
                  {{version || '--'}}
                </p>
                <p class="c-item" v-if="lineType === 'flow'">
                  <span class="l-txt">{{lang.mf_flow}}：</span>
                  {{params.flow ? params.flow + 'GB': (params.flow === 0 ? lang.mf_tip28:'--')}}
                </p>
                <p class="c-item" v-if="lineDetail.defence && params.peak_defence">
                  <span class="l-txt">{{lang.peak_defence}}：</span>
                  {{ params.peak_defence + 'G'}}
                </p>
              </div>
            </div>
            <a class="link" slot="reference">{{lang.cur_config}}</a>
          </el-popover>
          <div class="line-empty"></div>
          <el-popover placement="top" trigger="hover" popper-class="free-content">
            <div class="content">
              <div class="tit">{{lang.config_free_details}}</div>
              <div class="con">
                <p class="c-item" v-for="(item,index) in preview" :key="index">
                  <span class="l-txt">{{item.name}}：{{item.value}}</span>
                  <span class="price">{{commonData.currency_prefix}}{{item.price}}</span>
                </p>
              </div>
              <div class="bot">
                <p class="c-item" v-if="discount || levelNum">
                  <span class="l-txt">{{lang.mf_discount}}：</span>
                  <span class="price">-{{commonData.currency_prefix}}{{(discount * 1 + levelNum * 1 >= totalPrice * 1 * qty ? totalPrice * 1  * qty : discount * 1 + levelNum * 1).toFixed(2)}}</span>
                </p>
                <p class="c-item">
                  <span class="l-txt">{{lang.mf_total}}：</span>
                  <span class="price">{{commonData.currency_prefix}}{{calcTotalPrice}}</span>
                </p>
              </div>
            </div>
            <a class="link" slot="reference">{{lang.config_free}}</a>
          </el-popover>
          <div class="bot-price" v-loading="loadingPrice">
            <div class="new">{{commonData.currency_prefix}}<span>{{calcTotalPrice}}</span>
              <el-popover placement="top" width="200" trigger="hover" v-if="levelNum || discount" popper-class="level-pup">
                <div class="show-config-list">
                  <p v-if="levelNum">{{lang.shoppingCar_tip_text2}}：{{commonData.currency_prefix}} {{ levelNum |
                            filterMoney }}</p>
                  <p v-if="discount">{{lang.shoppingCar_tip_text4}}：{{commonData.currency_prefix}} {{ discount |
                            filterMoney }}</p>
                </div>
                <i class="el-icon-warning-outline total-icon" slot="reference"></i>
              </el-popover>
            </div>
            <div class="old">
              <div class="show" v-if="discount || levelNum ">
                {{commonData.currency_prefix}}{{(totalPrice * 1 * qty).toFixed(2)}}
              </div>
              <!-- 优惠码 -->
              <!-- 未使用 -->
              <el-popover placement="top" trigger="click" popper-class="discount-pup" v-model="dis_visible" v-if="!discount">
                <div class="discount">
                  <img src="/plugins/server/mf_dcim/template/clientarea/img/common/close_icon.png" alt="" class="close" @click="dis_visible = !dis_visible">
                  <div class="code">
                    <el-input v-model="promo.promo_code" :placeholder="`${lang.placeholder_pre1}${lang.cloud_code}`"></el-input>
                    <button class="sure" @click="useDiscount">{{lang.referral_btn6}}</button>
                  </div>
                  <span class="error-tip" v-show="showErr">{{lang.mf_tip8}}</span>
                </div>
                <p class="use" slot="reference" v-show="hasDiscount">{{lang.use_discount}}</p>
              </el-popover>
              <!-- 已使用 -->
              <div class="used" v-else>
                <span>{{promo.promo_code}}</span>
                <i class="el-icon-circle-close" @click="canclePromo"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="right">
          <el-popover placement="top" trigger="hover" popper-class="cart-pup" :content="calcCartName">
            <div class="add-cart" slot="reference" @click="handlerCart">
              <img src="/plugins/server/mf_dcim/template/clientarea/img/common/cart.svg" alt="">
            </div>
          </el-popover>
          <div class="buy" @click="submitOrder">{{lang.product_buy_now}}</div>
        </div>
      </div>
    </div>

  </div>
  <el-dialog title="" :visible.sync="cartDialog" custom-class="cartDialog" :show-close="false">
    <span class="tit">{{lang.product_tip}}</span>
    <span slot="footer" class="dialog-footer">
      <el-button type="primary" @click="cartDialog = false">{{lang.product_continue}}</el-button>
      <el-button @click="goToCart">{{lang.product_settlement}}</el-button>
    </span>
  </el-dialog>
</div>
<!-- =======页面独有======= -->
<script src="/plugins/server/mf_dcim/template/clientarea/api/common.js"></script>
<script src="/plugins/server/mf_dcim/template/clientarea/api/mf_dcim.js"></script>
<script src="/plugins/server/mf_dcim/template/clientarea/utils/util.js"></script>
<script src="/plugins/server/mf_dcim/template/clientarea/js/mf_dcim.js"></script>