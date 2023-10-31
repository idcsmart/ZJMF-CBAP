{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/security_group.css">
</head>
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
      <aside-menu @getruleslist="getRule"></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <el-main>
          <!-- 自己的东西 -->
          <div class="main-card security-group">
            <div class="main-card-title">
              <img :src="`${baseUrl}img/finance/back.png`" alt="" @click="back" class="back" style="cursor: pointer;">
              {{lang.security_title}}
            </div>
            <el-tabs v-model="activeName" @tab-click="handleClick">
              <el-tab-pane :label="lang.in_rules" name="in">
                <div class="content-table">
                  <div class="content_searchbar">
                    <div style="display: flex;">
                      <div class="left-btn" @click="createSecurity">
                        {{lang.com_config.add}}{{lang.rules}}
                      </div>
                      <div class="left-btn add" @click="batchCreateSecurity">
                        {{lang.batch_add}}
                      </div>
                      <div class="left-btn del" @click="batchDelete">
                        {{lang.batch_delete}}
                      </div>
                    </div>
                    <div class="searchbar com-search"></div>
                  </div>
                  <div class="tabledata">
                    <el-table v-loading="loading" :data="inList" ref="multipleTable" style="width: 100%;margin-bottom: .2rem;" @selection-change="handleSelectionChange">
                      <el-table-column type="selection" width="55">
                      </el-table-column>
                      <el-table-column prop="id" label="ID" align="left" width="100">
                      </el-table-column>
                      <el-table-column prop="protocol" :label="lang.protocol" align="left" width="150">
                      </el-table-column>
                      <el-table-column prop="port" :label="lang.port_range" align="left" width="200">
                      </el-table-column>
                      <el-table-column prop="ip" :label="lang.auth_ip" align="left" width="150">
                      </el-table-column>
                      <el-table-column prop="description" :label="lang.account_label9" min-width="200" align="left">
                      </el-table-column>
                      <el-table-column prop="create_time" :label="lang.account_label10" align="left" width="200">
                        <template slot-scope="scope">
                          <span>{{scope.row.create_time | formateTime}}</span>
                        </template>
                      </el-table-column>
                      <el-table-column prop="type" :label="lang.security_label3" width="100" align="left">
                        <template slot-scope="scope">
                          <el-popover placement="top-start" trigger="hover">
                            <div class="operation">
                              <div class="operation-item" @click="editItem(scope.row)">{{lang.edit}}</div>
                              <div class="operation-item" @click="deleteItem(scope.row, 'in')">{{lang.security_btn4}}
                              </div>
                            </div>
                            <span class="more-operation" slot="reference">
                              <div class="dot"></div>
                              <div class="dot"></div>
                              <div class="dot"></div>
                            </span>
                          </el-popover>
                        </template>
                      </el-table-column>
                    </el-table>
                    <pagination v-if="inParams.total" :page-data="inParams" @sizechange="sizeChange" @currentchange="currentChange">
                    </pagination>
                  </div>
                </div>
              </el-tab-pane>
              <el-tab-pane :label="lang.out_rules" name="out">
                <div class="content-table">
                  <div class="content_searchbar">
                    <div style="display: flex;">
                      <div class="left-btn" @click="createSecurity">
                        {{lang.com_config.add}}{{lang.rules}}
                      </div>
                      <div class="left-btn add" @click="batchCreateSecurity">
                        {{lang.batch_add}}
                      </div>
                      <div class="left-btn del" @click="batchDelete">
                        {{lang.batch_delete}}
                      </div>
                    </div>
                    <div class="searchbar com-search"></div>
                  </div>
                  <div class="tabledata">
                    <el-table v-loading="loading" :data="outList" ref="multipleTable" style="width: 100%;margin-bottom: .2rem;" @selection-change="handleSelectionChange">
                      <el-table-column type="selection" width="55">
                      </el-table-column>
                      <el-table-column prop="id" label="ID" align="left" width="100">
                      </el-table-column>
                      <el-table-column prop="protocol" :label="lang.protocol" align="left" width="150">
                      </el-table-column>
                      <el-table-column prop="port" :label="lang.port_range" align="left" width="200">
                      </el-table-column>
                      <el-table-column prop="ip" :label="lang.auth_ip" align="left" width="150">
                      </el-table-column>
                      <el-table-column prop="description" :label="lang.account_label9" min-width="200" align="left">
                      </el-table-column>
                      <el-table-column prop="create_time" :label="lang.account_label10" align="left" width="200">
                        <template slot-scope="scope">
                          <span>{{scope.row.create_time | formateTime}}</span>
                        </template>
                      </el-table-column>
                      <el-table-column prop="type" :label="lang.security_label3" width="100" align="left">
                        <template slot-scope="scope">
                          <el-popover placement="top-start" trigger="hover">
                            <div class="operation">
                              <div class="operation-item" @click="editItem(scope.row)">{{lang.edit}}</div>
                              <div class="operation-item" @click="deleteItem(scope.row, 'out')">{{lang.security_btn4}}
                              </div>
                            </div>
                            <span class="more-operation" slot="reference">
                              <div class="dot"></div>
                              <div class="dot"></div>
                              <div class="dot"></div>
                            </span>
                          </el-popover>
                        </template>
                      </el-table-column>
                    </el-table>
                    <pagination v-if="outParams.total" :page-data="outParams" @sizechange="sizeChange" @currentchange="currentChange">
                    </pagination>
                  </div>
                </div>
              </el-tab-pane>
              <el-tab-pane :label="lang.relation_instance" name="relation">
                <div class="content-table">
                  <div class="content_searchbar">
                    <div class="left-btn" @click="relationCloud">
                      {{lang.com_config.add}}{{lang.cloud_menu_1}}
                    </div>
                    <div class="searchbar com-search">

                    </div>
                  </div>
                  <div class="tabledata">
                    <el-table v-loading="loading" :data="dataList" style="width: 100%;margin-bottom: .2rem;">
                      <el-table-column prop="name" :label="lang.cloud_menu_1" align="left" width="300">
                      </el-table-column>
                      <el-table-column prop="ip" label="IP" align="left">
                        <template slot-scope="{row}">
                          <span>{{row.ip || '--'}}</span>
                        </template>
                      </el-table-column>
                      <el-table-column prop="type" :label="lang.security_label3" width="100" align="left">
                        <template slot-scope="scope">
                          <el-popover placement="top-start" trigger="hover">
                            <div class="operation">
                              <div class="operation-item" @click="deleteItem(scope.row, 'relation')">{{lang.security_btn4}}</div>
                            </div>
                            <span class="more-operation" slot="reference">
                              <div class="dot"></div>
                              <div class="dot"></div>
                              <div class="dot"></div>
                            </span>
                          </el-popover>
                        </template>
                      </el-table-column>
                    </el-table>
                    <pagination v-if="params.total" :page-data="params" @sizechange="sizeChange" @currentchange="currentChange">
                    </pagination>
                  </div>
                </div>
              </el-tab-pane>
            </el-tabs>
            <!-- 创建/编辑规则弹窗 -->
            <div class="create-api-dialog">
              <el-dialog :visible.sync="isShowCj" :show-close=false @close="cjClose">
                <p class="dia-tit">{{optTitle}}</p>
                <el-form :model="singleForm" ref="singleForm" v-if="isShowCj" :rules="rules" label-position="left">
                  <el-form-item :label="lang.protocol" prop="protocol">
                    <el-select v-model="singleForm.protocol" :placeholder="`${lang.protocol}`">
                      <el-option :label="item.label" :value="item.value" :key="item.value" v-for="item in protocol"></el-option>
                    </el-select>
                  </el-form-item>
                  <el-form-item :label="lang.common_cloud_label13" prop="port">
                    <el-input :placeholder="`${lang.security_tip2}`" v-model="singleForm.port" :disabled="singleForm.protocol !== 'tcp' && singleForm.protocol !== 'udp'"></el-input>
                  </el-form-item>
                  <el-form-item :label="lang.auth_ip" prop="ip">
                    <el-input :placeholder="`${lang.auth_ip}`" v-model="singleForm.ip"></el-input>
                  </el-form-item>
                  <el-form-item :label="lang.account_label9" prop="description">
                    <el-input type="textarea" :placeholder="`${lang.account_label9}`" :rows="4" v-model="singleForm.description"></el-input>
                  </el-form-item>
                  <div class="dialog-footer">
                    <el-button type="primary" class="btn-ok" @click="submitForm" :loading="submitLoading">{{lang.referral_btn6}}</el-button>
                    <el-button class="btn-no" @click="isShowCj = false">{{lang.referral_btn7}}</el-button>
                  </div>
                </el-form>
              </el-dialog>
            </div>
            <!-- 批量添加规则 -->
            <div class="create-api-dialog">
              <el-dialog width="6.6rem" :visible.sync="batchVisible" :show-close="false">
                <p class="dia-tit">{{optTitle}}</p>
                <el-form :model="batchForm" ref="batchForm" v-if="batchVisible" label-width=".7rem" :rules="batchRules" label-position="left">
                  <p class="s-tit"><span class="red">*</span>{{lang.common_port}}</p>
                  <el-form-item>
                    <!-- 循环展示协议和端口 -->
                    <div class="item" v-for="(item,index) in batchArr" :key="index">
                      <p>
                        <el-checkbox v-model="item.check" @change="changePar($event,index)">{{item.tit}}</el-checkbox>
                      </p>
                      <div class="child">
                        <span class="s-item" v-for="(el,ind) in item.child" :key="ind">
                          <el-checkbox v-model="el.check" @change="changeChild($event,index,ind)">{{el.tit}}</el-checkbox>
                        </span>
                      </div>
                    </div>
                  </el-form-item>
                  <el-form-item :label="lang.auth_ip" prop="ip">
                    <el-input :placeholder="`${lang.auth_ip}`" v-model="batchForm.ip"></el-input>
                  </el-form-item>
                  <el-form-item :label="lang.account_label9" prop="description">
                    <el-input type="textarea" :placeholder="`${lang.account_label9}`" :rows="4" v-model="batchForm.description"></el-input>
                  </el-form-item>
                  <div class="dialog-footer">
                    <el-button type="primary" class="btn-ok" @click="batchSubmitForm" :loading="submitLoading">{{lang.referral_btn6}}</el-button>
                    <el-button class="btn-no" @click="batchVisible = false">{{lang.referral_btn7}}</el-button>
                  </div>
                </el-form>
              </el-dialog>
            </div>
            <!-- 关联实例 -->
            <div class="create-api-dialog">
              <el-dialog width="6.2rem" :visible.sync="relationVisible" :show-close=false @close="reClose">
                <p class="dia-tit">{{optTitle}}</p>
                <el-form :model="relationForm" ref="relationForm" v-if="relationVisible" :rules="relationRules" label-position="left">
                  <el-form-item class="re-tip">
                    {{lang.security_tip3}}
                  </el-form-item>
                  <el-form-item :label="lang.cloud_menu_1" prop="host_id">
                    <el-select v-model="relationForm.host_id" :placeholder="`${lang.cloud_menu_1}`">
                      <el-option :label="item.name" :value="item.id" :key="item.id" v-for="item in availableCloud"></el-option>
                    </el-select>
                  </el-form-item>
                  <div class="dialog-footer">
                    <el-button type="primary" class="btn-ok" @click="submitRelation" :loading="submitLoading">{{lang.referral_btn6}}</el-button>
                    <el-button class="btn-no" @click="relationVisible = false">{{lang.referral_btn7}}</el-button>
                  </div>
                </el-form>
              </el-dialog>
            </div>
            <!-- 删除弹窗 -->
            <div class="delete-dialog">
              <el-dialog width="6.8rem" :visible.sync="isShowDel" :show-close=false @close="delClose">
                <div class="del-dialog-title">
                  <i class="el-icon-warning-outline del-icon"></i>{{delTile}}?
                </div>
                <div class="del-dialog-main">
                  <!-- {{delTile}}:{{delName}} -->
                </div>
                <div class="del-dialog-footer">
                  <div class="btn-ok" @click="delSub" v-loading="submitLoading">{{lang.security_btn9}}</div>
                  <div class="btn-no" @click="delClose">{{lang.security_btn6}}</div>
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
  <script src="/{$template_catalog}/template/{$themes}/api/security_group.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/group_rules.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  {include file="footer"}
