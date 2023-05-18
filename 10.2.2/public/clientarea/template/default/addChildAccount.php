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
                    <header class="addAccount-header">
                        <img src="/{$template_catalog}/template/{$themes}/img/invoice/路径 5684.png" alt="" @click="goBack">
                        <h1 v-if="accountId">{{accountType == 'edit'? '编辑子账户':'详情'}}</h1>
                        <h1 v-else>新增子账户</h1>
                    </header>
                                    <div class="addAccount-box">
                                        <p class="title"> 基本信息 </p>
                                        <el-form :inline="true"  :rules="rules"  :model="addAccountForm" class="demo-form-inline" label-width="1rem" ref="ruleForm">
                                            <div class="top">
                                                <el-form-item label="账户" prop="username">
                                                    <el-input v-model="addAccountForm.username" clearable placeholder="请输入账户" ></el-input>
                                                </el-form-item>
                                                <el-form-item label="手机" prop="phone" :rules="addAccountForm.email? {} : {required: true, message: '请输入手机号码', trigger: 'blur' }">
                                                    <el-input v-model="addAccountForm.phone" clearable placeholder="请输入手机" ></el-input>
                                                </el-form-item>
                                                <el-form-item label="区号" prop="phone_code" v-if="addAccountForm.phone" :rules="addAccountForm.phone?  {required: true, message: '请输入国际电话区号', trigger: 'blur' } : {} ">
                                                    <el-input v-model="addAccountForm.phone_code" clearable placeholder="请输入国际电话区号" ></el-input>
                                                </el-form-item>
                                                <el-form-item label="邮箱" prop="email" :rules="addAccountForm.phone? {} : {required: true,validator: validateEmail,message: '请填写正确的邮箱', trigger: 'blur'}">
                                                    <el-input v-model="addAccountForm.email" clearable placeholder="请输入邮箱" ></el-input>
                                                </el-form-item>
                                                <el-form-item label="密码" prop="password" v-if="!isDetali">
                                                    <el-input v-model="addAccountForm.password" show-password clearable placeholder="请输入密码" ></el-input>
                                                </el-form-item>
                                            </div>
                                            <div class="bom">
                                                <el-form-item label="所属项目" v-if="projectList.length > 0">
                                                    <el-select v-model="addAccountForm.project_id" clearable multiple placeholder="请选择">
                                                        <el-option
                                                        v-for="item in projectList"
                                                        :key="item.id"
                                                        :label="item.name"
                                                        
                                                        :value="item.id">
                                                        </el-option>
                                                    </el-select>
                                                </el-form-item>

                                                <el-form-item label="可见产品" v-else>
                                                    <el-select v-model="addAccountForm.visible_product" placeholder="产品类型" >
                                                        <el-option label="产品类型" value="module"  >  </el-option>
                                                        <el-option label="具体产品" value="host"  >  </el-option>
                                                    </el-select>
                                                    <el-select v-model="activeModule"  clearable v-if="addAccountForm.visible_product === 'module' " multiple placeholder="请选择" style="width:40%">
                                                        <el-option
                                                        v-for="item in productList"
                                                        :key="item.name"
                                                        :label="item.display_name"
                                                        
                                                        :value="item.name">
                                                        </el-option>
                                                    </el-select>
                                                    <el-select v-model="host_id" clearable v-else multiple placeholder="请选择" style="width:40%">
                                                        <el-option
                                                        v-for="item in host_idList"
                                                        :key="item.id"
                                                        
                                                        :label="item.product_name"
                                                        :value="item.id">
                                                        </el-option>
                                                    </el-select>
                                                </el-form-item>
                                            </div>
                                            
                                        </el-form>
                                        <p class="title"> 通知权限 </p>
                                        <div class="notice-box">
                                            <span class="notice-title">通知</span>
                                            <el-checkbox-group v-model="addAccountForm.notice">
                                                <el-checkbox  label="product">产品通知</el-checkbox>
                                                <el-checkbox  label="marketing">营销通知</el-checkbox>
                                                <el-checkbox  label="ticket">工单通知</el-checkbox>
                                                <el-checkbox  label="cost" >费用通知</el-checkbox>
                                                <el-checkbox  label="recommend" >推介通知</el-checkbox>
                                                <el-checkbox  label="system" >系统通知</el-checkbox>
                                            </el-checkbox-group>
                                        </div>
                                        
                                        <div class="tree-box">
                                            <span class="tree-title">权限</span>
                                            <div class="tree">
                                                <div class="tree-left">
                                                    <el-tree
                                                        :data="permissionsLeftList"
                                                        show-checkbox
                                                        node-key="id"
                                                        @check="checkLeftFun"
                                                        show-checkbox
                                                        ref="leftTree"
                                                        :check-strictly="isCheck"
                                                        default-expand-all
                                                        :props="defaultProps">
                                                    </el-tree>
                                                </div>
                                                
                                                <div class="tree-right">
                                                    <el-tree
                                                        :data="permissionsRightList"
                                                        show-checkbox
                                                        default-expand-all
                                                        node-key="id"
                                                        ref="rightTree"
                                                        :check-strictly="isCheckRight"
                                                        @check="checkRightFun"
                                                        show-checkbox
                                                        :props="defaultProps">
                                                    </el-tree>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <footer class="addAccount-footer">
                                        <button class="footer-btn1" @click="saveBtn" > 保存</button>
                                        <button class="footer-btn2" @click="saveBtn" > 取消</button>
                                    </footer>
                    </el-main>
            </el-container>
        </el-container>
    </div>  
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/childAccount.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/addChildAccount.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}