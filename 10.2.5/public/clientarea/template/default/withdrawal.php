{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/withdrawal.css">
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
            <header>
              <img src="/{$template_catalog}/template/{$themes}/img/invoice/路径 5684.png" alt="" @click="back">
              <h2>提现记录</h2>
            </header>
            <div class="withdrawal-content">
              <el-table :data="withdrawalArr">
                <el-table-column prop="withdraw_amount" label="提现金额" width="150">
                  <template slot-scope="{row}">
                    <span>￥{{row.withdraw_amount}}</span>
                  </template>
                </el-table-column>
                <el-table-column prop="create_time" label="创建时间" align="center" min-width="300">
                  <template slot-scope="{row}">
                    {{ row.create_time | formateTime}}
                  </template>
                </el-table-column>
                <el-table-column prop="state" label="状态" width="150">
                  <template slot-scope="{row}">
                  <el-tag :type="row.stateName" v-if="!row.reason">{{ row.stateText}}</el-tag>
                    <el-tooltip :content="row.reason" placement="top" v-else>
                      <el-tag :type="row.stateName">{{ row.stateText}}</el-tag>
                    </el-tooltip>
                  </template>
                </el-table-column>

              </el-table>
              <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange">
              </pagination>
            </div>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/js/withdraw.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/withdraw.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  {include file="footer"}