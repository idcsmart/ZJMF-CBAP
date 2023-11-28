{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common_product_detail.css">
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
  <div class="template common_product_detail">
    <el-container>
      <aside-menu></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <el-main>
          <!-- 自己的东西 -->
          <div class="main-card">
            <div class="main-card-title">
              <img :src="`${baseUrl}/img/finance/back.png`" alt="" @click="back" class="back">
              <span class="title">{{host.name}}</span>
              <span class="tag" v-if="host?.status" :style="'color:'+status[host.status]?.color + ';background:' + status[host.status]?.bgColor">{{status[host.status]?.text}}
              </span>
            </div>
            <!-- 财务信息 -->
            <p class="stop-info">
              <span class="info" v-if="(refundInfo?.status === 'Suspending' || refundInfo?.status === 'Suspend' || refundInfo?.status === 'Suspended') && refundInfo.type === 'Expire'">(
                产品于{{refundInfo.create_time | formateTime}}申请 到期停用 ，于{{host.due_time | formateTime}}自动删除
                )</span>
            </p>
            <p class="stop-info">
              <span class="info reject" v-if="refundInfo?.status === 'Reject'">
                （产品于{{refundInfo.create_time | formateTime}}申请 立即停用 失败，
                <el-tooltip class="item" :content="refundInfo.reject_reason" placement="top">
                  <a href="javascript:;">查看原因</a>
                </el-tooltip>

                ）
              </span>
            </p>
            <div class="finance-info">
              <p class="s-tit">财务信息</p>
              <div class="box">
                <div class="item">
                  <span>订购时间：</span>
                  <span>{{host.create_time | formateTime}}</span>
                </div>
                <div class="item">
                  <span>计费方式：</span>
                  <span>{{payWay[host.billing_cycle]}}</span>
                </div>
                <div class="item">
                  <span>订购金额：</span>
                  <span>{{commonData.currency_prefix}}{{host.first_payment_amount}}{{commonData.currency_suffix}}/{{host.billing_cycle_name}}</span>
                </div>
                <div class="item">
                  <span>到期时间：</span>
                  <span>{{host.due_time | formateTime}}</span>
                </div>
                <div class="item">
                  <span>计费周期：</span>
                  <span>{{host.billing_cycle_name}}</span>
                </div>
                <div class="item">
                  <span>续费金额：</span>
                  <span>{{commonData.currency_prefix}}{{host.renew_amount}}{{commonData.currency_suffix}}/{{host.billing_cycle_name}}</span>
                  <!-- 非tingyong -->

                  <span class="renew btn" @click="showRenew" v-if="!refundInfo || (refundInfo && refundInfo.status=='Cancelled') || (refundInfo && refundInfo.status=='Reject')">{{lang.cloud_re_btn}}</span>
                  <span class="disabeld btn" v-else>{{lang.cloud_re_btn}}</span>
                  <span class="war btn" v-if="refundInfo && refundInfo.status != 'Cancelled' && refundInfo.status != 'Reject'">{{refundStatus[refundInfo.status]}}</span>
                  <span class="refund-stop-btn" v-if="refundInfo && refundInfo.status=='Pending'" @click="cancelRefund">取消停用</span>
                  <span class="cancel btn" @click="stop_use" v-if="!refundInfo || (refundInfo && (refundInfo.status=='Reject')) || (refundInfo && (refundInfo.status=='Cancelled'))">停用</span>

                  <!-- <template v-if="refundInfo?.status !== 'Pending'">
                    <span class="renew btn" @click="showRenew">续费</span>
                    <span class="stop btn" @click="stop_use" v-if=" refundInfo.status === 'Reject' || refundInfo.status === 'Cancelled'">停用</span>
                  </template>
                  <template v-else>
                    <span class="disabeld btn" v-if="!refundInfo || (refundInfo && refundInfo.status=='Cancelled') || (refundInfo && refundInfo.status=='Reject')">续费</span>
                    <span class="war btn" v-if="refundInfo && refundInfo.status != 'Cancelled' && refundInfo.status != 'Reject'">{{refundInfo.status}}</span>
                    <span class="cancel btn" @click="cancelRefund" v-if="refundInfo && refundInfo.status=='Pending'">取消停用</span>
                  </template> -->
                </div>
              </div>
            </div>
            <!-- 基础信息 -->
            <div class="basic-info">
              <p class="s-tit">基础信息</p>
              <div class="box">
                <div class="item" v-for="(item,index) in configoptions" :key="index">
                  <p class="name">{{item.option_name}}：</p>
                  <div class="r-info">
                    <span class="s-item" v-if="item.option_type === 'quantity' || item.option_type === 'quantity_range'">
                      {{item.qty}}
                    </span>
                    <span class="s-item" v-for="(el,ind) in item.subs" :key="ind">
                      <img :src="`/upload/common/country/${el.country}.png`" alt="" v-if="item.option_type ==='area'">
                      {{el.option_name}}
                    </span>
                  </div>
                  <span>{{item.unit}}</span>
                </div>
              </div>
            </div>


            <!-- 续费弹窗 -->
            <div class="renew-dialog">
              <el-dialog width="6.9rem" :visible.sync="isShowRenew" :show-close=false @close="renewDgClose">
                <div class="dialog-title">
                  续费
                </div>
                <div class="dialog-main">
                  <div class="renew-content">
                    <div class="renew-item" :class="renewActiveId==index?'renew-active':''" v-for="(item,index) in renewPageData" :key="index" @click="renewItemChange(item,index)">
                      <div class="item-top">{{item.billing_cycle}}</div>
                      <div class="item-bottom">{{commonData.currency_prefix + item.price}}</div>
                      <i class="el-icon-check check" v-show="renewActiveId==index"></i>
                    </div>
                  </div>
                  <div class="pay-content">
                    <div class="pay-price">
                      <div class="text">合计</div>
                      <div class="money">{{commonData.currency_prefix + renewParams.price}}</div>
                    </div>
                  </div>
                </div>
                <div class="dialog-footer">
                  <div class="btn-ok" @click="subRenew">确认续费</div>
                  <div class="btn-no" @click="renewDgClose">取消</div>
                </div>
              </el-dialog>
            </div>

            <!-- 停用弹窗 -->
            <el-dialog title="退款" :visible.sync="refundVisible" class="refundDialog" :show-close="false">
              <el-form :model="refundForm" label-position="top">
                <p class="tit">产品信息</p>
                <div class="info">
                  <div class="des">
                    <div class="item">
                      <p class="s-tit">产品配置:</p>
                      <div class="config">
                        <div class="s-item" v-for="(item,index) in configoptions" :key="index" style="display: flex;">
                          <p class="name">{{item.option_name}}：</p>
                          <div class="r-info">
                            <span class="s-item" v-if="item.option_type === 'quantity' || item.option_type === 'quantity_range'">
                              {{item.qty}}
                            </span>
                            <span class="s-item" v-for="(el,ind) in item.subs" :key="ind">
                              <img :src="`/upload/common/country/${el.country}.png`" alt="" v-if="item.option_type ==='area'" style="width: .2rem;">
                              {{el.option_name}}
                            </span>
                          </div>
                          <span>{{item.unit}}</span>
                        </div>
                      </div>
                    </div>
                    <div class="item">
                      <p class="s-tit">订购时间: </p>
                      <div class="config">
                        {{refundDialog?.host?.create_time | formateTime}}
                      </div>
                    </div>
                    <div class="item">
                      <p class="s-tit">订购金额: </p>
                      <div class="config">
                        {{commonData.currency_prefix}}{{refundDialog?.host?.first_payment_amount}}
                      </div>
                    </div>
                  </div>
                </div>
                <el-form-item label="停用原因">
                  <el-select v-model="refundForm.arr" v-if="!refundDialog.reason_custom" style="width: 100%;" multiple>
                    <el-option :label="item.content" :value="item.id" v-for="item in refundDialog.reasons" :key="item.id"></el-option>
                  </el-select>
                  <el-input v-model="refundForm.str" v-else placeholder="请输入退款原因"></el-input>
                </el-form-item>
                <el-form-item label="停用时间">
                  <el-select v-model="refundForm.type" @change="changeReson">
                    <el-option label="到期后" value="Expire"></el-option>
                    <el-option label="立即" value="Immediate"></el-option>
                  </el-select>
                </el-form-item>
              </el-form>
              <p class="tit">退款金额</p>
              <p class="money">{{commonData.currency_prefix}}{{refundMoney}}</p>
              <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click="submitRefund">确认退款</el-button>
                <el-button @click="refundVisible = false">取消</el-button>
              </div>
            </el-dialog>

            <!-- 不允许退款 -->

            <el-dialog title="" :visible.sync="noRefundVisible" class="no-allow" :show-close="false">
              <img :src="`${baseUrl}/img/common/close.png`" alt="">
              <span class="tit">退款失败!</span>
              <span class="des">非常抱歉，给您带来不便，该产品不支持退款，<br />若有疑问，请联系售后人员！</span>
              <span slot="footer" class="dialog-footer">
                <el-button type="primary" @click="noRefundVisible = false">确认</el-button>
              </span>
            </el-dialog>

            <!-- 支付弹窗 -->
            <pay-dialog ref="payDialog" @payok="paySuccess" @paycancel="payCancel"></pay-dialog>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/common_product.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/common_product_detail.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>

  {include file="footer"}