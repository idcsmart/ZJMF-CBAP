{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/childAccount.css">
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
                        <div class="clid-box">
                            <header>
                                <h2>子账户列表</h2>
                                <el-button type="primary" @click="addChildAccountBtn" v-show="is_sub_account == 0">新增子账户</el-button>
                            </header>
                            <el-table :data="cildAccountList" style="width: 100%">
                                <el-table-column prop="id" label="ID"></el-table-column>
                                <el-table-column prop="username" label="账户 "></el-table-column>
                                <el-table-column prop="date" label="上次登录时间">
                                    <template slot-scope="{row}">
                                        {{row.last_action_time | formateTime }}
                                    </template>
                                </el-table-column>
                                <el-table-column  width="120px" label="操作" v-if="is_sub_account == 0 ">
                                    <template slot-scope="{row}">
                                        <div class="caozuo">
                                            <el-popover placement="top-start" trigger="hover">
                                                <div class="operation-box">
                                                   <div v-if="row.status" slot="reference" class="operation-item" @click="changeState(row)">
                                                        停用
                                                    </div>
                                                    <div v-else slot="reference" class="operation-item" @click="changeState(row)">
                                                        启用
                                                    </div>
                                                    <div class="operation-item" @click="handleEdit(row)">编辑</div>
                                                    <div class="operation-item"  @click="handleDel(row)">删除</div>
                                                </div>
                                                <i slot="reference" style="color: #0058FF"class="el-icon-more"></i>
                                                                
                                            </el-popover>
                                        </div>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange">
                            </pagination>
                        </div>
                </el-main>
            </el-container>
        </el-container>
    </div>  
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/childAccount.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/childAccount.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}