(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('product')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      data() {
        return {
          data: [],
          tableLayout: false,
          delVisible: false,
          groupModel: false,
          productModel: false,
          bordered: true,
          hover: true,
          expandArr: [],
          checkExpand: false,
          checkAll: false,
          agentVisble: false,
          loading: false,
          authList: [],
          arr: [],
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
              title: lang.showText,
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
            limit: 10000,
            orderby: 'id',
            sort: 'desc'
          },
          total: 0,
          pageSizeOptions: [20, 50, 100],
          curId: '',
          firstGroup: [], // 一级分组
          secondGroup: [], // 二级分组
          groupList: [],
          hasBaidu: false,
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
          authArr: [],
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
            pre_first_product_group_id: '',
            backward: 1
          },
          maxHeight: '',
          isFilter: false // 是否过滤其他分组
        }
      },
      // mounted() {
      //   this.maxHeight = document.getElementById('content').clientHeight - 150
      //   let timer = null
      //   window.onresize = () => {
      //     if (timer) {
      //       return
      //     }
      //     timer = setTimeout(() => {
      //       this.maxHeight = document.getElementById('content').clientHeight - 150
      //       clearTimeout(timer)
      //       timer = null
      //     }, 300)
      //   }
      // },
      watch: {
        concat_shop(val) {
          if (val) {
            let temp = JSON.parse(JSON.stringify(this.data)).map(item => {
              item.children = item.children.filter(el => el.id !== this.moveProductForm.id)
              return item
            })
            this.tempGroup = temp
            this.delHasPro = true
          }
        }
      },
      methods: {
        rowName({ row }) {
          if (row.key.indexOf('t') !== -1) {
            return 'row-bg'
          }
        },
        closeMove() {
          this.delHasPro = false
          this.concat_shop = ''
          this.tempGroup = []
        },
        closeAgentDia() {
          this.agentVisble = false
        },
        // 编辑
        editHandler(row) {
          if (row.key.indexOf('t') !== -1) {
            if (row.agent === 1) {
              location.href = `upstream_goods.html?id=${row.id}`
            } else {
              location.href = `product_detail.html?id=${row.id}`
            }
          } else if (row.key.indexOf('f') !== -1) {
            this.updateNameTip = lang.update + lang.first_group + lang.nickname
            this.changeFisrt(row)
          } else if (row.key.indexOf('s') !== -1) {
            this.updateNameTip = lang.update + lang.second_group + lang.nickname
            this.changeFisrt(row)
          }
        },
        // 展开/折叠
        expandAll() {
          this.expandArr = []
          const { tree } = this.$refs
          tree.getItems().forEach(item => {
            this.checkExpand ? this.expandArr.push(item.value) : []
          })
        },
        // 全选/全不选
        chooseAll() {
          if (this.checkAll) {
            this.authList = this.arr
          } else {
            this.authList = []
          }
        },
        // 节点点击的时候
        clickNode(e) {
          if (!e.node.expanded) {
            this.expandArr.push(e.node.value)
          } else {
            this.expandArr.splice(this.expandArr.indexOf(e.node.value), 1)
          }
        },
        async getPlugin() {
          try {
            const res = await getAddon();
            const temp = res.data.data.list.reduce((all, cur) => {
              all.push(cur.name);
              return all;
            }, [])
            this.hasBaidu = temp.includes("BaiduCloud");
          } catch (error) { }
        },
        // 修改分组名
        changeFisrt(row) {
          this.updateNameVisble = true
          this.updataData.id = row.id
          this.updataData.name = row.name
        },
        async submitUpdateName({ validateResult, firstError }) {
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
        chgangeFlag(row) {
          row.flag = false
        },
        getPro(row) {
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
        deleteHandler(row) {
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
        async moveProduct({ validateResult, firstError }) {
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
        // 拖动二级分组
        async movePorductGroup() {
          try {
            const res = await draySecondGroup(this.secondGroupForm)
            this.$message.success(res.data.msg)
            this.delHasPro = false
            this.getProductList()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        // 拖动商品至其他二级分组
        async movePorductHandel() {
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
        delteProduct(id) {
          this.delVisible = true
          this.curId = id
        },
        async sureDel() {
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
        beforeDragSort({ current, target }) {
          // if (current.key.indexOf('t') == -1) { // 移动的不是商品则阻止
          //   return false
          // }
          return true
        },
        onAbnormalDragSort(params) {
          // if (params.code === 1001) {
          //   this.$message.warning('不同层级的元素，不允许调整顺序');
          // }
        },
        // 保存可代理商品
        handelAgentable() {
          const arr = []
          this.authList.forEach((item) => {
            if (item.includes('t')) {
              const id = item.split('-')[1]
              arr.push(id)
            }
          })
          agentable({ id: arr }).then((res) => {
            this.$message.success(res.data.msg)
            this.agentVisble = false
            this.authList = []
            this.getProductList()
          }).catch((err) => {
            this.$message.error(err.data.msg)
          })
        },
        // 拖动商品移动分组
        async changeSort({ currentIndex, current, targetIndex, target }) {
          try {
            const tempForward = targetIndex - currentIndex > 0 ? 1 : 0  // 1向下拖动，0向上拖动
            this.firstMove.backward = tempForward
            // 一级分组移动
            if ((current.key.indexOf('f') !== -1) && (target.key.indexOf('f') !== -1)) {
              this.firstMove.id = current.id
              this.firstMove.pre_first_product_group_id = target.id
              this.moveFirst()
            }
            // 移动整个二级分组
            if ((current.key.indexOf('s') !== -1) && (target.key.indexOf('s') !== -1)) {
              this.secondGroupForm.id = current.id
              this.secondGroupForm.first_product_group_id = current.parent_id
              this.secondGroupForm.pre_product_group_id = target.id
              this.secondGroupForm.pre_first_product_group_id = target.parent_id
              this.secondGroupForm.backward = tempForward
              this.movePorductGroup()
            }
            // 移动商品到其他二级分组
            if ((current.key.indexOf('t') !== -1) && (target.key.indexOf('t') !== -1)) {
              this.dragForm.id = current.id
              this.dragForm.pre_product_id = target.id
              this.dragForm.product_group_id = target.product_group_id_second
              this.dragForm.backward = tempForward
              this.movePorductHandel()
            }
            // 特殊情况：拖动二级到无二级栏目的一级栏目下
            if ((current.key.indexOf('s') !== -1) && (target.key.indexOf('f') !== -1)) {
              const index = this.data.findIndex(item => item.key === target.key)
              this.secondGroupForm.id = current.id
              this.secondGroupForm.first_product_group_id = current.parent_id
              // 目标节点对应的数组
              const _temp = this.data[tempForward ? index : index - 1]
              this.secondGroupForm.pre_product_group_id = _temp.children.length > 0 ? _temp.children.at(-1).id : 0
              this.secondGroupForm.backward = _temp.children.length > 0 ? 1 : tempForward
              this.secondGroupForm.pre_first_product_group_id = _temp.children.length > 0 ? _temp.children.at(-1).parent_id : _temp.id
              this.movePorductGroup()
            }
            // 特殊情况：拖动商品到无商品二级栏目下
            if ((current.key.indexOf('t') !== -1) && (target.key.indexOf('s') !== -1)) {
              // console.log('@@@@', target.children.length > 0, target)
              const pArr = this.data.filter(item => item.id === target.parent_id)[0]
              const index = pArr.children.findIndex(item => item.key === target.key)
              this.dragForm.id = current.id
              // // 目标节点对应的数组
              const _temp = pArr.children[tempForward ? index : index - 1] || { children: [], id: target.id }
              this.dragForm.pre_product_id = _temp.children.length > 0 ? _temp.children.at(-1).id : 0
              this.dragForm.product_group_id = _temp.children.length > 0 ? _temp.children.at(-1).product_group_id_second : _temp.id
              this.dragForm.backward = _temp.children.length > 0 ? 1 : tempForward
              this.movePorductHandel()
            }
          } catch (error) {
            console.log('AAAA', error)
            this.$message.error(error.data.msg)
          }
        },
        async onChange(row) {
          try {
            if (row.qty !== undefined) {
              await toggleShow(row.id, row.hidden)
            } else {
              await groupListShow(row.id, row.hidden)
            }
            this.getProductList()
          } catch (error) {

          }
        },
        // 拖动一级商品分组
        async moveFirst() {
          try {
            const res = await moveFirstGroup(this.firstMove)
            this.$message.success(res.data.msg)
            this.getProductList()
          } catch (error) {
            this.$message.error(error.data.msg)
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
              this.getProductList()
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
        // 新建商品
        addProduct() {
          this.productModel = true
        },
        addBaiduProduct() {
          location.href = 'plugin/baidu_cloud/baiduProduct.html'
        },
        changeFirId(val) {
          this.secondGroup = this.tempSecondGroup.filter(item => item.parent_id === val)
          this.productData.product_group_id = ''
        },
        async submitProduct({ validateResult, firstError }) {
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
        closeProduct() {
          this.productModel = false
          this.$refs.productForm.reset()
        },
        treeExpandAndFoldIconRender(h, { type }) {

          //  return type === 'expand' ? $('.t-table__tree-op-icon').html(` <svg class="t-icon t-icon-chevron-up"><use href="#t-icon-chevron-up"></use></svg>`)
          //     : $('.t-table__tree-op-icon').html(` <svg class="t-icon t-icon-chevron-down"><use href="#t-icon-chevron-down"></use></svg>`)
          // return type === 'expand' ? <t-icon name="chevron-down"></t-icon> : 'chevron-up'
          // return type === 'expand' ? h('div', null, '11111') : h("t-icon",{class: 't-icon-loading',style: {color: 'red'}},[h('use',{href:'#t-icon-loading'})])

        },
        // 搜索
        clearKey() {
          this.params.keywords = ''
          this.isFilter = false
          this.getProductList()
        },
        seacrh() {
          this.isFilter = true
          this.getProductList()
        },
        // 切换分页
        changePage(e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getProductList()
        },
        // 排序
        sortChange(val) {
          if (!val) {
            return
          }
          this.params.orderby = val.sortBy
          this.params.sort = val.descending ? 'desc' : 'asc'
          this.getProductList()
        },

        // 获取一级分组
        async getFirPro() {
          try {
            const res = await getFirstGroup()
            this.firstGroup = res.data.data.list
          } catch (error) {
          }
        },
        handelAngenBtn() {
          this.authList = []
          this.shopList.forEach(e => {
            if (e.agentable === 1) {
              this.authList.push(e.key)
            }
          })
          this.checkExpand = false
          this.checkAll = false
          this.agentVisble = true
        },
        // 获取二级分组
        async getSecPro() {
          try {
            const res = await getSecondGroup()
            this.secondGroup = res.data.data.list
          } catch (error) {
          }
        },
        // 获取商品列表
        async getProList() {
          try {
            const res = await getProduct(this.params)
            this.shopList = res.data.data.list
          } catch (error) {
          }
        },
        getIdFun(list) {
          list.map(item => {
            if (item.children) {
              this.getIdFun(item.children);
            }
            this.arr.push(item.key)
          })
        },
        // 初始化
        getProductList() {
          try {
            this.loading = true
            this.authList = []
            // 获取商品，一级，二级分组
            Promise.all([this.getProList(), this.getFirPro(), this.getSecPro()]).then(res => {
              // 如果是搜索的时候需要过滤掉该商品之外的一二级分组
              if (this.isFilter) {
                const temp = this.shopList
                const filerFist = temp.reduce((all, cur) => {
                  all.push(cur.product_group_id_first)
                  return all
                }, [])
                const filerSecond = temp.reduce((all, cur) => {
                  all.push(cur.product_group_id_second)
                  return all
                }, [])
                this.firstGroup = this.firstGroup.filter(item => {
                  return Array.from(new Set(filerFist)).includes(item.id)
                })
                this.tempSecondGroup = this.secondGroup.filter(item => {
                  return Array.from(new Set(filerSecond)).includes(item.id)
                })
              } else {
                this.firstGroup = this.firstGroup
                this.tempSecondGroup = this.secondGroup
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
              this.data = this.firstGroup

              this.loading = false
              //  this.total = firstGroup.data.data.count
              // 展开全部
              // this.$nextTick(() => {
              //   this.$refs.table.expandAll()
              // })
              setTimeout(() => {
                this.firstGroup.forEach(item => {
                  item.children.forEach(ele => {
                    let temp = []
                    this.shopList.forEach(e => {
                      if (e.product_group_id_second === ele.id) {
                        e.key = 't-' + e.id
                        temp.push(e)
                      }
                      if (e.agentable === 1) {
                        this.authList.push(e.key)
                      }
                    })
                    ele.children = temp
                  })
                })
                this.data = this.firstGroup
                this.authArr = JSON.parse(JSON.stringify(this.firstGroup))
                this.getIdFun(this.firstGroup)
                this.$forceUpdate()
                this.$nextTick(() => {
                  this.$refs.table.expandAll()
                })
              }, 0)
            })

          } catch (error) {
            console.log('@@@@', error)
            this.loading = false
          }
        }
      },
      created() {
        this.getProductList()
        this.getPlugin()
      }
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
