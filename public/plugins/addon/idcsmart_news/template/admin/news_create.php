<link rel="stylesheet" href="/plugins/addon/idcsmart_news/template/admin/css/news_create.css" />
<link rel="stylesheet" href="/plugins/addon/idcsmart_news/template/admin/css/common/reset.css" />
<!-- =======内容区域======= -->
<div id="content" class="document newscreat" v-cloak>
  <t-card class="add_document">
    <div class="addtitle">{{id? lang.edit_news : lang.add_news}}</div>
    <div class="add_form">
      <t-form label-align="top" :data="detialform" class="add_tform" ref="myform" :rules="requiredRules" v-if="typelist.length > 0">
        <t-form-item :label="lang.news_title" name="title" class="inlineflex">
          <t-input :placeholder="lang.input" v-model="detialform.title" style="width: 250px;" />
        </t-form-item>
        <t-form-item :label="lang.news_classific" name="addon_idcsmart_news_type_id" class="inlineflex">
          <t-select bordered style="width: 250px;" v-model="detialform.addon_idcsmart_news_type_id">
            <t-option v-for="(item,index) in typelist" :key="item.id" :label="item.name" :value="item.id" />
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.keyword" name="keywords">
          <t-input :placeholder="lang.input" style="width: 250px;" v-model="detialform.keywords" />
        </t-form-item>
        <t-form-item :label="lang.order_attachment" name="attachment">
          <t-upload theme="custom" multiple v-model="files" :before-upload="beforeUploadfile" 
          :action="uploadUrl" :headers="uploadHeaders" :format-response="formatResponse" @fail="handleFail" @success="onSuccess" @progress="uploadProgress">
            <t-button theme="default" class="upload">
              <t-icon name="attach" color="#ccc"></t-icon> {{lang.enclosure}}
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
        <div style="margin-bottom: 10px;">{{lang.content}}</div>
        <textarea id="tiny" name="content"  v-html="detialform.content"></textarea>
      </form>
    </div>
    <div class="rich_btns">
      <t-button class="rich_btns" @click="submit">{{lang.publish}}</t-button>
     <!--  <t-button variant="outline" class="rich_btns rich_btns_save" @click="save">保存</t-button> -->
      <t-button theme="default" class="rich_btns" @click="cancle">{{lang.cancel}}</t-button>
    </div>
  </t-card>
</div>
<script src="/plugins/addon/idcsmart_news/template/admin/api/new.js"></script>
<script src="/plugins/addon/idcsmart_news/template/admin/js/news_create.js"></script>
<script src="/plugins/addon/idcsmart_news/template/admin/js/tinymce/tinymce.min.js"></script>