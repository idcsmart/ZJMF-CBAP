const template = document.getElementsByClassName('template')[0]
Vue.prototype.lang = window.lang
new Vue({
  components: {
    asideMenu,
    topMenu,
  },
  mounted () {
    this.getConfig()
  },
  destroyed () {

  },
  data () {
    return {
      id: '',
      tit: '',
      commonData: {},
      country: '',
      countryName: '',
      city: '',
      curImage: '',
      imageName: '',
      version: '',
      curImageId: '',
      dataList: [], // 数据中心
      memMarks: {},
      limitList: [], // 限制
      packageId: '', // 套餐ID
      imageList: [], // 镜像
      systemDiskList: [], // 系统盘
      dataDiskList: [], // 数据盘
      configLimitList: [], // 限制规则
      cloudIndex: 0,
      cycle: '', // 周期
      cycleList: [],
      qty: 1,
      recommendList: [], // 推荐套餐
      // 区域
      area_name: '',
      isChangeArea: true,
      lineList: [], // 线路
      lineDetail: {}, // 线路详情：bill_type, flow, bw, defence , ip
      lineName: '',
      lineType: '',
      bwName: '',
      defenseName: '',
      cpuName: '',
      memoryName: '',
      bwArr: [],
      bwMarks: {},
      bwTip: '',
      bwType: '',
      params: { // 配置参数
        data_center_id: '',
        image_id: 0,
        line_id: '',
        bw: '',
        flow: '',
        peak_defence: '',
        ip_num: '',
        duration_id: '',
        notes: '',
        model_config_id: '', // 型号id
        auto_renew: false
      },
      plan_way: 0,
      root_name: 'root',
      hover: false,
      login_way: lang.auto_create, // 登录方式 auto_create
      rules: {
        name: [
          {
            pattern: /^[A-Za-z][a-zA-Z0-9_.-]{5,24}$/, message: lang.mf_tip16,
          },
        ],
      },
      sshList: [],
      dis_visible: false,
      // 配置价格
      loadingPrice: false,
      totalPrice: 0.00,
      preview: [],
      discount: '',
      duration: '',
      /* 优惠码 */
      promo: {
        scene: 'new',
        promo_code: '',
        billing_cycle_time: ''
      },
      cartDialog: false,
      isInit: true,
      // 回调相关
      isUpdate: false,
      position: 0,
      backfill: {},
      // 公网ip
      ipData: [],
      ipName: '',
      modelArr: [],
      originModel: [],
      filterModle: [], // 根据区域过滤过的的型号数组
      cpuSelect: [], // 处理器选择
      memorySelect: [], // 内存选择
      curCpu: '',
      curMemory: '',
      isLogin: localStorage.getItem('jwt'),
      lineChange: false,
      showErr: false,
      showImage: false,
      isHide: true,
      isChangeAreaId: false
    }
  },
  filters: {
    formateTime (time) {
      if (time && time !== 0) {
        return formateDate(time * 1000)
      } else {
        return "--"
      }
    }
  },
  created () {
    this.id = this.getQuery('id')
    this.tit = this.getQuery('name')
    this.isUpdate = this.getQuery('change')
    this.getCommonData()
    this.getIamgeList()
    // 回显配置
    const temp = JSON.parse(sessionStorage.getItem('product_information'))
    if (this.isUpdate && temp.config_options) {
      this.backfill = temp.config_options
      this.isChangeArea = false
      const { country, countryName, city, curImage, version, curImageId,
        cloudIndex, imageName, peak_defence } = this.backfill
      this.promo = temp.customfield
      this.qty = temp.qty
      this.position = temp.position
      this.country = country
      this.countryName = countryName
      this.curImage = curImage
      this.city = city
      this.version = version
      this.curImageId = curImageId
      this.cloudIndex = cloudIndex
      this.imageName = imageName
      this.defenseName = peak_defence + 'G'
    }
  },
  watch: {
    'params.image_id' (id) {
      if (id) {
        this.showImage = false
      }
    },
    'params.data_center_id' (id) {
      this.curCpu = ''
      this.curMemory = ''
      if (this.isUpdate && this.isInit) {
        return
      }
      this.params.peak_defence = ''
      this.defenseName = ''
      this.params.model_config_id = this.calcModel[0]?.id
    },
    'params.line_id' (id) { // 区域改变，线路必定改变，根据线路改变拉取线路详情
      if (id) {
        this.lineChange = true
        this.lineType = this.lineList.filter(item => item.id === id)[0]?.bill_type
        this.getLineDetails(id)
      }
    },
    'params.model_config_id' (id) {
      if (this.isUpdate && this.isInit) {
        return
      }
      // this.isChangeArea = true
      if (!this.isChangeArea) {
        if (this.lineType === 'flow') {
          this.params.flow = this.calcFlowList[0].value
          this.flowName = this.params.flow + 'G'
        } else {
          if (this.bwType === 'radio') {
            this.params.bw = this.calcBwList[0].value || ''
          } else {
            this.params.bw = this.calcBwRange[0] * 1 || ''
          }
          this.bwName = this.params.bw + 'M'
        }
      }
      setTimeout(() => {
        if (!this.lineChange && !this.isInit) {
          this.getCycleList()
        }
      }, 0)
    }
  },
  computed: {
    calcArea () {
      const c = this.dataList.filter(item => item.id === this.country * 1)[0]?.name
      return c + this.city
    },
    calcAreaList () {// 计算区域列表
      const temp = this.dataList.filter(item => item.id === this.country * 1)[0]?.city.filter(item => item.name === this.city)[0]?.area || []
      if (!this.isChangeArea) {
        return temp
      }
      if (this.isUpdate && this.isInit) { } else {
        this.area_name = temp[0]?.name
        this.params.data_center_id = temp[0]?.id
      }
      // 根据区域变化，筛选符合条件的机型
      const limitArr = Array.from(new Set(this.configLimitList.filter(item => item.data_center_id === temp[0]?.id).reduce((all, cur) => {
        all.push(...cur.model_config_id)
        return all
      }, [])))
      this.filterModle = this.modelArr.filter(item => !limitArr.includes(String(item.id)))
      this.cpuSelect = this.modelArr.reduce((all, cur) => {
        const arr = all.filter(item => item.value === cur.cpu)
        if (arr.length === 0) {
          all.push({
            value: cur.cpu,
            label: cur.cpu
          })
        }
        return all
      }, [])
      this.memorySelect = this.modelArr.reduce((all, cur) => {
        const arr = all.filter(item => item.value === cur.memory)
        if (arr.length === 0) {
          all.push({
            value: cur.memory,
            label: cur.memory
          })
        }
        return all
      }, [])
      // 区域改变的时候，也需要计算线路展示
      return temp
    },
    calcUsable () {
      return this.dataList.filter(item => item.id === this.country * 1)[0]
        ?.city.filter(item => item.name === this.city)[0]
        ?.area.filter(item => item.id === this.params.data_center_id)[0]?.name
    },
    calcLine () {
      return this.dataList.filter(item => item.id === this.country * 1)[0]
        ?.city.filter(item => item.name === this.city)[0]
        ?.area.filter(item => item.id === this.params.data_center_id)[0]
        ?.line.filter(item => item.id === this.params.line_id)[0]?.name
    },
    calcCartName () {
      return this.isUpdate ? lang.product_sure_check : lang.product_add_cart
    },
    calcModel () {
      // 需要处理区域下面值设置了机型的时候，代表该机型可可选
      const arr = this.configLimitList.filter(item => item.data_center_id === this.params.data_center_id && item.line_id === 0)
      if (arr.length > 0) {
        const modelArr = Array.from(new Set(arr.reduce((all, cur) => {
          all.push(...cur.model_config_id)
          return all
        }, [])))
        this.modelArr = this.originModel.filter(item => !modelArr.includes(String(item.id)))
        console.log(this.modelArr)
      } else {
        this.modelArr = this.originModel
      }
      const temp = this.modelArr.reduce((all, cur) => {
        if (!this.curCpu && !this.curMemory) {
          all.push(cur)
        } else if (this.curCpu && !this.curMemory) {
          if (cur.cpu === this.curCpu) {
            all.push(cur)
          }
        } else if (!this.curCpu && this.curMemory) {
          if (cur.memory === this.curMemory) {
            all.push(cur)
          }
        } else {
          if (cur.cpu === this.curCpu && cur.memory === this.curMemory) {
            all.push(cur)
          }
        }
        return all
      }, [])
      return temp
    },
    calcSpecs () {
      return this.modelArr.filter(item => item.id === this.params.model_config_id)[0]?.name
    },
    calcBwList () { // 根据区域，机型以及 线路来判断计算可选带宽  单选
      const temp = this.configLimitList.filter(item =>
        this.params.data_center_id === item.data_center_id && this.params.line_id === item.line_id
        && item.model_config_id.includes(String(this.params.model_config_id))
      ) || []
      const bw = temp.reduce((all, cur) => {
        if (cur.min_bw) {
          all.push(...this.createArr([cur.min_bw * 1, cur.max_bw * 1]))
        }
        return all
      }, [])
      return this.lineDetail.bw.filter(item => !bw.includes(item.value))
    },
    calcBwRange () { // 根据区域，线路来判断计算可选带宽  范围
      const temp = this.configLimitList.filter(item =>
        this.params.data_center_id === item.data_center_id && this.params.line_id === item.line_id
        && item.model_config_id.includes(String(this.params.model_config_id))
      ) || []
      if (temp.length === 0) { // 没有匹配到限制条件
        this.bwTip = this.createTip(this.bwArr)
        this.bwMarks = this.createMarks(this.bwArr)
        return this.bwArr || []
      }
      let fArr = []
      temp.forEach(item => {
        fArr.push(...this.createArr([item.min_bw * 1, item.max_bw * 1]))
      })
      fArr = Array.from(new Set(fArr))
      const filterArr = this.bwArr.filter(item => !fArr.includes(item))
      this.bwTip = this.createTip(filterArr)
      this.bwMarks = this.createMarks(filterArr) // data 原数据，目标marks
      return filterArr.filter(item => !fArr.includes(item))
    },
    calcFlowList () { // 根据区域，线路来判断计算可选带宽  单选
      const temp = this.configLimitList.filter(item =>
        this.params.data_center_id === item.data_center_id && this.params.line_id === item.line_id
        && item.model_config_id.includes(String(this.params.model_config_id))
      ) || []

      const flow = temp.reduce((all, cur) => {
        if (cur.min_flow) {
          all.push(...this.createArr([cur.min_flow * 1, cur.max_flow * 1]))
        }
        return all
      }, [])
      return this.lineDetail.flow.filter(item => !flow.includes(item.value))
    },
    calcLineList () { // 区域，机型改变重置线路
      const temp = this.dataList.filter(item => item.id === this.country * 1)[0]?.city.filter(item => item.name === this.city)[0]?.area || []
      // 如果限制里面对应的线路，流量和带宽均无说明是全限制，则不显示tab
      const areaLimt = this.configLimitList.filter(item =>
        item.data_center_id === this.params.data_center_id && item.model_config_id.includes(String(this.params.model_config_id))
        && item.min_bw === '' && item.min_flow === ''
      )
      let lineId = [] // 限制里面的线路id
      if (areaLimt.length > 0) {
        lineId = Array.from(new Set(areaLimt.reduce((all, cur) => {
          all.push(cur.line_id)
          return all
        }, [])))
      }
      if (temp.length > 0) {
        this.lineList = temp.filter(item => item.id === this.params.data_center_id)[0]?.line.filter(item => !lineId.includes(item.id))
        if (!this.isChangeArea) {
          this.lineName = this.lineList.filter(item => item.id === this.params.line_id)[0]?.name
        } else {
          this.params.line_id = this.lineList[0]?.id
          this.lineName = this.lineList[0]?.name
        }
      }
      return this.lineList
    },
    calcImageList () {
      if (!this.isHide) {
        return this.imageList
      } else {
        const temp = JSON.parse(JSON.stringify(this.imageList))
        return temp.splice(0, 4)
      }
    },
  },

  methods: {
    getQuery (name) {
      const reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i')
      const r = window.location.search.substr(1).match(reg)
      if (r != null) return decodeURI(r[2])
      return null
    },
    // 配置数据
    async getConfig () {
      try {
        const params = {
          id: this.id
        }
        if (this.activeName === 'fast') {
          params.scene = 'recommend'
        }
        const res = await getOrderConfig(params)
        const temp = res.data.data
        // 通用数据处理
        this.dataList = temp.data_center
        this.originModel = this.modelArr = temp.model_config
        this.configLimitList = temp.config_limit

        // 如果没有推荐配置，跳转到自定义，重新获取数据
        if (this.dataList.length === 0) {
          this.activeName = 'custom'
          this.showFast = false
          this.getConfig()
          return
        }
        // 初始化数据
        if (!this.isUpdate) { // 不是回填
          this.params = {
            data_center_id: '',
            image_id: 0,
            line_id: '',
            bw: '',
            flow: '',
            peak_defence: '',
            ip_num: '',
            duration_id: '',
            notes: '',
            model_config_id: '', // 型号id
            auto_renew: false
          }
          this.qty = 1
          this.country = String(this.dataList[0]?.id)
          this.countryName = String(this.dataList[0]?.name)
          this.city = String(this.dataList[0]?.city[0]?.name)
          this.cloudIndex = 0
          this.params.model_config_id = this.modelArr[0].id
        } else {
          // 回填数据
          this.params = this.backfill
          // 根据区域变化，筛选符合条件的机型
          const filArea = this.dataList.filter(item => item.id === this.country * 1)[0]?.city.filter(item => item.name === this.city)[0]?.area || []
          const limitArr = Array.from(new Set(this.configLimitList.filter(item => item.data_center_id === filArea[0]?.id).reduce((all, cur) => {
            all.push(...cur.model_config_id)
            return all
          }, [])))
          this.filterModle = this.modelArr.filter(item => !limitArr.includes(String(item.id)))
          this.cpuSelect = this.filterModle.reduce((all, cur) => {
            const arr = all.filter(item => item.value === cur.cpu)
            if (arr.length === 0) {
              all.push({
                value: cur.cpu,
                label: cur.cpu
              })
            }
            return all
          }, [])
          this.memorySelect = this.filterModle.reduce((all, cur) => {
            const arr = all.filter(item => item.value === cur.memory)
            if (arr.length === 0) {
              all.push({
                value: cur.memory,
                label: cur.memory
              })
            }
            return all
          }, [])

        }
        this.totalPrice = 0.00
        this.isInit = true
        this.handlerCustom()
      } catch (error) {
        console.log('@@@', error)
      }
    },
    // 切换自定义配置
    handlerCustom () {
      if (!this.isUpdate) {

      } else { // 回填
        this.area_name = this.calcAreaList.filter(item => item.id === this.params.data_center_id)[0]?.name
      }
    },
    /* 线路 */
    changeLine (e) {
      this.params.line_id = this.lineList.filter(item => item.name === e)[0]?.id
    },
    async getLineDetails (id) {
      try {
        if (!id) {
          return
        }
        // 获取线路详情，
        const res = await getLineDetail({ id: this.id, line_id: id })
        this.lineDetail = res.data.data
        // 公网IP
        this.ipData = this.lineDetail.ip
        if (this.isUpdate && this.isInit) { } else {
          this.params.ip_num = this.ipData[0].value
        }

        this.ipName = this.params.ip_num + lang.mf_one
        if (this.lineDetail.bw) {
          if (this.isInit && this.isUpdate) { // 初次回填
          } else {
            this.params.bw = this.calcBwList[0]?.value || this.calcBwRange[0] || ''
          }
          this.bwType = this.lineDetail.bw[0]?.type
          this.bwName = this.params.bw + 'M'
          // 循环生成带宽可选数组
          const fArr = []
          this.lineDetail.bw.forEach(item => {
            fArr.push(...this.createArr([item.min_value, item.max_value]))
          })
          this.bwArr = fArr
          this.bwTip = this.createTip(fArr)
        }
        if (this.lineDetail.flow) {
          if (this.isInit && this.isUpdate) { // 初次回填
          } else {
            this.params.flow = this.calcFlowList[0]?.value || ''
          }
          this.flowName = this.params.flow > 0 ? (this.params.flow + 'G') : lang.mf_tip28
        }
        this.bwMarks = this.createMarks(this.bwArr)
        setTimeout(() => {
          this.getCycleList()
        }, 0)
      } catch (error) {
        console.log('####', error)
      }
    },
    changeBw (e) {
      this.params.bw = e.replace('M', '')
      // 计算价格
      setTimeout(() => {
        this.getCycleList()
      }, 0)
    },
    changeBwNum (num) {
      if (!this.calcBwRange.includes(num)) {
        this.calcBwRange.forEach((item, index) => {
          if (num > item && num < this.calcBwRange[index + 1]) {
            this.params.bw = (num - item) > (this.calcBwRange[index + 1] - num) ? this.calcBwRange[index + 1] : item
          }
        })
      }
      this.getCycleList()
    },
    // 选中/取消防御
    chooseDefence (e, c) {
      if (this.defenseName === c.value + 'G') {
        this.defenseName = ''
        this.params.peak_defence = ''
      } else {
        this.defenseName = c.value + 'G'
        this.params.peak_defence = c.value
      }
      setTimeout(() => {
        this.getCycleList()
      }, 0)
      e.preventDefault();
    },
    // 切换流量
    changeFlow (e) {
      if (e === lang.mf_tip28) {
        this.params.flow = 0
      } else {
        this.params.flow = e.replace('G', '') * 1
      }

      setTimeout(() => {
        this.getCycleList()
      }, 0)
    },
    // 切换IP
    changeIp (e) {
      this.params.ip_num = e.replace(lang.mf_one, '')
      setTimeout(() => {
        this.getCycleList()
      }, 0)
    },
    createArr ([m, n]) {// 生成数组
      let temp = []
      for (let i = m; i <= n; i++) {
        temp.push(i * 1)
      }
      return temp
    },
    createTip (arr) { // 生成范围提示
      let tip = ''
      let num = []
      arr.forEach((item, index) => {
        if (arr[index + 1] - item > 1) {
          num.push(index)
        }
      })
      if (num.length === 0) {
        tip = `${arr[0]}-${arr[arr.length - 1]}`
      } else {
        tip += `${arr[0]}-${arr[num[0]]},`
        num.forEach((item, ind) => {
          tip += arr[item + 1] + '-' + (arr[num[ind + 1]] ? (arr[num[ind + 1]] + ',') : arr[arr.length - 1])
        })
      }
      return tip
    },
    createMarks (data) {
      const obj = {
        0: '',
        25: '',
        50: '',
        75: '',
        100: ''
      }
      const range = data[data.length - 1] - data[0]
      obj[0] = `${data[0]}`
      obj[25] = `${data[0] + Math.ceil(range * 0.25)}`
      obj[50] = `${data[0] + Math.ceil(range * 0.5)}`
      obj[75] = `${data[0] + Math.ceil(range * 0.75)}`
      obj[100] = `${data[data.length - 1]}`
      return obj
    },
    // 选择区域
    changeArea (e) {
      console.log(11111)
      this.isChangeArea = false
      // 手动切换区域不初始化第一个区域
      this.params.data_center_id = this.calcAreaList.filter(item => item.name === e)[0]?.id
      this.area_name = this.calcAreaList.filter(item => item.name === e)[0]?.name
      this.lineList = this.calcAreaList.filter(item => item.name === e)[0]?.line
      this.params.line_id = this.lineList[0].id
      this.lineName = this.lineList[0].name

    },
    // 选择先线路
    chooseLine (item) {
      this.params.data_center_id = item.data_center_id
      this.params.line_id = item.id
    },
    // 切换套餐
    changeRecommend (item, index) {
      this.cloudIndex = index
      if (this.packageId === item.id) {
        return
      }
      // 赋值
      this.packageId = item.id
      const temp = JSON.parse(JSON.stringify(item))
      delete temp.data_disk_size
      delete temp.data_disk_type
      delete temp.system_disk_size
      delete temp.system_disk_type
      Object.assign(this.params, temp)
      this.params.data_disk = []
      if (item.data_disk_size * 1) {
        this.params.data_disk.push({
          size: item.data_disk_size,
          disk_type: item.data_disk_type
        })
      } else {
        this.params.data_disk = []
      }
      this.params.name = ''
      this.getCycleList()
    },
    // 切换城市
    changeCity (e, city) {
      this.isChangeArea = true
      this.cloudIndex = 0
    },
    tableRowClassName ({ row, rowIndex }) {
      row.index = rowIndex
    },

    // 提交前格式化数据
    formatData () {
      if (!this.params.image_id) {
        document.getElementById('image').scrollIntoView({ behavior: "smooth" })
        this.showImage = true
        return
      }
      // ssh
      if (this.login_way === lang.security_tab1 && !this.params.ssh_key_id) {
        return this.$message.warning(`${lang.placeholder_pre2}${lang.security_tab1}`)
      }
      return true
    },
    // 立即购买
    async submitOrder () {
      this.$refs.orderForm.validate(async (res) => {
        if (res) {
          const bol = this.formatData()
          if (bol !== true) {
            return
          }
          try {
            const params = {
              product_id: this.id,
              config_options: {
                ...this.params,
              },
              qty: this.qty,
              customfield: this.promo
            }
            if (this.lineDetail.bill_type === 'bw') {
              delete params.flow
            } else {
              delete params.bw
            }
            // 直接传配置到结算页面
            sessionStorage.setItem('product_information', JSON.stringify(params))
            location.href = `settlement.html?id=${params.product_id}`
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        }
      })
    },
    handlerCart () {
      if (this.isUpdate) {
        this.changeCart()
      } else {
        this.addCart()
      }
    },
    // 加入购物车
    addCart () {
      this.$refs.orderForm.validate(async (res) => {
        if (res) {
          const bol = this.formatData()
          if (bol !== true) {
            return
          }
          try {
            const params = {
              product_id: this.id,
              config_options: {
                ...this.params,
                // 其他需要回显的页面数据 
                activeName: this.activeName,
                country: this.country,
                countryName: this.countryName,
                city: this.city,
                curImage: this.curImage,
                curImageId: this.curImageId,
                imageName: this.imageName,
                version: this.version,
                cloudIndex: this.cloudIndex,
              },
              qty: this.qty,
              customfield: this.promo,
            }
            if (this.lineDetail.bill_type === 'bw') {
              delete params.flow
            } else {
              delete params.bw
            }
            const res = await addToCart(params)
            if (res.data.status === 200) {
              this.cartDialog = true
              const result = await getCart()
              localStorage.setItem('cartNum', 'cartNum-' + result.data.data.list.length)
            }
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        }
      })
    },
    // 修改购物车
    async changeCart () {
      this.$refs.orderForm.validate(async (res) => {
        if (res) {
          const bol = this.formatData()
          if (bol !== true) {
            return
          }
          try {
            const params = {
              position: this.position,
              product_id: this.id,
              config_options: {
                ...this.params,
                // 其他需要回显的页面数据 
                activeName: this.activeName,
                country: this.country,
                countryName: this.countryName,
                city: this.city,
                curImage: this.curImage,
                curImageId: this.curImageId,
                imageName: this.imageName,
                version: this.version,
                cloudIndex: this.cloudIndex,
                login_way: this.login_way
              },
              qty: this.qty,
              customfield: this.promo,
            }
            if (this.lineDetail.bill_type === 'bw') {
              delete params.flow
            } else {
              delete params.bw
            }
            this.dataLoading = true
            const res = await updateCart(params)
            this.$message.success(res.data.msg)
            setTimeout(() => {
              location.href = `shoppingCar.html`
            }, 300)
            this.dataLoading = false
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        }
      })
    },
    goToCart () {
      location.href = `shoppingCar.html`
      this.cartDialog = false
    },
    changeCountry () {
      this.countryName = this.dataList.filter(item => item.id === this.country * 1)[0]?.name
      this.isChangeArea = true
      this.city = this.dataList.filter(item => item.id === this.country * 1)[0].city[0]?.name
      this.cloudIndex = 0
      if (this.activeName === 'fast') {
        this.handlerFast()
      }
    },
    changQty () {
      if (this.promo.promo_code) {
        this.useDiscount()
      }
    },
    // 使用优惠码
    async useDiscount () {
      try {
        if (this.promo.promo_code.length !== 9) {
          this.showErr = true
          return
        }
        const params = JSON.parse(JSON.stringify(this.promo))
        params.product_id = this.id
        params.qty = this.qty
        params.amount = this.qty * this.totalPrice
        params.billing_cycle_time = this.duration
        const res = await usePromo(params)
        this.$message.success(res.data.msg)
        this.discount = res.data.data.discount
        this.dis_visible = false
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    canclePromo () {
      this.discount = ''
      this.promo.promo_code = ''
    },
    // 获取镜像
    async getIamgeList () {
      try {
        const res = await getSystemList({ id: this.id })
        this.imageList = res.data.data.list
      } catch (error) {
      }
    },
    changeDuration () {
      this.loadingPrice = true
      this.changeConfig()
    },
    // 获取周期
    async getCycleList () {
      try {
        this.lineChange = false
        this.loadingPrice = true
        const params = JSON.parse(JSON.stringify(this.params))
        params.id = this.id
        const hasDuration = params.duration_id
        if (hasDuration) {
          this.changeConfig()
        }
        const res = await getDuration(params)
        this.cycleList = res.data.data
        this.params.duration_id = this.params.duration_id || this.cycleList[0]?.id
        if (!hasDuration) {
          this.changeConfig()
        }
      } catch (error) {
      }
    },
    // 更改配置计算价格
    async changeConfig () {
      try {
        const params = {
          id: this.id,
          config_options: {
            ...this.params
          },
          qty: this.qty
        }
        const res = await calcPrice(params)
        this.totalPrice = res.data.data.price
        this.preview = res.data.data.preview
        this.duration = res.data.data.duration
        this.loadingPrice = false
        this.isInit = false
        // 如果已使用优惠码，计算价格过后重新计算
        if (this.promo.promo_code) {
          this.useDiscount()
        }
      } catch (error) {
        this.loadingPrice = false
        this.$message.error(error.data.msg)
      }
    },
    // 获取通用配置
    getCommonData () {
      this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
      document.title = this.commonData.website_name + '-' + this.tit
    },
    mouseenter (index) {
      if (index === this.curImage) {
        this.hover = true
      }
    },
    changeImage (item, index) {
      this.imageName = item.name
      this.curImage = index
      this.hover = true
    },
    chooseVersion (ver, id) {
      this.curImageId = id
      this.version = ver.name
      this.params.image_id = ver.id
      this.getCycleList()
    },
  }
}).$mount(template)