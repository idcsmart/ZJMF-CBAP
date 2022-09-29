<!DOCTYPE html>
<html lang="en" theme-color="default" theme-mode>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <title></title>
    <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
    <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/login.css">
    <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common/common.css">
    <script>
        const url = "/{$template_catalog}/template/{$themes}/"
    </script>
    <script src="/{$template_catalog}/template/{$themes}/js/common/lang.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/common/common.js"></script>
    <style>
        [v-clock] {
            display: none !important;
        }
    </style>
</head>

<body>
    <div id="mainLoading">
        <div class="ddr ddr1"></div>
        <div class="ddr ddr2"></div>
        <div class="ddr ddr3"></div>
        <div class="ddr ddr4"></div>
        <div class="ddr ddr5"></div>
    </div>
    <div class="template">
        <div id="login" v-cloak>
            <!-- 验证码 -->
            <captcha-dialog :is-show-captcha="isShowCaptcha" ref="captcha"></captcha-dialog>
            <div class="login-container">
                <div class="container-back">
                    <div class="back-line1"></div>
                    <div class="back-line2"></div>
                    <div class="back-line3"></div>
                    <div class="back-text">
                        <div class="text-welcome">
                            WELCOME
                        </div>
                        <div class="text-title">
                            欢迎来到{{commonData.website_name}}会员中心
                        </div>
                        <div class="text-level">
                            实现在线业务的便捷交易与管理
                        </div>
                    </div>
                </div>
                <div class="container-before">
                    <div class="login">
                        <div class="login-text">
                            <div class="login-text-title">登录</div>
                            <div class="login-text-regist" v-if="commonData.register_email == 1 || commonData.register_phone == 1">
                                没有账户？<a @click="toRegist">立即注册</a>
                            </div>
                        </div>
                        <div class="login-form">
                            <div class="login-top">
                                <div v-show="isPassOrCode" class="login-email" :class="isEmailOrPhone? 'active':null" @click="isEmailOrPhone = true">电子邮箱
                                </div>
                                <div class="login-phone" :class="!isEmailOrPhone? 'active':null" @click="isEmailOrPhone = false">手机号码
                                </div>
                            </div>
                            <div class="form-main">
                                <div class="form-item">
                                    <el-input v-if="isEmailOrPhone" v-model="formData.email" placeholder="请输入邮箱"></el-input>
                                    <el-input v-else class="input-with-select select-input" v-model="formData.phone" placeholder="请输入手机号码">
                                        <el-select filterable slot="prepend" v-model="formData.countryCode">
                                            <el-option v-for="item in countryList" :key="item.name" :value="item.phone_code" :label="item.name_zh + '+' + item.phone_code"></el-option>
                                        </el-select>
                                    </el-input>
                                </div>
                                <div v-if="isPassOrCode" class="form-item">
                                    <el-input :placeholder="lang.login_pass" v-model="formData.password" type="password">
                                    </el-input>
                                </div>
                                <div v-else class="form-item code-item">
                                    <!-- 邮箱验证码 -->
                                    <el-input v-if="isEmailOrPhone" v-model="formData.emailCode" :placeholder="lang.email_code">
                                    </el-input>
                                    <count-down-button ref="emailCodebtn" @click.native="sendEmailCode" v-if="isEmailOrPhone" my-class="code-btn"></count-down-button>
                                    <!-- <el-button v-if="isEmailOrPhone" class="code-btn" type="primary">获取验证码</el-button> -->

                                    <!-- 手机验证码 -->
                                    <el-input v-if="!isEmailOrPhone" v-model="formData.phoneCode" :placeholder="lang.login_phone_code">
                                    </el-input>
                                    <count-down-button ref="phoneCodebtn" @click.native="sendPhoneCode" v-if="!isEmailOrPhone" my-class="code-btn"></count-down-button>
                                    <!-- <el-button v-if="!isEmailOrPhone" class="code-btn" type="primary">获取验证码</el-button> -->
                                </div>
                                <div class="form-item rember-item">
                                    <el-checkbox v-model="formData.isRemember">{{lang.login_remember}}</el-checkbox>
                                    <a @click="toForget">{{lang.login_forget}}</a>
                                </div>
                                <div class="read-item" v-if="errorText.length !== 0">
                                    <el-alert :title="errorText" type="error" show-icon :closable="false">
                                    </el-alert>
                                </div>
                                <div class="form-item">
                                    <el-button type="primary" class="login-btn" @click="doLogin">{{lang.login}}</el-button>
                                </div>
                                <div class="form-item read-item">
                                    <el-checkbox v-model="checked">
                                    </el-checkbox>{{lang.login_read}}<a @click="toRead">{{lang.login_list}}</a>
                                </div>
                                
                                <div class="form-item line-item" v-if="commonData.login_phone_verify == 1">
                                    <el-divider><span class="text">or</span></el-divider>
                                </div>
                                <div class="form-item" v-if="commonData.login_phone_verify == 1">
                                    <el-button v-if="isPassOrCode" :disabled="commonData.login_phone_verify == 0" @click="isPassOrCode = false;isEmailOrPhone = false" class="type-btn">{{lang.login_code_login}}
                                    </el-button>
                                    <el-button v-else @click="isPassOrCode = true" class="type-btn">
                                        {{lang.login_pass_login}}
                                    </el-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- =======公共======= -->
    <script src="/{$template_catalog}/template/{$themes}/js/common/vue.js"></script>
    <script src="https://unpkg.com/element-ui/lib/index.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/common/axios.min.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/request.js"></script>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/countDownButton/countDownButton.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/login.js"></script>
    <script src="https://cdn.bootcss.com/crypto-js/3.1.9-1/crypto-js.min.js"></script>
    <script src="https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/captchaDialog/captchaDialog.js"></script>
</body>

</html>