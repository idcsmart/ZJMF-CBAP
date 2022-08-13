<link rel="stylesheet" href="/plugins/addon/idcsmart_help/template/admin/css/help_index.css" />
<link rel="stylesheet" href="/plugins/addon/idcsmart_help/template/admin/css/common/reset.css" />
        <!-- =======内容区域======= -->
        <div id="content" class="helpIndex" v-cloak>
          <t-card class="list-card-container">
            <div class="chosebtn" @click="Confirmindex" style="margin-top: 20px;">保存</div>
            <div class="index_help">
              <div class="index_item" v-for="(item,index) in list" :key="item.id">
                <!-- <div class="index_itemtitl select_index">
                  {{item.name}}
                  <t-icon name="chevron-down" class="chevron-down"></t-icon>
                </div> -->
                <t-select class="select_index" size="small" @Change="changetitle" v-model="item.id">
                  <t-option v-for="(it,ind) in typelist" :key="ind" :value="it.id" :label="it.name" />
                  <!-- {{it.name}} -->
                  <!-- </t-option> -->
                </t-select>
                <div class="item_name">{{item.helps && item.helps[0]?item.helps[0].title:'--'}}</div>
                <div class="item_name">{{item.helps && item.helps[1]?item.helps[1].title:'--'}}</div>
                <div class="item_name">{{item.helps && item.helps[2]?item.helps[2].title:'--'}}</div>
                <div class="chosedocument">
                  <div>
                    <t-checkbox checked="item.index_hot_show?true:false" v-model="item.index_hot_show"
                      :disabled="item.id===0" @change="hotchange($event,item.id)">按照访问热度自动显示
                    </t-checkbox>
                  </div>
                  <div class="chosebtn" @click="mobile_file(item.id)">选择文档</div>
                </div>
              </div>
            </div>
          </t-card>
          <t-dialog :close-btn="false" placement="center" :visible.sync="showdialog" @Cancel="onCancel" width="40%"
            cancel-btn="关闭" @Confirm="Confirmindex">
            <div class="content">
              <div class="content_left">
                <div>文档分类 > <span class="blodtitle">{{dialog_name?dialog_name:dialog.name}}</span></div>
                <t-input class="inputsearchbox" v-model="params.keywords" placeholder="请输入你需要搜索的内容"
                  @change="keywordssearch(1)">
                  <t-icon name="search" slot="suffixIcon" @click="keywordssearch(1)"></t-icon>
                </t-input>
                <t-checkbox-group class="con_chexkbox" v-model="checkgroup" @change="titlecheck($event)">
                  <t-tooltip v-for="(it,ind) in searchlist" :key="it.id" v-if="it.hidden===0" class="placement top left"
                    :content="it.title" placement="top-left" :showArrow='false' theme="light">
                    <t-checkbox class="checkboxitem" :value="it.id"
                      :class="{checkboxitem_acitve:checkgroup.includes(it.id)}">
                      {{it.title}}</t-checkbox>
                  </t-tooltip>

                </t-checkbox-group>
                <div class="con_page">
                  <!-- <t-icon name="chevron-left" @click="redeleteClickOp(slotProps)"></t-icon> -->
                  <span class="chevron">
                    <t-pagination theme="simple" size="small" :current="params.page" :total="total"
                      :page-size.sync="params.limit" @Change="changePage" />
                  </span>
                  <!-- <t-icon name="chevron-right" @click="redeleteClickOp(slotProps)"></t-icon> -->
                </div>
                <div class="connect_box">
                  <div class="connect">
                    <t-icon name="chevron-right"></t-icon>
                  </div>
                </div>
              </div>

              <div class="content_right">
                <div class="blodtitle choesetitle">已选择 </div>
                <div class="chosepro" v-for="(its,inds) in choselist" :key="its.id">
                  <div class=" chosetitle" :title="its.title">{{its.title}}</div>
                  <t-icon name="minus-rectangle" class="minusIcon" @click="redeleteClickOp(its.id)"></t-icon>
                </div>
              </div>
            </div>
          </t-dialog>
        </div>
<script src="/plugins/addon/idcsmart_help/template/admin/api/help.js"></script>
<script src="/plugins/addon/idcsmart_help/template/admin/js/help_index.js"></script>