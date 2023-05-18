{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/account.css">
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/certification.css">
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
  <div id="account" class="template">
    <el-container>
      <aside-menu :menu-active-id="2" @getruleslist="getRule"></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <el-main>
          <!-- 自己的东西 -->
          <div class="main-card">
            <div class="main-card-title">{{lang.account_title1}}</div>
            <div class="content-box">
              <el-tabs v-model="activeIndex" @tab-click="handleClick">
                <el-tab-pane :label="lang.account_menu1" name="1" v-if="showAccountController">
                  <div class="box-top">
                    <div class="right-name">
                      <div>
                        <span class="name-text">{{userName}}</span>
                        <p class="name-country">
                          <img v-show="imgShow" class="country-img" :src="curSrc">
                        </p>
                      </div>
                      {foreach $addons as $addon}
                      {if ($addon.name=='IdcsmartCertification')}
                      {php}$PluginModel=new app\admin\model\PluginModel();$config=$PluginModel->where('name','IdcsmartCertification')->value('config');$config=json_decode($config,true);{/php}
                      {if (isset($config.certification_open) && $config.certification_open)}
                      <div class="attestation-status" v-show="attestationStatusInfo.iocnShow" @click="handelAttestation({$addon.id})">
                        <img :src="attestationStatusInfo.iconUrl" alt="">
                        <span class="attestation-text" v-if="attestationStatusInfo.status === 0">未认证，前往<span class="bule-text">实名认证<i class="el-icon-arrow-right"></i></span></span>
                        <span class="attestation-text" v-else-if="attestationStatusInfo.status === 10">个人认证已完成，前往<span class="bule-text">企业认证<i class="el-icon-arrow-right"></i></span></span>
                        <span class="attestation-text" v-else-if="attestationStatusInfo.status === 20 || attestationStatusInfo.status === 30">实名认证已完成</span>
                      </div>
                      {/if}
                      {/if}
                      {/foreach}
                    </div>
                  </div>
                  <div class="box-main">
                    <div class="basic">
                      <el-row>
                        <el-col :span="7">
                          <div class="basic-title">
                            {{lang.account_menu3}}
                          </div>
                        </el-col>
                        <el-col :span="7">
                        </el-col>
                        <el-col :span="7">
                        </el-col>
                        <el-col :span="3">
                        </el-col>
                      </el-row>
                      <el-row>
                        <el-col :span="7">
                          <div class="box-item">
                            <div class="box-item-t">{{lang.account_label1}}</div>
                            <div class="box-item-b">
                              <el-input v-model="accountData.username"></el-input>
                            </div>
                          </div>
                        </el-col>
                        <el-col :span="7">
                          <div class="box-item">
                            <div class="box-item-t">{{lang.account_label2}}</div>
                            <div class="box-item-b">
                              <el-select v-model="accountData.language">
                                <el-option v-for="(item,index) in commonData.lang_list" :key="item.display_flag + index" :value="item.display_lang" :label="item.display_name"></el-option>
                              </el-select>
                            </div>
                          </div>
                        </el-col>
                      </el-row>
                      <el-row>
                        <el-col :span="7">
                          <div class="box-item">
                            <div class="box-item-t">{{lang.account_label3}}</div>
                            <div class="box-item-b">
                              <el-input v-model="accountData.company"></el-input>
                            </div>
                          </div>
                        </el-col>
                        <el-col :span="7">
                          <div class="box-item">
                            <div class="box-item-t">{{lang.account_label4}}</div>
                            <div class="box-item-b">
                              <el-select v-model="accountData.country" filterable>
                                <el-option v-for="item in countryList" :key="item.name" :value="item.name" :label="item.name_zh">
                                </el-option>
                              </el-select>
                            </div>
                          </div>
                        </el-col>
                        <el-col :span="7">
                          <div class="box-item">
                            <div class="box-item-t">{{lang.account_label5}}</div>
                            <div class="box-item-b">
                              <el-input v-model="accountData.address"></el-input>
                            </div>
                          </div>
                        </el-col>
                        <el-col :span="3">
                        </el-col>
                      </el-row>
                    </div>
                    <div class="account">
                      <el-row>
                        <el-col :span="7">
                          <div class="account-title">
                            {{lang.account_menu4}}
                          </div>
                        </el-col>
                        <el-col :span="7">
                        </el-col>
                        <el-col :span="7">
                        </el-col>
                        <el-col :span="3">
                        </el-col>
                      </el-row>
                      <el-row>
                        <el-col :span="7">
                          <div class="box-item">
                            <div class="box-item-t">{{lang.account_label6}}</div>
                            <div class="box-item-b" @click="showPhone">
                              <el-input :disabled="true" v-model="accountData.phone">
                                <i class="el-icon-edit edit-icon" slot="suffix"></i>
                              </el-input>
                            </div>
                          </div>
                        </el-col>
                        <el-col :span="7">
                          <div class="box-item">
                            <div class="box-item-t">{{lang.account_label7}}</div>
                            <div class="box-item-b" @click="showEmail">
                              <el-input :disabled="true" v-model="accountData.email">
                                <i class="el-icon-edit edit-icon" slot="suffix"></i>
                              </el-input>
                            </div>
                          </div>
                        </el-col>
                        <el-col :span="7">
                          <div class="box-item">
                            <div class="box-item-t">{{lang.account_label8}}</div>
                            <div class="box-item-b" @click="showPass">
                              <el-input :disabled="true" type="password" value="********">
                                <i class="el-icon-edit edit-icon" slot="suffix"></i>
                              </el-input>
                            </div>
                          </div>
                        </el-col>
                        <el-col :span="3">
                        </el-col>
                      </el-row>
                      <el-row>
                        <el-col :span="7">
                          <div>
                            <el-button class="btn-save" @click="saveAccount">{{lang.account_btn1}}</el-button>
                          </div>
                        </el-col>
                        <el-col :span="7">
                        </el-col>
                        <el-col :span="7">
                        </el-col>
                        <el-col :span="3">
                        </el-col>
                      </el-row>
                    </div>
                  </div>
                </el-tab-pane>
                <!-- 操作日志开始 -->
                <el-tab-pane :label="lang.account_menu2" name="2" v-if="showLogController">
                  <div class="searchbar com-search">
                    <el-input v-model="params.keywords" style="width: 3.2rem;margin-left: .2rem;" :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange" clearable @clear="getAccountList">
                      <i class="el-icon-search input-search" slot="suffix" @Click="inputChange"></i>
                    </el-input>
                  </div>
                  <div class="content_table">
                    <div class="tabledata">
                      <el-table v-loading="loading" :data="dataList" style="width: 100%;margin-bottom: .2rem;">
                        <el-table-column prop="id" label="ID" width="100" align="left">
                        </el-table-column>
                        <el-table-column prop="description" min-width="700" :show-overflow-tooltip="true" :label="lang.account_label9" align="left">

                        </el-table-column>
                        <el-table-column prop="create_time" :label="lang.account_label10" min-width="200" align="left">
                          <template slot-scope="scope">
                            <span>{{scope.row.create_time | formateTime}}</span>
                          </template>
                        </el-table-column>
                        <el-table-column prop="ip" label="IP" width="150" align="left">

                        </el-table-column>

                      </el-table>
                      <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange"></pagination>
                    </div>
                  </div>

                  <!-- 移动端显示表格开始 -->
                  <div class="mobel">
                    <div class="mob-searchbar mob-com-search">
                      <el-input class="mob-search-input" v-model="params.keywords" :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange" clearable @clear="getAccountList">
                        <i class="el-icon-search input-search" slot="suffix" @Click="inputChange"></i>
                      </el-input>
                    </div>
                    <div class="mob-tabledata">
                      <div class="mob-tabledata-item" v-for="item in dataList" :key="item.id">
                        <div class="mob-item-row mob-item-row1">
                          <span>{{item.id}}</span>
                          <span>
                            {{item.ip}}
                          </span>
                        </div>
                        <div class="mob-item-row mob-item-row2">
                          <span class="mob-item-row2-name" :title="item.description">
                            {{item.description}}
                          </span>
                        </div>
                        <div class="mob-item-row mob-item-row3">
                          <span>{{item.create_time | formateTime}}</span>
                          <div>

                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="bottom-text">
                      <span v-show="isEnd">{{lang.account_tips15}}</span>
                      <span v-loading=isShowMore></span>
                    </div>
                    <img v-show="isShowBackTop" class="back-top-img" @click="goBackTop" src="/{$template_catalog}/template/{$themes}/img/common/toTop.png">
                  </div>

                </el-tab-pane>
              </el-tabs>
            </div>
          </div>
          <!-- 更改密码弹框 -->
          <div class="edit-pass">
            <el-dialog width="6.8rem" :visible.sync="isShowPass" :show-close=false :close-on-click-modal=false>
              <div class="dialog-title">
                {{lang.account_title2}}
              </div>
              <div class="mian-form">
                <el-form :model="passData" label-position="top">
                  <el-form-item :label="lang.account_label11">
                    <el-input type="password" v-model="passData.old_password" :placeholder="lang.account_tips1">
                    </el-input>
                    <span class="forget-pass">{{lang.account_tips4}} <a @click="showCodePass">{{lang.account_tips5}}</a> </span>
                  </el-form-item>
                  <el-form-item :label="lang.account_label12">
                    <el-input type="password" v-model="passData.new_password" :placeholder="lang.account_tips2">
                    </el-input>
                  </el-form-item>
                  <el-form-item :label="lang.account_label13">
                    <el-input type="password" v-model="passData.repassword" :placeholder="lang.account_tips3">
                    </el-input>
                  </el-form-item>
                  <el-form-item v-show="errText">
                    <el-alert show-icon :title="errText" type="error" :closable="false"></el-alert>
                  </el-form-item>
                </el-form>
              </div>
              <div class="dialog-footer">
                <el-button class="btn-ok" @click="doPassEdit">{{lang.account_btn2}}</el-button>
                <el-button class="btn-no" @click="isShowPass= false">{{lang.account_btn3}}</el-button>
              </div>
            </el-dialog>
          </div>
          <!-- 验证码更改密码弹框 -->
          <div class="edit-pass">
            <el-dialog width="6.8rem" :visible.sync="isShowCodePass" :show-close=false :close-on-click-modal=false>
              <div class="dialog-title">
                {{lang.account_title2}}
              </div>
              <div class="login-top">
                <div class="login-email" :class="isEmailOrPhone? 'active':null" @click="isEmailOrPhone = true">{{lang.account_label14}}
                </div>
                <div class="login-phone" :class="!isEmailOrPhone? 'active':null" @click="isEmailOrPhone = false">{{lang.account_label15}}
                </div>
              </div>
              <div class="form-main">
                <div class="form-item">
                  <el-input v-if="isEmailOrPhone" v-model="formData.email" :placeholder="lang.account_tips6">
                  </el-input>
                  <el-input v-else class="input-with-select select-input" v-model="formData.phone" :placeholder="lang.account_tips7">
                    <el-select class="code-pass-select" filterable slot="prepend" v-model="formData.countryCode">
                      <el-option v-for="item in countryList" :key="item.name" :value="item.phone_code" :label="item.name_zh + '+' + item.phone_code">
                      </el-option>
                    </el-select>
                  </el-input>
                </div>
                <div class="form-item code-item">
                  <!-- 邮箱验证码 -->
                  <el-input v-if="isEmailOrPhone" v-model="formData.emailCode" :placeholder="lang.account_tips8">
                  </el-input>
                  <count-down-button ref="codeEmailCodebtn" @click.native="sendEmailCode('code')" v-if="isEmailOrPhone" my-class="code-btn"></count-down-button>
                  <!-- <el-button v-if="isEmailOrPhone" class="code-btn" type="primary">获取验证码</el-button> -->

                  <!-- 手机验证码 -->
                  <el-input v-if="!isEmailOrPhone" v-model="formData.phoneCode" :placeholder="lang.account_tips9">
                  </el-input>
                  <count-down-button ref="codePhoneCodebtn" @click.native="sendPhoneCode('code')" v-if="!isEmailOrPhone" my-class="code-btn"></count-down-button>
                  <!-- <el-button v-if="!isEmailOrPhone" class="code-btn" type="primary">获取验证码</el-button> -->

                </div>
                <div class="form-item">
                  <el-input :placeholder="lang.tip1" v-model="formData.password" type="password">
                  </el-input>
                </div>
                <div class="form-item">
                  <el-input :placeholder="lang.tip2" v-model="formData.repassword" type="password">
                  </el-input>
                </div>

                <div class="read-item" v-if="errorText.length !== 0">
                  <el-alert :title="errorText" type="error" show-icon :closable="false">
                  </el-alert>
                </div>
                <div class="form-item dialog-footer">
                  <el-button class="btn-ok" @click="doResetPass">{{lang.account_btn2}}</el-button>
                  <el-button class="btn-no" @click="quiteCodePass">{{lang.account_btn3}}</el-button>
                </div>
              </div>
            </el-dialog>
          </div>
          <!-- 验证手机号弹框 -->
          <div class="check-phone">
            <el-dialog width="6.8rem" :visible.sync="isShowPhone" :show-close=false :close-on-click-modal=false>
              <div class="dialog-title">
                {{lang.account_title3}}
              </div>
              <div class="mian-form">
                <el-form :model="phoneData" label-position="top">
                  <el-form-item :label="lang.account_label15">
                    <el-input :disabled="true" v-model="phoneData.phone" :placeholder=" lang.account_tips10 +accountData.phone+ lang.account_tips11"></el-input>
                  </el-form-item>
                  <el-form-item :label="lang.account_label16">
                    <div class="input-btn">
                      <el-input v-model="phoneData.code">
                      </el-input>
                      <count-down-button ref="phoneCodebtn" @click.native="sendPhoneCode('old')" my-class="code-btn" slot="append"></count-down-button>
                    </div>
                  </el-form-item>
                  <el-form-item v-show="errText">
                    <el-alert show-icon :title="errText" type="error" :closable="false"></el-alert>
                  </el-form-item>
                </el-form>
              </div>
              <div class="dialog-footer">
                <el-button class="btn-ok" @click="doPhoneEdit">{{lang.account_btn4}}</el-button>
                <el-button class="btn-no" @click="isShowPhone= false">{{lang.account_btn3}}</el-button>
              </div>
            </el-dialog>
          </div>
          <!-- 修改手机号弹框 -->
          <div class="check-phone">
            <el-dialog width="6.8rem" :visible.sync="isShowRePhone" :show-close=false :close-on-click-modal=false>
              <div class="dialog-title">
                {{accountData.phone?lang.account_title4 : lang.account_title5}}
              </div>
              <div class="mian-form">
                <el-form :model="rePhoneData" label-position="top">
                  <el-form-item :label="lang.account_label15">
                    <el-input v-model="rePhoneData.phone" :placeholder="lang.account_tips16">
                      <el-select class="select-input" filterable slot="prepend" v-model="rePhoneData.countryCode">
                        <el-option v-for="item in countryList" :key="item.name" :value="item.phone_code" :label="item.name_zh + '+' + item.phone_code"></el-option>
                      </el-select>
                    </el-input>
                  </el-form-item>
                  <el-form-item :label="lang.account_label16">
                    <div class="input-btn">
                      <el-input v-model="rePhoneData.code">
                      </el-input>
                      <count-down-button ref="rePhoneCodebtn" @click.native="sendPhoneCode('new')" my-class="code-btn" slot="append"></count-down-button>
                    </div>
                  </el-form-item>
                  <el-form-item v-show="errText">
                    <el-alert show-icon :title="errText" type="error" :closable="false"></el-alert>
                  </el-form-item>
                </el-form>
              </div>
              <div class="dialog-footer">
                <el-button class="btn-ok" @click="doRePhoneEdit">{{lang.account_btn4}}</el-button>
                <el-button class="btn-no" @click="isShowRePhone= false">{{lang.account_btn3}}</el-button>
              </div>
            </el-dialog>
          </div>
          <!-- 验证邮箱弹框 -->
          <div class="check-phone">
            <el-dialog width="6.8rem" :visible.sync="isShowEmail" :show-close=false :close-on-click-modal=false>
              <div class="dialog-title">
                {{lang.account_title6}}
              </div>
              <div class="mian-form">
                <el-form :model="emailData" label-position="top">
                  <el-form-item :label="lang.account_label7">
                    <el-input :disabled="true" v-model="emailData.email" :placeholder="lang.account_tips17 +accountData.email+ lang.account_tips18"></el-input>
                  </el-form-item>
                  <el-form-item :label="lang.account_label16">
                    <div class="input-btn">
                      <el-input v-model="emailData.code">
                      </el-input>
                      <count-down-button ref="emailCodebtn" @click.native="sendEmailCode('old')" my-class="code-btn" slot="append"></count-down-button>
                    </div>
                  </el-form-item>
                  <el-form-item v-show="errText">
                    <el-alert show-icon :title="errText" type="error" :closable="false"></el-alert>
                  </el-form-item>
                </el-form>
              </div>
              <div class="dialog-footer">
                <el-button class="btn-ok" @click="doEmailEdit">{{lang.account_btn4}}</el-button>
                <el-button class="btn-no" @click="isShowEmail= false">{{lang.account_btn3}}</el-button>
              </div>
            </el-dialog>
          </div>
          <!-- 修改邮箱弹框 -->
          <div class="check-phone">
            <el-dialog width="6.8rem" :visible.sync="isShowReEmail" :show-close=false :close-on-click-modal=false>
              <div class="dialog-title">
                {{accountData.email? lang.account_title7 : lang.account_title8}}
              </div>
              <div class="mian-form">
                <el-form :model="reEmailData" label-position="top">
                  <el-form-item :label="lang.account_label7">
                    <el-input v-model="reEmailData.email" :placeholder="lang.account_tips19"></el-input>
                  </el-form-item>
                  <el-form-item :label="lang.account_label16">
                    <div class="input-btn">
                      <el-input v-model="reEmailData.code">
                      </el-input>
                      <count-down-button ref="reEmailCodebtn" @click.native="sendEmailCode('new')" my-class="code-btn" slot="append"></count-down-button>
                    </div>
                  </el-form-item>
                  <el-form-item v-show="errText">
                    <el-alert show-icon :title="errText" type="error" :closable="false"></el-alert>
                  </el-form-item>
                </el-form>
              </div>
              <div class="dialog-footer">
                <el-button class="btn-ok" @click="doReEmailEdit">{{lang.account_btn4}}</el-button>
                <el-button class="btn-no" @click="isShowReEmail= false">{{lang.account_btn3}}</el-button>
              </div>
            </el-dialog>
          </div>
          <div>
            <certification-dialog :tip_dialong_show="tip_dialong_show" @close-dialog="tip_dialong_show = false"></certification-dialog>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/js/account.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/account.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/certification.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/captchaDialog/captchaDialog.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/certificationTips/certificationDialog.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/countDownButton/countDownButton.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  {include file="footer"}