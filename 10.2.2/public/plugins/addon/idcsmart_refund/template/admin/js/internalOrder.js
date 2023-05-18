(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName('template')[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      data () {
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
          // 上传文件headers设置
          uploadHeaders: {
            Authorization: 'Bearer' + ' ' + localStorage.getItem('jwt')
          },
          uploadTip: '',
          // 转发弹窗
          forwardDialogVisible: false,
          forwardFormData: {},
          forwardFormRules: {
            admin_role_id: [{ required: true, message: lang.order_designated_department+lang.isRequired, type: 'error' }],
          },
          
          // 新建工单弹窗
          addOrderDialogVisible: false,
          addOrderFormData: {},
          addOrderFormRules: {
            title: [
              { required: true, message: lang.order_title+lang.isRequired, type: 'error' },
              { validator: (val) => (val.length >= 1) && (val.length <= 150), message: lang.verify8+'1-150', type: 'warning' }
            ],
            priority: [{ required: true, message: lang.order_priority+lang.isRequired, type: 'error' }],
            ticket_type_id: [{ required: true, message: lang.order_name+lang.isRequired, type: 'error' }],
            admin_role_id: [{ required: true, message: lang.order_designated_department+lang.isRequired, type: 'error' }],
            content: [{ validator: (val) => !val || (val.length <= 3000), message: lang.verify3+'3000', type: 'warning' }]
          },
          
          // 工单类型管理弹窗
          orderTypeMgtDialogVisible: false,
          orderTypeMgtTableloading: false,
          orderTypeEditRow: {},
          orderTypeMgtData: [],
          orderTypeMgtColumns: [
            {
              align: 'left',
              width: 50,
              colKey: 'index',
              title: lang.order_index
            },
            {
              align: 'left',
              width: 130,
              colKey: 'name',
              title: lang.order_type_name
            },
            {
              align: 'left',
              width: 180,
              colKey: 'role_name',
              title: lang.order_default_receive_department
            },
            {
              align: 'left',
              width: 100,
              colKey: 'operation',
              title: lang.operation,
              fixed: 'right',
            }
          ],
          
          // 工单类型下拉框数据
          orderTypeOptions: [],
          // 关联客户下拉框数据
          clientOptions: [],
          // 关联产品下拉框数据
          hostOptions: [],
          // 指定部门下拉框数据（管理员分组列表数据）
          departmentOptions: [],
          // 指定人员下拉框数据（分组下管理员）
          adminsOptions: [],
          // 所有人员数据
          adminList: [],
          // 紧急程度下拉框数据
          priorityOptions: [
            {
              id: 'medium',
              name: lang.order_priority_medium
            },
            {
              id: 'high',
              name: lang.order_priority_high
            }
          ],
          // 内部工单列表
          internalOrderTableloading: true,
          internalOrderData: [],
          internalOrderColumns: [
            {
              align: 'left',
              width: '180',
              colKey: 'ticket_num',
              title: lang.order_ticket_num
            },
            {
              align: 'left',
              colKey: 'title',
              title: lang.order_title,
              ellipsis:true
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
              title: lang.order_poster
            },
            {
              align: 'left',
              width: '116',
              colKey: 'hosts',
              title: lang.order_hosts,
              ellipsis:true
            },
            {
              align: 'left',
              width: '116',
              colKey: 'priority',
              title: lang.order_priority
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
        getOrderTypeOptions () {
          getUserOrderType().then(result => {
            this.orderTypeOptions = result.data.data.list;
          }).catch();
        },
        // 获取客户数据
        getClientOptions () {
          getClient({page:1,limit:10000}).then(result => {
            this.clientOptions = result.data.data.list;
          }).catch();
        },
        // 选择客户变化
        clientChange (val) {
          // 清除已选产品数据
          this.addOrderFormData.host_ids = [];
          getHost({client_id: val, page:1, limit:10000}).then(result => {
            this.hostOptions = result.data.data.list;
          }).catch();
        },
        // 选择产品变化
        hostChange () {
          this.$forceUpdate();
        },
        // 获取部门数据
        async getDepartmentOptions () {
          const result = await getAdminRole({page:1,limit:10000});
          if (result.status === 200) {
            this.departmentOptions = result.data.data.list;
          }
        },
        // 选择部门变化
        departmentChange (val) {
          // 获取部门id对应部门名称
          const department = this.departmentOptions.filter(item=>item.id === val)[0];
          const name = department?department.name:null;
          const optionList = [];
          // 清除已选人员数据
          this.addOrderFormData.admin_id = '';
          this.forwardFormData.admin_id = '';
          this.adminList.forEach(item => {
            if(name && item.roles === name){
              optionList.push(item);
            }
          });
          this.adminsOptions = optionList;
        },
        // 获取人员数据
        getAdminList () {
          getAdminList({page:1,limit:10000}).then(result => {
            this.adminList = result.data.data.list;
          }).catch();
        },
        // 选择人员变化
        adminChange (val) {
          this.$forceUpdate();
        },
        
        // 工单-列表-获取数据
        async getInternalOrderList () {
          const internalOrderData = await getInternalOrder(this.params);
          if (internalOrderData && internalOrderData.data) {
            this.internalOrderData = internalOrderData.data.data.list;
            this.total = internalOrderData.data.data.count;
            this.internalOrderTableloading = false;
          }
        },
        // 工单-列表-切换分页
        changePage (e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.params.keywords = '';
          this.getInternalOrderList();
        },
        // 工单-列表-查询
        doInternalOrderSearch () {
          this.params.page = 1;
          this.getInternalOrderList();
        },
        // 工单-列表-清空
        doInternalOrderClear () {
          this.params.page = 1;
          this.params.keywords = '';
          this.getInternalOrderList();
        },
        
        // 工单-接收
        internalOrderReceive (row) {
          receiveInternalOrder(row.id).then(result => {
            this.$message.success({ content: result.data.msg, placement: 'top-right' });
            this.getInternalOrderList();
          }).catch(result => {
            this.$message.warning({ content: result.data.msg, placement: 'top-right' });
          });
        },
        
        // 工单-已解决
        internalOrderResolved (row) {
          resolvedInternalOrder(row.id).then(result => {
            this.$message.success({ content: result.data.msg, placement: 'top-right' });
            this.getInternalOrderList();
          }).catch(result => {
            this.$message.warning({ content: result.data.msg, placement: 'top-right' });
          });
        },
        
        // 工单-转发
        internalOrderForward (row) {
          this.getAdminList();
          this.forwardFormData = {};
          this.forwardFormData = {id: row.id};
          this.forwardDialogVisible = true;
        },
        // 工单-转发-提交
        forwardFormSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            const data = this.forwardFormData;
            const params = {
              id: data.id,
              admin_role_id: data.admin_role_id,//指定部门
              admin_id: data.admin_id?data.admin_id:null,//管理员
            };
            forwardInternalOrder(data.id, params).then(result => {
              this.$message.success({ content: result.data.msg, placement: 'top-right' });
              this.forwardDialogClose();
              this.getInternalOrderList();
            }).catch(result => {
              this.$message.warning({ content: result.data.msg, placement: 'top-right' });
            });
          } else {
            this.$message.warning({ content: firstError, placement: 'top-right' });
          }
        },
        // 工单-转发-弹窗关闭
        forwardDialogClose () {
          this.forwardDialogVisible = false;
        },
        
        // 工单-新建工单-弹窗显示
        newOrderDialogShow () {
          this.addOrderFormData = {};
          this.addOrderFormData.attachment=[];
          this.getClientOptions();
          this.getOrderTypeOptions();
          this.getAdminList();
          this.addOrderDialogVisible = true;
        },
        // 工单-新建工单-上传文件返回内容
        uploadFormatResponse(res) {
          if (!res || (res.status !== 200)) {
            return { error: res.msg };
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
            this.addOrderFormData.attachment.splice(this.addOrderFormData.attachment.length-1,1);
          }
          this.$forceUpdate();
        },
        removeAttachment(file,i){
          this.addOrderFormData.attachment.splice(i,1);
          this.$forceUpdate();
        },
        // 工单-新建工单-工单类型变化
        orderTypeChange (val) {
          // 获取当前所选数据工单类型对应部门名称
          const orderType = this.orderTypeOptions.filter(item=>item.id === val)[0];
          const admin_role_name = orderType?orderType.role_name:null;
          if(admin_role_name){
            // 默认设置部门为工单类型对应的部门
            const department = this.departmentOptions.filter(item=>item.name === admin_role_name)[0];
            this.addOrderFormData.admin_role_id = department?department.id:null;
            // 获取该部门下人员列表
            this.departmentChange (this.addOrderFormData.admin_role_id);
          }
          // 清除已选人员数据
          this.addOrderFormData.admin_id = null;
        },
        // 工单-新建工单-提交
        addOrderFormSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            const data = this.addOrderFormData;
            const attachmentList = [];
            data.attachment.forEach(item => {
              attachmentList.push(item.response.save_name);
            });
            const params = {
              title: data.title,//内部工单标题
              ticket_type_id: data.ticket_type_id,//内部工单类型ID
              priority: data.priority,//紧急程度:medium一般,high紧急
              client_id: data.client_id?data.client_id:null,//关联用户
              admin_role_id: data.admin_role_id,//指定部门
              admin_id: data.admin_id?data.admin_id:null,//管理员ID
              host_ids: data.host_ids?data.host_ids:[],//关联产品ID,数组
              content: data.content?data.content:'',//问题描述
              attachment: attachmentList,//附件,数组,取上传文件返回值save_name)
            };
            newInternalOrder(params).then(result => {
              this.$message.success({ content: result.data.msg, placement: 'top-right' });
              this.addOrderDialogClose();
              this.getInternalOrderList();
            }).catch(result => {
              this.$message.warning({ content: result.data.msg, placement: 'top-right' });
            });
          } else {
            this.$message.warning({ content: firstError, placement: 'top-right' });
          }
        },
        // 工单-新建工单-弹窗关闭
        addOrderDialogClose () {
          this.addOrderDialogVisible = false;
        },
        
        // 工单-工单类型管理-弹窗显示
        orderTypeMgtDialogShow () {
          this.orderTypeMgtTableloading = true;
          this.getOrderTypeMgtList();
          this.orderTypeMgtDialogVisible = true;
        },
        // 工单-工单类型管理-获取工单类型数据
        async getOrderTypeMgtList () {
          const result = await getUserOrderType();
          if (result.status === 200) {
            const data = result.data.data.list;
            data.forEach(item => {
              if(item.role_name){
                const department = this.departmentOptions.filter(op=>op.name === item.role_name)[0];
                item.admin_role_id = department?department.id:null;
              }
            });
            this.orderTypeMgtData = data;
            this.orderTypeMgtTableloading = false;
          }
        },
        // 工单-工单类型管理-校验当前数据是否都已保存
        checkOrderType () {
          let result = true;
          this.orderTypeMgtData.forEach(item => {
            if(item.status === 'edit' || item.status === 'add'){
              this.$message.warning({ content: lang.order_type_verify3, placement: 'top-right' });
              result = false;
            }
          });
          return result;
        },
        // 工单-工单类型管理-新增
        newOrderType () {
          const checkResult = this.checkOrderType();
          if(checkResult){
            this.orderTypeMgtData.push({
              status: 'add',
              id: Math.random(),
              role_name: null,
              name: ''
            });
          }
        },
        // 工单-工单类型管理-编辑
        orderTypeMgtEdit (row) {
          const checkResult = this.checkOrderType();
          if(checkResult){
            for(let i=0;i<this.orderTypeMgtData.length;i++){
              if(this.orderTypeMgtData[i].id===row.id){
                this.orderTypeMgtData.splice(i,1,{
                  status: 'edit',
                  ...row
                });
              }
            }
          }
        },
        // 工单-工单类型管理-保存
        async orderTypeMgtSave(row) {
          if(!row.role_name || !row.name){
            this.$message.warning({ content: lang.order_type_verify1, placement: 'top-right' });
            return;
          }
          const params = {
            admin_role_id: row.admin_role_id,
            name: row.name
          };
          let result = {};
          if (row.status === 'edit') {
            params.id = row.id;
            orderTypeEdit(row.id, params).then(result => {
              this.$message.success({ content: result.data.msg, placement: 'top-right' });
              this.getOrderTypeMgtList();
            }).catch(result => {
              this.$message.warning({ content: result.data.msg, placement: 'top-right' });
            });
          } else {
            orderTypeAdd(params).then(result => {
              this.$message.success({ content: result.data.msg, placement: 'top-right' });
              this.getOrderTypeMgtList();
            }).catch(result => {
              this.$message.warning({ content: result.data.msg, placement: 'top-right' });
            });
          }
        },
        // 工单-工单类型管理-取消编辑
        orderTypeMgtCancel (row) {
          for(let i=0;i<this.orderTypeMgtData.length;i++){
            if(this.orderTypeMgtData[i].id===row.id){
              if(row.status === 'add'){
                this.orderTypeMgtData.splice(i, 1);
              }else{
                this.orderTypeMgtData[i].status = null;
                this.orderTypeMgtData.splice(i,1,this.orderTypeMgtData[i]);
                this.getOrderTypeMgtList();
              }
            }
          }
        },
        // 工单-工单类型管理-删除
        async orderTypeMgtDelete (row) {
          const result = await orderTypeDelete(row.id);
          if (result.status === 200) {
            this.$message.success({ content: result.data.msg, placement: 'top-right' });
            this.getOrderTypeMgtList();
          } else {
            this.$message.warning({ content: result.data.msg, placement: 'top-right' });
          }
        },
        
        // 工单-回复
        internalOrderReply (row) {
          location.href = `internalOrderReply.html?id=${row.id}`;
        },
        // 时间格式转换
        formatDate (dateStr) {
          const date = new Date(dateStr * 1000);
          const str1 = [date.getFullYear(), date.getMonth()+1, date.getDate()].join('-');
          const str2 = [this.formatDateAdd0(date.getHours()), this.formatDateAdd0(date.getMinutes()), this.formatDateAdd0(date.getSeconds())].join(':');
          return str1 + ' ' + str2;
        },
        formatDateAdd0 (m) {
          return m<10?'0'+m:m;
        }
      },
      created () {
        const domHeight = template.scrollHeight;
        this.tableHeight = domHeight - 230;
        this.getInternalOrderList();
        this.getDepartmentOptions();
      },
    }).$mount(template);
    typeof old_onload == 'function' && old_onload();
  };
})(window);
