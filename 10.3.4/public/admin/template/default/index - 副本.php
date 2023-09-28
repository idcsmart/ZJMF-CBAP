{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/index.css">
<!-- =======内容区域======= -->
<div id="content" class="index-page" v-cloak>
  <template v-if="showIndexPage">
    <!-- 销售额统计 -->
    <div class="top-box" v-if="showOnLoad">
      <div class="top-item" :class="parseInt(indexData.this_year_amount_percent) >= 0 ? 'increase-bg' : 'decline-bg'">
        <div class="item-nums">
          <span class="num">{{ thousandth(indexData.this_year_amount) }}</span>
          <span class="trend up-green-text" v-if="parseInt(indexData.this_year_amount_percent) >= 0">{{lang.index_text2}} ↑ {{indexData.this_year_amount_percent }}%</span>
          <span class="trend down-red-text" v-else>{{lang.index_text2}} ↓ {{parseInt(indexData.this_year_amount_percent)*-1 }}%</span>
        </div>
        <div class="item-title">{{lang.index_text1}}</div>
      </div>
      <div class="top-item" :class="parseInt(indexData.this_month_amount_percent) >= 0 ? 'increase-bg' : 'decline-bg'">
        <div class="item-nums">
          <span class="num">{{ thousandth(indexData.this_month_amount) }}</span>
          <span class="trend up-green-text" v-if="parseInt(indexData.this_month_amount_percent) >= 0">{{lang.index_text2}} ↑ {{parseInt(indexData.this_month_amount_percent) }}%</span>
          <span class="trend down-red-text" v-else>{{lang.index_text2}} ↓ {{ parseInt(indexData.this_month_amount_percent)*-1 }}%</span>
        </div>
        <div class="item-title">
          {{lang.index_text3}}
        </div>
      </div>
      <div class="top-item">
        <div class="item-nums">
          <span class="num">{{ thousandth(indexData.today_sale_amount) }}</span>
          <!-- <span class="trend up-green-text" v-if="parseInt(indexData.today_sale_amount_percent) >= 0">同比 ↑ {{indexData.this_month_amount_percent }}%</span>
        <span class="trend down-red-text" v-else>同比 ↓ {{ parseInt(indexData.today_sale_amount_percent)*-1 }}%</span> -->
        </div>
        <div class="item-title">
          {{lang.index_text_0}}
        </div>
      </div>
      <div class="top-item active-div">
        <div class="item-nums">
          <span class="num">{{indexData.active_client_count }}</span>
        </div>
        <div class="item-title">
          {{lang.index_text4}}
        </div>
        <div class="trend blue-text active-box">{{lang.index_text5}} {{indexData.active_client_percent}}%</div>
        <div class="histogram-box">
          <span></span>
          <span></span>
          <span></span>
          <span></span>
        </div>
      </div>
    </div>
    <div class="mid-box">
      <!-- 销售统计 -->
      <div id="echars-box" class="echars-div" v-if="showSale"></div>
      <!-- 大客户统计 -->
      <div class="statistics-box" v-if="showClient">
        <div class="statistics-title">{{lang.index_text6}}</div>
        <div class="statistics-list">
          <div class="customer-item" v-for="(item,index) in clients" :key="index">
            <div class="customer-ranking">
              <span class="ranking">{{index+1}}</span>
              <span class="customer-name">
                <a :href="`client_detail.htm?client_id=${item.id}`" class="aHover">{{item.username}}</a>
              </span>
            </div>
            <div class="customer-money">￥{{thousandth(item.amount)}}</div>
          </div>
        </div>
      </div>
      <!-- 最近访问用户统计 -->
      <div class="bottom-item" v-if="showVisit">
        <div class="statistics-title">{{lang.index_text7}}</div>
        <div class="table-head">
          <div>
            <span class="index-item">{{lang.index_text8}}</span>
            <span class="userName-item">{{lang.index_text9}}</span>
          </div>
          <div class="time">{{lang.index_text10}}</div>
        </div>
        <t-list class="bottom-list">
          <t-list-item v-for="(item,index) in visitClientList" :key="index">
            <div class="customer-item">
              <div class="customer-ranking">
                <span class="ranking">{{index+1}}</span>
                <span class="customer-name mar-113">
                  <a :href="`client_detail.htm?client_id=${item.id}`" class="aHover">{{item.username}}</a>
                </span>
              </div>
              <span class="visit_time-itme">{{item.visit_time}}</span>
            </div>
          </t-list-item>
        </t-list>
      </div>
      <!-- 当前在线管理员 -->
      <div class="bottom-item" v-if="showOnline">
        <div class="statistics-title">{{lang.index_text11}}</div>
        <div class="table-head">
          <div>
            <span class="index-item">{{lang.index_text12}}</span>
            <span class="userName-item">{{lang.index_text13}}</span>
          </div>
          <div class="time">{{lang.index_text14}}</div>
        </div>
        <t-list class="bottom-list">
          <t-list-item v-for="(item,index) in onlineAdminList" :key="index">
            <div class="customer-item">
              <div class="customer-ranking">
                <span class="ranking">{{index+1}}</span>
                <span class="customer-name mar-113">{{item.name}}</span>
              </div>
              <span class="visit_time-itme">{{item.last_action_time}}</span>
            </div>
          </t-list-item>
        </t-list>
      </div>
    </div>
  </template>
  <template v-else>
    <h2>欢迎你，{{userName}}</h2>
  </template>
</div>
<!-- =======页面独有======= -->
<script src="https://cdn.bootcdn.net/ajax/libs/echarts/5.3.3/echarts.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/index.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/index.js"></script>
{include file="footer"}