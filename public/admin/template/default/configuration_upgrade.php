{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="configuration-system configuration-login" v-cloak>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li>
        <a href="configuration_system.html">{{lang.system_setting}}</a>
      </li>
      <li>
      <a href="configuration_login.html">{{lang.login_setting}}</a>
      </li>
      <li>
        <a href="configuration_theme.html">{{lang.theme_setting}}</a>
      </li>
      <li class="active">
        <a href="javascript:;">{{lang.system_upgrade}}</a>
      </li>
    </ul>
    <div class="upgrade-box">
      <div class="upgrade-contend">
        <div class="msg-item">
          <div class="msg-item-l">{{lang.upload_text1}}:</div>
          <div class="msg-item-r">{{systemData.last_version}}</div>
          <div class="msg-footer">
            <div class="footer-btn" v-if="!isShowProgress">
              <t-button @click="beginDown" v-show="!isDown">{{lang.upload_text2}}</t-button>
              <t-button @click="toUpdate" v-show="isDown">{{lang.upload_text3}}</t-button>
            </div>
            <div class="footer-progress" v-else>
              <div class="progress-text">{{lang.upload_text4}}{{'...(' + updateData.progress + ')'}}</div>
              <div class="progress">
                <div :style="'width:'+ updateData.progress" class="down-progress-success"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="msg-item no-margin">
          <div class="msg-item-l">{{lang.upload_text5}}:</div>
          <div class="msg-item-r">{{systemData.version}}
        </div>
          <!-- <div class="msg-item">
          <div class="msg-item-l">系统识别码:</div>
          <div class="msg-item-r">{{systemData.version}}
        </div> -->
        </div>
        <div class="upgrade-box-title public-box">
          <span class="upgrade-title-text">{{lang.upload_text6}}</span>
        </div>
        <t-table 
        :data="newList" 
        row-key="id" 
        :columns="columns" 
        bordered
        hover="hover"
        table-layout="auto"
        :loading="isLoading"
        :pagination="pagination"
        @page-change="onPageChange"
        cellEmptyContent="-"
        @row-click="onRowClick"
        class="table-box"
        ></t-table>
      </div>
    </div>
  </t-card>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/configuration_upgrade.js"></script>

{include file="footer"}