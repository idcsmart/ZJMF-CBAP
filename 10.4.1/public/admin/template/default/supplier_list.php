{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/supplier_list.css">
<div id="content" class="supplier_list" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <div class="add-btn">
        <t-button @click="addSupplier" v-permission="'auth_upstream_downstream_supplier_create_supplier'">{{lang.upstream_text41}}</t-button>
      </div>
      <t-table row-key="id" :data="data" size="medium" :columns="columns" @sort-change="sortChange" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" display-type="fixed-width">
        <template #num="{row}">
          <span>{{row.host_num}}/{{row.product_num}}</span>
        </template>
        <template #name="{row}">
          <span style="color: var(--td-brand-color); cursor: pointer;" @click="goDetail(row.id)" v-if="$checkPermission('auth_upstream_downstream_supplier_detail')">{{row.name}}</span>
          <span v-else>{{row.name}}</span>
        </template>
        <template #type="{row}">
          <span>{{typeObj[row.type]}}</span>
        </template>
        <template #status="{row}">
          <span>
            <t-icon v-if="row.status" name="check-circle-filled" size="18px" style="color: #00A870;"></t-icon>
            <t-tooltip :content="row.resgen" :show-arrow="false" theme="light" v-else>
              <t-icon name="error-circle-filled" size="18px" style="color: #E34D59;"></t-icon>
            </t-tooltip>
          </span>
        </template>
        <template #op="{row}">
          <t-tooltip :content="lang.look" :show-arrow="false" theme="light">
            <t-icon name="view-module" @click="goDetail(row.id)" class="common-look" v-permission="'auth_upstream_downstream_supplier_detail'"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.invoice_text19" :show-arrow="false" theme="light">
            <t-icon name="edit-1" @click="handelEdit(row)" class="common-look" v-permission="'auth_upstream_downstream_supplier_update_supplier'"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.box_content8" :show-arrow="false" theme="light">
            <t-icon name="delete" @click="handelDel(row.id)" class="common-look" v-permission="'auth_upstream_downstream_supplier_delete_supplier'"></t-icon>
          </t-tooltip>
        </template>
      </t-table>
      <t-pagination show-jumper :total="total" :page-size="params.limit" :current="params.page" :page-size-options="pageSizeOptions" @change="changePage" v-if="total" />
    </t-card>
    <!-- 配置弹窗 -->
    <t-dialog :header="lang.upstream_text42" :visible.sync="configVisble" :footer="false" width="650"
     @closed="diaClose" class="config-dialog">
      <t-form :rules="rules" ref="userDialog" :data="formData" @submit="onSubmit" :label-width="120" reset-type="initial">
        <t-form-item :label="item.title" :name="item.name" v-for="item in configData" :key="item.title">
          <!-- text -->
          <t-input style="width: calc(100% - 30px);" v-if="item.type==='text'" :disabled="(item.disableEdit && editId !='')" v-model="formData[item.name]" :placeholder="item.tip ? item.tip : item.title"></t-input>
          <!-- password -->
          <t-input v-if="item.type==='password'" type="password" v-model="formData[item.name]" :placeholder="item.tip ? item.tip :item.title"></t-input>
          <!-- textarea -->
          <t-textarea style="width: calc(100% - 30px);" v-if="item.type==='textarea'" v-model="formData[item.name]" :placeholder="item.tip ? item.tip :lang.input + item.title">
          </t-textarea>
          <!-- radio -->
          <t-radio-group style="width: calc(100% - 30px);" v-if="item.type==='radio'" v-model="formData[item.name]" :options="computedOptions(item.options)">
          </t-radio-group>
          <!-- checkbox -->
          <t-checkbox-group style="width: calc(100% - 30px);" v-if="item.type==='checkbox'" v-model="formData[item.name]" :options="item.options">
          </t-checkbox-group>
          <!-- select -->
          <t-select style="width: calc(100% - 30px);" v-if="item.type==='select'" v-model="formData[item.name]" :placeholder="item.tip ? item.tip :item.title">
            <t-option v-for="ele in computedOptions(item.options)" :value="ele.value" :label="ele.label" :key="ele.value">
            </t-option>
          </t-select>
          <t-tooltip :content="item.tip" :show-arrow="false" theme="light" v-if="item.tip">
            <t-icon name="help-circle" size="18px" style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
          </t-tooltip>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="configVisble=false">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 删除弹窗 -->
    <t-dialog theme="warning" :header="lang.sureDelete" :visible.sync="delVisible">
      <template slot="footer">
        <t-button theme="primary" @click="sureDel" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/upstream.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/supplier_list.js"></script>
{include file="footer"}
