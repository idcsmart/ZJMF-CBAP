{include file="header"}
<!-- =======内容区域======= -->
<div id="content" class="template" v-cloak>
  <div class="no-auth">
    <img :src="`${urlPath}/img/no-auth.png`" alt="">
    <p class="tit">{{lang.tip17}}</p>
    <p class="path">
      <span>{{lang.user_manage}}</span>
      <span>&gt;</span>
      <span>{{lang.user_list}}</span>
      <span>&gt;</span>
      <span>{{lang.user_detail}}</span>
    </p>
    <p>{{lang.tip18}}</p>
  </div>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/no_auth.js"></script>
{include file="footer"}