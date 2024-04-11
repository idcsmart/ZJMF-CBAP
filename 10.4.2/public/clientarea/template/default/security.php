{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/security.css">
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
          <div class="main-card">
            <div class="main-card-title">{{lang.security_title}}</div>
            <el-tabs v-model="activeName" @tab-click="handleClick">
              <el-tab-pane label="API" name="1" v-if="isShowAPI">
                <div class="content-table">
                  <div class="content_searchbar">
                    <div class="left-btn" @click="showCreateApi">
                      {{lang.security_btn1}}
                    </div>
                    <div class="searchbar com-search">

                    </div>
                  </div>
                  <div class="tabledata">
                    <el-table v-loading="loading" :data="dataList" style="width: 100%;">
                      <el-table-column prop="name" :label="lang.security_label1" width="400"
                        :show-overflow-tooltip="true" align="left">
                      </el-table-column>
                      <el-table-column prop="id" label="ID" width="100" align="left">
                      </el-table-column>
                      <el-table-column prop="create_time" :label="lang.account_label10" min-width="200" align="left">
                        <template slot-scope="scope">
                          <span>{{scope.row.create_time | formateTime}}</span>
                        </template>
                      </el-table-column>
                      <el-table-column prop="ip" :label="lang.security_label2" width="150" align="left">
                        <template slot-scope="scope">
                          <!-- 已开启白名单 -->
                          <div class="open-show">
                            <!-- 未开启白名单 -->
                            <div class="un-open" v-if="scope.row.status == 0">{{lang.security_text2}}</div>
                            <div class="open" v-else>{{lang.security_text1}}</div>
                            <span class="setting" @click="showWhiteIp(scope.row)">{{lang.security_btn3}}</span>
                          </div>
                        </template>
                      </el-table-column>
                      <el-table-column prop="type" :label="lang.security_label3" width="100" align="left">
                        <template slot-scope="scope">
                          <el-popover placement="top-start" trigger="hover">
                            <div class="operation">
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
                  </div>
                </div>
              </el-tab-pane>
              {foreach $addons as $addon}
              {if ($addon.name=='IdcsmartSshKey')}
              <el-tab-pane :label="lang.security_tab1" name="2"></el-tab-pane>
              {/if}
              {/foreach}
              <el-tab-pane :label="lang.security_tab2" name="3" v-if="isShowAPILog"></el-tab-pane>
              {foreach $addons as $addon}
              {if ($addon.name=='IdcsmartCloud')}
              <el-tab-pane :label="lang.security_group" name="4"></el-tab-pane>
              {/if}
              {/foreach}
            </el-tabs>

            <!-- 创建API弹窗 -->
            <div class="create-api-dialog">
              <el-dialog width="6.8rem" :visible.sync="isShowCj" :show-close=false @close="cjClose">
                <div class="dialog-title">
                  {{lang.security_btn1}}
                </div>
                <div class="dialog-main">
                  <div class="label">{{lang.security_label1}}</div>
                  <el-input v-model="apiName"></el-input>
                  <el-alert class="alert-text" :title="errText" v-show="errText" type="error" show-icon
                    :closable="false">
                  </el-alert>
                </div>
                <div class="dialog-footer">
                  <div class="btn-ok" @click="cjSub">{{lang.security_btn5}}</div>
                  <div class="btn-no" @click="cjClose">{{lang.security_btn6}}</div>
                </div>
              </el-dialog>
            </div>

            <!-- 创建API成功弹窗 -->
            <div class="create-api-dialog">
              <el-dialog width="6.8rem" :visible.sync="isShowCj2" :show-close=false @close="cj2Close">
                <div class="dialog-title">
                  {{lang.security_created_api}}
                </div>
                <div class="dialog-main">
                  <div class="content-msg">
                    <div class="msg-item">
                      <div class="item-label">{{lang.security_label1}}:</div>
                      <div class="item-vlaue">{{apiData.name}}</div>
                    </div>
                    <div class="msg-item">
                      <div class="item-label">ID:</div>
                      <div class="item-vlaue">{{apiData.id}}</div>
                    </div>
                    <div class="msg-item">
                      <div class="item-label">Token:</div>
                      <div class="item-vlaue">{{apiData.token}}</div>
                    </div>
                    <div class="msg-item">
                      <div class="item-label">{{lang.security_label9}}:</div>
                      <div class="item-vlaue">{{apiData.private_key}}</div>
                      <span class="copy" @click="copyToken(apiData)">{{lang.security_btn11}}</span>
                    </div>
                    <div class="msg-item">
                      <div class="item-label">{{lang.security_label4}}:</div>
                      <div class="item-vlaue">{{apiData.create_time | formateTime}}</div>
                    </div>
                  </div>
                  <el-checkbox v-model="checked">
                    <span>
                      {{lang.security_tips}}
                      <span class="yellow">{{lang.security_tips2}}</span>
                    </span>

                  </el-checkbox>
                  <el-alert class="alert-text" :title="errText" v-show="errText" type="error" show-icon
                    :closable="false">
                  </el-alert>
                </div>
                <div class="dialog-footer">
                  <div class="btn-ok" @click="cj2Sub">{{lang.security_btn8}}</div>
                </div>
              </el-dialog>
            </div>

            <!-- 删除弹窗 -->
            <div class="delete-dialog">
              <el-dialog width="6.8rem" :visible.sync="isShowDel" :show-close=false @close="delClose">
                <div class="del-dialog-title">
                  <i class="el-icon-warning-outline del-icon"></i>{{lang.security_title3}}?
                </div>
                <div class="del-dialog-main">
                  {{lang.security_title3}}:{{delName}}
                </div>
                <div class="del-dialog-footer">
                  <div class="btn-ok" @click="delSub">{{lang.security_btn9}}</div>
                  <div class="btn-no" @click="delClose">{{lang.security_btn6}}</div>
                </div>
              </el-dialog>
            </div>

            <!-- ip白名单设置弹窗 -->
            <div class="white-ip-dialog">
              <el-dialog width="6.8rem" :visible.sync="isShowWhiteIp" :show-close=false @close="whiteIpClose"
                :destroy-on-close=true>
                <div class="dialog-title">
                  {{lang.security_title4}}
                </div>
                <div class="dialog-main">
                  <el-alert class="info-alert" :title="lang.security_tips3" type="info">
                  </el-alert>
                  <div class="ip-status">
                    <div class="ip-status-text">{{lang.security_label5}}</div>
                    <el-switch v-model="whiteIpData.status" active-color="#0058FF" inactive-color="#8692B0"
                      active-value="1" inactive-value="0" :active-text="lang.security_text3"
                      :inactive-text="lang.security_text3">
                    </el-switch>
                  </div>
                  <div class="status-remind">
                    {{lang.security_tips4}}
                  </div>
                  <div v-show="whiteIpData.status == '1'">
                    <div class="label">{{lang.security_label6}}</div>
                    <el-input type="textarea" :rows="3"
                      :placeholder="lang.security_tips5 + `&#10;1.1.1.1&#10;1.1.1.1-2.2.2.2`" v-model="whiteIpData.ip">
                    </el-input>
                  </div>

                  <el-alert class="alert-text" :title="errText" v-show="errText" type="error" show-icon
                    :closable="false">
                  </el-alert>
                </div>
                <div class="dialog-footer">
                  <div class="btn-ok" @click="whiteIpSub">{{lang.security_btn5}}</div>
                  <div class="btn-no" @click="whiteIpClose">{{lang.security_btn6}}</div>
                </div>
              </el-dialog>
            </div>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/security.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/security.js"></script>
  {include file="footer"}
