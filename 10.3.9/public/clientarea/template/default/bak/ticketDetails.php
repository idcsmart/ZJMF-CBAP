{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/ticketDetails.css">
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
    <div class="template ticket-details">
        <el-container>
            <aside-menu></aside-menu>
            <el-container>
                <top-menu></top-menu>
                <el-main>
                    <!-- 自己的东西 -->
                    <div class="main-card">
                        <div class="top">
                            <div class="top-l"><img src="/{$template_catalog}/template/{$themes}/img/finance/back.png" class="top-img" @click="backTicket">工单详情</div>
                        </div>
                        <div class="top-line"></div>

                        <div class="card base-card">
                            <div class="card-title">基本信息</div>
                            <div class="card-main">
                                <el-row>
                                    <el-col :span="24">
                                        <div class="card-main-item">
                                            <div class="main-item-label">标题</div>
                                            <div class="main-item-text">
                                                {{baseMsg.title}}
                                            </div>
                                        </div>
                                    </el-col>
                                </el-row>
                                <el-row>
                                    <el-col :span="8">
                                        <div class="card-main-item">
                                            <div class="main-item-label">工单类型</div>
                                            <div class="main-item-text">
                                                {{baseMsg.type}}
                                            </div>
                                        </div>
                                    </el-col>
                                    <el-col :span="8">
                                        <div class="card-main-item">
                                            <div class="main-item-label">关联产品</div>
                                            <div class="main-item-text">
                                                {{baseMsg.hosts}}
                                            </div>
                                        </div>
                                    </el-col>
                                    <el-col :span="8">
                                        <div class="card-main-item">
                                            <div class="main-item-label">当前状态</div>
                                            <div class="main-item-text">
                                                {{baseMsg.status}}
                                            </div>
                                        </div>
                                    </el-col>
                                </el-row>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-title">沟通记录</div>
                            <div class="card-main talk-main">
                                <div class="main-old-msg infinite-list-wrapper">
                                    <div class="reply-item " v-for="item in ticketData.replies" :key="item.create_time" :class="item.type">
                                        <div class="reply-item-top">
                                            <div class="reply-time">
                                                {{item.create_time | formateTime}}
                                            </div>
                                            <div class="reply-name">
                                                {{item.type == 'Client'? item.client_name : item.admin_name}}
                                            </div>
                                            <div class="reply-img">
                                                <img :src="item.type == 'Client'? '/{$template_catalog}/template/{$themes}/img/ticket/client.png':'/{$template_catalog}/template/{$themes}/img/ticket/admin.png'">
                                            </div>
                                        </div>
                                        <div class="reply-item-content">
                                            {{item.content}}
                                        </div>
                                        <div class="reply-item-attachment">
                                            <div class="reply-item-attachment-item" v-for="(f,i) in item.attachment" :key="i" @click="downloadfile(f)">
                                                <span :title="f.split('^')[1]">
                                                    <i class="el-icon-tickets"></i><span>{{f.split('^')[1]}}</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="main-now-msg">
                                    <el-input class="msg-input" type="textarea" :autosize="{ minRows: 5, maxRows: 5}" resize=none maxlength="3000" placeholder="请输入内容" v-model="replyData.content">
                                    </el-input>
                                </div>
                            </div>
                            <div class="card-footer">
                                <el-upload ref="fileupload" class="upload-btn" action="/console/v1/upload" :before-remove="beforeRemove" multiple :file-list="fileList" :on-success="handleSuccess">
                                    <el-button icon="el-icon-upload2">上传文件</el-button>
                                </el-upload>


                                <div class="send-btn" @click="doReplyTicket" v-loading="sendBtnLoading">
                                    发送
                                </div>
                            </div>
                        </div>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/ticket.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/ticketDetails.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}