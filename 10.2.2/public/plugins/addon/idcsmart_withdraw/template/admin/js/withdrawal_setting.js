(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('withdrawal-setting')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = window.moment
    new Vue({
      data () {
        return {
          tableLayout: true,
          bordered: true,
          visible: false,
          delVisible: false,
          statusVisble: false,
          hover: true,
          virtualScroll: false,
          columns: [
            {
              colKey: 'name',
              title: lang.withdrawal_way
            },
            {
              colKey: 'admin',
              title: lang.addition,
              width: 390
            },
            {
              colKey: 'create_time',
              title: lang.add_time,
              width: 390
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 100,
              ellipsis: true
            },
          ],
          columns1: [
            {
              colKey: 'reason',
              title: lang.dismiss_the_reason,
            },
            {
              colKey: 'admin',
              title: lang.addition,
              width: 390
            },
            {
              colKey: 'create_time',
              title: lang.add_time,
              width: 390
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 100,
              ellipsis: true
            },
          ],
          hideSortTips: true,
          formData: {
            name: '',
            reason: ''
          },
          rules: {
            name: [
              { required: true, message: lang.input + lang.withdrawal_way, type: 'error' },
              { validator: val => val.length <= 100, message: lang.verify3 + 100, type: 'warning' }
            ],
            reason: [
              { required: true, message: lang.input + lang.dismiss_the_reason, type: 'error' },
              { validator: val => val.length <= 100, message: lang.verify3 + 100, type: 'warning' }
            ]
          },
          wayList: [],
          rejectList: [],
          loading: false,
          rejectLoading: false,
          optType: '', // way, reject
          optWay: '', // add,update
          curId: '',
          diaTitle: '',
          submitLoading: false
        }
      },
      created () {
        this.getMethods()
        this.getRejects()
      },
      methods: {
        // 获取列表
        async getMethods () {
          try {
            this.loading = true
            const res = await getWithdrawWay()
            this.loading = false
            this.wayList = res.data.data.list
          } catch (error) {
            this.loading = false
          }
        },
        // 获取列表
        async getRejects () {
          try {
            this.rejectLoading = true
            const res = await getRejectReason()
            this.rejectLoading = false
            this.rejectList = res.data.data.list
          } catch (error) {
            this.rejectLoading = false
          }
        },
        // 添加
        addItem (type) {
          this.optType = type
          this.optWay = 'add'
          this.visible = true
          this.formData.name = ''
          this.formData.reason = ''
          this.diaTitle = type === 'way' ? `${lang.add}${lang.withdrawal_way}` : `${lang.add}${lang.dismiss_the_reason}`
        },
        // 编辑
        editItem (row, type) {
          this.optType = type
          this.optWay = 'update'
          this.visible = true
          this.formData = JSON.parse(JSON.stringify(row))
          this.diaTitle = type === 'way' ? `${lang.edit}${lang.withdrawal_way}` : `${lang.edit}${lang.dismiss_the_reason}`
        },
        // 删除
        delItem (row, type) {
          this.optType = type
          this.curId = row.id
          this.delVisible = true
        },
        // 提交
        onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const params = JSON.parse(JSON.stringify(this.formData))
              if (this.optWay === 'add') {
                delete params.id
              }
              this.submitLoading = true
              if (this.optType === 'way') {
                delete params.reason
                this.submitWay(params)
              } else {
                delete params.name
                this.submitReject(params)
              }
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        // 添加/修改提现方式
        async submitWay (params) {
          try {
            const res = await andAndUpdateWithdrawWay(this.optWay, params)
            this.$message.success(res.data.msg)
            this.submitLoading = false
            this.visible = false
            this.getMethods()
          } catch (error) {
            this.submitLoading = false
            this.$message.error(error.data.msg)
          }
        },
        // 添加/修改驳回原因
        async submitReject (params) {
          try {
            const res = await andAndUpdateRejectReason(this.optWay, params)
            this.$message.success(res.data.msg)
            this.submitLoading = false
            this.visible = false
            this.getRejects()
          } catch (error) {
            this.submitLoading = false
            this.$message.error(error.data.msg)
          }
        },
        async sureDel () {
          try {
            if (this.optType === 'way') {
              const res = await delWithdrawWay(this.curId)
              this.$message.success(res.data.msg)
              this.getMethods()
              this.delVisible = false
            } else {
              const result = await delRejectReason(this.curId)
              this.$message.success(result.data.msg)
              this.getRejects()
              this.delVisible = false
            }
          } catch (error) {

          }
        },
        close () {
          this.delVisible = false
        }
      },

    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
