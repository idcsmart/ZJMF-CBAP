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
          isSubmitIng: false,
          detialform: {
            host_ids: [],
            ticket_type_id: '',
          },
          userParams: {
            keywords: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          userTotal: 0,
          searchLoading: false,
          departmentList: [],
          orderTypeOptions: [],
          // 关联客户下拉框数据
          clientOptions: [],
          // 关联产品下拉框数据
          hostOptions: [],
          popupProps: {
            overlayStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
              'max-height': '362px'
            }),
          },
          requiredRules: {
            title: [{ required: true, message: lang.order_text15 }],
            ticket_type_id: [{ required: true, message: lang.order_text16 }],
            client_id: [{ required: true, message: lang.order_text17 }],
          },
        }
      },
      methods: {
        initTemplate() {
          tinymce.init({
            selector: '#tiny1',
            language_url: '/tinymce/langs/zh_CN.js',
            language: 'zh_CN',
            min_height: 400,
            width: '100%',
            plugins: 'link lists image code table colorpicker textcolor wordcount contextmenu fullpage',
            toolbar: 'bold italic underline strikethrough | fontsizeselect | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent blockquote | undo redo | link unlink image fullpage code | removeformat',
            images_upload_url: 'http://' + str + 'v1/upload',
            convert_urls: false,

            // }
            images_upload_handler: this.handlerAddImg
          });

        },
        filterMethod() {
          return true;
        },
        handleScrollToBottom({ e }) {
          const { scrollTop, clientHeight, scrollHeight } = e.target;
          if (scrollHeight - scrollTop === clientHeight) {
            if (this.clientOptions.length < this.userTotal) {
              this.userParams.page++;
              this.getClientOptions();
            }
          }
        },
        // 远程搜素
        remoteMethod(key) {
          this.searchLoading = true
          this.userParams.keywords = key
          this.userParams.page = 1
          this.getClientOptions()
        },
        clearKey() {
          this.searchLoading = true
          this.userParams.keywords = ''
          this.userParams.page = 1
          this.getClientOptions()
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
        departmentChange(val) {
          this.getOrderTypeOptions(val)
          this.detialform.ticket_type_id = ''
        },
        addOrderFormSubmit() {
          this.detialform.content = tinyMCE.editors[0].getContent();
          // this.detialform.notes = tinyMCE.editors[1].getContent();
          this.$refs.myform.validate(this.requiredRules).then((res) => {
            if (res !== true) {
              const firstError = Object.values(res)[0][0].message
              console.log(firstError);
              this.$message.warning({ content: firstError, placement: 'top-right' });
              return
            }
            this.isSubmitIng = true
            const data = this.detialform;
            const attachmentList = [];
            const params = {
              title: data.title, //工单标题
              ticket_type_id: data.ticket_type_id, //工单类型ID
              client_id: data.client_id ? data.client_id : null, //关联用户
              host_ids: data.host_ids ? data.host_ids : [], //关联产品ID,数组
              content: data.content ? data.content : '', //问题描述
              notes: data.notes ? data.notes : '',
              attachment: attachmentList, //附件,数组,取上传文件返回值save_name)
            };
            newUserOrder(params).then(result => {
              this.isSubmitIng = false
              this.$message.success({ content: result.data.msg, placement: 'top-right' });
              this.goList()
            }).catch(result => {
              this.$message.warning({ content: result.data.msg, placement: 'top-right' });
              this.isSubmitIng = false
            });
          })

        },
        // 获取工单类型数据
        getOrderTypeOptions(id) {
          const params = {
            admin_role_id: id ? id : ''
          }
          getUserOrderType(params).then(result => {
            this.orderTypeOptions = result.data.data.list;
          }).catch();
        },
        // 获取用户列表
        async getClientOptions() {
          try {
            const { data: { data } } = await getClient(this.userParams)
            this.userTotal = data.count
            if (this.userParams.page === 1) {
              this.clientOptions = data.list
            } else {
              this.clientOptions = this.clientOptions.concat(...data.list)
            }
            this.searchLoading = false
          } catch (error) {
            this.searchLoading = false
          }
        },
        // 工单-转内部-关联用户变化
        clientChange(val) {
          // 清除已选产品数据
          this.detialform.host_ids = []
          getHost({ client_id: val, page: 1, limit: 10000 }).then(result => {
            result.data.data.list.forEach((item) => {
              item.showName = item.product_name + '(' + item.name + ')'
            })
            this.hostOptions = result.data.data.list;

            this.hostChange();
          });
        },
        // 工单-转内部-关联产品变化
        hostChange() {
          this.$forceUpdate();
        },
        // 获取工单部门
        getTicketDepartment() {
          ticketDepartment().then((res) => {
            this.departmentList = res.data.data.list
          })
        },
        // 返回
        goList() {
          location.href = 'index.htm'
        }
      },
      created() {
        this.getOrderTypeOptions()
        this.getClientOptions()
        this.getTicketDepartment()
      },
      mounted() {
        this.initTemplate()
      }
    }).$mount(template);
    typeof old_onload == 'function' && old_onload();
  };
})(window);
