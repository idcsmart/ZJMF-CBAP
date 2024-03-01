{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/home.css">
<!-- =======内容区域======= -->
<div id="content" class="home-drag" v-cloak>
  <com-config>
    <div class="top-setting">
      <h2>{{lang.welcome}}，{{userName}}</h2>
      <t-popup placement="right-bottom" overlay-class-name="home-checked">
        <t-icon name="setting"></t-icon>
        <template #content>
          <p class="tit">{{lang.widget_manage}}</p>
          <t-checkbox v-for="(item,index) in allWidget" :key="item.id" v-model="item.checked" @change="changeCheck($event,item)">{{item.title}}</t-checkbox>
        </template>
      </t-popup>
    </div>
    <template v-show="showList.length > 0">
      <t-loading size="small" :loading="loading" attach="#drag-row" :show-overlay="false"></t-loading>
      <t-row :gutter="30" id="drag-row">
        <draggable v-model="showList" chosen-class="chosen" handle=".mover" force-fallback="true" animation="300" @start="onStart" @end="onEnd">
          <transition-group>
            <t-col :sm="calcSm(item.columns)" :md="calcMd(item.columns)" :xl="calcXl(item.columns)" v-for="(item,index) in showList" :key="item.id">
              <div class="drag-box" :id="item.id"></div>
              <t-icon name="move" class="mover"></t-icon>
            </t-col>
          </transition-group>
        </draggable>
      </t-row>
    </template>
    <!-- <template v-else>
    <h2>欢迎你，{{userName}}</h2>
  </template> -->
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/js/common/jquery.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/common/echarts.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/common/Sortable.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/common/vuedraggable.umd.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/home.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/home.js"></script>
{include file="footer"}
