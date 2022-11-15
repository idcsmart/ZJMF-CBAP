<link rel="stylesheet" href="/plugins/server/idcsmart_common/template/clientarea/css/common_product_detail.css">

<div class="template common_product_detail">
  <!-- 自己的东西 -->
  <div class="main-card">
    <div class="main-card-title">
      <img :src="`${baseUrl}/img/finance/back.png`" alt="" @click="back" class="back">
      <span class="title">{{host.name}}</span>
      <span class="tag" v-if="hostData?.status" :style="'color:'+status[hostData.status]?.color + ';background:' + status[hostData.status]?.bgColor">{{status[hostData.status].text}}
      </span>
    </div>
    <!-- 备注 -->
    <div class="notes">
      <!-- 有备注 -->
      <span class="yes-notes" v-if="hostData.notes" @click="doEditNotes">
        <i class="el-icon-edit notes-icon"></i>
        <span class="notes-text">{{hostData.notes}}</span>
      </span>
      <!-- 无备注 -->
      <span class="no-notes" v-else @click="doEditNotes">
        {{lang.cloud_add_notes + ' +'}}
      </span>
    </div>
    <div class="finance-info">
      <div class="status-box">
        <p class="s-tit">{{lang.menu_3}}</p>
        <template>
          <!-- 财务信息 -->
          <p class="stop-info" v-if="(refundInfo?.status === 'Suspending' || refundInfo?.status === 'Suspend' || refundInfo?.status === 'Suspended') && refundInfo.type === 'Expire'">
            <span class="info">(
              产品于{{refundInfo.create_time | formateTime}}申请 到期停用 ，于{{host.due_time | formateTime}}自动删除
              )</span>
          </p>
          <p class="stop-info" v-if="refundInfo?.status === 'Reject'">
            <span class="info reject">
              （产品于{{refundInfo.create_time | formateTime}}申请 立即停用 失败，
              <el-tooltip class="item" :content="refundInfo.reject_reason" placement="top">
                <a href="javascript:;">查看原因</a>
              </el-tooltip>

              ）
            </span>
          </p>
        </template>
      </div>
      <div class="box">
        <div class="item">
          <span>订购时间：</span>
          <span>{{host.create_time | formateTime}}</span>
        </div>
        <div class="item">
          <span>订购金额：</span>
          <span>{{commonData.currency_prefix}}{{host.first_payment_amount}}{{commonData.currency_suffix}}</span>
        </div>
        <div class="item">
          <span>到期时间：</span>
          <span>{{host.due_time | formateTime}}</span>
        </div>
        <div class="item">
          <span>标识：</span>
          <span>{{hostData.name}}</span>
        </div>
        <div class="item">
          <span>续费金额：</span>
          <span>{{commonData.currency_prefix}}{{host.renew_amount}}{{commonData.currency_suffix}}/{{host.billing_cycle_name}}</span>
          <!-- 非tingyong -->

          <template v-if="host?.status==='Active'">
            {foreach $addons as $addon}
            {if ($addon.name=='IdcsmartRenew')}
            <span class="renew btn" @click="showRenew" v-if="!refundInfo || (refundInfo && refundInfo.status=='Cancelled') || (refundInfo && refundInfo.status=='Reject')">{{lang.cloud_re_btn}}</span>
            <span class="disabeld btn" v-else>{{lang.cloud_re_btn}}</span>
            {/if}
            {/foreach}
            {foreach $addons as $addon}
            {if ($addon.name=='IdcsmartRefund')}
            <span class="war btn" v-if="refundInfo && refundInfo.status != 'Cancelled' && refundInfo.status != 'Reject'">{{refundStatus[refundInfo.status]}}</span>
            <!-- <span class="war btn refund-stop-btn" v-if="refundInfo && refundInfo.status=='Pending'" @click="cancelRefund">取消停用</span> -->
            <span class="war btn refund-stop-btn" v-if="refundInfo && (refundInfo.status=='Pending' || refundInfo.status=='Suspend' || refundInfo.status=='Suspending')" @click="cancelRefund">取消停用</span>
            <span class="cancel btn" @click="stop_use" v-if="!refundInfo || (refundInfo && (refundInfo.status=='Reject')) || (refundInfo && (refundInfo.status=='Cancelled'))">停用</span>
            {/if}
            {/foreach}
          </template>

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
        {foreach $addons as $addon}
        {if ($addon.name=='IdcsmartRenew')}
        <div class="item">
          <span>自动续费：</span>
          <el-switch :value="isShowPayMsg" active-color="#0052D9" :active-value="1" :inactive-value="0" @change="changeAutoStatus">
          </el-switch>
          <el-popover placement="top" trigger="hover">
            <div class="sure-remind">
              开启自动续费后，即将到期时不再发送续费通知，而是检测余额是否充足，余额充足时将自动续费
            </div>
            <div class="help" slot="reference">?</div>
          </el-popover>
        </div>
        {/if}
        {/foreach}
        <div class="item">
          <span>优惠码：</span>
          <template v-if="promo_code.length > 0">
            <span v-for="(item,index) in promo_code" :key="item">{{item}}</span>
            <span v-if="index !== 0">;</span>
          </template>
          <span v-else>--</span>
        </div>
      </div>
    </div>
    <!-- 基础信息 -->
    <div class="basic-info">
      <p class="s-tit">基础信息</p>
      <div class="box">
        <div class="item" v-for="(item,index) in configoptions" :key="index" :class="{last_item: configoptions % 2 !== 0 && index === configoptions.length - 2}">

          <p @mouseenter="e=>checkWidth(e,index)" @mouseout="hideTip(index)" v-if="!item.show" class="name">
            {{item.option_name}}
          </p>
          <el-tooltip class="item" :content="item.option_name" placement="top" v-if="item.show">
            <p class="name">{{item.option_name}}</p>
          </el-tooltip>

          <div class="r-info">
            <span class="s-item" v-if="item.option_type === 'quantity' || item.option_type === 'quantity_range'">
              {{item.qty}}
            </span>
            <span class="s-item" v-for="(el,ind) in item.subs" :key="ind">
              <img :src="`/upload/common/country/${el.country}.png`" alt="" v-if="item.option_type ==='area'">
              <template v-if="item.option_type ==='area'">
                {{filterCountry(el.country)}}&nbsp;-
              </template>
              {{el.option_name}}
            </span>
            <span>{{item.unit}}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 续费弹窗 -->
    <div class="renew-dialog">
      <el-dialog width="6.9rem" :visible.sync="isShowRenew" :show-close=false @close="renewDgClose">
        <div class="dialog-title">续费</div>
        <div class="dialog-main">
          <div class="renew-content">
            <div class="renew-item" :class="renewActiveId==index?'renew-active':''" v-for="(item,index) in renewPageData" :key="index" @click="renewItemChange(item,index)">
              <div class="item-top">{{item.billing_cycle}}</div>
              <div class="item-bottom">{{commonData.currency_prefix + item.price}}</div>
              <i class="el-icon-check check" v-show="renewActiveId==index"></i>
            </div>
          </div>
        </div>
        <div class="pay-content">
            <div class="pay-price">
                <div class="money" v-loading="renewLoading">
                    <span class="text">{{lang.common_cloud_label11}}:</span><span>{{commonData.currency_prefix}}{{renewParams.totalPrice | filterMoney}}</span>
                    <el-popover placement="top-start" width="200" trigger="hover" v-if="isShowLevel || (isShowPromo && isUseDiscountCode) || isShowCash">
                      <div class="show-config-list">
                        <p v-if="isShowLevel">{{lang.shoppingCar_tip_text2}}：{{commonData.currency_prefix}} {{ renewParams.clDiscount | filterMoney}}</p>
                        <p v-if="isShowPromo && isUseDiscountCode">{{lang.shoppingCar_tip_text4}}：{{commonData.currency_prefix}} {{ renewParams.code_discount | filterMoney }}</p>
                        <p v-if="isShowCash && customfield.voucher_get_id">代金券抵扣金额：{{commonData.currency_prefix}} {{ renewParams.cash_discount | filterMoney}}</p>
                      </div>
                      <i class="el-icon-warning-outline total-icon" slot="reference"></i>
                  </el-popover>
                  <p class="original-price" v-if="renewParams.totalPrice != renewParams.original_price">{{commonData.currency_prefix}} {{ renewParams.original_price | filterMoney}}</p>
                  <div class="code-box">
                    <!-- 代金券 -->
                    <cash-coupon ref="cashRef" v-show=" isShowCash && !cashObj.code" :currency_prefix="commonData.currency_prefix" @use-cash="reUseCash" scene='renew' :product_id="[product_id]" :price="renewParams.original_price"></cash-coupon>
                    <!-- 优惠码 -->
                    <discount-code v-show="isShowLevel && !customfield.promo_code" @get-discount="getDiscount(arguments)" scene='renew' :product_id="product_id" :amount="renewParams.original_price" :billing_cycle_time="renewParams.duration"></discount-code>
                  </div>
                  <div class="code-number-text">
                    <div class="discount-codeNumber" v-show="customfield.promo_code">{{ customfield.promo_code }}<i class="el-icon-circle-close remove-discountCode" @click="removeDiscountCode()"></i></div>
                    <div class="cash-codeNumber" v-show="cashObj.code">{{ cashObj.code }}<i class="el-icon-circle-close remove-discountCode" @click="reRemoveCashCode()"></i></div>
                  </div>
                </div>
            </div>
        </div>
        <div class="dialog-footer">
          <el-button class="btn-ok" @click="subRenew" :loading="loading">确认续费</el-button>
          <div class="btn-no" @click="renewDgClose">取消</div>
        </div>
      </el-dialog>
    </div>

    <!-- 停用弹窗 -->
    <el-dialog :title="refundDialog.allow_refund == 1?'退款':'停用'" :visible.sync="refundVisible" class="refundDialog" :show-close="false">
      <el-form :model="refundForm" label-position="top">
        <p class="tit">产品信息</p>
        <div class="info">
          <div class="des">
            <div class="item">
              <p class="s-tit">产品配置：</p>
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
            <div class="item" v-if="refundDialog.allow_refund == 1">
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
          <el-input v-model="refundForm.str" v-else placeholder="请输入停用原因"></el-input>
        </el-form-item>
        <el-form-item label="停用时间">
          <el-select v-model="refundForm.type" @change="changeReson">
            <el-option label="到期后" value="Expire"></el-option>
            <el-option label="立即" value="Immediate"></el-option>
          </el-select>
        </el-form-item>
      </el-form>
      <p class="tit" v-if="refundDialog.allow_refund == 1">退款金额</p>
      <p class="money" v-if="refundDialog.allow_refund == 1">{{commonData.currency_prefix}}{{refundMoney}}</p>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click="submitRefund" :loading="loading">{{refundDialog.allow_refund == 1?'确认退款':'确认停用'}}</el-button>
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
    <!-- 修改备注弹窗 -->
    <div class="notes-dialog">
      <el-dialog width="6.2rem" :visible.sync="isShowNotesDialog" :show-close=false @close="notesDgClose">
        <div class="dialog-title">
          {{hostData.notes?'修改备注':'添加备注'}}
        </div>
        <div class="dialog-main">
          <div class="label">备注</div>
          <el-input class="notes-input" v-model="notesValue"></el-input>
        </div>
        <div class="dialog-footer">
          <div class="btn-ok" @click="subNotes">提交</div>
          <div class="btn-no" @click="notesDgClose">取消</div>
        </div>
      </el-dialog>
    </div>

    <!-- 自动续费 -->
    <el-dialog :visible.sync="dialogVisible" width="6.2rem;" custom-class="autoDialog" :show-close="false">
      <span class="title">{{autoTitle}}</span>
      <div class="dialog-main">
        <div class="label">主机名</div>
        <div class="value">{{hostData.name}}</div>
      </div>
      <div slot="footer" class="dialog-footer">
        <div type="primary" @click="changeAuto" class="btn-ok">提交</div>
        <div @click="dialogVisible = false" class="btn-no">取消</div>
      </div>
    </el-dialog>
  </div>
</div>
<!-- =======页面独有======= -->
<script src="/plugins/server/idcsmart_common/template/clientarea/api/common_config.js"></script>
<script src="/plugins/server/idcsmart_common/template/clientarea/utils/util.js"></script>
<script src="/plugins/server/idcsmart_common/template/clientarea/js/common_product_detail.js"></script>