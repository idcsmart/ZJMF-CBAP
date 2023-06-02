{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="configuration-system configuration-login" v-cloak>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li>
        <a href="configuration_system.htm">{{lang.system_setting}}</a>
      </li>
      <li>
        <a href="configuration_login.htm">{{lang.login_setting}}</a>
      </li>
      <li>
        <a href="configuration_theme.htm">{{lang.theme_setting}}</a>
      </li>
      <li>
        <a href="info_config.htm">{{lang.info_config}}</a>
      </li>
      <li class="active">
        <a style="display: flex; align-items: center;" href="javascript:;">{{lang.system_upgrade}}
          <img v-if="isCanUpdata" style="width: 20px; height: 20px; margin-left: 5px;" src="/{$template_catalog}/template/{$themes}/img/upgrade.svg">
        </a>
      </li>
    </ul>
    <div class="upgrade-box">
      <div class="upgrade-contend">
        <div class="msg-item">
          <div class="s-item">
            <div class="msg-item-l">{{lang.upload_text1}}：</div>
            <div class="msg-item-r">{{systemData.last_version}}</div>
            <div class="msg-footer" v-if="hasUpdate">
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
          <div class="s-item">
            <div class="msg-item-l">{{lang.upload_text7}}：</div>
            <div class="msg-item-r">{{systemData.license}}</div>
          </div>
        </div>
        <div class="msg-item no-margin">
          <div class="s-item">
            <div class="msg-item-l">{{lang.upload_text5}}：</div>
            <div class="msg-item-r">{{systemData.version}}</div>
          </div>
          <div class="s-item">
            <div class="msg-item-l">{{lang.upload_text8}}：</div>
            <div class="msg-item-r">{{systemData.service_due_time ? systemData.service_due_time === '0000-00-00 00:00:00' ? '未订购服务' : systemData.service_due_time : '未订购服务'}}</div>
          </div>
          <!-- <div class="msg-item">
          <div class="msg-item-l">系统识别码:</div>
          <div class="msg-item-r">{{systemData.version}}
        </div> -->
        </div>
        <div class="upgrade-box-title public-box">
          <span class="upgrade-title-text">{{lang.upload_text6}}</span>
        </div>
        <t-table :data="newList" row-key="id" :columns="columns" bordered hover="hover" table-layout="auto" :loading="isLoading" :pagination="pagination" @page-change="onPageChange" cellEmptyContent="-" @row-click="onRowClick" class="table-box"></t-table>
      </div>
    </div>
  </t-card>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/configuration_upgrade.js"></script>

{include file="footer"}