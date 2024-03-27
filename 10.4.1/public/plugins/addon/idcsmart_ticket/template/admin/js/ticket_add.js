(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName('template')[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    const host = location.host
    const fir = location.pathname.split('/')[1]
    const str = `${host}/${fir}/`
    new Vue({
      components: {
        comConfig,
        comTinymce,
        comChooseUser
      },
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
            overlayInnerStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
              'max-height': '362px'
            }),
          },
          requiredRules: {
            title: [{ required: true, message: lang.order_text15 }],
            ticket_type_id: [{ required: true, message: lang.order_name }],
            client_id: [{ required: true, message: lang.order_text17 }],
          },
        }
      },
      methods: {
        filterMethod() {
          return true;
        },
        changeUser (val) { // 选择用户， 获取对应的产品
          this.$set(this.detialform, 'client_id', val)
          this.clientChange(val)
        },

        departmentChange(val) {
          this.getOrderTypeOptions(val)
          this.detialform.ticket_type_id = ''
        },
        addOrderFormSubmit() {
          this.detialform.content = this.$refs.comTinymce.getContent();
          // this.detialform.notes = tinyMCE.editors[1].getContent();
          this.$refs.myform.validate(this.requiredRules).then((res) => {
            if (res !== true) {
              const firstError = Object.values(res)[0][0].message
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
    }).$mount(template);
    typeof old_onload == 'function' && old_onload();
  };
})(window);
