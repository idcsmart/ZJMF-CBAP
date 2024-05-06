{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/security_group.css">
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
            <div class="main-card-title">{{lang.security_title}}</div>
            <el-tabs v-model="activeName" @tab-click="handleClick">
              <el-tab-pane label="API" name="1" v-if="isShowAPI"></el-tab-pane>
              {foreach $addons as $addon}
              {if ($addon.name=='IdcsmartSshKey')}
              <el-tab-pane :label="lang.security_tab1" name="2"></el-tab-pane>
              {/if}
              {/foreach}
              <el-tab-pane :label="lang.security_tab2" name="3" v-if="isShowAPILog"></el-tab-pane>
              {foreach $addons as $addon}
              {if ($addon.name=='IdcsmartCloud')}
              <el-tab-pane :label="lang.security_group" name="4">
                <div class="content-table">
                  <div class="content_searchbar">
                    <div class="left-btn" @click="createSecurity">
                      {{lang.create_security_group}}
                    </div>
                    <div class="searchbar com-search">

                    </div>
                  </div>
                  <div class="tabledata">
                    <el-table v-loading="loading" :data="dataList" style="width: 100%;margin-bottom: .2rem;">
                      <el-table-column prop="name" :label="lang.security_label1" width="200" align="left">
                        <template slot-scope="{row}">
                          <a :href="`group_rules.htm?id=${row.id}`" class="link">{{row.name}}</a>
                        </template>
                      </el-table-column>
                      <el-table-column prop="host_num" :label="lang.cloud_menu_1" align="left" width="150">
                      </el-table-column>
                      <el-table-column prop="rule_num" :label="lang.rules" align="left">
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
                              <div class="operation-item" @click="deleteItem(scope.row)">{{lang.security_btn4}}</div>
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
                    <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange">
                    </pagination>
                  </div>
                </div>
              </el-tab-pane>
              {/if}
              {/foreach}
            </el-tabs>
            <!-- 创建/编辑安全组弹窗 -->
            <div class="create-api-dialog">
              <el-dialog width="6.8rem" :visible.sync="isShowCj" :show-close=false @close="cjClose">
                <div class="dialog-title">
                  {{lang.create_security_group}}
                </div>
                <div class="dialog-main">
                  <div class="label">{{lang.security_label1}}</div>
                  <el-input v-model="createForm.name"></el-input>
                  <div class="label">{{lang.account_label9}}</div>
                  <el-input type="textarea" rows="5" v-model="createForm.description"></el-input>
                  <el-alert class="alert-text" :title="errText" v-show="errText" type="error" show-icon
                    :closable="false">
                  </el-alert>
                </div>
                <div class="dialog-footer">
                  <div class="btn-ok" @click="cjSub" v-loading="submitLoading">{{lang.security_btn5}}</div>
                  <div class="btn-no" @click="cjClose">{{lang.security_btn6}}</div>
                </div>
              </el-dialog>
            </div>
            <!-- 删除安全组弹窗 -->
            <div class="delete-dialog">
              <el-dialog width="6.8rem" :visible.sync="isShowDel" :show-close=false @close="delClose">
                <div class="del-dialog-title">
                  <i class="el-icon-warning-outline del-icon"></i>{{lang.del_group}}?
                </div>
                <div class="del-dialog-main">
                  {{lang.del_group}}:{{delName}}
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
  <script src="/{$template_catalog}/template/{$themes}/api/security_group.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/security_group.js"></script>
  {include file="footer"}