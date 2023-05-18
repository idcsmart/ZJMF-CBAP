{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/businessDetail.css">
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
          <div class="businessDetail-centent">
            <header>
              <img src="/{$template_catalog}/template/{$themes}/img/invoice/back_5684.png" alt="" @click="goBack">
              应用详情
            </header>
            <main>
              <div class="main-info">
                <p>基本信息</p>
                <div class="info-box">
                  <div class="info-item">
                    <p>授权码</p>
                    <p>{{ authorizeInfo.license}}</p>
                  </div>
                  <div class="info-item">
                    <p>关联域名</p>
                    <p>{{ authorizeInfo.domain}}</p>
                  </div>
                  <div class="info-item">
                    <p>IP地址</p>
                    <p>{{ authorizeInfo.ip || '--'}}</p>
                  </div>
                </div>
              </div>
              <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="应用列表" name="1" :key="1">
                  <div class="table-box">
                    <el-table v-loading="loading1" :data="appTable" style="width: 100%;margin-bottom: .2rem;" :key="1">
                      <el-table-column prop="id" label="ID" width="100" :show-overflow-tooltip="true" align="left">
                      </el-table-column>
                      <el-table-column prop="name" label="应用名称" :show-overflow-tooltip="true" align="left">
                      </el-table-column>
                      <el-table-column prop="id" label="应用类别" :show-overflow-tooltip="true" align="left">
                        <template slot-scope="{row}">
                          {{ typeList.find(item =>item.value == row.type).label }}
                        </template>
                      </el-table-column>
                      <el-table-column label="购买时间" :show-overflow-tooltip="true" align="left">
                        <template slot-scope="{row}">
                          {{ row.create_time | formateTime}}
                        </template>
                      </el-table-column>
                      <el-table-column label="到期时间" :show-overflow-tooltip="true" align="left">
                        <template slot-scope="{row}">
                          {{ row.due_time | formateTime}}
                        </template>
                      </el-table-column>
                      <el-table-column prop="str" label="购买金额" :show-overflow-tooltip="true" align="left">
                      </el-table-column>
                      <el-table-column label="操作" width="100" :show-overflow-tooltip="true" align="left">
                        <template slot-scope="{row}">
                          <div class="caozuo">
                            <el-popover placement="top-start" trigger="hover">
                              <div class="operation-box">
                                <div slot="reference" v-if="row.status !== 'Unpaid' " class="operation-item" @click="download(row.product_id)">下载安装包</div>
                                <div slot="reference" class="operation-item" @click="handelPay(row)" v-if="row.status == 'Unpaid' ">去支付</div>
                                <div class="operation-item" @click="goShop_client(row)" v-if="row.pay_type !== 'onetime' && row.status !=='Unpaid'">续费</div>
                              </div>
                              <i slot="reference" style="color: #0058FF" class="el-icon-more"></i>
                            </el-popover>
                          </div>
                        </template>
                      </el-table-column>
                    </el-table>
                    <pagination :page-data="params1" @sizechange="sizeChange1" @currentchange="currentChange1">
                    </pagination>
                  </div>
                </el-tab-pane>
                <el-tab-pane label="服务列表" name="2" :key="2">
                  <div class="table-box">
                    <el-table v-loading="loading2" :data="serveTable" style="width: 100%;margin-bottom: .2rem;" :key="2">
                      <el-table-column prop="id" label="ID" width="100" :show-overflow-tooltip="true" align="left">
                      </el-table-column>
                      <el-table-column prop="name" label="应用名称" :show-overflow-tooltip="true" align="left">
                      </el-table-column>
                      <el-table-column label="购买时间" :show-overflow-tooltip="true" align="left">
                        <template slot-scope="{row}">
                          {{ row.create_time | formateTime}}
                        </template>
                      </el-table-column>
                      <el-table-column label="到期时间" :show-overflow-tooltip="true" align="left">
                        <template slot-scope="{row}">
                          {{ row.due_time | formateTime}}
                        </template>
                      </el-table-column>
                      <el-table-column prop="str" label="购买金额" :show-overflow-tooltip="true" align="left">
                      </el-table-column>
                      <el-table-column label="是否已使用" :show-overflow-tooltip="true" align="left">
                        <template slot-scope="{row}">
                          {{row.used === 1 ? '是' : row.used === 0 ? '否' : '--'}}
                        </template>
                      </el-table-column>
                      <el-table-column label="操作" width="100" :show-overflow-tooltip="true" align="left">
                        <template slot-scope="{row}">
                          <div class="caozuo">
                            <el-popover placement="top-start" trigger="hover">
                              <div class="operation-box">
                                <div slot="reference" @click="goShop(row)" v-if="row.pay_type === 'onetime' && row.status !=='Unpaid'" class="operation-item">再次购买</div>
                                <div slot="reference" @click="goPay(row)" v-if="row.status ==='Unpaid'" class="operation-item">去支付</div>
                                <div class="operation-item" @click="goShopDetail(row)" v-if="row.pay_type !== 'onetime' && row.status !=='Unpaid'" class="operation-item">续费</div>
                              </div>
                              <i slot="reference" style="color: #0058FF" class="el-icon-more"></i>
                            </el-popover>
                          </div>
                        </template>
                      </el-table-column>
                    </el-table>
                    <pagination :page-data="params2" @sizechange="sizeChange2" @currentchange="currentChange2">
                    </pagination>
                  </div>
                </el-tab-pane>
                <el-tab-pane label="文件下载" name="3" :key="3">
                  <p class="file-title">DCIM文件下载</p>
                  <div class="file-box">
                    <div class="table-box">
                      <el-table v-loading="loading3" :data="fileTable" style="width: 100%;margin-bottom: .2rem;" :key="3">
                        <el-table-column prop="name" :label="lang.file_name" :show-overflow-tooltip="true">
                        </el-table-column>
                        <el-table-column prop="create_time" :label="lang.file_time" width="200">
                          <template slot-scope="{row}">
                            {{row.create_time | formateTime}}
                          </template>
                        </el-table-column>
                        <el-table-column prop="filetype" :label="lang.file_type" width="120">
                        </el-table-column>
                        <el-table-column prop="filesize" :label="lang.file_size" width="120">
                          <template slot-scope="{row}">
                            {{row.filesize | formateByte}}
                          </template>
                        </el-table-column>
                        <el-table-column prop="opt" :label="lang.file_opt" width="100">
                          <template slot-scope="{row}">
                            <i class="el-icon-download" @click="downFile(row)"></i>
                          </template>
                        </el-table-column>
                      </el-table>
                      <pagination :page-data="params3" @sizechange="sizeChange3" @currentchange="currentChange3">
                      </pagination>
                    </div>
                    <div class="file-right">
                      <div class="com-r-box">
                      <div class="tit">{{lang.file_folder}}</div>
                      <p class="total" @click="getAllFiles" :class="{active:curId == ''}">
                        <span>{{lang.file_all}}</span>
                        <span class="tag info">{{fileCount}}</span>
                      </p>
                      <div class="file_folder">
                        <p class="item" v-for="(item,index) in AllFile" :key="item.id" :class="{active:curId === item.id}" @click="changeFolder(item)">
                          {{item.name}}
                          <span class="tag" :class="{ suc: index % 4 === 0 ,war: index % 4 === 1,error: index  % 4 === 2, def: index % 4 === 3}">
                            {{item.file_num ? item.file_num : 0}}
                          </span>
                        </p>
                      </div>
                    </div>
                  </div>
                </el-tab-pane>
              </el-tabs>
              <!-- 支付弹窗 -->
              <pay-dialog ref="payDialog" @payok="paySuccess" @paycancel="payCancel"></pay-dialog>
            </main>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/js/businessDetail.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/market.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  {include file="footer"}