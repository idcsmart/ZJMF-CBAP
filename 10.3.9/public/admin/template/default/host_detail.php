{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
<!-- =======内容区域======= -->
<div id="content" class="host-detail hasCrumb" v-cloak>
  <!-- crumb -->
  <div class="com-crumb">
    <span>{{lang.user_manage}}</span>
    <t-icon name="chevron-right"></t-icon>
    <span style="cursor: pointer;" @click="goClient">{{lang.user_list}}</span>
    <t-icon name="chevron-right"></t-icon>
    <span class="cur">{{lang.product_info}}</span>
    <span class="back-text" @click="goBack">
      <t-icon name="chevron-left-double"></t-icon>{{lang.back}}
    </span>
  </div>
  <t-card class="list-card-container">
    <div class="com-h-box">
      <ul class="common-tab">
        <li>
          <a :href="`${baseUrl}/client_detail.htm?id=${client_id}`">{{lang.personal}}</a>
        </li>
        <li class="active">
          <a :href="`${baseUrl}/client_host.htm?id=${client_id}`">{{lang.product_info}}</a>
        </li>
        <li>
          <a :href="`${baseUrl}/client_order.htm?id=${client_id}`">{{lang.order_manage}}</a>
        </li>
        <li>
          <a :href="`${baseUrl}/client_transaction.htm?id=${client_id}`">{{lang.flow}}</a>
        </li>
        <li>
          <a :href="`${baseUrl}/client_log.htm?id=${client_id}`">{{lang.operation}}{{lang.log}}</a>
        </li>
        <li>
          <a :href="`${baseUrl}/client_notice_sms.htm?id=${client_id}`">{{lang.notice_log}}</a>
        </li>
        <li v-if="hasTicket && authList.includes('TicketController::ticketList')">
          <a :href="`${baseUrl}/plugin/idcsmart_ticket/client_ticket.htm?id=${client_id}`">{{lang.auto_order}}</a>
        </li>
        <li>
          <a :href="`${baseUrl}/client_records.htm?id=${client_id}`">{{lang.info_records}}</a>
        </li>
      </ul>
      <div class="user">
        <t-select class="pro-select" v-if="this.clientList" v-model="client_id" :popup-props="popupProps" filterable :filter="filterMethod" @change="changeUser" :loading="searchLoading" reserve-keyword :on-search="remoteMethod">
          <t-option :key="clientDetail.id" :value="clientDetail.id" :label="calcShow(clientDetail)" v-if="isExist">
            #{{clientDetail.id}}-{{clientDetail.username ? clientDetail.username : (clientDetail.phone?
            clientDetail.phone: clientDetail.email)}}
            <span v-if="clientDetail.company">({{clientDetail.company}})</span>
          </t-option>
          <t-option v-for="item in clientList" :value="item.id" :label="calcShow(item)" :key="item.id">
            #{{item.id}}-{{item.username ? item.username : (item.phone? item.phone: item.email)}}
            <span v-if="item.company">({{item.company}})</span>
          </t-option>
        </t-select>
        <t-select class="pro-select" v-model="formData.id" :placeholder="lang.tailorism" @change="changePro">
          <t-option v-for="item in hostArr" :value="item.id" :label="item.product_name" :key="item.id"></t-option>
        </t-select>
        <t-button class="btn-transfer" v-if="hasTransfer" @click="handelTransfer">
          {{lang.host_transfer_text11}}
        </t-button>
      </div>

    </div>
    <div class="box">
      <t-form :data="formData" :rules="rules" ref="userInfo" label-align="top">
        <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 90 }">
          <t-col :xs="12" :xl="6">
            <p class="com-tit"><span>{{ lang.basic_info }}</span></p>
            <div class="item">
              <t-form-item :label="lang.product" name="product_id">
                <!-- 商品选择： 根据插件设置的下拉类型处理 -->
                <t-select v-model="formData.product_id" :popup-props="popupProps" v-if="selectWay === 'default'">
                  <t-option v-for="item in proList" :value="item.id" :label="item.name" :key="item.id">
                  </t-option>
                </t-select>
                <t-popup attach="#myPopup" placement="bottom-left" :visible="visibleTree" v-else style="width: 100%">
                  <div id="myPopup">
                    <t-input :value="calcName(formData.product_id)" :placeholder='lang.tailorism' @click.native="focusHandler">
                      <template slot="suffix">
                        <t-icon name="chevron-down" size="18px" :class="{active:visibleTree}"></t-icon>
                      </template>
                    </t-input>
                  </div>
                  <template slot="content" class="test">
                    <!--  :expanded="isClick?clickExpand:calcExpand" -->
                    <t-tree :data="calcProduct" :activable="true" :expand-on-click-node="true" ref="tree" :keys="{value: 'key', label:'name'}" @click="onClick" hover :expand-mutex="true" id="product-tree">
                    </t-tree>
                  </template>
                </t-popup>
              </t-form-item>
              <t-form-item :label="lang.interface" v-if="!isAgent">
                <t-select v-model="formData.server_id" :popup-props="popupProps">
                  <t-option v-for="item in serverList" :value="item.id" :label="item.name" :key="item.id">
                  </t-option>
                </t-select>
              </t-form-item>
            </div>
            <div class="item">
              <t-form-item :label="lang.host_name" name="name">
                <t-input v-model="formData.name" :placeholder="lang.host_name"></t-input>
              </t-form-item>
              <t-form-item :label="lang.status">
                <t-select v-model="formData.status" :popup-props="popupProps">
                  <t-option v-for="item in status" :value="item.value" :label="item.label" :key="item.value">
                  </t-option>
                </t-select>
              </t-form-item>
            </div>
            <t-form-item :label="lang.admin_notes" name="notes">
              <t-textarea v-model="formData.notes" :placeholder="lang.admin_notes"></t-textarea>
            </t-form-item>
            <!-- 1-31 后台返回操作按钮模块 -->
            <div class="module-opt" style="display: flex;justify-content: space-between;">
              <!-- <t-button @click="handlerMoudle('create')" v-if="curStatus === 'Failed'">{{lang.module_create}}</t-button>
              <t-button @click="handlerSuspend" v-if="curStatus === 'Active'">{{lang.deactivate}}</t-button>
              <t-button @click="handlerMoudle('unsuspend')" v-if="curStatus === 'Suspended'">{{lang.cancel}}{{lang.deactivate}}</t-button>
              <t-tooltip placement="top-right" :content="lang.module_tip" :show-arrow="false" theme="light" v-if="curStatus !== 'Deleted'">
                <t-button @click="handlerMoudle('delete')">
                  {{lang.delete}}
                  <t-icon name="help-circle" size="18px" />
                </t-button>
              </t-tooltip> -->
              <div class="left">
                <t-button @click="handlerMoudle(item.func)" v-for="(item,index) in optBtns" :key="index">
                  <template v-if="item.func !== 'terminate'">{{item.name}}</template>
                  <t-tooltip placement="top-right" :content="lang.module_tip" :show-arrow="false" theme="light" v-else>
                    {{item.name}}
                    <t-icon name="help-circle" size="18px" />
                  </t-tooltip>
                </t-button>
              </div>
              <div class="right">
                <t-button @click="jumpToOrder">
                  {{lang.connect}}{{lang.order}}
                </t-button>
                <t-button @click="jumpToTicket" v-if="hasTicket && authList.includes('TicketController::ticketList')">
                  {{lang.connect}}{{lang.auto_order}}
                </t-button>
                <t-button @click="jumpToPro(connectProId)" v-if="connectProId">
                  {{lang.order_hosts}}
                </t-button>
              </div>
            </div>

          </t-col>
          <t-col :xs="12" :xl="6">
            <!-- 优惠码 -->
            <template v-if="hasPlugin">
              <p class="com-tit"><span>{{lang.promo_code}}</span></p>
              <t-table row-key="id" :data="promoList" size="medium" :columns="recordColumns" :hover="hover" bordered :loading="recordLoading" :table-layout="tableLayout ? 'auto' : 'fixed'">
                <template #create_time="{row}">
                  {{row.create_time ? moment(row.create_time * 1000).format('YYYY/MM/DD HH:mm') : '--'}}
                </template>
                <template #scene="{row}">
                  {{lang[row.scene]}}
                </template>
                <template #order_id="{row}">
                  <a class="jump" @click="jumpOrder(row)">{{row.order_id}}</a>
                </template>
                <template #promo="{row}">
                  {{row.code}}：-{{currency_prefix}}{{row.discount}}
                </template>
              </t-table>
            </template>
          </t-col>
          <t-col :xs="24" :xl="12" style="margin-top: 20px;">
            <p class="com-tit"><span>{{lang.financial_infos}}</span></p>
            <!-- 续费 -->
            <!-- <t-button theme="primary" class="renew-btn" @click="renewDialog" v-if="(curStatus === 'Active' || curStatus === 'Suspended') && hasPlugin && tempCycle !== 'free'  && tempCycle !== 'onetime'">{{lang.renew}}</t-button> -->
            <div class="config-item">
              <t-form-item :label="lang.buy_amount" name="first_payment_amount">
                <t-input v-model="formData.first_payment_amount" :placeholder="lang.buy_amount">
                </t-input>
              </t-form-item>
              <t-form-item :label="lang.billing_cycle">
                <t-input v-model="formData.billing_cycle_name" disabled></t-input>
              </t-form-item>
              <!-- 续费方式 -->
              <t-form-item name="renew_amount" :required-mark="false" class="renew-item">
                <div slot="label" class="custom-label">
                  <span class="label">{{lang.cur_renew_price}}</span>
                  <div class="right" v-if="tempHostId === 0">
                    {{lang.bt_tip4}}
                    <t-switch size="small" :custom-value="[1,0]" v-model="formData.ratio_renew"></t-switch>
                    <t-tooltip placement="top-right" :content="lang.bt_tip5" :show-arrow="false" theme="light">
                      <t-icon name="help-circle" size="18px" />
                    </t-tooltip>
                  </div>
                </div>
                <t-input v-model="formData.renew_amount" :placeholder="lang.renew_amount"></t-input>
              </t-form-item>
              <!-- <t-form-item :label="lang.discount">
                <t-select v-model="formData.rel_id">
                  <t-option v-for="item in curList" :value="item.id" :label="item.name" :key="item.id">
                  </t-option>
                </t-select>
              </t-form-item> -->

              <!-- 当前周期原价 -->
              <t-form-item name="base_price" v-if="tempHostId === 0">
                <div slot="label" class="custom-label">
                  <span class="label">{{lang.cur_cycle_price}}</span>
                  <t-tooltip placement="top-right" :content="lang.cur_cycle_tip" :show-arrow="false" theme="light">
                    <t-icon name="help-circle" size="18px" />
                  </t-tooltip>
                </div>
                <t-input v-model="formData.base_price"></t-input>
              </t-form-item>
              <t-form-item :label="lang.billing_way">
                <t-select v-model="formData.billing_cycle" :popup-props="popupProps">
                  <t-option v-for="item in cycleList" :value="item.value" :label="item.label" :key="item.value">
                  </t-option>
                </t-select>
              </t-form-item>

              <t-form-item :label="lang.open_time" name="active_time" :rules="[{ validator: checkTime}]">
                <t-date-picker mode="date" format="YYYY-MM-DD HH:mm:ss" enable-time-picker v-model="formData.active_time" @change="changeActive" />
              </t-form-item>
              <t-form-item :label="lang.due_time" name="due_time" :rules="[{ validator: checkTime1}]">
                <t-date-picker mode="date" format="YYYY-MM-DD HH:mm:ss" enable-time-picker v-model="formData.due_time" @change="changeActive" :disabled="disabled" />
              </t-form-item>
            </div>
          </t-col>
          <t-col :xs="24" :xl="12" style="margin-top: 30px;" v-if="tempHostId > 0 || hostFieldList.length > 0">
            <p class="com-tit"><span>{{lang.box_label22}}</span></p>
            <!-- 修改host id -->
            <div class="config-item" v-if="tempHostId > 0">
              <t-form-item :label="lang.upstream_host_id">
                <t-input-number v-model="formData.upstream_host_id" :min="0" :decimal-places="0" theme="normal" style="width: 100%;"></t-input-number>
              </t-form-item>
            </div>
            <!-- 渲染配置信息 -->
            <template v-for="(item,index) in hostFieldList">
              <p style="margin: 0; font-weight: bold;">{{item.name}}</p>
              <div class="config-item render">
                <t-form-item :label="el.name" v-for="(el,ind) in item.field" :key="ind">
                  <t-select v-model="el.value" v-if="el.options">
                    <t-option :value="op.id + ''" :label="op.name" v-for="op in el.options" :key="op.id">
                    </t-option>
                  </t-select>
                  <t-input v-model="el.value" :disabled="el.disable" v-else></t-input>
                  <!-- 手动资源 -->
                  <t-button class="distribute" @click="handlerDistribute" v-if="el.key === 'manual_resource'">{{lang.distribute}}
                  </t-button>
                  <!-- DCIM机器 -->
                  <template v-if="el.key === 'zjmf_dcim_id'">
                    <!-- 分配 -->
                    <t-button class="distribute" @click="showDcim" v-if="curDcimId === '0'">{{lang.distribute}}
                    </t-button>
                    <template v-else>
                      <t-button class="distribute" @click="jumpDcim(el.url)">{{lang.manage}}
                      </t-button>
                      <t-button class="distribute" @click="optItem('', 'free')">{{lang.idle}}
                      </t-button>
                    </template>
                  </template>
                </t-form-item>
              </div>
            </template>
          </t-col>

          <!-- 商品自定义字段 -->
          <t-col :xs="24" :xl="12" style="margin-top: 20px;" v-if="self_defined_field.length > 0">
            <p class="com-tit"><span>{{lang.client_custom_label32}}</span></p>
            <t-form :data="customForm" :rules="customRule" ref="customForm" label-align="top">
              <div class="config-item">
                <t-form-item :label="item.field_name" v-for="item in self_defined_field" :key="item.id">
                  <!-- 下拉类型 -->
                  <t-select v-model="customForm[item.id + '']" :placeholder="item.description" v-if="item.field_type === 'dropdown'">
                    <t-option v-for="(items,indexs) in calcFieldOption(item.field_option)" :value="items" :label="items" :key="indexs">
                    </t-option>
                  </t-select>
                  <!-- 勾选框类型 -->
                  <t-checkbox v-model="customForm[item.id + '']" :label="item.description" v-else-if="item.field_type === 'tickbox'">{{item.description}}
                  </t-checkbox>
                  <t-textarea v-model="customForm[item.id + '']" :placeholder="item.description" v-else-if="item.field_type === 'textarea'">
                  </t-textarea>
                  <t-input :placeholder="item.description" v-model="customForm[item.id + '']" v-else>
                  </t-input>
                </t-form-item>
              </div>
            </t-form>
          </t-col>

          <t-col :xs="24" :xl="12">
            <div class="footer-btn">
              <t-button theme="primary" type="submit" @click="updateUserInfo" :loading="isLoading">{{lang.hold}}</t-button>
              <t-button theme="default" variant="base" @click="back">{{lang.delete}}</t-button>
            </div>
          </t-col>
          <!-- <t-col :xs="12" :xl="6">
            <div class="config-area" v-html="config"></div>
          </t-col> -->
          <!-- 内页模块 -->
          <t-col :xs="24" :xl="12" style="margin-top: 20px;" v-if="isShowModule">
            <div class="config-box">
              <div class="content"></div>
            </div>
          </t-col>


          <!-- 上游信息 -->
          <t-col :xs="24" :xl="12" style="margin-top: 20px;" v-if="upData && upData.id">
            <p class="com-tit"><span>{{lang.upstream_info}}</span></p>
            <div class="item">
              <t-form-item :label="lang.buy_amount">
                <div>{{upData.first_payment_amount}}</div>
              </t-form-item>
              <t-form-item :label="lang.renew_amount">
                <div>{{upData.renew_amount}}</div>
              </t-form-item>
              <t-form-item :label="lang.billing_way">
                <div>{{cycleObj[upData.billing_cycle]}}</div>
              </t-form-item>
              <t-form-item :label="lang.billing_cycle">
                <div>{{upData.billing_cycle_name}}</div>
              </t-form-item>
              <t-form-item :label="lang.open_time">
                <div>{{upData.active_time ? moment(upData.active_time * 1000).format('YYYY-MM-DD HH:mm:ss') : '--' }}
                </div>
              </t-form-item>
              <t-form-item :label="lang.due_time">
                <div>{{upData.due_time ? moment(upData.due_time * 1000).format('YYYY-MM-DD HH:mm:ss') : '--' }}</div>
              </t-form-item>
              <t-form-item :label="lang.status">
                <div>{{lang[upData.status]}}</div>
              </t-form-item>
              <t-form-item :label="lang.host_name">
                <div>{{upData.name}}</div>
              </t-form-item>
            </div>
          </t-col>
        </t-row>
      </t-form>
    </div>

  </t-card>




  <!-- 产品转移弹窗 -->
  <t-dialog :header="lang.host_transfer_text11" :close-btn="false" :close-on-overlay-click="false" :visible.sync="transferVisible" width="800" :footer="false">
    <t-form :data="transferForm" ref="transferInfo" :rules="transferRules" @submit="onTransferSubmit" label-align="top">
      <t-form-item :label="lang.host_transfer_text13" name="client_id">
        <t-select v-if="this.clientList" v-model="transferForm.client_id" :popup-props="popupProps" filterable :filter="filterMethod" :loading="searchLoading" reserve-keyword :on-search="remoteMethod">
          <t-option v-for="item in clientList" :value="item.id" :label="calcShow(item)" :key="item.id">
            #{{item.id}}-{{item.username ? item.username : (item.phone? item.phone: item.email)}}
            <span v-if="item.company">({{item.company}})</span>
          </t-option>
        </t-select>
      </t-form-item>
      <t-form-item :label="lang.host_transfer_text14">
        <div>
          <t-table hover row-key="index" :data="hostList" bordered :columns="columns" class="table-box">
            <template #host_id="{row}">
              <span>{{row.id}}</span>
            </template>
            <template #product_name="{row}">
              <span>{{row.product_name}}</span>
            </template>
            <template #name="{row}">
              <span>{{row.name}}</span>
            </template>
            <template #notes="{row}">
              <span>{{row.notes || '--'}}</span>
            </template>
          </t-table>
        </div>
      </t-form-item>
      <t-form-item :label="lang.host_transfer_text17" v-if="link_host.length">
        <div>
          <t-table hover row-key="index" :data="link_host" :columns="columns" class="table-box" bordered>
            <template #host_id="{row}">
              <span>{{row.id}}</span>
            </template>
            <template #product_name="{row}">
              <span>{{row.product_name}}</span>
            </template>
            <template #name="{row}">
              <span>{{row.name}}</span>
            </template>
            <template #notes="{row}">
              <span>{{row.notes || '--'}}</span>
            </template>
          </t-table>
        </div>
      </t-form-item>
      <p class="red-tip" v-if="hostTips != ''">{{hostTips}}</p>
      <div class="tip-box" v-if="canTransfer">
        <p>{{lang.host_transfer_text18}}</p>
        <p>{{lang.host_transfer_text19}}</p>
      </div>
      <div class="com-f-btn">
        <t-button theme="primary" type="submit" :disabled="!canTransfer" :loading="transfering">{{lang.host_transfer_text12}}</t-button>
        <t-button theme="default" variant="base" @click="transferVisible = false">{{lang.cancel}}</t-button>
      </div>
    </t-form>
  </t-dialog>




  <!-- 删除 -->
  <t-dialog theme="warning" :header="lang.sureDelete" :close-btn="false" :visible.sync="delVisible">
    <template slot="footer">
      <div class="common-dialog">
        <t-button @click="onConfirm">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </div>
    </template>
  </t-dialog>
  <!-- 续费弹窗 -->
  <t-dialog :header="lang.renew" :visible.sync="renewVisible" class="renew-dialog" :footer="false">
    <div class="swiper" v-if="renewList.length >0 ">
      <div class="l-btn" @click="subIndex">
        <t-icon name="chevron-left"></t-icon>
      </div>
      <div class="m-box">
        <div class="swiper-item" v-for="(item,index) in renewList" :key="item.id" :class="{card: item.id === showId[0] || item.id === showId[1] || item.id === showId[2], active: item.id === curId}" @click="checkCur(item)">
          <p class="cycle">{{item.billing_cycle}}</p>
          <p class="price"><span>{{currency_prefix}}</span>{{item.price}}</p>
        </div>
      </div>
      <div class="r-btn" @click="addIndex">
        <t-icon name="chevron-right"></t-icon>
      </div>
    </div>
    <div class="com-f-btn">
      <div class="total">{{lang.total}}：<span class="price"><span class="symbol">{{currency_prefix}}</span>{{curRenew.price}}</span></div>
      <div>
        <t-checkbox v-model="pay">{{lang.mark_Paid}}</t-checkbox>
      </div>
      <t-button theme="primary" @click="submitRenew" :loading="submitLoading">{{lang.sure_renew}}</t-button>
    </div>
  </t-dialog>

  <!-- 1-7 新增 -->
  <!-- 开通，取消暂停，删除 -->
  <t-dialog theme="warning" :header="optTilte" :close-btn="false" :visible.sync="moduleVisible" :close-on-overlay-click="false">
    <template slot="footer">
      <div class="common-dialog">
        <t-button @click="confirmModule" :loading="moduleLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="moduleVisible=false">{{lang.cancel}}</t-button>
      </div>
    </template>
  </t-dialog>
  <!-- 停用 -->
  <t-dialog :header="lang.deactivate" :close-btn="false" :close-on-overlay-click="false" :visible.sync="suspendVisible" width="600" :footer="false">
    <t-form :data="suspendForm" ref="userInfo" @submit="onSubmit">
      <t-form-item :label="lang.suspend_type">
        <t-select v-model="suspendForm.suspend_type">
          <t-option :value="item.value" :label="item.label" v-for="item in suspendType" :key="item.value">
          </t-option>
        </t-select>
      </t-form-item>
      <t-form-item :label="lang.suspend_reason">
        <t-textarea v-model="suspendForm.suspend_reason"></t-textarea>
      </t-form-item>
      <div class="com-f-btn">
        <t-button theme="primary" type="submit" :loading="moduleLoading">{{lang.hold}}</t-button>
        <t-button theme="default" variant="base" @click="suspendVisible = false">{{lang.cancel}}</t-button>
      </div>
    </t-form>
  </t-dialog>
  <!-- 1-7 新增 end -->

  <!-- 手动资源 | DCIM资源 分配 -->
  <t-dialog :header="distributeTitle" :visible.sync="resourceDialog" width="80%" :footer="false">
    <div class="left-search">
      <template v-if="distributeType === 'manual'">
        <t-input v-model="resourceForm.keywords" class="search-input" :placeholder="lang.manual_text1" @keyup.enter.native="getResourcesList" :on-clear="clearKey" clearable></t-input>
        <t-select v-model="resourceForm.addon_manual_resource_supplier_id" :placeholder="lang.manual_text2" clearable>
          <t-option v-for="item in supplierList" :value="item.id" :label="item.name" :key="item.id">
          </t-option>
        </t-select>
        <t-button @click="getResourcesList" class="seacrh">{{lang.query}}</t-button>
      </template>
      <template v-if="distributeType === 'dcim'">
        <t-input v-model="dcimForm.ip" class="search-input" :placeholder="`${lang.input}IP`"
        @keyup.enter.native="getDcimList" :on-clear="clearIp" clearable></t-input>
        <t-select v-model="dcimForm.status" :placeholder="lang.status" clearable>
          <t-option v-for="item in statusArr" :value="item.value" :label="item.label" :key="item.value">
          </t-option>
        </t-select>
        <t-select v-model="dcimForm.server_group_id" :placeholder="lang.group" clearable>
          <t-option v-for="item in dcimGroup" :value="item.id" :label="item.name" :key="item.id">
          </t-option>
        </t-select>
        <t-button @click="getDcimList" class="seacrh">{{lang.query}}</t-button>
      </template>
    </div>
    <t-table row-key="index" :data="distributeType === 'manual' ? resourceList : dcimList"
    :columns="distributeType === 'manual' ? resourcesColumns : dcimColumns"
    :loading="resourceLoading" :hover="true">
      <template #id="{row}">
        <a :href="row.dcim_url" target="_blank" class="common-look">{{row.id}}-{{row.wltag}}</a>
      </template>
      <template #ip="{row}">
        {{row.ip[0]?.ipaddress}}
        <t-tooltip :show-arrow="false" theme="light" trigger="hover">
          <template slot="content">
            <div class="dcim-ip-list">
              <p>IP</p>
              <p v-for="(item,index) in row.ip" :key="index">{{item.ipaddress}}</p>
            </div>
          </template>
          <span v-if="row.ip.length > 1" class="common-look">({{row.ip.length}})</span>
        </t-tooltip>
      </template>
      <template #bw="{row}">
        {{(row.in_bw).replace('Mbps','') || 0}} / {{(row.out_bw).replace('Mbps','') || 0}}Mbps
      </template>
      <template #status="{row}">
        <span :class="`dcim-status-${row.status}`">{{calcDcimStatus(row.status)}}</span>
      </template>
      <template #remarks="{row}">
        {{row.remarks || '--'}}
      </template>
      <template #group_name="{row}">
        {{row.group_name || '--'}}
      </template>
      <template #due_time="{row}">
        {{moment(row.due_time * 1000).format('YYYY/MM/DD HH:mm') }}
      </template>
      <template #user="{row}">
        <span v-if="row.client_name">
          {{row.client_name}}
          <span v-if="row.host_name">({{row.host_name}})</span>
        </span>
        <span v-else>--</span>
      </template>
      <template #configuration="{row}">
        <span v-if="row.configuration">{{row.configuration}}</span>
        <span v-else>--</span>
      </template>
      <template #notes="{row}">
        <span v-if="row.notes">{{row.notes}}</span>
        <span v-else>--</span>
      </template>
      <template #power_status="{row}">
        {{calcStatus(row.power_status)}}
      </template>
      <template #cost="{row}">
        {{currency_prefix}}{{row.cost}}
      </template>
      <template #opt="{row}">
        <template v-if="distributeType === 'manual'">
          <template v-if="curResourcesId">
            <t-tooltip :content="lang.idle" :show-arrow="false" theme="light" v-if="row.host_id">
              <a class="common-look" @click="optItem(row, 'free')">
                <img :src="`${urlPath}/img/icon/free.png`" alt="">
              </a>
            </t-tooltip>
          </template>
          <template v-else>
            <t-tooltip :content="lang.distribute" :show-arrow="false" theme="light" v-if="!row.host_id">
              <a class="common-look" @click="optItem(row, 'allot')">
                <img :src="`${urlPath}/img/icon/allot.png`" alt="">
              </a>
            </t-tooltip>
            <t-tooltip :content="lang.idle" :show-arrow="false" theme="light" v-else>
              <a class="common-look" @click="optItem(row, 'free')">
                <img :src="`${urlPath}/img/icon/free.png`" alt="">
              </a>
            </t-tooltip>
          </template>
        </template>
        <template v-if="distributeType === 'dcim'">
          <t-tooltip :content="lang.distribute" :show-arrow="false" theme="light" v-if="row.status == '1'">
            <a class="common-look" @click="optItem(row, 'allot')">
              <img :src="`${urlPath}/img/icon/allot.png`" alt="">
            </a>
          </t-tooltip>
        </template>
      </template>
    </t-table>
    <t-pagination show-jumper :total="dcimTotal" v-if="dcimTotal" :current="dcimForm.page"
    :page-size="dcimForm.limit" :page-size-options="pageSizeOptions" :on-change="changePage" />
  </t-dialog>
  <!-- 分配/空闲 -->
  <t-dialog theme="warning" :header="resourceTitle" :close-btn="false" :visible.sync="resourceVisible">
    <template slot="footer">
      <div class="common-dialog">
        <t-button @click="handlerResource">{{lang.sure}}</t-button>
        <t-button theme="default" @click="resourceVisible=false">{{lang.cancel}}</t-button>
      </div>
    </template>
  </t-dialog>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/host_detail.js"></script>
{include file="footer"}
