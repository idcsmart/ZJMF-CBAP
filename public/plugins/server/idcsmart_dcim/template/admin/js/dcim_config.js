const template = document.getElementsByClassName('dcim')[0]
Vue.prototype.lang = window.lang
new Vue({
  data () {
    return {
      id: '',
      // 通用
      tabs: 'package', // package,data_center,system,other
      hover: true,
      tableLayout: false,
      dataLoading: false,
      dataTitle: '',
      delVisible: false,
      delId: '',
      delType: '',
      payType: '', // 计费方式 free , onetime, recurring_prepayment , recurring_postpaid
      currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix || '¥',
      optType: 'add', // 新增/编辑
      // 套餐
      curModel: 0,
      package_total: 0,
      packageParams: {
        page: 1,
        limit: 20,
        orderby: 'order'
      },
      selectDataCenter: [], // 套餐下拉数据
      packageForm: {// 套餐表单
        name: '',
        data_center_id: [],
        description: '',
        // 详细配置
        dcim_server_group_id: '',
        in_bw: '',
        out_bw: '',
        ip_num: '',
        ip_group: '',
        custom_param: '',
        traffic_enable: 1,
        flow: '',
        traffic_bill_type: 'month',
        // 一次性
        onetime_fee: '',
        // 周期
        month_fee: '',
        quarter_fee: '',
        half_year_fee: '',
        year_fee: '',
        two_year: '',
        three_year: ''
      },
      curCarging: '1', // 1 一次性 2周期
      packageRules: {
        name: [
          { required: true, message: lang.input + lang.package + lang.nickname, type: 'error' },
          {
            validator: val => val.length <= 20, message: lang.verify8 + '1-20', type: 'warning'
          }
        ],
        data_center_id: [
          { required: true, message: lang.select + lang.data_center, type: 'error' },
        ],
        description: [
          { required: true, message: lang.input + lang.description, type: 'error' },
        ],
        dcim_server_group_id: [
          { required: true, message: lang.input + lang.sales_id_group, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + lang.package_tip, type: 'warning'
          }
        ],
        memory: [
          { required: true, message: lang.input + lang.memory, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + '128-524288' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 128 && val <= 524288, message: lang.input + '128-524288' + lang.verify2, type: 'warning'
          }
        ],
        system_disk_size: [
          { required: true, message: lang.input + lang.system_disk_size, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + '1-1048576' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 1 && val <= 1048576, message: lang.input + '1-1048576' + lang.verify2, type: 'warning'
          }
        ],
        free_data_disk_size: [
          {
            pattern: /^[0-9]*$/, message: lang.input + '1-1048576' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 1 && val <= 1048576, message: lang.input + '1-1048576' + lang.verify2, type: 'warning'
          }
        ],
        /* 带宽 */
        in_bw: [
          { required: true, message: lang.input + lang.in + lang.bw, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + '0-999999' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 0 && val <= 999999, message: lang.input + '0-999999' + lang.verify2, type: 'warning'
          }
        ],
        out_bw: [
          { required: true, message: lang.input + lang.out + lang.bw, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + '0-999999' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 0 && val <= 999999, message: lang.input + '0-999999' + lang.verify2, type: 'warning'
          }
        ],
        ip_num: [
          { required: true, message: lang.input + lang.ip_num, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + lang.package_tip, type: 'warning'
          },
          {
            validator: val => val >= 0 && val <= 999999, message: lang.input + '0-999999' + lang.verify2, type: 'warning'
          }
        ],
        custom_param: [
          {
            validator: val => val.length <= 200, message: lang.verify8 + '0-200', type: 'warning'
          }
        ]
      },
      packageModel: false,
      packageList: [],
      pageSizeOptions: [20, 50, 100],
      packageColumns: [ // 套餐表格
        {
          colKey: 'order',
          title: lang.sort + 'ID',
          width: 80
        },
        {
          colKey: 'name',
          title: lang.package + lang.nickname,
          width: 250,
          ellipsis: true
        },
        {
          colKey: 'data_center_id',
          title: lang.data_center,
          width: 150
        },
        {
          colKey: 'dcim_server_group_id',
          title: lang.sales_id_group,
          width: 100
        },
        {
          colKey: 'bw',
          title: lang.bw + `（${lang.in}/${lang.out}）`,
          width: 100,
          ellipsis: true
        },
        {
          colKey: 'ip_num',
          title: lang.ip_num,
          width: 120,
          ellipsis: true
        },
        {
          colKey: 'cycle',
          title: lang.money,
          width: 100,
          ellipsis: true
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 80
        },
      ],
      /* 数据中心 */
      allStatus: false,
      data_total: 0,
      dataParams: {
        page: 1,
        limit: 20,
        orderby: 'order'
      },
      dataModel: false,
      dataList: [],
      dataForm: {
        country_id: '',
        city: ''
      },
      dataRules: {
        country_id: [
          { required: true, message: lang.select + lang.area, type: 'error' },
        ],
        city: [
          { required: true, message: lang.input + lang.city, type: 'error' },
        ],
        cloud_config: [
          { required: true, message: lang.select + lang.city, type: 'error' },
        ],
        cloud_config_id: [
          { required: true, message: lang.input + 'ID', type: 'error' },
        ],
      },
      countryList: [],
      // 配置选项
      dataConfig: [
        { value: 'node', lable: lang.node + 'ID' },
        { value: 'area', lable: lang.area + 'ID' },
        { value: 'node_group', lable: lang.node_group + 'ID' },
      ],
      submitLoading: false,
      pageSizeOptions: [20, 50, 100],
      dataColumns: [ // 数据中心表格
        {
          colKey: 'order',
          title: lang.sort + 'ID',
          width: '20%'
        },
        {
          colKey: 'country_name',
          title: lang.area + lang.nickname,
          ellipsis: true,
          className: 'country_name',
          width: '50%'
        },
        {
          colKey: 'city',
          title: lang.city,
          width: '20%'
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 100
        },
      ],
      /* 操作系统 */
      systemList: [],
      systemColumns: [ // 套餐表格
        {
          colKey: 'id',
          title: lang.order_index,
          width: 50
        },
        {
          colKey: 'image_group_name',
          title: lang.system_classify,
          width: 150,
          ellipsis: true
        },
        {
          colKey: 'name',
          title: lang.system_name,
          width: '40%'
        },
        {
          colKey: 'pay',
          title: lang.pay_system,
          width: '25%',
          ellipsis: true
        },
        {
          colKey: 'available',
          title: lang.available,
          width: 80
        },
      ],
      imageStatus: false,
      systemName: [],
      image_group_id: '',
    }
  },
  created () {
    this.id = location.href.split('?')[1].split('=')[1]
    this.getProDetail()
    this.getPackageList()
  },
  computed: {
    isShowTip () {
      if (this.selectDataCenter.length > 0 && (this.packageForm.data_center_id.length === 0 || this.packageForm.data_center_id === '')) {
        return true
      } else {
        return false
      }
    }
  },
  methods: {
    async getProDetail () {
      try {
        const res = await getProductDetail(this.id)
        this.payType = res.data.data.product.pay_type
      } catch (error) {
      }
    },
    // 切换选项卡
    changeTab (e) {
      this.allStatus = false
      switch (e) {
        case 'package':
          this.getPackageList()
          break;
        case 'data_center':
          this.getData()
          this.getCountryList()
          break;
        case 'system':
          this.getSystems()
          break;
        default:
          break;
      }
    },
    /* 套餐 */
    async getPackageList () {
      try {
        this.dataLoading = true
        const params = {
          product_id: this.id,
          ...this.packageParams
        }
        const res = await getPackage(params)
        this.packageList = res.data.data.list.map(item => {
          item.status = false
          return item
        })
        this.package_total = res.data.data.count
        this.dataLoading = false
        const temp = await getDataCenter({
          product_id: this.id,
          page: 1,
          limit: 1000
        })
        this.selectDataCenter = temp.data.data.list
      } catch (error) {
        this.dataLoading = false
        this.$message.error(error.data.msg)
      }
    },
    // 修改套餐排序
    async savePackageOrder (row) {
      try {
        const { id, order } = row
        const res = await updatePackageOrders({
          id,
          order
        })
        this.$message.success(res.data.msg)
        this.getPackageList()
        this.allStatus = false
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 切换分页
    changePage (e) {
      this.packageParams.page = e.current
      this.packageParams.limit = e.pageSize
      this.getPackageList()
    },
    // 删除套餐
    async deletePackage () {
      try {
        const res = await deletePackage(this.delId)
        this.$message.success(res.data.msg)
        this.delVisible = false
        this.getPackageList()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 新增套餐
    addPackage () {
      this.packageModel = true
      this.optType = 'add'
      this.dataTitle = lang.new_create + lang.package
      this.packageForm.name = ''
      this.packageForm.data_center_id = []
      this.packageForm.description = ''
      this.packageForm.dcim_server_group_id = ''
      this.packageForm.system_disk_store = ''
      this.packageForm.free_data_disk_size = ''
      this.packageForm.data_disk_store = ''
      this.packageForm.in_bw = ''
      this.packageForm.out_bw = ''
      this.packageForm.ip_num = ''
      this.packageForm.ip_group = ''
      this.packageForm.custom_param = ''
      this.packageForm.traffic_enable = 1
      this.packageForm.flow = ''
      this.packageForm.traffic_bill_type = 'month'
      this.packageForm.onetime_fee = ''
      this.packageForm.month_fee = ''
      this.packageForm.quarter_fee = ''
      this.packageForm.half_year_fee = ''
      this.packageForm.year_fee = ''
      this.packageForm.two_year = ''
      this.packageForm.three_year = ''
    },
    editPackage (row) {
      this.packageModel = true
      this.optType = 'update'
      this.dataTitle = lang.update + lang.package
      this.packageForm = JSON.parse(JSON.stringify(row))
    },
    closePackage () {
      this.submitLoading = false
    },
    changeWay (e) {
      this.curCarging = e
    },
    // 保存套餐配置
    async submitPackage ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          if (this.payType === 'recurring_prepayment' || this.payType === 'recurring_postpaids') {
            const { month_fee, quarter_fee, half_year_fee, year_fee, two_year, three_year } = this.packageForm
            if (!month_fee && !quarter_fee && !half_year_fee && !year_fee && !two_year && !three_year) {
              return this.$message.error(lang.price_tip_force)
            }
          }
          this.submitLoading = true
          const params = JSON.parse(JSON.stringify(this.packageForm))
          params.product_id = this.id
          if (this.optType === 'add') {
            delete params.id
          }
          const res = await createOrUpdatePackage(this.optType, params)
          this.$message.success(res.data.msg)
          this.submitLoading = false
          this.packageModel = false
          this.getPackageList()
        } catch (error) {
          this.submitLoading = false
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },
    // 修改月套餐
    changeMonth (val, curType) {
      if (typeof val === 'string' && isNaN(val * 1) || val * 1 < 0) {
        return false
      }
      this.packageForm[curType] = (this.packageForm[curType] * 1).toFixed(2)
      if (curType) {
        return false
      }
      const monthFree = this.packageForm.month_fee
      this.packageForm.quarter_fee = (monthFree * 3).toFixed(2)
      this.packageForm.half_year_fee = (monthFree * 6).toFixed(2)
      this.packageForm.year_fee = (monthFree * 12).toFixed(2)
      this.packageForm.two_year = (monthFree * 24).toFixed(2)
      this.packageForm.three_year = (monthFree * 36).toFixed(2)
    },
    /* 套餐 end */

    /* 数据中心 */
    async getData () {
      try {
        this.dataLoading = true
        const params = {
          product_id: this.id,
          ...this.dataParams
        }
        const res = await getDataCenter(params)
        const temp = res.data.data
        this.dataList = temp.list.map(item => {
          item.status = false
          return item
        })
        this.data_total = temp.count
        this.dataLoading = false
      } catch (error) {
        this.dataLoading = false
        this.$message.error(error.data.msg)
      }
    },
    // 国家列表
    async getCountryList () {
      try {
        const res = await getCountry()
        this.countryList = res.data.data.list
      } catch (error) {

      }
    },
    /* 排序 */
    eidtDataOrder (row) {
      // if (this.allStatus) {
      //   this.$message.warning(lang.order_type_verify3)
      //   return false
      // }
      // this.allStatus = true
      row.status = !row.status
    },
    async saveDataOrder (row) {
      try {
        const { id, order } = row
        const res = await updateDataCenterOrders({
          id,
          order
        })
        this.$message.success(res.data.msg)
        this.getData()
        this.allStatus = false
      } catch (error) {
        console.log(error)
        this.$message.error(error.data.msg)
      }
    },
    addData () {
      this.optType = 'add'
      this.dataModel = true
      this.dataForm.country_id = ''
      this.dataForm.city = ''
      this.dataForm.cloud_config = 'node'
      this.dataForm.cloud_config_id = ''
      this.dataTitle = lang.new_create + lang.data_center
    },
    changeDataPage (e) {
      this.dataParams.page = e.current
      this.dataParams.limit = e.pageSize
      this.getData()
    },
    closeData () {
      this.dataModel = false
    },
    changeType () {
      this.$refs.dataForm.clearValidate(['cloud_config_id'])
    },
    editData (row) {
      if (this.allStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      this.dataModel = true
      this.optType = 'update'
      this.dataTitle = lang.edit + lang.data_center
      this.dataForm = JSON.parse(JSON.stringify(row))
    },
    async submitData ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          this.submitLoading = true
          const params = JSON.parse(JSON.stringify(this.dataForm))
          params.product_id = this.id
          if (this.optType === 'add') {
            delete params.order
          }
          const res = await createOrUpdateDataCenter(this.optType, params)
          this.$message.success(res.data.msg)
          this.submitLoading = false
          this.dataModel = false
          this.getData()
        } catch (error) {
          this.submitLoading = false
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },
    async deleteData () {
      try {
        const res = await deleteDataCenter(this.delId)
        this.$message.success(res.data.msg)
        this.delVisible = false
        this.getData()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    /* 数据中心 end */

    /* 操作系统 */
    async getSystems () {
      try {
        this.dataLoading = true
        const res = await getImage({
          product_id: this.id,
          image_group_id: this.image_group_id
        })
        this.systemList = res.data.data.list.map(item => {
          item.status = false
          return item
        })
        this.dataLoading = false
        this.systemName = res.data.data.image_group
      } catch (error) {
        this.dataLoading = false
        this.$message.error(error.data.msg)
      }
    },
    async refeshImage () {
      try {
        this.dataLoading = true
        const res = await getImageStatus({ product_id: this.id })
        if (res.data.status === 200) {
          this.getSystems()
        }
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    changeSwitch (row) {
      this.submitPrice(row)
    },
    focusEidt (row) {
      if (this.imageStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      row.status = true
      this.imageStatus = true
    },
    // 保存修改价格
    savePrice (row) {
      row.status = false
      this.imageStatus = false
      const { id, charge, price, enable } = row
      this.submitPrice({
        id, charge, price, enable
      })
    },
    async submitPrice (row) {
      try {
        const { id, charge, price, enable } = row
        const reg = /^\d+(\.\d{0,2})?$/
        if (!reg.test(price)) {
          return this.$message.warning(lang.verify5)
        }
        const params = [{
          id, charge, price, enable
        }]
        const res = await updateImage(params)
        this.$message.success(res.data.msg)
        this.getSystems()
        this.imageStatus = false
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },

    /* 其他配置 */
    async ohterConfig () {
      try {
        const res = await getOtherConfig({
          product_id: this.id
        })
        this.otherForm = res.data.data
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    async submitConfig ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          this.submitLoading = true
          const params = JSON.parse(JSON.stringify(this.otherForm))
          params.product_id = this.id
          const res = await saveOtherConfig(params)
          this.$message.success(res.data.msg)
          this.submitLoading = false
          this.dataModel = false
          this.ohterConfig()
        } catch (error) {
          this.submitLoading = false
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },
    // 获取备份
    async getBackup (type) {
      try {
        const params = {
          type,
          product_id: this.id
        }
        if (type === 'backup') {
          this.backLoading = true
        } else {
          this.snapLoading = true
        }
        const res = await getBackupConfig(params)
        if (type === 'backup') {
          this.backList = res.data.data.list.map(item => {
            item.status = false
            item.price = item.price * 1
            return item
          })
        } else {
          this.snapList = res.data.data.list.map(item => {
            item.status = false
            item.price = item.price * 1
            return item
          })
        }
        if (type === 'backup') {
          this.backLoading = false
        } else {
          this.snapLoading = false
        }
      } catch (error) {

      }
    },
    addGroup (type) {
      const temp = {
        num: 1,
        type: type,
        price: 0.00,
        status: true // 编辑状态
      }
      if (this.backAllStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      this.backAllStatus = true
      if (type === 'backup') {
        this.backList.push(temp)
      } else {
        this.snapList.push(temp)
      }
    },
    // 交互校验最小/大容量
    checkNum (val) {
      if (val * 1 > this.otherForm.disk_max_size * 1) {
        return { result: false, message: lang.capacity_tip, type: 'warning' }
      }
      return { result: true }
    },
    checkNum1 (val) {
      if (val * 1 < this.otherForm.disk_min_size * 1) {
        return { result: false, message: lang.capacity_tip, type: 'warning' }
      }
      return { result: true }
    },
    changeNum () {
      console.log(2323223)
      this.$refs.otherConfig.validate({
        fields: ['disk_min_size', 'disk_max_size']
      });
    },
    // 创建/编辑 备份/快照
    async saveBack (item, type) {
      if (item.price === undefined) {
        this.$message.warning({ content: lang.input + lang.price });
        return;
      }
      const { id, price, num } = item
      const params = {
        product_id: this.id,
        id, price, type, num
      }
      let opt = ''
      if (id) { // 是编辑状态
        opt = 'update'
        delete params.product_id
      } else {
        opt = 'add'
        delete params.id
      }
      try {
        const res = await createOrUpdateBackup(opt, params)
        this.$message.success(res.data.msg)
        this.backAllStatus = false
        this.getBackup(type)
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    openEdit (type, index) {
      if (this.backAllStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      this.backAllStatus = true
      if (type === 'backup') {
        this.backList[index].status = true
      } else {
        this.snapList[index].status = true
      }
    },
    closeEdit (row, index, type) {
      if (row.id) { // 取消已有数据的编辑
        if (type === 'backup') {
          this.backList[index].status = false
        } else {
          this.snapList[index].status = false
        }
      } else { // 新增未加入数据库的
        if (type === 'backup') {
          this.backList.splice(index, 1)
        } else {
          this.snapList.splice(index, 1)
        }
      }
      this.backAllStatus = false
    },

    // 删除 备份/快照
    async deleteBackup (type) {
      try {
        const res = await deleteBackup(this.delId)
        this.$message.success(res.data.msg)
        this.getBackup(type)
        this.delVisible = false
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    /* 通用删除按钮 */
    comDel (type, row) {
      if (this.allStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      this.delId = row.id
      this.delType = type
      this.delVisible = true
    },
    // 通用删除
    sureDelete () {
      switch (this.delType) {
        case 'package':
          return this.deletePackage()
        case 'data':
          return this.deleteData()
        case 'backup':
          return this.deleteBackup('backup')
        case 'snap':
          return this.deleteBackup('snap')
        default:
          return null
      }
    },
  },
}).$mount(template)
typeof old_onload == 'function' && old_onload()