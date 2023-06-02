(function(window, undefined) {
  var old_onload = window.onload;
  window.onload = function() {
    const template = document.getElementsByClassName('template')[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      data() {
        return {
          // 加载中
          pageLoading: false,
          // 工单详情
          orderDetailData: {},
          // 回复记录列表高度
          replyListHeight: (template.scrollHeight - 440) +'px',
          // 回复内容
          replyData: '',
          // 上传附件
          attachmentList: [],
          // 上传附件headers设置
          uploadHeaders: {
            Authorization: 'Bearer' + ' ' + localStorage.getItem('jwt')
          },
          uploadTip: ''
        };
      },
      methods: {
        // 提交回复
        submitReply () {
          if(!this.replyData){
            this.$message.warning({ content: lang.order_reply_verify, placement: 'top-right' });
            return;
          }
          const attachmentList = [];
          this.attachmentList.forEach(item => {
            attachmentList.push(item.response.save_name);
          });
          const params = {
            id: this.orderDetailData.id,
            content: this.replyData,
            attachment: attachmentList
          };
          replyUserOrder(this.orderDetailData.id, params).then(result => {
            this.$message.success({ content: result.data.msg, placement: 'top-right' });
            this.replyData = '';
            this.attachmentList = [];
            this.getOrderDetailData();
          }).catch(error => {
            this.$message.warning({ content: error.data.msg, placement: 'top-right' });
          });
        },
        goback () {
          location.href = 'userOrder.htm';
        },
        // 上传附件-返回内容
        uploadFormatResponse(res) {
          if (!res || (res.status !== 200)) {
            return { error: lang.upload_fail };
          }
          return { ...res, save_name: res.data.save_name };
        },
        // 上传附件-进度
        uploadProgress(val) {
          if(val.percent){
            this.uploadTip = 'uploaded'+val.percent+'%';
            if(val.percent === 100){
              this.uploadTip = '';
            }
          }
        },
        // 上传附件-成功后
        uploadSuccess(res) {
          if(res.fileList.filter(item=>item.name==res.file.name).length>1){
            this.$message.warning({ content: lang.upload_same_name, placement: 'top-right' });
            this.attachmentList.splice(this.attachmentList.length-1,1);
          }
          this.$forceUpdate();
        },
        // 删除已上传附件
        removeAttachment(file,i){
          this.attachmentList.splice(i,1);
          this.$forceUpdate();
        },
        // 下载文件
        downFile(res, title) {
          let url = res.lastIndexOf('/');
          res = res.substring(url+1,res.length);
          downloadFile(
              {
                name:res,
              }
          ).then(function (response) {
            const blob = new Blob([response.data]);
            const fileName = title;
            const linkNode = document.createElement('a');
            linkNode.download = fileName; //a标签的download属性规定下载文件的名称
            linkNode.style.display = 'none';
            linkNode.href = URL.createObjectURL(blob); //生成一个Blob URL
            document.body.appendChild(linkNode);
            linkNode.click(); //模拟在按钮上的一次鼠标单击
            URL.revokeObjectURL(linkNode.href); // 释放URL 对象
            document.body.removeChild(linkNode);
          }).catch(function (error) {
            console.log(error);
          });
        },

        // 获取工单详情
        async getOrderDetailData() {
          this.pageLoading = true;
          const str = location.search.substr(1).split('&');
          const orderId = str[0].split('=')[1];
          const result = await getUserOrderDetail(orderId);
          if (result.status === 200) {
            this.orderDetailData = result.data.data.ticket;
            this.getOrderTypeName();
            this.getHostsName();
          }
        },
        // 获取当前工单类型名称
        getOrderTypeName() {
          getUserOrderType().then(result => {
            const orderTypeList = result.data.data.list;
            const orderType = orderTypeList.filter(item=>item.id===this.orderDetailData.ticket_type_id)[0];
            this.orderDetailData.ticket_type = orderType?orderType.name:null;
            this.$forceUpdate();
          });
        },
        // 获取当前用户关联产品名称
        getHostsName() {
          getHost({ page: 1, limit: 10000 }).then(result => {
            const data = result.data.data.list;
            const hostList = [];
            this.orderDetailData.host_ids.forEach(id => {
              hostList.push(data.filter(item=>item.id===id)[0]);
            });
            this.orderDetailData.hostStr = hostList.map(item=>item&&item.product_name?item.product_name:null).join('、');
            this.$forceUpdate();
            this.pageLoading = false;
          });
        },

        // 时间格式转换
        formatDate (dateStr) {
          const date = new Date(dateStr * 1000);
          const str1 = [date.getFullYear(), date.getMonth()+1, date.getDate()].join('-');
          const str2 = [this.formatDateAdd0(date.getHours()), this.formatDateAdd0(date.getMinutes())].join(':');
          return str1 + ' ' + str2;
        },
        formatDateAdd0 (m) {
          return m<10?'0'+m:m;
        }
      },
      created() {
        this.getOrderDetailData();
      },
    }).$mount(template);
    typeof old_onload == 'function' && old_onload();
  };
})(window);
