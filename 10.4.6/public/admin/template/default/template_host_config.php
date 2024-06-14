{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/template_controller.css" />
<!-- =======内容区域======= -->
<div id="content" class="template template_host_config" v-cloak>
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
        <p class="s-tip">{{lang.tem_tip10}}</p>
        <div class="content">
          <!-- 侧边栏 -->
          <div class="slider">
            <p class="item" :class="{ active: curValue === item.value }" @click="changeSlider(item.value)"
              v-for="(item,index) in sliderArr" ::key="index">{{item.label}}</p>
          </div>
          <!-- 内容区 -->
          <div class="con">
            <!-- 轮播图：云服务器|物理服务器独有 -->
            <div class="banner-table" v-show="curValue === 'cloud' || curValue === 'dcim'">
              <div class="common-header">
                <div class="left">{{lang.tem_banner}}</div>
                <div class="client-search">
                  <t-button @click="addBanner">{{lang.tem_add}}</t-button>
                </div>
              </div>
              <t-table row-key="id" :columns="bannerColumns" :data="tempBanner" :loading="loading"
                drag-sort="row-handler" @drag-sort="onDragSort">
                <template #drag="{row}">
                  <t-icon name="move"></t-icon>
                </template>
                <template #img="{row}">
                  <img :src="row.img" alt="" class="b-img" v-if="!row.edit" />
                  <t-upload v-model="editItem.img" :action="uploadUrl" :headers="uploadHeaders"
                    :format-response="formatImgResponse" :placeholder="lang.upload_tip" theme="image" accept="image/*"
                    :auto-upload="true" :allow-upload-duplicate-file="false" v-else>
                  </t-upload>
                </template>
                <template #url="{row}">
                  <span v-if="!row.edit">{{row.url}}</span>
                  <t-input v-else v-model="editItem.url" :placeholder="lang.jump_link"></t-input>
                </template>
                <template #time="{row}">
                  <template v-if="!row.edit">
                    {{moment(row.start_time *
                    1000).format('YYYY-MM-DD')}}&nbsp;{{lang.to}}&nbsp;{{moment(row.end_time *
                    1000).format('YYYY-MM-DD')}}
                  </template>
                  <t-date-range-picker allow-input clearable v-else v-model="editItem.timeRange" format="YYYY-MM-DD" />
                </template>
                <template #show="{row}">
                  <t-switch v-model="row.show" :custom-value="[1,0]" @change="changeShow($event,row)">
                  </t-switch>
                </template>
                <template #notes="{row}">
                  <span v-if="!row.edit">{{row.notes}}</span>
                  <t-input v-else v-model="editItem.notes" :placeholder="lang.notes"></t-input>
                </template>
                <template #op="{row, rowIndex}">
                  <template v-if="row.edit">
                    <t-tooltip :content="lang.cancel" :show-arrow="false" theme="light">
                      <t-icon name="close" class="common-look" @click="cancelItem(row, rowIndex)"></t-icon>
                    </t-tooltip>
                    <t-tooltip :content="lang.hold" :show-arrow="false" theme="light">
                      <t-icon name="save" class="common-look" @click="saveBannerItem(row,rowIndex)"></t-icon>
                    </t-tooltip>
                  </template>
                  <template v-else>
                    <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
                      <t-icon name="edit" size="18px" @click="handlerEdit(row)" class="common-look"></t-icon>
                    </t-tooltip>
                    <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
                      <t-icon name="delete" class="common-look" @click="delBanner(row)"></t-icon>
                    </t-tooltip>
                  </template>
                </template>
              </t-table>
            </div>

            <!-- ICP -->
            <div class="icp limit_table" v-if="curValue === 'icp'">
              <p>{{lang.tem_tip11}}</p>
              <div class="host-id">
                <t-input v-model="icp_product_id" :placeholder="lang.tem_input"></t-input>
                <t-button @click="saveIcp" :loading="submitLoading">{{lang.hold}}</t-button>
              </div>
            </div>
            <!-- 通用 -->
            <div class="common-header" :class="{ 'limit_table': curValue !== 'cloud' && curValue !== 'dcim'}">
              <div class="left">{{calcTit}}</div>
              <div class="client-search">
                <t-button @click="manageArea" v-if="showEditArea">{{lang.temp_edit_area}}</t-button>
                <t-button @click="handleBaseAdd">{{lang.tem_add}}</t-button>
              </div>
            </div>
            <!-- 基础表格 -->
            <t-table row-key="id" :data="baseList" size="medium" :hide-sort-tips="true" :columns="calcColumns"
              :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'"
              :class="{ 'limit_table': curValue !== 'cloud' && curValue !== 'dcim'}">
              <template #price="{row}">
                {{currency_prefix}}{{row.price | filterMoney}}
              </template>
              <template #op="{row}">
                <t-tooltip :content="lang.tem_edit" :show-arrow="false" theme="light">
                  <t-icon name="edit-1" class="common-look" @click="editBase(row)">
                  </t-icon>
                </t-tooltip>
                <t-tooltip :content="lang.tem_delete" :show-arrow="false" theme="light">
                  <t-icon name="delete" class="common-look" @click="comDel(curValue, row)">
                  </t-icon>
                </t-tooltip>
              </template>
            </t-table>
            <!-- 更多优惠 | 商标延申服务 -->
            <div class="common-header more" :class="{ 'limit_table': curValue !== 'cloud' && curValue !== 'dcim'}">
              <div class="left">
                <span v-if="curValue === 'cloud' || curValue === 'dcim' || curValue === 'brand'">{{calcTit1}}</span>
                <t-switch size="medium" :custom-value="[1,0]" v-model="hostConfig.cloud_server_more_offers"
                  @change="changeHostConfig" v-show="curValue === 'cloud'"></t-switch>
                <t-switch size="medium" :custom-value="[1,0]" v-model="hostConfig.physical_server_more_offers"
                  @change="changeHostConfig" v-show="curValue === 'dcim'"></t-switch>
              </div>
              <div class="client-search" v-show="showMore">
                <t-button @click="handleMoreAdd">{{lang.tem_add}}</t-button>
              </div>
            </div>
            <t-table row-key="id" :data="moreList" v-if="showMore" size="medium" :hide-sort-tips="true"
              :columns="curValue === 'brand' ? baseColumns : moreColumns" :hover="hover" :loading="moreLoadng"
              :table-layout="tableLayout ? 'auto' : 'fixed'"
              :class="{ 'limit_table': curValue !== 'cloud' && curValue !== 'dcim'}">
              <template #price="{row}">
                {{currency_prefix}}{{row.price | filterMoney}}
              </template>
              <template #op="{row}">
                <t-tooltip :content="lang.tem_edit" :show-arrow="false" theme="light">
                  <t-icon name="edit-1" class="common-look" @click="editMore(row)">
                  </t-icon>
                </t-tooltip>
                <t-tooltip :content="lang.tem_delete" :show-arrow="false" theme="light">
                  <t-icon name="delete" class="common-look" @click="comMoreDel(curValue, row)">
                  </t-icon>
                </t-tooltip>
              </template>
            </t-table>
          </div>
        </div>
      </div>
    </t-card>
    <!-- 基础弹窗：适用于 cloud,dcim,ssl,sms,brand,cabinet,icp -->
    <t-dialog :header="optTitle" :visible.sync="baseVisible" :footer="false" width="500" @closed="baseVisible = false"
      placement="center">
      <t-form :rules="baseRules" ref="baseDialog" :data="baseFormData" @submit="baseSubmit" :label-width="120"
        reset-type="initial" label-align="top">
        <t-form-item :label="lang.temp_title" name="title">
          <t-input v-model="baseFormData.title" :placeholder="lang.tem_input" :maxlength="15"
            show-limit-number></t-input>
        </t-form-item>
        <template v-if="curValue !== 'cloud' && curValue !== 'dcim'">
          <t-form-item :label="lang.temp_price" name="price" class="price_item">
            <t-input-number v-model="baseFormData.price" theme="normal" @blur="changePrice" :min="0"
              :decimal-places="2">
            </t-input-number>
            <t-select v-if="curValue === 'sms' || curValue === 'ssl'" class="unit" :borderless="true"
              v-model="baseFormData.price_unit">
              <t-option v-for="item in unitSelect" :value="item.value" :label="item.label" :key="item.value"></t-option>
            </t-select>
          </t-form-item>
          <t-form-item :label="lang.temp_description" name="description">
            <t-textarea v-model="baseFormData.description" :autosize="{ minRows: 3, maxRows: 5 }"
              :placeholder="lang.tem_tip5">
            </t-textarea>
          </t-form-item>
          <t-form-item :label="lang.temp_product" name="product_id">
            <t-input v-model="baseFormData.product_id" :placeholder="lang.tem_tip12"></t-input>
          </t-form-item>
        </template>
        <template v-else>
          <t-form-item :label="lang.temp_description" name="description">
            <t-input v-model="baseFormData.description" :placeholder="lang.tem_input">
            </t-input>
          </t-form-item>
          <t-form-item :label="lang.tem_jump_link" name="url">
            <t-input v-model="baseFormData.url" :placeholder="lang.tem_input">
            </t-input>
          </t-form-item>
        </template>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="baseVisible = false">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 商品弹窗：适用于 cloud,dcim,server -->
    <t-dialog :header="optTitle" :visible.sync="hostVisible" :footer="false" width="800" @closed="hostVisible = false"
      placement="center" class="host_dialog">
      <t-form :rules="baseRules" ref="hostDialog" :data="hostFormData" @submit="hostSubmit" :label-width="120"
        reset-type="initial" label-align="top">
        <!-- server -->
        <t-form-item :label="lang.temp_belong_area" name="area_id" v-if="curValue === 'server'">
          <t-select class="unit" v-model="hostFormData.area_id">
            <t-option v-for="item in areaList" :value="item.id" :label="item.first_area" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <!-- cloud dcim -->
        <template v-if="curValue === 'cloud' || curValue === 'dcim'">
          <t-form-item :label="lang.temp_belong_area" name="firId">
            <t-select class="unit" v-model="hostFormData.firId" :placeholder="lang.temp_first_area" @change="changeFir">
              <t-option v-for="(item,index) in hostAreaSelect" :value="index" :label="item.name" :key="index">
              </t-option>
            </t-select>
          </t-form-item>
          <t-form-item label=" " name="area_id" :required-mark="false" :rules="[
          { required: true, message: `${lang.tem_select}${lang.temp_second_area}`, type: 'error' }]">
            <t-select class="unit" v-model="hostFormData.area_id" :placeholder="lang.temp_second_area">
              <t-option v-for="item in calcHostArea" :value="item.id" :label="item.name" :key="item.id">
              </t-option>
            </t-select>
          </t-form-item>
        </template>
        <t-form-item :label="lang.temp_title" name="title">
          <t-input v-model="hostFormData.title" :placeholder="lang.tem_input" :maxlength="15" show-limit-number>
          </t-input>
        </t-form-item>
        <t-form-item :label="lang.description" name="description" v-if="curValue === 'cloud' || curValue === 'dcim'">
          <t-input v-model="hostFormData.description" :placeholder="lang.tem_input" :maxlength="30"
            show-limit-number></t-input>
        </t-form-item>
        <t-form-item :label="lang.temp_system_disk" name="system_disk" v-if="curValue === 'cloud'">
          <t-input v-model="hostFormData.system_disk" :placeholder="lang.tem_input">
          </t-input>
        </t-form-item>
        <template v-if="curValue === 'cloud' || curValue === 'dcim'">
          <t-form-item :label="lang.temp_cpu" name="cpu">
            <t-input v-model="hostFormData.cpu" :placeholder="lang.tem_input">
            </t-input>
          </t-form-item>
          <t-form-item :label="lang.temp_memory" name="memory">
            <t-input v-model="hostFormData.memory" :placeholder="lang.tem_tip13">
            </t-input>
          </t-form-item>
        </template>
        <t-form-item :label="lang.temp_disk" name="disk" v-if="curValue === 'dcim'">
          <t-input v-model="hostFormData.disk" :placeholder="lang.tem_tip13">
          </t-input>
        </t-form-item>
        <t-form-item :label="lang.temp_region" name="region" v-if="curValue === 'server'">
          <t-input v-model="hostFormData.region" :placeholder="lang.tem_input"></t-input>
        </t-form-item>
        <t-form-item :label="lang.temp_ip_num" name="ip_num" v-if="curValue === 'dcim' || curValue === 'server'">
          <t-input v-model="hostFormData.ip_num" :placeholder="lang.tem_tip13"></t-input>
        </t-form-item>
        <t-form-item :label="lang.temp_bw" name="bandwidth">
          <t-input v-model="hostFormData.bandwidth" :placeholder="lang.tem_tip13">
          </t-input>
        </t-form-item>
        <template v-if="curValue === 'server'">
          <t-form-item :label="lang.temp_defense" name="defense">
            <t-input v-model="hostFormData.defense" :placeholder="lang.tem_tip13">
            </t-input>
          </t-form-item>
          <t-form-item :label="lang.temp_bw_price" name="bandwidth_price" class="price_item">
            <t-input-number v-model="hostFormData.bandwidth_price" theme="normal" @blur="changePrice" :min="0"
              :decimal-places="2">
            </t-input-number>
            <t-select class="unit bw" :borderless="true" v-model="hostFormData.bandwidth_price_unit">
              <t-option v-for="item in bwUnitSelect" :value="item.value" :label="item.label" :key="item.value">
              </t-option>
            </t-select>
          </t-form-item>
        </template>
        <template v-if="curValue === 'cloud' || curValue === 'dcim'">
          <t-form-item :label="lang.temp_duration" name="duration">
            <t-input v-model="hostFormData.duration" :placeholder="lang.tem_tip13">
            </t-input>
          </t-form-item>
          <t-form-item :label="lang.temp_tag" name="tag">
            <t-input v-model="hostFormData.tag" :placeholder="lang.tem_tip13">
            </t-input>
          </t-form-item>
          <t-form-item :label="lang.temp_original_price" name="original_price" class="price_item">
            <t-input-number v-model="hostFormData.original_price" theme="normal" :min="0" :decimal-places="2">
            </t-input-number>
            <t-select class="unit" :borderless="true" v-model="hostFormData.original_price_unit">
              <t-option v-for="item in unitSelect" :value="item.value" :label="item.label" :key="item.value">
              </t-option>
            </t-select>
          </t-form-item>
        </template>
        <t-form-item :label="lang.temp_sell_price" name="selling_price" class="price_item">
          <t-input-number v-model="hostFormData.selling_price" theme="normal" @blur="changePrice" :min="0"
            :decimal-places="2">
          </t-input-number>
          <t-select class="unit" :borderless="true" v-model="hostFormData.selling_price_unit">
            <t-option v-for="item in unitSelect" :value="item.value" :label="item.label" :key="item.value">
            </t-option>
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.temp_product" name="product_id">
          <t-input v-model="hostFormData.product_id" :placeholder="lang.tem_tip12"></t-input>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="hostVisible = false">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 区域弹窗：适用于 cloud,dcim,server -->
    <t-dialog :header="lang.temp_edit_area" :visible.sync="areaVisble" :footer="false" width="600"
      @closed="areaVisble = false" placement="center" class="group_dialog">
      <t-form :rules="baseRules" ref="comDialog" :data="areaForm" @submit="submitArea" :label-width="120"
        reset-type="initial" label-align="top">
        <t-form-item :label="lang.temp_first_area" name="name">
          <t-input v-model="areaForm.first_area" :placeholder="lang.tem_input"></t-input>
          <t-button theme="primary" type="submit" :loading="submitLoading" v-if="curValue === 'server'">{{optType === 'add' ? lang.tem_add :
            lang.hold}}
          </t-button>
        </t-form-item>
        <t-form-item :label="lang.temp_second_area" name="name" v-if="curValue === 'cloud' || curValue === 'dcim'">
          <t-input v-model="areaForm.second_area" :placeholder="lang.tem_input"></t-input>
          <t-button theme="primary" type="submit" :loading="submitLoading">{{optType === 'add' ? lang.tem_add :
            lang.hold}}
          </t-button>
        </t-form-item>
      </t-form>
      <t-table row-key="id" :data="areaList" size="medium" :hide-sort-tips="true" :columns="calcAreaColumns"
        :hover="hover" :loading="areaLoading" :table-layout="tableLayout ? 'auto' : 'fixed'" :max-height="400">
        <template #op="{row}">
          <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
            <t-icon name="edit-1" @click="editArea(row)" class="common-look"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
            <t-icon name="delete" @click="delArea(row)" class="common-look"></t-icon>
          </t-tooltip>
        </template>
      </t-table>
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
<script src="/{$template_catalog}/template/{$themes}/js/template_host_config.js"></script>
{include file="footer"}
