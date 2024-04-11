{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/transfer.css">
</head>

<body>
  <div id="content" class="template transfer" style="font-size: 0.14rem;">
    <div class="con" v-cloak>
      <div class="logo">
        <img :src="logoUrl" alt="">
      </div>
      <div class="info">
        <p class="tit">{{lang.jump_tip1}}{{website_name}}</p>
        <p class="des">{{lang.jump_tip2}}{{website_name}}，{{lang.jump_tip3}}</p>
        <p class="link">{{jumpUrl}}</p>
        <div class="jump">
          <div class="btn" @click="jumpLink">{{lang.jump_tip4}}</div>
        </div>
      </div>
    </div>
  </div>
  <script src="/{$template_catalog}/template/{$themes}/js/transfer.js"></script>
  {include file="footer"}