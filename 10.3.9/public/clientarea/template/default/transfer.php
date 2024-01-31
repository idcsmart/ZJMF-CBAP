<!DOCTYPE html>
<html lang="en" theme-color="default" theme-mode>

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
  <title></title>
  <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common/common.css">
  <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/transfer.css">
  <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common/element.css">
  <script>
    const url = "/{$template_catalog}/template/{$themes}/"
  </script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/lang.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/common.js"></script>
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


  <!-- =======公共======= -->
  <script src="/{$template_catalog}/template/{$themes}/js/common/vue.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/element.js"></script>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/js/transfer.js"></script>


</body>

</html>
