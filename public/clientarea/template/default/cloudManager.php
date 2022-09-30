{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/cloudManager.css">
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/cloudTop.css">

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
                        <cloud-top active-name="2" ref="cloudTop" @getstatus="getPowerStatus" @getrescuestatus="getRescueStatus"></cloud-top>
                        <!-- 管理 -->
                        <div class="manage-content">
                            <!-- 第一行 -->
                            <el-row>
                                <el-col :span="8">
                                    <div class="manage-item">
                                        <div class="item-top">
                                            <el-select v-model="powerStatus">
                                                <el-option value="on" label="开机"></el-option>
                                                <el-option value="off" label="关机"></el-option>
                                                <el-option value="rebot" label="重启"></el-option>
                                                <el-option value="hardRebot" label="强制重启"></el-option>
                                                <el-option value="hardOff" label="强制关机"></el-option>
                                            </el-select>
                                            <div class="item-top-btn" @click="toChangePower" v-loading="loading1">
                                                确定
                                            </div>
                                        </div>
                                        <div class="item-bottom">
                                            <div class="bottom-row">对您的实例进行电源操作</div>
                                        </div>
                                    </div>
                                </el-col>
                                <el-col :span="8">
                                    <div class="manage-item">
                                        <div class="item-top-btn" @click="getVncUrl" v-loading="loading2">
                                            控制台
                                        </div>
                                        <div class="item-bottom">
                                            <div class="bottom-row">通过实体显示器与鼠标键盘控制您的实例</div>
                                            <div class="bottom-row">即使实例没有网络也能控制</div>
                                        </div>
                                    </div>
                                </el-col>
                                <el-col :span="8">
                                    <div class="manage-item">
                                        <div class="item-top-btn" @click="showRePass">
                                            重置密码
                                        </div>
                                        <div class="item-bottom">
                                            <div class="bottom-row">如您忘记密码或密码无法进入</div>
                                            <div class="bottom-row">可强制修改您的实例的root/administrator密码</div>
                                        </div>
                                    </div>
                                </el-col>
                            </el-row>
                            <!-- 第二行 -->
                            <el-row>
                                <el-col :span="8">
                                    <div class="manage-item">
                                        <div class="item-top-btn" @click="showRescueDialog" v-if="!isRescue">
                                            救援模式
                                        </div>
                                        <div class="item-top-btn" @click="showQuitRescueDialog" v-else>
                                            退出救援模式
                                        </div>
                                        <div class="item-bottom">
                                            <div class="bottom-row">如实例系统损坏无法启动</div>
                                            <div class="bottom-row">可进入临时救援系统进行修复或数据拷贝</div>
                                        </div>
                                    </div>
                                </el-col>
                                <el-col :span="8">
                                    <div class="manage-item">
                                        <div class="item-top-btn" @click="showReinstall">
                                            重装系统
                                        </div>
                                        <div class="item-bottom">
                                            <div class="bottom-row">快速更换实例为其他操作系统</div>
                                        </div>
                                    </div>
                                </el-col>
                                <el-col :span="8">
                                    <div class="manage-item">
                                        <div class="item-top-btn" @click="showUpgrade">
                                            升降级
                                        </div>
                                        <div class="item-bottom">
                                            <div class="bottom-row">升降级配置</div>
                                        </div>
                                    </div>
                                </el-col>
                               
                            </el-row>
                            <!-- 第三行 -->
                            <el-row>
                                <el-col :span="8">
                                    <div class="manage-item">
                                        <div class="item-top-btn" style="background: #eee;cursor: not-allowed;color: #999;">
                                            设置启动项
                                        </div>
                                        <div class="item-bottom">
                                            <div class="bottom-row">可设置您的实例从ISO或本地硬盘中启动</div>
                                        </div>
                                    </div>
                                </el-col>
                                <el-col :span="8">
                                    <div class="manage-item">
                                        <div class="item-top-btn" style="background: #eee;cursor: not-allowed;color: #999;">
                                            挂载ISO
                                        </div>
                                        <div class="item-bottom">
                                            <div class="bottom-row">挂载ISO文件，用于安装系统或者提供Virto驱动</div>
                                        </div>
                                    </div>
                                </el-col>
                                <el-col :span="8">
                                    <div class="manage-item">
                                        <div class="item-top-btn" style="background: #eee;cursor: not-allowed;color: #999;">
                                            删除实例
                                        </div>
                                        <div class="item-bottom">
                                            <div class="bottom-row">不再使用该实例，彻底销毁并删除所有数据该操作不可逆</div>
                                        </div>
                                    </div>
                                </el-col>
                            </el-row>
                        </div>
                        <!-- 救援系统弹窗 -->
                        <div class="rescue-dialog">
                            <el-dialog width="6.8rem" :visible.sync="isShowRescue" :show-close=false @close="rescueDgClose">
                                <div class="dialog-title">
                                    救援系统
                                    <span class="second-title">当您系统损坏时，可进入救援模式，您的系统盘将会挂载作为数据盘</span>
                                </div>
                                <div class="dialog-main">
                                    <div class="label">救援系统类型</div>
                                    <el-select class="os-select" v-model="rescueData.type">
                                        <img class="os-img" :src="'/plugins/server/common_cloud/view/img/' + (rescueData.type==1?'Windows':'Linux') + '.png'" slot="prefix" alt="">
                                        <el-option value="1" label="Windows">
                                            <div class="os-item">
                                                <img class="item-os-img" src="/plugins/server/common_cloud/view/img/Windows.png" alt="">
                                                <span class="item-os-text">Windows</span>
                                            </div>
                                        </el-option>
                                        <el-option value="2" label="Linux">
                                            <div class="os-item">
                                                <img class="item-os-img" src="/plugins/server/common_cloud/view/img/Linux.png" alt="">
                                                <span class="item-os-text">Linux</span>
                                            </div>
                                        </el-option>
                                    </el-select>
                                    <div class="label">临时密码</div>
                                    <el-input class="pass-input" v-model="rescueData.password" placeholder="请输入内容">
                                        <div class="pass-btn" slot="suffix" @click="autoPass">随机生成</div>
                                    </el-input>
                                    <div class="alert">
                                        <el-alert :title="errText" type="error" show-icon :closable="false" v-show="errText">
                                        </el-alert>
                                        <div class="remind" v-show="!errText">请妥善保存当前密码，该密码不会二次使用</div>
                                    </div>
                                </div>
                                <div class="dialog-footer">
                                    <div class="btn-ok" @click="rescueSub" v-loading="loading3">提交</div>
                                    <div class="btn-no" @click="rescueDgClose">取消</div>
                                </div>
                            </el-dialog>
                        </div>
                        <!-- 升降级弹窗 -->
                        <div class="upgrade-dialog">
                            <el-dialog width="6.8rem" :visible.sync="isShowUpgrade" :show-close=false @close="upgradeDgClose">
                                <div class="dialog-title">
                                    升降级
                                </div>
                                <div class="dialog-main">
                                    <div class="package package-now">
                                        <div class="label">当前配置</div>
                                        <div class="package-main">
                                            <span class="day-row">当前配置剩余天数：<span class="day">{{hostData.due_time |
                                                    formateDueDay}}</span> 天</span>
                                            <span class="money">{{commonData.currency_prefix +
                                                cloudData.first_payment_amount + '/' +
                                                hostData.billing_cycle_name}}</span>
                                            <div class="package-line"></div>
                                            <div class="description" v-html="cloudData.package.description"></div>
                                        </div>
                                    </div>
                                    <div class="package">
                                        <div class="label">可切换配置</div>
                                        <el-select class="package-select" v-model="upgradePackageId" @change="upgradeSelectChange">
                                            <el-option v-for="item in upgradeList" :key="item.id" :value="item.id" :label="item.name"></el-option>
                                        </el-select>
                                        <div class="package-main package-upgrade">
                                            <span class="money">{{commonData.currency_prefix}}{{changeUpgradeData.money}}/{{changeUpgradeData.duration}}</span>
                                            <div class="package-line"></div>
                                            <div class="description" v-html="changeUpgradeData.description"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert">
                                    <el-alert :title="errText" type="error" show-icon :closable="false" v-show="errText">
                                    </el-alert>
                                </div>
                                <div class="dialog-footer">
                                    <span class="money-text">
                                        切换所需费用：
                                    </span>
                                    <span class="money-num">{{commonData.currency_prefix + upPrice}}</span>
                                    <div class="btn-ok" @click="upgradeSub" v-loading="loading4">提交</div>
                                    <div class="btn-no" @click="upgradeDgClose">取消</div>
                                </div>
                            </el-dialog>
                        </div>
                        <!-- 重置密码弹窗 -->
                        <div class="repass-dialog">
                            <el-dialog width="6.8rem" :visible.sync="isShowRePass" :show-close=false @close="rePassDgClose">
                                <div class="dialog-title">
                                    重置密码
                                    <span class="second-title">如您忘记密码，可直接输入新密码进行破解</span>
                                </div>
                                <div class="dialog-main">
                                    <div class="label">
                                        密码
                                        <el-popover placement="top-start" width="200" trigger="hover" content="1、长度6位及以上 2、只能输入大写字母、小写字母、数字、~!@#$&* ()_ -+=| {}[];:<>?,./中的特殊符号3、不能以“/”开头4、必须包含小写字母a~z，大写字母A~Z,字母0-9">
                                            <i class="el-icon-question" slot="reference"></i>
                                        </el-popover>

                                    </div>
                                    <el-input class="pass-input" v-model="rePassData.password" placeholder="请输入内容">
                                        <div class="pass-btn" slot="suffix" @click="autoPass">随机生成</div>
                                    </el-input>

                                    <div class="alert" v-show="powerStatus=='off'">
                                        <div class="title">当前操作需要实例在关机状态下进行</div>
                                        <div class="row"><span class="dot"></span> 为了避免数据丢失，实例将关机中断您的业务，请仔细确认。</div>
                                        <div class="row"><span class="dot"></span> 强制关机可能会导致数据丢失或文件系统损坏，您也可以主动关机后再进行操作
                                        </div>
                                    </div>
                                    <el-checkbox v-model="rePassData.checked" v-show="powerStatus=='off'">同意强制关机</el-checkbox>
                                    <div class="alert-err-text">
                                        <el-alert :title="errText" type="error" show-icon :closable="false" v-show="errText">
                                        </el-alert>
                                    </div>
                                </div>
                                <div class="dialog-footer">
                                    <div class="btn-ok" @click="rePassSub" v-loading="loading5">提交</div>
                                    <div class="btn-no" @click="rePassDgClose">取消</div>
                                </div>
                            </el-dialog>
                        </div>
                        <!-- 停用弹窗（删除实例） -->
                        <div class="refund-dialog">
                            <el-dialog width="6.8rem" :visible.sync="isShowRefund" :show-close=false @close="refundDgClose">
                                <div class="dialog-title">
                                    退款
                                </div>
                                <div class="dialog-main">
                                    <div class="label">产品信息</div>
                                    <div class="host-content">
                                        <div class="host-item">
                                            <div class="left">产品配置:</div>
                                            <div class="right">ahsdj</div>
                                        </div>
                                        <div class="host-item">
                                            <div class="left">订购时间:</div>
                                            <div class="right">{{refundPageData.host.create_time | formateTime}}</div>
                                        </div>
                                        <div class="host-item">
                                            <div class="left">订购金额:</div>
                                            <div class="right">{{commonData.currency_prefix + refundPageData.host.first_payment_amount}}</div>
                                        </div>
                                    </div>
                                    <div class="label">停用原因</div>
                                    <el-select v-if="refundPageData.reason_custom == 0" v-model="refundParams.suspend_reason" multiple>
                                        <el-option v-for="item in refundPageData.reasons" :key="item.id" :value="item.id" :label="item.content"></el-option>
                                    </el-select>
                                    <el-input v-else v-model="refundParams.suspend_reason"></el-input>
                                    <div class="label">停用时间</div>
                                    <el-select v-model="refundParams.type">
                                        <el-option value="Expire" label="到期"></el-option>
                                        <el-option value="Immediate" label="立即"></el-option>
                                    </el-select>
                                    <div class="label">退款金额</div>
                                    <div class="amount-content">{{commonData.currency_prefix}}{{refundParams.type=='Immediate'?refundPageData.host.amount:'0.00'}}</div>
                                </div>
                                <div class="dialog-footer">
                                    <div class="btn-ok" @click="subRefund">确认退款</div>
                                    <div class="btn-no" @click="refundDgClose">取消</div>
                                </div>
                            </el-dialog>
                        </div>
                        <!-- 确认退出救援模式弹窗 -->
                        <div class="quitRescu">
                            <el-dialog width="6.8rem" :visible.sync="isShowQuit" :show-close=false @close="quitDgClose">
                                <div class="dialog-title">
                                    退出救援模式
                                </div>
                                <div class="dialog-main">
                                    您将退出救援模式请确认操作
                                </div>
                                <div class="dialog-footer">
                                    <div class="btn-ok" @click="reQuitSub">确认</div>
                                    <div class="btn-no" @click="quitDgClose">取消</div>
                                </div>
                            </el-dialog>
                        </div>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/js/cloudManager.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/cloud.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/cloudTop/cloudTop.js"></script>
    {include file="footer"}