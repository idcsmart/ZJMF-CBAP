{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/buyDomain.css">
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
            <div class="page-title">
              <div class="back-btn" @click="goBack">
                <img src="/{$template_catalog}/template/{$themes}/img/finance/back.png" alt="">
              </div>
              <div class="title">{{lang.template_text65}}</div>
            </div>
            <div class="whios-title">
              <el-divider direction="vertical"></el-divider>
              <span>{{lang.template_text66}}</span>
            </div>
            <div class="car-box">
              <div class="car-tips">{{lang.template_text67}}</div>
              <div class="car-list" v-loading="isCarLoading">
                <div class="car-item" v-for="(item,index) in carList" :key="index">
                  <div class="domain-name">{{item.config_options.domain}}</div>
                  <div class="domain-year">
                    <el-select v-model="item.selectYear" @change="(val)=>changeCart(val,item)">
                      <el-option v-for="items in item.priceArr" :key="items.buyyear" :label="items.buyyear + lang.common_cloud_text112" :value="items.buyyear">
                      </el-option>
                    </el-select>
                  </div>
                  <div class="domain-price" v-loading="item.priceLoading">{{priceCalc(item)}}{{commonData.currency_suffix}}</div>
                  <div class="domain-sel" @click="deleteCart(item)">{{lang.common_cloud_btn25}}</div>
                </div>
              </div>
            </div>
            <div class="whios-title">
              <el-divider direction="vertical"></el-divider>
              <span>{{lang.domain_template}}</span>
            </div>
            <div class="car-box">
              <div class="car-tips">{{lang.template_text68}}</div>
              <div class="info-search">
                <el-select v-model="templateParams.type" @change="getTemplateList">
                  <el-option :label="lang.template_text69" :value="''"></el-option>
                  <el-option :label="lang.template_text70" value="personal"></el-option>
                  <el-option :label="lang.template_text71" value="enterprise"></el-option>
                </el-select>
                <el-input @keyup.enter.native="getTemplateList" class="input-tem" :placeholder="lang.template_text72" v-model="templateParams.keywords">
                  <i slot="suffix" style="cursor: pointer;" class="el-input__icon el-icon-search" @click="getTemplateList"></i>
                </el-input>
              </div>
              <div class="creat-tem" @click="goCreatTem"><i class="el-icon-circle-plus-outline"></i> {{lang.template_text73}}</div>
              <div class="info-list" v-loading="temLoding">
                <el-empty :image-size="200" :description="lang.template_text74" v-if="templateArr.length === 0"></el-empty>
                <template v-else>
                  <div class="info-item" v-for="(item,index) in templateArr" :key="item.id">
                    <div class="radio-box">
                      <el-radio v-model="templateId" :label="item.id"><span></span></el-radio>
                    </div>
                    <div class="info-name">{{item.zh_owner}}</div>
                    <div class="info-type">{{item.type === 'personal' ? lang.template_text70 : lang.template_text71}}</div>
                    <div class="info-email">{{item.email}}</div>
                    <div class="info-real" :class="item.status === 1 ? 'green-text' : 'red-text'">{{item.status === 0 ? lang.template_text75 : item.status === 1 ? lang.template_text76 : item.status === 2 ? lang.template_text77 : '--'}}</div>
                    <div class="info-detail" @click="lookItem(item)">{{lang.template_text78}}</div>
                  </div>
                </template>
              </div>
            </div>
            <div class="whios-title">
              <el-divider direction="vertical"></el-divider>
              <span>{{lang.template_text79}}</span>
            </div>
            <div class="domian-status">
              <div class="status-item" v-if="commonData.cron_due_renewal_first_swhitch === '1'">
                <el-checkbox v-model="autoRenew" :disabled="commonData.cron_due_renewal_first_swhitch !== '1'"></el-checkbox>
                <div class="status-text">
                  <p class="status-name">{{lang.template_text80}}</p>
                  <p class="status-tip">{{lang.template_text81}} {{commonData.cron_due_renewal_first_swhitch}} {{lang.template_text156}}</p>
                </div>
              </div>
              <div class="status-item">
                <el-checkbox v-model="autoUpload"></el-checkbox>
                <div class="status-text">
                  <p class="status-name">{{lang.template_text82}}</p>
                  <p class="status-tip">{{lang.template_text83}}</p>
                </div>
              </div>
            </div>
          </div>
        </el-main>
        <el-footer>
          <div class="footer-box">
            <div class="footer-left">
              <el-checkbox v-model="isAgree">
                <span class="agree-text">
                  {{lang.template_text84}}
                  <span class="a-text-blue" @click="openUrl(domainConfig.domain_register_agreement_url)">{{lang.template_text85}}</span>、
                  <span class="a-text-blue" @click="openUrl(domainConfig.domain_information_service_agreement_url)">{{lang.template_text86}}</span>
                </span>
              </el-checkbox>
            </div>
            <div class="footer-right">
              <div class="total-box">
                <div class="total-text">{{lang.template_text87}}：</div>
                <div class="money-text">{{commonData.currency_prefix}} {{totalMoneyCalc}}</div>
              </div>
              <el-button class="sub-button" @click="submitOrder" :loading="subLoading">{{lang.template_text88}}</el-button>
            </div>
          </div>
        </el-footer>
        <!-- 支付弹窗 -->
        <pay-dialog ref="payDialog" @payok="paySuccess" @paycancel="payCancel"></pay-dialog>
        <!-- 信息模板详情 -->
        <div class="temp-dialog">
          <el-dialog width="9rem" :visible.sync="isShowTemp" :show-close=false>
            <h3 class="tit">{{lang.info_template}}</h3>
            <div class="details">
              <table width="100%">
                <tr>
                  <td>{{lang.owner_zh_name}}：</td>
                  <td>{{infoDetails.zh_owner}}</td>
                  <td>{{lang.owner_en_name}}：</td>
                  <td>{{infoDetails.en_owner}}</td>
                </tr>
                <tr>
                  <td>{{lang.concat_zh_name}}：</td>
                  <td>{{infoDetails.zh_last_name}}{{infoDetails.zh_first_name}}</td>
                  <td>{{lang.concat_en_name}}：</td>
                  <td>{{infoDetails.en_last_name}}{{infoDetails.en_first_name}}</td>
                </tr>
                <tr>
                  <td>{{lang.concat_type}}：</td>
                  <td>
                    <span v-if="infoDetails.type === 'personal'">{{lang.personal}}</span>
                    <span v-if="infoDetails.type === 'enterprise'">{{lang.business}}</span>
                  </td>
                  <td>{{lang.certificate_status}}：</td>
                  <td>
                    <span class="status success" v-if="infoDetails.status === 1">{{lang.certified}}</span>
                    <span class="status" v-if="infoDetails.status === 2">{{lang.identification}}</span>
                    <span class="status danger" v-if="infoDetails.status === 0">{{lang.not_certified}}</span>
                  </td>
                </tr>
                <tr>
                  <td>{{lang.login_phone}}：</td>
                  <td>{{infoDetails.phone}}</td>
                  <td>{{lang.belong_area}}：</td>
                  <td>{{calcCountry(infoDetails.country)}}/{{infoDetails.zh_province}}/{{infoDetails.zh_city}}</td>
                </tr>
                <tr>
                  <td>{{lang.address_zh}}：</td>
                  <td>{{infoDetails.zh_address}}</td>
                  <td>{{lang.email_address}}：</td>
                  <td>{{infoDetails.email}}</td>
                </tr>
                <tr>
                  <td>{{lang.address_en}}：</td>
                  <td>{{infoDetails.en_address}}</td>
                </tr>
              </table>
            </div>
            <button class="close-dia" @click="isShowTemp = false">{{lang.ticket_btn5}}</button>
          </el-dialog>
        </div>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/goodsList.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/buyDomain.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
  {include file="footer"}