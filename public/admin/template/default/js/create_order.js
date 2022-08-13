// 给自定义渲染配置项提供请求价格的方法（只用于右侧页面的展示）
async function changeProductConfigPrice (id, tag, config) {
  const params = {
    id,
    config_options: config
  }
  const res = await getProPrice(params)
  $(`.config-show #${tag}`).html(res.data.data.content)
  $(`.config-show #${tag}`).attr('data-price', res.data.data.price)
  let totalPrice = 0
  $('.config-show .box>div').each((ind, el) => {
    totalPrice += Number($(el).attr('data-price'))
  })
  // 升降级单独调用
  if (window.orderType === 'upgrade') {
    const upgradeParams = {
      host_id: window.host_id,
      product: {
        product_id: window.product_id,
        config_options: config,
        price: res.data.data.price
      },
      client_id: window.client_id
    }
    const result = await getUpgradeAmount(upgradeParams)
    const data = result.data.data
    $('.upgrade .config-show .refund').html(data.refund)
    $('.upgrade .config-show .pay').html(data.pay)
    $('.upgrade .config-show .amount').html(data.amount)
    return
  }
  $('.config-show .total .total-price').html(totalPrice.toFixed(2))
}

(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('create-order')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      data () {
        return {
          userList: [],
          userParams: {
            keywords: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix,
          currency_code: JSON.parse(localStorage.getItem('common_set')).currency_code,
          formData: {
            client_id: '', // 用户id
            type: 'new', // new新订单 renew 续费订单 upgrade 升降级订单 artificial 人工订单
            // 新订单
            products: [
              {
                product_id: '',
                config_options: {
                },
                qty: 1,
                price: '',
                product_name: '',
                key: new Date().getTime()
              }
            ],
            // 升降级订单
            host_id: '',
            host_name: '',
            product: {
              product_id: '',
              config_options: {},
              price: '',
              product_name: ''
            },
            // 人工订单
            amount: '',
            description: ''
          },
          showProduct: [], // 右侧渲染
          productList: [], // 所有商品下拉选择
          curClientProduct: [], // 当前用户已有的商品
          totalPrice: 0,
          rules: {
            client_id: [{ required: true, message: lang.input + lang.user, type: 'error' }],
            product_name: [{ required: true, message: lang.input + lang.product, type: 'error' }],
            type: [{ required: true, message: lang.input + lang.order_type, type: 'error' }],
            description: [{ required: true, message: lang.input + lang.description, type: 'error' }],
            amount: [
              { required: true, message: lang.input + lang.price, type: 'error' },
              {
                pattern: /^\d+(\.\d{0,2})?$/, message: lang.verify5, type: 'warning'
              },
              {
                validator: val => val > 0, message: lang.verify5, type: 'warning'
              }
            ],
            lang_admin: [{ required: true }]
          },
          orderType: [
            { type: 'new', name: lang.new },
            // { type: 'renew', name: lang.renew },
            { type: 'upgrade', name: lang.upgrade },
            { type: 'artificial', name: lang.artificial },
          ],
          curNum: 0,
          clientParams: {
            client_id: ''
          },
          isShowTree: false,
          clientShopList: [],
          shopId: '',
          upgradeList: [], // 升降级产品列表
          treeProps: {
            keys: {
              label: 'name',
              value: 'key'
            },
          },
          userTotal: 0,
          popupProps: {
            overlayStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
              'max-height': '362px'
            }),
          },
          renewIds: [],
          visibleTreeObj: [
            { visibleTree: false }
          ]
        }
      },
      mounted () {
        document.onclick = () => {
          this.visibleTreeObj.forEach(item => {
            item.visibleTree = false
          })
        }
        this.$nextTick(() => {
          document.getElementById(`myPopup${this.curNum}`).onclick = () => {
            event.stopPropagation()
          }
        })

        // document.getElementById("product-tree").onclick = function () {
        //   event.stopPropagation();
        // }
      },
      watch: {
        'formData.client_id' (val) {
          this.formData.host_id = ''
          this.shopId = ''
          this.formData.host_name = ''
          this.formData.product.product_id = ''
          this.formData.product.config_options = {}
          this.formData.product.price = ''
          this.getActiveShop(val)
        },
        // 检测vue版下插件数据的改变
        'formData.products': {
          deep: true,
          handler () {

          }
        }
      },
      created () {
        // 获取用户列表
        this.getUserList()
        // 获取分组
        // 获取产品列表
        this.getProductList()
      },
      methods: {
        // 选择用户
        chooseUser (e) {
          window.client_id = e
        },
        // 改变类型
        changeType (type) {
          window.orderType = type
          if (type === 'new') {
            this.visibleTreeObj = []
            this.formData.products = []
            this.addMore()
            this.$nextTick(() => {
              this.curNum = 0
              this.$forceUpdate()
              console.log(2323232)
            })
          }
          if ((type === 'new') || (type === 'upgrade')) {
            $('.config-show .box').html('')
            $('.config-show .total-price').html('0.00')
          }
        },
        onBlurTrigger (e) {
          console.log(e)
        },
        treeClick () {
          console.log(2222)
        },
        /*** 升降级 ***/
        chooseActive (e) {
          window.host_id = e
          this.formData.product.product_id = e
          this.formData.product.price = ''
          this.formData.product.product_name = ''
          $('.config-area').html('')
          $('.config-show .box').html('')
          $('.total-price').html(0)
          this.clientShopList.forEach(item => {
            if (item.id === e) {
              this.getRelationList(item.product_id)
              this.formData.host_id = e
            }
          })
        },
        // 选择升级至的商品
        chooseUpgrade (e) {
          this.formData.product.product_id = e
          window.product_id = e
          this.getProConfig(e, `tag_0_${e}`)
        },
        // 单独获取升降级的价格
        async getUpgradeAmount () {
          try {
            const { host_id, product, client_id } = this.formData
            const params = {
              host_id,
              product,
              client_id
            }
            delete params.product.product_name
            const res = await getUpgradeAmount(params)
            const data = res.data.data
            $('.upgrade .config-show .refund').html(data.refund)
            $('.upgrade .config-show .pay').html(data.pay)
            $('.upgrade .config-show .amount').html(data.amount)
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        // 获取用户已开通的产品
        async getActiveShop (val) {
          try {
            const params = {
              client_id: val,
              status: 'Active'
            }
            const res = await getShopList(params)
            this.clientShopList = res.data.data.list
          } catch (error) {

          }
        },
        // 获取升降级关联产品
        async getRelationList (id) {
          try {
            const res = await getRelationList(id)
            this.upgradeList = res.data.data.list
          } catch (error) {

          }
        },
        // 设置产品下拉框名称
        getLabel (createElement, node) {
          const label = node.data.name
          const { data } = node
          data.label = label
          return label
        },
        // 删除已选商品
        deltePro (index) {
          $('.config-show .box>div').eq(index).remove()
          $('.new-order .pro-item').eq(index).remove()
          this.formData.products.splice(index, 1)
          this.changPrice()
        },
        // 重新计算当前页面的价格
        async changPrice () {
          // 升降级的时候单独处理价格
          let total = 0
          $('.config-show .box>div').each((ind, el) => {
            total += Number($(el).attr('data-price'))
          })
          if (this.formData.type === 'upgrade') {
            this.getUpgradeAmount()
            return false
          }
          $('.config-show .total-price').html(Math.round(total.toFixed(2) * 100) / 100)
        },
        checkNum (num) {
          this.curNum = num
        },
        focusHandler (index) {
          this.curNum = index
          console.log(this.visibleTreeObj[index].visibleTree)
          this.$set(this.visibleTreeObj[index], 'visibleTree', !this.visibleTreeObj[index].visibleTree)
          console.log(this.visibleTreeObj[index].visibleTree)
        },
        // 商品选择
        onClick (e) {
          if (!e.node.data.children) {
            const pName = e.node.data.name
            const pId = e.node.data.id
            this.$set(this.formData.products[this.curNum], 'product_name', pName)
            // this.formData.products[this.curNum].product_name = pName
            this.formData.products[this.curNum].product_id = pId
            // 获取该商品的自定义配置项，需传入右侧展示位置的tag：tag-index-id
            console.log('index:', this.curNum)
            const tag = `tag_${this.curNum}_${pId}`
            this.getProConfig(pId, tag)
            this.totalPrice = 0
            this.$set(this.visibleTreeObj[this.curNum], 'visibleTree', false)
            // $('.t-popup').hide()
            // $('.tree-select .t-select').removeClass('t-is-active')
            // $('.tree-select .t-select svg.t-fake-arrow').removeClass('t-fake-arrow--active')
          }
        },
        // 原生根据所选商品获取自定义配置
        async getProConfig (id, tag) {
          try {
            const params = { id, tag }
            console.log(params)
            const res = await getProConfig(params)
            // 渲染左侧配置项
            $('.pro-item').eq(this.curNum).find('.config-area').html(res.data.data.content)
            // 初次根据配置请求右侧价格
            const pParams = {
              id,
              config_options: {}
            }
            // vue
            if ($('.pro-item').eq(this.curNum).find('form')[0]?.__vue__) {
              const data = $('.pro-item').eq(this.curNum).find('form')[0].__vue__.data
              pParams.config_options = data
              this.formData.products[this.curNum].config_options = data
            } else { // 原生form
              const configArr = $('.pro-item form').eq(this.curNum).serializeArray()
              configArr.forEach(item => {
                pParams.config_options[item.name] = item.value
              })
            }

            const result = await getProPrice(params)
            this.$nextTick(() => {
              // 右侧展示区域追加还是修改
              console.log($('.config-show .box>div').eq(this.curNum).attr('id'))
              if (($('.config-show .box>div').eq(this.curNum).attr('id') == undefined)) {
                $('.config-show .box').append(
                  `<div id="${tag}" data-price="${result.data.data.price}">
                 ${result.data.data.content}
               </div>`
                )
              } else {
                $('.config-show .box>div').eq(this.curNum).replaceWith(
                  `<div id="${tag}" data-price="${result.data.data.price}">
                 ${result.data.data.content}
                </div>`
                )
              }
              if (this.formData.type === 'upgrade') {
                this.formData.product.price = result.data.data.price
                this.formData.product.config_options = pParams.config_options
              }
              if (this.formData.type === 'new') {
                this.formData.products[this.curNum].price = result.data.data.price
              }
              this.changPrice()
            })
          } catch (error) {  // 处理添加过后，修改产品没有配置项的时候
            this.$message.error(error.data.msg)
            $('.config-show .box>div').each((ind, el) => {
              if ($(el).attr('id').split('_')[1] == this.curNum) {
                $(el).remove()
              }
            })
            let totalPrice = 0
            $('.config-show .box>div').each((ind, el) => {
              totalPrice += Number($(el).attr('data-price'))
            })
            $('.config-show .total-price').html(totalPrice.toFixed(2))
          }
        },
        addMore () {
          this.visibleTreeObj.push({
            visibleTree: false
          })
          this.formData.products.push({
            product_id: '',
            config_options: {
            },
            qty: 1,
            price: '',
            product_name: '',
            key: new Date().getTime()
          })
          this.curNum = this.formData.products.length - 1
          this.$nextTick(() => {
            document.getElementById(`myPopup${this.curNum}`).onclick = () => {
              event.stopPropagation()
            }
          })
        },
        // 获取列表
        async getProductList () {
          try {
            // 获取商品，一级，二级分组
            const shopList = await getProList()
            const firstGroup = await getFirstGroup()
            const secondGroup = await getSecondGroup()
            this.firstGroup = firstGroup.data.data.list
            this.tempSecondGroup = secondGroup.data.data.list
            // 组装数据，一级分组装二级分组，二级分组填入符合需求的数据
            firstGroup.data.data.list.forEach(item => {
              item.key = 'f-' + item.id  // 多级Id会重复，故需要设置独一的key
              item.flag = false  // flag切换显示修改
              let secondArr = []
              // item.disabled = true
              secondGroup.data.data.list.forEach(sItem => {
                if (sItem.parent_id === item.id) {
                  sItem.key = 's-' + sItem.id
                  sItem.flag = false  // flag切换显示修改
                  secondArr.push(sItem)
                  //  sItem.disabled = true
                }
              })
              item.children = secondArr
            })
            firstGroup.data.data.list.forEach(item => {
              item.children.forEach(ele => {
                let temp = []
                shopList.data.data.list.forEach(e => {
                  if (e.product_group_id_second === ele.id) {
                    e.key = 't-' + e.id
                    e.flag = false  // flag切换显示修改
                    temp.push(e)
                  }
                })
                ele.children = temp
              })
            })
            this.productList = firstGroup.data.data.list
          } catch (error) {
          }
        },
        // 续费选择
        chooseRenew () {
          // 拉取页面
          this.getRenewPage()


        },
        async getRenewPage () {
          try {
            const params = {
              ids: JSON.parse(JSON.stringify(this.renewIds)),
              client_id: this.formData.client_id
            }
            params.ids = params.ids.toString()
            console.log(params)
            const res = await getRenewBatch(params)
            // console.log(res)
          } catch (error) {

          }
        },
        /*** 提交订单 ***/
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const {
                client_id, type, amount, description, products,
                host_id, product
              } = this.formData
              switch (this.formData.type) {
                // 人工订单
                case 'artificial':
                  const params = {
                    client_id,
                    type,
                    amount,
                    description
                  }
                  const res = await createOrder(params)
                  this.$message.success(res.data.msg)
                  setTimeout(() => {
                    location.href = 'order.html'
                  }, 300)
                  break;
                // 新订单
                case 'new':
                  const params1 = {
                    type, products, client_id
                  }
                  if (!params1.products[0].product_id) {
                    this.$message.error(lang.select + lang.product)
                    return
                  }
                  // 原生的form需要手动获取页面数据进行整合，vue则直接提交
                  if (!$('.pro-item').eq(this.curNum).find('form')[0]?.__vue__) {
                    $('.pro-item').find('form').each((ind, el) => {
                      let arr = $(el).serializeArray()
                      arr.forEach(item => {
                        params1.products[ind].config_options[item.name] = item.value
                      })
                    })
                  }
                  $('.config-show .box>div').each((ind, el) => {
                    params1.products[ind].price = Number($(el).attr('data-price'))
                  })
                  // 过滤没有选择商品的项目
                  params1.products = params1.products.filter(item => {
                    delete item.key
                    delete item.product_name
                    return item.product_id
                  })
                  const res1 = await createOrder(params1)
                  this.$message.success(res1.data.msg)
                  setTimeout(() => {
                    location.href = 'order.html'
                  }, 300)
                  break;
                // 续费订单
                case 'renew':
                  break;
                // 升降级
                case 'upgrade':
                  const params3 = {
                    type, product, client_id, host_id
                  }
                  if (!params3.host_id) {
                    this.$message.error(lang.select + lang.tailorism)
                    return
                  }
                  if (!params3.product.product_id) {
                    this.$message.error(lang.select + lang.product)
                    return
                  }
                  $('.pro-item').find('form').each((ind, el) => {
                    let arr = $(el).serializeArray()
                    arr.forEach(item => {
                      params3.product.config_options[item.name] = item.value
                    })
                  })
                  product.price = $('.upgrade .config-show .box>div').attr('data-price') * 1
                  const res3 = await createOrder(params3)
                  this.$message.success(res3.data.msg)
                  setTimeout(() => {
                    location.href = 'order.html'
                  }, 300)
                  break;
              }
            } catch (error) {
              console.log(error)
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        // 获取用户列表
        async getUserList () {
          try {
            this.searchLoading = true
            const { data: { data } } = await getClientList(this.userParams)
            this.userList = data.list
            this.userTotal = data.count
            if (this.userTotal > 20) {
              this.userParams.limit = this.userTotal
              const { data: { data } } = await getClientList(this.userParams)
              this.userList = data.list
            }
            this.searchLoading = false
          } catch (error) {
            this.searchLoading = false
          }
        },
        // 远程搜素
        remoteMethod (key) {
          this.userParams.keywords = key
          this.getUserList()
        },
        clearKey () {
          this.userParams.keywords = ''
          this.getUserList()
        }
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
