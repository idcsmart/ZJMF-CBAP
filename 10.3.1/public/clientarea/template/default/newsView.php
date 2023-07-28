<!DOCTYPE html>
<html lang="en" theme-color="default" theme-mode>

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
  <title></title>
  <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common/element.css">
  <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/news.css">
  <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common/common.css">
  <script>
    const url = "/{$template_catalog}/template/{$themes}/"
  </script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/lang.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/common.js"></script>
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
        <p v-for="(item,index) in newDetail.attachment">
          <a :href="item" :key="index">
            {{item.split('^')[1]}}
          </a>
        </p>
      </div>
    </div>
  </div>


  <!-- =======公共======= -->
  <script src="/{$template_catalog}/template/{$themes}/js/common/vue.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/element.js"></script>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/js/newsView.js"></script>


</body>

</html>