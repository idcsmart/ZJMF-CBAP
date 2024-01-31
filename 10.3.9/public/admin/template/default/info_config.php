{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/system.css">
<div id="content" class="template feedback" v-cloak>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li>
        <a href="configuration_system.htm">{{lang.system_setting}}</a>
      </li>
      <li>
        <a href="configuration_debug.htm">{{lang.debug_setting}}</a>
      </li>
      <li>
        <a href="configuration_login.htm">{{lang.login_setting}}</a>
      </li>
      <li>
        <a href="configuration_theme.htm">{{lang.theme_setting}}</a>
      </li>
      <li class="active">
        <a href="javascript:;">{{lang.info_config}}</a>
      </li>
      <li>
        <a style="display: flex; align-items: center;" href="configuration_upgrade.htm">{{lang.system_upgrade}}
          <img v-if="isCanUpdata" style="width: 20px; height: 20px; margin-left: 5px;" src="/{$template_catalog}/template/{$themes}/img/upgrade.svg">
        </a>
      </li>
    </ul>
    <div class="box">
      <t-form :data="infoParams" @submit="submitSystemGroup" :rules="typeRules" class="info-form" label-align="top">
        <t-form-item :label="lang.icp_info" name="icp_info">
          <t-input v-model="infoParams.icp_info" :placeholder="`${lang.icp_info}`"></t-input>
        </t-form-item>
        <t-form-item :label="lang.jump_link" name="icp_info_link">
          <t-input v-model="infoParams.icp_info_link" :placeholder="`${lang.jump_link}`"></t-input>
        </t-form-item>
        <t-form-item :label="lang.put_on_record" name="public_security_network_preparation">
          <t-input v-model="infoParams.public_security_network_preparation" :placeholder="`${lang.put_on_record}`"></t-input>
        </t-form-item>
        <t-form-item :label="lang.jump_link" name="public_security_network_preparation_link">
          <t-input v-model="infoParams.public_security_network_preparation_link" :placeholder="`${lang.jump_link}`"></t-input>
        </t-form-item>
        <t-form-item :label="lang.telecom_value" name="telecom_appreciation">
          <t-input v-model="infoParams.telecom_appreciation" :placeholder="`${lang.telecom_value}`"></t-input>
        </t-form-item>
        <t-form-item :label="lang.copyright" name="copyright_info">
          <t-input v-model="infoParams.copyright_info" :placeholder="`${lang.copyright}`">
          </t-input>
        </t-form-item>
        <t-form-item :label="lang.enterprise_name" name="enterprise_name">
          <t-input v-model="infoParams.enterprise_name" :placeholder="`${lang.enterprise_name}`"></t-input>
        </t-form-item>
        <t-form-item :label="lang.enterprise_telephone" name="enterprise_telephone">
          <t-input v-model="infoParams.enterprise_telephone" :placeholder="`${lang.enterprise_telephone}`"></t-input>
        </t-form-item>
        <t-form-item :label="lang.enterprise_mailbox" name="enterprise_mailbox">
          <t-input v-model="infoParams.enterprise_mailbox" :placeholder="`${lang.enterprise_mailbox}`"></t-input>
        </t-form-item>
        <t-form-item :label="lang.cloud_product_link" name="cloud_product_link">
          <t-input v-model="infoParams.cloud_product_link" :placeholder="`${lang.cloud_product_link}`"></t-input>
        </t-form-item>
        <t-form-item :label="lang.dcim_product_link" name="dcim_product_link">
          <t-input v-model="infoParams.dcim_product_link" :placeholder="`${lang.dcim_product_link}`"></t-input>
        </t-form-item>
        <t-form-item :label="lang.online_customer_service_link" name="online_customer_service_link" class="qrcode">
          <t-textarea v-model="infoParams.online_customer_service_link" :autosize="{ minRows: 3, maxRows: 8 }" :placeholder="`${lang.online_customer_service_link}`"></t-textarea>
        </t-form-item>
        <t-form-item :label="lang.enterprise_qrcode" name="qrcode">
          <t-upload :action="uploadUrl" :headers="uploadHeaders" v-model="infoParams.qrcode" theme="image" :placeholder="lang.upload_tip" theme="image-flow" accept="image/*" :auto-upload="true"></t-upload>
        </t-form-item>
        <t-form-item :label="lang.web_logo" name="logo" class="web-logo">
          <t-upload :tips="lang.logo_tip" :action="uploadUrl" :headers="uploadHeaders" v-model="infoParams.logo" theme="image" :placeholder="lang.upload_tip" theme="image-flow" accept="image/*" :auto-upload="true"></t-upload>
        </t-form-item>
        <t-button theme="primary" type="submit" class="info-btn">{{lang.hold}}</t-button>
      </t-form>
      <!-- 友情链接 -->
      <div class="limit-table">
        <div class="com-top">
          <p class="tit">{{lang.friendly_link}}</p>
          <div class="add-btn" @click="addCalc('friendly_link')">{{lang.order_text53}}</div>
        </div>
        <t-table row-key="id" :data="friendly_link_list" size="medium" :columns="linkColumns" :hover="hover" :loading="friendly_link_loading" :table-layout="tableLayout ? 'auto' : 'fixed'" display-type="fixed-width" :hide-sort-tips="true">
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
        <t-table row-key="id" :data="honor_list" size="medium" :columns="honorColumns" :hover="hover" :loading="honor_loading" :table-layout="tableLayout ? 'auto' : 'fixed'" display-type="fixed-width" :hide-sort-tips="true">
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
        <t-table row-key="id" :data="partner_list" size="medium" :columns="partnerColumns" :hover="hover" :loading="partner_loading" :table-layout="tableLayout ? 'auto' : 'fixed'" display-type="fixed-width" :hide-sort-tips="true">
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
  <t-dialog :header="infoTit" :visible.sync="classModel" :footer="false" width="600" :close-on-overlay-click="false" class="info-dialog">
    <t-form :data="classParams" ref="classForm" @submit="submitInfo" :rules="typeRules" class="cycle-form" v-if="classModel">
      <t-form-item :label="lang.nickname" name="name">
        <t-input v-model="classParams.name" :placeholder="`${lang.nickname}`"></t-input>
      </t-form-item>
      <t-form-item :label="lang.feed_link" name="url" v-if="calcType === 'friendly_link'">
        <t-input v-model="classParams.url" :placeholder="`${lang.feed_link}`"></t-input>
      </t-form-item>
      <t-form-item :label="lang.picture" name="qrcode" v-if="calcType !== 'friendly_link'" :rules="[
              { required: true, message: lang.upload + lang.picture, type: 'error' },]">
        <t-upload :action="uploadUrl" :headers="uploadHeaders" v-model="classParams.qrcode" theme="image" :placeholder="lang.upload_tip" theme="image-flow" accept="image/*" :auto-upload="true">
        </t-upload>
      </t-form-item>
      <t-form-item :label="lang.description" name="name" v-if="calcType === 'partner'">
        <t-input v-model="classParams.description" :placeholder="`${lang.description}`"></t-input>
      </t-form-item>
      <div class="com-f-btn">
        <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
        <t-button theme="default" variant="base" type="reset" @click="classModel = false">{{lang.close}}</t-button>
      </div>
    </t-form>
  </t-dialog>
  <!-- 删除提示框 -->
  <t-dialog theme="warning" :header="lang.sureDelete" :close-btn="false" :visible.sync="delVisible" class="deleteDialog">
    <template slot="footer">
      <t-button theme="primary" @click="sureDelete">{{lang.sure}}</t-button>
      <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
    </template>
  </t-dialog>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/system.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/info_config.js"></script>
{include file="footer"}