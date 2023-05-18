(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName('template')[0];
    Vue.prototype.lang = window.lang;
    const host = location.host
    const fir = location.pathname.split('/')[1]
    const str = `${host}/${fir}/`
    new Vue({
      data() {
        return {
          // 加载中
          pageLoading: false,
          // 删除对话框
          deleteVisible: false,
          replayVisible: false,
          sendLoading: false,
          isAddNotes: false,
          viewer: null,
          // 添加备注按钮的loading
          addNotesLing: false,
          top: 'top',
          // 工单详情
          isEditing: false, // 是否正在编辑模式
          orderDetailData: {},
          params: {
            selectHostList: [], // 选择的产品
            ticket_type: '', // 产品类型
            status: '' // 工单状态
          },
          // 回复内容
          replyData: '',
          editObj: {}, // 正在编辑的对象
          // 产品列表
          hostList: [],
          // 编辑按钮loading
          editLoding: false,
          // 订单类型列表
          order_status_options: [],
          // 回复列表
          replyList: [],
          product_obj_list: [],
          prereplyList: [],
          // 日志列表
          logList: [],
          logVisible: false,
          columns: [
            {
              colKey: 'description',
              title: lang.order_text39,
              cell: "description",
              ellipsis: true
            },
            {
              colKey: 'create_time',
              title: lang.order_text40,
              cell: "create_time",
              width: '157'
            }
          ],
          // 工单状态下拉框数据
          orderTypeList: [],
          // 上传附件
          attachmentList: [],
          // 预览图片地址
          preImg: '',
          // 上传附件headers设置
          uploadHeaders: {
            Authorization: 'Bearer' + ' ' + localStorage.getItem('backJwt')
          },
          uploadTip: '',
          img_visible: false,
          baseURL: url,
          uploadUrl: 'http://' + str + 'v1/upload',
          /** 非受控用法：与分页组件对齐（此处注释为非受控用法示例，代码有效，勿删） */
          pagination: {
            current: 1,
            pageSize: 10,
            total: 0,
            showJumper: true,
          },
        };
      },
      computed: {
        avatar() {
          return (type) => {
            return type === 'Client' ? `${this.baseURL}img/client.png` : `${this.baseURL}img/admin.png`
          }
        }
      },
      methods: {
        // 分页变化时触发该事件
        onPageChange(pageInfo, newData) {
          // 受控用法所需
          this.pagination.current = pageInfo.current;
          this.pagination.pageSize = pageInfo.pageSize;
        },
        // 点击预设回复弹窗
        usePrePlay(item) {
          tinyMCE.editors[0].setContent(item.content)
          this.replayVisible = false
        },
        // 点击图片
        hanldeImage(event) {
          if (event.target.nodeName == 'IMG' || event.target.nodeName == 'img') {
            const img = event.target.currentSrc
            this.preImg = img
            this.viewer.show()

          }
        },
        // 跳转用户信息页
        goUserPagr() {
          const url = 'http://' + str + `client_detail.htm?client_id=${this.orderDetailData.client_id}`
          window.open(url)
        },
        goClientPage(id) {
          if (id) {
            const url = 'http://' + str + `client_detail.htm?client_id=${id}`
            window.open(url)
          }
        },
        // 获取工单预设回复列表
        getTicketPrereply() {
          ticketPrereply().then((res) => {
            this.prereplyList = res.data.data.list
          })
        },
        goList() {
          location.href = 'index.htm'
        },
        goProductDetail(item) {
          const url = 'http://' + str + `host_detail.htm?client_id=${item.client_id}&id=${item.id}`
          window.open(url)
        },
        // 确认添加备注
        handelAddNotes() {
          this.addNotesLing = true
          const content = tinyMCE.editors[0].getContent()
          const params = {
            id: this.orderDetailData.id,
            content: content
          }
          addTicketNotes(params).then((res) => {
            this.getOrderDetailData();
            tinyMCE.editors[0].setContent('')
            this.isAddNotes = false
          }).catch(error => {
            this.$message.warning({ content: error.data.msg, placement: 'top-right' });
          }).finally(() => {
            this.addNotesLing = false
          })
        },
        // 取消添加备注
        cancelAddNotes() {
          tinyMCE.editors[0].setContent('')
          this.isAddNotes = false
        },
        // 备注列表
        getTicketNotes() {
          const str = location.search.substr(1).split('&');
          const orderId = str[0].split('=')[1];
          ticketNotes(orderId).then((res) => {
            this.notesList = res.data.data.list
            const arr = this.orderDetailData.replies.concat(this.notesList)
            arr.sort((a, b) => {
              return a.create_time - b.create_time
            })
            arr.forEach((item) => {
              item.isShowBtn = false
              if (!item.type) {
                item.type = 'notes'
              }
            })
            this.replyList = arr
          }).catch((err) => {
            this.replyList = this.orderDetailData.replies.concat([])
          }).finally(() => {
            this.pageLoading = false;
            this.$nextTick(() => {
              this.scrollBotton()
            })
          })
        },
        // 编辑消息
        editItem(item) {
          this.editObj = item
          this.isEditing = true
          this.replyData = item.content
          tinyMCE.editors[0].setContent(item.content)
          tinyMCE.editors[0].editorManager.get('tiny').focus();
          this.handleScrollBottom()
        },
        // 聊天列表滚动到底部
        scrollBotton() {
          const listDom = document.querySelector('.reply-list')
          const listBoxDom = document.querySelector('.t-list__inner')
          const h = listBoxDom.scrollHeight
          listDom.scrollTop = h
        },
        // 滚动到底部
        handleScrollBottom() {
          const detailDom = document.querySelector('.area')
          console.log(detailDom.scrollTop);
          detailDom.scrollTop = detailDom.scrollHeight
        },
        // 确认编辑
        handelEdit() {
          this.editLoding = true
          const conten = tinyMCE.editors[0].getContent()
          if (this.editObj.type === 'notes') {
            const params = {
              ticket_id: this.editObj.ticket_id,
              id: this.editObj.id,
              content: conten
            }
            notesReplyEdit(params).then((result) => {
              this.getOrderDetailData();
              tinyMCE.editors[0].setContent('')
              this.isEditing = false
            }).catch(error => {
              this.$message.warning({ content: error.data.msg, placement: 'top-right' });
            }).finally(() => {
              this.editLoding = false
            })
          } else {
            const params = {
              id: this.editObj.id,
              content: conten
            }
            ticketReplyEdit(this.editObj.id, params).then((result) => {
              this.getOrderDetailData();
              tinyMCE.editors[0].setContent('')
              this.isEditing = false
            }).catch(error => {
              this.$message.warning({ content: error.data.msg, placement: 'top-right' });
            }).finally(() => {
              this.editLoding = false
            })
          }

        },
        // 点击添加备注
        addNotes() {
          this.isAddNotes = true
          tinyMCE.editors[0].editorManager.get('tiny').focus();
        },
        // 取消编辑
        cancelEdit() {
          tinyMCE.editors[0].setContent('')
          this.isEditing = false
        },
        // 点击删除按钮
        deleteItem(item) {
          if (this.isEditing) {
            this.$message.error(lang.order_text41)
            return
          }
          this.editObj = item
          this.deleteVisible = true
        },
        // 删除弹窗确认
        handelDelete() {
          if (this.editObj.type === 'notes') {
            const params = {
              ticket_id: this.editObj.ticket_id,
              id: this.editObj.id,
            }
            orderNotesDelete(params).then((result) => {
              this.getOrderDetailData();
              this.$message.success({ content: result.data.msg, placement: 'top-right' });
            }).finally(() => {
              this.deleteVisible = false
            })
          } else {
            const params = {
              id: this.editObj.id,
            }
            orderReplyDelete(params).then((result) => {
              this.getOrderDetailData();
              this.$message.success({ content: result.data.msg, placement: 'top-right' });
            }).finally(() => {
              this.deleteVisible = false
            })
          }
        },
        // 提交回复
        submitReply() {
          this.sendLoading = true
          const conten = tinyMCE.editors[0].getContent()
          const attachmentList = [];
          this.attachmentList.forEach(item => {
            attachmentList.push(item.response.save_name);
          });
          const params = {
            id: this.orderDetailData.id,
            content: conten,
            attachment: attachmentList
          };
          replyUserOrder(this.orderDetailData.id, params).then(result => {
            tinyMCE.editors[0].setContent('')
            this.attachmentList = [];
            this.getOrderDetailData();
          }).catch(error => {
            this.$message.warning({ content: error.data.msg, placement: 'top-right' });
          }).finally(() => {
            this.sendLoading = false
          })
        },
        goback() {
          location.href = 'index.htm';
        },
        // 工单-转内部-关联产品变化
        hostChange() {
          this.$forceUpdate();
        },
        // 上传附件-返回内容
        uploadFormatResponse(res) {
          if (!res || (res.status !== 200)) {
            return { error: lang.upload_fail };
          }
          return { ...res, save_name: res.data.save_name };
        },
        // 修改工单状态
        handelEditOrderStatus() {
          if (this.params.status == '') {
            return this.$message.warning({ content: lang.order_text42, placement: 'top-right' });
          }
          const str = location.search.substr(1).split('&');
          const orderId = str[0].split('=')[1];
          const obj = {
            id: orderId,
            status: this.params.status,
            ticket_type_id: this.params.ticket_type,
            host_ids: this.params.selectHostList
          }
          editOrderStatus(obj).then((result) => {
            if (obj.status == 4) {
              this.goList()
            } else {
              this.$message.success({ content: result.data.msg, placement: 'top-right' });
              this.getOrderDetailData()
            }
          })
        },
        handelLog() {
          this.pagination.current = 1;
          this.pagination.pageSize = 10;
          this.getTicketLog()
          this.logVisible = true
        },
        // 上传附件-进度
        uploadProgress(val) {
          if (val.percent) {
            this.uploadTip = 'uploaded' + val.percent + '%';
            if (val.percent === 100) {
              this.uploadTip = '';
            }
          }
        },
        // 上传附件-成功后
        uploadSuccess(res) {
          if (res.fileList.filter(item => item.name == res.file.name).length > 1) {
            this.$message.warning({ content: lang.upload_same_name, placement: 'top-right' });
            this.attachmentList.splice(this.attachmentList.length - 1, 1);
          }
          this.$forceUpdate();
        },
        // 删除已上传附件
        removeAttachment(file, i) {
          this.attachmentList.splice(i, 1);
          this.$forceUpdate();
        },
        // 下载文件
        downFile(res, title) {
          let url = res.lastIndexOf('/');
          res = res.substring(url + 1, res.length);
          downloadFile({ name: res, }).then(function (response) {
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
        // 附件下载
        downloadfile(url) {
          const name = url
          const type = name.substring(name.lastIndexOf(".") + 1)
          if (['png', 'jpg', 'jepg', 'bmp', 'webp', 'PNG', 'JPG', 'JEPG', 'BMP', 'WEBP'].includes(type)) {
            this.preImg = url
            this.viewer.show()
          } else {
            const downloadElement = document.createElement("a");
            downloadElement.href = url;
            downloadElement.download = url.split("^")[1]; // 下载后文件名
            document.body.appendChild(downloadElement);
            downloadElement.click(); // 点击下载
          }
        },
        timeago(time) {
          if (time == 0) {
            return '--'
          }
          // time 毫秒
          const dateTimeStamp = time
          const minute = 1000 * 60;      //把分，时，天，周，半个月，一个月用毫秒表示
          const hour = minute * 60;
          const day = hour * 24;
          const week = day * 7;
          const month = day * 30;
          const year = month * 12;
          const now = new Date().getTime();   //获取当前时间毫秒
          const diffValue = now - dateTimeStamp;//时间差

          let result = "";
          if (diffValue < 0) {
            result = "" + lang.order_text43;
          }
          const minC = diffValue / minute;  //计算时间差的分，时，天，周，月
          const hourC = diffValue / hour;
          const dayC = diffValue / day;
          const weekC = diffValue / week;
          const monthC = diffValue / month;
          const yearC = diffValue / year;

          if (yearC >= 1) {
            result = " " + parseInt(yearC) + lang.order_text44
          } else if (monthC >= 1 && monthC < 12) {
            result = " " + parseInt(monthC) + lang.order_text45
          } else if (weekC >= 1 && weekC < 5 && dayC > 6 && monthC < 1) {
            result = " " + parseInt(weekC) + lang.order_text46
          } else if (dayC >= 1 && dayC <= 6) {
            result = " " + parseInt(dayC) + lang.order_text47
          } else if (hourC >= 1 && hourC <= 23) {
            result = " " + parseInt(hourC) + lang.order_text48
          } else if (minC >= 1 && minC <= 59) {
            result = " " + parseInt(minC) + lang.order_text49
          } else if (diffValue >= 0 && diffValue <= minute) {
            result = lang.order_text50
          }
          return result
        },
        // 工单日志
        getTicketLog() {
          const str = location.search.substr(1).split('&');
          const orderId = str[0].split('=')[1];
          ticketLog(orderId).then((res) => {
            this.logList = res.data.data.list
            this.pagination.total = res.data.data.list.length
          })
        },
        // 获取工单详情
        async getOrderDetailData() {
          this.pageLoading = true;
          const str = location.search.substr(1).split('&');
          const orderId = str[0].split('=')[1];
          const result = await getUserOrderDetail(orderId);
          if (result.status === 200) {
            this.orderDetailData = result.data.data.ticket;
            this.orderDetailData.distanceTime = this.timeago(this.orderDetailData.last_reply_time * 1000)
            this.getOrderTypeName();
            this.getHostsName();
            this.getTicketStatus()
            this.getTicketNotes()
          }
        },
        // 获取当前工单类型名称
        getOrderTypeName() {
          getUserOrderType().then(result => {
            const orderTypeList = result.data.data.list;
            this.orderTypeList = orderTypeList;
            const orderType = orderTypeList.filter(item => item.id === this.orderDetailData.ticket_type_id)[0];
            this.params.ticket_type = orderType ? orderType.id : null;
          });
        },
        // 获取当前用户关联产品名称
        getHostsName() {
          getHost({ client_id: this.orderDetailData.client_id, page: 1, limit: 10000 }).then(result => {
            const data = result.data.data.list;
            data.forEach((item) => {
              item.showName = item.product_name + '(' + item.name + ')'
            })
            this.hostList = data
            const arr = []
            this.product_obj_list = []
            this.orderDetailData.host_ids.forEach(id => {
              data.forEach((item) => {
                if (item.id == id) {
                  arr.push(item.id)
                  this.product_obj_list.push(item)
                }
              })
            });
            this.params.selectHostList = [...arr]
          });
        },
        // 获取工单状态列表
        getTicketStatus() {
          ticketStatus().then((res) => {
            res.data.data.list.forEach((item) => {
              // if (item['default'] === 1) {
              //   this.order_status.push(item.id)
              // }
              if (item.name === this.orderDetailData.status) {
                this.params.status = item.id
              }
              delete item['default']
            })
            this.order_status_options = res.data.data.list
          })
        },
        // 时间格式转换
        formatDate(dateStr) {
          const date = new Date(dateStr * 1000);
          const str1 = [date.getFullYear(), date.getMonth() + 1, date.getDate()].join('-');
          const str2 = [this.formatDateAdd0(date.getHours()), this.formatDateAdd0(date.getMinutes())].join(':');
          return str1 + ' ' + str2;
        },
        formatDateAdd0(m) {
          return m < 10 ? '0' + m : m;
        },
        initTemplate() {
          tinymce.init({
            selector: '#tiny',
            language_url: '/tinymce/langs/zh_CN.js',
            language: 'zh_CN',
            min_height: 400,
            width: '100%',
            plugins: 'link lists image code table colorpicker textcolor wordcount contextmenu fullpage',
            toolbar: 'bold italic underline strikethrough | fontsizeselect | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent blockquote | undo redo | link unlink image fullpage code | removeformat',
            images_upload_url: 'http://' + str + 'v1/upload',
            convert_urls: false,
            // images_upload_url: 'http://' + str + 'v1/upload',
            // images_upload_handler: function (blobInfo, success, failure) {
            //   // 上传图片
            //   const formData = new FormData()
            //   formData.append('image', blobInfo.blob(), blobInfo.filename())
            //   console.log('@@@@', formData)
            //   axios.post('http://' + str + 'v1/upload', formData, {
            //     'Content-Type': 'multipart/form-data',
            //     headers: {
            //       Authorization: 'Bearer' + ' ' + localStorage.getItem('backJwt')
            //     }
            //   }).then(res => {
            //     const json = {}
            //     if (res.status !== 200) {
            //       failure('HTTP Error: ' + res.msg)
            //       return
            //     }
            //     // json = JSON.parse(res)
            //     json.location = res.data.data

            //     if (!json || typeof json.location !== 'string') {
            //       failure('Invalid JSON: ' + res)
            //       return
            //     }
            //     success(json.location)
            //   })
            // }
            images_upload_handler: this.handlerAddImg
          });
        },
        handlerAddImg(blobInfo, success, failure) {
          return new Promise((resolve, reject) => {
            const formData = new FormData()
            formData.append('file', blobInfo.blob())
            axios.post(`${location.protocol}//${str}v1/upload`, formData, {
              headers: {
                Authorization: 'Bearer' + ' ' + localStorage.getItem('backJwt')
              }
            }).then(res => {
              const json = {}
              if (res.status !== 200) {
                failure('HTTP Error: ' + res.data.msg)
                return
              }
              // json = JSON.parse(res)
              json.location = res.data.data?.image_url
              if (!json || typeof json.location !== 'string') {
                failure('Error:' + res.data.msg)
                return
              }
              success(json.location)
            })
          })
        },
        mouseOver(val) {
          val.isShowBtn = true
          this.$forceUpdate();

        },
        mouseLeave(val) {
          val.isShowBtn = false
          this.$forceUpdate();
        },
        initViewer() {
          this.viewer = new Viewer(document.getElementById('viewer'), {
            button: true,
            inline: false,
            zoomable: true,
            title: true,
            tooltip: true,
            minZoomRatio: 0.5,
            maxZoomRatio: 100,
            movable: true,
            interval: 2000,
            navbar: true,
            loading: true,
          });
        }
      },
      created() {
        this.getOrderDetailData();
        this.getTicketLog()
        this.getTicketPrereply()
      },
      mounted() {
        this.initTemplate()
        this.initViewer()
      }
    }).$mount(template);
    typeof old_onload == 'function' && old_onload();
  };
})(window);