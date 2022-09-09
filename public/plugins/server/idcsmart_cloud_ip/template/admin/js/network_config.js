const template = document.getElementsByClassName('network-config')[0]
Vue.prototype.lang = window.lang
new Vue({
  data () {
    return {
      id: '',
      tabs: 'basic',
      allStatus: false, // 总的编辑状态
      calGroupList: [], // 计算型号分组列表
      calParams: {
        page: 1,
        limit: 1000
      },
      hover: true,
      tableLayout: false,
      dataLoading: false,
      packageLoading: false,
      bwLoading: false,
      calLoading: false,
      delVisible: false,
      optType: 'add', // 操作类型
      delType: '', // 删除的类型
      delId: '', // 删除的id
      // 磁盘
      dataList: [],
      diskLoading: false,
      networkModel: false,
      dataTitle: '',
      networkList: [],
      networkForm: {
        ip_enable: 0,
        ip_price: '',
        ip_max: '',
        bw_enable: 0,
        bw_precision: '',
        bw_price: [{
          id: new Date().getTime(),
          min: 0,
          max: '',
          price: ''
        }]
      },
      networkRules: {
        order: [
          { required: true, message: lang.input + lang.sort + 'ID', type: 'error' },
          {
            pattern: /^[0-9]\d*$/, message: lang.verify7, type: 'warning'
          }
        ],
        name: [
          { required: true, message: lang.input + lang.show_name, type: 'error' },
        ],
        description: [
          { required: true, message: lang.input + lang.description, type: 'error' },
        ],
        size_min: [
          { required: true, message: lang.input + lang.bw, type: 'error' },
          {
            pattern: /^\d+$/, message: lang.verify7, type: 'warning'
          }
        ],
        size_max: [
          { required: true, message: lang.input + lang.bw, type: 'error' },
          {
            pattern: /^\d+$/, message: lang.verify7, type: 'warning'
          }
        ],
        precision: [
          { required: true, message: lang.input + lang.min_precision, type: 'error' },
          {
            pattern: /^([1-9][0-9]*)$/, message: lang.input + lang.verify16, type: 'warning'
          }
        ],
        price: [
          { required: true, message: lang.input + lang.price, type: 'error' },
          {
            pattern: /^\d+(\.\d{0,2})?$/, message: lang.verify5, type: 'warning'
          },
          {
            validator: val => val > 0, message: lang.verify5, type: 'warning'
          }
        ],
      },
      // 周期
      durationList: [],
      durationLoading: false,
      durationColumns: [ // 套餐表格
        {
          colKey: 'duration',
          title: lang.duration,
          width: 175
        },
        {
          colKey: 'display_name',
          title: lang.display_name,
          width: 300,
          ellipsis: true
        },
        {
          colKey: 'ip_ratio',
          title: 'IP',
          width: 400,
          ellipsis: true
        },
        {
          colKey: 'bw_ratio',
          title: lang.bw,
          width: 400,
          ellipsis: true
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 80
        },
      ],
      networkColumns: [ // 网络表格
        {
          colKey: 'bw_type_name',
          title: lang.bw_type,
          width: 150,
          ellipsis: true
        },
        {
          colKey: 'area',
          title: lang.area,
          width: 850
        },
        {
          colKey: 'ip_enable',
          title: lang.attach + 'IP',
          width: 100
        },
        {
          colKey: 'bw_enable',
          title: lang.alone_bw,
          width: 100
        },
        {
          colKey: 'op',
          title: lang.manage,
          width: 75
        },
      ],
      validateIndex: 0,
      currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix || '¥'
    }
  },
  computed: {
    calMin: () => {
      return (ind, form) => {
        if (ind === 0) {
          return 0
        } else {
          return form.bw_price[ind - 1].max * 1
        }
      }
    }
  },
  created () {
    this.id = location.href.split('?')[1].split('=')[1]
    // 网络列表
    this.getNetworkList()
    // 获取周期
    this.getDurationList()
    // 数据中心
    this.getDataCenterList()
  },
  methods: {
    // 切换 附加ip
    changeIp (e) {
      this.networkForm.ip_enable = e === 1 ? 0 : 1
      if (this.networkForm.ip_enable === 0) {
        this.networkForm.bw_enable = 0
      }
    },
    changeBw (e) {
      if (this.networkForm.ip_enable === 0) {
        return this.$message.warning(lang.net_tip)
      }
      this.networkForm.bw_enable = e === 1 ? 0 : 1
    },
    addPrice (index) {
      if (this.networkForm.bw_price[index].min === '') {
        return this.$message.warning(lang.input + lang.min_num)
      }
      if (this.networkForm.bw_price[index].max === '') {
        return this.$message.warning(lang.input + lang.max_num)
      }
      if (this.networkForm.bw_price[index].price === '') {
        return this.$message.warning(lang.input + lang.unit_price)
      }
      this.networkForm.bw_price.push(
        {
          id: new Date().getTime(),
          min: this.networkForm.bw_price[index].max || 0,
          max: '',
          price: ''
        }
      )
    },
    deletePrice (index) {
      this.networkForm.bw_price.splice(index, 1)
    },
    checkSize (val) {
      const ind = this.networkForm.bw_price.findIndex(item => item.min === val)
      this.validateIndex = ind
      if (val * 1 >= this.networkForm.bw_price[ind].max * 1) {
        return { result: false, message: lang.disk_tip, type: 'warning' }
      }
      return { result: true }
    },
    checkSize1: (val, index, form) => {
      return () => {
        if (val * 1 <= form.bw_price[index].min * 1) {
          return { result: false, message: lang.disk_tip, type: 'warning' }
        }
        return { result: true }
      }
    },
    changeNum () {
      this.$refs.networkForm.validate({
        fields: [`bw_price[${this.validateIndex}].min`, `bw_price[${this.validateIndex}].max`]
      });
    },
    changeNum1 (val, ind) {
      if (ind < this.networkForm.bw_price.length - 1) {
        this.networkForm.bw_price[ind + 1].min = val * 1
      }
      this.$refs.networkForm.validate({
        fields: [`bw_price[${this.validateIndex}].min`, `bw_price[${this.validateIndex}].max`]
      });
    },
    /* 获取数据中心 */
    async getDataCenterList () {
      try {
        const params = {
          product_id: 30,
          page: 1,
          limit: 100
        }
        this.dataLoading = true
        const res = await getDataCenter(params)
        const temp = res.data.data.list.map(item => {
          item.status = false // 是否编辑状态
          return item
        })
        this.dataList = temp
        this.dataLoading = false
      } catch (error) {
        this.$message.error(error.data.msg)
        this.dataLoading = false
      }
    },
    /* 基本配置 */
    // 周期
    async getDurationList () {
      try {
        this.durationLoading = true
        const res = await getDuration(this.id)
        const temp = res.data.data.list.map(item => {
          item.status = false
          return item
        })
        this.durationList = temp
        this.durationLoading = false
      } catch (error) {
        this.durationLoading = false
        this.$message.error(error.data.msg)
      }
    },
    editDuration (row) {
      if (this.allStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      row.status = true
      this.allStatus = true
    },
    async saveDuration (row) {
      try {
        const { id, ip_ratio, bw_ratio } = row
        const data = [
          {
            id, ip_ratio, bw_ratio
          }
        ]
        const res = await updateDuration({ data })
        this.$message.success(res.data.msg)
        this.allStatus = false
        row.status = false
        this.getDurationList()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    /* 磁盘列表 */
    addDisk () {
      if (this.allStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      this.optType = 'add'
      this.networkModel = true
      this.dataTitle = lang.create + lang.disk_package
      this.$refs.networkForm && this.$refs.networkForm.reset()

      this.networkForm.ip_enable = 0
      this.networkForm.ip_price = ''
      this.networkForm.ip_max = ''
      this.networkForm.bw_enable = 0
      this.networkForm.size_min = ''
      this.networkForm.size_max = ''
      this.networkForm.precision = ''
      this.networkForm.price = ''
    },
    editDisk (row) {
      this.optType = 'update'
      const temp = JSON.parse(JSON.stringify(row))
      if (Array.from(temp.bw_price).length === 0) {
        temp.bw_price = [
          {
            id: new Date().getTime(),
            min: 0,
            max: '',
            price: ''
          }
        ]
      }
      this.networkForm = temp
      this.dataTitle = lang.update + lang.bw
      this.networkModel = true
    },
    // 添加/编辑磁盘
    async submitNetwork ({ validateResult, firstError }) {
      const {
        id, ip_enable, ip_price, ip_max, bw_enable, bw_precision, bw_price
      } = this.networkForm
      if (bw_enable === 0) {
        this.$refs.networkForm.clearValidate()
      }
      if ((validateResult === true) || (bw_enable === 0 && bw_precision === '')) {
        try {
          const params = {
            id, ip_enable,
            ip_price,
            ip_max,
            bw_enable,
            bw_precision,
            bw_price,
            product_id: this.id
          }
          params.bw_price = params.bw_price.filter(item => {
            if (Number(item.max) !== 0) {
              return item
            }
          })
          params.bw_price.map(item => {
            delete item.id
            item.min = Number(item.min)
            item.max = Number(item.max)
            item.price = Number(item.price)
            return item
          })
          if (params.bw_price.length === 0) {
            delete params.bw_price
          }
          const res = await addAndUpdateNetwork(this.optType, params)
          this.$message.success(res.data.msg)
          this.networkModel = false
          this.getNetworkList()
          this.closeDisk()
        } catch (error) {
          this.$message.error(error.data.msg)
        }
      } else {
        this.$message.warning(firstError);
      }
    },
    closeDisk () {
      this.$refs.networkForm.clearValidate()
      this.networkModel = false
    },
    // 删除磁盘
    async deleteDisk () {
      try {
        const res = await delDisk(this.delId)
        this.$message.success(res.data.msg)
        this.getnetworkList()
        this.delVisible = false
      } catch (error) {
        this.$message.error(error.data.msg)
        this.delVisible = false
      }
    },
    // 获取磁盘列表
    async getNetworkList () {
      try {
        const params = {
          product_id: this.id,
          page: 1,
          limit: 1000
        }
        this.diskLoading = true
        const res = await getNetwork(params)
        const temp = res.data.data.list.map(item => {
          item.status = false
          if (item.ip_price == 0) {
            item.ip_price = ''
          }
          if (item.ip_max == 0) {
            item.ip_max = ''
          }
          if (item.bw_precision == 0) {
            item.bw_precision = ''
          }
          return item
        })
        this.networkList = temp
        this.diskLoading = false
      } catch (error) {
        console.log(error)
        this.$message.error(error.data.msg)
        this.diskLoading = false
      }
    },
    /* 通用删除按钮 */
    comDel (type, row) {
      this.delId = row.id
      this.delType = type
      this.delVisible = true
      this.parent_id = row.module_idcsmart_cloud_cal_group_id
    },
    // 通用删除
    sureDelete () {
      switch (this.delType) {
        case 'bw':
          return this.deleteDisk()
        default:
          return null
      }
    }
  },
}).$mount(template)