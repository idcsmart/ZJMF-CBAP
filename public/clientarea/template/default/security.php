{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/security.css">
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
                        <div class="main-card-title">{{lang.security_title}}</div>
                        <el-tabs v-model="activeName" @tab-click="handleClick">
                            <el-tab-pane label="API" name="1">
                                <div class="content-table">
                                    <div class="content_searchbar">
                                        <div class="left-btn" @click="showCreateApi">
                                            {{lang.security_btn1}}
                                        </div>
                                        <div class="searchbar com-search">

                                        </div>
                                    </div>
                                    <div class="tabledata">
                                        <el-table v-loading="loading" :data="dataList" style="width: 100%;">
                                            <el-table-column prop="name" :label="lang.security_label1" width="400" :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="id" label="ID" width="100" align="left">
                                            </el-table-column>
                                            <el-table-column prop="create_time" :label="lang.account_label10" min-width="200" align="left">
                                                <template slot-scope="scope">
                                                    <span>{{scope.row.create_time | formateTime}}</span>
                                                </template>
                                            </el-table-column>
                                            <el-table-column prop="ip" :label="lang.security_label2" width="150" align="left">
                                                <template slot-scope="scope">
                                                    <!-- 已开启白名单 -->
                                                    <div class="open-show">
                                                        <!-- 未开启白名单 -->
                                                        <div class="un-open" v-if="scope.row.status == 0">未开启</div>
                                                        <div class="open" v-else>已开启</div>
                                                        <span class="setting" @click="showWhiteIp(scope.row)">设置</span>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                            <el-table-column prop="type" label="操作" width="100" align="left">
                                                <template slot-scope="scope">
                                                    <el-popover placement="top-start" trigger="hover">
                                                        <div class="operation">
                                                            <div class="operation-item" @click="deleteItem(scope.row)">删除</div>
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
                            </el-tab-pane>
                            {foreach $addons as $addon}
                            {if ($addon.name=='IdcsmartSshKey')}
                            <el-tab-pane :label="lang.security_tab1" name="2"></el-tab-pane>
                            {/if}
                            {/foreach}
                            <el-tab-pane :label="lang.security_tab2" name="3"></el-tab-pane>
                        </el-tabs>

                        <!-- 创建API弹窗 -->
                        <div class="create-api-dialog">
                            <el-dialog width="6.8rem" :visible.sync="isShowCj" :show-close=false @close="cjClose">
                                <div class="dialog-title">
                                    {{lang.security_btn1}}
                                </div>
                                <div class="dialog-main">
                                    <div class="label">名称</div>
                                    <el-input v-model="apiName"></el-input>
                                    <el-alert class="alert-text" :title="errText" v-show="errText" type="error" show-icon :closable="false">
                                    </el-alert>
                                </div>
                                <div class="dialog-footer">
                                    <div class="btn-ok" @click="cjSub">提交</div>
                                    <div class="btn-no" @click="cjClose">取消</div>
                                </div>
                            </el-dialog>
                        </div>

                        <!-- 创建API成功弹窗 -->
                        <div class="create-api-dialog">
                            <el-dialog width="6.8rem" :visible.sync="isShowCj2" :show-close=false @close="cj2Close">
                                <div class="dialog-title">
                                    创建API
                                </div>
                                <div class="dialog-main">
                                    <div class="content-msg">
                                        <div class="msg-item">
                                            <div class="item-label">名称:</div>
                                            <div class="item-vlaue">{{apiData.name}}</div>
                                        </div>
                                        <div class="msg-item">
                                            <div class="item-label">ID:</div>
                                            <div class="item-vlaue">{{apiData.id}}</div>
                                        </div>
                                        <div class="msg-item">
                                            <div class="item-label">Token:</div>
                                            <div class="item-vlaue">{{apiData.token}} <span class="copy" @click="copyToken(apiData.token)">复制</span></div>
                                        </div>
                                        <div class="msg-item">
                                            <div class="item-label">创建时间:</div>
                                            <div class="item-vlaue">{{apiData.create_time | formateTime}}</div>
                                        </div>
                                    </div>
                                    <el-checkbox v-model="checked">
                                        <span>
                                            为了保证数据安全，DNSPod不会存储你的原始密钥，
                                            <span class="yellow">以上信息仅在创建时候显示一次，请务必妥善保存。</span>
                                        </span>

                                    </el-checkbox>
                                    <el-alert class="alert-text" :title="errText" v-show="errText" type="error" show-icon :closable="false">
                                    </el-alert>
                                </div>
                                <div class="dialog-footer">
                                    <div class="btn-ok" @click="cj2Sub">我已保存</div>
                                </div>
                            </el-dialog>
                        </div>

                        <!-- 删除弹窗 -->
                        <div class="delete-dialog">
                            <el-dialog width="6.8rem" :visible.sync="isShowDel" :show-close=false @close="delClose">
                                <div class="del-dialog-title">
                                    <i class="el-icon-warning-outline del-icon"></i>删除API？
                                </div>
                                <div class="del-dialog-main">
                                    删除API:{{delName}}
                                </div>
                                <div class="del-dialog-footer">
                                    <div class="btn-ok" @click="delSub">确认删除</div>
                                    <div class="btn-no" @click="delClose">取消</div>
                                </div>
                            </el-dialog>
                        </div>

                        <!-- ip白名单设置弹窗 -->
                        <div class="white-ip-dialog">
                            <el-dialog width="6.8rem" :visible.sync="isShowWhiteIp" :show-close=false @close="whiteIpClose" :destroy-on-close=true>
                                <div class="dialog-title">
                                    IP白名单设置
                                </div>
                                <div class="dialog-main">
                                    <el-alert class="info-alert" title="IP白名单功能可以指定IP地址进行API调用，以保证密钥安全" type="info">
                                    </el-alert>
                                    <div class="ip-status">
                                        <div class="ip-status-text">开启状态</div>
                                        <el-switch v-model="whiteIpData.status" active-color="#0058FF" inactive-color="#8692B0" active-value="1" inactive-value="0" active-text="开" inactive-text="关">
                                        </el-switch>
                                    </div>
                                    <div class="status-remind">
                                        开启后可指定IP地址进行API调试
                                    </div>
                                    <div v-show="whiteIpData.status == '1'">
                                        <div class="label">允许访问的IP</div>
                                        <el-input type="textarea" :rows="3" placeholder="请输入IP地址,每行一段，如：&#10;1.1.1.1&#10;1.1.1.1-2.2.2.2" v-model="whiteIpData.ip">
                                        </el-input>
                                    </div>

                                    <el-alert class="alert-text" :title="errText" v-show="errText" type="error" show-icon :closable="false">
                                    </el-alert>
                                </div>
                                <div class="dialog-footer">
                                    <div class="btn-ok" @click="whiteIpSub">提交</div>
                                    <div class="btn-no" @click="whiteIpClose">取消</div>
                                </div>
                            </el-dialog>
                        </div>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/security.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/security.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}