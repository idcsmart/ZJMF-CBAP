const template = document.getElementsByClassName('disk-config')[0]
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
      diskModel: false,
      dataTitle: '',
      diskList: [],
      diskForm: {
        order: 0,
        name: '',
        description: '',
        module_idcsmart_cloud_data_center_id: '',
        size_min: '',
        size_max: '',
        precision: '',
        price: ''
      },
      diskRules: {
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
        module_idcsmart_cloud_data_center_id: [
          { required: true, message: lang.select + lang.available_area, type: 'error' },
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
          colKey: 'disk_ratio',
          title: lang.disk_scale,
          width: 400,
          ellipsis: true
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 80
        },
      ],
      diskColumns: [ // 磁盘表格
        {
          colKey: 'order',
          title: lang.sort + 'ID',
          width: 100
        },
        {
          colKey: 'name',
          title: lang.nickname,
          width: 150,
          ellipsis: true
        },
        {
          colKey: 'area',
          title: lang.available_area,
          width: 700
        },
        {
          colKey: 'range',
          title: lang.capacity_range + '(GB)',
          width: 144,
          ellipsis: true
        },
        {
          colKey: 'price',
          title: lang.unit_price,
          width: 150
        },
        {
          colKey: 'op',
          title: lang.manage,
          width: 100
        },
      ],
      currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix || '¥'
    }
  },
  created () {
    this.id = location.href.split('?')[1].split('=')[1]
    // 带宽列表
    this.getDiskList()
    // 获取周期
    this.getDurationList()
    // 数据中心
    this.getDataCenterList()
  },
  methods: {
    checkSize (val) {
      if (val * 1 >= this.diskForm.size_max * 1) {
        return { result: false, message: lang.disk_tip, type: 'warning' }
      }
      return { result: true }
    },
    checkSize1 (val) {
      if (val * 1 <= this.diskForm.size_min * 1) {
        return { result: false, message: lang.disk_tip, type: 'warning' }
      }
      return { result: true }
    },
    changeSize () {
      this.$refs.diskForm.validate({
        fields: ['size_min', 'size_max']
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
        const { id, disk_ratio } = row
        const data = [
          {
            id, disk_ratio
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
      this.diskModel = true
      this.dataTitle = lang.create + lang.disk_package
      this.$refs.diskForm && this.$refs.diskForm.reset()
      this.diskForm.order = 0
      this.diskForm.description = ''
      this.diskForm.name = ''
      this.diskForm.module_idcsmart_cloud_data_center_id = ''
      this.diskForm.size_min = ''
      this.diskForm.size_max = ''
      this.diskForm.precision = ''
      this.diskForm.price = ''
    },
    editDisk (row) {
      this.optType = 'update'
      const temp = JSON.parse(JSON.stringify(row))
      this.diskForm = temp
      this.dataTitle = lang.update + lang.disk_package
      this.diskModel = true
    },
    // 添加/编辑磁盘
    async submitDisk ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.diskForm))
          params.product_id = this.id
          if (this.optType === 'add') {
            delete params.id
          }
          const res = await addAndUpdateDisk(this.optType, params)
          this.$message.success(res.data.msg)
          this.diskModel = false
          this.getDiskList()
          this.closeDisk()
        } catch (error) {
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },
    closeDisk () {
      this.$refs.diskForm.clearValidate()
      this.diskModel = false
    },
    // 删除磁盘
    async deleteDisk () {
      try {
        const res = await delDisk(this.delId)
        this.$message.success(res.data.msg)
        this.getDiskList()
        this.delVisible = false
      } catch (error) {
        this.$message.error(error.data.msg)
        this.delVisible = false
      }
    },
    // 获取磁盘列表
    async getDiskList () {
      try {
        const params = {
          product_id: this.id,
          page: 1,
          limit: 1000
        }
        this.diskLoading = true
        const res = await getDisk(params)
        const temp = res.data.data.list.map(item => {
          item.status = false
          return item
        })
        this.diskList = temp
        this.diskLoading = false
      } catch (error) {
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