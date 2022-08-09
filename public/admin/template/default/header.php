<!DOCTYPE html>
<html lang="en" theme-color="default" theme-mode>

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
  <title>tempalte</title>
  <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common/tdesign.min.css" />
  <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common/reset.css" />
  <script src="/{$template_catalog}/template/{$themes}/js/common/vue.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/tdesign.min.js"></script>
  <script>
    Vue.prototype.lang = window.lang
    const url = "/{$template_catalog}/template/{$themes}/"
  </script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/lang.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/moment.min.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/layout.js"></script>
</head>

<body>
  <!-- loading -->
  <div id="loading">
    <div class="box">
      <div></div>
      <div></div>
    </div>
  </div>
  <t-layout id="layout">
    <!-- header+menu -->
    <t-layout class="aside" id="aside" v-cloak :class="{isFold:collapsed}">
      <div class="header">
        <div class="logo">
          <img :src="logUrl" alt="logo">
        </div>
        <div class="h-left">
          <t-button theme="default" shape="square" variant="text" @click.native="changeCollapsed">
            <t-icon name="view-list"></t-icon>
          </t-button>
          <div class="global-search">
            <t-input :class="{ 'hover-active': isSearchFocus, 'h-search': true}" :placeholder="lang.please_search" @blur="changeSearchFocus(false)" @focus="changeSearchFocus(true)" @change="changeSearch">
              <template #prefix-icon>
                <t-icon name="search" size="20px"></t-icon>
              </template>
            </t-input>
            <div class="search-content" v-if="isShow">
              <t-loading attach="#con" :loading="loadingSearch" size="small"></t-loading>
              <div class="con" v-if="global" id="con">
                <div class="item" v-if="global.clients.length>0">
                  <p class="tit">{{lang.user}}</p>
                  <ul>
                    <li v-for="item in global.clients" :key="item.id">
                      <a :href="`client_detail.html?client_id=${item.id}`">
                        <p class="s-tit">{{item.username}}<span class="company" v-if="item.company">{{'(' + item.company
                            + ')'}}</span></p>
                        <p class="phone" v-if="item.phone">{{item.phone}}</p>
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="item" v-if="global.hosts.length>0">
                  <p class="tit">{{lang.tailorism}}</p>
                  <ul>
                    <li v-for="item in global.hosts" :key="item.id">
                      <a :href="`host_detail.html?client_id=${item.client_id}&id=${item.id}`">
                        <p class="s-tit">{{item.product_name}}&nbsp;#/{{item.id}}</p>
                        <p class="host-name">{{item.product_name}}</p>
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="item" v-if="global.products.length>0">
                  <p class="tit">{{lang.product}}</p>
                  <ul>
                    <li v-for="item in global.products" :key="item.id">
                      <a :href="`product_detail.html?id=${item.id}`">
                        <p class="s-tit">{{item.name}}</p>
                        <p class="host-name">{{item.product_group_name_first}}/{{item.product_group_name_second}}</p>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              <p class="no-data" v-if="noData">{{lang.tip10}}</p>
            </div>
          </div>
        </div>
        <!-- header operations -->
        <div class="operations-container">
          <t-tooltip placement="bottom" :content="lang.help_document">
            <t-button theme="default" shape="square" variant="text">
              <t-icon name="help-circle" size="20px" />
            </t-button>
          </t-tooltip>
          <t-dropdown @click="changeLang" trigger="click" :min-column-width="125">
            <t-button variant="text">
              <img :src="curSrc" alt="" class="cur-img">
            </t-button>
            <t-dropdown-menu slot="dropdown" class="lang-list" attach="html">
              <t-dropdown-item :value="item.display_lang" v-for="item in langList" :key="item.display_lang">
                <img :src="item.display_img" alt="" class="img">
                {{item.display_name}}
              </t-dropdown-item>
            </t-dropdown-menu>
          </t-dropdown>
          <t-dropdown :min-column-width="125" trigger="click" class="user-btn" size="small">
            <template #dropdown>
              <t-dropdown-item class="operations-dropdown-container-item" @click="handleLogout">
                <template>
                  <t-icon name="poweroff"></t-icon>
                  退出登录
                </template>
              </t-dropdown-item>
              </t-dropdown-menu>
            </template>
            <t-button class="header-user-btn" theme="default" variant="text">
              <template #icon>
                <t-icon name="user-circle" size="20px"></t-icon>
              </template>
              <div class="header-user-account">
                {{userName}}
                <t-icon name="chevron-down"></t-icon>
              </div>
            </t-button>
          </t-dropdown>
          <t-tooltip placement="bottom" :content="lang.system_setting">
            <t-button theme="default" shape="square" variant="text" @click="toggleSettingPanel">
              <t-icon name="setting" size="20px"></t-icon>
            </t-button>
          </t-tooltip>
        </div>
        <!-- system-setting -->
        <t-drawer :visible.sync="visible" :header="lang.system_setting" :footer="false" id="setting">
          <template Slot="closeBtn">
            <t-icon name="close"></t-icon>
          </template>
          <div class="setting-group-title">{{ lang.theme_mode }}</div>
          <t-radio-group v-model="formData.mode">
            <div v-for="(item, index) in MODE_OPTIONS" :key="index" class="setting-layout-drawer">
              <div>
                <t-radio-button :key="index" :value="item.type">
                  <img :src="item.src"></img>
                </t-radio-button>
                <p :style="{ textAlign: 'center', marginTop: '8px' }">{{ item.text }}</p>
              </div>
            </div>
          </t-radio-group>
          <div class="setting-group-title">{{ lang.theme_color }}</div>
          <t-radio-group v-model="formData.brandTheme">
            <div v-for="(item, index) in COLOR_OPTIONS.slice(0, COLOR_OPTIONS.length)" :key="index" class="setting-layout-drawer theme" :class="{no:item!==formData.brandTheme}">
              <t-radio-button :key="index" :value="item" class="setting-layout-color-group">
                <template>
                  <div :style="{background:getBrandColor(item,colorList)['@brand-color']}" class="color"></div>
                </template>
              </t-radio-button>
            </div>
          </t-radio-group>
        </t-drawer>
      </div>
      <!-- aside menu -->
      <t-menu :theme="formData.mode" :value="curValue" :collapsed="collapsed" :expanded="expanded" @expand="expanded = $event">
        <div v-for="(item,index) in navList" :key="index">
          <t-menu-item :value="item.id" v-if="!item.child">
            <template #icon>
              <t-icon :name="iconList[index]" />
            </template>
            <span @click="jumpHandler(item.url)">{{item.title}}</span>
          </t-menu-item>
          <t-submenu :value="item.id" mode="popup" v-else>
            <template #icon>
              <t-icon :name="iconList[index]" />
            </template>
            <span slot="title">{{item.title}}</span>
            <t-menu-item :value="e.id" v-for="e in item.child" :key="e.id" @click="jumpHandler(e)">
              {{e.title}}
            </t-menu-item>
            <template v-if="item.id===91">
              <li class="t-menu__item t-menu__item--plain t-submenu__item">
                <a href="#">推介计划</a>
              </li>
              <li class="t-menu__item t-menu__item--plain t-submenu__item">
                <a href="http://101.35.248.14/admin/plugin/idcsmart_ticket/ticket.html">工单</a>
              </li>
              <li class="t-menu__item t-menu__item--plain t-submenu__item">
                <a href="http://101.35.248.14/admin/plugin/idcsmart_promo_code/promo_code.html">优惠码</a>
              </li>
              <li class="t-menu__item t-menu__item--plain t-submenu__item">
                <a href="http://101.35.248.14/admin/plugin/idcsmart_refund/refund_product.html">退款</a>
              </li>
              <li class="t-menu__item t-menu__item--plain t-submenu__item">
                <a href="#">提现</a>
              </li>
              <li class="t-menu__item t-menu__item--plain t-submenu__item">
                <a href="http://101.35.248.14/admin/plugin/idcsmart_help/help.html">帮助中心</a>
              </li>
              <li class="t-menu__item t-menu__item--plain t-submenu__item">
                <a href="http://101.35.248.14/admin/plugin/idcsmart_news/news.html">新闻中心</a>
              </li>
              <li class="t-menu__item t-menu__item--plain t-submenu__item">
                <a href="http://101.35.248.14/admin/plugin/idcsmart_file_download/file_download.html">文件下载</a>
              </li>
            </template>
          </t-submenu>
        </div>
      </t-menu>
    </t-layout>
    <t-layout class="t-layout right-box">
      <div class="empty"></div>
      <t-content class="area">