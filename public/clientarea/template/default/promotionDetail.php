{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/promotionDetail.css">
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
                    <div class="promotionDetail-contetn">
                        <header>
                          <img src="/{$template_catalog}/template/{$themes}/img/invoice/路径 5684.png" alt="" @click="goBack">
                          促销详情
                        </header>
                        <main>
                          <p class="title">基础信息</p>

                          <el-form  :rules="rules" :model="formData" ref="ruleForm" label-width="1.2rem" label-position="left" class="demo-ruleForm">
                            <el-form-item label="活动名称" prop="name">
                              <el-input v-model="formData.name"></el-input>
                            </el-form-item>

                            <el-row>
                              <el-col :span="10">
                                  <el-form-item label="活动开始时间" prop="startTime">
                                    <el-date-picker
                                      style="width:3.6rem;"
                                      align="center"
                                      v-model="formData.startTime"
                                      type="datetime"
                                      placeholder="选择活动开始时间">
                                    </el-date-picker>
                                  </el-form-item>
                              </el-col>

                              <el-col :span="10">
                                  <el-form-item label="活动结束时间" prop="endTime">
                                      <el-date-picker
                                        style="width:3.6rem;"
                                        align="center"
                                        v-model="formData.endTime"
                                        type="datetime"
                                        placeholder="选择活动结束时间">
                                      </el-date-picker>
                                  </el-form-item>
                              </el-col>
                            </el-row>

                            <el-form-item label="活动描述">
                              <el-input class="textarea" v-model="formData.aaa" type="textarea"></el-input>
                            </el-form-item>

                            <el-form-item label="促销方式" prop="price">
                                <div>
                                  <el-radio v-model="formData.type" label="1">折扣</el-radio>
                                </div>
                                <div>
                                  <el-input v-model="formData.price"></el-input> <span>%</span>
                                </div>
                                <p class="text">优惠后金额=折前金额*（100-输入折扣）%</p>
                            </el-form-item>
                          </el-form>
                          
                          <p class="title">促销应用</p>

                          <div class="content-table">
                             <el-table v-loading="loading"  :date="tableData" style="width: 100%;margin-bottom: .2rem;">
                                 <el-table-column prop="id"  type="selection" width="80" :show-overflow-tooltip="true" align="left">
                                 </el-table-column>
                                 <el-table-column prop="id"   width="120" label="应用名称"  :show-overflow-tooltip="true" align="left">
                                 </el-table-column>
                                 <el-table-column prop="id"  width="140"  label="应用标识"  :show-overflow-tooltip="true" align="left">
                                 </el-table-column>
                                 <el-table-column prop="id" min-width="140"  label="应用类型"  :show-overflow-tooltip="true" align="left">
                                 </el-table-column>
                                 <el-table-column prop="id" width="140"  label="应用模块"  :show-overflow-tooltip="true" align="left">
                                 </el-table-column>
                                 <el-table-column prop="id" width="110" label="出售价格" :show-overflow-tooltip="true" align="left">
                                 </el-table-column>
                             </el-table>
                             <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange">
                             </pagination>
                          </div>
                          <el-button @click="btn">确定</el-button>
                        </main>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/js/promotionDetail.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}