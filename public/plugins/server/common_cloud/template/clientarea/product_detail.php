<!-- 统计图表 -->
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/plugins/server/common_cloud/template/clientarea/css/cloudTop.css">
<link rel="stylesheet" href="/plugins/server/common_cloud/template/clientarea/css/cloudDetail.css">

<div class="template" id="product_detail_cloud">
    <!-- 自己的东西 -->
    <div class="main-card">
        <div class="card-top">
            <div class="top-operation">
                <div class="operation-row1">

                    <img @click="goBack" class="back-img" src="/plugins/server/common_cloud/template/clientarea/img/finance/back.png" />
                    <span class="top-product-name">{{hostData.product_name}}</span>
                    <div class="host-status" :class="hostData.status">{{hostData.status_name}}</div>
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
                    <div class="row2-right" v-show="hostData.status == 'Active'">
                        <!-- 停用-->
                        {foreach $addons as $addon}
                        {if ($addon.name=='IdcsmartRefund')}
                        <span class="refund">
                            <span class="refund-status" v-if="refundData && refundData.status != 'Cancelled' && refundData.status != 'Reject'">{{refundStatus[refundData.status]}}</span>
                            <span class="refund-stop-btn" v-if="refundData && (refundData.status=='Pending' || refundData.status=='Suspend' || refundData.status=='Suspending')" @click="quitRefund">{{lang.common_cloud_btn8}}</span>
                            <span class="refund-btn" @click="showRefund" v-if="!refundData || (refundData && (refundData.status=='Reject')) || (refundData && (refundData.status=='Cancelled'))">{{lang.common_cloud_btn9}}</span>
                        </span>
                        {/if}
                        {/foreach}
                        <!-- 控制台 -->
                        <img class="console-img" src="/plugins/server/common_cloud/template/clientarea/img/cloudDetail/console.png" :title="lang.common_cloud_text8" @click="doGetVncUrl" v-show="status != 'operating'">
                        <!-- 开关机 -->
                        <span class="on-off">
                            <el-popover placement="bottom" v-model="onOffvisible" trigger="click">
                                <div class="sure-remind">
                                    <span class="text">{{lang.common_cloud_text9}}</span>
                                    <span class="status">{{status == 'on'?lang.common_cloud_text11:lang.common_cloud_text10}} </span>
                                    <span>?</span>
                                    <!-- 关机确认 -->
                                    <span class="sure-btn" v-if="status == 'on'" @click="doPowerOff">{{lang.common_cloud_btn10}}</span>
                                    <!-- 开机确认 -->
                                    <span class="sure-btn" v-else @click="doPowerOn">{{lang.common_cloud_btn10}}</span>
                                </div>
                                <img :src="'/plugins/server/common_cloud/template/clientarea/img/cloudDetail/'+status+'.png'" :title="statusText" v-show="(status != 'operating') && (status != 'fault')" slot="reference">
                            </el-popover>
                            <i class="el-icon-loading" :title="lang.common_cloud_text12" v-show="status == 'operating'"></i>
                            <img :src="'/plugins/server/common_cloud/template/clientarea/img/cloudDetail/'+status+'.png'" :title="statusText" v-show="status == 'fault'">
                        </span>

                        <!-- 重启 -->
                        <span class="restart">
                            <el-popover placement="bottom" v-model="rebotVisibel" trigger="click">
                                <div class="sure-remind">
                                    <span class="text">{{lang.common_cloud_text9}}</span>
                                    <span class="status">{{lang.common_cloud_text13}}</span>
                                    <span>?</span>
                                    <span class="sure-btn" @click="doReboot">{{lang.common_cloud_btn10}}</span>
                                </div>
                                <img src="/plugins/server/common_cloud/template/clientarea/img/cloudDetail/restart.png" :title="lang.common_cloud_text13" v-show="status != 'operating'" slot="reference">
                            </el-popover>

                            <!-- <i class="el-icon-loading" :title="lang.common_cloud_text12" v-show="status == 'operating'"></i> -->
                        </span>

                        <!-- 救援模式 -->
                        <img class="fault" src="/plugins/server/common_cloud/template/clientarea/img/cloudDetail/fault.png" v-show="isRescue && status != 'operating'" :title="lang.common_cloud_text14">
                    </div>
                </div>
                <div class="operation-row3" v-show="hostData.status == 'Active'">
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
            {foreach $addons as $addon}
            {if ($addon.name=='IdcsmartRefund')}
            <div class="refund-msg">
                <!-- 停用成功 -->
                <div class="refund-success" v-if="refundData && refundData.status == 'Suspending'">
                    ({{lang.common_cloud_tip6}}{{refundData.create_time | formateTime}}{{lang.common_cloud_tip7}} {{refundData.type=='Expire'?lang.common_cloud_tip8:lang.common_cloud_tip9}}，{{lang.common_cloud_tip13}} {{refundData.type=='Expire'? lang.common_cloud_tip10:lang.common_cloud_tip11}} {{lang.common_cloud_tip12}})
                </div>
                <!-- 停用失败 -->
                <div class="refund-fail" v-if="refundData && refundData.status == 'Reject'">
                    ({{lang.common_cloud_tip6}}{{refundData.create_time | formateTime}}{{lang.common_cloud_tip7}} {{refundData.type=='Expire'?lang.common_cloud_tip8:lang.common_cloud_tip9}} {{lang.common_cloud_tip14}}，

                    <el-popover placement="top-start" trigger="hover">
                        <span>{{refundData.reject_reason}}</span>
                        <span class="reason-text" slot="reference">{{lang.common_cloud_text15}}</span>
                    </el-popover>

                    )
                </div>
            </div>
            {/if}
            {/foreach}
            <div class="top-msg">
                <!-- 实例信息 -->
                <div class="msg-l">
                    <div class="system-t">
                        <img class="os-img" :src="'/plugins/server/common_cloud/view/img/' + cloudData.image.image_group_name + '.png'" alt="">
                        <span class="os">
                            <span class="os-text">{{lang.cloud_os}}</span>
                            <span class="os-name">{{cloudData.image.name}}</span>
                        </span>
                        <div class="btn-port">
                            <div v-show="hostData.status == 'Active'" class="re-btn" @click="showReinstall">{{lang.cloud_os_btn}}</div>
                            <div class="port">{{lang.common_cloud_label13}}:{{rescueStatusData.port}}</div>
                        </div>


                        <div class="user-name-pass">
                            <div class="user-name">{{lang.common_cloud_label14}}：
                                <span> {{rescueStatusData.username}} </span>
                            </div>
                            <div class="user-pass">{{lang.common_cloud_label7}}：
                                <span v-show="isShowPass"> {{rescueStatusData.password}} </span>
                                <span v-show="!isShowPass"> {{passHidenCode}} </span>
                                <img class="eyes" :src="isShowPass?'/plugins/server/common_cloud/template/clientarea/img/cloud/pass-show.png':'/plugins/server/common_cloud/template/clientarea/img/cloud/pass-hide.png'" @click="isShowPass=!isShowPass" />
                            </div>
                        </div>

                    </div>
                    <div class="system-b">
                        <div class="b-item">
                            <div class="item-label">CPU</div>
                            <div class="item-val">{{cloudData.package.cpu + '核'}}</div>
                        </div>
                        <div class="b-item">
                            <div class="item-label">{{lang.cloud_memery}}</div>
                            <div class="item-val">{{(cloudData.package.memory/1024).toFixed(2) + 'GB'}}</div>
                        </div>
                        <div class="b-item">
                            <div class="item-label">{{lang.cloud_bw}}</div>
                            <div class="item-val">{{cloudData.package.out_bw + 'Mbps'}}</div>
                        </div>
                        <div class="b-item">
                            <div class="item-label">{{lang.cloud_disk}}</div>
                            <div class="item-val">{{cloudData.package.system_disk_size + 'GB'}}</div>
                        </div>
                    </div>
                </div>
                <!-- 付款信息 -->
                <div class="msg-r">
                    <div class="r-t">
                        <div class="r-t-l">
                            <span class="r-t-l-text">{{lang.cloud_pay_title}}</span>
                        </div>
                        <!-- 续费 -->
                        {foreach $addons as $addon}
                        {if ($addon.name=='IdcsmartRenew')}
                        <div class="r-t-r" v-show="hostData.status == 'Active'">
                            <span>{{lang.common_cloud_text16}}：</span>
                            <el-switch v-model="isShowPayMsg" active-color="#0052D9" @change="autoRenewChange">
                            </el-switch>
                            <el-popover placement="top" trigger="hover">
                                <div class="sure-remind">
                                    {{lang.common_cloud_tip15}}
                                </div>
                                <div class="help" slot="reference">?</div>
                            </el-popover>
                        </div>
                        {/if}
                        {/foreach}
                    </div>
                    <div class="r-b">
                        <div class="row">
                            <div class="row-l">
                                <div class="label">{{lang.cloud_due_time}}:</div>
                                <div class="value" :class="isRead?'red':''">{{hostData.due_time | formateTime}}</div>
                            </div>
                            <div class="row-r">
                                <div class="label">{{lang.cloud_creat_time}}:</div>
                                <div class="value">{{hostData.active_time | formateTime}}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="row-l">
                                <div class="label">{{lang.cloud_pay_style}}:</div>
                                <div class="value">{{hostData.billing_cycle_name + lang.common_cloud_text17}}</div>
                            </div>
                            <div class="row-r">
                                <div class="label">{{lang.cloud_first_pay}}:</div>
                                <div class="value">{{commonData.currency_prefix + hostData.first_payment_amount + commonData.currency_suffix}}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="row-l">
                                <div class="label">{{lang.cloud_re_text}}:</div>
                                <div class="value">{{commonData.currency_prefix + hostData.renew_amount + commonData.currency_suffix}}</div>
                                {foreach $addons as $addon}
                                {if ($addon.name=='IdcsmartRenew')}
                                <span v-show="hostData.status == 'Active'" v-loading="renewBtnLoading" class="renew-btn" @click="showRenew" v-if="!refundData || (refundData && refundData.status=='Cancelled') || (refundData && refundData.status=='Reject')">{{lang.cloud_re_btn}}</span>
                                <span v-show="hostData.status == 'Active'" class="renew-btn-disable" v-else>{{lang.cloud_re_btn}}</span>
                                {/if}
                                {/foreach}
                            </div>
                            <div class="row-r">
                                <div class="label">{{lang.cloud_code}}:</div>
                                <div class="value" :title="codeString">{{codeString?codeString:'--'}}</div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <el-tabs class="tabs" v-model="activeName" @tab-click="handleClick" v-show="hostData.status == 'Active'">
                <el-tab-pane :label="lang.common_cloud_tab1" name="1">
                    <el-select class="time-select" v-model="chartSelectValue" @change="chartSelectChange">
                        <el-option value='1' :label="lang.common_cloud_label15"></el-option>
                        <el-option value='2' :label="lang.common_cloud_label16"></el-option>
                        <el-option value='3' :label="lang.common_cloud_label17"></el-option>
                    </el-select>

                    <div class="echart-main">
                        <!-- cpu用量图 -->
                        <div id="cpu-echart" class="my-echart" v-loading="echartLoading1"></div>
                        <!-- 网络带宽 -->
                        <div id="bw-echart" class="my-echart" v-loading="echartLoading2"></div>
                        <!-- 磁盘IO -->
                        <div id="disk-io-echart" class="my-echart" v-loading="echartLoading3"></div>
                        <!-- 内存用量 -->
                        <div id="memory-echart" class="my-echart" v-loading="echartLoading4"></div>
                    </div>
                </el-tab-pane>
                <el-tab-pane :label="lang.common_cloud_tab2" name="2">
                    <!-- 管理 -->
                    <div class="manage-content">
                        <!-- 第一行 -->
                        <el-row>
                            <el-col :span="8">
                                <div class="manage-item">
                                    <div class="item-top">
                                        <el-select v-model="powerStatus">
                                            <el-option v-for="item in powerList" :key="item.id" :value="item.value" :label="item.label"></el-option>
                                        </el-select>
                                        <div class="item-top-btn" @click="showPowerDialog" v-loading="loading1">
                                            {{lang.common_cloud_btn10}}
                                        </div>
                                    </div>
                                    <div class="item-bottom">
                                        <div class="bottom-row">{{lang.common_cloud_tip16}}</div>
                                    </div>
                                </div>
                            </el-col>
                            <el-col :span="8">
                                <div class="manage-item">
                                    <div class="item-top-btn" @click="getVncUrl" v-loading="loading2">
                                        {{lang.common_cloud_btn11}}
                                    </div>
                                    <div class="item-bottom">
                                        <div class="bottom-row">{{lang.common_cloud_tip17}}</div>
                                        <div class="bottom-row">{{lang.common_cloud_tip18}}</div>
                                    </div>
                                </div>
                            </el-col>
                            <el-col :span="8">
                                <div class="manage-item">
                                    <div class="item-top-btn" @click="showRePass">
                                        {{lang.common_cloud_btn12}}
                                    </div>
                                    <div class="item-bottom">
                                        <div class="bottom-row">{{lang.common_cloud_tip19}}</div>
                                        <div class="bottom-row">{{lang.common_cloud_tip20}}</div>
                                    </div>
                                </div>
                            </el-col>

                        </el-row>
                        <!-- 第二行 -->
                        <el-row>
                            <el-col :span="8">
                                <div class="manage-item">
                                    <div class="item-top-btn" @click="showRescueDialog" v-if="!isRescue">
                                        {{lang.common_cloud_btn13}}
                                    </div>
                                    <div class="item-top-btn" @click="showQuitRescueDialog" v-else>
                                        {{lang.common_cloud_btn14}}
                                    </div>
                                    <div class="item-bottom">
                                        <div class="bottom-row">{{lang.common_cloud_tip21}}</div>
                                        <div class="bottom-row">{{lang.common_cloud_tip22}}</div>
                                    </div>
                                </div>
                            </el-col>
                            <el-col :span="8">
                                <div class="manage-item">
                                    <div class="item-top-btn" @click="showReinstall">
                                        {{lang.common_cloud_btn15}}
                                    </div>
                                    <div class="item-bottom">
                                        <div class="bottom-row">{{lang.common_cloud_tip23}}</div>
                                    </div>
                                </div>
                            </el-col>
                            <el-col :span="8">
                                <div class="manage-item">
                                    <div class="item-top-btn" @click="showUpgrade">
                                        {{lang.common_cloud_btn16}}
                                    </div>
                                    <div class="item-bottom">
                                        <div class="bottom-row">{{lang.common_cloud_tip24}}</div>
                                    </div>
                                </div>
                            </el-col>
                        </el-row>
                        <!-- 第三行 -->
                        <el-row>
                            <el-col :span="8">
                                <div class="manage-item">
                                    <div class="item-top-btn" style="background: #eee;cursor: not-allowed;color: #999;">
                                        {{lang.common_cloud_btn17}}
                                    </div>
                                    <div class="item-bottom">
                                        <div class="bottom-row">{{lang.common_cloud_tip25}}</div>
                                    </div>
                                </div>
                            </el-col>
                            <el-col :span="8">
                                <div class="manage-item">
                                    <div class="item-top-btn" style="background: #eee;cursor: not-allowed;color: #999;">
                                        {{lang.common_cloud_btn18}}
                                    </div>
                                    <div class="item-bottom">
                                        <div class="bottom-row">{{lang.common_cloud_tip26}}</div>
                                    </div>
                                </div>
                            </el-col>
                            <el-col :span="8">
                                <div class="manage-item">
                                    <div class="item-top-btn" style="background: #eee;cursor: not-allowed;color: #999;">
                                        {{lang.common_cloud_btn19}}
                                    </div>
                                    <div class="item-bottom">
                                        <div class="bottom-row">{{lang.common_cloud_tip27}}</div>
                                    </div>
                                </div>
                            </el-col>
                        </el-row>
                    </div>
                </el-tab-pane>
                <el-tab-pane :label="lang.common_cloud_tab3" name="3">
                    <div class="disk-top-operation">
                        <el-button v-if="diskList.length != 0" type="primary" class="btn1" @click="showExpansion">{{lang.common_cloud_btn20}}</el-button>
                        <el-button type="primary" class="btn2">{{lang.common_cloud_btn21}}</el-button>
                    </div>
                    <div class="no-disk" v-if="diskList.length == 0">
                        <span class="text">{{lang.common_cloud_text18}}</span>
                        <span class="text2">{{lang.common_cloud_text19}}</span>
                    </div>
                    <div class="yes-disk" v-else>
                        <div class="main-table">
                            <el-table v-loading="diskLoading" :data="diskList" style="width: 100%">
                                <el-table-column prop="name" :label="lang.common_cloud_label18" min-width="400" align="left">
                                </el-table-column>
                                <el-table-column prop="create_time" width="600" :label="lang.common_cloud_label19" align="left">
                                </el-table-column>
                                <el-table-column prop="size" :label="lang.common_cloud_label20" width="200" align="left" :show-overflow-tooltip="true">
                                    <template slot-scope="scope">
                                        <span>{{scope.row.size + 'G'}}</span>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                    </div>
                    <span v-show="(configData.buy_data_disk==1) && (diskList.length < configData.disk_max_num)" class="buy-btn" @click="showDg">{{lang.common_cloud_btn22}}</span>
                </el-tab-pane>
                <el-tab-pane :label="lang.common_cloud_tab4" name="4">
                    <div class="net">
                        <div class="title">{{lang.common_cloud_title3}}</div>
                        <div class="main_table">
                            <el-table v-loading="netLoading" :data="netDataList" style="width: 100%;margin-bottom: .2rem;">
                                <el-table-column prop="ip" :label="lang.common_cloud_label21" min-width="200" align="left">
                                </el-table-column>
                                <el-table-column prop="gateway" width="400" :label="lang.common_cloud_label22" align="left">
                                </el-table-column>
                                <el-table-column prop="subnet_mask" width="400" :label="lang.common_cloud_label23" align="left">
                                </el-table-column>
                            </el-table>
                            <pagination :page-data="netParams" @sizechange="netSizeChange" @currentchange="netCurrentChange">
                            </pagination>
                        </div>
                        <div class="title">{{lang.common_cloud_title4}}</div>
                        <div class="flow-content">
                            <div class="flow-item">
                                <div class="flow-label">{{lang.common_cloud_label24}}:</div>
                                <div class="flow-value">{{flowData.total}}</div>
                            </div>
                            <div class="flow-item">
                                <div class="flow-label">{{lang.common_cloud_label25}}:</div>
                                <div class="flow-value">{{flowData.leave}}</div>
                            </div>
                            <div class="flow-item">
                                <div class="flow-label">{{lang.common_cloud_label26}}:</div>
                                <div class="flow-value">{{flowData.reset_flow_date}}</div>
                            </div>
                        </div>

                        <el-select class="time-select" v-model="chartSelectValue" @change="chartSelectChange">
                            <el-option value='1' :label="lang.common_cloud_label15"></el-option>
                            <el-option value='2' :label="lang.common_cloud_label16"></el-option>
                            <el-option value='3' :label="lang.common_cloud_label17"></el-option>
                        </el-select>
                        <div class="echart-main">
                            <!-- 网络带宽 -->
                            <div id="bw2-echart" class="my-echart" v-loading="echartLoading2"></div>
                        </div>

                    </div>
                </el-tab-pane>
                <el-tab-pane :label="lang.common_cloud_tab5" name="5">
                    <!-- 备份 -->
                    <!-- 启用备份 -->
                    <div class="main-content" v-if="cloudData.backup_num > 0">
                        <div class="content-top">
                            <div class="top-title">{{lang.common_cloud_title5}}</div>
                            <div class="top-btn" @click="showCreateBs('back')">{{lang.common_cloud_btn23}}</div>
                        </div>
                        <div class="main-table">
                            <el-table v-loading="backLoading" :data="dataList1" style="width: 100%;">
                                <el-table-column prop="name" :label="lang.common_cloud_label27" width="300" align="left">
                                </el-table-column>
                                <el-table-column prop="create_time" width="300" :label="lang.common_cloud_label28" align="left">
                                    <template slot-scope="scope">
                                        {{scope.row.create_time | formateTime}}
                                    </template>
                                </el-table-column>
                                <el-table-column prop="notes" :label="lang.common_cloud_label29" min-width="400" align="left" :show-overflow-tooltip="true">
                                </el-table-column>
                                <el-table-column prop="type" :label="lang.common_cloud_label30" width="150" align="left">
                                    <template slot-scope="scope">
                                        <el-popover placement="top-start" trigger="hover">
                                            <div class="operation">
                                                <div class="operation-item" @click="showhyBs('back',scope.row)">{{lang.common_cloud_btn24}}</div>
                                                <div class="operation-item" @click="showDelBs('back',scope.row)">{{lang.common_cloud_btn25}}</div>
                                            </div>
                                            <span class="more-operation" slot="reference">
                                                <div class="dot"></div>
                                                <div class="dot"></div>
                                                <div class="dot"></div>
                                            </span>
                                        </el-popover>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                    </div>
                    <!-- 没有启用备份 -->
                    <div class="no-bs no-back" v-else>
                        <span class="no-bs-title">{{lang.common_cloud_title5}}</span>
                        <div class="no-bs-content">
                            <span class="text">{{lang.common_cloud_text20}}</span>
                            <div class="btn" v-if="configData.backup_enable == 1" @click="openBs('back')">{{lang.common_cloud_btn26}}</div>
                        </div>
                    </div>
                    <!-- 快照 -->
                    <div class="main-content" v-if="cloudData.snap_num > 0">
                        <div class="content-top">
                            <div class="top-title">{{lang.common_cloud_title6}}</div>
                            <div class="top-btn" @click="showCreateBs('snap')">{{lang.common_cloud_btn27}}</div>
                        </div>
                        <div class="main-table">
                            <el-table v-loading="snapLoading" :data="dataList2" style="width: 100%;margin-bottom: .2rem;">
                                <el-table-column prop="name" :label="lang.common_cloud_label31" width="300" align="left">
                                </el-table-column>
                                <el-table-column prop="create_time" width="300" :label="lang.common_cloud_label28" align="left">
                                    <template slot-scope="scope">
                                        {{scope.row.create_time | formateTime}}
                                    </template>
                                </el-table-column>
                                <el-table-column prop="notes" :label="lang.common_cloud_label29" min-width="400" align="left" :show-overflow-tooltip="true">
                                </el-table-column>
                                <el-table-column prop="type" :label="lang.common_cloud_label30" width="150" align="left">
                                    <template slot-scope="scope">
                                        <el-popover placement="top-start" trigger="hover">
                                            <div class="operation">
                                                <div class="operation-item" @click="showhyBs('snap',scope.row)">{{lang.common_cloud_btn24}}</div>
                                                <div class="operation-item" @click="showDelBs('snap',scope.row)">{{lang.common_cloud_btn25}}</div>
                                            </div>
                                            <span class="more-operation" slot="reference">
                                                <div class="dot"></div>
                                                <div class="dot"></div>
                                                <div class="dot"></div>
                                            </span>
                                        </el-popover>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                    </div>
                    <div class="no-bs no-snap" v-else>
                        <span class="no-bs-title">{{lang.common_cloud_title6}}</span>
                        <div class="no-bs-content">
                            <span class="text">{{lang.common_cloud_text21}}</span>
                            <div class="btn" v-if="configData.snap_enable == 1" @click="openBs('snap')">{{lang.common_cloud_btn26}}</div>
                        </div>
                    </div>
                </el-tab-pane>
                <el-tab-pane :label="lang.common_cloud_tab6" name="6">
                    <div class="log">
                        <div class="main_table">
                            <el-table v-loading="logLoading" :data="logDataList" style="width: 100%;margin-bottom: .2rem;">
                                <el-table-column prop="id" :label="lang.common_cloud_label32" width="400" align="left">
                                </el-table-column>
                                <el-table-column prop="create_time" width="400" :label="lang.common_cloud_label33" align="left">
                                    <template slot-scope="scope">
                                        <span>{{scope.row.create_time | formateTime}}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="description" :label="lang.common_cloud_label34" min-width="400" align="left" :show-overflow-tooltip="true">
                                </el-table-column>
                            </el-table>
                            <pagination :page-data="logParams" @sizechange="logSizeChange" @currentchange="logCurrentChange">
                            </pagination>
                        </div>
                    </div>
                </el-tab-pane>
            </el-tabs>


            <!-- 修改备注弹窗 -->
            <div class="notes-dialog">
                <el-dialog width="6.2rem" :visible.sync="isShowNotesDialog" :show-close=false @close="notesDgClose">
                    <div class="dialog-title">
                        {{hostData.notes?lang.common_cloud_title7:lang.common_cloud_title8}}
                    </div>
                    <div class="dialog-main">
                        <div class="label">{{lang.common_cloud_label29}}</div>
                        <el-input class="notes-input" v-model="notesValue"></el-input>
                    </div>
                    <div class="dialog-footer">
                        <div class="btn-ok" @click="subNotes">{{lang.common_cloud_btn28}}</div>
                        <div class="btn-no" @click="notesDgClose">{{lang.common_cloud_btn29}}</div>
                    </div>
                </el-dialog>
            </div>

            <!-- 重装系统弹窗 -->
            <div class="reinstall-dialog">
                <el-dialog width="6.2rem" :visible.sync="isShowReinstallDialog" :show-close=false @close="reinstallDgClose">
                    <div class="dialog-title">
                        {{lang.common_cloud_title9}}
                    </div>
                    <div class="dialog-main">
                        <div class="label">{{lang.common_cloud_label6}}</div>
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
                        <div class="label">{{lang.common_cloud_label7}}</div>
                        <div class="pass-content">
                            <el-select class="pass-select" v-model="reinstallData.type">
                                <el-option value="pass" :label="lang.common_cloud_label7"></el-option>

                            </el-select>
                            <el-popover placement="top-start" width="200" trigger="hover" content="1、长度6位及以上 2、只能输入大写字母、小写字母、数字、~!@#$&* ()_ -+=| {}[];:<>?,./中的特殊符号3、不能以“/”开头4、必须包含小写字母a~z，大写字母A~Z,字母0-9">
                                <i class="el-icon-question help-icon" slot="reference"></i>
                            </el-popover>
                            <el-input class="pass-input" v-model="reinstallData.password" placeholder="请输入内容" v-show="reinstallData.type=='pass'">
                                <div class="pass-btn" slot="suffix" @click="autoPass">{{lang.common_cloud_btn1}}</div>
                            </el-input>
                            <div class="key-select" v-show="reinstallData.type=='key'">
                                <el-select v-model="reinstallData.key">
                                    <el-option v-for="item in sshKeyData" :key="item.id" :value="item.id"></el-option>
                                </el-select>
                            </div>
                        </div>
                        <div class="label">{{lang.common_cloud_label13}}</div>
                        <el-input class="pass-input" v-model="reinstallData.port" placeholder="请输入内容">
                            <div class="pass-btn" slot="suffix" @click="autoPort">{{lang.common_cloud_btn1}}</div>
                        </el-input>

                        <div class="pay-img" v-show="isPayImg">
                            {{lang.common_cloud_tip28}},{{commonData.currency_prefix + payMoney + '/次'}}
                            <el-popover placement="top-start" width="200" trigger="hover">
                                <div class="show-config-list">等级折扣金额：{{commonData.currency_prefix}}{{ payDiscount }}</div>
                                <div class="show-config-list">优惠码折扣金额：{{commonData.currency_prefix}}{{ payCodePrice }}</div>
                                <i class="el-icon-warning-outline total-icon" slot="reference" v-if="isShowLevel || isShowPromo"></i>
                            </el-popover>

                            <span class="img-buy-btn" @click="payImg">{{lang.common_cloud_btn5}}</span>
                        </div>

                        <div class="alert">
                            <el-alert :title="errText" type="error" show-icon :closable="false" v-show="errText">
                            </el-alert>

                            <div class="remind" v-show="!errText">{{lang.common_cloud_tip29}}</div>
                        </div>
                    </div>
                    <div class="dialog-footer">
                        <div class="btn-ok" @click="doReinstall">{{lang.common_cloud_btn28}}</div>
                        <div class="btn-no" @click="reinstallDgClose">{{lang.common_cloud_btn29}}</div>
                    </div>
                </el-dialog>
            </div>


            <!-- 续费弹窗 -->
            <div class="renew-dialog">
                <el-dialog width="6.9rem" :visible.sync="isShowRenew" :show-close=false @close="renewDgClose">
                    <div class="dialog-title">
                        {{lang.common_cloud_title10}}
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
                                <div class="money" v-loading="renewLoading">
                                    <span class="text">{{lang.common_cloud_label11}}:</span> <span>{{commonData.currency_prefix}}{{renewParams.totalPrice | filterMoney}}</span>
                                    <el-popover placement="top-start" width="200" trigger="hover" v-if="isShowLevel || (isShowPromo && renewParams.isUseDiscountCode) || isShowCash">
                                    <div class="show-config-list">
                                        <p v-if="isShowLevel">{{lang.shoppingCar_tip_text2}}：{{commonData.currency_prefix}} {{ renewParams.clDiscount | filterMoney}}</p>
                                        <p v-if="isShowPromo && renewParams.isUseDiscountCode">{{lang.shoppingCar_tip_text4}}：{{commonData.currency_prefix}} {{ renewParams.code_discount | filterMoney }}</p>
                                        <p v-if="isShowCash && renewParams.customfield.voucher_get_id">代金券抵扣金额：{{commonData.currency_prefix}} {{ renewParams.cash_discount | filterMoney}}</p>
                                    </div>
                                    <i class="el-icon-warning-outline total-icon" slot="reference"></i>
                                    </el-popover>
                                    <p class="original-price" v-if="renewParams.totalPrice != renewParams.original_price">{{commonData.currency_prefix}} {{ renewParams.original_price | filterMoney}}</p>
                                    <div class="code-box">
                                        <!-- 代金券 -->
                                        <cash-coupon ref="cashRef" v-show="isShowCash && !cashObj.code" :currency_prefix="commonData.currency_prefix" @use-cash="reUseCash" scene='renew' :product_id="[product_id]" :price="renewParams.original_price"></cash-coupon>
                                        <!-- 优惠码 -->
                                        <discount-code v-show="isShowPromo && !renewParams.customfield.promo_code" @get-discount="getRenewDiscount(arguments)" scene='renew' :product_id="product_id" :amount="renewParams.original_price" :billing_cycle_time="renewParams.duration"></discount-code>
                                     </div>
                                    <div class="code-number-text">
                                        <div class="discount-codeNumber" v-show="renewParams.customfield.promo_code">{{ renewParams.customfield.promo_code }}<i class="el-icon-circle-close remove-discountCode" @click="removeRenewDiscountCode()"></i></div>
                                        <div class="cash-codeNumber" v-show="cashObj.code">{{ cashObj.code }}<i class="el-icon-circle-close remove-discountCode" @click="reRemoveCashCode()"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dialog-footer">
                        <div class="btn-ok" @click="subRenew">{{lang.common_cloud_btn30}}</div>
                        <div class="btn-no" @click="renewDgClose">{{lang.common_cloud_btn29}}</div>
                    </div>
                </el-dialog>
            </div>

            <!-- 停用弹窗（删除实例） -->
            <div class="refund-dialog">
                <el-dialog width="6.8rem" :visible.sync="isShowRefund" :show-close=false @close="refundDgClose">
                    <div class="dialog-title">
                        {{refundPageData.allow_refund == 1?lang.common_cloud_title11:lang.common_cloud_title12}}
                    </div>
                    <div class="dialog-main">
                        <div class="label">{{lang.common_cloud_label35}}</div>
                        <div class="host-content">
                            <div class="host-item">
                                <div class="left">{{lang.common_cloud_label36}}:</div>
                                <div class="right">
                                    <div class="right-row">
                                        <div class="right-row-item">{{cloudData.package.cpu}} 核CPU</div>
                                        <div class="right-row-item">{{(cloudData.package.memory/1024).toFixed(2) + 'GB 内存'}}</div>
                                        <div class="right-row-item">{{cloudData.package.system_disk_size}} GB 存储容量</div>
                                        <div class="right-row-item">{{cloudData.package.out_bw}} M 宽带</div>
                                    </div>
                                </div>
                            </div>
                            <div class="host-item">
                                <div class="left">{{lang.common_cloud_label37}}:</div>
                                <div class="right">{{refundPageData.host.create_time | formateTime}}</div>
                            </div>
                            <div class="host-item" v-if="refundPageData.allow_refund == 1">
                                <div class="left">{{lang.common_cloud_label38}}:</div>
                                <div class="right">{{commonData.currency_prefix + refundPageData.host.first_payment_amount}}</div>
                            </div>
                        </div>
                        <div class="label">{{lang.common_cloud_label39}}</div>
                        <el-select v-if="refundPageData.reason_custom == 0" v-model="refundParams.suspend_reason" multiple>
                            <el-option v-for="item in refundPageData.reasons" :key="item.id" :value="item.id" :label="item.content"></el-option>
                        </el-select>
                        <el-input v-else v-model="refundParams.suspend_reason"></el-input>
                        <div class="label">{{lang.common_cloud_label40}}</div>
                        <el-select v-model="refundParams.type">
                            <el-option value="Expire" :label="lang.common_cloud_label41"></el-option>
                            <el-option value="Immediate" :label="lang.common_cloud_label42"></el-option>
                        </el-select>
                        <div class="label" v-if="refundPageData.allow_refund == 1">{{lang.common_cloud_label43}}}</div>
                        <div class="amount-content" v-if="refundPageData.allow_refund == 1">{{commonData.currency_prefix}}{{refundParams.type=='Immediate'?refundPageData.host.amount:'0.00'}}</div>
                    </div>
                    <div class="dialog-footer">
                        <div class="btn-ok" @click="subRefund">{{refundPageData.allow_refund == 1? lang.common_cloud_btn31: lang.common_cloud_btn32}}</div>
                        <div class="btn-no" @click="refundDgClose">{{lang.common_cloud_btn29}}</div>
                    </div>
                </el-dialog>
            </div>

            <!-- 重置密码弹窗 -->
            <div class="repass-dialog">
                <el-dialog width="6.8rem" :visible.sync="isShowRePass" :show-close=false @close="rePassDgClose">
                    <div class="dialog-title">
                        {{lang.common_cloud_title13}}
                        <span class="second-title">{{lang.common_cloud_text23}}</span>
                    </div>
                    <div class="dialog-main">
                        <div class="label">
                            {{lang.common_cloud_label7}}
                            <el-popover placement="top-start" width="200" trigger="hover" content="1、长度6位及以上 2、只能输入大写字母、小写字母、数字、~!@#$&* ()_ -+=| {}[];:<>?,./中的特殊符号3、不能以“/”开头4、必须包含小写字母a~z，大写字母A~Z,字母0-9">
                                <i class="el-icon-question" slot="reference"></i>
                            </el-popover>

                        </div>
                        <el-input class="pass-input" v-model="rePassData.password" placeholder="请输入内容">
                            <div class="pass-btn" slot="suffix" @click="autoPass">{{lang.common_cloud_btn1}}</div>
                        </el-input>

                        <div class="alert" v-show="powerStatus=='off'">
                            <div class="title">{{lang.common_cloud_tip30}}</div>
                            <div class="row"><span class="dot"></span> {{lang.common_cloud_tip31}}</div>
                            <div class="row"><span class="dot"></span> {{lang.common_cloud_tip32}}
                            </div>
                        </div>
                        <el-checkbox v-model="rePassData.checked" v-show="powerStatus=='off'">{{lang.common_cloud_text24}}</el-checkbox>
                        <div class="alert-err-text">
                            <el-alert :title="errText" type="error" show-icon :closable="false" v-show="errText">
                            </el-alert>
                        </div>
                    </div>
                    <div class="dialog-footer">
                        <div class="btn-ok" @click="rePassSub" v-loading="loading5">{{lang.common_cloud_btn28}}}</div>
                        <div class="btn-no" @click="rePassDgClose">{{lang.common_cloud_btn29}}</div>
                    </div>
                </el-dialog>
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
                                <span class="day-row">当前配置剩余天数：<span class="day">{{hostData.due_time | formateDueDay}}</span> 天</span>
                                <span class="money">{{commonData.currency_prefix + cloudData.first_payment_amount + '/' + hostData.billing_cycle_name}}</span>
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
                                <div class="package-upgrade-main" v-show="changeUpgradeData.money">
                                    <span class="money">{{commonData.currency_prefix}}{{changeUpgradeData.money}}/{{changeUpgradeData.duration}}</span>
                                    <div class="package-line"></div>
                                    <div class="description" v-html="changeUpgradeData.description"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alert">
                        <el-alert :title="errText" type="error" show-icon :closable="false" v-show="errText">
                        </el-alert>
                    </div>
                    <div class="dialog-footer">
                        <div class="footer-top">
                          <div class="money-text">切换所需费用：</div>
                          <div class="money" v-loading="upgradePriceLoading">
                                <span class="money-num">{{commonData.currency_prefix }} {{ upParams.totalPrice | filterMoney}}</span>
                                <el-popover placement="top-start" width="200" trigger="hover" v-if="isShowLevel || (isShowPromo && upParams.isUseDiscountCode) || isShowCash">
                                    <div class="show-config-list">
                                        <p v-if="isShowLevel">{{lang.shoppingCar_tip_text2}}：{{commonData.currency_prefix}} {{ upParams.clDiscount | filterMoney }}</p>
                                        <p v-if="isShowPromo && upParams.isUseDiscountCode">{{lang.shoppingCar_tip_text4}}：{{commonData.currency_prefix}} {{ upParams.code_discount | filterMoney}}</p>
                                        <p v-if="isShowCash && upParams.customfield.voucher_get_id">代金券抵扣金额：{{commonData.currency_prefix}} {{ upParams.cash_discount | filterMoney}}</p>
                                    </div>
                                    <i class="el-icon-warning-outline total-icon" slot="reference"></i>
                                </el-popover>
                                <p class="original-price" v-if="upParams.totalPrice != upParams.original_price">{{commonData.currency_prefix}} {{ upParams.original_price | filterMoney}}</p>
                                <div class="code-box">
                                    <!-- 代金券 -->
                                    <cash-coupon ref="cashRef" v-show="isShowCash && !cashObj.code" :currency_prefix="commonData.currency_prefix" @use-cash="upUseCash" scene='upgrade' :product_id="[product_id]" :price="upParams.original_price"></cash-coupon>
                                      <!-- 优惠码 -->
                                    <discount-code v-show="isShowPromo && !upParams.customfield.promo_code  " @get-discount="getUpDiscount(arguments)" scene='upgrade' :product_id="product_id" :amount="upParams.original_price" :billing_cycle_time="hostData.billing_cycle_time"></discount-code>
                                </div>
                               <div class="code-number-text">
                                 <div class="discount-codeNumber" v-show="upParams.customfield.promo_code">{{ upParams.customfield.promo_code }}<i class="el-icon-circle-close remove-discountCode" @click="removeUpDiscountCode()"></i></div>
                                 <div class="cash-codeNumber" v-show="cashObj.code">{{ cashObj.code }}<i class="el-icon-circle-close remove-discountCode" @click="upRemoveCashCode()"></i></div>
                                </div>
                           </div>
                        </div>
                        <div class="footer-bottom">
                            <div class="btn-ok" @click="upgradeSub" v-loading="loading4">提交</div>
                            <div class="btn-no" @click="upgradeDgClose">取消</div>
                        </div>
                    </div>
                </el-dialog>
            </div>

            <!-- 订购磁盘弹窗 -->
            <div class="dg-dialog">
                <el-dialog width="9.12rem" :visible.sync="isShowDg" :show-close=false @close="dgClose">
                    <div class="dialog-title">
                        订购磁盘
                    </div>
                    <!-- 当前配置磁盘 -->
                    <div class="disk-card disk-old" v-if="oldDiskList2.length > 0">
                        <div class="disk-label">当前配置磁盘</div>
                        <div class="disk-main-card">
                            <div class="old-disk-item" v-for="item in oldDiskList2" :key="item.id">
                                <span class="disk-item-text">{{item.name}}</span>
                                <span class="disk-item-size">{{item.size + 'G'}}</span>
                                <i class="el-icon-remove-outline del" @click="delOldSize2(item.id)"></i>
                            </div>
                        </div>
                    </div>
                    <!-- 新增磁盘 -->
                    <div class=" disk-card disk-new">
                        <div class="disk-label">新增磁盘</div>
                        <div class="disk-main-card">
                            <div class="new-disk-item" v-for="item in moreDiskData" :key="item.id">
                                <span class="item-name">数据盘{{item.index}}</span>
                                <span class="item-min-size">{{configData.disk_min_size}}G</span>
                                <el-slider :step="10" :min="configData.disk_min_size" :max="configData.disk_max_size" v-model="item.size"></el-slider>
                                <span class="item-max-size">{{configData.disk_max_size}}G</span>
                                <i class="el-icon-remove-outline del" @click="delMoreDisk(item.id)"></i>
                                <i class="el-icon-circle-plus-outline add" v-show="item.index === moreDiskData.length" @click="addMoreDisk"></i>
                            </div>
                        </div>
                    </div>
                    <div class="dialog-footer">
                        <span class="text">订购所需费用:</span>
                        <span class="num" v-loading="diskPriceLoading">
                            {{commonData.currency_prefix}}{{moreDiskPrice}}
                            <el-popover placement="top-start" width="200" trigger="hover">
                                <div class="show-config-list">等级折扣金额：{{commonData.currency_prefix}}{{ moreDiscountkDisPrice }}</div>
                                <div class="show-config-list">优惠码折扣金额：{{commonData.currency_prefix}}{{ moreCodePrice }}</div>
                                <i class="el-icon-warning-outline total-icon" slot="reference" v-if="isShowLevel || isShowPromo"></i>
                            </el-popover>
                        </span>
                        <div class="btn-ok" @click="toCreateDisk">提交</div>
                        <div class="btn-no" @click="dgClose">取消</div>
                    </div>
                </el-dialog>
            </div>

            <!-- 扩容磁盘弹窗 -->
            <div class="kr-dialog">
                <el-dialog width="8.41rem" :visible.sync="isShowExpansion" :show-close=false @close="krClose">
                    <div class="dialog-title">
                        扩容磁盘
                    </div>
                    <div class="dialog-main">
                        <div class="label">当前配置磁盘容量</div>
                        <div class="disk-main-card">
                            <div class="old-disk-item" v-for="item in oldDiskList" :key="item.id">
                                <span class="disk-item-text" :title="item.name">{{item.name}}</span>
                                <span class="disk-item-size">{{item.size + 'G'}}</span>
                                <el-slider :step="10" :min="Number(item.minSize)" :max="configData.disk_max_size" v-model="item.size"></el-slider>
                                <span class="item-max-size">{{configData.disk_max_size}}G</span>
                            </div>
                        </div>
                    </div>
                    <div class="dialog-footer">
                        <span class="text">扩容所需费用:</span>
                        <span class="num" v-loading="diskPriceLoading">
                            {{commonData.currency_prefix}}{{expansionDiskPrice}}
                            <el-popover placement="top-start" width="200" trigger="hover">
                                <div class="show-config-list">等级折扣金额：{{commonData.currency_prefix}}{{ expansionDiscount }}</div>
                                <div class="show-config-list">优惠码折扣金额：{{commonData.currency_prefix}}{{ expansionCodePrice }}</div>
                                <i class="el-icon-warning-outline total-icon" slot="reference"  v-if="isShowLevel || isShowPromo"></i>
                            </el-popover>
                        </span>
                        <div class="btn-ok" @click="subExpansion">提交</div>
                        <div class="btn-no" @click="krClose">取消</div>
                    </div>
                </el-dialog>
            </div>
            <!-- 创建备份/快照弹窗 -->
            <div class="create-bs-dialog">
                <el-dialog width="6.8rem" :visible.sync="isShwoCreateBs" :show-close=false @close="bsCgClose">
                    <div class="dialog-title">
                        {{isBs?'创建备份':'创建快照'}}
                    </div>
                    <div class="title-second-text">
                        建议您关闭实例后{{isBs?'创建备份':'创建快照'}}，确保一致性避免出现问题
                    </div>
                    <div class="dialog-main">
                        <div class="label">磁盘</div>
                        <el-select v-model="createBsData.disk_id">
                            <el-option v-for="item in allDiskList" :key="item.id" :value="item.id" :label="item.name"></el-option>
                        </el-select>
                        <div class="label">备注</div>
                        <el-input v-model="createBsData.name"></el-input>
                        <div class="alert">
                            <el-alert :title="errText" type="error" show-icon :closable="false" v-show="errText">
                            </el-alert>
                        </div>
                    </div>
                    <div class="dialog-footer">
                        <div class="btn-ok" @click="subCgBs" v-loading="cgbsLoading">立即创建</div>
                        <div class="btn-no" @click="bsCgClose">取消</div>
                    </div>
                </el-dialog>
            </div>
            <!-- 还原备份、快照弹窗 -->
            <div class="restore-dialog">
                <el-dialog width="6.8rem" :visible.sync="isShowhyBs" :show-close=false @close="bshyClose">
                    <div class="dialog-title">
                        恢复
                    </div>
                    <div class="dialog-main">
                        <span>您将恢复实例{{restoreData.cloud_name}}的磁盘</span>
                        <span>{{restoreData.time | formateTime}}创建的{{isBs?'备份':'快照'}}</span>
                        <span>当前的所有数据丢失，请确认您需要恢复{{isBs?'备份':'快照'}}</span>
                    </div>
                    <div class="dialog-footer">
                        <div class="btn-ok" @click="subhyBs" v-loading="loading3">立即恢复</div>
                        <div class="btn-no" @click="bshyClose">取消</div>
                    </div>
                </el-dialog>
            </div>
            <!-- 删除备份、快照弹窗 -->
            <div class="del-dialog">
                <el-dialog width="6.8rem" :visible.sync="isShowDelBs" :show-close=false @close="delBsClose">
                    <div class="dialog-title">
                        <i class="el-icon-warning-outline"></i>
                        {{isBs?'删除备份':'删除快照'}}
                    </div>
                    <div class="dialog-main">
                        <span class="row-1">{{isBs?'删除备份：':'删除快照：'}}{{delData.name}}</span>
                        <span class="row-2">您将删除实例{{delData.cloud_name}}的磁盘{{delData.time | formateTime}}创建的{{isBs?'备份':'快照'}}，删除后不可恢复</span>
                    </div>
                    <div class="dialog-footer">
                        <div class="btn-ok" @click="subDelBs" v-loading="loading4">确认删除</div>
                        <div class="btn-no" @click="delBsClose">取消</div>
                    </div>
                </el-dialog>
            </div>

            <!-- 开启备份/快照弹窗 -->
            <div class="bs-dialog">
                <el-dialog width="6.2rem" :visible.sync="isShowOpenBs" :show-close=false @close="bsopenDgClose">
                    <div class="dialog-title">
                        {{isBs?'开启备份':'开启快照'}}
                    </div>
                    <div class="dialog-main">
                        <!-- 备份下拉框 -->
                        <el-select v-model="bsData.backNum" v-show="isBs" @change="bsSelectChange">
                            <el-option v-for="item in configData.backup_option" :key="item.id" :value="item.num" :label="item.num + '个备份' + commonData.currency_prefix + item.price + '/' + bsData.duration"></el-option>
                        </el-select>
                        <!-- 快照下拉框 -->
                        <el-select v-model="bsData.snapNum" v-show="!isBs" @change="bsSelectChange">
                            <el-option v-for="item in configData.snap_option" :key="item.id" :value="item.num" :label="item.num + '个快照' + commonData.currency_prefix + item.price  + '/' + bsData.duration"></el-option>
                        </el-select>
                        <span class="num">开启创建{{isBs?bsData.backNum:bsData.snapNum}}个{{isBs?'备份':'快照'}}</span>
                        <span class="price" v-loading="bsDataLoading">
                            {{commonData.currency_prefix + bsData.money}}
                            <el-popover placement="top-start" width="200" trigger="hover">
                                <div class="show-config-list">等级折扣金额：{{commonData.currency_prefix}}{{ bsData.moneyDiscount }}</div>
                                <div class="show-config-list">优惠码折扣金额：{{commonData.currency_prefix}}{{ bsData.codePrice }}</div>
                                <i class="el-icon-warning-outline total-icon" slot="reference" v-if="isShowLevel || isShowPromo"></i>
                            </el-popover>

                        </span>
                    </div>
                    <div class="dialog-footer">
                        <div class="btn-ok" @click="bsopenSub">提交</div>
                        <div class="btn-no" @click="bsopenDgClose">取消</div>
                    </div>
                </el-dialog>
            </div>

            <!-- 电源操作确认弹窗 -->
            <div class="power-dialog">
                <el-dialog width="6.2rem" :visible.sync="isShowPowerChange" :show-close=false @close="powerDgClose">
                    <div class="dialog-title">
                        请确认您将{{powerTitle}}以下实例
                    </div>
                    <div class="dialog-main">
                        <div class="label">主机名</div>
                        <div class="value">{{hostData.name}}</div>
                    </div>
                    <div class="dialog-footer">
                        <div class="btn-ok" @click="toChangePower()">提交</div>
                        <div class="btn-no" @click="powerDgClose">取消</div>
                    </div>
                </el-dialog>
            </div>

            <!-- 自动续费确认弹窗 -->
            <div class="power-dialog">
                <el-dialog width="6.2rem" :visible.sync="isShowAutoRenew" :show-close=false :close-on-click-modal=false>
                    <div class="dialog-title">
                        请确认您将为以下实例{{isShowPayMsg?'开启':'关闭'}}自动续费
                    </div>
                    <div class="dialog-main">
                        <div class="label">主机名</div>
                        <div class="value">{{hostData.name}}</div>
                    </div>
                    <div class="dialog-footer">
                        <div class="btn-ok" @click="doAutoRenew">提交</div>
                        <div class="btn-no" @click="autoRenewDgClose">取消</div>
                    </div>
                </el-dialog>
            </div>

            <pay-dialog ref="topPayDialog" @payok="paySuccess" @paycancel="payCancel"></pay-dialog>
        </div>
    </div>
</div>
<!-- =======页面独有======= -->
<script src="/plugins/server/common_cloud/template/clientarea/api/common.js"></script>
<script src="/plugins/server/common_cloud/template/clientarea/api/cloud.js"></script>
<script src="/plugins/server/common_cloud/template/clientarea/utils/util.js"></script>
<script src="https://cdn.bootcdn.net/ajax/libs/echarts/5.3.3/echarts.min.js"></script>
<script src="/plugins/server/common_cloud/template/clientarea/js/cloudDetail.js"></script>