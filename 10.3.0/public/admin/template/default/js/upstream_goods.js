(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('upstream_goods')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = window.moment
    new Vue({
      data() {
        return {
          // 分页相关
          params: {
            keywords: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc',
          },
          total: 0,
          pageSizeOptions: [20, 50, 100],
          // 表格相关
          data: [],
          columns: [
            {
              title: lang.belong_group,
              width: '200',
              ellipsis: true,
              cell: 'group',
            },
            {
              title: lang.product_name,
              colKey: 'name',
              cell: 'name',
              ellipsis: true
            },
            {
              title: lang.upstream_text18,
              width: '120',
              cell: 'profit_percent',
            },
            {
              title: lang.upstream_text33,
              width: '250',
              cell: 'price',
              ellipsis: true

            },
            {
              title: lang.showText,
              cell: 'hidden',
              width: '200',
            },
            {
              title: lang.order_text67,
              width: '120',
              cell: 'op',
            },
          ],
          rules: {
            name: [
              { required: true, message: lang.input + lang.group_name, type: 'error' },
              { validator: val => val.length <= 100, message: lang.verify3 + 100, type: 'warning' },
            ],
            target_product_group_id: [{ required: true, message: lang.select + lang.product_group, type: 'error' }],
            group_name: [{ required: true, message: lang.input + lang.group_name, type: 'error' }],
          },
          productRules: {
            supplier_id: [{ required: true, message: lang.select + lang.upstream_text6, type: 'error' }],
            upstream_product_id: [{ required: true, message: lang.select + lang.product, type: 'error' }],
            name: [{ required: true, message: lang.input + lang.product_name, type: 'error' }],
            profit_percent: [{ required: true, message: lang.input + lang.upstream_text18, type: 'error' }, { pattern: /^([1-9]\d*(\.\d{1,2})?|([0](\.([0][1-9]|[1-9]\d{0,1}))))$/, message: lang.upstream_text39, type: 'error' }],
            certification_method: [{ required: true, message: lang.select + lang.upstream_text38, type: 'error' }],
            firstId: [{ required: true, message: lang.select + lang.first_group, type: 'error' }],
            product_group_id: [{ required: true, message: lang.select + lang.second_group, type: 'error' }]
          },
          productModel: false,
          firstGroup: [],
          secondGroup: [],
          tempSecondGroup: [],
          currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix || '¥',
          currency_suffix: JSON.parse(localStorage.getItem('common_set')).currency_suffix,
          money: {},
          tableLayout: false,
          hover: true,
          delId: '',
          editId: '',
          loading: false,
          maxHeight: '',
          delVisible: false,
          goodsId: '',
          goodsOption: [],
          isEdit: false,
          groupModel: false,
          formData: { // 新建分组
            name: '',
            id: '' // 0 代表一级分组
          },
          productData: { // 新建分组
            id: '',
            supplier_id: '',
            upstream_product_id: '',
            name: '',
            firstId: '',
            profit_percent: '',
            auto_setup: 0,
            certification: 0,
            sync: 0,
            certification_method: '',
            product_group_id: '',
            description: '',
          },
          supplierOption: [],
          methodOption: [
            {
              id: 'agent',
              name: lang.upstream_text36
            },
            {
              id: 'client',
              name: lang.upstream_text37
            },
          ]
        }
      },
      filters: {
        filterMoney(money) {
          if (isNaN(money)) {
            return '0.00'
          } else {
            const temp = `${money}`.split('.')
            return parseInt(temp[0]).toLocaleString() + '.' + (temp[1] || '00')
          }
        }
      },
      mounted() {
        this.maxHeight = document.getElementById('content').clientHeight - 270
        let timer = null
        window.onresize = () => {
          if (timer) {
            return
          }
          timer = setTimeout(() => {
            this.maxHeight = document.getElementById('content').clientHeight - 270
            clearTimeout(timer)
            timer = null
          }, 300)
        }
      },
      computed: {
        calcType() {
          return this.supplierOption.filter(item => item.id === this.productData.supplier_id)[0]?.type === 'finance'
        }
      },
      created() {
        const temp = this.getQuery(location.search)
        temp.id && (this.goodsId = temp.id)
        this.getUpstreamList()
        this.getSupplierList()
        this.getFirPro()
        this.getSecPro()
        setTimeout(() => {
          temp.id && this.getUpProductDetail()
        }, 1000)
      },
      methods: {
        // 解析url
        getQuery(url) {
          const str = url.substr(url.indexOf('?') + 1)
          const arr = str.split('&')
          const res = {}
          for (let i = 0; i < arr.length; i++) {
            const item = arr[i].split('=')
            res[item[0]] = item[1]
          }
          return res
        },
        // 商品详情
        getUpProductDetail() {
          upstreamProductDetail(this.goodsId).then((res) => {
            this.editGoods(res.data.data.product)
          })
        },
        // 商品列表
        getGoodsList(id) {
          supplierGoodsList(id).then((res) => {
            this.goodsOption = res.data.data.list
          })
        },
        // 搜索框 搜索
        seacrh() {
          this.params.page = 1
          // 重新拉取申请列表
          this.getUpstreamList()
        },
        changeFirId(val) {
          this.tempSecondGroup = this.secondGroup.filter(item => item.parent_id === val)
          this.productData.product_group_id = ''
        },
        closeProduct() {
          this.productModel = false
          this.$refs.productForm.reset()
        },
        // 新建商品
        addProduct() {
          this.isEdit = false
          this.productModel = true
        },
        supplierChange(id) {
          this.productData.upstream_product_id = ''
          this.getGoodsList(id)
        },
        editGoods(row) {
          this.isEdit = true
          this.getGoodsList(row.supplier_id)
          this.productData.id = row.id
          this.productData.supplier_id = row.supplier_id
          this.productData.upstream_product_id = row.upstream_product_id
          this.productData.name = row.name
          this.productData.profit_percent = row.profit_percent
          this.productData.auto_setup = row.auto_setup
          this.productData.sync = row.sync || 0
          this.productData.certification = row.certification
          this.productData.certification_method = row.certification_method
          this.productData.product_group_id = row.product_group_id_second
          this.productData.firstId = row.product_group_id_first
          this.productData.description = row.description
          this.tempSecondGroup = this.secondGroup.filter(item => item.parent_id === this.productData.firstId)
          this.productModel = true
        },
        async submitProduct({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const params = { ...this.productData }
              delete params.firstId
              const res = this.isEdit ? await editUpstreamProduct(params) : await addUpstreamProduct(params)
              this.$message.success(res.data.msg)
              this.productModel = false
              this.$refs.productForm.reset()
              this.getUpstreamList()
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult);
          }
        },
        // 新建分组
        addGroup() {
          this.groupModel = true
          this.formData.id = ''
        },
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const params = { ...this.formData }
              if (params.id === '') { // 新建一级分组
                params.id = 0
              }
              const res = await addGroup(params)
              this.$message.success(res.data.msg)
              this.groupModel = false
              this.$refs.groupForm.reset()
              this.getFirPro()
              this.getSecPro()
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        closeGroup() {
          this.groupModel = false
          this.$refs.groupForm.reset()
        },
        // 获取申请列表
        async getSupplierList() {
          const res = await supplierList({ page: 1, limit: 1000 })
          this.supplierOption = res.data.data.list
        },
        async onChange(row) {
          try {
            await toggleShow(row.id, row.hidden)
            this.getUpstreamList()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },

        // 获取一级分组
        async getFirPro() {
          try {
            const res = await getFirstGroup()
            this.firstGroup = res.data.data.list
          } catch (error) {
          }
        },
        // 获取二级分组
        async getSecPro() {
          try {
            const res = await getSecondGroup()
            this.secondGroup = res.data.data.list
          } catch (error) {
          }
        },
        deleteHandler(id) {
          this.delId = id
          this.delVisible = true
        },
        async sureDel() {
          // 分删除分组和删除商品
          try {
            res = await deleteProduct(this.delId)
            this.$message.success(res.data.msg)
            this.delVisible = false
            this.getUpstreamList()
          } catch (error) {
            this.$message.error(error.data.msg)
            this.delVisible = false
          }
        },
        // 清空搜索框
        clearKey() {
          this.params.keywords = ''
          this.params.page = 1
          // 重新拉取申请列表
          this.getUpstreamList()
        },
        // 底部分页 页面跳转事件
        changePage(e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getUpstreamList()
        },
        // 获取申请列表
        async getUpstreamList() {
          this.loading = true
          const res = await upstreamList(this.params)
          this.data = res.data.data.list
          this.total = res.data.data.count
          this.loading = false
        }
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
