(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('product_detail')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      components: {
        asideMenu,
        topMenu,
        pagination,
      },
      created() {
        this.id = location.href.split('?')[1].split('=')[1]
        this.getCommonData()
        this.getHostDetail()
      },
      mounted() {
        const addons_js_arr = JSON.parse(document.querySelector('#addons_js').getAttribute('addons_js')) // 插件列表
        const arr = addons_js_arr.map((item) => {
          return item.name
        })
        // 开启了电子合同
        if (arr.includes('EContract')) {
          this.getTimeoutStatus()
        } else {
          this.timeouted = true
          this.getList()
        }
      },
      updated() {
        // // 关闭loading
        // document.getElementById('mainLoading').style.display = 'none';
        // document.getElementsByClassName('product_detail')[0].style.display = 'block'
      },
      destroyed() {

      },
      data() {
        return {
          // 电子合同开始
          timeouted: false,
          actStatus: [],
          hostData: {},
          // 电子合同结束
          id: '',
          params: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: 'id',
            sort: 'desc',
            keywords: '',
          },
          commonData: {},

          content: ''
        }
      },
      filters: {
        formateTime(time) {
          if (time && time !== 0) {
            return formateDate(time * 1000)
          } else {
            return "--"
          }
        }
      },
      methods: {
        // 返回产品列表页
        goBack() {
          window.history.back();
        },
        // 获取产品详情
        getHostDetail() {
          const params = {
            id: this.id
          }
          hostDetail(params).then(res => {
            if (res.data.status === 200) {
              this.hostData = res.data.data.host
            }
          })
        },
        // 获取产品合同是否逾期
        getTimeoutStatus() {
          timeoutStatus(this.id).then(res => {
            if (res.data.status === 200) {
              if (res.data.data.timeout) {
                this.actStatus = res.data.data.act
              }
            }
          }).catch((err) => {
            this.$message.error(err.data.msg)
          }).finally(() => {
            this.timeouted = true
            this.getList()
          })
        },
        // 获取插件Id
        pluginId(name) {
          const addons_js_arr = JSON.parse(document.querySelector("#addons_js").getAttribute("addons_js")); // 插件列表
          for (let index = 0; index < addons_js_arr.length; index++) {
            const element = addons_js_arr[index];
            if (name === element.name) {
              return element.id
            }
          }
        },
        // 去签订合同
        goContractDetail() {
          location.href = `/plugin/${this.pluginId('EContract')}/signContract.htm?id=${this.hostData.order_id}`
        },
        async getList() {
          try {
            const res = await getProductDetail(this.id)
            this.$nextTick(() => {
              $('.config-box .content').html(res.data.data.content)
            })
            this.content = res.data.data.content
          } catch (error) {

          }
        },
        // 每页展示数改变
        sizeChange(e) {
          this.params.limit = e
          this.params.page = 1
          // 获取列表
        },
        // 当前页改变
        currentChange(e) {
          this.params.page = e

        },

        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
        }
      },

    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
