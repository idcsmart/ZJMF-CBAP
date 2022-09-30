{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/ticket.css">
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
    <div class="template ticket">
        <el-container>
            <aside-menu></aside-menu>
            <el-container>
                <top-menu></top-menu>
                <el-main>
                    <!-- 自己的东西 -->
                    <div class="main-card">
                        <div class="main-card-title">{{lang.ticket_title}}</div>
                        <div class="main-card-top">
                            <div class="top-item" v-for="item in topData" :key="item.id" :style="item.style">
                                <div class="top-item-text">
                                    {{item.text}}
                                </div>
                                <div class="top-item-num">
                                    {{item.num}}
                                </div>
                            </div>
                        </div>

                        <div class="content_searchbar">
                            <div class="new-ticket-btn" @click="showCreateDialog">新建工单</div>
                            <div class="searchbar com-search">
                                <el-input v-model="params.keywords" style="width: 3.2rem;margin-left: .2rem;" :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange" clearable @clear="getTicketList">
                                    <i class="el-icon-search input-search" slot="suffix" @Click="inputChange"></i>
                                </el-input>
                            </div>
                        </div>

                        <div class="tabledata">
                            <el-table v-loading="tableLoading" :data="dataList" style="width: 100%;margin-bottom: .2rem;">
                                <el-table-column prop="name" label="工单类型" width="150" align="left">
                                </el-table-column>
                                <el-table-column prop="title" min-width="400" label="工单标题" align="left" :show-overflow-tooltip="true">
                                    <template slot-scope="scope">
                                        <span>{{"#" + scope.row.ticket_num + " " + scope.row.title }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="post_time" label="提交时间" width="200" align="left">
                                    <template slot-scope="scope">
                                        <span>{{scope.row.post_time | formateTime}}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="status" label="状态" width="150" align="left">
                                    <template slot-scope="scope">
                                        <div :class="scope.row.status">{{stats[scope.row.status].name}}</div>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="id" width="100" label="操作" align="left">
                                    <template slot-scope="scope">
                                        <el-popover placement="top-start" trigger="hover">
                                            <div class="operation">
                                                <div class="operation-item" @click="itemReply(scope.row)">回复</div>
                                                <div v-if="scope.row.status != 'Closed' && scope.row.status != 'Resolved'" class="operation-item" @click="itemUrge(scope.row)">催单</div>
                                                <div v-if="scope.row.status != 'Closed' && scope.row.status != 'Resolved'" class="operation-item" @click="itemClose(scope.row)">关闭</div>
                                            </div>
                                            <div class="more-operation" slot="reference">...</div>
                                        </el-popover>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange"></pagination>
                        </div>

                        <!-- 新建工单弹窗 -->
                        <div class="create-ticket">
                            <el-dialog width="6.8rem" :visible.sync="isShowDialog" :show-close=false :close-on-click-modal=false>
                                <div class="dialog-title">
                                    新建工单
                                </div>
                                <div class="mian-form">
                                    <el-form :model="formData" label-position="top">
                                        <el-form-item label="工单类型">
                                            <el-select v-model="formData.ticket_type_id" placeholder="请选择" style="width: 100%;">
                                                <el-option v-for="item in ticketType" :key="item.id" :value="item.id" :label="item.name"></el-option>
                                            </el-select>
                                        </el-form-item>
                                        <el-form-item label="工单标题">
                                            <div class="ticket-row-title">
                                                <el-input v-model="formData.title" placeholder="请输入" maxlength="150"></el-input>
                                                <span>不超过150字</span>
                                            </div>
                                        </el-form-item>
                                        <el-form-item label="关联产品">
                                            <el-select v-model="formData.host_ids" multiple collapse-tags placeholder="请选择" style="width: 100%;">
                                                <el-option v-for="item in hostList" :key="item.id" :value="item.id" :label="item.product_name + '('+item.name+')'"></el-option>
                                            </el-select>
                                        </el-form-item>
                                        <el-form-item label="问题描述">
                                            <div class="ticket-row-title">
                                                <el-input v-model="formData.content" placeholder="请输入" type="textarea" :autosize="{ minRows: 4}" maxlength="3000"></el-input>
                                                <span>不超过3000字</span>
                                            </div>
                                        </el-form-item>
                                        <el-form-item>
                                            <!-- <div class="upload-btn">
                                                <i class="el-icon-upload2"></i>
                                                <span class="btn-text">上传文件</span>
                                            </div> -->

                                            <el-upload class="upload-btn" action="/console/v1/upload" :before-remove="beforeRemove" multiple :file-list="fileList" :on-success="handleSuccess" ref="fileupload">
                                                <el-button icon="el-icon-upload2">上传文件</el-button>
                                            </el-upload>

                                        </el-form-item>
                                        <el-form-item v-show="errText">
                                            <el-alert show-icon :title="errText" type="error" :closable="false"></el-alert>
                                        </el-form-item>
                                    </el-form>
                                </div>
                                <div class="dialog-footer">
                                    <el-button class="btn-ok" @click="doCreateTicket" v-loading="createBtnLoading">提交</el-button>
                                    <el-button class="btn-no" @click="closeDialog">取消</el-button>
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
    <script src="/{$template_catalog}/template/{$themes}/api/ticket.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/ticket.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}