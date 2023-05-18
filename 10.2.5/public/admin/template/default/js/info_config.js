(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('feedback')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = window.moment
    const host = location.origin + '/' + location.pathname.split('/')[1]
    new Vue({
      data () {
        return {
          hover: true,
          tableLayout: false,
          delVisible: false,
          loading: false,
          systemGroup: [],
          classModel: false,
          classParams: {
            name: '',
            url: '',
            img: '',
            description: '',
            qrcode: []
          },
          list: [],
          linkColumns: [
            {
              colKey: 'name',
              title: lang.nickname,
              ellipsis: true
            },
            {
              colKey: 'url',
              title: lang.feed_link,
              ellipsis: true,
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 100
            },
          ],
          honorColumns: [
            {
              colKey: 'img',
              title: lang.picture,
              ellipsis: true,
            },
            {
              colKey: 'name',
              title: lang.nickname,
              ellipsis: true
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 100
            },
          ],
          partnerColumns: [
            {
              colKey: 'img',
              title: lang.picture,
              ellipsis: true,
            },
            {
              colKey: 'name',
              title: lang.nickname,
              ellipsis: true
            },
            {
              colKey: 'description',
              title: lang.description,
              ellipsis: true
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 100
            },
          ],
          groupColumns: [ // 套餐表格
            {
              colKey: 'image_group_name',
              title: lang.type_manage,
              ellipsis: true
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 100
            },
          ],
          typeRules: {
            icp_info: [
              { required: true, message: lang.input + lang.icp_info, type: 'error' },
            ],
            icp_info_link: [
              { required: true, message: lang.input + lang.jump_link, type: 'error' },
              {
                pattern: /(https?|ftp|file):\/\/[-A-Za-z0-9+&@#/%?=~_|!:,.;]+[-A-Za-z0-9+&@#/%=~_|]/, message: lang.feed_tip, type: 'warning'
              }
            ],
            public_security_network_preparation: [
              { required: true, message: lang.input + lang.put_on_record, type: 'error' },
            ],
            public_security_network_preparation_link: [
              { required: true, message: lang.input + lang.jump_link, type: 'error' },
              {
                pattern: /(https?|ftp|file):\/\/[-A-Za-z0-9+&@#/%?=~_|!:,.;]+[-A-Za-z0-9+&@#/%=~_|]/, message: lang.feed_tip, type: 'warning'
              }
            ],
            telecom_appreciation: [
              { required: true, message: lang.input + lang.telecom_value, type: 'error' },
            ],
            copyright_info: [
              { required: true, message: lang.input + lang.copyright, type: 'error' },
            ],
            put_on_record: [
              { required: true, message: lang.input + lang.put_on_record, type: 'error' },
            ],
            enterprise_name: [
              { required: true, message: lang.input + lang.enterprise_name, type: 'error' },
            ],
            enterprise_telephone: [
              { required: true, message: lang.input + lang.enterprise_telephone, type: 'error' },
            ],
            enterprise_mailbox: [
              { required: true, message: lang.input + lang.enterprise_mailbox, type: 'error' },
            ],
            online_customer_service_link: [
              { required: true, message: lang.input + lang.online_customer_service_link, type: 'error' },
            ],
            qrcode: [
              { required: true, message: lang.attachment + lang.enterprise_qrcode, type: 'error' },
            ],
            logo: [
              { required: true, message: lang.attachment + lang.web_logo, type: 'error', trigger: 'change' },
            ]
          },
          popupProps: {
            overlayClassName: `custom-select`,
            overlayStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` }),
          },
          submitLoading: false,
          // 反馈详情
          detailModel: false,
          params: {
            keywords: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          total: 0,
          pageSizeOptions: [20, 50, 100],
          infoParams: {
            icp_info: '',
            icp_info_link: '',
            public_security_network_preparation: '',
            public_security_network_preparation_link: '',
            telecom_appreciation: '',
            copyright_info: '',
            enterprise_name: '',
            enterprise_telephone: '',
            enterprise_mailbox: '',
            online_customer_service_link: '',
            qrcode: [],
            logo: []
          },
          // 图片上传相关
          uploadUrl: host + '/v1/upload',
          // uploadUrl: 'https://kfc.idcsmart.com/admin/v1/upload',
          uploadHeaders: {
            Authorization: "Bearer" + " " + localStorage.getItem("backJwt"),
          },
          // 友情链接
          friendly_link_list: [],
          honor_list: [],
          partner_list: [],
          friendly_link_loading: false,
          honor_loading: false,
          partner_loading: false,
          infoTit: '',
          calcType: '', // friendly_link honor partner
        }
      },
      computed: {
      },
      created () {
        this.getConfigInfo()
        this.getInfoList('friendly_link')
        this.getInfoList('honor')
        this.getInfoList('partner')
        document.title = lang.info_config + '-' + localStorage.getItem('back_website_name')
      },
      methods: {
        // 获取反馈
        async getConfigInfo () {
          try {
            const res = await getConfigInfo(this.params)
            const temp = res.data.data
            temp.qrcode = temp.enterprise_qrcode ? [{
              url: temp.enterprise_qrcode
            }] : []
            temp.logo = temp.official_website_logo ? [{
              url: temp.official_website_logo
            }] : []
            this.infoParams = temp
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        async getInfoList (name) {
          try {
            this[`${name}_loading`] = true
            const res = await getComInfo(name)
            this[`${name}_list`] = res.data.data.list
            this[`${name}_loading`] = false
          } catch (error) {
            this[`${name}_loading`] = false
            this.$message.error(error.data.msg)
          }
        },
        lookDetail (row) {

        },
        addCalc (type) { // friendly_link honor partner
          this.calcType = type
          this.optType = 'add'
          switch (type) {
            case 'friendly_link':
              this.infoTit = lang.order_text53 + lang.friendly_link;
              break;
            case 'honor':
              this.infoTit = lang.order_text53 + lang.honor;
              break;
            case 'partner':
              this.infoTit = lang.order_text53 + lang.partner;
              break;
          }
          this.classParams = {
            name: '',
            url: '',
            img: '',
            description: '',
            qrcode: []
          }
          this.classModel = true
        },
        updateItem (type, row) {
          this.calcType = type
          this.optType = 'update'
          Object.assign(this.classParams, row)
          this.classParams.qrcode = [{
            url: row.img
          }]
          this.classModel = true
          switch (type) {
            case 'friendly_link':
              this.infoTit = lang.edit + lang.friendly_link;
              break;
            case 'honor':
              this.infoTit = lang.edit + lang.honor;
              break;
            case 'partner':
              this.infoTit = lang.edit + lang.partner;
              break;
          }
        },
        async submitInfo ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true
              const params = JSON.parse(JSON.stringify(this.classParams))
              if (this.calcType !== 'friendly_link') {
                params.img = params.qrcode[0]?.response?.data.image_url || params.qrcode[0].url
              }
              const res = await addAndUpdateComInfo(this.calcType, this.optType, params)
              this.$message.success(res.data.msg)
              this.getInfoList(this.calcType)
              this.submitLoading = false
              this.classModel = false
            } catch (error) {
              this.submitLoading = false
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        deleteItem (type, row) {
          this.calcType = type
          this.delId = row.id
          this.delVisible = true
        },
        async sureDelete () {
          try {
            const res = await delComInfo(this.calcType, this.delId)
            this.$message.success(res.data.msg)
            this.delVisible = false
            this.getInfoList(this.calcType)
          } catch (error) {
            this.delVisible = false
            this.$message.error(error.data.msg)
          }
        },
        // 切换分页
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getConfigInfo()
        },
        // 分类管理
        classManage () {
          this.classModel = true
          this.classParams.name = ''
          this.classParams.icon = ''
          this.optType = 'add'
        },
        async submitSystemGroup ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true
              const params = JSON.parse(JSON.stringify(this.infoParams))
              params.enterprise_qrcode = params.qrcode[0].url
              params.official_website_logo = params.logo[0].url
              const res = await saveConfigInfo(params)
              this.$message.success(res.data.msg)
              this.getConfigInfo()
              this.submitLoading = false
            } catch (error) {
              this.submitLoading = false
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        editGroup (row) {
          this.optType = 'update'
          this.classParams = JSON.parse(JSON.stringify(row))
        },
        async deleteGroup () {
          try {
            const res = await delImageGroup({
              id: this.delId
            })
            this.$message.success(res.data.msg)
            this.delVisible = false
            this.getGroup()
            this.classParams.name = ''
            this.classParams.icon = ''
            this.$refs.classForm.reset()
            this.optType = 'add'
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);