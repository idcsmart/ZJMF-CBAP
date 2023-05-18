{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common_product_list.css">
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
  <div class="template common_product_lists">
    <el-container>
      <aside-menu></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <el-main>
          <!-- 自己的东西 -->
          <div class="main-card">
            <div class="main-card-title">
              <div class="main-card-title">
                <span class="title-text">{{lang.cloud_title}}</span>
                <!-- <div class="add-btn" @click="toOrder">
                  <i class="el-icon-plus"></i>
                  添加
                </div> -->
              </div>
            </div>
            <div class="main-card-table">
              <!-- 筛选 -->
              <div class="main-card-search">
                <!-- 产品状态 -->
                <el-select v-model="params.status" style="width:3.2rem;margin-right: .2rem;" clearable @change="getList">
                  <el-option v-for="item in statusSelect" :key="item.id" :value="item.status" :label="item.label">
                  </el-option>
                </el-select>
                <!-- <el-input suffix-icon="el-input__icon el-icon-search" @input="inputChange" v-model="params.keywords" style="width: 3.2rem;margin-left: .2rem;" :placeholder="lang.cloud_tip_2"></el-input> -->
                <el-input v-model="params.keywords" style="width: 3.2rem;margin-right: .2rem;" :placeholder="lang.cloud_tip_2" clearable @clear="getList">
                </el-input>
                <div class="search-btn" @Click="inputChange">查询</div>
              </div>
              <div class="table">
                <!-- @row-click="(row)=>toDetail(row.id)" -->
                <el-table v-loading="loading" :data="commonList" style="width: 100%;margin-bottom: .2rem;" >
                  <el-table-column prop="id" label="ID" width="100" align="left">
                    <template slot-scope="scope">
                      <span class="column-id" @click="toDetail(scope.row)">{{scope.row.id}}</span>
                    </template>
                  </el-table-column>
                  <el-table-column prop="name" label="产品名称" min-width="180" :show-overflow-tooltip="true">
                    <template slot-scope="scope">
                      <div class="cloud-name" @click="toDetail(scope.row)">
                        <span class="packge-name">{{ scope.row.package_name }}</span>
                        <span class="name">{{ scope.row.name }}</span>
                      </div>
                    </template>
                  </el-table-column>
                  <el-table-column prop="ip" label="金额/周期" width="200" :show-overflow-tooltip="true">
                    <template slot-scope="{row}">
                      {{commonData.currency_prefix}}{{row.first_payment_amount}}/{{row.billing_cycle}}
                    </template>
                  </el-table-column>
                  <el-table-column prop="due_time" label="订购时间" width="180">
                    <template slot-scope="scope">
                      {{scope.row.active_time | formateTime}}
                    </template>
                  </el-table-column>
                  <el-table-column prop="due_time" :label="lang.cloud_table_head_4" width="180">
                    <template slot-scope="scope">
                      {{scope.row.due_time | formateTime}}
                    </template>
                  </el-table-column>
                  <el-table-column prop="id" label="状态" width="120" align="left">
                    <template slot-scope="scope">
                      <div class="status" :style="'color:'+status[scope.row.status].color + ';background:' + status[scope.row.status].bgColor">
                        {{status[scope.row.status].text }}
                      </div>
                    </template>
                  </el-table-column>
                </el-table>
              </div>
              <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange">
              </pagination>
            </div>

          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/common_product.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/common_product_list.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  {include file="footer"}