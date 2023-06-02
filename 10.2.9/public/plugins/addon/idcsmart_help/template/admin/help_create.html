<link rel="stylesheet" href="/plugins/addon/idcsmart_help/template/admin/css/help_create.css" />
<link rel="stylesheet" href="/plugins/addon/idcsmart_help/template/admin/css/common/reset.css" />
<!-- =======内容区域======= -->
<div id="content" class="document" v-cloak>
  <t-card class="add_document">
    <div class="addtitle">{{id?"编辑文档":"新增文档"}}</div>
    <div class="add_form">
      <t-form label-Align="top" :data="detialform" class="add_tform" ref="myform" :rules="requiredRules">
        <t-form-item label="文档名称" name="title" class="inlineflex">
          <t-input placeholder="请输入" v-model="detialform.title" style="width: 250px;" />
        </t-form-item>
        <t-form-item label="分类" name="addon_idcsmart_help_type_id" class="inlineflex">
          <template v-if="id">
            <t-select bordered style="width: 250px;" v-model="detialform.addon_idcsmart_help_type_id" v-if="detialform.addon_idcsmart_help_type_id">
              <t-option v-for="(item,index) in typelist" :key="item.id" :label="item.name" :value="item.id" />
            </t-select>
          </template>
          <template v-else>
            <t-select bordered style="width: 250px;" v-model="detialform.addon_idcsmart_help_type_id">
              <t-option v-for="(item,index) in typelist" :key="item.id" :label="item.name" :value="item.id" />
            </t-select>
          </template>
        </t-form-item>
        <t-form-item label="关键字" name="keywords">
          <t-input placeholder="请输入" style="width: 250px;" v-model="detialform.keywords" />
        </t-form-item>
        <t-form-item label="上传附件" name="attachment">
          <t-upload theme="custom" multiple v-model="files" :before-upload="beforeUploadfile" :action="uploadUrl" :format-response="formatResponse" :headers="uploadHeaders" @fail="handleFail" @progress="uploadProgress">
            <t-button theme="default" class="upload">
              <t-icon name="attach" color="#ccc"></t-icon> 附件
            </t-button>
            <span>{{uploadTip}}</span>
          </t-upload>

          <div v-if="files && files.length" class='list-custom'>
            <ul>
              <li v-for="(item, index) in files" :key="index">
                {{ item.name}}
                <t-icon class="delfile" name="close-circle" color="#ccc" @click="delfiles(item.name)"></t-icon>
              </li>
            </ul>
          </div>
        </t-form-item>
      </t-form>

    </div>
    <div class="add_richtext">
      <form method="post">
        <div style="margin-bottom: 10px;">内容</div>
        <textarea id="tiny" name="content" v-html="detialform.content"></textarea>
      </form>
    </div>
    <div class="rich_btns">
      <div>
        <t-button @click="viewNew">预览</t-button>
      </div>
      <div class="right-btn">
        <t-button class="confirm-btn" @click="submit">发布</t-button>
        <t-button theme="default" @click="cancle">取消</t-button>
      </div>
    </div>
  </t-card>
</div>
<script src="/plugins/addon/idcsmart_help/template/admin/api/help.js"></script>
<script src="/plugins/addon/idcsmart_help/template/admin/js/help_create.js"></script>
<script src="/plugins/addon/idcsmart_help/template/admin/js/tinymce/tinymce.min.js"></script>