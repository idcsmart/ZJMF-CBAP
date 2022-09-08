{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/account.css">
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
            <aside-menu :menu-active-id="4"></aside-menu>
            <el-container>
                <top-menu></top-menu>
                <el-main>
                    <!-- 自己的东西 -->
                    <div class="main-card">
                        <div class="main-card-title">{{lang.account_title1}}</div>
                        <div class="content-box">
                            <el-tabs v-model="activeIndex" @tab-click="handleClick">
                                <el-tab-pane :label="lang.account_menu1" name="1">
                                    <div class="box-top">
                                        <div class="right-name">
                                            <span class="name-text">{{userName}}</span>
                                            <span class="name-country">
                                                <!-- <img src="/{$template_catalog}/template/{$themes}/img/ali/user.png">
                                             -->
                                                <img v-show="imgShow" :src="curSrc">
                                            </span>
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
                                                                <el-option value="zh-cn" label="中文"></el-option>
                                                                <el-option value="en-us" label="英文"></el-option>
                                                            </el-select>
                                                        </div>
                                                    </div>
                                                </el-col>
                                                <el-col :span="7">

                                                </el-col>
                                                <el-col :span="3">

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
                                                        <div class="box-item-b">
                                                            <el-input :disabled="true" v-model="accountData.phone">
                                                                <i class="el-icon-edit edit-icon" slot="suffix" @click="showPhone"></i>
                                                            </el-input>
                                                        </div>
                                                    </div>
                                                </el-col>
                                                <el-col :span="7">
                                                    <div class="box-item">
                                                        <div class="box-item-t">{{lang.account_label7}}</div>
                                                        <div class="box-item-b">
                                                            <el-input :disabled="true" v-model="accountData.email">
                                                                <i class="el-icon-edit edit-icon" slot="suffix" @click="showEmail"></i>
                                                            </el-input>
                                                        </div>
                                                    </div>
                                                </el-col>
                                                <el-col :span="7">
                                                    <div class="box-item">
                                                        <div class="box-item-t">{{lang.account_label8}}</div>
                                                        <div class="box-item-b">
                                                            <el-input :disabled="true" type="password" value="********">
                                                                <i class="el-icon-edit edit-icon" slot="suffix" @click="showPass"></i>
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
                                <el-tab-pane :label="lang.account_menu2" name="2">
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
                                    <el-input v-else class="input-with-select" v-model="formData.phone" :placeholder="lang.account_tips7">
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
                                    <el-form-item label="验证码">
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
                                <el-button class="btn-ok" @click="doPhoneEdit">验证</el-button>
                                <el-button class="btn-no" @click="isShowPhone= false">取消</el-button>
                            </div>
                        </el-dialog>
                    </div>
                    <!-- 修改手机号弹框 -->
                    <div class="check-phone">
                        <el-dialog width="6.8rem" :visible.sync="isShowRePhone" :show-close=false :close-on-click-modal=false>
                            <div class="dialog-title">
                                {{accountData.phone?"更改手机号" : "绑定手机号"}}
                            </div>
                            <div class="mian-form">
                                <el-form :model="rePhoneData" label-position="top">
                                    <el-form-item label="手机号">
                                        <el-input v-model="rePhoneData.phone" placeholder="请输入新手机号">
                                            <el-select class="select-input" filterable slot="prepend" v-model="rePhoneData.countryCode">
                                                <el-option v-for="item in countryList" :key="item.name" :value="item.phone_code" :label="item.name_zh + '+' + item.phone_code"></el-option>
                                            </el-select>
                                        </el-input>
                                    </el-form-item>
                                    <el-form-item label="验证码">
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
                                <el-button class="btn-ok" @click="doRePhoneEdit">验证</el-button>
                                <el-button class="btn-no" @click="isShowRePhone= false">取消</el-button>
                            </div>
                        </el-dialog>
                    </div>
                    <!-- 验证邮箱弹框 -->
                    <div class="check-phone">
                        <el-dialog width="6.8rem" :visible.sync="isShowEmail" :show-close=false :close-on-click-modal=false>
                            <div class="dialog-title">
                                验证邮箱
                            </div>
                            <div class="mian-form">
                                <el-form :model="emailData" label-position="top">
                                    <el-form-item label="邮箱">
                                        <el-input :disabled="true" v-model="emailData.email" :placeholder="'使用邮箱'+accountData.email+'验证'"></el-input>
                                    </el-form-item>
                                    <el-form-item label="验证码">
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
                                <el-button class="btn-ok" @click="doEmailEdit">验证</el-button>
                                <el-button class="btn-no" @click="isShowEmail= false">取消</el-button>
                            </div>
                        </el-dialog>
                    </div>
                    <!-- 修改邮箱弹框 -->
                    <div class="check-phone">
                        <el-dialog width="6.8rem" :visible.sync="isShowReEmail" :show-close=false :close-on-click-modal=false>
                            <div class="dialog-title">
                                {{accountData.email?"更改邮箱" : "绑定邮箱"}}
                            </div>
                            <div class="mian-form">
                                <el-form :model="reEmailData" label-position="top">
                                    <el-form-item label="邮箱">
                                        <el-input v-model="reEmailData.email" placeholder="请输入新邮箱"></el-input>
                                    </el-form-item>
                                    <el-form-item label="验证码">
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
                                <el-button class="btn-ok" @click="doReEmailEdit">验证</el-button>
                                <el-button class="btn-no" @click="isShowReEmail= false">取消</el-button>
                            </div>
                        </el-dialog>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/js/account.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/account.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/captchaDialog/captchaDialog.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/countDownButton/countDownButton.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}