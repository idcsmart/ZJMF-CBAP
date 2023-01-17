{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/serveDetail.css">
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
                  <div class="applicationDetail-content">
                    <header>
                      <img src="/{$template_catalog}/template/{$themes}/img/invoice/路径 5684.png" alt="" @click="goBack">
                      服务详情
                    </header>
                  <main>
                      <p class="title">基础信息</p>
                      <el-form :model="formData" :rules="rules" ref="ruleForm" label-position="left"  label-width="1rem">
                        
                        <el-form-item label="服务名称" prop="name" >
                          <el-input v-model="formData.name"></el-input>
                        </el-form-item>

                        <el-form-item label="系统类型" prop="system_type">
                            <el-select v-model="formData.system_type">
                                <el-option value="finance" label="魔方财务">魔方财务</el-option>
                                <el-option value="cloud" label="魔方云">魔方云</el-option>
                                <el-option value="dcim" label="DCIM">DCIM</el-option>
                            </el-select>
                        </el-form-item>

                        <el-form-item label="应用分类" prop="instruction">
                          <el-input v-model="formData.instruction"></el-input>
                        </el-form-item>

                        <p class="title">价格管理</p>

                        <el-form-item label="价格配置" prop="pay_type">
                          <div class="price-box">
                            <p class="price-top">
                               <el-radio v-model="formData.pay_type" :label="0">一次性/周期</el-radio>
                               <el-radio v-model="formData.pay_type" :label="1">免费</el-radio>
                            </p>
                            <div class="price-bom"  v-if="formData.pay_type == 0">
                              <div class="price-item">
                                <p> <el-input v-model="formData.onetime" placeholder="请输入价格"></el-input> /一次性</p>
                              </div>
                              <div class="price-item">
                                <p> <el-input v-model="formData.monthly" placeholder="请输入价格"></el-input> 元/月</p>
                                <p> <el-input v-model="formData.quarterly" placeholder="请输入价格"></el-input> 元/季</p>
                              </div>
                              <div class="price-item">
                                <p> <el-input v-model="formData.semiannually" placeholder="请输入价格"></el-input> 元/半年</p>
                                <p> <el-input v-model="formData.annually" placeholder="请输入价格"></el-input> 元/半年</p>
                              </div>
                            </div>
                          </div>
                        </el-form-item>
                        
                        <p class="title">应用介绍</p>

                        <el-form-item label="服务图标" prop="icon">
                            <p  style="margin:0.1rem 0;" class="text"> 允许的后缀名: .jpg .gif .jpeg .png， 图片比例1:1，最大支持500kb</p>
                            <el-upload
                              action="/console/v1/upload"
                              :on-preview="handlePictureCardPreview"
                              :on-remove="handleRemoveICon"
                              accept=".png,.jpg,.jpeg,.gif"
                              :on-success="onSuccessIcon"
                              :file-list="fileIconList"
                              :limit="1"
                              :on-exceed="onExceedIcon"
                              :before-upload="beforeUpload"
                              list-type="picture-card">
                              <i class="el-icon-plus"></i>
                            </el-upload>
                        </el-form-item>

                        <el-form-item label="服务图片" >
                          <p  style="margin:0.1rem 0;" class="text"> 允许的后缀名: .jpg .gif .jpeg .png，图片比例1:1</p>
                          <el-upload
                                action="/console/v1/upload"
                                list-type="picture-card"
                                :on-preview="handlePictureCardPreview"
                                :on-remove="handleRemoveImages"
                                accept=".png,.jpg,.jpeg,.gif"
                                :on-success="onSuccessImages"
                                :file-list="fileImagesList"
                                :before-upload="beforeUploadImags"
                            >
                                <i class="el-icon-plus"></i>
                          </el-upload>
                        </el-form-item>

                        <el-form-item label="服务介绍" prop="info">
                            <el-input type="textarea" :rows="4" v-model="formData.info"></el-input>
                        </el-form-item>


                        <el-form-item label="  " >
                          <div class="save-box">
                            <el-button @click="saveApp">提交</el-button>
                          </div>
                        </el-form-item>
                      </el-form>
                  </main>
                  <el-dialog :visible.sync="dialogVisible">
                    <img width="100%" :src="dialogImageUrl" alt="">
                  </el-dialog>
                  </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/serveDetail.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/serveDetail.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}