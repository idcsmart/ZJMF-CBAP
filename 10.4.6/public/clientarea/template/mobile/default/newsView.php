{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/news.css">

</head>

<body>
  <div id="mainLoading">
    <div class="ddr ddr1"></div>
    <div class="ddr ddr2"></div>
    <div class="ddr ddr3"></div>
    <div class="ddr ddr4"></div>
    <div class="ddr ddr5"></div>
  </div>
  <div id="content" class="template news news_detail" style="font-size: 0.14rem;">
    <!-- pc端 -->
    <div class="new-box">
      <p class="tit">{{newDetail.title}}</p>
      <p class="time">
        {{lang.updatw_time}}：{{newDetail.create_time | formateTime}} &nbsp;&nbsp;
        {{lang.news_key}}：{{newDetail.keywords}}
      </p>
      <div class="content" v-html="calStr(newDetail.content)"></div>
      <div class="news_annex" v-if="newDetail.attachment.length > 0">
        <p>{{lang.news_annex}}： </p>
        <p v-for="(item,index) in newDetail.attachment" :key="index">
          <a :href="item.url" target="_blank">
            {{item.name}}
          </a>
        </p>
      </div>
    </div>
  </div>
  <script src="/{$template_catalog}/template/{$themes}/js/newsView.js"></script>
  {include file="footer"}