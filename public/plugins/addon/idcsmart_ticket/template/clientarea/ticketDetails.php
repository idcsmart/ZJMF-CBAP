<!-- 页面独有样式 -->
<link rel="stylesheet" href="/plugins/addon/idcsmart_ticket/template/clientarea/css/ticketDetails.css">
<link rel="stylesheet" href="/plugins/addon/idcsmart_ticket/template/clientarea/css/common/viewer.min.css">
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
                            <div class="top-l"><img src="/plugins/addon/idcsmart_ticket/template/clientarea/img/finance/back.png" class="top-img" @click="backTicket">{{lang.ticket_title3}}</div>
                        </div>
                        <div class="top-line"></div>

                        <div class="card base-card">
                            <div class="card-title">{{baseMsg.title}}</div>
                            <div class="card-main">
                                <el-row>
                                    <el-col :span="12">
                                        <div class="card-main-item">
                                            <!-- <div class="main-item-label">{{lang.ticket_label9}}</div>
                                            <div class="main-item-text">
                                                {{baseMsg.title}}
                                            </div> -->
                                        </div>
                                    </el-col>
                                    <el-col :span="12">
                                        <div class="close-btn" @click="showClose" v-if="baseMsg.status != lang.ticket_text5">
                                            {{lang.ticket_btn7}}
                                        </div>
                                    </el-col>
                                </el-row>
                                <el-row>
                                    <el-col :span="6">
                                        <div class="card-main-item">
                                            <div class="main-item-label">{{lang.ticket_label10}}</div>
                                            <div class="main-item-text">
                                                {{baseMsg.create_time | formateTime}}
                                            </div>
                                        </div>
                                    </el-col>
                                    <el-col :span="6">
                                        <div class="card-main-item">
                                            <div class="main-item-label">{{lang.ticket_label2}}</div>
                                            <div class="main-item-text">
                                                {{baseMsg.type}}
                                            </div>
                                        </div>
                                    </el-col>
                                    <el-col :span="6">
                                        <div class="card-main-item">
                                            <div class="main-item-label">{{lang.ticket_label11}}</div>
                                            <div class="main-item-text">
                                                {{baseMsg.status}}
                                            </div>
                                        </div>
                                    </el-col>
                                    <el-col :span="6">
                                        <div class="card-main-item">
                                            <div class="main-item-label">{{lang.ticket_label7}}</div>
                                            <div class="main-item-text host-item-text" v-if="baseMsg.hosts[0]">
                                                <div class="host-item" v-for="item in baseMsg.hosts" :key="item.id" @click="toHost(item.id)">{{item.label}}</div>
                                            </div>
                                            <div v-else>--</div>
                                        </div>
                                    </el-col>
                                </el-row>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-title">{{lang.ticket_title5}}</div>
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
                                            <!-- <div class="reply-img">
                                                <img :src="item.type == 'Client'? '/plugins/addon/idcsmart_ticket/template/clientarea/img/ticket/client.png':'/plugins/addon/idcsmart_ticket/template/clientarea/img/ticket/admin.png'">
                                            </div> -->
                                        </div>
                                        <div class="reply-item-content" v-html="item.content" @click="hanldeImage($event)">
                                            <!-- {{item.content}} -->
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
                                    <!-- <textarea ref="content" id="tiny" name="content"></textarea> -->
                                </div>
                            </div>
                            <div class="card-footer">
                                <el-upload ref="fileupload" class="upload-btn" action="/console/v1/upload" :before-remove="beforeRemove" multiple :file-list="fileList" :on-success="handleSuccess" :on-progress="handleProgress">
                                    <el-button icon="el-icon-upload2">上传文件</el-button>
                                </el-upload>
                                <div class="send-btn" @click="doReplyTicket" v-loading="sendBtnLoading">
                                    {{lang.ticket_btn8}}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 确认关闭弹窗 -->
                    <div class="del-dialog">
                        <el-dialog width="6.8rem" :visible.sync="visible" :show-close=false>
                            <div class="dialog-title">
                                {{lang.ticket_title6}}
                            </div>
                            <div class="dialog-main">
                                {{lang.ticket_tips11}} {{baseMsg.title}}，{{lang.ticket_tips12}}
                            </div>
                            <div class="dialog-footer">
                                <div class="btn-ok" @click="doCloseTicket" v-loading="delLoading">{{lang.ticket_btn6}}</div>
                                <div class="btn-no" @click="visible= false">{{lang.ticket_btn9}}</div>
                            </div>
                        </el-dialog>
                    </div>
                    <div style="height: 0;">
                        <img id="viewer" :src="preImg" alt="">
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/plugins/addon/idcsmart_ticket/template/clientarea/api/common.js"></script>
    <script src="/plugins/addon/idcsmart_ticket/template/clientarea/api/ticket.js"></script>
    <script src="/plugins/addon/idcsmart_ticket/template/clientarea/js/ticketDetails.js"></script>
    <script src="/plugins/addon/idcsmart_ticket/template/clientarea/utils/util.js"></script>
    <script src="/plugins/addon/idcsmart_ticket/template/clientarea/js/tinymce/tinymce.min.js"></script>
    <script src="/plugins/addon/idcsmart_ticket/template/clientarea/js/common/viewer.min.js"></script>