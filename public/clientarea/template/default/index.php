{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/home.css">
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
        <top-menu></top-menu>
        <el-main>
          <!-- 自己的东西 -->
          <div class="main-card">
            <div class="main-content">
              <div class="left-box">
                <div class="info-box">
                  <div class="info-first" @click="goUser" v-loading="nameLoading">
                    <div class="name-first" ref="headBoxRef">
                      {{account.firstName}}
                    </div>
                    <div class="name-box">
                      <p class="hello">{{lang.index_hello}}</p>
                      <p class="name">{{account.username}}</p>
                    </div>
                  </div>
                  <el-divider class="divider-box" direction="vertical"></el-divider>
                  <div class="info-second" v-loading="nameLoading">
                    <div class="email-box">
                      <span><img src="/{$template_catalog}/template/{$themes}/img/home/email-icon.png" alt="">{{lang.index_email}}</span>
                      <span class="phone-number">{{account.email ? account.email : '--'}}</span>
                    </div>
                    <div class="phone-box">
                      <span><img src="/{$template_catalog}/template/{$themes}/img/home/tel-icon.png" alt="">{{lang.index_tel}}</span>
                      <span class="phone-number">{{account.phone ? account.phone : '--'}}</span>
                    </div>
                  </div>
                  <el-divider class="divider-box" direction="vertical"></el-divider>
                  {foreach $addons as $addon}
                  {if ($addon.name=='IdcsmartCertification')}
                  {php}$PluginModel=new app\admin\model\PluginModel();$config=$PluginModel->where('name','IdcsmartCertification')->value('config');$config=json_decode($config,true);{/php}
                  {if (isset($config.certification_open) && $config.certification_open)}
                  <div class="info-three" v-if="certificationObj.certification_open === 1">
                    <div class="email-box">
                      <span><img src="/{$template_catalog}/template/{$themes}/img/home/compny-icon.png" alt="">{{lang.index_compny}}</span>
                      <span class="company-name" v-if="certificationObj.company?.status === 1">{{certificationObj.company.certification_company}}</span>
                      <span class="company-name bule-text" @click="handelAttestation({$addon.id})" v-else>{{lang.index_goAttestation}}</span>
                    </div>
                    <div class="phone-box">
                      <span><img src="/{$template_catalog}/template/{$themes}/img/home/person-icon.png" alt="">{{lang.index_name}}</span>
                      <span class="company-name" v-if="certificationObj.is_certification">{{certificationObj.company.status === 1 ? certificationObj.company.card_name : certificationObj.person.card_name}}</span>
                      <span class="company-name bule-text" @click="handelAttestation({$addon.id})" v-else>{{lang.index_goAttestation}}</span>
                    </div>
                  </div>
                  {/if}
                  {/if}
                  {/foreach}
                </div>
                <div class="statistics-box">
                  <h3 class="title-text">{{lang.index_text1}}</h3>
                  <div class="statistics-content" v-loading="nameLoading">
                    <div class="money-box">
                      <div class="statistics-top">
                        <div class="statisticstop-left">
                          <div class="credit-box">
                            <div class="statistics-credit">￥{{account.credit}}</div>
                            <div class="recharge-btn" @click="showCz">{{lang.index_text2}}</div>
                          </div>
                          <div class="griy-12">{{lang.index_text3}}</div>
                        </div>
                        <div class="statisticstop-right">
                          <img src="/{$template_catalog}/template/{$themes}/img/home/pic-1.png" alt="">
                        </div>
                      </div>
                      <div class="statistics-bottom">
                        <div class="progress-box">
                          <el-progress type="circle" :width="Number(117)" :stroke-width="Number(12)" color='#04C8C9' :show-text="false" :percentage="percentage"></el-progress>
                        </div>
                        <div class="statistics-bottom-right">
                          <div class="money-month">
                            <div>
                              <span class="type-box green-bg"></span>
                              <span>{{lang.index_text4}}
                                <span v-if="Number(account.this_month_consume_percent) >= 0" class="percent-box-green">↑{{Number(account.this_month_consume_percent)}}%</span>
                                <span v-else class="percent-box-red">↓{{Number(account.this_month_consume_percent) *-1}}%</span>
                              </span>
                            </div>
                            <div class="money-num">￥{{account.this_month_consume}}</div>
                          </div>
                          <div class="money-total">
                            <div><span class="type-box grey-bg"></span><span>{{lang.index_text5}}</span></div>
                            <div class="money-num">￥{{account.consume}}</div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="order-box">
                      <div class="order-item order-box-1">
                        <div class="order-type-img">
                          <img src="/{$template_catalog}/template/{$themes}/img/home/activation-icon.png" alt="">
                        </div>
                        <h3 class="order-title">{{lang.index_text6}}</h3>
                        <div class="order-nums">{{account.host_active_num}}</div>
                      </div>
                      <div class="order-item order-box-2">
                        <div class="order-type-img">
                          <img src="/{$template_catalog}/template/{$themes}/img/home/prduct-icon.png" alt="">
                        </div>
                        <h3 class="order-title">{{lang.index_text7}}</h3>
                        <div class="order-nums">{{account.host_num}}</div>
                      </div>
                      <div class="order-item order-box-3">
                        <div class="order-type-img">
                          <img src="/{$template_catalog}/template/{$themes}/img/home/no-pay-order.png" alt="">
                        </div>
                        <h3 class="order-title">{{lang.index_text8}}</h3>
                        <div class="order-nums">{{account.unpaid_order}}</div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="product-list-box">
                  <h3 class="title-text">{{lang.index_text9}}</h3>
                  <div class="goods-box" v-loading="productListLoading">
                    <table class="goods-table">
                      <thead>
                        <tr>
                          <td>{{lang.index_text10}}</td>
                          <td>{{lang.index_text11}}</td>
                          <td>{{lang.index_text12}}</td>
                          <td class="time-box">{{lang.index_text13}}</td>
                        </tr>
                      </thead>
                      <tbody v-if="productList.length !== 0">
                        <tr v-for="item in productList" :key="item.id" class="product-item" @click="goProductPage(item.id)">
                          <td>{{item.product_name}}</td>
                          <td>{{item.type ? item.type : '--'}}</td>
                          <td>{{item.name}}</td>
                          <td :class="item.isOverdue ? 'red-time' : ''">{{item.due_time | formateTime}}</td>
                        </tr>
                      </tbody>
                    </table>
                    <div v-if="productList.length === 0 && !productListLoading" class="no-product">
                      <h2>{{lang.index_text14}}</h2>
                      <p>{{lang.index_text15}}</p>
                      <el-button @click="goGoodsList">{{lang.index_text16}}</el-button>
                    </div>
                  </div>
                </div>
              </div>
              <div class="right-box">
                <!-- 推介计划开始 -->
                {foreach $addons as $addon}
                {if ($addon.name=='IdcsmartRecommend')}
                <div class="recommend-box-open" v-if="showRight && isOpen">
                  <div class="recommend-top">
                    <div class="left">
                      <div class="row1">
                        <div class="title-text">{{lang.referral_title1}}</div>
                        <span class="reword" @click="toReferral({$addon.id})"><img src="/{$template_catalog}/template/{$themes}/img/home/reword.png" alt="">{{lang.referral_text14}}</span>
                      </div>
                      <div class="row2">{{lang.referral_title6}}</div>
                      <div class="row3">{{lang.referral_text15}}</div>
                      <div class="row4">{{lang.referral_text16}}</div>
                    </div>
                    <img class="right" src="/{$template_catalog}/template/{$themes}/img/home/credit-card.png" alt="">
                  </div>
                  <div class="url">
                    <div class="url-text" :title="promoterData.url">{{promoterData.url}}</div>
                    <div class="copy-btn" @click="copyUrl(promoterData.url)">{{lang.referral_btn2}}</div>
                  </div>
                  <div class="top-statistic">
                    <div class="top-item">
                      <div class="item-top">
                        <div class="top-money">{{commonData.currency_prefix}}{{promoterData.withdrawable_amount}}</div>
                        <div class="top-text">{{lang.referral_title2}}</div>
                      </div>
                      <img class="top-img" src="/{$template_catalog}/template/{$themes}/img/referral/top1.png" />
                    </div>
                    <div class="top-item">
                      <div class="item-top">
                        <div class="top-money">{{commonData.currency_prefix}}{{promoterData.pending_amount}}
                          <!-- <div class="icon-help" :title="`${lang.referral_text7}：${commonData.currency_prefix}${promoterData.frozen_amount}`">?</div> -->
                        </div>
                        <div class="top-text">{{lang.referral_title4}}</div>
                      </div>
                      <img class="top-img" src="/{$template_catalog}/template/{$themes}/img/referral/top3.png" />
                    </div>
                  </div>
                </div>
                <div class="recommend-box" v-else>
                  <img src="/{$template_catalog}/template/{$themes}/img/home/recommend-img.png" alt="">
                    <div v-if="showRight">
                      <h2>{{lang.index_text17}}</h2>
                      <p>{{lang.index_text18}}</p>
                      <div class="no-recommend" @click="openVisible = true">立刻开启</div>
                    </div>
                    <div v-else class="recommend-text">{{lang.index_text21}}</div>
                </div>
                {/if}
                {/foreach}
                <div class="recommend-box" v-if="!showRight || !isOpen">
                  <img src="/{$template_catalog}/template/{$themes}/img/home/recommend-img.png" alt="">
                    <div v-if="showRight">
                      <h2>{{lang.index_text17}}</h2>
                      <p>{{lang.index_text18}}</p>
                      <div class="no-recommend" @click="openVisible = true">立刻开启</div>
                    </div>
                    <div v-else class="recommend-text">{{lang.index_text21}}</div>
                </div>
                <!-- 推介计划结束 -->
                {foreach $addons as $addon}
                {if ($addon.name=='IdcsmartTicket')}
                <div class="WorkOrder-box" v-if="ticketList.length !==0 ">
                  <div class="title-text WorkOrder-title">
                    <div>{{lang.index_text22}}</div>
                    <div class="more" @click="goWorkPage({$addon.id})">···</div>
                  </div>
                  <div class="WorkOrder-content">
                    <div class="WorkOrder-item" v-for="item in ticketList" :key="item.id">
                      <div class="replay-div" :class="item.status === 'Reply' ? 'replay-red' : item.status === 'Pending' ? 'replay-green' : ''">{{item.statusText}}</div>
                      <div class="replay-box">
                        <div class="replay-title">{{item.title}}</div>
                        <div class="replay-name">{{item.name}}</div>
                      </div>
                    </div>
                  </div>
                </div>
                {/if}
                {/foreach}
                {foreach $addons as $addon}
                {if ($addon.name=='IdcsmartNews')}
                <div class="notice-box" v-if="homeNewList.length !==0">
                  <div class="title-text WorkOrder-title">
                    <div>{{lang.index_text23}}</div>
                    <div class="more" @click="goNoticePage({$addon.id})">···</div>
                  </div>
                  <div class="WorkOrder-content">
                    <div v-for="item in homeNewList" :key="item.id" class="notice-item" @click="goNoticeDetail({$addon.id},item.id)">
                      <div class="notice-item-left">
                        <h3 class="notice-time">{{item.create_time | formareDay}}</h3>
                        <h4 class="notice-title">{{item.title}}</h4>
                        <h5 class="notice-type">{{item.type}}</h5>
                      </div>
                      <div class="notice-item-right"><i class="el-icon-arrow-right"></i></div>
                    </div>
                  </div>
                </div>
                {/if}
                {/foreach}
              </div>
            </div>
          </div>
          <!-- 充值 dialog -->
          <div class="cz-dialog">
            <el-dialog width="6.8rem" :visible.sync="isShowCz" :show-close=false @close="czClose">
              <div class="dialog-title">{{lang.index_text24}}</div>
              <div class="dialog-form">
                <el-form :model="czData" label-position="top">
                  <el-form-item :label="lang.index_text25">
                    <el-select v-model="czData.gateway" @change="czSelectChange" class="ty-select">
                      <el-option v-for="item in gatewayList" :key="item.id" :label="item.title" :value="item.name"></el-option>
                    </el-select>
                  </el-form-item>
                  <el-form-item :label="lang.index_text26" @keyup.native="czData.amount=oninput(czData.amount)">
                    <div class="cz-input">
                      <el-input v-model="czData.amount"></el-input>
                      <el-button class="btn-ok" @click="czInputChange">{{lang.index_text27}}</el-button>
                    </div>
                  </el-form-item>
                  <el-form-item v-if="errText">
                    <el-alert :title="errText" type="error" :closable="false" show-icon></el-alert>
                  </el-form-item>
                  <el-form-item v-loading="payLoading1">
                    <div class="pay-html" v-show="isShowimg1" v-html="payHtml"></div>
                  </el-form-item>
                </el-form>
              </div>
            </el-dialog>
          </div>
          <!-- 确认开启弹窗 -->
          <el-dialog :title="lang.referral_title8" :visible.sync="openVisible" width="4.8rem" custom-class="open-dialog">
            <span>{{lang.referral_tips7}}</span>
            <span slot="footer" class="dialog-footer">
              <el-button class="btn-ok" type="primary" @click="openReferral">{{lang.referral_btn6}}</el-button>
              <el-button class="btn-no" @click="openVisible = false">{{lang.referral_btn7}}</el-button>
            </span>
          </el-dialog>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/finance.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/home.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/home.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>

  {include file="footer"}