{include file="header"}
<!-- 统计图表 -->
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/cloudTop.css">
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/cloudBackupSnapshot.css">
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
                        <cloud-top active-name="5" ref="cloudTop" @getclouddetail="getCloudDetail"></cloud-top>
                        <!-- 备份 -->
                        <!-- 启用备份 -->
                        <div class="main-content" v-if="cloudDetail.backup_num > 0">
                            <div class="content-top">
                                <div class="top-title">备份</div>
                                <div class="top-btn" @click="showCreateBs('back')">立即备份</div>
                            </div>
                            <div class="main-table">
                                <el-table v-loading="loading1" :data="dataList1" style="width: 100%;margin-bottom: .2rem;">
                                    <el-table-column prop="name" label="备份名称" width="300" align="left">
                                    </el-table-column>
                                    <el-table-column prop="create_time" width="300" label="生成时间" align="left">
                                        <template slot-scope="scope">
                                            {{scope.row.create_time | formateTime}}
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="notes" label="备注" min-width="400" align="left" :show-overflow-tooltip="true">
                                    </el-table-column>
                                    <el-table-column prop="type" label="操作" width="150" align="left">
                                        <template slot-scope="scope">
                                            <el-popover placement="top-start" trigger="hover">
                                                <div class="operation">
                                                    <div class="operation-item" @click="showhyBs('back',scope.row)">还原</div>
                                                    <div class="operation-item" @click="showDelBs('back',scope.row)">删除</div>
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
                            <span class="no-bs-title">备份</span>
                            <div class="no-bs-content">
                                <span class="text">您尚未开启备份功能</span>
                                <div class="btn" @click="openBs('back')">立刻开启</div>
                            </div>
                        </div>
                        <!-- 快照 -->
                        <div class="main-content" v-if="cloudDetail.snap_num > 0">
                            <div class="content-top">
                                <div class="top-title">快照</div>
                                <div class="top-btn" @click="showCreateBs('snap')">生成快照</div>
                            </div>
                            <div class="main-table">
                                <el-table v-loading="loading2" :data="dataList2" style="width: 100%;margin-bottom: .2rem;">
                                    <el-table-column prop="name" label="快照名称" width="300" align="left">
                                    </el-table-column>
                                    <el-table-column prop="create_time" width="300" label="生成时间" align="left">
                                        <template slot-scope="scope">
                                            {{scope.row.create_time | formateTime}}
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="notes" label="备注" min-width="400" align="left" :show-overflow-tooltip="true">
                                    </el-table-column>
                                    <el-table-column prop="type" label="操作" width="150" align="left">
                                        <template slot-scope="scope">
                                            <el-popover placement="top-start" trigger="hover">
                                                <div class="operation">
                                                    <div class="operation-item" @click="showhyBs('snap',scope.row)">还原</div>
                                                    <div class="operation-item" @click="showDelBs('snap',scope.row)">删除</div>
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
                            <span class="no-bs-title">快照</span>
                            <div class="no-bs-content">
                                <span class="text">您尚未开启快照功能</span>
                                <div class="btn" @click="openBs('snap')">立刻开启</div>
                            </div>
                        </div>
                        <!-- 开启备份/快照弹窗 -->
                        <div class="bs-dialog">
                            <el-dialog width="6.2rem" :visible.sync="isShowOpenBs" :show-close=false @close="bsopenDgClose">
                                <div class="dialog-title">
                                    {{isBs?'开启备份':'开启快照'}}
                                </div>
                                <div class="dialog-main">
                                    <!-- 备份下拉框 -->
                                    <el-select v-model="bsData.backNum" v-show="isBs">
                                        <el-option v-for="item in configData.backup_option" :key="item.id" :value="item.num" :label="item.num + '个备份' + commonData.currency_prefix + item.price"></el-option>
                                    </el-select>
                                    <!-- 快照下拉框 -->
                                    <el-select v-model="bsData.snapNum" v-show="!isBs">
                                        <el-option v-for="item in configData.snap_option" :key="item.id" :value="item.num" :label="item.num + '个快照' + commonData.currency_prefix + item.price"></el-option>
                                    </el-select>
                                    <span class="num">开启创建{{isBs?bsData.backNum:bsData.snapNum}}个{{isBs?'备份':'快照'}}</span>
                                    <span class="price">{{commonData.currency_prefix + bsData.money + '/' + bsData.duration}}</span>
                                </div>
                                <div class="dialog-footer">
                                    <div class="btn-ok" @click="bsopenSub">提交</div>
                                    <div class="btn-no" @click="bsopenDgClose">取消</div>
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
                                        <el-option v-for="item in diskList" :key="item.id" :value="item.id" :label="item.name"></el-option>
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
                        <!-- 支付弹窗 -->
                        <pay-dialog ref="payDialog" @payok="paySuccess" @paycancel="payCancel"></pay-dialog>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/cloud.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/cloudBackupSnapshot.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/cloudTop/cloudTop.js"></script>

    {include file="footer"}