{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/applicationDetail.css">
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
                      应用详情
                    </header>

                  <main>
                      <p class="title">基础信息</p>
                      <el-form :model="formData" :rules="rules" ref="ruleForm" label-position="left"  label-width="1rem">
                        
                        <el-form-item label="应用名称" prop="name" >
                          <el-input v-model="formData.name"></el-input>
                        </el-form-item>

                        <el-form-item label="系统类型" prop="system_type">
                            <el-select v-model="formData.system_type">
                                <el-option value="finance" label="魔方财务">魔方财务</el-option>
                                <el-option value="cloud" label="魔方云">魔方云</el-option>
                                <el-option value="dcim" label="DCIM">DCIM</el-option>
                            </el-select>
                        </el-form-item>

                        <el-form-item label="应用分类" prop="type">
                            <el-select v-model="formData.type">
                                <el-option v-for="item in typeList" :value="item.value" :label="item.label"></el-option>
                            </el-select>
                        </el-form-item>

                        <el-form-item label="应用文件" prop="file">
                          <div class="upload-box">
                            <el-upload
                              class="upload-demo"
                              :file-list="fileZipList"
                              action="http://kfc.idcsmart.com/console/v1/upload"
                              :on-success="onSuccessFile"
                              :on-remove="handleRemoveZip"
                              :before-upload="beforeUploadFile"
                              :limit="1"
                              >
                                <el-button size="small">
                                  <i class="el-icon-upload2"></i> 点击上传
                                </el-button>
                                <p slot="tip"   class="text">请在文件根目录下压缩后上传，且文件解压后必须与标识一致。仅支持ZIP</p>
                            </el-upload>
                          </div>
                        </el-form-item>

                        <el-form-item label="应用标识" prop="uuid" >
                          <el-input v-model="formData.uuid" placeholder="上传文件后系统将自动识别" disabled></el-input>
                        </el-form-item>

                        <el-form-item label="授权管理" prop="name" v-if="false">
                          <div class="switch-box">
                            <el-switch
                              v-model="formData.name">
                            </el-switch>
                            <p class="text">开启后将会使用智简魔方官方授权服务器进行授权管理，如您的应用没有授权管理能力，建议开启，避免盗版</p>
                          </div>
                        </el-form-item>

                        <p class="title">促销价格</p>

                        <el-form-item label="价格配置" >
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

                        <el-form-item label="应用图标" prop="icon">
                            <p  style="margin:0.1rem 0;" class="text"> 允许的后缀名: .jpg .gif .jpeg .png， 图片比例1:1，最大支持500kb</p>
                            <el-upload
                              action="http://kfc.idcsmart.com/console/v1/upload"
                              :on-preview="handlePictureCardPreview"
                              :on-remove="handleRemoveIcon"
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

                        <el-form-item label="应用图片" >
                          <p  style="margin:0.1rem 0;" class="text"> 允许的后缀名: .jpg .gif .jpeg .png，图片比例1:1</p>
                          <el-upload
                                action="http://kfc.idcsmart.com/console/v1/upload"
                                accept=".png,.jpg,.jpeg,.gif"
                                :on-success="onSuccessApp"
                                :on-remove="handleRemoveApp"
                                :on-preview="handlePictureCardPreview"
                                :file-list="fileAppList"
                                list-type="picture-card">
                                <i class="el-icon-plus"></i>
                          </el-upload>
                        </el-form-item>

                        <el-form-item label="应用介绍" >
                        <el-input type="textarea" :rows="4" v-model="formData.instruction"></el-input>
                        </el-form-item>

                        <p class="title">版本管理</p>

                        <el-form-item label=" 版本号 "  prop="version">
                          <el-input v-model="formData.version"></el-input>
                        </el-form-item>
                        
                        <el-form-item label="版本说明"  prop="description">
                          <el-input type="textarea" :rows="4" v-model="formData.description"></el-input>
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
    <script src="/{$template_catalog}/template/{$themes}/js/applicationDetail.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/applicationDetail.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}