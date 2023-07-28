<!-- 页面独有样式 -->
<link rel="stylesheet" href="/plugins/addon/idcsmart_file_download/template/clientarea/css/file_download.css">
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
  <div class="file_download" v-cloak>
    <el-container>
      <aside-menu></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <el-main>
          <!-- 自己的东西 -->
          <div class="main-card">
            <div class="main-card-title">{{lang.file_download}}</div>
            <!-- pc端 -->
            <div class="box pc">
              <div class="com-r-box">
                <div class="tit">{{lang.file_folder}}</div>
                <p class="total">
                  <span>{{lang.file_all}}</span>
                  <span class="tag info">{{folderNum}}</span>
                </p>
                <div class="file_folder">
                  <p class="item" v-for="(item,index) in folder" :key="item.id" :class="{active:curId === item.id}" @click="changeFolder(item)">
                    {{item.name}}
                    <span class="tag" :class="{
                      suc: index % 4 === 0 ,war: index % 4 === 1,error: index  % 4 === 2, def: index % 4 === 3
                      }">
                      {{item.file_num ? item.file_num : 0}}
                    </span>
                  </p>
                </div>
              </div>
              <div class="com-l-box">
                <div class="top-search">
                  <p class="tit">{{curTit}}</p>
                  <div class="searchbar com-search">
                    <el-input v-model="params.keywords" style="width: 3.2rem;margin-left: .2rem;" :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange" clearable @clear="getData">
                      <i class="el-icon-search input-search" slot="suffix" @Click="inputChange"></i>
                    </el-input>
                  </div>
                </div>
                <!-- table -->
                <el-table :data="tableData" style="width: 100%" v-loading="loading">
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
                <pagination v-if="params.total" :page-data="params" @sizechange="sizeChange" @currentchange="currentChange">
                </pagination>
              </div>
            </div>
            <!-- 移动端 -->
            <!-- <div class="box mobile">
              <div class="com-r-box">移动端</div>
              <div class="com-l-box">
                在这里写 {{commonData.currency_suffix}}
                <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange">
                </pagination>
              </div>
            </div> -->
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/plugins/addon/idcsmart_file_download/template/clientarea/api/common.js"></script>
  <script src="/plugins/addon/idcsmart_file_download/template/clientarea/utils/util.js"></script>
  <script src="/plugins/addon/idcsmart_file_download/template/clientarea/api/file_download.js"></script>
  <script src="/plugins/addon/idcsmart_file_download/template/clientarea/js/file_download.js"></script>
  <script src="/plugins/addon/idcsmart_file_download/template/clientarea/components/pagination/pagination.js"></script>