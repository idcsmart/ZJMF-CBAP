    {include file="header"}
    <!-- 页面独有样式 -->
    <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/aliHome.css">
    <script src="https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js"></script>
    </head>

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
            <ali-aside-menu :menu-active-id="1"></ali-aside-menu>
            <el-container>
                <top-menu></top-menu>
                <el-main>
                    <div id="ali-home" class="ali-home">
                        <div>
                            <div class="banner">
                                <img class="banner-img" src="/{$template_catalog}/template/{$themes}/img/ali/banner.png" alt="">
                                <div class="ali-text" v-show="isShowText">
                                    <span>{{isShow? lang.ali_title2 : lang.ali_title1}}</span>
                                    <span class="ali-text-b">{{isShow? lang.ali_title3 : lang.ali_title4}}</span>
                                </div>
                                <div class="banner-search" v-if="isShow" v-show="isShowText">
                                    <el-input class="banner-input" v-model="email" :disabled="accountData.email?true:false" :placeholder="lang.ali_tips1">
                                        <!-- <el-button class="input-btn" slot="suffix" :disabled="aliInviteState===0?false:true" @click="gitInvite">{{aliInviteState===0?'已邀请':lang.btn_text1}}</el-button> -->
                                        <el-button class="input-btn" slot="suffix" :disabled="aliInviteState===0" @click="gitInvite">{{aliInviteState===0?'已邀请':'获取邀请'}}</el-button>
                                    </el-input>
                                    <span class="ali-tip-text">{{aliInviteState===0?'请前往邮箱注册，完成验证!':'输入邮箱后点击按钮，即可获取阿里邀请函！'}}</span>
                                </div>
                            </div>
                            <div class="ali-main">
                                <div class="ali-main-card">
                                    <div class="card-title">
                                        {{lang.ali_title5}}
                                    </div>
                                    <div class="card-recharge">
                                        <span class="recharge-text">{{lang.ali_tips3}}({{commonData.currency_suffix}})</span>
                                        <el-input class="recharge-input" :placeholder="lang.ali_tips4+ commonData.currency_prefix +commonData.recharge_min +commonData.currency_suffix" v-model="czData.amount" @keyup.native="czData.amount=oninput(czData.amount)">
                                            <span slot="prefix">{{commonData.currency_prefix}}</span>
                                        </el-input>
                                        <el-button class="recharge-btn-ok" @click="showCz">{{lang.btn_text2}}</el-button>
                                        <el-button class="recharge-btn-old" @click="toPageList">{{lang.btn_text3}}</el-button>
                                    </div>
                                    <div class="card-choice">
                                        <div class="choice-item" :class="item.id === activeId?'active':''" v-for="item in money" :key="item.id" @click="activeId = item.id;czData.amount = item.num">
                                            <span class="item-pre">{{commonData.currency_prefix}}</span>
                                            <span class="item-num">{{item.num}}</span>
                                            <span class="item-num">{{commonData.currency_suffix}}</span>
                                        </div>
                                    </div>
                                    <div class="mob-footer-btn">
                                        <el-button class="mob-recharge-btn-ok" @click="showCz">{{lang.btn_text2}}</el-button>
                                        <el-button class="mob-recharge-btn-old" @click="toPageList">{{lang.btn_text3}}</el-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- 充值 dialog -->
                        <div class="cz-dialog">
                            <el-dialog width="6.8rem" :visible.sync="isShowCz" :show-close=false @close="czClose">
                                <div class="dialog-title">
                                    {{lang.ali_title6}}
                                </div>
                                <div class="dialog-form">
                                    <el-form :model="czData" label-position="top">
                                        <el-form-item :label="lang.ali_label1">
                                            <el-select @change="czSelectChange" v-model="czData.gateway">
                                                <el-option v-for="item in gatewayList" :key="item.id" :label="item.title" :value="item.name"></el-option>
                                            </el-select>
                                        </el-form-item>
                                        <el-form-item v-if="errText">
                                            <el-alert :title="errText" type="error" :closable="false" show-icon>
                                            </el-alert>
                                        </el-form-item>
                                        <el-form-item>
                                            <div class="loading" v-show="imgLoading" v-loading="true"></div>
                                            <!-- <div class="pay-html" v-show="!imgLoading" v-html="payHtml"></div> -->
                                            <div class="pay-html"></div>
                                        </el-form-item>
                                    </el-form>
                                </div>
                            </el-dialog>
                        </div>
                    </div>
                </el-main>
            </el-container>

        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/aliHome.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/aliHome.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}