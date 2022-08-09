<link rel="stylesheet" href="/plugins/addon/idcsmart_ticket/template/admin/css/order.css" />
<!-- =======内容区域======= -->

<div id="content" class="template" v-cloak>
  <t-card class="list-card-container">
    <t-loading attach="#content" size="small" :loading="pageLoading"></t-loading>
    <t-divider class="reply-divider" align="left">{{lang.order_detail}}</t-divider>
    <div>
      <div class="detail-item">
        <span class="detail-label no-wrap">{{lang.order_title}} :</span>
        <span class="detail-value">{{orderDetailData.title}}</span>
      </div>
    </div>
    <div class="detail-row">
      <div class="detail-item no-wrap">
        <span class="detail-label">{{lang.order_name}} :</span>
        <span class="detail-value">{{orderDetailData.ticket_type}}</span>
      </div>
      <div class="detail-item">
        <span class="detail-label no-wrap">{{lang.order_current_status}} :</span>
        <t-tag theme="primary" variant="light" v-if="orderDetailData.status==='Pending'">
          {{lang.order_pending}}
        </t-tag>
        <t-tag theme="warning" variant="light" v-if="orderDetailData.status==='Handling'">
          {{lang.order_handling}}
        </t-tag>
        <t-tag theme="success" variant="light" v-if="orderDetailData.status==='Resolved'">
          {{lang.order_resolved}}
        </t-tag>
        <t-tag theme="danger" variant="light" v-if="orderDetailData.status==='Closed'">{{lang.order_closed}}
        </t-tag>
        <t-tag theme="primary" variant="light" v-if="orderDetailData.status==='Reply'">{{lang.order_reply}}
        </t-tag>
        <t-tag theme="success" variant="light" v-if="orderDetailData.status==='Replied'">
          {{lang.order_replied}}
        </t-tag>
      </div>
      <div class="detail-item">
        <span class="detail-label no-wrap">{{lang.order_priority}} :</span>
        <span class="detail-value" v-if="orderDetailData.priority === 'high'" style="color: #E34D59">
          {{ lang.order_priority_high}}
        </span>
        <span class="detail-value" v-if="orderDetailData.priority === 'medium'" style="color: #333333">
          {{ lang.order_priority_medium}}
        </span>
      </div>
      <div class="detail-item">
        <span class="detail-label no-wrap">{{lang.order_hosts}} :</span>
        <span class="detail-value">{{orderDetailData.hostStr}}</span>
      </div>
      <div class="detail-item">
        <span class="detail-label no-wrap">{{lang.order_client}} :</span>
        <span class="detail-value">{{orderDetailData.client_name}}</span>
      </div>
      <div class="detail-item">
        <span class="detail-label no-wrap">{{lang.order_poster}} :</span>
        <span class="detail-value">{{orderDetailData.post_admin_name}}</span>
      </div>
      <div class="detail-item">
        <span class="detail-label no-wrap">{{lang.order_receiver}} :</span>
        <span class="detail-value">{{orderDetailData.admin_name}}</span>
      </div>
    </div>
    <t-divider class="reply-divider" align="left">{{lang.communication_records}}</t-divider>
    <t-list class="reply-list" :style="{ height: replyListHeight }">
      <t-list-item v-for="(item, index) in orderDetailData.replies" :key="index">
        <template>
          <t-comment v-if="(item.content&&item.content!=='')||(item.attachment&&item.attachment.length>0)" :avatar="item.type==='Client'?'./img/client.png':'./img/admin.png'" :content="item.content" :author="item.client_name?item.client_name:item.admin_name">
            <template #actions>
              <div class="reply-list-attachment">
                <div class="reply-list-attachment-item" v-for="(f,i) in item.attachment" :key="i" @click="downFile(f,f.split('^')[1])">
                  <span :title="f.split('^')[1]">
                    <t-icon name="file-paste" size="small" style="color:#9696A3"></t-icon>{{f.split('^')[1]}}
                  </span>
                </div>
              </div>
              <span class="no-wrap">{{formatDate(item.create_time)}}</span>
            </template>
          </t-comment>
        </template>
      </t-list-item>
    </t-list>
    <div class="reply-form">
      <t-textarea :placeholder="lang.input" :maxcharacter="6000" :autosize="{minRows: 5, maxRows: 5}" v-model.trim="replyData"></t-textarea>
      <div class="reply-form-btn">
        <div class="upload-list">
          <t-upload theme="custom" v-model="attachmentList" action="http://101.35.248.14/admin/v1/upload" :headers="uploadHeaders" :format-response="uploadFormatResponse" show-upload-progress @progress="uploadProgress" @success="uploadSuccess" multiple :max="0">
            <t-button theme="default" class="upload-btn">
              <t-icon name="upload" size="small" style="color:#999999"></t-icon>
              <span>{{lang.attachment}}</span>
            </t-button>
            <span>{{uploadTip}}</span>
          </t-upload>
          <div class='list-custom'>
            <span v-for="(item, index) in attachmentList" :key="index" style="margin:10px">
              {{ item.name }}
              <t-icon name="close-circle-filled" @click="removeAttachment(item, index)"></t-icon>
            </span>
          </div>
        </div>
        <div>
          <t-button theme="primary" @click="submitReply">{{lang.reply}}</t-button>
          <t-button theme="default" @click="goback">{{lang.back}}</t-button>
        </div>
      </div>
    </div>
  </t-card>
</div>

<!-- =======页面独有======= -->
<script src="/plugins/addon/idcsmart_ticket/template/admin/api/order.js"></script>
<script src="/plugins/addon/idcsmart_ticket/template/admin/js/internalOrderReply.js"></script>