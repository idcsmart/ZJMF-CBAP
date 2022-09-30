{include file="header"}
<!-- 统计图表 -->
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/cloudTop.css">
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/cloudDisk.css">
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
                        <cloud-top active-name="3"></cloud-top>
                        <div class="no-disk" v-if="diskList.length == 0">
                            <span class="text">没有磁盘</span>
                            <span class="text2">您尚未订购任何数据盘，立刻购买磁盘</span>
                        </div>
                        <div class="yes-disk" v-else>
                            <div class="main-table">
                                <el-table v-loading="loading" :data="diskList" style="width: 100%;margin-bottom: .2rem;">
                                    <el-table-column prop="name" label="磁盘名称" width="300" align="left">
                                    </el-table-column>
                                    <el-table-column prop="create_time" width="300" label="操作时间" align="left">
                                    </el-table-column>
                                    <el-table-column prop="size" label="容量" min-width="400" align="left" :show-overflow-tooltip="true">
                                        <template slot-scope="scope">
                                            <span>{{scope.row.size + 'G'}}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="type" label="操作" width="150" align="left">
                                        <template slot-scope="scope">
                                            <el-popover placement="top-start" trigger="hover">
                                                <div class="operation">
                                                    <div class="operation-item" @click="showExpansion(scope.row)">扩容</div>
                                                    <div class="operation-item">指南</div>
                                                    <!-- <div class="operation-item">删除</div> -->
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
                        <span class="buy-btn" @click="showDg">订购磁盘</span>

                        <!-- 订购磁盘弹窗 -->
                        <div class="dg-dialog">
                            <el-dialog width="9.12rem" :visible.sync="isShowDg" :show-close=false @close="dgClose">
                                <div class="dialog-title">
                                    订购磁盘
                                </div>
                                <!-- 当前配置磁盘 -->
                                <div class="disk-card disk-old" v-if="oldDiskList.length > 0">
                                    <div class="disk-label">当前配置磁盘</div>
                                    <div class="disk-main-card">
                                        <div class="old-disk-item" v-for="item in oldDiskList" :key="item.id">
                                            <span class="disk-item-text">{{item.name}}</span>
                                            <span class="disk-item-size">{{item.size + 'G'}}</span>
                                            <i class="el-icon-remove-outline del" @click="delOldSize(item.id)"></i>
                                        </div>
                                    </div>
                                </div>
                                <!-- 新增磁盘 -->
                                <div class=" disk-card disk-new">
                                    <div class="disk-label">新增磁盘</div>
                                    <div class="disk-main-card">
                                        <div class="new-disk-item" v-for="item in moreDiskData" :key="item.id">
                                            <span class="item-name">数据盘{{item.index}}</span>
                                            <span class="item-min-size">{{configData.disk_min_size}}</span>
                                            <el-slider :step="10" :min="configData.disk_min_size" :max="configData.disk_max_size" v-model="item.size"></el-slider>
                                            <span class="item-max-size">{{configData.disk_max_size}}</span>
                                            <i class="el-icon-remove-outline del" @click="delMoreDisk(item.id)"></i>
                                            <i class="el-icon-circle-plus-outline add" @click="addMoreDisk"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="dialog-footer">
                                    <span class="text">订购所需费用:</span>
                                    <span class="num">{{commonData.currency_prefix}}{{moreDiskPrice}}</span>
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
                                            <span class="item-max-size">{{configData.disk_max_size}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="dialog-footer">
                                    <span class="text">扩容所需费用:</span>
                                    <span class="num">{{commonData.currency_prefix}}{{expansionDiskPrice}}</span>
                                    <div class="btn-ok" @click="subExpansion">提交</div>
                                    <div class="btn-no" @click="krClose">取消</div>
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
    <script src="/{$template_catalog}/template/{$themes}/js/cloudDisk.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/cloudTop/cloudTop.js"></script>

    {include file="footer"}