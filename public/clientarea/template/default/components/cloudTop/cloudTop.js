const cloudTop = {
    template:
        `
    <div class="card-top">
        <div class="top-operation">
            <div class="operation-row1">
                <img @click="goBack" class="back-img" src="${url}/img/finance/back.png" />
                <span class="top-product-name">{{hostData.product_name}}</span>
                <span class="top-area">
                    <img class="country-img" :src="'/upload/common/country/' + cloudData.data_center.iso +'.png'">
                    <span class="country">{{cloudData.data_center.country_name}}</span>
                    <span class="city">{{cloudData.data_center.city}}</span>
                </span>
            </div>
            <div class="operation-row2">
                <div class="row2-left">
                    <span class="name">{{hostData.name}}</span>
                    <span class="ip">{{cloudData.ip}}</span>
                </div>
                <div class="row2-right">
                    <!-- 控制台 -->
                    <img class="console-img" src="${url}/img/cloudDetail/console.png" title="前往控制台" @click="getVncUrl" v-show="status != 'operating'">
                    <!-- 开关机 -->
                    <span class="on-off">
                        <el-popover placement="bottom"   v-model="onOffvisible"  trigger="click">
                            <div class="sure-remind">
                                <span class="text">是否确认 </span>
                                <span class="status">{{status == 'on'?'关机':'开机'}} </span>
                                <span>?</span>
                                <!-- 关机确认 -->
                                <span class="sure-btn" v-if="status == 'on'" @click="doPowerOff">确认</span>
                                <!-- 开机确认 -->
                                <span class="sure-btn" v-else @click="doPowerOn">确认</span>
                            </div>
                            <img :src="'${url}/img/cloudDetail/'+status+'.png'" :title="statusText" v-show="status != 'operating'" slot="reference">
                        </el-popover>

                        <i class="el-icon-loading" title="操作中"  v-show="status == 'operating'"></i>
                    </span>
                    
                    <!-- 重启 -->
                    <span class="restart">
                        <el-popover placement="bottom" v-model="rebotVisibel"  trigger="click">
                            <div class="sure-remind">
                                <span class="text">是否确认</span>
                                <span class="status">重启</span>
                                <span>?</span>
                                <span class="sure-btn" @click="doReboot">确认</span>
                            </div>
                            <img src="${url}/img/cloudDetail/restart.png" title="重启" v-show="status != 'operating'" slot="reference">
                        </el-popover>

                        <i class="el-icon-loading" title="操作中" v-show="status == 'operating'"></i>
                    </span>

                    <!-- 救援模式 -->
                    <img class="fault" src="${url}/img/cloudDetail/fault.png" v-show="isRescue && status != 'operating'" title="救援模式" >
                </div>
            </div>
            <div class="operation-row3">
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
        </div>
        <div class="top-msg">
            <!-- 实例信息 -->
            <div class="msg-l">
                <div class="system-t">
                    <img class="os-img" :src="'/plugins/server/common_cloud/view/img/' + cloudData.image.image_group_name + '.png'" alt="">
                    <span class="os">
                        <span class="os-text">{{lang.cloud_os}}</span>
                        <span class="os-name">{{cloudData.image.name}}</span>
                    </span>
                    <div class="re-btn" @click="showReinstall">{{lang.cloud_os_btn}}</div>
                </div>
                <div class="system-b">
                    <div class="b-item">
                        <div class="item-label">CPU</div>
                        <div class="item-val">{{cloudData.package.cpu + '核'}}</div>
                    </div>
                    <div class="b-item">
                        <div class="item-label">{{lang.cloud_memery}}</div>
                        <div class="item-val">{{(cloudData.package.memory/1024).toFixed(2) + 'G'}}</div>
                    </div>
                    <div class="b-item">
                        <div class="item-label">{{lang.cloud_bw}}</div>
                        <div class="item-val">{{cloudData.package.out_bw + 'Mbps'}}</div>
                    </div>
                    <div class="b-item">
                        <div class="item-label">{{lang.cloud_disk}}</div>
                        <div class="item-val">{{cloudData.package.system_disk_size + 'G'}}</div>
                    </div>
                </div>
            </div>
            <!-- 付款信息 --> 
            <div class="msg-r">
                <div class="r-t">
                    <div class="r-t-l">
                        <span class="r-t-l-text">{{lang.cloud_pay_title}}</span>
                        
                        <!-- <el-switch v-model="isShowPayMsg" active-color="#0052D9">
                        </el-switch>
                        <div class="help">?</div> -->
                    </div>

                    <!-- 续费 -->
                    <div class="r-t-r">
                        <span class="r-t-r-text">{{lang.cloud_re_text + ':' + commonData.currency_prefix + hostData.renew_amount + commonData.currency_suffix}}</span>
                        <span class="renew-btn" @click="showRenew" v-if="!refundData || (refundData && refundData.status=='Cancelled') || (refundData && refundData.status=='Reject')">{{lang.cloud_re_btn}}</span>
                        <span class="renew-btn-disable" v-else>{{lang.cloud_re_btn}}</span>
                        <span class="refund-status" v-if="refundData && refundData.status != 'Cancelled' && refundData.status != 'Reject'">{{refundStatus[refundData.status]}}</span>
                        <span class="refund-stop-btn" v-if="refundData && refundData.status=='Pending'" @click="quitRefund">取消停用</span>
                        <span class="refund-btn" @click="showRefund" v-if="!refundData || (refundData && (refundData.status=='Reject')) || (refundData && (refundData.status=='Cancelled'))">停用</span>
                    </div>
                </div>
                <div class="r-b">
                    <div class="row">
                        <div class="row-l">
                            <div class="label">{{lang.cloud_first_pay}}:</div>
                            <div class="value">{{commonData.currency_prefix + hostData.first_payment_amount + commonData.currency_suffix}}</div>
                        </div>
                        <div class="row-r">
                            <div class="label">{{lang.cloud_pay_style}}:</div>
                            <div class="value">{{hostData.billing_cycle_name + '付'}}</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="row-l">
                            <div class="label">{{lang.cloud_creat_time}}:</div>
                            <div class="value">{{hostData.active_time | formateTime}}</div>
                        </div>
                        <div class="row-r">
                            <div class="label">{{lang.cloud_code}}:</div>
                            <div class="value" :title="codeString">{{codeString}}</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="row-l">
                            <div class="label">{{lang.cloud_due_time}}:</div>
                            <div class="value">{{hostData.due_time | formateTime}}</div>
                        </div>
                        <div class="row-r">
                            <div class="label">{{lang.cloud_rest_day}}:</div>
                            <div class="value">{{hostData.due_time | formateDueDay}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="refund-msg">
            <!-- 停用成功 -->
            <div class="refund-success" v-if="refundData && refundData.status == 'Suspending'">
                (产品于{{refundData.create_time | formateTime}}申请 {{refundData.type=='Expire'?'到期退款':'立即退款'}}，于 {{refundData.type=='Expire'?'到期后':'通过当天24点后'}} 自动删除)
            </div>
            <!-- 停用失败 -->
            <div class="refund-fail"  v-if="refundData && refundData.status == 'Reject'">
                (产品于{{refundData.create_time | formateTime}}申请 {{refundData.type=='Expire'?'到期退款':'立即退款'}} 失败，

                <el-popover
                placement="top-start"
                trigger="hover"
                >
                    <span>{{refundData.reject_reason}}</span>
                    <span class="reason-text" slot="reference">查看原因</span>
                </el-popover>
                
                )
            </div>
        </div>

    <el-tabs class="tabs" v-model="activeName" @tab-click="handleClick">
        <el-tab-pane label="统计图表" v-if="false" name="1"></el-tab-pane>
        <el-tab-pane label="管理" name="2"></el-tab-pane>
        <el-tab-pane label="磁盘" name="3"></el-tab-pane>
        <el-tab-pane label="网络" name="4"></el-tab-pane>
        <el-tab-pane label="备份与快照" name="5"></el-tab-pane>
        <el-tab-pane label="日志" name="6"></el-tab-pane>
    </el-tabs>


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
    
    <!-- 重装系统弹窗 -->
    <div class="reinstall-dialog">
        <el-dialog width="6.2rem" :visible.sync="isShowReinstallDialog" :show-close=false @close="reinstallDgClose">
            <div class="dialog-title">
                重装系统
            </div>
            <div class="dialog-main">
                <div class="label">操作系统</div>
                <div class="os-content">
                    <!-- 镜像组选择框 -->
                    <el-select class="os-select os-group-select" v-model="reinstallData.osGroupId" @change="osSelectGroupChange">
                        <img class="os-group-img" :src="osIcon" slot="prefix" alt="">
                        <el-option v-for="item in osData" :key='item.id' :value="item.id" :label="item.name">
                            <div class="option-label">
                                <img class="option-img" :src="'/plugins/server/common_cloud/view/img/' + item.name + '.png'" alt="">
                                <span class="option-text">{{item.name}}</span>
                            </div>
                        </el-option>
                    </el-select>
                    <!-- 镜像实际选择框 -->
                    <el-select class="os-select" v-model="reinstallData.osId" @change="osSelectChange">
                        <el-option v-for="item in osSelectData" :key="item.id" :value="item.id" :label="item.name +'-' + commonData.currency_prefix + item.price"></el-option>
                    </el-select>
                </div>
                <div class="label">密码</div>
                <div class="pass-content">
                    <el-select class="pass-select" v-model="reinstallData.type" >
                        <el-option value="pass" label="密码"></el-option>
                        
                    </el-select>
                    <i class="el-icon-question help-icon"></i>
                    <el-input class="pass-input" v-model="reinstallData.password" placeholder="请输入内容" v-show="reinstallData.type=='pass'">
                        <div class="pass-btn" slot="suffix" @click="autoPass">随机生成</div>
                    </el-input>
                    <div class="key-select" v-show="reinstallData.type=='key'">
                        <el-select v-model="reinstallData.key">
                            <el-option v-for="item in sshKeyData" :key="item.id" :value="item.id"></el-option>
                        </el-select>
                    </div>
                </div>
                <div class="label">端口</div>
                <el-input class="pass-input" v-model="reinstallData.port" placeholder="请输入内容">
                    <div class="pass-btn" slot="suffix" @click="autoPort">随机生成</div>
                </el-input>

                <div class="pay-img" v-show = "isPayImg">
                    当前系统为付费系统，{{commonData.currency_prefix + payMoney + '/次'}}
                    <span class="buy-btn" @click="payImg">立即购买</span>
                </div>

                <div class="alert">
                    <el-alert
                    :title="errText"
                    type="error"
                    show-icon
                    :closable="false"
                    v-show="errText">
                    </el-alert>
                    
                    <div class="remind" v-show="!errText">请妥善保存当前密码，该密码不会二次出现</div>
                </div>
            </div>
            <div class="dialog-footer">
                <div class="btn-ok" @click="doReinstall">提交</div>
                <div class="btn-no" @click="reinstallDgClose">取消</div>
            </div>
        </el-dialog>
    </div>


    <!-- 续费弹窗 -->
    <div class="renew-dialog">
        <el-dialog width="6.9rem" :visible.sync="isShowRenew" :show-close=false @close="renewDgClose">
            <div class="dialog-title">
                续费
            </div>
            <div class="dialog-main">
                <div class="renew-content">
                    <div class="renew-item" :class="renewActiveId==item.id?'renew-active':''" v-for="item in renewPageData" :key="item.id" @click="renewItemChange(item)">
                        <div class="item-top">{{item.billing_cycle}}</div>
                        <div class="item-bottom">{{commonData.currency_prefix + item.price}}</div>
                        <i class="el-icon-check check" v-show="renewActiveId==item.id"></i>
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
                        <div class="right">
                            <div class="right-row">
                                soan
                            </div>
                        </div>
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

    <pay-dialog ref="topPayDialog" @payok="paySuccess" @paycancel="payCancel"></pay-dialog>
</div>
    `,
    created() {
        // 获取产品id
        this.id = location.href.split('?')[1].split('=')[1]
        // this.id = 5315
        // 获取通用信息
        this.getCommonData()
        // 获取产品详情
        this.getHostDetail()
        // 获取实例详情
        this.getCloudDetail()

        // 获取ssh列表
        // this.getSshKey()
        // 获取实例状态
        this.getCloudStatus()
        // 获取产品停用信息
        this.getRefundMsg()
        // 优惠码信息
        this.getPromoCode()
        // 获取救援模式状态
        this.getRemoteInfo()
    },
    components: {
        payDialog,
    },
    data() {
        return {
            commonData: {},
            // 实例id
            id: null,
            // 产品id
            product_id: 0,
            // 实例状态
            status: 'operating',
            // 实例状态描述
            statusText: '',
            // 是否救援系统
            isRescue: false,
            // 产品详情
            hostData: {},
            // 实例详情
            cloudData: {
                data_center: {
                    iso: 'CN'
                },
                image: {
                    icon: ''
                },
                package: {
                    cpu: ''
                },
                iconName: 'Windows'
            },
            // 是否显示支付信息
            isShowPayMsg: false,
            imgBaseUrl: '',
            // 是否显示添加备注弹窗
            isShowNotesDialog: false,
            // 备份输入框内容
            notesValue: '',
            // 显示重装系统弹窗
            isShowReinstallDialog: false,
            // 重装系统弹窗内容
            reinstallData: {
                image_id: null,
                password: null,
                ssh_key_id: null,
                port: null,
                osGroupId: null,
                osId: null,
                type: 'pass'
            },
            // 镜像数据
            osData: [],
            // 镜像版本选择框数据
            osSelectData: [],
            // 镜像图片地址
            osIcon: '',
            // Shhkey列表
            sshKeyData: [],
            // 错误提示信息
            errText: '',
            // 镜像是否需要付费
            isPayImg: false,
            payMoney: 0,
            onOffvisible: false,
            rebotVisibel: false,
            codeString: '',
            // 停用信息
            refundData: {

            },
            // 停用状态
            refundStatus: {
                Pending: "待审核",
                Suspending: "待停用",
                Suspend: "停用中",
                Suspended: "已停用",
                Refund: "已退款",
                Reject: "审核驳回",
                Cancelled: "已取消"
            },

            // 停用相关
            // 是否显示停用弹窗
            isShowRefund: false,
            // 停用页面信息
            refundPageData: {
                host: {
                    create_time: 0,
                    first_payment_amount: 0
                }
            },
            // 停用页面参数
            refundParams: {
                host_id: 0,
                suspend_reason: null,
                type: 'Expire'
            },


            // 续费
            // 显示续费弹窗
            isShowRenew: false,
            // 续费页面信息
            renewPageData: [],
            // 续费参数
            renewParams: {
                id: 0,
                billing_cycle: '',
                price: 0
            },
            renewActiveId: '',
            renewOrderId: 0,
            isShowRefund: false,

        }
    },
    props: {
        activeName: {
            type: String,
            default: "1"
        },
    },
    filters: {
        formateTime(time) {
            if (time && time !== 0) {
                return formateDate(time * 1000)
            } else {
                return "--"
            }
        },
        // 返回剩余到期时间
        formateDueDay(time) {
            return Math.floor((time * 1000 - Date.now()) / (1000 * 60 * 60 * 24))
        }
    },
    methods: {
        // 跳转对应页面
        handleClick() {
            switch (this.activeName) {
                case '1':
                    location.href = `cloudDetail.html?id=${this.id}`
                    break;
                case '2':
                    location.href = `cloudManager.html?id=${this.id}`
                    break;
                case '3':
                    location.href = `cloudDisk.html?id=${this.id}`
                    break;
                case '4':
                    location.href = `cloudNet.html?id=${this.id}`
                    break;
                case '5':
                    location.href = `cloudBackupSnapshot.html?id=${this.id}`
                    break;
                case '6':
                    location.href = `cloudLog.html?id=${this.id}`
                    break;
            }
        },
        // 获取通用配置
        getCommonData() {
            getCommon().then(res => {
                if (res.data.status === 200) {
                    this.commonData = res.data.data
                    localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
                    document.title = this.commonData.website_name + '-产品详情'
                }
            })
        },
        // 获取产品详情
        getHostDetail() {
            const params = {
                id: this.id
            }
            hostDetail(params).then(res => {
                if (res.data.status === 200) {
                    this.hostData = res.data.data.host
                    this.product_id = this.hostData.product_id
                    // 获取镜像数据
                    this.getImage()
                    console.log(this.product_id);
                }
            })
        },
        // 获取实例详情
        getCloudDetail() {
            const params = {
                id: this.id
            }
            cloudDetail(params).then(res => {
                if (res.data.status === 200) {
                    this.cloudData = res.data.data
                    this.$emit('getclouddetail', this.cloudData)
                }
            })
        },
        // 关闭备注弹窗
        notesDgClose() {
            this.isShowNotesDialog = false
        },
        // 显示 修改备注 弹窗
        doEditNotes() {
            this.isShowNotesDialog = true
            this.notesValue = this.hostData.notes
        },
        // 修改备注提交
        subNotes() {
            const params = {
                id: this.id,
                notes: this.notesValue
            }
            editNotes(params).then(res => {
                if (res.data.status === 200) {
                    // 重新拉取产品详情
                    this.getHostDetail()
                    this.$message({
                        message: '修改成功',
                        type: 'success'
                    });
                    this.isShowNotesDialog = false
                }
            }).catch(err => {
                this.$message.error(err.data.msg);
            })
        },
        // 返回产品列表页
        goBack() {
            location.href = "cloudList.html"
        },
        // 关闭重装系统弹窗
        reinstallDgClose() {
            this.isShowReinstallDialog = false
        },
        // 展示重装系统弹窗
        showReinstall() {
            this.errText = ''
            this.reinstallData.password = null
            this.reinstallData.key = null
            this.reinstallData.port = null
            this.isShowReinstallDialog = true
        },
        // 提交重装系统
        doReinstall() {
            let isPass = true
            const data = this.reinstallData

            if (!data.osId) {
                isPass = false
                this.errText = "请选择操作系统"
                return false
            }

            if (!data.port) {
                isPass = false
                this.errText = "请输入端口号"
            }

            if (data.type == 'pass') {
                if (!data.password) {
                    isPass = false
                    this.errText = "请输入密码"
                    return false
                }
            } else {
                if (!data.key) {
                    isPass = false
                    this.errText = "请选择SSHKey"
                    return false
                }
            }

            if (isPass) {
                this.errText = ""
                let params = {
                    id: this.id,
                    image_id: data.osId,
                    port: data.port
                }

                if (data.type == 'pass') {
                    params.password = data.password
                } else {
                    params.ssh_key_id = data.key
                }


                // 调用重装系统接口
                reinstall(params).then(res => {
                    if (res.data.status == 200) {
                        this.$message.success(res.data.msg)
                        this.isShowReinstallDialog = false

                    }
                }).catch(err => {
                    this.errText = err.data.msg
                })
            }

        },
        // 检查产品是否购买过镜像
        doCheckImage() {
            const params = {
                id: this.id,
                image_id: this.reinstallData.osId
            }
            checkImage(params).then(res => {
                if (res.data.status === 200) {
                    this.payMoney = res.data.data.price
                    if (this.payMoney > 0) {
                        this.isPayImg = true
                    } else {
                        this.isPayImg = false
                    }
                }
            })
        },
        // 购买镜像
        payImg() {
            const params = {
                id: this.id,
                image_id: this.reinstallData.osId
            }
            imageOrder(params).then(res => {
                if (res.data.status === 200) {
                    const orderId = res.data.data.id
                    const amount = this.payMoney
                    this.$refs.topPayDialog.showPayDialog(orderId, amount)
                }
            })
        },
        // 获取镜像数据
        getImage() {
            const params = {
                id: this.product_id
            }
            image(params).then(res => {
                if (res.data.status === 200) {
                    this.osData = res.data.data.list
                    this.osSelectData = this.osData[0].image
                    this.reinstallData.osGroupId = this.osData[0].id
                    this.osIcon = "/plugins/server/common_cloud/view/img/" + this.osData[0].name + '.png'
                    this.reinstallData.osId = this.osData[0].image[0].id
                }
            })
        },
        // 镜像分组改变时
        osSelectGroupChange(e) {
            this.osData.map(item => {
                if (item.id == e) {
                    this.osSelectData = item.image
                    this.osIcon = "/plugins/server/common_cloud/view/img/" + item.name + '.png'
                    this.reinstallData.osId = item.image[0].id
                    this.doCheckImage()
                }
            })
        },
        // 镜像版本改变时
        osSelectChange(e) {
            this.doCheckImage()
        },
        // 随机生成密码
        autoPass() {
            let pass = randomCoding(1) + ')' + genEnCode(9, 1, 1, 0, 1, 0)
            this.reinstallData.password = pass
        },
        // 随机生成port
        autoPort() {
            this.reinstallData.port = genEnCode(3, 1, 0, 0, 0, 0)
        },
        // 获取SSH秘钥列表
        getSshKey() {
            const params = {
                page: 1,
                limit: 1000,
                orderby: "id",
                sort: "desc"
            }
            sshKey(params).then(res => {
                if (res.data.status === 200) {
                    this.sshKeyData = res.data.data.list
                    console.log(this.sshKeyData);
                }
            })
        },
        // 获取实例状态
        getCloudStatus() {
            const params = {
                id: this.id
            }
            cloudStatus(params).then(res => {
                if (res.status === 200) {
                    this.status = res.data.data.status
                    this.statusText = res.data.data.desc
                    if (this.status == 'operating') {
                        this.getCloudStatus()
                    } else {
                        console.log("ss");
                        this.$emit('getstatus', res.data.data.status)
                    }
                }
            }).catch(err => {

                this.getCloudStatus()
            })
        },
        // 获取救援模式状态
        getRemoteInfo() {
            const params = {
                id: this.id
            }

            remoteInfo(params).then(res => {
                if (res.data.status === 200) {
                    this.isRescue = (res.data.data.rescue == 1)
                    this.$emit('getrescuestatus', this.isRescue)
                }
            })
        },
        // 控制台点击
        getVncUrl() {
            const params = {
                id: this.id
            }
            vncUrl(params).then(res => {
                if (res.data.status === 200) {
                    window.open(res.data.data.url);
                }
            }).catch(err => {
                this.$message.error(err.data.msg)
            })
        },
        // 开机
        doPowerOn() {
            this.onOffvisible = false
            const params = {
                id: this.id
            }
            powerOn(params).then(res => {
                if (res.data.status === 200) {
                    this.$message.success("开机发起成功!")
                    this.status = 'operating'
                    this.getCloudStatus()
                }
            }).catch(err => {
                this.$message.error(err.data.msg)
            })
        },
        // 关机
        doPowerOff() {
            this.onOffvisible = false
            const params = {
                id: this.id
            }
            powerOff(params).then(res => {
                if (res.data.status === 200) {
                    this.$message.success("关机发起成功!")
                    this.status = 'operating'
                    this.getCloudStatus()
                }
            }).catch(err => {
                this.$message.error(err.data.msg)
            })
        },
        // 重启
        doReboot() {
            this.rebotVisibel = false
            const params = {
                id: this.id
            }
            reboot(params).then(res => {
                if (res.data.status === 200) {
                    this.$message.success("重启发起成功!")
                    this.status = 'operating'
                    this.getCloudStatus()
                }
            }).catch(err => {
                this.$message.error(err.data.msg)
            })
        },
        // 强制重启
        doHardReboot() {
            const params = {
                id: this.id
            }
            hardReboot(params).then(res => {
                if (res.data.status === 200) {
                    this.$message.success("强制重启发起成功!")
                    this.status = 'operating'
                    this.getCloudStatus()
                }
            }).catch(err => {
                this.$message.error(err.data.msg)
            })
        },
        // 强制关机
        doHardOff() {
            const params = {
                id: this.id
            }
            hardOff(params).then(res => {
                if (res.data.status === 200) {
                    this.$message.success("强制重启发起成功!")
                    this.status = 'operating'
                    this.getCloudStatus()
                }
            }).catch(err => {
                this.$message.error(err.data.msg)
            })
        },
        // 获取产品停用信息
        getRefundMsg() {
            const params = {
                id: this.id
            }
            refundMsg(params).then(res => {
                if (res.data.status === 200) {
                    this.refundData = res.data.data.refund
                }
            })
        },
        // 支付成功回调
        paySuccess(e) {
            if (e == this.renewOrderId) {
                // 刷新实例详情
                this.getHostDetail()
                return true
            }
            // 重新检查当前选择镜像是否购买
            this.doCheckImage()


        },
        // 取消支付回调
        payCancel(e) {
            console.log(e);
        },
        // 获取优惠码信息
        getPromoCode() {
            const params = {
                id: this.id
            }
            promoCode(params).then(res => {
                if (res.data.status === 200) {
                    let codes = res.data.data.promo_code
                    console.log(codes);
                    let code = ''
                    codes.map(item => {
                        code += item + ","
                    })
                    code = code.slice(0, -1)
                    this.codeString = code
                }
            })
        },
        // 显示续费弹窗
        showRenew() {
            // 获取续费页面信息
            const params = {
                id: this.id,
            }
            renewPage(params).then(res => {
                if (res.data.status === 200) {
                    this.renewPageData = res.data.data.host

                    this.renewActiveId = this.renewPageData[0].id
                    this.renewParams.billing_cycle = this.renewPageData[0].billing_cycle
                    this.renewParams.price = this.renewPageData[0].price
                    this.isShowRenew = true

                }
            }).catch(err => {
                this.$message.error(err.data.msg)
            })

        },
        // 续费弹窗关闭
        renewDgClose() {
            this.isShowRenew = false
        },
        // 续费提交
        subRenew() {
            const params = {
                id: this.id,
                billing_cycle: this.renewParams.billing_cycle,
                customfield: {
                    promo_code: []
                }
            }
            renew(params).then(res => {
                if (res.data.status === 200) {
                    this.isShowRenew = false
                    this.renewOrderId = res.data.data.id
                    const orderId = res.data.data.id
                    const amount = this.renewParams.price
                    this.$refs.topPayDialog.showPayDialog(orderId, amount)
                }
            })
        },
        // 续费周期点击
        renewItemChange(item) {
            this.renewActiveId = item.id
            this.renewParams.billing_cycle = item.billing_cycle
            this.renewParams.price = item.price
        },
        // 取消停用
        quitRefund() {
            const params = {
                id: this.refundData.id
            }
            cancel(params).then(res => {
                if (res.data.status == 200) {
                    this.$message.success("取消停用成功")
                    this.getRefundMsg()
                }
            }).catch(err => {
                this.$message.error(err.data.msg)
            })
        },
        // 关闭停用
        refundDgClose() {

        },
        // 删除实例点击
        showRefund() {
            const params = {
                host_id: this.id
            }
            refundMsg(params).then(res => {
                if (res.data.status === 200) {
                    console.log(res);
                }
            })
            // 获取停用页面信息
            refundPage(params).then(res => {
                if (res.data.status == 200) {
                    this.refundPageData = res.data.data
                    if (this.refundPageData.allow_refund === 0) {
                        this.$message.warning("不支持退款")
                    } else {
                        this.isShowRefund = true
                    }
                }
            })
        },
        // 关闭停用弹窗
        refundDgClose() {
            this.isShowRefund = false
        },
        // 停用弹窗提交
        subRefund() {
            const params = {
                host_id: this.id,
                suspend_reason: this.refundParams.suspend_reason,
                type: this.refundParams.type
            }
            if (!params.suspend_reason) {
                this.$message.error("请选择停用原因")
                return false
            }
            if (!params.type) {
                this.$message.error("请选择停用时间")
                return false
            }

            refund(params).then(res => {
                if (res.data.status == 200) {
                    this.$message.success("停用申请成功！")
                    this.isShowRefund = false
                    this.getRefundMsg()
                }
            }).catch(err => {
                this.$message.error(err.data.msg)
            })
        },

    },
}