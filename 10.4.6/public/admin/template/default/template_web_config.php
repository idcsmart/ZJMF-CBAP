{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/template_controller.css" />
<!-- =======内容区域======= -->
<div id="content" class="template template_web_config" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <div class="top-box">
        <h2 class="top-back">{{lang.temp_controller}}
          <a :href="backUrl" class="template-back">&lt;&lt;{{lang.temp_back}}</a>
        </h2>
        <div class="top-btn">
          <t-button @click="handleUpgrade" v-if="themeInfo.upgrade === 1">{{lang.upgrade_plugin}}</t-button>
          <t-button theme="danger" @click="handleDelete">{{lang.tem_delete}}</t-button>
        </div>
      </div>
      <t-tabs v-model="tab" class="controller-tab" @change="changeTab">
        <t-tab-panel v-for="item in tabList" :value="item.url" :key="item.name" :label="item.title">
        </t-tab-panel>
      </t-tabs>
      <div class="box">
        <!-- 参数配置 -->
        <t-form :data="infoParams" @submit="submitConfig" :rules="typeRules" class="info-form" label-align="top">
          <t-form-item :label="lang.temp_icp_info" name="icp_info">
            <t-input v-model="infoParams.icp_info" :placeholder="`${lang.temp_icp_info}`"></t-input>
          </t-form-item>
          <t-form-item :label="lang.tem_jump_link" name="icp_info_link">
            <t-input v-model="infoParams.icp_info_link" :placeholder="`${lang.tem_jump_link}`"></t-input>
          </t-form-item>
          <t-form-item :label="lang.temp_put_on_record" name="public_security_network_preparation">
            <t-input v-model="infoParams.public_security_network_preparation"
              :placeholder="`${lang.temp_put_on_record}`"></t-input>
          </t-form-item>
          <t-form-item :label="lang.tem_jump_link" name="public_security_network_preparation_link">
            <t-input v-model="infoParams.public_security_network_preparation_link"
              :placeholder="`${lang.tem_jump_link}`"></t-input>
          </t-form-item>
          <t-form-item :label="lang.temp_telecom_value" name="telecom_appreciation">
            <t-input v-model="infoParams.telecom_appreciation" :placeholder="`${lang.temp_telecom_value}`"></t-input>
          </t-form-item>
          <t-form-item :label="lang.temp_copyright" name="copyright_info">
            <t-input v-model="infoParams.copyright_info" :placeholder="`${lang.temp_copyright}`">
            </t-input>
          </t-form-item>
          <t-form-item :label="lang.temp_enterprise_name" name="enterprise_name">
            <t-input v-model="infoParams.enterprise_name" :placeholder="`${lang.temp_enterprise_name}`"></t-input>
          </t-form-item>
          <t-form-item :label="lang.temp_enterprise_telephone" name="enterprise_telephone">
            <t-input v-model="infoParams.enterprise_telephone"
              :placeholder="`${lang.temp_enterprise_telephone}`"></t-input>
          </t-form-item>
          <t-form-item :label="lang.temp_enterprise_mailbox" name="enterprise_mailbox">
            <t-input v-model="infoParams.enterprise_mailbox" :placeholder="`${lang.temp_enterprise_mailbox}`"></t-input>
          </t-form-item>
          <t-form-item :label="lang.temp_online_link" name="online_customer_service_link" class="qrcode">
            <t-textarea v-model="infoParams.online_customer_service_link" :autosize="{ minRows: 3, maxRows: 8 }"
              :placeholder="`${lang.temp_online_link}`"></t-textarea>
          </t-form-item>
          <t-form-item :label="lang.temp_enterprise_qrcode" name="qrcode">
            <t-upload :action="uploadUrl" :headers="uploadHeaders" :format-response="formatResponse"
              v-model="infoParams.qrcode" theme="image" :placeholder="lang.upload_tip" theme="image-flow"
              accept="image/*" :auto-upload="true"></t-upload>
          </t-form-item>
          <t-form-item :label="lang.temp_web_logo" name="logo" class="web-logo">
            <t-upload :tips="lang.tem_tip7" :action="uploadUrl" :format-response="formatResponse"
              :headers="uploadHeaders" v-model="infoParams.logo" theme="image" :placeholder="lang.upload_tip"
              theme="image-flow" accept="image/*" :auto-upload="true">
            </t-upload>
          </t-form-item>
          <t-button theme="primary" type="submit" :loading="loading" class="info-btn">
            {{lang.hold}}
          </t-button>
        </t-form>
        <!-- 友情链接 -->
        <div class="limit-table">
          <div class="com-top">
            <p class="tit">{{lang.temp_friendly_link}}</p>
            <div class="add-btn" @click="addCalc('friendly_link')">{{lang.tem_add}}</div>
          </div>
          <t-table row-key="id" :data="friendly_link_list" size="medium" :columns="linkColumns" :hover="hover"
            :loading="friendly_link_loading" :table-layout="tableLayout ? 'auto' : 'fixed'" display-type="fixed-width"
            :hide-sort-tips="true">
            <template slot="sortIcon">
              <t-icon name="caret-down-small"></t-icon>
            </template>
            <template #op="{row}">
              <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
                <t-icon name="edit-1" @click="updateItem('friendly_link', row)" class="common-look"></t-icon>
              </t-tooltip>
              <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
                <t-icon name="delete" @click="deleteItem('friendly_link',row)" class="common-look"></t-icon>
              </t-tooltip>
            </template>
          </t-table>
        </div>
        <!-- 荣誉资质 -->
        <div class="limit-table">
          <div class="com-top">
            <p class="tit">{{lang.honor}}</p>
            <div class="add-btn" @click="addCalc('honor')">{{lang.order_text53}}</div>
          </div>
          <t-table row-key="id" :data="honor_list" size="medium" :columns="honorColumns" :hover="hover"
            :loading="honor_loading" :table-layout="tableLayout ? 'auto' : 'fixed'" display-type="fixed-width"
            :hide-sort-tips="true">
            <template slot="sortIcon">
              <t-icon name="caret-down-small"></t-icon>
            </template>
            <template #img="{row}">
              {{row.img.split('^')[1] || '--'}}
            </template>
            <template #op="{row}">
              <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
                <t-icon name="edit-1" @click="updateItem('honor', row)" class="common-look"></t-icon>
              </t-tooltip>
              <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
                <t-icon name="delete" @click="deleteItem('honor',row)" class="common-look"></t-icon>
              </t-tooltip>
            </template>
          </t-table>
        </div>
        <!-- 合作伙伴 -->
        <div class="limit-table">
          <div class="com-top">
            <p class="tit">{{lang.partner}}</p>
            <div class="add-btn" @click="addCalc('partner')">{{lang.order_text53}}</div>
          </div>
          <t-table row-key="id" :data="partner_list" size="medium" :columns="partnerColumns" :hover="hover"
            :loading="partner_loading" :table-layout="tableLayout ? 'auto' : 'fixed'" display-type="fixed-width"
            :hide-sort-tips="true">
            <template slot="sortIcon">
              <t-icon name="caret-down-small"></t-icon>
            </template>
            <template #img="{row}">
              {{row.img.split('^')[1] || '--'}}
            </template>
            <template #op="{row}">
              <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
                <t-icon name="edit-1" @click="updateItem('partner', row)" class="common-look"></t-icon>
              </t-tooltip>
              <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
                <t-icon name="delete" @click="deleteItem('partner',row)" class="common-look"></t-icon>
              </t-tooltip>
            </template>
          </t-table>
        </div>
      </div>
    </t-card>
    <!-- 新增/编辑 友情链接/荣誉/合作 -->
    <t-dialog :header="infoTit" :visible.sync="classModel" :footer="false" width="600" :close-on-overlay-click="false"
      class="info-dialog">
      <t-form :data="classParams" ref="classForm" @submit="submitInfo" :rules="typeRules" class="cycle-form"
        v-if="classModel">
        <t-form-item :label="lang.tem_name" name="name">
          <t-input v-model="classParams.name" :placeholder="`${lang.tem_name}`"></t-input>
        </t-form-item>
        <t-form-item :label="lang.temp_feed_link" name="url" v-if="calcType === 'friendly_link'">
          <t-input v-model="classParams.url" :placeholder="`${lang.temp_feed_link}`"></t-input>
        </t-form-item>
        <t-form-item :label="lang.temp_picture" name="qrcode" v-if="calcType !== 'friendly_link'" :rules="[
    { required: true, message: lang.tem_attachment + lang.temp_picture, type: 'error' } ]">
          <t-upload :action="uploadUrl" :headers="uploadHeaders" v-model="classParams.qrcode" theme="image"
            :placeholder="lang.upload_tip" theme="custom" accept="image/*" :auto-upload="true"
            :format-response="formatResponse">
          </t-upload>
        </t-form-item>
        <t-form-item :label="lang.description" name="description" v-if="calcType === 'partner'">
          <t-input v-model="classParams.description" :placeholder="`${lang.description}`"></t-input>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" type="reset" @click="classModel = false">{{lang.close}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 删除提示框 -->
    <t-dialog theme="warning" :header="lang.temp_sure_delete" :close-btn="false" :visible.sync="delVisible">
      <template slot="footer">
        <t-button theme="primary" @click="sureDelete" :loading="delLoading">{{lang.tem_sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.tem_cancel}}</t-button>
      </template>
    </t-dialog>

    <!-- 删除主题弹窗 -->
    <t-dialog theme="warning" :header="lang.tem_tip14" :close-btn="false" :visible.sync="delDialog">
      <div class="del-tip">
        <div class="del-tip-text">
          <h3>{{lang.tem_tip15}} <span style="color: var(--td-brand-color);"> {{theme}} </span></h3>
          <p>{{lang.tem_tip16}}</p>
        </div>
      </div>
      <template slot="footer">
        <t-button theme="primary" @click="sureDel" :loading="delLoading">{{lang.tem_sure}}</t-button>
        <t-button theme="default" @click="delDialog=false">{{lang.tem_cancel}}</t-button>
      </template>
    </t-dialog>

    <!-- 升级主题弹窗 -->
    <t-dialog :header="lang.tem_tip17" :visible.sync="upgradeDialog" :footer="false" width="500"
      @closed="upgradeDialog = false" placement="center">
      <div class="tem-upgrade-box" style="padding-left: 120px;">
        <p>{{lang.tem_tip18}}：{{themeInfo.old_version}}</p>
        <p>{{lang.tem_tip19}}：{{themeInfo.version}}</p>
        <p>{{lang.tem_tip20}}: {{themeInfo.description || '--'}}</p>
      </div>
      <div class="com-f-btn">
        <t-button theme="primary" @click="sureUpgrade" :loading="submitLoading">{{lang.tem_sure}}</t-button>
        <t-button theme="default" variant="base" @click="upgradeDialog = false">{{lang.cancel}}</t-button>
      </div>
    </t-dialog>
  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/template_controller.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/template_web_config.js"></script>
{include file="footer"}
