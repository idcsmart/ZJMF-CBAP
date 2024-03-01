(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('product-api')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      components: {
        comConfig,
      },
      data () {
        return {
          id: '',
          formData: {
            auto_setup: 1,
            type: 'server_group',
            rel_id: '',
            show: 1
          },
          submitLoading: false,
          checkOptions: [
            {
              value: 1,
              label: lang.open,
            },
            {
              value: 0,
              label: lang.close,
            },
          ],
          serverParams: {
            page: 1,
            limit: 20
          },
          serverGroupParams: {
            page: 1,
            limit: 20
          },
          total: 0,
          groupTotal: 0,
          serverList: [],
          serverGroupList: [],
          rules: {},
          curList: [],
          content: '',
        }
      },
      watch: {
        'formData.type': {
          immediate: true,
          handler (val) {
            this.curList = val === 'server' ? this.serverList : this.serverGroupList
          }
        },
        // 'formData.rel_id': {
        //   immediate: true,
        //   handler (val) {
        //     if (val) {
        //       this.chooseId()
        //     }
        //   }
        // }
      },
      methods: {
        chooseInterfaceId(e){
          this.formData.rel_id = e
        },
        // 选择接口id
        async chooseId () {
          try {
            const params = { ...this.formData }
            delete params.auto_setup
            const res = await getProductConfig(this.id, params)
            this.$nextTick(() => {
              $('.config-box .content').html(res.data.data.content)
            })
            this.content = res.data.data.content
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        changeType (type) {
          this.formData.type = type
          this.formData.rel_id = ''
          this.curList = []
        },
        async onSubmit () {
          try {
            this.submitLoading = true;
            const res = await editProductServer(this.id, this.formData)
            this.$message.success(res.data.msg)
            this.getUserDetail()
            this.submitLoading = false;
           // location.reload()
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg)
          }
        },
        async getUserDetail () {
          try {
            const res = await getProductDetail(this.id)
            const temp = res.data.data.product
            document.title = lang.product_list + '-' + temp.name + '-' + localStorage.getItem('back_website_name')
            this.formData.auto_setup = temp.auto_setup
            this.formData.type = temp.type
            this.formData.rel_id = temp.rel_id || ''
            this.formData.show = temp.show
            $('.config-box .content').html('')
            let inter = await getInterface(this.serverParams)
            this.serverList = inter.data.data.list
            this.total = inter.data.data.count
            if (this.total > 20) {
              this.serverParams.limit = this.total
              inter = await getInterface(this.serverParams)
              this.serverList = inter.data.data.list
            }
            let group = await getGroup(this.serverGroupParams)
            this.groupTotal = group.data.data.count
            this.serverGroupList = group.data.data.list
            if (this.groupTotal > 20) {
              this.serverGroupParams.limit = this.groupTotal
              group = await getGroup(this.serverGroupParams)
              this.serverGroupList = group.data.data.list
            }
            this.curList = temp.type === 'server' ? this.serverList : this.serverGroupList
           // this.formData.rel_id = this.curList[0].id
            this.chooseId()
            this.$forceUpdate()
          } catch (error) {
            console.log(error)
          }
        },
        back () {
          location.href = 'product.htm'
        }
      },
      created () {
        this.id = location.href.split('?')[1].split('=')[1]
        this.getUserDetail()
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
