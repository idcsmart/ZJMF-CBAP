{include file="header"}
  <!-- 页面独有样式 -->
  <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/authentication.css">
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
            <div class="main-top">
              <div class="main-card-title"><img src="/{$template_catalog}/template/{$themes}/img/finance/back.png" class="top-back-img" @click="backTicket">实名认证</div>
              <div class="top-line"></div>
            </div>
            <!-- 企业认证页面 -->
            <div class="main-content">
              <el-form :model="certificationEnterprise" class="certification-enterprise" :rules="enterpriseRules" ref="certificationEnterprise" label-position='top' label-width="100px">
                <el-form-item label="企业全称" prop="company">
                  <el-input v-model="certificationEnterprise.company" placeholder="请输入企业全称"></el-input>
                </el-form-item>
                <el-form-item label="统一社会信用代码" prop="company_organ_code">
                  <el-input v-model="certificationEnterprise.company_organ_code" placeholder="请输入企业统一社会信用代码"></el-input>
                </el-form-item>
                <el-form-item label="认证人姓名" prop="card_name">
                  <el-input v-model="certificationEnterprise.card_name" placeholder="请输入认证人姓名"></el-input>
                </el-form-item>
                <el-form-item label="手机号" prop="phone">
                  <el-input v-model="certificationEnterprise.phone" placeholder="请输入手机号"></el-input>
                </el-form-item>
                <!-- <el-form-item label="证件类型" prop="card_type">
                  <el-select v-model="certificationEnterprise.card_type">
                    <el-option v-for="item in id_card_type" :key="item.label" :label="item.label" :value="item.value">
                    </el-option>
                  </el-select>
                </el-form-item> -->
                <el-form-item v-for="(item,index) in custom_fieldsObj" :key="index" :prop="certificationEnterprise.custom_fields[`${item.field}`]" :label="item.title" :rules="{ required: item.required, message: item.tip, trigger: 'blur'}">
                  <el-input v-model="certificationEnterprise.custom_fields[`${item.field}`]" v-if="item.type ==='text'"></el-input>
                  <el-select v-model="certificationEnterprise.custom_fields[`${item.field}`]" clearable v-if="item.type ==='select'">
                    <el-option v-for=" (items,key,indexs) in item.options" :key="indexs" :label="items" :value="key">
                    </el-option>
                  </el-select>
                  <el-upload v-if="item.type==='file'" class="upload-btn" action="/console/v1/upload" :before-remove="beforeRemove" multiple :file-list="filelist" :on-success="(response, file, fileList)=>handleSuccess(response, file, fileList,item)" ref="fileupload">
                    <el-button icon="el-icon-upload2">上传文件</el-button>
                  </el-upload>
                </el-form-item>
                <el-form-item label="证件号码" prop="card_number">
                  <el-input v-model="certificationEnterprise.card_number" placeholder="请输入您的证件号码"></el-input>
                </el-form-item>
                <el-form-item label="证件照片(允许的后缀名: .jpg、.gif、.jpeg、png)" v-if="certificationInfoObj.certification_upload ==='1'" required>
                  <div class="upload-btn">
                    <el-upload action="/console/v1/upload" accept=".jpg,.gif,.jpeg,.png" :before-upload="(file)=>onUpload(file,'img_one')" :file-list="card_one_fileList" :on-remove="handleRemove1" :limit=1 list-type="picture-card" :class="{ hide:img_one!='' }" :on-success="handleSuccess1">
                      <div slot="default" class="upload-btn-img">
                        <img src="/{$template_catalog}/template/{$themes}/img/account/IDcard-1.png" alt="">
                      </div>
                      <div slot="file" slot-scope="{file}">
                        <img class="el-upload-list__item-thumbnail" :src="file.url" alt="">
                        <span class="el-upload-list__item-actions">
                          <span class="el-upload-list__item-preview" @click="handlePictureCardPreview(file)">
                            <i class="el-icon-zoom-in"></i>
                          </span>
                          <span class="el-upload-list__item-delete" @click="handleRemove1">
                            <i class="el-icon-delete"></i>
                          </span>
                        </span>
                      </div>
                      <div slot="tip" class="el-upload__tip red-text" v-show="uploadTipsText1!=''">{{ uploadTipsText1 }}</div>
                    </el-upload>
                    <el-upload action="/console/v1/upload" accept=".jpg,.gif,.jpeg,.png" :before-upload="(file)=>onUpload(file,'img_two')" :file-list="card_two_fileList" :on-remove="handleRemove2" :limit=1 list-type="picture-card" :class="{ hide:img_two!='' }" :on-success="handleSuccess2">
                      <div slot="default" class="upload-btn-img">
                        <img src="/{$template_catalog}/template/{$themes}/img/account/IDcard-2.png" alt="">
                      </div>
                      <div slot="file" slot-scope="{file}">
                        <img class="el-upload-list__item-thumbnail" :src="file.url" alt="">
                        <span class="el-upload-list__item-actions">
                          <span class="el-upload-list__item-preview" @click="handlePictureCardPreview(file)">
                            <i class="el-icon-zoom-in"></i>
                          </span>
                          <span class="el-upload-list__item-delete" @click="handleRemove2">
                            <i class="el-icon-delete"></i>
                          </span>
                        </span>
                      </div>
                      <div slot="tip" class="el-upload__tip red-text" v-show="uploadTipsText2!=''">{{ uploadTipsText2 }}</div>
                    </el-upload>
                  </div>
                  <el-upload action="/console/v1/upload" accept=".jpg,.gif,.jpeg,.png" :before-upload="(file)=>onUpload(file,'img_three')" :file-list="card_three_fileList" :on-remove="handleRemove3" :limit=1 list-type="picture-card" :class="{ hide:img_three!='' }" :on-success="handleSuccess3">
                    <div slot="default" class="upload-btn-img">
                      <img src="/{$template_catalog}/template/{$themes}/img/account/IDcard-3.png" alt="">
                    </div>
                    <div slot="file" slot-scope="{file}">
                      <img class="el-upload-list__item-thumbnail" :src="file.url" alt="">
                      <span class="el-upload-list__item-actions">
                        <span class="el-upload-list__item-preview" @click="handlePictureCardPreview(file)">
                          <i class="el-icon-zoom-in"></i>
                        </span>
                        <span class="el-upload-list__item-delete" @click="handleRemove3">
                          <i class="el-icon-delete"></i>
                        </span>
                      </span>
                    </div>
                    <div slot="tip" class="el-upload__tip red-text" v-show="uploadTipsText3!=''">{{ uploadTipsText3 }}</div>
                  </el-upload>
                </el-form-item>
              </el-form>
              <div class="next-box">
                <el-button v-loading="sunmitBtnLoading" @click="companySumit">下一步</el-button>
              </div>
            </div>
          </div>
          <el-dialog :visible.sync="dialogVisible">
            <div class="visibleImg">
              <img :src="dialogImageUrl" alt="">
            </div>
          </el-dialog>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/certification.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/authenticationCompny.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  {include file="footer"}