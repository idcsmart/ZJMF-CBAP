{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/security_ssh.css">
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
            <aside-menu @getruleslist="getRule"></aside-menu>
            <el-container>
                <top-menu></top-menu>
                <el-main>
                    <!-- 自己的东西 -->
                    <div class="main-card">
                        <div class="main-card-title">{{lang.security_title}}</div>
                        <el-tabs v-model="activeName" @tab-click="handleClick">
                            <el-tab-pane label="API" name="1" v-if="isShowAPI"></el-tab-pane>
                            {foreach $addons as $addon}
                            {if ($addon.name=='IdcsmartSshKey')}
                            <el-tab-pane :label="lang.security_tab1" name="2">
                                <div class="content-table">
                                    <div class="top-text">
                                        密钥将用于创建实例时使用，您可以使用您的私钥登陆云服务器
                                        <span class="top-link" @click="toLearn">查看指南</span>
                                    </div>
                                    <div class="content_searchbar">
                                        <div class="left-btn" @click="showCreateSsh">
                                            {{lang.security_btn2}}
                                        </div>
                                        <div class="searchbar com-search">

                                        </div>
                                    </div>
                                    <div class="tabledata">
                                        <el-table v-loading="loading" :data="dataList" style="width: 100%;margin-bottom: .2rem;">
                                            <el-table-column prop="name" label="名称" width="300" :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="finger_print" label="指纹" min-width="200" :show-overflow-tooltip="true" align="left">
                                            </el-table-column>
                                            <el-table-column prop="type" label="操作" width="100" align="left">
                                                <template slot-scope="scope">
                                                    <el-popover placement="top-start" trigger="hover">
                                                        <div class="operation">
                                                            <div class="operation-item" @click="editItem(scope.row)">编辑</div>
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
                                        <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange"></pagination>
                                    </div>
                                </div>
                            </el-tab-pane>
                            {/if}
                            {/foreach}
                            <el-tab-pane :label="lang.security_tab2" name="3" v-if="isShowAPILog"></el-tab-pane>
                            {foreach $addons as $addon}
                            {if ($addon.name=='IdcsmartCloud')}
                            <el-tab-pane :label="lang.security_group" name="4"></el-tab-pane>
                            {/if}
                            {/foreach}
                          </el-tabs>

                        <!-- 删除弹窗 -->
                        <div class="delete-dialog">
                            <el-dialog width="6.8rem" :visible.sync="isShowDel" :show-close=false @close="delClose">
                                <div class="del-dialog-title">
                                    <i class="el-icon-warning-outline del-icon"></i>{{lang.security_title5}}
                                </div>
                                <div class="del-dialog-main">
                                    {{lang.security_title5}}:{{delName}}
                                </div>
                                <div class="del-dialog-footer">
                                    <div class="btn-ok" @click="delSub">{{lang.security_btn9}}</div>
                                    <div class="btn-no" @click="delClose">{{lang.security_btn6}}</div>
                                </div>
                            </el-dialog>
                        </div>
                        <!-- 创建SSH弹窗 -->
                        <div class="cj-dialog">
                            <el-dialog width="6.8rem" :visible.sync="isShowCj" :show-close=false @close="cjClose">
                                <div class="dialog-title">
                                    {{lang.security_btn10}}
                                </div>
                                <div class="dialog-main">
                                    <div class="label">{{lang.security_label1}}</div>
                                    <el-input v-model="cjData.name"></el-input>
                                    <div class="label">{{lang.security_label7}}</div>
                                    <el-input type="textarea" v-model="cjData.public_key" :rows="3"></el-input>

                                    <el-alert class="alert-text" :title="errText" v-show="errText" type="error" show-icon :closable="false">
                                    </el-alert>
                                </div>
                                <div class="dialog-footer">
                                    <div class="btn-ok" @click="cjSub">{{lang.security_btn5}}</div>
                                    <div class="btn-no" @click="cjClose">{{lang.security_btn6}}</div>
                                </div>
                            </el-dialog>
                        </div>

                        <!-- 编辑SSh弹窗 -->
                        <div class="edit-dialog">
                            <el-dialog width="6.8rem" :visible.sync="isShowEdit" :show-close=false @close="editClose">
                                <div class="dialog-title">
                                    {{lang.security_title6}}
                                </div>
                                <div class="dialog-main">
                                    <div class="label">{{lang.security_label1}}</div>
                                    <el-input v-model="editData.name"></el-input>
                                    <div class="label">{{lang.security_label7}}</div>
                                    <el-input type="textarea" v-model="editData.public_key" :rows="3"></el-input>

                                    <el-alert class="alert-text" :title="errText" v-show="errText" type="error" show-icon :closable="false">
                                    </el-alert>
                                </div>
                                <div class="dialog-footer">
                                    <div class="btn-ok" @click="editSub">{{lang.security_btn5}}</div>
                                    <div class="btn-no" @click="editClose">{{lang.security_btn6}}</div>
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
    <script src="/{$template_catalog}/template/{$themes}/js/security_ssh.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}