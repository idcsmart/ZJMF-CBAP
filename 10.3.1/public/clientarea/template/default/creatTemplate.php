{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/creatTemplate.css">
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
            <div class="page-title">
              <div class="back-btn" @click="goBack">
                <img src="/{$template_catalog}/template/{$themes}/img/finance/back.png" alt="">
              </div>
              <div class="title">{{lang.template_text1}}</div>
            </div>
            <el-form hide-required-asterisk class="tem-form" :model="ruleForm" :rules="rules" ref="ruleForm" label-width="2rem" label-position="left">

              <div class="whios-title">
                <el-divider direction="vertical"></el-divider>
                <span>{{lang.template_text2}}</span>
              </div>
              <el-form-item label="">
                <el-checkbox v-model="checked" @change="useAccont">{{lang.template_text3}}</el-checkbox>
              </el-form-item>
              <el-form-item :label="lang.template_text4">
                <div class="user-type">
                  <div class="type-item" @click="typeChange('personal')" :class="ruleForm.type === 'personal' ? 'is-active': ''">{{lang.template_text5}}</div>
                  <div class="type-item" @click="typeChange('enterprise')" :class="ruleForm.type === 'enterprise' ? 'is-active': ''">{{lang.template_text6}}</div>
                </div>
              </el-form-item>
              <el-form-item :label="lang.template_text7" prop="zh_owner">
                <el-input v-model="ruleForm.zh_owner" :placeholder="lang.template_text8"></el-input>
                <div class="form-tip">{{lang.template_text9}}</div>
              </el-form-item>
              <el-form-item :label="lang.template_text10" prop="zh_all_name">
                <el-input v-model="ruleForm.zh_all_name" :placeholder="lang.template_text11"></el-input>
              </el-form-item>
              <el-form-item :label="lang.template_text12" prop="zh_last_name">
                <el-input v-model="ruleForm.zh_last_name" :placeholder="lang.template_text13"></el-input>
              </el-form-item>
              <el-form-item :label="lang.template_text14" prop="zh_first_name">
                <el-input v-model="ruleForm.zh_first_name" :placeholder="lang.template_text15"></el-input>
              </el-form-item>
              <el-form-item :label="lang.template_text16" prop="email">
                <el-input v-model="ruleForm.email" :placeholder="lang.template_text17"></el-input>
              </el-form-item>
              <el-form-item :label="lang.template_text18" prop="cityArr">
                <el-cascader v-model="ruleForm.cityArr" :options="citys" clearable></el-cascader>
              </el-form-item>
              <el-form-item :label="lang.template_text19" prop="zh_address">
                <el-input v-model="ruleForm.zh_address" :placeholder="lang.template_text20"></el-input>
              </el-form-item>
              <el-form-item :label="lang.template_text21" prop="postal_code">
                <el-input v-model="ruleForm.postal_code" :placeholder="lang.template_text22"></el-input>
              </el-form-item>
              <el-form-item :label="lang.template_text23" prop="phone">
                <el-input v-model="ruleForm.phone" :placeholder="lang.template_text24"></el-input>
              </el-form-item>
              <div class="whios-title">
                <el-divider direction="vertical"></el-divider>
                <span>{{lang.template_text25}}</span>
              </div>
              <el-form-item :label="lang.template_text26" prop="en_owner">
                <el-input v-model="ruleForm.en_owner" :placeholder="lang.template_text27"></el-input>
                <div class="form-tip">{{lang.template_text28}}</div>
              </el-form-item>
              <el-form-item :label="lang.template_text29" prop="en_all_name">
                <el-input v-model="ruleForm.en_all_name" :placeholder="lang.template_text27"></el-input>
                <div class="form-tip">{{lang.template_text28}}</div>
              </el-form-item>
              <el-form-item :label="lang.template_text30" prop="en_last_name">
                <el-input v-model="ruleForm.en_last_name" :placeholder="lang.template_text31"></el-input>
              </el-form-item>
              <el-form-item :label="lang.template_text32" prop="en_first_name">
                <el-input v-model="ruleForm.en_first_name" :placeholder="lang.template_text33"></el-input>
              </el-form-item>
              <el-form-item :label="lang.template_text34" prop="en_address">
                <el-input v-model="ruleForm.en_address" :placeholder="lang.template_text35"></el-input>
              </el-form-item>
              <div class="whios-title">
                <el-divider direction="vertical"></el-divider>
                <span>{{lang.template_text36}}</span>
              </div>
              <el-form-item :label="lang.template_text37" prop="idtype">
                <el-select v-model="ruleForm.idtype" :placeholder="lang.template_text38">
                  <el-option v-for="item in selectIdTypeOption" :label="item.label" :value="item.value" :key="item.value"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item :label="lang.template_text39" prop="idnum">
                <el-input v-model="ruleForm.idnum" :placeholder="lang.template_text40"></el-input>
              </el-form-item>
              <el-form-item :label="lang.template_text41">
                <el-checkbox v-model="isAgree">
                  {{lang.template_text42}}
                  <span class="a-blue-text" @click="goUrl">{{lang.template_text43}}</span>
                  {{lang.template_text44}}
                </el-checkbox>
              </el-form-item>
              <div class="sub-box">
                <el-button :loading="subLoading" @click="submitForm('ruleForm')">{{lang.template_text45}}</el-button>
              </div>
            </el-form>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/goodsList.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/creatTemplate.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/citys/citys.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/pinyin/pinyin.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
  {include file="footer"}