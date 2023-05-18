<!-- 页面独有样式 -->
<link rel="stylesheet" href="/plugins/addon/idcsmart_help/template/clientarea/css/helpTotal.css">
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
  <div class="template help-total">
    <el-container>
      <aside-menu></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <div class="top-back"></div>
        <el-main>
          <!-- 自己的东西 -->
          <div class="main-card">
            <div class="main-card-title">{{lang.source_title}}</div>

            <el-input v-model="params.keywords" class="search-input" :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange" clearable>
              <i class="el-icon-search input-search" slot="suffix" @Click="inputChange"></i>
            </el-input>

            <img class="back-img" src="/plugins/addon/idcsmart_help/template/clientarea/img/source/source_back.png">

            <el-tabs v-model="activeIndex" @tab-click="handleClick">
              {foreach $addons as $addon}
              {if $addon['name']=='IdcsmartHelp'}
              <el-tab-pane ref="help" id="{$addon.id}" :label="lang.source_tab1" name="1">
                <div class="main-card-top">
                  <ul class="top-menu">
                    <li class="top-menu-item" @click="toHelpIndex">{{lang.source_title1}}</li>
                    <li class="top-menu-item top-menu-item-active">{{lang.source_title2}}</li>
                  </ul>
                  <div class="content_searchbar balance-searchbar">

                  </div>
                </div>
                <!-- 主体部分 -->
                <div class="main-card-content">
                  <div class="content-left">
                    <div class="content-left-text">
                      {{lang.source_title3}}
                    </div>
                    <div class="content-left-menu">
                      <el-menu @open="handleOpen" @close="handleClose" :default-active="activeId">
                        <el-submenu :index="menu.id.toString()" v-for="menu in helpList" :key="menu.id">
                          <template slot="title">{{menu.name}}</template>
                          <el-menu-item :title="item.title" v-for="item in menu.helps" :key="item.id" :index="item.id.toString()" @click="itemClick(item.id)">{{item.title}}</el-menu-item>
                        </el-submenu>
                      </el-menu>
                    </div>
                  </div>
                  <div class="contnet-right-out" v-loading="contentLoading">
                    <div class="content-right" v-if="detailData.id">
                      <!-- 标题 -->
                      <div class="right-title">
                        {{detailData.title}}
                      </div>
                      <!-- 更新时间 -->
                      <div class="right-keywords-time">
                        <div class="right-time">
                          {{lang.source_text1}}：{{detailData.create_time | formateTime}}
                        </div>
                        <div class="right-keywords">
                          {{lang.source_text2}}：{{detailData.keywords}}
                        </div>
                      </div>

                      <!-- 主体内容 -->
                      <div class="right-content" v-html="calStr(detailData.content)">
                      </div>
                      <!-- 附件 -->
                      <div class="right-attachment" v-if="detailData.attachment.length > 0">
                        {{lang.source_text3}}：
                        <div class="right-attachment-item" v-for="(f,i) in detailData.attachment" :key="i" @click="downloadfile(f)">
                          <span :title="f.split('^')[1]">
                            <i class="el-icon-tickets"></i><span>{{f.split('^')[1]}}</span>
                          </span>
                        </div>
                      </div>
                    </div>
                    <div class="page" v-if="JSON.stringify(detailData) !== '{}' ">
                      <div class="pre">
                        <div v-if="JSON.stringify(detailData.prev) !== '{}'" @click="itemClick(detailData.prev.id)" :class="preId == detailData.prev.id?'blue':''">
                          <i class="el-icon-arrow-left"></i>
                          <span>{{lang.source_text4}}{{detailData.prev.title}}</span>
                        </div>
                      </div>
                      <div class="next">
                        <div v-if="JSON.stringify(detailData.next) !== '{}'" @click="itemClick(detailData.next.id)" :class="preId == detailData.next.id?'blue':''">
                          <span>{{lang.source_text5}}{{detailData.next.title}}</span>
                          <i class="el-icon-arrow-right"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </el-tab-pane>
              {elseif $addon['name']=='IdcsmartNews' /}
              <el-tab-pane ref="news" id="{$addon.id}" :label="lang.source_tab2" name="2"></el-tab-pane>
              {elseif $addon['name']=='IdcsmartFileDownload' /}
              <el-tab-pane ref="download" id="{$addon.id}" :label="lang.source_tab3" name="3"></el-tab-pane>
              {/if}
              {/foreach}
            </el-tabs>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/plugins/addon/idcsmart_help/template/clientarea/api/common.js"></script>
  <script src="/plugins/addon/idcsmart_help/template/clientarea/api/help.js"></script>
  <script src="/plugins/addon/idcsmart_help/template/clientarea/js/helpTotal.js"></script>
  <script src="/plugins/addon/idcsmart_help/template/clientarea/utils/util.js"></script>