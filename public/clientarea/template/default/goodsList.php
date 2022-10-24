{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/goodsList.css">
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
            <div class="main-title">{{lang.new_goods}}</div>
            <!-- <div class="main-content">
              <div class="select-box">
                <el-input placeholder="请输入关键字搜索" v-model="searchValue">
                  <i slot="suffix" class="el-input__icon el-icon-search"></i>
                </el-input>
                <div class="group-box">
                  <div class="first-box">
                    <div class="first-item" @click="selectFirstType(item)" v-for="item in first_group_list" :key="item.id" :class="item.id === select_first_obj.id ? 'select-first-item' : ''">{{ item.name}}</div>
                  </div>
                  <div class="second-box" v-loading="secondLoading">
                    <div class="second-item" @click="selectSecondType(item)" v-for="item in second_group_list" :key="item.id" :class="item.id === select_second_obj.id ? 'select-second-item' : ''">{{ item.name}}</div>
                  </div>
                </div>
              </div>
              <div class="shopping-box" v-loading="goodSLoading">
                <div v-if="goodsList.length !== 0" class="goods-list-div">
                  <div v-for="item in goodsList" :key="item.id" class="shopping-item">
                    <div class="goods-name">{{ item.name }}</div>
                    <div v-html="item.description" class="goods-description"></div>
                    <div class="btn-box">
                      <el-button>购物车</el-button>
                      <el-button>购买</el-button>
                    </div>
                  </div>
                </div>
                <div class="no-goods" v-else>
                  <el-empty description="暂无商品"></el-empty>
                </div>
              </div>
            </div> -->
            <div class="main-content-box">
              <div class="search-box">
                <el-select v-model="select_first_obj.id" :placeholder="lang.first_level" @change="selectFirstType">
                  <el-option v-for="item in first_group_list" :key="item.name " :label="item.name" :value="item.id">
                  </el-option>
                </el-select>
                <el-select v-model="select_second_obj.id" :placeholder="lang.second_level" :disabled="secondLoading" :loading="secondLoading" @change="selectSecondType" class="second-select">
                  <el-option v-for="item in second_group_list" :key="item.name " :label="item.name" :value="item.id">
                  </el-option>
                </el-select>
                <el-input :placeholder="lang.search_placeholder" clearable v-model="searchValue" class="search-input"></el-input>
                <el-button class="search-btn" type="primary" key="ddd" @click="searchGoods" :loading="searchLoading">{{lang.search}}</el-button>
              </div>
              <div class="shopping-box" v-loading="goodSLoading">
                <div class="no-goods" v-if="goodsList.length === 0 && !goodSLoading">
                  <el-empty :description="lang.no_goods"></el-empty>
                </div>
                <div v-else class="goods-list-div">
                  <div v-for="item in goodsList" :key="item.id" class="shopping-item">
                    <div class="goods-name">{{ item.name }}</div>
                    <div v-html="item.description" class="goods-description"></div>
                    <div class="btn-box">
                      <el-button type="primary" :key="item.id + 'aaa'" @click="goOrder(item)">{{lang.buy}}</el-button>
                    </div>
                  </div>
                </div>
              </div>
              <p v-if="!scrollDisabled && goodsList.length !== 0" class="tips">{{lang.goods_loading}}</p>
              <p v-if="scrollDisabled && goodsList.length !== 0" class="tips">{{lang.no_more_goods}}</p>
            </div>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/goodsList.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/goodsList.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
  {include file="footer"}