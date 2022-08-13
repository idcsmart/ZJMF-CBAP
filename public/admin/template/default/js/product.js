(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('product')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      data () {
        return {
          data: [],
          tableLayout: false,
          delVisible: false,
          groupModel: false,
          productModel: false,
          bordered: true,
          hover: true,
          loading: false,
          columns: [
            {
              // 列拖拽排序必要参数
              colKey: 'drag',
              width: 40
            },
            {
              colKey: 'product_group_name',
              title: lang.group_name,
              width: 500,
              ellipsis: true
            },
            {
              colKey: 'name',
              title: lang.product_name,
              width: 500,
              ellipsis: true
            },
            {
              colKey: 'qty',
              title: lang.qty_manage,
              width: 120,
              align: 'center',
              ellipsis: true
            },
            {
              colKey: 'hidden',
              title: lang.hidden,
              align: 'center',
              width: 120,
              ellipsis: true
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 140,
              ellipsis: true
            },
          ],
          hideSortTips: true,
          params: {
            keywords: '',
            page: 1,
            limit: 100,
            orderby: 'id',
            sort: 'desc'
          },
          total: 0,
          pageSizeOptions: [20, 50, 100],
          curId: '',
          firstGroup: [],
          secondGroup: [],
          groupList: [],
          tempSecondGroup: [],
          rules: {
            name: [
              { required: true, message: lang.input + lang.group_name, type: 'error' },
              { validator: val => val.length <= 100, message: lang.verify3 + 100, type: 'warning' },
            ],
            target_product_group_id: [{ required: true, message: lang.select + lang.product_group, type: 'error' }],
            group_name: [{ required: true, message: lang.input + lang.group_name, type: 'error' }],
          },
          productRules: {
            name: [
              { required: true, message: lang.input + lang.product_name, type: 'error' },
              { validator: val => val.length <= 100, message: lang.verify3 + 100, type: 'warning' }
            ],
            firstId: [{ required: true, message: lang.select + lang.first_group, type: 'error' }],
            product_group_id: [{ required: true, message: lang.select + lang.second_group, type: 'error' }]
          },
          formData: { // 新建分组
            name: '',
            id: '' // 0 代表一级分组
          },
          productData: { // 新建分组
            name: '',
            firstId: '',
            product_group_id: ''
          },
          updataData: {
            id: '',
            name: ''
          },
          delHasPro: false,
          concat_shop: '',
          tempObj: '',
          updateNameTip: '',
          updateNameVisble: false,
          delteType: '',
          moveProductForm: { // 移动商品至其他分组
            id: '',
            target_product_group_id: ''
          },
          tempGroup: [],
          popupProps: {
            overlayStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` }),
          },
          deleteItem: '',
          moveData: {
            id: '',
            pre_product_id: 0,
            product_group_id: ''
          },
          firstMove: {
            id: '', // 当前id
            pre_first_product_group_id: 0, // 目标一级分组id
            backward: 1
          },
          dragForm: { // 拖动商品至其他商品租
            id: '', // 当前id
            pre_product_id: 0,
            product_group_id: ''
          },
          secondGroupForm: { // 拖动整个二级分组
            id: 0,
            first_product_group_id: '',
            pre_product_group_id: '',
            pre_first_product_group_id: ''
          },
          maxHeight: '',
          isFilter: false // 是否过滤其他分组
        }
      },
      mounted () {
        this.maxHeight = document.getElementById('content').clientHeight - 150
        let timer = null
        window.onresize = () => {
          if (timer) {
            return
          }
          timer = setTimeout(() => {
            this.maxHeight = document.getElementById('content').clientHeight - 150
            clearTimeout(timer)
            timer = null
          }, 300)
        }
      },
      watch: {
        concat_shop (val) {
          if (val) {
            const temp = this.data
            temp.forEach(item => {
              item.children = item.children.filter(el => el.id !== this.moveProductForm.id)
            })
            this.tempGroup = temp
            this.delHasPro = true
          }
        }
      },
      methods: {
        rowName ({ row }) {
          if (row.key.indexOf('t') !== -1) {
            return 'row-bg'
          }
        },
        closeMove () {
          this.delHasPro = false
          this.concat_shop = ''
        },
        // 编辑
        editHandler (row) {
          if (row.key.indexOf('t') !== -1) {
            location.href = `product_detail.html?id=${row.id}`
          } else if (row.key.indexOf('f') !== -1) {
            this.updateNameTip = lang.update + lang.first_group + lang.nickname
            this.changeFisrt(row)
          } else if (row.key.indexOf('s') !== -1) {
            this.updateNameTip = lang.update + lang.second_group + lang.nickname
            this.changeFisrt(row)
          }
        },
        // 修改分组名
        changeFisrt (row) {
          this.updateNameVisble = true
          this.updataData.id = row.id
          this.updataData.name = row.name
        },
        async submitUpdateName ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const res = await updateGroup(this.updataData)
              this.$message.success(res.data.msg)
              this.getProductList()
              this.updateNameVisble = false
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        chgangeFlag (row) {
          row.flag = false
        },
        getPro (row) {
          if (row.children && row.children.length > 0) {
            row.children.forEach(item => {
              this.getPro(item)
            })
          } else {
            if (row.key && row.key.indexOf('t') !== -1) {
              this.concat_shop += `${row.name}、`
            }
          }
        },
        // 删除
        deleteHandler (row) {
          this.concat_shop = ''
          this.moveProductForm.target_product_group_id = ''
          if (row.key.indexOf('f') !== -1) { // 一级分组
            this.delteType = 'group'
            this.delteProduct(row.id)
            // 一级分组页面删除不做限制
            // if (row.children.length > 0) {
            //   row.children.forEach(item => {
            //     if (item.children.length > 0) {
            //       item.children.forEach(ele => {
            //         this.getPro(ele)
            //       })
            //     } else {
            //       this.delteProduct(item.id)
            //     }
            //   })
            // } else {
            //   this.delteProduct(row.id)
            // }
          } else if (row.key.indexOf('s') !== -1) { // 二级分组
            this.delteType = 'group'
            this.moveProductForm.id = row.id
            if (row.children.length > 0) {
              row.children.forEach(item => {
                this.getPro(item)
              })
              this.concat_shop = this.concat_shop.substring(0, this.concat_shop.length - 1)
            } else {
              this.delteProduct(row.id)
            }
          } else if (row.key.indexOf('t') !== -1) { // 删除商品
            this.delteType = 'product'
            this.delteProduct(row.id)
          }
        },
        // 弹窗移动分组
        async moveProduct ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const res = await moveProductGroup(this.moveProductForm)
              this.$message.success(res.data.msg)
              this.delHasPro = false
              this.getProductList()
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        // 拖动移动
        async movePorductHandel () {
          try {
            const res = await dragProductGroup(this.dragForm)
            this.$message.success(res.data.msg)
            this.delHasPro = false
            this.getProductList()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        // 删除商品
        delteProduct (id) {
          this.delVisible = true
          this.curId = id
        },
        async sureDel () {
          // 分删除分组和删除商品
          try {
            let res
            if (this.delteType === 'group') {
              res = await deleteGroup(this.curId)
            } else if (this.delteType === 'product') {
              res = await deleteProduct(this.curId)
            }
            this.$message.success(res.data.msg)
            this.delVisible = false
            this.getProductList()
          } catch (error) {
            this.$message.error(error.data.msg)
            this.delVisible = false
          }
        },
        // 拖拽执行之前，可阻止跨级拖拽
        beforeDragSort ({ current, target }) {
          // if (current.key.indexOf('t') == -1) { // 移动的不是商品则阻止
          //   return false
          // }
          return true
        },
        onAbnormalDragSort (params) {
          if (params.code === 1001) {
            this.$message.warning('不同层级的元素，不允许调整顺序');
          }
        },
        // 拖动商品移动分组
        async changeSort ({ currentIndex, current, targetIndex, target }) {
          try {
            this.firstMove.backward = targetIndex - currentIndex > 0 ? 1 : 0
            // 一级分组移动
            if ((current.key.indexOf('f') !== -1) && (target.key.indexOf('f') !== -1)) {
              this.firstMove.id = current.id
              this.firstMove.pre_first_product_group_id = target.id
              this.moveFirst()
            }
            // 移动整个二级分组
            if ((current.key.indexOf('s') !== -1) && (target.key.indexOf('s') !== -1)) {
              console.log("current:", current, "target:", target)
              this.secondGroupForm.id = current.id
              this.secondGroupForm.first_product_group_id = current.id
              this.secondGroupForm.pre_product_group_id = target.id
              this.secondGroupForm.pre_first_product_group_id = target.parent_id
              this.movePorductHandel()
            }
            // 移动商品到其他二级分组
            if ((current.key.indexOf('t') !== -1) && (target.key.indexOf('t') !== -1)) {
              console.log("current:", current.id, "target:", target.product_group_id_second)
              this.dragForm.id = current.id
              this.dragForm.pre_product_id = target.id
              this.dragForm.product_group_id = target.product_group_id_second
              this.movePorductHandel()
            }
            // this.moveData.id = current.id
            // // this.moveData.pre_product_id = target.id
            // this.moveData.product_group_id = target.product_group_id_second
            // const res = await changeOrder(this.moveData)
            // this.$message.success(res.data.msg)
            // this.getProductList()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        async onChange (row) {
          try {
            const res = await toggleShow(row.id, row.hidden)
            this.getProductList()
          } catch (error) {

          }
        },
        // 拖动一级商品分组
        async moveFirst () {
          try {
            const res = await moveFirstGroup(this.firstMove)
            this.$message.success(res.data.msg)
            this.getProductList()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        // 新建分组
        addGroup () {
          this.groupModel = true
          this.formData.id = ''
        },
        async onSubmit ({ validateResult, firstError }) {
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
              this.getProductList()
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        closeGroup () {
          this.groupModel = false
          this.$refs.groupForm.reset()
        },
        // 新建商品
        addProduct () {
          this.productModel = true
        },
        changeFirId (val) {
          this.secondGroup = this.tempSecondGroup.filter(item => item.parent_id === val)
          this.productData.product_group_id = ''
        },
        async submitProduct ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const params = { ...this.productData }
              delete params.firstId
              const res = await addProduct(params)
              this.$message.success(res.data.msg)
              this.productModel = false
              this.$refs.productForm.reset()
              this.getProductList()
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        closeProduct () {
          this.productModel = false
          this.$refs.productForm.reset()
        },
        treeExpandAndFoldIconRender (h, { type }) {

          //  return type === 'expand' ? $('.t-table__tree-op-icon').html(` <svg class="t-icon t-icon-chevron-up"><use href="#t-icon-chevron-up"></use></svg>`)
          //     : $('.t-table__tree-op-icon').html(` <svg class="t-icon t-icon-chevron-down"><use href="#t-icon-chevron-down"></use></svg>`)
          // return type === 'expand' ? <t-icon name="chevron-down"></t-icon> : 'chevron-up'
          // return type === 'expand' ? h('div', null, '11111') : h("t-icon",{class: 't-icon-loading',style: {color: 'red'}},[h('use',{href:'#t-icon-loading'})])

        },
        // 搜索
        clearKey () {
          this.params.keywords = ''
          this.isFilter = false
          this.getProductList()
        },
        seacrh () {
          this.isFilter = true
          this.getProductList()
        },
        // 切换分页
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getProductList()
        },
        // 排序
        sortChange (val) {
          if (!val) {
            return
          }
          this.params.orderby = val.sortBy
          this.params.sort = val.descending ? 'desc' : 'asc'
          this.getProductList()
        },

        // 获取列表
        async getProductList () {
          try {
            this.loading = true
            // 获取商品，一级，二级分组
            const shopList = await getProduct(this.params)
            const firstGroup = await getFirstGroup()
            const secondGroup = await getSecondGroup()
            // 如果是搜索的时候需要过滤掉该商品之外的一二级分组
            if (this.isFilter) {
              const temp = shopList.data.data.list
              const filerFist = temp.reduce((all, cur) => {
                all.push(cur.product_group_id_first)
                return all
              }, [])
              const filerSecond = temp.reduce((all, cur) => {
                all.push(cur.product_group_id_second)
                return all
              }, [])
              this.firstGroup = firstGroup.data.data.list.filter(item => {
                return Array.from(new Set(filerFist)).includes(item.id)
              })
              this.tempSecondGroup = secondGroup.data.data.list.filter(item => {
                return Array.from(new Set(filerSecond)).includes(item.id)
              })
            } else {
              this.firstGroup = firstGroup.data.data.list
              this.tempSecondGroup = secondGroup.data.data.list
            }
            // this.firstGroup = firstGroup.data.data.list
            // this.tempSecondGroup = secondGroup.data.data.list
            // 组装数据，一级分组装二级分组，二级分组填入符合需求的数据
            this.firstGroup.forEach(item => {
              item.key = 'f-' + item.id  // 多级Id会重复，故需要设置独一的key
              let secondArr = []
              this.tempSecondGroup.forEach(sItem => {
                if (sItem.parent_id === item.id) {
                  sItem.key = 's-' + sItem.id
                  secondArr.push(sItem)
                }
              })
              item.children = secondArr
            })
            this.firstGroup.forEach(item => {
              item.children.forEach(ele => {
                let temp = []
                shopList.data.data.list.forEach(e => {
                  if (e.product_group_id_second === ele.id) {
                    e.key = 't-' + e.id
                    temp.push(e)
                  }
                })
                ele.children = temp
              })
            })
            this.data = this.firstGroup
            this.total = firstGroup.data.data.count
            // 展开全部
            this.$nextTick(() => {
              this.$refs.table.expandAll()
            })
            this.loading = false
          } catch (error) {
            this.loading = false
          }
        }
      },
      created () {
        this.getProductList()
      }
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
