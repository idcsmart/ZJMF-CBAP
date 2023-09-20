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
      <aside-menu @getruleslist="getRule"></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <el-main>
          <!-- 自己的东西 -->
          <div class="main-card">
            <div class="main-title">{{lang.new_goods}}</div>
            <div class="main-content-box">
              <div class="search-box">
                <el-select v-model="select_first_obj.id" :placeholder="lang.first_level" @change="selectFirstType">
                  <el-option v-for="item in first_group_list" :key="item.id " :label="item.name" :value="item.id">
                  </el-option>
                </el-select>
                <el-select v-model="select_second_obj.id" :placeholder="lang.second_level" :disabled="secondLoading" :loading="secondLoading" @change="selectSecondType" class="second-select">
                  <el-option v-for="item in second_group_list" :key="item.name " :label="item.name" :value="item.id">
                  </el-option>
                </el-select>
                <el-input :placeholder="lang.search_placeholder" v-if="!isDomain" clearable v-model="searchValue" class="search-input" @keyup.enter.native="searchGoods"></el-input>
                <el-button class="search-btn" type="primary" key="ddd" @click="searchGoods" :loading="searchLoading" v-if="!isDomain">{{lang.search}}</el-button>
              </div>
              <div class="shopping-box" v-loading="goodSLoading">
                <template v-if="!isDomain">
                  <div class="no-goods" v-if="goodsList.length === 0 && !goodSLoading">
                    <el-empty :description="lang.no_goods"></el-empty>
                  </div>
                  <div v-else class="goods-list-div">
                    <div v-for="item in goodsList" :key="item.id" class="shopping-item">
                      <div class="goods-name">{{ item.name }}</div>
                      <div v-html="item.description" class="goods-description"></div>
                      <div class="btn-box">
                        <span class="item-price">{{commonData.currency_prefix}} {{item.price}}{{item.cycle ? '/' + item.cycle : ''}}</span>
                        <el-button type="primary" :key="item.id + 'aaa'" @click="goOrder(item)">{{lang.buy}}</el-button>
                      </div>
                    </div>
                  </div>
                </template>
                <template v-else>
                  <div class="domain-box">
                    <div class="register-type">
                      <span class="reg-ridio" :class="regType === '1' ? 'isActice' : ''" @click="regType = '1'">{{lang.template_text93}}</span>
                      <el-divider direction="vertical"></el-divider>
                      <span class="reg-ridio" :class="regType === '2' ? 'isActice' : ''" @click="regType = '2'">{{lang.template_text94}}</span>
                    </div>
                    <div class="domain-content">
                      <div class="domain-left">
                        <template v-if="regType === '1'">
                          <div class="domain-search">
                            <el-input :placeholder="lang.template_text92" v-model="domainInput" clearable @keyup.enter.native="handelDomainSearch">
                              <div class="suffix-box" slot="append" @click="isShowSuffixBox = !isShowSuffixBox">
                                {{selectSuffix}}
                                <i class="el-icon-arrow-down select-btn"></i>
                              </div>
                            </el-input>
                            <el-button class="search-button" @click="handelDomainSearch" :loading="isSearching">{{lang.template_text95}}</el-button>
                            <div class="suffix-list" v-show="isShowSuffixBox">
                              <div class="suffix-item" @click="handelSelectSuffix(item.suffix)" :class="selectSuffix === item.suffix ? 'suffix-active' : ''" v-for="item in suffixList" :key="item.suffix">{{item.suffix}}</div>
                            </div>
                          </div>
                          <div class="domain-one">
                            <div v-if="domainList.length !==0" v-loading="isSearching">
                              <div class="search-title">{{lang.template_text96}}</div>
                              <div class="domain-list">
                                <div class="domain-item" v-for="(item,index) in domainList" :key="index">
                                  <div class="item-left">
                                    <span class="domain-name">{{item.name}}</span>
                                    <span class="domain-status" v-if="item.avail === 0">{{lang.template_text97}}</span>
                                    <span class="domain-status" v-if="(item.avail === 1 || item.avail === -2) && item.description">{{item.description}}</span>
                                  </div>
                                  <div class="item-right">
                                    <div class="premium-type" v-if="item.type && item.type === 'premium'">{{lang.template_text98}}</div>
                                    <el-popover placement="bottom" trigger="hover">
                                      <div class="pirce-box" v-if="item.avail === 1" slot="reference" v-loading="item.priceLoading">
                                        <span class="now-price"><span style="font-size: 0.16rem; margin-right: 0.02rem;">{{item.showPrice}} </span> {{commonData.currency_suffix}}/{{lang.common_cloud_text112}}</span>
                                        <i class="el-icon-arrow-down"></i>
                                      </div>
                                      <div class="price-list">
                                        <div class="price-item">
                                          <div class="price-year"></div>
                                          <div class="price-new">{{lang.template_text99}}</div>
                                          <div class="price-renew">{{lang.template_text100}}</div>
                                        </div>
                                        <div class="price-item" v-for="items in item.priceArr" :key="items.buyyear">
                                          <div class="price-year">{{items.buyyear}}{{lang.template_text101}}</div>
                                          <div class="price-new">{{items.buyprice}} {{commonData.currency_suffix}}</div>
                                          <div class="price-renew">{{items.renewprice}} {{commonData.currency_suffix}}</div>
                                        </div>
                                      </div>
                                    </el-popover>
                                    <el-button :class="isAddCart(item) ? 'dis-add-btn' : 'add-btn'" v-if="item.avail === 1 " @click="addCart(item)">{{lang.template_text102}}</el-button>
                                    <div class="whois-box" v-if="item.avail === 0" @click="goWhois(item)">{{lang.template_text103}}</div>
                                    <div v-if="item.avail === -1">{{lang.template_text104}}</div>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="start-search" v-else v-loading="isSearching">
                              <img src="/{$template_catalog}/template/{$themes}/img/goodsList/search_domain.png" alt="">
                              <p>{{lang.template_text105}}</p>
                            </div>
                          </div>
                        </template>
                        <template v-else>
                          <div class="batch-box">
                            <div class="batch-tips" v-loading="batchLoading">
                              <el-input v-model="textarea2" resize="none" class="input-batch" type="textarea" :placeholder="`${lang.template_text106}\n${lang.template_text107}${domainConfig.number_limit}${lang.template_text108}${domainConfig.number_limit}${lang.template_text109}\n${lang.template_text110}\n${lang.template_text111}`">
                              </el-input>
                              <div class="upload-btn" @click="isShowUpload = true">
                                <img src="/{$template_catalog}/template/{$themes}/img/goodsList/upload.png" alt="">
                              </div>
                            </div>
                            <div class="batch-btn">
                              <el-button @click="batchSearchDomain" :loading="batchLoading">{{lang.template_text112}}</el-button>
                            </div>
                            <div class="batch-main">
                              <template v-if="availList.length !== 0 || unavailList.length !==0 || faillList.length !== 0">
                                <div class="search-title">{{lang.template_text113}}({{availList.length}})</div>
                                <div class="avail-list" v-loading="batchLoading">
                                  <!-- 可注册域名 -->
                                  <el-checkbox-group v-model="batchCheckGroup" @change="handleBatchChange">
                                    <div class="batch-item" v-for="(item,index) in availList" :key="index">
                                      <div class="item-left">
                                        <el-checkbox :label="item.name">
                                          <span class="domain-name">{{item.name}}</span>
                                        </el-checkbox>
                                        <span class="domain-status" v-if="item.avail === 0">{{lang.template_text114}}</span>
                                        <span class="domain-status" v-if="(item.avail === 1 || item.avail === -2) && item.description">{{item.description}}</span>
                                      </div>
                                      <div class="item-right">
                                        <div class="premium-type" v-if="item.type && item.type === 'premium'">{{lang.template_text115}}</div>
                                        <el-popover placement="bottom" trigger="hover">
                                          <div class="pirce-box" v-if="item.avail === 1" slot="reference" v-loading="item.priceLoading">
                                            <span class="now-price"><span style="font-size: 0.16rem; margin-right: 0.02rem;">{{item.showPrice}} </span> {{commonData.currency_suffix}}/{{lang.template_text101}}</span>
                                            <i class="el-icon-arrow-down"></i>
                                          </div>
                                          <div class="price-list">
                                            <div class="price-item">
                                              <div class="price-year"></div>
                                              <div class="price-new">{{lang.template_text99}}</div>
                                              <div class="price-renew">{{lang.template_text100}}</div>
                                            </div>
                                            <div class="price-item" v-for="items in item.priceArr" :key="items.buyyear">
                                              <div class="price-year">{{items.buyyear}}{{lang.template_text101}}</div>
                                              <div class="price-new">{{items.buyprice}} {{commonData.currency_suffix}}</div>
                                              <div class="price-renew">{{items.renewprice}} {{commonData.currency_suffix}}</div>
                                            </div>
                                          </div>
                                        </el-popover>
                                        <el-button :class="isAddCart(item) ? 'dis-add-btn' : 'add-btn'" v-if="item.avail === 1 " @click="addCart(item)">{{lang.template_text102}}</el-button>
                                      </div>
                                    </div>
                                  </el-checkbox-group>
                                </div>
                                <div class="all-check" v-if="availList.length > 0">
                                  <el-checkbox :indeterminate="isBatchIndeterminate" v-model="isBatchAllCheck" @change="handleBatchCheckAllChange">{{lang.template_text116}}</el-checkbox>
                                  <el-button @click="addAllCart" :loading="addAllLoading">{{lang.template_text117}}</el-button>
                                </div>
                                <el-collapse v-model="activeNames" v-loading="batchLoading">
                                  <el-collapse-item name="1" style="margin-top: 0.6rem;" v-show="unavailList.length > 0">
                                    <template slot="title">
                                      <div class="unavail-title">
                                        <span>{{lang.template_text118}}({{unavailList.length}})</span>
                                        <span class="open-text" v-if="activeNames.includes('1')">{{lang.template_text119}}</span>
                                        <span class="open-text" v-else>{{lang.template_text120}}</span>
                                      </div>
                                    </template>
                                    <div class="unavail-list">
                                      <div class="unavail-item" v-for="(item,index) in unavailList" :key="index">
                                        <span class="unavail-name">{{item.name}}</span>
                                        <span class="unavail-reason">{{item.reason}}</span>
                                      </div>
                                    </div>
                                  </el-collapse-item>
                                  <el-collapse-item name="2" style="margin-top: 0.4rem;" v-show="faillList.length > 0">
                                    <template slot="title">
                                      <div class="unavail-title">
                                        <span>{{lang.template_text121}}({{faillList.length}})</span>
                                        <span class="open-text" v-if="activeNames.includes('2')">{{lang.template_text119}}</span>
                                        <span class="open-text" v-else>{{lang.template_text120}}</span>
                                      </div>
                                    </template>
                                    <div class="unavail-list">
                                      <div class="unavail-item" v-for="(item,index) in faillList" :key="index">
                                        <span class="unavail-name">{{item.name}}</span>
                                        <span class="unavail-reason">{{item.reason}}</span>
                                      </div>
                                    </div>
                                  </el-collapse-item>
                                </el-collapse>
                              </template>
                              <div class="batch-search" v-else v-loading="batchLoading">
                                <img src="/{$template_catalog}/template/{$themes}/img/goodsList/search_domain.png" alt="">
                                <p>{{lang.template_text122}}</p>
                              </div>
                            </div>
                          </div>
                        </template>
                      </div>
                      <div class="domain-right">
                        <div class="car-top">
                          <span>
                            <el-divider direction="vertical"></el-divider>
                            {{lang.template_text123}}
                          </span>
                          <span class="clear-car" @click="deleteClearCart()">{{lang.template_text124}}</span>
                        </div>
                        <div class="car-box" v-loading="isCarLoading">
                          <div class="car-no" v-if="carList.length === 0">
                            {{lang.template_text125}}
                            <span v-show="carList.length === 0">{{lang.template_text126}}</span>
                            <span v-if="!isLogin" class="blue-a-text" @click="goLogin"> {{lang.template_text127}}</span>
                          </div>
                          <div class="car-list" v-else>
                            <el-checkbox-group v-model="checkList" @change="handleCheckedCitiesChange">
                              <div class="car-item" v-for="(item,index) in carList" :key="index">
                                <div class="caritem-top">
                                  <div class="car-name">
                                    <el-checkbox :label="item.positions">
                                      <span class="shop-name">{{item.config_options.domain}}</span>
                                    </el-checkbox>
                                  </div>
                                  <div class="car-del" @click="deleteCart(item)">{{lang.template_text128}}</div>
                                </div>
                                <div class="car-bottom">
                                  <div class="car-year">
                                    <el-select v-model="item.selectYear" @change="(val)=>changeCart(val,item)">
                                      <el-option v-for="items in item.priceArr" :key="items.buyyear" :label="items.buyyear + lang.template_text101" :value="items.buyyear">
                                      </el-option>
                                    </el-select>
                                  </div>
                                  <div v-loading="item.priceLoading" class="car-price"><span style="font-size: 0.16rem; margin-right: 0.02rem;">{{priceCalc(item)}} </span> {{commonData.currency_suffix}}</div>
                                </div>
                              </div>
                            </el-checkbox-group>
                          </div>
                        </div>
                        <div class="car-money">
                          <el-checkbox :indeterminate="isIndeterminate" v-model="isAllCheck" @change="handleCheckAllChange">{{lang.template_text129}}</el-checkbox>
                          <span class="mon-right">
                            {{lang.template_text87}}:
                            <span class="money-text"><span style="font-size: 0.24rem;">{{totalMoneyCalc}}</span> {{commonData.currency_suffix}} </span>
                          </span>
                        </div>
                        <div class="car-settle">
                          <el-button class="settle-btn" @click="goBuyDomain">{{lang.template_text130}}</el-button>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
              </div>
              <p v-if="!isDomain && !scrollDisabled && goodsList.length !==0" class="tips">{{lang.goods_loading}}</p>
              <p v-if="!isDomain && scrollDisabled && goodsList.length !== 0" class="tips">{{lang.no_more_goods}}</p>
            </div>
          </div>
        </el-main>
        <div class="up-dialog">
          <el-dialog width="6.8rem" :visible.sync="isShowUpload" :show-close=false>
            <div class="dia-title">{{lang.template_text131}}</div>
            <div class="dia-concent">
              <p class="up-tips">{{lang.template_text132}}</p>
              <div class="file-box">
                <input accept="text/plain" type="file" id="upFile" autocomplete="off" tabindex="-1" style="display: none;">
                <input class="file-name" :placeholder="lang.template_text133" readonly :value="fileName">
                <!-- 选择文件按钮 -->
                <div class="file-btn" @click="selectFile">{{lang.template_text134}}</div>
              </div>
            </div>
            <div class="dia-foter">
              <el-button class="confim-btn" @click="confirmUpload">{{lang.template_text135}}</el-button>
              <el-button class="cancel-btn" @click="cancelUpload">{{lang.template_text136}}</el-button>
            </div>
          </el-dialog>
        </div>
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