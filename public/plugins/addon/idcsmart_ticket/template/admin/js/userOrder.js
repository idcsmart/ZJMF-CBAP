(function(window, undefined) {
  var old_onload = window.onload;
  window.onload = function() {
    const template = document.getElementsByClassName('template')[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      data() {
        return {
          message: 'template...',
          activeTab: 'first',
          params: {
            keywords: '',
            page: 1,
            limit: 20,
            orderby: 'status',
            sort: 'desc'
          },
          total: 100,
          pageSizeOptions: [20, 50, 100],
          tableHeight: 500,
          // 工单转内部弹窗
          turnInsideDialogVisible: false,
          turnInsideFormData: {},
          turnInsideFormRules: {
            title: [
              { required: true, message: lang.order_title + lang.isRequired, type: 'error' },
              { validator: (val) => (val.length >= 1) && (val.length <= 150), message: lang.verify8 + '1-150',
                type: 'warning' }
            ],
            priority: [{ required: true, message: lang.order_priority + lang.isRequired, type: 'error' }],
            ticket_type_id: [{ required: true, message: lang.order_name + lang.isRequired, type: 'error' }],
            admin_role_id: [{ required: true, message: lang.order_designated_department + lang.isRequired,
              type: 'error' }],
            content: [{ validator: (val) => !val || (val.length <= 3000), message: lang.verify3 + '3000',
              type: 'warning' }]
          },
          // 指定部门下拉框数据（管理员分组列表数据）
          departmentOptions: [],
          // 指定人员下拉框数据（分组下管理员）
          adminsOptions: [],
          // 所有人员数据
          adminList: [],
          // 关联客户下拉框数据
          clientOptions: [],
          // 关联产品下拉框数据
          hostOptions: [],
          // 工单类型下拉框数据
          orderTypeOptions: [],
          // 紧急程度下拉框数据
          priorityOptions: [{
              id: 'medium',
              name: lang.order_priority_medium
            },
            {
              id: 'high',
              name: lang.order_priority_high
            }
          ],
          // 上传文件headers设置
          uploadHeaders: {
            Authorization: 'Bearer' + ' ' + localStorage.getItem('jwt')
          },
          uploadTip: '',
          // 用户工单列表
          userOrderTableloading: true,
          userOrderData: [],
          userOrderColumns: [{
              align: 'left',
              width: '180',
              colKey: 'ticket_num',
              title: lang.order_ticket_num
            },
            {
              align: 'left',
              width: '32%',
              colKey: 'title',
              title: lang.order_title,
              ellipsis: true
            },
            {
              align: 'left',
              width: '116',
              colKey: 'name',
              title: lang.order_name
            },
            {
              align: 'left',
              width: '207',
              colKey: 'post_time',
              title: lang.order_post_time
            },
            {
              align: 'left',
              width: '147',
              colKey: 'username',
              title: lang.order_username
            },
            {
              align: 'left',
              width: '116',
              colKey: 'hosts',
              title: lang.order_hosts,
              ellipsis: true
            },
            {
              align: 'left',
              width: '170',
              colKey: 'status',
              title: lang.order_status
            },
            {
              align: 'left',
              width: '120',
              colKey: 'operation',
              title: lang.operation,
              fixed: 'right',
            }
          ]
        };
      },
      methods: {
        // 获取工单类型数据
        getOrderTypeOptions() {
          getUserOrderType().then(result => {
            this.orderTypeOptions = result.data.data.list;
          }).catch();
        },
        // 获取客户数据
        getClientOptions() {
          getClient({ page: 1, limit: 10000 }).then(result => {
            this.clientOptions = result.data.data.list;
          }).catch();
        },
        // 获取部门数据
        getDepartmentOptions() {
          getAdminRole({ page: 1, limit: 10000 }).then(result => {
            this.departmentOptions = result.data.data.list;
          }).catch();
        },
        // 获取人员数据
        getAdminList() {
          getAdminList({ page: 1, limit: 10000 }).then(result => {
            this.adminList = result.data.data.list;
          }).catch();
        },
        // 工单-获取数据
        async getUserOrderList() {
          const userOrderData = await getUserOrder(this.params);
          if (userOrderData && userOrderData.data) {
            this.userOrderData = userOrderData.data.data.list;
            this.total = userOrderData.data.data.count;
            this.userOrderTableloading = false;
          }
        },
        // 工单-切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.params.keywords = '';
          this.getUserOrderList();
        },
        // 工单-查询
        doUserOrderSearch() {
          this.params.page = 1;
          this.getUserOrderList();
        },
        // 工单-列表-清空
        doUserOrderClear () {
          this.params.page = 1;
          this.params.keywords = '';
          this.getUserOrderList();
        },
        // 工单-转内部
        userOrderTurnInside(row) {
          this.turnInsideFormData = {};
          getUserOrderDetail(row.id).then(result => {
            const data = result.data.data.ticket;
            if(data.attachment&&data.attachment.length>0){
              data.attachment.forEach((item,i) => {
                data.attachment[i] = {response:{}};
                data.attachment[i].name = item.split('^')[1];
                data.attachment[i].response.save_name = item.split('upload/')[1];
              });
            }
            this.turnInsideFormData = { ...row, ...data };
            // const client = this.clientOptions.filter(item => item.username === row.username)[0];
            // this.turnInsideFormData.client_id = client ? client.id : null;
            
            if (this.turnInsideFormData.client_id) {
              this.clientChange(this.turnInsideFormData.client_id, true);
            }
            if (!this.turnInsideFormData.ticket_type_id) {
              const orderType = this.orderTypeOptions.filter(item => item.name === this.turnInsideFormData
                .name)[0];
              this.turnInsideFormData.ticket_type_id = orderType ? orderType.id : null;
            }
            this.orderTypeChange(this.turnInsideFormData.ticket_type_id);
            // this.turnInsideFormData.attachment = [];
            this.turnInsideDialogVisible = true;
          }).catch(error => {
            console.log(error);
          });
        },
        // 工单-转内部-关联用户变化
        clientChange(val, isFirst) {
          if(!isFirst){
            // 清除已选产品数据
            this.turnInsideFormData.host_ids = [];
          }
          getHost({ client_id: val, page: 1, limit: 10000 }).then(result => {
            this.hostOptions = result.data.data.list;
            this.hostChange();
          });
        },
        // 工单-转内部-关联产品变化
        hostChange() {
          this.$forceUpdate();
        },
        // 工单-转内部-选择部门变化
        departmentChange(val) {
          // 获取部门id对应部门名称
          const department = this.departmentOptions.filter(item => item.id === val)[0];
          const name = department ? department.name : null;
          const optionList = [];
          // 清除已选人员数据
          this.turnInsideFormData.admin_id = null;
          this.adminList.forEach(item => {
            if (name && item.roles === name) {
              optionList.push(item);
            }
          });
          this.adminsOptions = optionList;
        },
        // 工单-转发-选择人员变化
        adminChange(val) {
          this.$forceUpdate();
        },
        // 工单-转内部-工单类型变化
        orderTypeChange(val) {
          // 获取当前所选数据工单类型对应部门名称
          const type = this.orderTypeOptions.filter(item => item.id === val)[0];
          const admin_role_name = type ? type.role_name : null;
          if (admin_role_name) {
            // 默认设置部门为工单类型对应的部门
            const data = this.departmentOptions.filter(item => item.name && item.name === admin_role_name)[0];
            this.turnInsideFormData.admin_role_id = data && data.id ? data.id : null;
            // 获取该部门下人员列表
            this.departmentChange(this.turnInsideFormData.admin_role_id);
          }
          // 清除已选人员数据
          this.turnInsideFormData.admin_id = null;
        },
        // 工单-转内部-上传附件-返回
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
          if (res.fileList.filter(item => item.name == res.file.name).length > 1) {
            this.$message.warning({ content: lang.upload_same_name, placement: 'top-right' });
            this.turnInsideFormData.attachment.splice(this.turnInsideFormData.attachment.length - 1, 1);
          }
          this.$forceUpdate();
        },
        removeAttachment(file, i) {
          this.turnInsideFormData.attachment.splice(i, 1);
          this.$forceUpdate();
        },
        // 工单-转内部-提交
        turnInsideFormSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            const data = this.turnInsideFormData;
            const attachmentList = [];
            data.attachment.forEach(item => {
              attachmentList.push(item.response.save_name);
            });
            const params = {
              ticket_id: data.id, //工单ID(转内部工单时需要传此参数)
              title: data.title, //内部工单标题
              ticket_type_id: data.ticket_type_id, //内部工单类型ID
              priority: data.priority, //紧急程度:medium一般,high紧急
              client_id: data.client_id ? data.client_id : null, //关联用户
              admin_role_id: data.admin_role_id, //指定部门
              admin_id: data.admin_id ? data.admin_id : null, //管理员ID
              host_ids: data.host_ids ? data.host_ids : [], //关联产品ID,数组
              content: data.content ? data.content : '', //问题描述
              attachment: attachmentList, //附件,数组,取上传文件返回值save_name)
            };
            newInternalOrder(params).then(result => {
              this.$message.success({ content: result.data.msg, placement: 'top-right' });
              this.turnInsideDialogClose();
              this.getUserOrderList();
            }).catch(result => {
              this.$message.warning({ content: result.data.msg, placement: 'top-right' });
            });
          } else {
            this.$message.warning({ content: firstError, placement: 'top-right' });
          }
        },
        // 工单-转内部-弹窗关闭
        turnInsideDialogClose() {
          this.turnInsideDialogVisible = false;
        },
        // 工单-接收
        userOrderReceive(row) {
          receiveUserOrder(row.id).then(result => {
            this.$message.success({ content: result.data.msg, placement: 'top-right' });
            this.getUserOrderList();
          }).catch(result => {
            this.$message.warning({ content: result.data.msg, placement: 'top-right' });
          });
        },
        // 工单-回复
        userOrderReply(row) {
          location.href = `ticket_detail.html?id=${row.id}`;
        },
        // 工单-已解决
        userOrderResolved(row) {
          resolvedUserOrder(row.id).then(result => {
            this.$message.success({ content: result.data.msg, placement: 'top-right' });
            this.getUserOrderList();
          }).catch(result => {
            this.$message.warning({ content: result.data.msg, placement: 'top-right' });
          });
        },
        // 时间格式转换
        formatDate(dateStr) {
          const date = new Date(dateStr * 1000);
          const str1 = [date.getFullYear(), date.getMonth() + 1, date.getDate()].join('-');
          const str2 = [this.formatDateAdd0(date.getHours()), this.formatDateAdd0(date.getMinutes()), this
            .formatDateAdd0(date.getSeconds())
          ].join(':');
          return str1 + ' ' + str2;
        },
        formatDateAdd0(m) {
          return m < 10 ? '0' + m : m;
        }
      },
      created() {
        localStorage.setItem('curValue', 253)
        const domHeight = template.scrollHeight;
        this.tableHeight = domHeight - 230;
        this.getUserOrderList();
        this.getOrderTypeOptions();
        this.getDepartmentOptions();
        this.getAdminList();
        this.getClientOptions();
        window.doUserOrderSearch = this.doUserOrderSearch();
      },
    }).$mount(template);
    typeof old_onload == 'function' && old_onload();
  };
})(window);
