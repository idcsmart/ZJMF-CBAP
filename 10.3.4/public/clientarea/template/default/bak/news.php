{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/news.css">
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
  <div class="news" v-cloak>
    <el-container>
      <aside-menu></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <el-main>
          <!-- 自己的东西 -->
          <div class="main-card">
            <div class="main-card-title">{{lang.news}}</div>
            <!-- pc端 -->
            <div class="box pc">
              <div class="com-r-box">
                <div class="tit">{{lang.news_classify}}</div>
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
                      {{item.news_num > 0 ? item.news_num : 0}}
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
                <div class="news-list" v-if="params.total" id="news-list">
                  <div class="item" v-for="item in tableData" :key="item.id" @click="goDetail(item.id)">
                    <p class="title">{{item.title}}</p>
                    <p class="info">
                      <i class="el-icon-time"></i>
                      <span>{{item.create_time | formateTime}}</span>
                    </p>
                  </div>
                </div>
                <!-- no-data -->
                <div class="no-data" v-else>
                  {{lang.no_data}}
                </div>
                <pagination v-if="params.total" :page-data="params" @sizechange="sizeChange" @currentchange="currentChange">
                </pagination>
              </div>
            </div>
            <!-- 移动端 -->
            <div class="box mobile">
              <div class="com-r-box">移动端</div>
              <div class="com-l-box">
                在这里写 {{commonData.currency_suffix}}
                <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange">
                </pagination>
              </div>

            </div>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/news.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/news.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  {include file="footer"}