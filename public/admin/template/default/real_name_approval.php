<!-- =======内容区域======= -->
<link rel="stylesheet" href="/plugins/addon/idcsmart_certification/template/admin/css/real_name.css">
<link href="//unpkg.com/viewerjs/dist/viewer.css" rel="stylesheet">
<script src="//unpkg.com/viewerjs"></script>
<script src="//unpkg.com/v-viewer"></script>
<div id="content" class="real_name_approval table" v-cloak>
  <t-card class="list-card-container">
    <ul class="common-tab">
      <li class="active">
        <a href="javascript:;">{{lang.real_name_approval}}</a>
      </li>
      <li>
        <a href="real_name_setting.html">{{lang.real_name_setting}}</a>
      </li>
      <li>
        <a href="real_name_interface.html">{{lang.interface_manage}}</a>
      </li>
    </ul>
    <div class="common-header">
      <p></p>
      <div class="r-box">
        <div class="com-search">
          <t-input v-model="params.keywords" class="search-input" :placeholder="`${lang.please_search}${lang.proposer}`" @keyup.enter.native="seacrh" :on-clear="clearKey" clearable>
          </t-input>
          <t-icon size="20px" name="search" @click="seacrh" class="com-search-btn" />
        </div>
      </div>
    </div>
    <t-table row-key="1" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange" :hide-sort-tips="hideSortTips" >
      <template slot="sortIcon">
        <t-icon name="caret-down-small"></t-icon>
      </template>
      <template #phone="{row}">
        <span v-if="row.phone">+{{row.phone_code}}&nbsp;-&nbsp;{{row.phone}}</span>
      </template>
      <template #username="{row}">
        <a class="jump" @click="jumpUser(row)" style="cursor: pointer;">{{row.username}}</a>
      </template>
      <template #real_name="{row}">
        <template v-if="row.type === 1">
          {{row.card_name}}
        </template>
        <template v-else>{{row.company}}</template>
      </template>
      <template #status="{row}">
        <t-tag theme="success" class="status" v-if="row.status===1" variant="light">{{lang.certified}}</t-tag>
        <t-tag theme="danger" class="status" v-if="row.status===2" variant="light">{{lang.fail}}</t-tag>
        <t-tag theme="warning" class="status" v-if="row.status===3" variant="light">{{lang.to_audit}}</t-tag>
        <t-tag class="status" v-if="row.status===4" variant="light">{{lang.submitted}}</t-tag>
      </template>
      <template #type="{row}">
        <span v-if="row.type === 1">{{lang.personal_way}}</span>
        <span v-if="row.type === 2">{{lang.business_way}}</span>
        <span v-if="row.type === 3">{{lang.personal_to_business}}</span>
      </template>
      <template #create_time="{row}">
        {{row.create_time ? moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm') : ''}}
      </template>
      <template #op="{row}">
        <t-tooltip :content="lang.pass" :show-arrow="false" theme="light">
          <t-icon name="check-circle" class="common-look" @click="passHandler(row)" v-if="row.status !== 1">
          </t-icon>
        </t-tooltip>
        <t-tooltip :content="lang.reject" :show-arrow="false" theme="light">
          <t-icon name="file-excel" class="common-look" @click="rejectHandler(row)" v-if="row.status !== 2">
          </t-icon>
        </t-tooltip>
        <!-- 详情 -->
        <t-tooltip :content="lang.detail" :show-arrow="false" theme="light">
          <t-icon name="view-module" class="common-look" @click="getDetail(row)"></t-icon>
        </t-tooltip>
      </template>
    </t-table>
    <t-pagination :total="total" :page-size="params.limit" :page-size-options="pageSizeOptions" :on-change="changePage" :current="params.page" v-if="total" />
  </t-card>
  <!-- 通过/驳回-->
  <t-dialog theme="warning" :header="statusTip" :visible.sync="statusVisble">
    <template slot="footer">
      <t-button theme="primary" @click="sureChange">{{lang.sure}}</t-button>
      <t-button theme="default" @click="closeDialog">{{lang.cancel}}</t-button>
    </template>
  </t-dialog>
  <!-- 详情 -->
  <t-dialog :visible.sync="detailVisible" :header="payTit" :on-close="closePay" :footer="false" placement="center" width="600" class="detailDialog">
    <t-form :data="realDetai" ref="payDialog" :label-width="100" v-if="realDetai">
      <t-form-item :label="lang.proposer">
        <p class="disabled">{{realDetai.username}}</p>
      </t-form-item>
      <t-form-item :label="lang.order_post_time">
        <p class="disabled">{{realDetai.create_time ? moment(realDetai.create_time * 1000).format('YYYY-MM-DD HH:mm') : ''}}</p>
      </t-form-item>
      <t-form-item :label="lang.auth_way">
        <p class="disabled">{{realDetai.title}}</p>
      </t-form-item>
      <t-form-item :label="lang.auth_type">
        <p class="disabled">{{realDetai.type === 1 ? lang.personal : realDetai.type === 2 ? lang.business_way : lang.personal_to_business}}</p>
      </t-form-item>
      <t-form-item :label="lang.name" v-if="realDetai.type === 1">
        <p class="disabled">{{realDetai.card_name}}</p>
      </t-form-item>
      <t-form-item :label="lang.business_way + lang.name" v-if="realDetai.type !== 1">
        <p class="disabled">{{realDetai.company}}</p>
      </t-form-item>
      <!-- <t-form-item :label="lang.ID_type">
        <p class="disabled">{{realDetai.card_type === 0 ? lang.no_mainland : lang.mainland}}</p>
      </t-form-item> -->
      <t-form-item :label="lang.certificate_no" v-if="realDetai.type === 1">
        <p class="disabled">{{realDetai.card_number}}</p>
      </t-form-item>
      <t-form-item :label="lang.personal_no" v-if="realDetai.type !== 1">
        <p class="disabled">{{realDetai.company_organ_code}}</p>
      </t-form-item>
      <div class="card-img">
        <div class="item" v-if="realDetai.fontUrl">
          <p class="tit">{{lang.id_Photo_front}}</p>
          <div class="img" @click="lookImg(realDetai.fontUrl)">
            <img :src="realDetai.fontUrl" alt="">
            <div class="preview">
              <t-icon name="browse"></t-icon>
            </div>
          </div>
        </div>
        <div class="item" v-if="realDetai.backUrl">
          <p class="tit">{{lang.id_Photo_back}}</p>
          <div class="img" @click="lookImg(realDetai.backUrl)">
            <img :src="realDetai.backUrl" alt="">
            <div class="preview">
              <t-icon name="browse"></t-icon>
            </div>
          </div>
        </div>
        <div class="item" v-if="realDetai.slicense">
          <p class="tit">{{lang.business_slicense}}</p>
          <div class="img" @click="lookImg(realDetai.slicense)">
            <img :src="realDetai.slicense" alt="">
            <div class="preview">
              <t-icon name="browse"></t-icon>
            </div>
          </div>
        </div>
      </div>
      <div class="f-btn">
        <t-button theme="default" variant="base" @click="detailVisible = false">{{lang.close}}</t-button>
      </div>
    </t-form>
  </t-dialog>
  <!-- 查看大图 -->
  <!-- <t-dialog :visible.sync="imgVisble" class="imgDialog" width="800" :footer="false">
    <div class="img-box">
      <img src="https://tdesign.gtimg.com/demo/demo-image-1.png" alt="">
    </div>
    <div class="opt">
      <t-popup content="预览">
        <t-icon name="rotation" @click="rotation"></t-icon>
      </t-popup>
      <t-icon name="zoom-out" @click="subScla"></t-icon>
      <span>{{prentNum * sNum}}%</span>
      <t-icon name="zoom-in" @click="addScla"></t-icon>
    </div>
  </t-dialog> -->
</div>
<script src="/plugins/addon/idcsmart_certification/template/admin/api/real_name.js"></script>
<script src="/plugins/addon/idcsmart_certification/template/admin/js/real_name_approval.js"></script>