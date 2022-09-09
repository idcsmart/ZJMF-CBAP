const template = document.getElementsByClassName('cloud-config')[0]
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
      dataList: [], // 数据中心
      bwTypeList: [], // 带宽类型
      bwList: [], // 带宽列表
      otherConfig: {// 其他配置
        backup_enable: 0,
        backup_param: "",
        backup_price: "0",
        hostname_rule: 1,
        panel_enable: 0,
        panel_param: "",
        panel_price: "",
        snap_enable: 0,
        snap_free_num: 0,
        snap_price: ""
      },
      // 计算型号
      calModel: false,
      calForm: {
        name: '',
        module_idcsmart_cloud_cal_group_id: '',
        cpu: '',
        memory: '',
        disk_size: '',
        price: '',
        description: '',
        other_param: ''
      },
      parent_id: '',
      calList: [], // 存储计算型号数据
      calPage: [], // 存储计算型号分页
      // 数据中心
      dataTitle: '',
      dataModel: false,
      dataForm: {
        country: '',
        country_code: '',
        city: '',
        area: '',
        server: [{
          server_id: '',
          server_param: ''
        },]
      },
      // 带宽
      bwModel: false,
      selectBwList: [],
      bwForm: {
        module_idcsmart_cloud_bw_type_id: '',
        data_center_id: [],
        bw: '',
        flow: '',
        price: '',
        description: '',
        flow_type: 'in',
        in_bw_enable: false,
        in_bw: ''
      },
      // 套餐
      allCalList: [],
      packageList: [],
      packageModel: false,
      packageForm: {
        name: '',
        cal_id: [],
        data_center_id: [],
        bw_id: []
      },
      // 接口
      interfaceList: [],
      packageRules: {

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
          colKey: 'cal_ratio',
          title: lang.model_scale,
          width: 400
        },
        {
          colKey: 'bw_ratio',
          title: lang.bw_scale,
          width: 400,
          ellipsis: true
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 80
        },
      ],
      calRules: {
        name: [
          { required: true, message: lang.input + lang.nickname, type: 'error' },
          {
            validator: val => val.length <= 100,
            message: lang.verify3 + 100, type: 'waring'
          }
        ],
        module_idcsmart_cloud_cal_group_id: [
          { required: true, message: lang.select + lang.group, type: 'error' },
        ],
        cpu: [
          { required: true, message: lang.input + 'CPU', type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + '1-240' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 1 && val <= 240, message: lang.input + '1-240' + lang.verify2, type: 'warning'
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
        disk_size: [
          { required: true, message: lang.input + lang.disk, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + '1-1048576' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 1 && val <= 1048576, message: lang.input + '1-1048576' + lang.verify2, type: 'warning'
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
        ]
      },
      bwRules: {
        module_idcsmart_cloud_bw_type_id: [
          { required: true, message: lang.select + lang.bw_type, type: 'error' },
        ],
        data_center_id: [
          { required: true, message: lang.select + lang.area, type: 'error' },
        ],
        bw: [
          { required: true, message: lang.input + lang.bw, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.verify7, type: 'warning'
          }
        ],
        in_bw: [
          { required: true, message: lang.input + lang.in_bw, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.verify7, type: 'warning'
          }
        ],
        flow: [
          { required: true, message: lang.input + lang.cloud_flow, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.verify7, type: 'warning'
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
        description: [
          {
            validator: val => val.length <= 1000,
            message: lang.verify3 + 1000, type: 'waring'
          }
        ]
      },
      dataRules: {
        country: [
          { required: true, message: lang.input + lang.country, type: 'error' },
          {
            validator: val => val.length <= 100,
            message: lang.verify3 + 100, type: 'waring'
          }
        ],
        country_code: [
          { required: true, message: lang.input + lang.country_code, type: 'error' },
        ],
        city: [
          { required: true, message: lang.input + lang.region, type: 'error' },
          {
            validator: val => val.length <= 100,
            message: lang.verify3 + 100, type: 'waring'
          }
        ],
        area: [
          { required: true, message: lang.input + lang.area, type: 'error' },
          {
            pattern: /^[1-9]$/, message: lang.input + '1-9' + lang.verify2, type: 'warning'
          }
        ],
        server_id: [
          { required: true, message: lang.input + lang.interface, type: 'error' },
        ],
        server_param: [
          { required: true, message: lang.input + lang.server_param, type: 'error' },
        ],
      },
      packageRules: {
        name: [
          { required: true, message: lang.input + lang.package + lang.nickname, type: 'error' },
        ],
        cal_id: [
          { required: true, message: lang.select + lang.calc, type: 'error' },
        ],
        data_center_id: [
          { required: true, message: lang.select + lang.area, type: 'error' },
        ],
        bw_id: [
          { required: true, message: lang.select + lang.bw, type: 'error' },
        ]
      },
      calColumns: [ // 数据中心表格
        {
          colKey: 'order',
          title: lang.sort + 'ID',
          width: 100
        },
        {
          colKey: 'name',
          title: lang.nickname,
          ellipsis: true,
          width: 120
        },
        {
          colKey: 'cpu',
          title: 'CPU',
          ellipsis: true,
          width: 100
        },
        {
          colKey: 'memory',
          title: lang.memory,
          ellipsis: true,
          width: 100
        },
        {
          colKey: 'disk_size',
          title: lang.disk,
          width: 100
        },
        {
          colKey: 'price',
          title: lang.price,
          width: 130
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 109
        },
      ],
      dataColumns: [ // 数据中心表格
        {
          colKey: 'order',
          title: lang.sort + 'ID',
          width: 112
        },
        {
          colKey: 'country',
          title: lang.country_name,
          width: 333,
          ellipsis: true
        },
        {
          colKey: 'country_code',
          title: lang.country_code,
          width: 180,
          ellipsis: true
        },
        {
          colKey: 'city',
          title: lang.city,
          width: 180,
          ellipsis: true
        },
        {
          colKey: 'area',
          title: lang.area,
          width: 260
        },
        {
          colKey: 'server_name',
          title: lang.interface,
          width: 290
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 100
        },
      ],
      bwColumns: [ // 带宽表格
        {
          colKey: 'bw_type_name',
          title: lang.bw_type,
          width: 175
        },
        {
          colKey: 'bw',
          title: lang.bw,
          width: 120,
          ellipsis: true
        },
        {
          colKey: 'flow',
          title: lang.cloud_flow,
          width: 144,
          ellipsis: true
        },
        {
          colKey: 'area',
          title: lang.area,
          width: 773
        },
        {
          colKey: 'price',
          title: lang.total_price,
          width: 150
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 100
        },
      ],
      packageColumns: [ // 套餐表格
        {
          colKey: 'name',
          title: lang.nickname,
          width: 175
        },
        {
          colKey: 'cal_name',
          title: lang.calc,
          width: 120,
          ellipsis: true
        },
        {
          colKey: 'area',
          title: lang.area,
          width: 773
        },
        {
          colKey: 'bw',
          title: lang.bw,
          width: 144,
          ellipsis: true
        },
        {
          colKey: 'price',
          title: lang.total_price,
          width: 150
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 100
        },
      ],
      currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix || '¥',
      /* 操作系统 */
      imageLoading: false,
      imageGroupList: [],
      imageStatus: false, // 操作系统总编辑状态
      existLoading: true,
      // 应用镜像
      applyList: [],
      applyArea: [], // 应用镜像区域列表
      applyLoading: false,
      applyGroupId: '',
      applyColumns: [
        {
          colKey: 'name',
          title: lang.system_name,
          width: 200,
          fixed: 'left'
        },
        {
          colKey: 'icon',
          title: lang.icon,
          width: 125,
          ellipsis: true
        },
        {
          colKey: 'area',
          title: 'title-slot-area'
        },
        {
          colKey: 'op',
          title: lang.pay_system,
          width: 180
        },
      ],
      // 官方镜像
      officialList: []
    }
  },
  computed: {
    filterInterface () {
      let arr = []
      arr = this.dataForm.server.reduce((all, cur) => {
        all.push(cur.server_id)
        return all
      }, [])
      let temp = []
      temp = this.interfaceList.map(item => {
        item.disabled = false
        if (arr.includes(item.id)) {
          item.disabled = true
        }
        return item
      })
      return temp
    },
    filterCalList () {
      return this.calList.filter(item => item.data?.length > 0)
    },
    calcName () {
      return (id) => {
        const arr = this.imageGroupList.filter(item => item.id === id)
        return arr[0].name
      }
    }
  },
  created () {
    this.id = location.href.split('?')[1].split('=')[1]
    // 获取计算分组
    this.getCalGroupList()
    // 数据中心
    this.getDataCenterList()
    // 获取接口
    this.getInterfaceList()
    // 带宽类型
    this.getBwTypeList()
    // 带宽列表
    this.getBwList()
    // 获取套餐
    this.getPackageList()
    // 其他配置
    this.getSettingObj()
    // 获取计算型号
    this.getCalList()
    // 获取周期
    this.getDurationList()
    /* 操作系统相关 */
    // 操作系统组列表
    this.getImageGroupList()
    // 应用镜像
    this.getApplyImageList(true)
  },
  methods: {
    /* 操作系统 */
    async getApplyImageList (bol = false, id) { // bol是否刷新状态，镜像分组id
      try {
        const params = {
          product_id: this.id,
          image_type: id ? 'system' : 'app',
          module_idcsmart_cloud_image_group_id: id || ''
        }
        if (id) {
          this.officialList.forEach((item, index) => {
            if (item.id === id) {
              item.loading = true
            }
          })
        } else {
          this.applyLoading = true
        }
        const res = await getImage(params)
        const temp = res.data.data
        // 通用动态表头
        this.applyArea = temp.data_center

        // 过滤多余的数据中心
        let dataIdArr = []
        dataIdArr = temp.data_center.reduce((all, cur) => {
          all.push(cur.id)
          return all
        }, [])
        const resultList = temp.list.map(item => {
          item.status = false
          item.data_center = item.data_center.filter(el => {
            return dataIdArr.includes(el.module_idcsmart_cloud_data_center_id)
          })
          return item
        })
        if (id) { // 官方镜像
          this.officialList.forEach((item, index) => {
            if (item.id === id) {
              item.loading = false
              this.$set(this.officialList[index], 'data', resultList)
            }
          })
        } else {
          // 应用镜像
          this.applyList = resultList
          this.applyGroupId = temp.list[0]?.module_idcsmart_cloud_image_group_id
          this.applyLoading = false
        }
        // 刷新存在状态
        if (bol) {
          this.getAllStatus()
        }
      } catch (error) {
        this.$message.error(error.data.msg)
        this.applyLoading = false
      }
    },
    // 修改是否启用
    async changeEnable (id, item, groupId) {
      try {
        const params = {
          id,
          enable: item.enable ? 0 : 1
        }
        if (item.module_idcsmart_cloud_data_center_id !== undefined) { // 修改单个区域
          params.module_idcsmart_cloud_data_center_id = item.module_idcsmart_cloud_data_center_id
        }
        const res = await changeImageEnable(params)
        this.$message.success(res.data.msg)
        if (item.module_idcsmart_cloud_image_group_id !== 0 || groupId !== 0) { // 刷新官方镜像
          this.getApplyImageList(false, item.module_idcsmart_cloud_image_group_id || groupId)
        } else {
          this.getApplyImageList()
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
      console.log(row)
      this.imageStatus = true
    },
    // 保存修改价格
    savePrice (row) {
      row.status = false
      this.imageStatus = false
      const { id, charge, price } = row
      this.submitPrice({
        id, charge, price
      })
    },
    async submitPrice (row) {
      try {
        const { id, charge, price } = row
        const reg = /^\d+(\.\d{0,2})?$/
        if (!reg.test(row.price)) {
          return this.$message.warning(lang.verify5)
        }
        const params = {
          id, charge, price
        }
        const res = await updateImage(params)
        this.$message.success(res.data.msg)
        this.getApplyImageList(false, id)
        this.imageStatus = false
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 获取镜像存在状态
    async getAllStatus () {
      try {
        this.existLoading = true
        await getImageStatus(this.id)
        this.existLoading = false
        this.getApplyImageList(false)
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 添加镜像分组
    addImageGroup () {
      const temp = {
        name: '',
        order: 0,
        enable: 0,
        description: '',
        status: true
      }
      if (this.imageStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      this.imageStatus = true
      this.imageGroupList.push(temp)
    },
    // 保存镜像分组
    async saveImageGroup (item, index) {
      if (!item.name.trim()) {
        this.$message.warning({ content: lang.input + lang.nickname });
        return;
      }
      const params = {
        product_id: this.id,
        id: item.id,
        name: item.name,
        enable: item.enable,
        order: item.order,
        description: item.description
      }
      let type = 'add'
      if (item.id) { // 是编辑状态
        delete params.product_id
        type = 'update'
      } else {
        type = 'add'
        delete params.id
      }
      try {
        const res = await createOrUpdateImageGroup(type, params)
        this.$message.success(res.data.msg)
        this.imageStatus = false
        this.imageGroupList[index].status = false
        this.getImageGroupList()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 关闭编辑状态
    closeImageEdit (index) {
      this.imageStatus = false
      if (!this.imageGroupList[index].id) {
        this.imageGroupList.splice(index, 1)
        return false
      }
      this.imageGroupList[index].status = false
    },
    openImageEdit (index) {
      if (this.imageStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      this.imageStatus = true
      this.imageGroupList[index].status = true
    },
    // 删除镜像分组
    async deleteImageGroup (item) {
      this.imageStatus = false
      try {
        const res = await deleteImageGroupId(item.id)
        this.$message.success(res.data.msg)
        this.getImageGroupList()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 获取镜像分组列表
    async getImageGroupList () {
      try {
        const res = await getImageGroup(this.id)
        const temp = res.data.data.list.map(item => {
          item.status = false // 是否编辑状态
          return item
        })
        this.imageGroupList = temp
        const arr = res.data.data.list.reduce((all, cur) => {
          all.push(cur.id)
          return all
        }, [])
        this.officialList = []
        arr.forEach(id => {
          this.officialList.push({
            id,
            loading: false
          })
          this.getApplyImageList(false, id)
        })
      } catch (error) {
        this.$message.error(error.data.msg)
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
        const { id, cal_ratio, bw_ratio } = row
        const data = [
          {
            id, cal_ratio, bw_ratio
          }
        ]
        const res = await updateDuration({ data })
        this.$message.success(res.data.msg)
        this.allStatus = false
        row.status = false
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 添加型号分组
    addGroup () {
      const temp = {
        name: '',
        order: 0,
        description: '',
        status: true // 编辑状态
      }
      if (this.allStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      this.allStatus = true
      this.calGroupList.push(temp)
    },
    // 创建/编辑 计算分组
    async saveGroup (item, index) {
      if (!item.name.trim()) {
        this.$message.warning({ content: lang.input + lang.nickname });
        return;
      }
      const params = {
        product_id: this.id,
        id: item.id,
        name: item.name,
        order: item.order,
        description: item.description
      }
      let type = 'add'
      if (item.id) { // 是编辑状态
        delete params.product_id
        type = 'update'
      } else {
        type = 'add'
        delete params.id
      }
      try {
        const res = await createOrUpdateCalGroup(type, params)
        this.$message.success(res.data.msg)
        this.allStatus = false
        this.calGroupList[index].status = false
        this.getCalGroupList()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 关闭编辑状态
    closeEdit (index) {
      this.allStatus = false
      if (!this.calGroupList[index].id) {
        this.calGroupList.splice(index, 1)
        return false
      }
      this.calGroupList[index].status = false
      this.getCalGroupList(true)
    },
    openEdit (index) {
      if (this.allStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      this.allStatus = true
      this.calGroupList[index].status = true
    },
    // 删除计算分组
    async deleteGroup () {
      this.allStatus = false
      try {
        const res = await deleteCalGroup(this.delId)
        this.$message.success(res.data.msg)
        this.getCalGroupList()
        this.delVisible = false
      } catch (error) {
        this.$message.error(error.data.msg)
        this.delVisible = false
      }
    },
    // 获取计算型号分组
    async getCalGroupList (bol = false) {
      try {
        const params = {
          id: this.id,
          sort: 'asc'
        }
        const res = await getCalGroup(params)
        const temp = res.data.data.list.map(item => {
          item.status = false // 是否编辑状态
          return item
        })
        this.calGroupList = temp
        if (bol) {
          return false
        }
        // 循环分组ID获取具体计算
        const arr = temp.reduce((all, cur) => {
          all.push(cur.id)
          return all
        }, [])
        // this.calList = []
        arr.forEach(id => {
          const temp = this.calList.reduce((all, cur) => {
            all.push(cur.id)
            return all
          }, [])
          if (!temp?.includes(id)) {
            this.calList.push({
              id,
              params: {
                page: 1,
                limit: 3
              },
              loading: false,
              total: 0
            })
          }
          this.getCalList(id)
        })
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    /* 计算型号 */
    closeCal () {
      this.calModel = false
    },
    addCal () {
      if (this.allStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      this.optType = 'add'
      this.calModel = true
      this.dataTitle = lang.create + lang.computer_model
      this.calForm.name = ''
      this.calForm.module_idcsmart_cloud_cal_group_id = ''
      this.calForm.cpu = ''
      this.calForm.memory = ''
      this.calForm.disk_size = ''
      this.calForm.price = ''
      this.calForm.description = ''
      this.calForm.other_param = ''
    },
    editCal (row) {
      this.calModel = true
      this.optType = 'update'
      this.dataTitle = lang.update + lang.computer_model
      this.calForm.id = row.id
      this.calForm.name = row.name
      this.calForm.module_idcsmart_cloud_cal_group_id = row.module_idcsmart_cloud_cal_group_id
      this.calForm.cpu = row.cpu
      this.calForm.memory = row.memory
      this.calForm.disk_size = row.disk_size
      this.calForm.price = row.price
      this.calForm.description = row.description
      this.calForm.other_param = row.other_param
      this.parent_id = row.module_idcsmart_cloud_cal_group_id

    },
    // 新增/编辑计算型号
    async submitCal ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.calForm))
          params.product_id = this.id
          if (this.optType === 'update') {
            delete params.product_id
          }
          if (this.optType === 'add') {
            delete params.id
          }
          const res = await createOrUpdateCal(this.optType, params)
          this.$message.success(res.data.msg)
          this.calModel = false
          // if (this.optType === 'add') {
          //   this.getCalGroupList()
          // } else if(this.optType === 'update') {
          //   this.getCalList(this.parent_id)
          // }
          // 涉及到切换类型，所有都要刷新
          this.getCalGroupList()
          this.closeCal()
          if (this.optType === 'update') { // 修改价格，刷新套餐
            this.getPackageList()
          }
        } catch (error) {
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },
    // 根据组id获取计算型号
    async getCalList (groupId) {
      try {
        let params = {}
        if (groupId) {
          this.calList.forEach((item, index) => {
            if (item.id === groupId) {
              item.loading = true
              params = { ...this.calList[index]?.params }
            }
          })
          params.module_idcsmart_cloud_cal_group_id = groupId
        } else {
          params = { ...this.calParams }
        }
        params.product_id = this.id
        const res = await getCal(params)

        const temp = res.data.data.list.map(item => {
          item.status = false
          return item
        })
        if (!groupId) { // 首次拉取给套餐做下拉数据
          this.allCalList = temp
        } else { // 根据分组id拉取对应的数据
          this.calList.forEach((item, index) => {
            if (item.id === groupId) {
              item.loading = false
              this.$set(this.calList[index], 'data', temp)
              this.$set(this.calList[index], 'total', res.data.data.count)
            }
          })
        }
        this.calLoading = false
      } catch (error) {
        console.log(error)
        this.$message.error(error.data.msg)
      }
    },
    // 计算型号排序
    eidtCalOrder (row) {
      if (this.allStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      this.allStatus = true
      row.status = !row.status
    },
    async saveCalOrder (row) {
      try {
        const { id, order } = row
        const res = await updateCalOrder({
          id,
          order
        })
        this.$message.success(res.data.msg)
        this.allStatus = false
        this.getCalList(row.module_idcsmart_cloud_cal_group_id)
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    /* 数据中心排序 */
    eidtDataOrder (row) {
      if (this.allStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      this.allStatus = true
      row.status = !row.status
    },
    async saveDataOrder (row) {
      try {
        const { id, order } = row
        const res = await updateDataCenterOrder({
          id,
          order
        })
        this.$message.success(res.data.msg)
        this.getDataCenterList()
        this.allStatus = false
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 型号翻页
    prev (id) {
      const temp = this.calList.filter(item => item.id === id)
      if (temp[0].params.page === 1) {
        return
      }
      temp[0].params.page -= 1
      this.getCalList(id)
    },
    next (id) {
      const temp = this.calList.filter(item => item.id === id)
      if (temp[0].params.page === Math.ceil(temp[0].total / 3)) {
        return
      }
      temp[0].params.page += 1
      this.getCalList(id)
    },
    // 删除计算型号
    async deleteCal () {
      try {
        const res = await deleteCalId(this.delId)
        this.$message.success(res.data.msg)
        this.getCalList(this.parent_id)
        this.delVisible = false
      } catch (error) {
        this.$message.error(error.data.msg)
        this.delVisible = false
      }
    },
    /* 带宽类型 */
    // 获取带宽类型
    async getBwTypeList () {
      try {
        const params = {
          id: this.id,
          sort: 'asc'
        }
        const res = await getBwType(params)
        const temp = res.data.data.list.map(item => {
          item.status = false // 是否编辑状态
          return item
        })
        this.bwTypeList = temp
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 添加新的带宽类型
    addBwType () {
      const temp = {
        name: '',
        order: 0,
        description: '',
        status: true
      }
      if (this.allStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      this.allStatus = true
      this.bwTypeList.push(temp)
    },
    closeEditBwType (index) {
      this.allStatus = false
      if (!this.bwTypeList[index].id) {
        this.bwTypeList.splice(index, 1)
        return false
      }
      this.bwTypeList[index].status = false
      this.getBwTypeList()
    },
    openEditBwType (index) {
      if (this.allStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      this.allStatus = true
      this.bwTypeList[index].status = true
    },
    // 创建/编辑 带宽类型
    async saveBwType (item, index) {
      if (!item.name.trim()) {
        this.$message.warning({ content: lang.input + lang.nickname });
        return;
      }
      const params = {
        product_id: this.id,
        id: item.id,
        name: item.name,
        order: item.order,
        description: item.description
      }
      let type = 'add'
      if (item.id) { // 是编辑状态
        delete params.product_id
        type = 'update'
      } else {
        type = 'add'
        delete params.id
      }
      try {
        const res = await createOrUpdateBwType(type, params)
        this.$message.success(res.data.msg)
        this.allStatus = false
        this.bwTypeList[index].status = false
        this.getBwTypeList()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 删除带宽类型
    async delBwType () {
      this.allStatus = false
      try {
        const res = await deleteBwType(this.delId)
        this.$message.success(res.data.msg)
        this.delVisible = false
        this.getBwTypeList()
      } catch (error) {
        this.$message.error(error.data.msg)
        this.delVisible = false
      }
    },
    /* 带宽 */
    addBw () {
      if (this.allStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      this.optType = 'add'
      this.bwModel = true
      this.dataTitle = lang.create + lang.bw
      this.$refs.bwForm && this.$refs.bwForm.reset()
      this.bwForm.module_idcsmart_cloud_bw_type_id = ''
      this.bwForm.data_center_id = []
      this.bwForm.bw = ''
      this.bwForm.flow = ''
      this.bwForm.price = ''
      this.bwForm.description = ''
      this.bwForm.flow_type = 'in'
      this.bwForm.in_bw_enable = false
      this.bwForm.in_bw = ''
    },
    editBw (row) {
      this.optType = 'update'
      const temp = JSON.parse(JSON.stringify(row))
      temp.in_bw_enable = temp.in_bw_enable === 1 ? true : false
      temp.data_center_id = temp.data_center.reduce((all, cur) => {
        all.push(cur.id)
        return all
      }, [])
      delete temp.data_center
      this.bwForm = temp
      this.dataTitle = lang.update + lang.bw
      this.bwModel = true
    },
    // 添加/编辑带宽
    async submitBw ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.bwForm))
          params.product_id = this.id
          if (this.optType === 'update') {
            delete params.product_id
          }
          if (this.optType === 'add') {
            delete params.id
          }
          params.in_bw_enable = params.in_bw_enable ? 1 : 0
          const res = await createOrUpdateBw(this.optType, params)
          this.$message.success(res.data.msg)
          this.bwModel = false
          this.getBwList()
          this.closeBw()
          if (this.optType === 'update') {
            this.getPackageList()
          }
        } catch (error) {
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },
    closeBw () {
      this.$refs.bwForm.clearValidate()
      this.bwModel = false
    },
    // 删除带宽
    async deleteBw () {
      try {
        const res = await deleteBwId(this.delId)
        this.$message.success(res.data.msg)
        this.getBwList()
        this.delVisible = false
      } catch (error) {
        this.$message.error(error.data.msg)
        this.delVisible = false
      }
    },
    // 获取套餐下拉的带宽
    async getSelectBwList (data_center_id = []) {
      try {
        const params = {
          product_id: this.id,
          page: 1,
          limit: 1000,
          data_center_id
        }
        const res = await getBw(params)
        this.selectBwList = res.data.data.list
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 获取带宽列表
    async getBwList () {
      try {
        const params = {
          product_id: this.id,
          page: 1,
          limit: 1000
        }
        const res = await getBw(params)
        const temp = res.data.data.list.map(item => {
          item.status = false
          return item
        })
        this.bwList = temp
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    /* 获取数据中心 */
    async getDataCenterList () {
      try {
        const params = {
          product_id: this.id,
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
    // 获取接口
    async getInterfaceList () {
      try {
        const res = await getInterface({
          page: 1,
          limit: 1000
        })
        const temp = res.data.data.list.filter(item => item.module === "idcsmart_cloud")
        temp.map(item => {
          item.disabled = false
          return item
        })
        this.interfaceList = temp
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 添加数据中心
    addData () {
      if (this.allStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      this.optType = 'add'
      this.dataModel = true
      this.dataTitle = lang.create + lang.data_center
      this.dataForm.country = ''
      this.dataForm.country_code = ''
      this.dataForm.city = ''
      this.dataForm.area = ''
      this.dataForm.server = []
      this.dataForm.server.push({
        server_id: '',
        server_param: ''
      })
    },
    // 新增接口
    addInterface (index) {
      if (!this.dataForm.server[index].server_id) {
        this.$message.warning(lang.select + lang.interface)
        return
      }
      if (!this.dataForm.server[index].server_param) {
        this.$message.warning(lang.input + lang.server_param)
        return
      }
      this.dataForm.server.push({
        server_id: '',
        server_param: ''
      })
    },
    // 删除接口
    deleteInterface (index) {
      this.dataForm.server.splice(index, 1)
    },
    closeData () {
      this.$refs.dataForm.clearValidate()
      this.dataModel = false
    },
    // 新增/编辑 数据中心
    async submitData ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.dataForm))
          params.product_id = this.id
          if (this.optType === 'update') {
            delete params.product_id
          }
          if (this.optType === 'add') {
            delete params.id
          }
          const res = await createOrUpdateDataCenter(this.optType, params)
          this.$message.success(res.data.msg)
          this.dataModel = false
          this.getDataCenterList()
          this.closeData()
        } catch (error) {
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },
    // 删除 数据中心
    async deleteData () {
      try {
        const res = await deleteDataCenter(this.delId)
        this.$message.success(res.data.msg)
        this.delVisible = false
        this.getDataCenterList()
      } catch (error) {
        this.$message.error(error.data.msg)
        this.delVisible = false
      }
    },
    // 编辑数据中心
    editData (row) {
      this.optType = 'update'
      this.dataForm = { ...row }
      this.dataTitle = lang.update + lang.data_center
      this.dataModel = true
    },
    /* 套餐 */
    // 获取套餐列表
    async getPackageList () {
      try {
        this.packageLoading = true
        const res = await getPackage({
          page: 1,
          limit: 1000,
          product_id: this.id
        })
        this.packageList = res.data.data.list
        this.packageLoading = false
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 编辑套餐
    editPackage (row) {
      this.optType = 'update'
      console.log(row)
      this.packageForm.id = row.id
      this.packageForm.name = row.name
      this.packageForm.cal_id = row.module_idcsmart_cloud_cal_id
      const areaArr = row.data_center.reduce((all, cur) => {
        all.push(cur.id)
        return all
      }, [])
      this.packageForm.data_center_id = [...Array.from(areaArr)]
      this.packageForm.bw_id = row.module_idcsmart_cloud_bw_id
      this.dataTitle = lang.update + lang.package
      this.packageModel = true
      this.getSelectBwList()
    },
    addPackage () {
      if (this.allStatus) {
        this.$message.warning(lang.order_type_verify3)
        return false
      }
      this.optType = 'add'
      this.packageModel = true
      this.dataTitle = lang.create + lang.package
      this.packageForm.id = ''
      this.packageForm.name = ''
      this.packageForm.cal_id = []
      this.packageForm.data_center_id = []
      this.packageForm.bw_id = []
      this.getSelectBwList()
    },
    // 选择数据中心筛选带宽
    choosePackage (e) {
      this.getSelectBwList(e)
    },
    // 新增/编辑套餐
    async submitPackage ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.packageForm))
          params.product_id = this.id
          if (this.optType === 'update') {
            delete params.product_id
            params.module_idcsmart_cloud_cal_id = params.cal_id
            params.module_idcsmart_cloud_bw_id = params.bw_id
          }
          if (this.optType === 'add') {
            delete params.id
          }
          const res = await createOrUpdatePackage(this.optType, params)
          this.$message.success(res.data.msg)
          this.packageModel = false
          this.getPackageList()
          this.closePackage()
        } catch (error) {
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },
    closePackage () {
      this.packageModel = false
    },
    // 删除套餐
    async deletePackage () {
      try {
        const res = await deletePackage(this.delId)
        this.$message.success(res.data.msg)
        this.getPackageList()
        this.delVisible = false
      } catch (error) {
        this.$message.error(error.data.msg)
        this.delVisible = false
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
        case 'calGroup':
          return this.deleteGroup()
        case 'cal':
          return this.deleteCal()
        case 'data':
          return this.deleteData()
        case 'bwType':
          return this.delBwType()
        case 'bw':
          return this.deleteBw()
        case 'package':
          return this.deletePackage()
        default:
          return null
      }
    },
    // 获取其他配置
    async getSettingObj () {
      try {
        const res = await getSetting(this.id)
        const data = res.data.data
        this.otherConfig = { ...data }
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 保存其他配置
    async submitConfig () {
      try {
        const params = { ...this.otherConfig }
        params.product_id = this.id
        const res = await updateSetting(params)
        this.$message.success(res.data.msg)
        this.getSettingObj()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    }
  },
}).$mount(template)
