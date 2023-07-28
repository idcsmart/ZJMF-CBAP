const template = document.getElementsByClassName('common_config')[0]
Vue.prototype.lang = window.lang
new Vue({
  data () {
    return {
      host: location.origin,
      id: '',
      tabs: 'duration', // duration,calc,data_center,store,limit,system,recommend,other
      hover: true,
      tableLayout: false,
      delVisible: false,
      loading: false,
      currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix || '¥',
      currency_suffix: JSON.parse(localStorage.getItem('common_set')).currency_suffix || '',
      optType: 'add', // 新增/编辑
      comTitle: '',
      delTit: '',
      delType: '',
      delId: '',
      submitLoading: false,
      /* 周期 */
      cycleData: [],
      dataModel: false,
      cycleModel: false,
      cycleForm: {
        product_id: '',
        name: '',
        num: '',
        unit: 'month',
        price_factor: null
      },
      cycleTime: [
        {
          value: 'hour',
          label: lang.hour
        },
        {
          value: 'day',
          label: lang.day
        },
        {
          value: 'month',
          label: lang.natural_month
        },
      ],
      cycleColumns: [
        {
          colKey: 'name',
          title: lang.cycle_name,
          ellipsis: true
        },
        {
          colKey: 'unit',
          title: lang.cycle_time,
          ellipsis: true
        },
        {
          colKey: 'price_factor',
          title: lang.price_factor,
          ellipsis: true,
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 100
        },
      ],
      cycleRules: {
        name: [
          { required: true, message: lang.input + lang.cycle_name, type: 'error' },
          {
            validator: val => val?.length <= 10, message: lang.verify8 + '1-10', type: 'warning'
          }
        ],
        num: [
          { required: true, message: lang.input + lang.cycle_time, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + lang.verify16, type: 'warning'
          },
          {
            validator: val => val > 0 && val <= 999, message: lang.cycle_time + '1-999', type: 'warning'
          }
        ],
        // 系统相关
        image_group_id: [
          { required: true, message: lang.select + lang.system_classify, type: 'error' }
        ],
        rel_image_id: [
          { required: true, message: lang.input + lang.opt_system + 'ID', type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + lang.verify16, type: 'warning'
          },
        ],
        price: [
          { required: true, message: lang.input + lang.price, type: 'error' },
          {
            pattern: /^\d+(\.\d{0,2})?$/, message: lang.verify12, type: 'warning'
          },
          {
            validator: val => val >= 0, message: lang.verify12, type: 'warning'
          }
        ],
        icon: [
          { required: true, message: lang.select + lang.mf_icon, type: 'error', trigger: 'change' }
        ],
      },
      /* 操作系统 */
      systemGroup: [],
      systemList: [],
      systemParams: {
        product_id: '',
        page: 1,
        limit: 1000,
        image_group_id: '',
        keywords: ''
      },
      systemModel: false,
      createSystem: { // 添加操作系统表单
        image_group_id: '',
        name: '',
        charge: 0,
        price: '',
        enable: 0,
        rel_image_id: ''
      },
      systemColumns: [ // 套餐表格
        {
          colKey: 'id',
          title: lang.order_index,
          width: 100
        },
        {
          colKey: 'image_group_name',
          title: lang.system_classify,
          width: 200,
          ellipsis: true
        },
        {
          colKey: 'name',
          title: lang.system_name,
          ellipsis: true,
        },
        {
          colKey: 'charge',
          title: lang.mf_charge,
          width: 200
        },
        {
          colKey: 'price',
          title: lang.price,
        },
        {
          colKey: 'enable',
          title: lang.mf_enable,
          width: 200
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 100
        },
      ],
      groupColumns: [ // 套餐表格
        {
          // 列拖拽排序必要参数
          colKey: 'drag',
          width: 20,
          className: 'drag-icon'
        },
        {
          colKey: 'image_group_name',
          title: lang.system_classify,
          ellipsis: true,
          className: 'group-column'
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 100
        },
      ],
      // 操作系统图标
      iconList: ['Windows', 'CentOS', 'Ubuntu', 'Debian', 'ESXi', 'XenServer', 'FreeBSD', 'Fedora', '其他', 'ArchLinux'],
      iconSelecet: [],
      classModel: false,
      classParams: {
        id: '',
        name: '',
        icon: ''
      },
      popupProps: {
        overlayClassName: `custom-select`,
        overlayStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` }),
      },
      /* 其他设置 */
      otherForm: {
        product_id: '',
        host_prefix: '',
        host_length: '',
        ipv6_num: '',
        nat_acl_limit: '',
        nat_web_limit: '',
        niccard: 0,
        cpu_model: 0,
        node_priority: 1,
        ip_mac_bind: 0,
        support_ssh_key: 0,
        rand_ssh_port: 0,
        backup_enable: 0,
        snap_enable: 0,
        reinstall_sms_verify: 0,
        reset_password_sms_verify: 0,
        snap_data: [],
        backup_data: [],
        resource_package: [],
        is_agent: ''
      },
      rulesList: [ // 平衡规则
        { value: 1, label: lang.mf_rule1 },
        { value: 2, label: lang.mf_rule2 },
        { value: 3, label: lang.mf_rule3 },
      ],
      dataRules: {
        data_center_id: [
          { required: true, message: `${lang.select}${lang.area}`, type: 'error' },
        ],
        line_id: [
          { required: true, message: `${lang.select}${lang.line_name}`, type: 'error' },
        ],
        flow: [
          { required: true, message: `${lang.input}${lang.cloud_flow}`, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + '0-999999' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 0 && val <= 999999, message: lang.input + '0-999999' + lang.verify2, type: 'warning'
          }
        ],
        host_prefix: [
          { required: true, message: `${lang.input}${lang.host_prefix}`, type: 'error' },
          {
            pattern: /^[A-Za-z][a-zA-Z0-9_.]{0,9}$/, message: lang.verify8 + '1-10', type: 'warning'
          },
        ],
        host_length: [
          { required: true, message: `${lang.input}${lang.mf_tip2}`, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.mf_tip2, type: 'warning'
          },
        ],
        country_id: [
          { required: true, message: lang.select + lang.country_area, type: 'error' },
        ],
        city: [
          { required: true, message: lang.select + lang.city, type: 'error' },
        ],
        cloud_config: [
          { required: true, message: lang.select + lang.city, type: 'error' },
        ],
        cloud_config_id: [
          { required: true, message: lang.input + 'ID', type: 'error' },
        ],
        area: [
          { required: true, message: `${lang.input}${lang.area}${lang.nickname}`, type: 'error' },
        ],
        name: [
          { required: true, message: `${lang.input}${lang.box_label23}`, type: 'error' },
        ],
        description: [
          { required: true, message: `${lang.input}${lang.description}`, type: 'error' },
        ],
        order: [
          { required: true, message: `${lang.input}${lang.sort}ID`, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.verify7, type: 'warning'
          },
          {
            validator: val => val >= 0, message: lang.verify7, type: 'warning'
          }
        ],
        cpu: [
          { required: true, message: `${lang.input}CPU`, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + '1-240' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 1 && val <= 240, message: lang.input + '1-240' + lang.verify2, type: 'warning'
          }
        ],
        memory: [
          { required: true, message: `${lang.input}${lang.memory}`, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + '1-512' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 1 && val <= 512, message: lang.input + '1-512' + lang.verify2, type: 'warning'
          }
        ],
        system_disk_size: [
          { required: true, message: `${lang.input}${lang.system_disk_size}`, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + '1-1048576' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 1 && val <= 1048576, message: lang.input + '1-1048576' + lang.verify2, type: 'warning'
          }
        ],
        data_disk_size: [{
          pattern: /^[0-9]*$/, message: lang.input + '1-1048576' + lang.verify2, type: 'warning'
        },
        {
          validator: val => val >= 1 && val <= 1048576, message: lang.input + '1-1048576' + lang.verify2, type: 'warning'
        }
        ],
        network_type: [
          { required: true, message: lang.select + lang.net_type, type: 'error' },
        ],
        bw: [
          { required: true, message: `${lang.input}${lang.bw}`, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + '1-30000' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 1 && val <= 30000, message: lang.input + '1-30000' + lang.verify2, type: 'warning'
          }
        ],
        peak_defence: [{
          pattern: /^[0-9]*$/, message: lang.input + '1-999999' + lang.verify2, type: 'warning'
        },
        {
          validator: val => val >= 1 && val <= 999999, message: lang.input + '1-999999' + lang.verify2, type: 'warning'
        }],
        min_memory: [
          { required: true, message: `${lang.input}${lang.memory}`, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + '1-512' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 1 && val <= 512, message: lang.input + '1-512' + lang.verify2, type: 'warning'
          }
        ],
        max_memory: [
          { required: true, message: `${lang.input}${lang.memory}`, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + '1-512' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 1 && val <= 512, message: lang.input + '1-512' + lang.verify2, type: 'warning'
          }
        ],
        line_id: [
          { required: true, message: `${lang.select}${lang.bw_line}`, type: 'error' },
        ],
        min_bw: [
          { required: true, message: `${lang.input}${lang.min_value}`, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + '1-30000' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 1 && val <= 30000, message: lang.input + '1-30000' + lang.verify2, type: 'warning'
          }
        ],
        max_bw: [
          { required: true, message: `${lang.input}${lang.max_value}`, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + '1-30000' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 1 && val <= 30000, message: lang.input + '1-30000' + lang.verify2, type: 'warning'
          }
        ],
      },
      backupColumns: [ // 备份表格
        {
          colKey: 'id',
          title: lang.order_index,
          width: 160
        },
        {
          colKey: 'num',
          title: lang.allow_back_num,
          ellipsis: true,
          width: 180
        },
        {
          colKey: 'price',
          title: lang.min_cycle_price,
          className: 'back-price'
        },
      ],
      snappColumns: [ // 快照表格
        {
          colKey: 'id',
          title: lang.order_index,
          width: 160
        },
        {
          colKey: 'num',
          title: lang.allow_snap_num,
          width: 180,
          ellipsis: true
        },
        {
          colKey: 'price',
          title: lang.min_cycle_price,
          className: 'back-price'
        },
      ],
      resourceColumns: [ // 资源包
        {
          colKey: 'id',
          title: lang.order_index,
          width: 160
        },
        {
          colKey: 'rid',
          title: `${lang.resource_package}ID`,
          width: 180,
          ellipsis: true
        },
        {
          colKey: 'name',
          title: `${lang.resource_package}${lang.nickname}`,
          className: 'back-price'
        },
      ],
      backList: [],
      snapList: [],
      resourceList: [],
      backLoading: false,
      snapLoading: false,
      backAllStatus: false,
      /* 计算配置 */
      cpuList: [],
      cpuLoading: false,
      memoryList: [],
      memoryLoading: false,
      memoryType: '', // 内存方式
      cpuColumns: [ // cpu表格
        {
          colKey: 'value',
          title: `CPU（${lang.cores}）`,
          width: 300
        },
        {
          colKey: 'price',
          title: lang.price,
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 100
        },
      ],
      memoryColumns: [ // memory表格
        {
          colKey: 'value',
          title: `${lang.memory}（GB）`,
          width: 300
        },
        {
          colKey: 'price',
          title: lang.price,
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 100
        },
      ],
      calcType: '', // cpu, memory
      calcForm: {
        // cpu
        product_id: '',
        cpuValue: '', // cpu里面的value， 提交的时候转换
        price: [],
        other_config: {
          advanced_cpu: '',
          cpu_limit: '',
          ipv6_num: '',
          disk_type: ''
        },
        // memory
        type: '',
        value: '',
        min_value: '',
        max_value: '',
        step: '',
        calcForm: 'GB',
        // 性能
        read_bytes: '',
        write_bytes: '',
        read_iops: '',
        write_iops: ''
      },
      calcModel: false,
      configType: [
        { value: 'radio', label: lang.mf_radio },
        { value: 'step', label: lang.mf_step },
        { value: 'total', label: lang.mf_total },
      ],
      calcRules: { // 计算配置验证
        value: [
          { required: true, message: `${lang.input}${lang.bw}`, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + '0-30000' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 0 && val <= 30000, message: lang.input + '0-30000' + lang.verify2, type: 'warning'
          }
        ],
        cpuValue: [
          { required: true, message: `${lang.input}${lang.mf_cores}`, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + '1-240' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 1 && val <= 240, message: lang.input + '1-240' + lang.verify2, type: 'warning'
          }
        ],
        type: [
          { required: true, message: `${lang.select}${lang.config}${lang.mf_way}`, type: 'error' },
        ],
        price: [
          {
            pattern: /^\d+(\.\d{0,2})?$/, message: lang.input + lang.money, type: 'warning'
          },
          {
            validator: val => val >= 0 && val <= 999999, message: lang.verify12, type: 'warning'
          }
        ],
        min_value: [
          { required: true, message: `${lang.input}${lang.min_value}`, type: 'error' },
          {
            pattern: /^([1-9][0-9]*)$/, message: lang.input + '1~1048576' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 1 && val <= 1048576, message: lang.input + '1~1048576' + lang.verify2, type: 'warning'
          }
        ],
        max_value: [
          { required: true, message: `${lang.input}${lang.max_value}`, type: 'error' },
          {
            pattern: /^([1-9][0-9]*)$/, message: lang.input + '2~1048576' + lang.verify2, type: 'warning'
          },
          {
            validator: val => val >= 2 && val <= 1048576, message: lang.input + '2~1048576' + lang.verify2, type: 'warning'
          }
        ],
        step: [
          { required: true, message: `${lang.input}${lang.min_step}`, type: 'error' },
          {
            pattern: /^([1-9][0-9]*)$/, message: lang.input + lang.verify16, type: 'warning'
          },
        ],
        read_bytes: [
          { required: true, message: `${lang.input}`, type: 'error' },
          { validator: this.checkLimit }
        ],
        write_bytes: [
          { required: true, message: `${lang.input}`, type: 'error' },
          { validator: this.checkLimit }
        ],
        read_iops: [
          { required: true, message: `${lang.input}`, type: 'error' },
          { validator: this.checkLimit }
        ],
        write_iops: [
          { required: true, message: `${lang.input}`, type: 'error' },
          { validator: this.checkLimit }
        ],
        traffic_type: [
          { required: true, message: `${lang.select}${lang.traffic_type}`, type: 'error' },
        ],
        bill_cycle: [
          { required: true, message: `${lang.select}${lang.billing_cycle}`, type: 'error' },
        ],
      },
      isAdvance: false, // 是否展开高级配置
      /* 存储配置 */
      systemDisk: [],
      systemLoading: false,
      systemType: '', // 系统盘类型
      dataDisk: [],
      dataLoading: false,
      diskType: '', // 数据盘类型
      systemDiskColumns: [
        {
          colKey: 'value',
          title: `${lang.system_disk_size}（GB）`,
          width: 300
        },
        {
          colKey: 'price',
          title: lang.price,
        },
        {
          colKey: 'type',
          title: lang.disk,
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 100
        },
      ],
      diskColumns: [],
      store_limit: 0, // 性能限制
      systemLimitList: [],
      systemLimitLoading: false,
      diskLimitLoading: false,
      diskLimitList: [],
      natureColumns: [ // 性能表格
        {
          colKey: 'id',
          title: lang.index_text8,
          width: 100
        },
        {
          colKey: 'capacity_size',
          title: `${lang.capacity_size}（GB）`,
          width: 200
        },
        {
          colKey: 'read_bytes',
          title: `${lang.random_read}（MB/s）`,
        },
        {
          colKey: 'write_bytes',
          title: `${lang.random_write}（MB/s）`,
        },
        {
          colKey: 'read_iops',
          title: `${lang.read_iops}（IOPS/s）`,
        },
        {
          colKey: 'write_iops',
          title: `${lang.write_iops}（IOPS/s）`,
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 100
        },
      ],
      disabledWay: false, // 配置方式是否可选
      natureModel: false,
      /* 数据中心 */
      dataList: [],
      dataColumns: [
        {
          colKey: 'id',
          title: lang.index_text8,
          width: 100
        },
        {
          colKey: 'country_name',
          title: lang.country,
          width: 150,
          ellipsis: true,
          className: 'country-td',
        },
        {
          colKey: 'city',
          title: lang.city,
          width: 150,
          ellipsis: true,
          className: 'city-td',
        },
        {
          colKey: 'area',
          title: `${lang.area}${lang.nickname}`,
          width: 150,
          ellipsis: true,
          className: 'area-td',
        },
        {
          colKey: 'line',
          title: lang.line_name,
          className: 'line-td',
          width: 250,
          ellipsis: true,
        },
        {
          colKey: 'price',
          title: lang.price,
          className: 'line-td',
          ellipsis: true,
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 100,
          className: 'line-td'
        },
      ],
      dataForm: { // 新建数据中心
        country_id: '',
        city: '',
        area: '',
        cloud_config: 'node',
        cloud_config_id: ''
      },
      countryList: [],
      // 配置选项
      dataConfig: [
        { value: 'node', lable: lang.node + 'ID' },
        { value: 'area', lable: lang.area + 'ID' },
        { value: 'node_group', lable: lang.node_group + 'ID' },
      ],
      /* 线路相关 */
      lineType: '', // 新增,编辑线路，新增的时候本地操作，保存一次性提交
      subType: '', // 线路子项类型， line_bw, line_flow, line_defence, line_ip
      lineForm: {
        country_id: '', // 线路国家
        city: '', // 线路城市
        data_center_id: '',
        name: '',
        bill_type: '', // bw, flow
        bw_ip_group: '',
        defence_ip_group: '',
        ip_enable: 0, // ip开关
        defence_enable: 0, // 防护开关
        bw_data: [], // 带宽
        flow_data: [], //流量
        defence_data: [], // 防护
        ip_data: [], // ip
        flow: '',
        line_id: '',
        link_clone: false,
        /* 推荐配置 */
        description: '',
        order: '',
        cpu: '',
        memory: '',
        system_disk_size: '',
        system_disk_type: '',
        data_disk_size: '',
        data_disk_type: '',
        network_type: '',
        bw: '',
        peak_defence: '',
        /* 配置限制 */
        type: '', // cpu, data_center,line
        line_id: '',
        min_bw: '',
        max_bw: '',
        min_memory: '',
        max_memory: ''
      },
      bw_ip_show: false, // bw 高级配置
      defence_ip_show: false, // 防护高级配置
      subForm: { // 线路子项表单
        type: '',
        value: '',
        price: [],
        min_value: '',
        max_value: '',
        step: '',
        other_config: {
          in_bw: '',
          out_bw: '',
          traffic_type: '',
          bill_cycle: '',
          store_id: '',
          advanced_bw: ''
        }
      },
      lineModel: false,
      lineRight: false,
      delSubIndex: 0,
      subId: '',
      countrySelect: [], // 国家三级联动
      billType: [
        { value: 'bw', label: lang.mf_bw },
        { value: 'flow', label: lang.mf_flow }
      ],
      bwColumns: [
        {
          colKey: 'fir',
          title: lang.bw,
        },
        {
          colKey: 'price',
          title: lang.price,
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 100
        },
      ],
      trafficTypes: [
        { value: '1', label: lang.in },
        { value: '2', label: lang.out },
        { value: '3', label: lang.in_out },
      ],
      billingCycle: [
        { value: 'month', label: lang.natural_month },
        { value: 'last_30days', label: lang.last_30days },
      ],
      /* 推荐配置 */
      calcLineType: '',
      recommendList: [],
      systemDiskType: [],
      dataDiskType: [],
      recommendModel: false,
      recommendColumns: [
        {
          colKey: 'id',
          title: lang.order_text68,
          width: 100
        },
        {
          colKey: 'name',
          title: `${lang.mf_recommend}${lang.nickname}`,
          width: 250,
          ellipsis: true,
        },
        {
          colKey: 'description',
          title: lang.mf_des,
          ellipsis: true,
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 100
        },
      ],
      networkType: [
        { value: 'normal', label: lang.normal_network },
        { value: 'vpc', label: lang.vpc_network }
      ],
      /* 配置限制 */
      cpu_list: [],
      data_center_list: [],
      line_ist: [],
      cpu_loading: false,
      data_center_loading: false,
      line_oading: false,
      data_center_columns: [],
      cpu_columns: [
        {
          colKey: 'cpu',
          title: 'CPU',
        },
        {
          colKey: 'memory',
          title: lang.memory,
        },
        {
          colKey: 'op',
          title: lang.operation,
          width: 100
        },
      ],
      line_columns: [],
      limitArr: [
        { name: 'cpu', tit: lang.mf_tip16 },
        { name: 'data_center', tit: lang.mf_tip17 },
        // { name: 'line', tit: lang.mf_tip18 },
      ],
      limitType: '',
      limitModel: false,
      limitMemoryType: '', // 配置限制里面内存的方式
      memory_unit: ''
    }
  },
  watch: {
    'otherForm.backup_enable': {
      handler () {
        if (this.backList.length === 0) {
          this.backList.push({
            num: 1,
            type: 'backup',
            price: 0.00,
            status: true
          })
          this.backAllStatus = true
        }
      },
      immediate: true
    },
    'otherForm.snap_enable': {
      handler () {
        if (this.snapList.length === 0) {
          this.snapList.push({
            num: 1,
            type: 'snap',
            price: 0.00,
            status: true
          })
          this.backAllStatus = true
        }
      },
      immediate: true
    },
    store_limit: {
      immediate: true,
      handler (val) {
        if (val * 1) {
          this.getStoreLimitList('system_disk_limit')
          this.getStoreLimitList('data_disk_limit')
        }
      }
    }
  },
  computed: {
    calcName () {
      return (type) => {
        switch (type) {
          case 'memory':
            return `${lang.memory_config}`;
          case 'system_disk':
            return `${lang.system_disk_size}${lang.capacity}`;
          case 'data_disk':
            return `${lang.data_disk}${lang.capacity}`
          case 'line_bw':
            return `${lang.bw}（Mbps）`;
        }
      }

    },
    calcIcon () {
      return this.host + '/upload/common/country/' + this.countryList.filter(item => item.id === this.dataForm.country_id)[0]?.iso + '.png'
    },
    calcIcon1 () {
      if (!this.countrySelect) {
        return
      }
      return this.host + '/upload/common/country/' + this.countrySelect.filter(item => item.id === this.lineForm.country_id)[0]?.iso + '.png'
    },
    calcCity () {
      if (!this.countrySelect) {
        return
      }
      const city = this.countrySelect.filter(item => item.id === this.lineForm.country_id)[0]?.city || []
      if (city.length === 1) {
        this.lineForm.city = city[0].name
      }
      return city
    },
    calcArea () {
      if (!this.countrySelect) {
        return
      }
      const area = this.countrySelect.filter(item => item.id === this.lineForm.country_id)[0]?.city.filter(item => item.name === this.lineForm.city)[0]?.area || []
      if (area.length === 1) {
        this.lineForm.data_center_id = area[0].id
      }
      return area
    },
    calcSelectLine () {
      if (!this.countrySelect) {
        return
      }
      const line = this.countrySelect.filter(item => item.id === this.lineForm.country_id)[0]
        ?.city.filter(item => item.name === this.lineForm.city)[0]
        ?.area.filter(item => item.id === this.lineForm.data_center_id)[0]?.line || []
      if (line.length === 1) {
        this.lineForm.line_id = line[0].id
        this.calcLineType = line[0].bill_type
      }
      return line
    },
    calcColums () {
      return (val) => {
        const temp = JSON.parse(JSON.stringify(this.bwColumns))
        switch (val) {
          case 'flow':
            temp[0].title = lang.cloud_flow + '（GB）'
            return temp;
          case 'defence':
            temp[0].title = lang.defence + '（GB）'
            return temp;
          case 'ip':
            temp[0].title = 'IP' + lang.auth_num + `（${lang.one}）`
            return temp
        }
      }
    },
    calcSubTitle () { // 副标题
      return (data) => {
        if (data.length > 0) {
          return lang[`mf_${data[0].type}`] + lang.mf_way
        } else {
          return ''
        }
      }
    },
    calcPrice () { // 处理本地价格展示
      return (price) => {
        // 找到价格最低的
        const arr = Object.values(price).sort((a, b) => {
          return a - b
        }).filter(Number)
        if (arr.length > 0) {
          let temp = ''
          Object.keys(price).forEach(item => {
            if (price[item] * 1 === arr[0] * 1) {
              const name = this.cycleData.filter(el => el.id === item * 1)[0]?.name
              temp = (arr[0] * 1).toFixed(2) + '/' + name
            }
          })
          return temp
        } else {
          return '0.00'
        }
      }
    },
    // 子项的计费方式是否可选
    calcShow () {
      switch (this.subType) {
        case 'line_bw':
          return this.lineForm.bw_data.length > 0 ? true : false
      }
    },
    calcLimitData () {
      return (name) => {
        return this[`${name}_list`]
      }
    },
    calcLimitCol () {
      return (name) => {
        return this[`${name}_columns`]
      }
    },
    calcCpu () {
      return (val) => {
        return val.value + lang.cores
      }
    },
    calcMemory () {
      return (val) => {
        return val.value + this.memory_unit
      }
    },
    calcLine () { // 当前线路
      return this.dataList.filter(item => item.country_id === this.lineForm.country_id && item.city === this.lineForm.city)[0]?.line
    },
    calcMemery () {
      return (data) => {
        return data.split(',')
      }
    },
    calcRange () { // 计算验证范围
      return (val) => {
        if (this.calcType === 'memory') { // 内存
          if (this.calcForm.memory_unit === 'GB') {
            return val >= 1 && val <= 512
          } else {
            return val >= 128 && val <= 524288
          }
        } else {
          return val >= 1 && val <= 1048576
        }
      }
    },
    calcReg () { // 动态生成规则
      return (name, min, max) => {
        return [
          { required: true, message: `${lang.input}${name}`, type: 'error' },
          {
            pattern: /^[0-9]*$/, message: lang.input + `${min}-${max}` + lang.verify1, type: 'warning'
          },
          {
            validator: val => val >= min && val <= max, message: lang.input + `${min}-${max}` + lang.verify1, type: 'warning'
          }]
      }
    },
    calcUnit () {
      if (this.calcType === 'memory') {
        return this.calcForm.memory_unit
      } else {
        return 'GB'
      }
    },
    calcPlaceh () {
      if (this.calcType === 'memory') {
        return this.calcForm.memory_unit === 'GB' ? lang.mf_tip9 : lang.mf_tip33
      } else {
        return lang.mf_tip9
      }
    },
    calcMemeryColumns () {
      if (this.memoryList.length === 0) {
        return this.memoryColumns
      } else {
        const temp = JSON.parse(JSON.stringify(this.memoryColumns))
        temp[0].title = `${lang.memory}（MB）`
        return this.memory_unit === 'MB' ? temp : this.memoryColumns
      }
    }
  },
  methods: {
    async changeSort (e) {
      try {
        this.systemGroup = e.newData
        const image_group_order = e.newData.reduce((all, cur) => {
          all.push(cur.id)
          return all
        }, [])
        const res = await changeImageGroup({ image_group_order })
        this.$message.success(res.data.msg)
        this.getGroup()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 切换选项卡
    changeTab (e) {
      this.allStatus = false
      this.backAllStatus = false
      switch (e) {
        case 'duration':
          this.getDurationList()
          break;
        case 'calc':
          this.getCpuList()
          this.getMemoryList()
          this.getDurationList()
          break;
        case 'data_center':
          this.getDataList()
          this.getCountryList()
          this.chooseData()
          this.getDurationList()
          break;
        case 'store':
          this.getStoreList('system_disk')
          this.getStoreList('data_disk')
          this.getOtherConfig() // 获取性能开关
          this.getDurationList()
          break;
        case 'limit':
          this.getConfigLimitList('cpu')
          this.getConfigLimitList('data_center')
          // this.getConfigLimitList('line')
          this.getCpuList()
          this.getMemoryList()
          this.chooseData()
          this.getDataList()
          break;
        case 'recommend':
          this.getRecommendList()
          this.getMemoryList()
          this.chooseData()
          this.getDiskTypeList('system_disk')
          this.getDiskTypeList('data_disk')
          this.calcType = 'memory'
          break;
        case 'system':
          this.getSystemList()
          this.getGroup()
          break;
        case 'other':
          this.getOtherConfig()
          break;
        default:
          break;
      }
    },
    checkLimit (val) {
      const reg = /^[0-9]*$/
      if (reg.test(val) && val >= 0 && val <= 99999999) {
        return { result: true };
      } else {
        return { result: false, message: lang.input + '0~99999999' + lang.verify2, type: 'warning' };
      }
    },
    changeMinMemory (val) {
      if (this.lineForm.max_memory) {
        if (val * 1 >= this.lineForm.max_memory * 1) {
          this.lineForm.min_memory = val >= 524288 ? val - 1 : val
          this.lineForm.max_memory =  this.lineForm.min_memory * 1 + 1
        }
      }
    },
    changeMaxMemory (val) {
      if (this.lineForm.min_memory) {
        if (val * 1 <= this.lineForm.min_memory * 1) {
          this.lineForm.max_memory = this.lineForm.max_memory >= 2 ? val : 2
          this.lineForm.min_memory = this.lineForm.max_memory * 1 - 1
        }
      }
    },
    // 处理价格
    blurPrice (val, ind) {
      let temp = (String(val).match(/^\d*(\.?\d{0,2})/g)[0]) || ''
      if (temp && !isNaN(Number(temp))) {
        temp = Number(temp).toFixed(2)
      }
      if (temp >= 999999) {
        this.calcForm.price[ind].price = Number(999999).toFixed(2)
      } else {
        this.calcForm.price[ind].price = temp
      }
    },
    blurSubPrice (val, ind) {
      let temp = (String(val).match(/^\d*(\.?\d{0,2})/g)[0]) || ''
      if (temp && !isNaN(Number(temp))) {
        temp = Number(temp).toFixed(2)
      }
      if (temp >= 999999) {
        val = 999999.00
        this.subForm.price[ind].price = Number(999999).toFixed(2)
      } else {
        this.subForm.price[ind].price = temp
      }
    },
    /* 配置限制 */
    /* name: cpu , data_center ,line */
    async getConfigLimitList (name) {
      try {
        this[`${name}_loading`] = true
        const res = await getConfigLimit({
          product_id: this.id,
          type: name,
          orderby: 'id',
          sort: 'desc',
          page: 1,
          limit: 1000
        })
        this[`${name}_list`] = res.data.data.list
        this[`${name}_loading`] = false
      } catch (error) {
        this[`${name}_loading`] = false
      }
    },
    addLimit (name) {
      this.optType = 'add'
      this.limitType = name
      this.limitModel = true
      this.dataForm.country_id = ''
      this.lineForm = {
        country_id: '',
        city: '',
        data_center_id: '',
        type: '', // cpu, data_center,line
        line_id: '',
        min_bw: '',
        max_bw: '',
        cpu: [],
        memory: [],
        min_memory: '',
        max_memory: ''
      }
      this.comTitle = `${lang.order_text53}${lang.limit}`
    },
    editLimit (name, row) {
      this.comTitle = `${lang.edit}${lang.limit}`
      this.limitType = name
      this.limitModel = true
      this.optType = 'update'
      const temp = JSON.parse(JSON.stringify(row))
      temp.memory = temp.memory.split(',').map(item => item * 1)
      temp.cpu = temp.cpu.map(item => item * 1)
      this.lineForm = temp

    },
    async submitLimit ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.lineForm))
          params.product_id = this.id
          params.type = this.limitType
          if (this.optType === 'add') {
            delete params.id
          }
          this.submitLoading = true
          const res = await createAndUpdateConfigLimit(this.optType, params)
          this.$message.success(res.data.msg)
          this.getConfigLimitList(this.limitType)
          this.limitModel = false
          this.submitLoading = false
        } catch (error) {
          this.submitLoading = false
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },
    async delLimit () {
      try {
        const res = await delConfigLimit({ id: this.delId })
        this.$message.success(res.data.msg)
        this.delVisible = false
        this.getConfigLimitList(this.delType)
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    /* 推荐配置 */
    async getRecommendList () {
      try {
        this.dataLoading = true
        const res = await getRecommend({
          product_id: this.id,
          page: 1,
          limit: 1000
        })
        this.recommendList = res.data.data.list
        this.dataLoading = false
      } catch (error) {
        this.dataLoading = false
      }
    },
    async getDiskTypeList (type) {
      try {
        const res = await getDiskType(type, {
          product_id: this.id,
        })
        if (type === 'system_disk') {
          this.systemDiskType = res.data.data.list
        } else {
          this.dataDiskType = res.data.data.list
        }
      } catch (error) {
        this.dataLoading = false
      }
    },

    addRecommend () {
      this.lineForm = {
        country_id: '',
        city: '',
        name: '',
        description: '',
        order: '',
        data_center_id: '',
        cpu: '',
        memory: '',
        system_disk_size: '',
        system_disk_type: '',
        data_disk_size: '',
        data_disk_type: '',
        network_type: '',
        bw: '',
        peak_defence: '',
        flow: '',
        line_id: ''
      }
      this.optType = 'add'
      this.recommendModel = true
    },
    editRecommend (row) {
      this.lineForm = JSON.parse(JSON.stringify(row))
      this.optType = 'update'
      this.recommendModel = true
      const type = this.countrySelect.filter(item => item.id === this.lineForm.country_id)[0]
        ?.city.filter(item => item.name === this.lineForm.city)[0]
        ?.area.filter(item => item.id === this.lineForm.data_center_id)[0]
        ?.line.filter(item => item.id === this.lineForm.line_id)[0]?.bill_type
      this.calcLineType = type
    },
    async submitRecommend ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.lineForm))
          if (this.optType === 'add') {
            delete params.id
          }
          if (this.calcLineType === 'bw') {
            delete params.flow
          } else if (this.calcLineType === 'flow') {
            delete params.bw
          }
          this.submitLoading = true
          const res = await createAndUpdateRecommend(this.optType, params)
          this.$message.success(res.data.msg)
          this.getRecommendList(this.calcType)
          this.recommendModel = false
          this.submitLoading = false
        } catch (error) {
          this.submitLoading = false
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },
    // 删除推荐
    async delRecommend () {
      try {
        const res = await delRecommend({ id: this.delId })
        this.$message.success(res.data.msg)
        this.delVisible = false
        this.getRecommendList()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    /* 推荐配置 end*/
    /* 线路 */
    addLine () {
      this.lineModel = true
      this.lineType = 'add'
      this.dataForm.country_id = ''
      this.lineForm = {
        country_id: '', // 线路国家
        city: '', // 线路城市
        data_center_id: '',
        name: '',
        bill_type: 'bw', // bw, flow
        bw_ip_group: '',
        defence_ip_group: '',
        ip_enable: 0, // ip开关
        defence_enable: 0, // 防护开关
        bw_data: [], // 带宽
        flow_data: [], //流量
        defence_data: [], // 防护
        ip_data: [], // ip
        link_clone: false
      }
      this.lineRight = false
    },
    async editLine (row) {
      try {
        const res = await getLineDetails({ id: row.id })
        this.lineForm = JSON.parse(JSON.stringify(res.data.data))
        this.lineForm.link_clone = this.lineForm.link_clone * 1 ? true : false
        this.lineType = 'update'
        this.optType = 'update'
        this.lineRight = false
        this.lineModel = true
        this.bw_ip_show = this.lineForm.bw_ip_group ? true : false
        this.defence_ip_show = this.lineForm.defence_ip_group ? true : false
        this.subId = row.id
      } catch (error) {

      }
    },
    changeCountry () {
      this.lineForm.city = ''
      this.lineForm.data_center_id = ''
    },
    changeCity () {
      this.lineForm.data_center_id = ''
    },
    // 编辑线路子项
    async editSubItem (row, index, type) {
      this.subType = type
      this.optType = 'update'
      this.delSubIndex = index
      this.lineRight = true
      let temp = ''
      if (this.lineType === 'add') {
        temp = row
      } else {
        const res = await getLineChildDetails(type, { id: row.id })
        temp = res.data.data
        if (temp.other_config?.traffic_type) {
          temp.other_config.traffic_type = String(temp.other_config.traffic_type)
        }
        this.delId = row.id
      }
      setTimeout(() => {
        const price = temp.duration.reduce((all, cur) => {
          all.push({
            id: cur.id,
            name: cur.name,
            price: cur.price
          })
          return all
        }, []).sort((a, b) => {
          return a.id - b.id
        })
        Object.assign(this.subForm, temp)
        this.subForm.price = price
        if (this.subForm.other_config.in_bw || this.subForm.other_config.advanced_bw) {
          this.isAdvance = true
        } else {
          this.isAdvance = false
        }
      }, 0);
    },
    // 删除线路子项
    async delSubItem () {
      try {
        this.lineRight = false
        if (this.lineType === 'add') { // 本地删除
          switch (this.delType) {
            case 'line_bw':
              return this.lineForm.bw_data.splice(this.delSubIndex, 1)
            case 'line_flow':
              return this.lineForm.flow_data.splice(this.delSubIndex, 1)
            case 'line_defence':
              return this.lineForm.defence_data.splice(this.delSubIndex, 1)
            case 'line_ip':
              return this.lineForm.ip_data.splice(this.delSubIndex, 1)
          }
        } else { // 编辑的时候删除
          const res = await delLineChild(this.delType, { id: this.delId })
          this.$message.success(res.data.msg)
          this.delVisible = false
          // this.editLine({ id: this.subId })
          this.submitLine({ validateResult: true, firstError: '' }, false)
        }
      } catch (error) {
        this.delVisible = false
        this.$message.error(error.data.msg)
      }
    },
    // 新增线路子项
    addLineSub (type) {
      this.subType = type
      this.optType = 'add'
      this.isAdvance = false
      if (type === 'line_bw') {
        this.subForm.type = this.lineForm.bw_data[0]?.type || 'radio'
      }

      this.subForm.value = ''
      this.subForm.other_config = {
        in_bw: '',
        advanced_bw: '',
        traffic_type: '3',
        bill_cycle: 'last_30days'
      }
      this.lineRight = true
      const price = this.cycleData.reduce((all, cur) => {
        all.push({
          id: cur.id,
          name: cur.name,
          price: ''
        })
        return all
      }, []).sort((a, b) => {
        return a.id - b.id
      })
      this.subForm.price = price
      this.bw_ip_show = false
      this.defence_ip_show = false
    },
    /* 推荐配置 */
    changeBillType (e) {
      this.calcLineType = this.calcSelectLine.filter(item => item.id === e)[0]?.bill_type
    },
    // 保存线路子项
    async submitSub ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.subForm))
          params.product_id = this.id
          params.step = 1
          this.submitLoading = true
          const duration = JSON.parse(JSON.stringify(params.price))
          params.price = params.price.reduce((all, cur) => {
            cur.price && (all[cur.id] = cur.price)
            return all
          }, {})

          // 新增的时候本地处理
          if (this.lineType === 'add') {
            params.duration = duration
            switch (this.subType) {
              case 'line_bw':
                this.optType === 'add'
                  ? this.lineForm.bw_data.unshift(params)
                  : this.lineForm.bw_data.splice(this.delSubIndex, 1, params)
                break;
              case 'line_flow':
                this.optType === 'add'
                  ? this.lineForm.flow_data.unshift(params)
                  : this.lineForm.flow_data.splice(this.delSubIndex, 1, params)
                break;
              case 'line_defence':
                this.optType === 'add'
                  ? this.lineForm.defence_data.unshift(params)
                  : this.lineForm.defence_data.splice(this.delSubIndex, 1, params)
                break;
              case 'line_ip':
                this.optType === 'add'
                  ? this.lineForm.ip_data.unshift(params)
                  : this.lineForm.ip_data.splice(this.delSubIndex, 1, params)
                break;
            }
            this.submitLoading = false
            this.lineRight = false
            return
          }
          // 新增：传线路id，编辑传配置id
          params.id = this.optType === 'add' ? this.subId : this.delId
          const res = await createAndUpdateLineChild(this.subType, this.optType, params)
          this.$message.success(res.data.msg)
          // this.editLine({ id: this.subId })
          // 保存子项的时候需要保存线路配置，第一次未开启防护/附加IP的时候，开关会被重置
          this.submitLine({ validateResult: true, firstError: '' }, false)
          this.submitLoading = false
        } catch (error) {
          this.submitLoading = false
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },

    async submitLine ({ validateResult, firstError }, bol = true) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.lineForm))
          params.product_id = this.id
          params.link_clone = params.link_clone ? 1 : 0
          // if (params.bill_type === 'bw') {
          //   if (params.bw_data.length === 0) {
          //     return this.$message.warning(lang.mf_tip13)
          //   }
          // }
          // if (params.bill_type === 'flow') {
          //   if (params.flow_data.length === 0) {
          //     return this.$message.warning(lang.mf_tip14)
          //   }
          // }
          const isAdd = params.id ? 'update' : 'add'
          this.submitLoading = true
          const res = await createAndUpdateLine(isAdd, params)
          if (bol) {
            this.$message.success(res.data.msg)
            this.getDataList()
            this.lineModel = false
          } else {
            this.editLine({ id: this.subId })
          }
          this.submitLoading = false
        } catch (error) {
          this.submitLoading = false
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },

    /* 数据中心 */
    async getDataList () {
      try {
        this.dataLoading = true
        const res = await getDataCenter({
          product_id: this.id,
          page: 1,
          limit: 1000
        })
        this.dataList = res.data.data.list
        this.dataLoading = false
      } catch (error) {
        this.dataLoading = false
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
    async chooseData () {
      try {
        const res = await chooseDataCenter({
          product_id: this.id
        })
        this.countrySelect = res.data.data.list
        if (this.countrySelect.length === 1) {
          this.lineForm.country_id = this.countrySelect[0].id
        }
      } catch (error) {
      }
    },
    changeType () {
      this.$refs.dataForm.clearValidate(['cloud_config_id'])
    },
    addData () {
      this.optType = 'add'
      this.dataModel = true
      this.dataForm.country_id = ''
      this.dataForm.city = ''
      this.dataForm.area = ''
      this.dataForm.cloud_config = 'node'
      this.dataForm.cloud_config_id = ''
      this.comTitle = lang.new_create + lang.data_center
    },
    async deleteData () {
      try {
        const res = await deleteDataCenter({ id: this.delId })
        this.$message.success(res.data.msg)
        this.delVisible = false
        this.getDataList()
        this.chooseData()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    async deleteLine () {
      try {
        const res = await delLine({ id: this.delId })
        this.$message.success(res.data.msg)
        this.delVisible = false
        this.getDataList()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    editData (row) {
      this.comTitle = lang.edit + lang.data_center
      this.optType = 'update'
      this.dataModel = true
      const { id, country_id, city, area, cloud_config, cloud_config_id } = row
      this.dataForm = {
        id, country_id, city, area, cloud_config, cloud_config_id
      }
    },
    // 保存数据中心
    async submitData ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.dataForm))
          params.product_id = this.id
          if (this.optType === 'add') {
            delete params.id
          }
          this.submitLoading = true
          const res = await createOrUpdateDataCenter(this.optType, params)
          this.$message.success(res.data.msg)
          this.getDataList()
          this.chooseData()
          this.dataModel = false
          this.submitLoading = false
        } catch (error) {
          this.submitLoading = false
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },
    /* 存储配置 */
    async getStoreList (name) {
      try {
        if (name === 'system_disk') {
          this.systemLoading = true
        } else {
          this.dataLoading = true
        }
        const res = await getStore(name, {
          product_id: this.id,
          page: 1,
          limit: 1000
        })
        if (name === 'system_disk') {
          this.systemDisk = res.data.data.list
        } else {
          this.dataDisk = res.data.data.list
        }
        if (name === 'system_disk') {
          this.systemLoading = false
        } else {
          this.dataLoading = false
        }
      } catch (error) {
        this.systemLoading = false
        this.dataLoading = false
      }
    },
    async getStoreLimitList (name) {
      try {
        if (name === 'system_disk_limit') {
          this.systemLimitLoading = true
        } else {
          this.diskLimitLoading = true
        }
        const res = await getStoreLimit(name, {
          product_id: this.id,
          page: 1,
          limit: 1000
        })
        if (name === 'system_disk_limit') {
          this.systemLimitList = res.data.data.list
        } else {
          this.diskLimitList = res.data.data.list
        }
        if (name === 'system_disk_limit') {
          this.systemLimitLoading = false
        } else {
          this.diskLimitLoading = false
        }
      } catch (error) {
        this.systemLimitLoading = false
        this.diskLimitLoading = false
      }
    },
    // 切换性能开关
    async changeLimit (val) {
      try {
        const res = await changeCloudSwitch({
          product_id: this.id,
          status: val * 1
        })
        this.$message.success(res.data.msg)
        this.getOtherConfig()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 删除存储
    async deleteStore (name) {
      try {
        const res = await delStore(name, { id: this.delId })
        this.$message.success(res.data.msg)
        this.delVisible = false
        this.getStoreList(name)
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 删除存储限制
    async deleteStoreLimit (name) {
      try {
        const res = await delStoreLimit(name, { id: this.delId })
        this.$message.success(res.data.msg)
        this.delVisible = false
        this.getStoreLimitList(name)
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 性能提交
    async submitNature ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const {
            id,
            min_value,
            max_value,
            read_bytes,
            write_bytes,
            read_iops,
            write_iops } = this.calcForm
          const params = {
            id,
            product_id: this.id,
            min_value,
            max_value,
            read_bytes,
            write_bytes,
            read_iops,
            write_iops
          }
          if (this.optType === 'add') {
            delete params.id
          }
          this.submitLoading = true
          const res = await createAndUpdateStoreLimit(this.calcType, this.optType, params)
          this.$message.success(res.data.msg)
          this.getStoreLimitList(this.calcType)
          this.natureModel = false
          this.submitLoading = false
        } catch (error) {
          this.submitLoading = false
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },
    /* 存储配置 end*/
    /* 计算配置 */
    async getCpuList () {
      try {
        this.cpuLoading = true
        const res = await getCpu({
          product_id: this.id,
          page: 1,
          limit: 1000
        })
        this.cpuList = res.data.data.list
        this.cpuLoading = false
      } catch (error) {
        this.cpuLoading = false
      }
    },
    async getMemoryList () {
      try {
        this.memoryLoading = true
        const res = await getMemory({
          product_id: this.id,
          page: 1,
          limit: 1000
        })
        this.memoryList = res.data.data.list
        this.calcForm.memory_unit = this.memory_unit = res.data.data.memory_unit
        this.memoryLoading = false
        this.memoryType = lang['mf_' + this.memoryList[0]?.type]
        this.limitMemoryType = this.memoryList[0]?.type || ''
      } catch (error) {
        this.memoryLoading = false
      }
    },
    addCalc (type) { // 添加cpu/memory/system/disk
      this.calcType = type
      this.optType = 'add'
      let temp_type = '', memory_unit = ''
      switch (type) {
        case 'cpu':
          this.comTitle = `${lang.order_text53}CPU${lang.auth_num}`;
          break;
        case 'memory':
          if (this.memoryList.length > 0) {
            this.disabledWay = true
            temp_type = this.memoryList[0].type
            memory_unit = this.memory_unit
          } else {
            this.disabledWay = false
            memory_unit = 'GB'
          }
          this.comTitle = `${lang.order_text53}${lang.memory}`;
          break;
        case 'system_disk':
          if (this.systemDisk.length > 0) {
            this.disabledWay = true
            temp_type = this.systemDisk[0].type
          } else {
            this.disabledWay = false
          }
          this.comTitle = `${lang.order_text53}${lang.system_disk_size}`;
          break;
        case 'data_disk':
          if (this.dataDisk.length > 0) {
            this.disabledWay = true
            temp_type = this.dataDisk[0].type
          } else {
            this.disabledWay = false
          }
          this.comTitle = `${lang.order_text53}${lang.data_disk}`;
          break;
        case 'system_disk_limit':
        case 'data_disk_limit':
          this.comTitle = `${lang.order_text53}${lang.disk_limit_enable}`;
          this.natureModel = true
          this.calcForm = {
            min_value: '',
            max_value: '',
            read_bytes: '',
            write_bytes: '',
            read_iops: '',
            write_iops: ''
          }
          return;
      }
      this.calcModel = true
      const price = this.cycleData.reduce((all, cur) => {
        all.push({
          id: cur.id,
          name: cur.name,
          price: ''
        })
        return all
      }, []).sort((a, b) => {
        return a.id - b.id
      })
      this.isAdvance = false
      this.calcForm = {
        product_id: '',
        cpuValue: '', // cpu里面的value， 提交的时候转换
        price,
        other_config: {
          advanced_cpu: '',
          cpu_limit: '',
          ipv6_num: '',
          disk_type: '',
          store_id: ''
        },
        // memory
        type: temp_type,
        value: '',
        min_value: '',
        max_value: '',
        step: '',
        memory_unit: memory_unit
      }
    },
    // 编辑cpu,memory
    async editCalc (row, type) {
      this.calcType = type
      this.optType = 'update'
      this.disabledWay = true
      switch (type) {
        case 'cpu':
          this.comTitle = `${lang.edit}CPU${lang.auth_num}`
          this.editCpu(row)
          break;
        case 'memory':
          this.comTitle = `${lang.edit}${lang.memory}`
          this.calcForm.memory_unit = this.memory_unit
          this.editMemory(row)
          break;
        case 'system_disk':
          this.comTitle = `${lang.edit}${lang.system_disk_size}`
          this.editStore('system_disk', row)
          break;
        case 'data_disk':
          this.comTitle = `${lang.edit}${lang.data_disk}`
          this.editStore('data_disk', row)
          break;
        case 'system_disk_limit':
          this.comTitle = `${lang.edit}${lang.system_disk_size}`
          Object.assign(this.calcForm, row)
          this.natureModel = true
          break;
        case 'data_disk_limit':
          this.comTitle = `${lang.edit}${lang.system_disk_size}`
          Object.assign(this.calcForm, row)
          this.natureModel = true
          break;
      }
      this.isAdvance = false
    },
    async editCpu (row) {
      try {
        const res = await getCpuDetails({
          id: row.id
        })
        this.calcModel = true
        const temp = res.data.data
        this.calcForm.id = temp.id
        this.calcForm.cpuValue = temp.value
        let price = temp.duration.reduce((all, cur) => {
          all.push({
            id: cur.id,
            name: cur.name,
            price: cur.price
          })
          return all
        }, []).sort((a, b) => {
          return a.id - b.id
        })
        this.calcForm.id = row.id
        this.calcForm.price = price
        this.calcForm.other_config = temp.other_config
        this.optType = 'update'
        this.calcModel = true
        if (this.calcForm.other_config.advanced_cpu || this.calcForm.other_config.cpu_limit || this.calcForm.other_config.ipv6_num) {
          this.isAdvance = true
        }
      } catch (error) {

      }
    },
    // 编辑内存
    async editMemory (row) {
      try {
        const res = await getMemoryDetails({
          id: row.id
        })
        this.calcModel = true
        const temp = res.data.data
        this.calcForm.id = temp.id
        this.calcForm.type = temp.type
        this.calcForm.value = temp.value
        let price = temp.duration.reduce((all, cur) => {
          all.push({
            id: cur.id,
            name: cur.name,
            price: cur.price
          })
          return all
        }, []).sort((a, b) => {
          return a.id - b.id
        })
        this.calcForm.id = row.id
        this.calcForm.price = price
        this.calcForm.min_value = temp.min_value
        this.calcForm.max_value = temp.max_value
        this.calcForm.step = temp.step
        this.optType = 'update'
        this.calcModel = true
      } catch (error) {

      }
    },
    // 编辑存储
    async editStore (name, row) {
      try {
        const res = await getStoreDetails(name, {
          id: row.id
        })
        const temp = res.data.data
        this.calcForm.id = temp.id
        this.calcForm.value = temp.value
        this.calcForm.min_value = temp.min_value
        this.calcForm.max_value = temp.max_value
        this.calcForm.step = temp.step
        this.calcForm.type = temp.type
        let price = temp.duration.reduce((all, cur) => {
          all.push({
            id: cur.id,
            name: cur.name,
            price: cur.price
          })
          return all
        }, []).sort((a, b) => {
          return a.id - b.id
        })
        this.calcForm.price = price
        this.calcForm.other_config = temp.other_config
        this.optType = 'update'
        if (temp.other_config.disk_type || temp.other_config.store_id) {
          this.isAdvance = true
        }
        this.calcModel = true
      } catch (error) {

      }
    },
    submitCalc ({ validateResult, firstError }) {
      if (validateResult === true) {
        switch (this.calcType) {
          case 'cpu':
            return this.handlerCpu()
          case 'memory':
            return this.handlerMemory()
          case 'system_disk':
          case 'data_disk':
            return this.handlerStore()
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },
    async deleteCpu () {
      try {
        const res = await delCpu({
          id: this.delId
        })
        this.$message.success(res.data.msg)
        this.delVisible = false
        this.getCpuList()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 提交cpu
    async handlerCpu () {
      try {
        let { id, cpuValue, price, other_config } = this.calcForm
        price = price.reduce((all, cur) => {
          cur.price && (all[cur.id] = cur.price)
          return all
        }, {})
        const params = {
          id,
          product_id: this.id,
          value: cpuValue,
          price,
          other_config
        }
        if (this.optType === 'add') {
          delete params.id
        }
        this.submitLoading = true
        const res = await createAndUpdateCpu(this.optType, params)
        this.$message.success(res.data.msg)
        this.getCpuList()
        this.calcModel = false
        this.submitLoading = false
      } catch (error) {
        this.submitLoading = false
        this.$message.error(error.data.msg)
      }
    },
    /* 改变最大最小值：内存，系统盘和数据盘
         根据calcType来区分：memory=512， 其他 1048576
          */
    changeMin (e) {
      const num = this.calcType === 'memory' ? (this.calcForm.memory_unit === 'GB' ? 512 : 524288) : 1048576
      if (e * 1 >= num) {
        this.calcForm.min_value = 1
      } else if (e * 1 >= this.calcForm.max_value * 1) {
        if (this.calcForm.max_value * 1) {
          this.calcForm.max_value = e * 1 + 1
        }
      }
    },
    changeMax (e) {
      const num = this.calcType === 'memory' ? (this.calcForm.memory_unit === 'GB' ? 512 : 524288) : 1048576
      if (e * 1 === 1) {
        return this.calcForm.max_value = 2
      }
      if (e * 1 > num) {
        this.calcForm.max_value = num
      } else if (e * 1 <= this.calcForm.min_value * 1 && e * 1 > 1) {
        if (this.calcForm.min_value * 1) {
          this.calcForm.min_value = e * 1 - 1
        }
      }
    },
    changeStep (e) {
      if (e * 1 > this.calcForm.max_value * 1 - this.calcForm.min_value * 1) {
        this.calcForm.step = 1
      }
    },
    async deleteMemory () {
      try {
        const res = await delMemory({
          id: this.delId
        })
        this.$message.success(res.data.msg)
        this.delVisible = false
        this.getMemoryList()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    // 提交内存
    async handlerMemory () {
      try {
        let { id, value, type, price, min_value, max_value, memory_unit } = this.calcForm
        price = price.reduce((all, cur) => {
          cur.price && (all[cur.id] = cur.price)
          return all
        }, {})
        const params = {
          id,
          product_id: this.id,
          type,
          value,
          price,
          min_value,
          max_value,
          memory_unit,
          step: 1
        }
        if (this.optType === 'add') {
          delete params.id
        }
        this.submitLoading = true
        const res = await createAndUpdateMemory(this.optType, params)
        this.$message.success(res.data.msg)
        this.getMemoryList()
        this.calcModel = false
        this.submitLoading = false
      } catch (error) {
        this.submitLoading = false
        this.$message.error(error.data.msg)
      }
    },
    // 提交存储
    async handlerStore () {
      try {
        let { id, value, type, price, min_value, max_value, step, other_config } = this.calcForm
        price = price.reduce((all, cur) => {
          cur.price && (all[cur.id] = cur.price)
          return all
        }, {})
        const params = {
          id,
          product_id: this.id,
          type,
          value,
          price,
          min_value,
          max_value,
          step: 1,
          other_config
        }
        if (this.optType === 'add') {
          delete params.id
        }
        this.submitLoading = true
        const res = await createAndUpdateStore(this.calcType, this.optType, params)
        this.$message.success(res.data.msg)
        this.getStoreList(this.calcType)
        this.calcModel = false
        this.submitLoading = false
      } catch (error) {
        this.submitLoading = false
        this.$message.error(error.data.msg)
      }
    },

    changeAdvance () {
      this.isAdvance = !this.isAdvance
    },
    /* 计算配置 end*/
    /* 周期相关 */
    closeData () {
      this.dataModel = false
    },
    async getDurationList () {
      try {
        this.loading = true
        const res = await getDuration({
          product_id: this.id,
          page: 1,
          limit: 100
        })
        this.cycleData = res.data.data.list
        this.loading = false
      } catch (error) {
        this.loading = false
      }
    },
    addCycle () {
      this.optType = 'add'
      this.comTitle = lang.add_cycle
      this.cycleForm.name = ''
      this.cycleForm.unit = 'month'
      this.cycleForm.num = ''
      this.cycleForm.price_factor = null
      this.cycleModel = true
    },
    editCycle (row) {
      this.optType = 'update'
      this.comTitle = lang.update + lang.cycle
      this.cycleForm = JSON.parse(JSON.stringify(row))
      this.cycleModel = true
    },
    async submitCycle ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.cycleForm))
          params.product_id = this.id
          if (this.optType === 'add') {
            delete params.id
          }
          if (!params.price_factor && params.price_factor !== 0) {
            params.price_factor = '1.00'
          }
          this.submitLoading = true
          const res = await createAndUpdateDuration(this.optType, params)
          this.$message.success(res.data.msg)
          this.getDurationList()
          this.cycleModel = false
          this.submitLoading = false
        } catch (error) {
          this.submitLoading = false
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },
    // 删除周期
    async deleteCycle () {
      try {
        const res = await delDuration({
          product_id: this.id,
          id: this.delId
        })
        this.$message.success(res.data.msg)
        this.delVisible = false
        this.getDurationList()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    /* 操作系统 */
    // 系统列表
    async getSystemList () {
      try {
        this.loading = true
        const params = JSON.parse(JSON.stringify(this.systemParams))
        params.product_id = this.id
        const res = await getImage(params)
        this.systemList = res.data.data.list
        this.loading = false
      } catch (error) {
        this.loading = false
      }
    },
    // 系统分类
    async getGroup () {
      try {
        const res = await getImageGroup({
          product_id: this.id,
          orderby: 'id',
          sort: 'desc'
        })
        this.systemGroup = res.data.data.list
      } catch (error) {

      }
    },
    createNewSys () { // 新增
      this.systemModel = true
      this.optType = 'add'
      this.comTitle = `${lang.add}${lang.system}`
      this.createSystem.image_group_id = ''
      this.createSystem.name = ''
      this.createSystem.charge = 0
      this.createSystem.price = ''
      this.createSystem.enable = 0
      this.createSystem.rel_image_id = ''
    },
    editSystem (row) {
      this.optType = 'update'
      this.comTitle = lang.update + lang.system
      this.createSystem = { ...row }
      this.systemModel = true
    },
    async submitSystem ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.createSystem))
          params.product_id = this.id
          if (this.optType === 'add') {
            delete params.id
          }
          this.submitLoading = true
          const res = await createAndUpdateImage(this.optType, params)
          this.$message.success(res.data.msg)
          this.getSystemList()
          this.systemModel = false
          this.submitLoading = false
        } catch (error) {
          this.submitLoading = false
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },
    // 列表修改状态
    async changeSystemStatus (row) {
      try {
        const params = JSON.parse(JSON.stringify(row))
        params.product_id = this.id
        const res = await createAndUpdateImage('update', params)
        this.$message.success(res.data.msg)
        this.getSystemList()
      } catch (error) {

      }
    },
    // 拉取系统
    async refeshImageHandler () {
      try {
        this.$message.success(lang.mf_tip)
        await refreshImage({
          product_id: this.id
        })
        this.getSystemList()
        this.getGroup()
      } catch (error) {

      }
    },
    // 分类管理
    classManage () {
      this.classModel = true
      this.classParams.name = ''
      this.classParams.icon = ''
      this.optType = 'add'
    },
    async submitSystemGroup ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.classParams))
          if (this.optType === 'add') {
            delete params.id
            params.product_id = this.id
          }
          this.submitLoading = true
          const res = await createAndUpdateImageGroup(this.optType, params)
          this.$message.success(res.data.msg)
          this.getGroup()
          this.submitLoading = false
          this.classParams.name = ''
          this.classParams.icon = ''
          this.$refs.classForm.reset()
          this.optType = 'add'
        } catch (error) {
          this.submitLoading = false
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },
    editGroup (row) {
      this.optType = 'update'
      this.classParams = JSON.parse(JSON.stringify(row))
    },
    async deleteGroup () {
      try {
        const res = await delImageGroup({
          id: this.delId
        })
        this.$message.success(res.data.msg)
        this.delVisible = false
        this.getGroup()
        this.classParams.name = ''
        this.classParams.icon = ''
        this.$refs.classForm.reset()
        this.optType = 'add'
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    async deleteSystem () {
      try {
        const res = await delImage({
          id: this.delId
        })
        this.$message.success(res.data.msg)
        this.delVisible = false
        this.getSystemList()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    /* 其他设置 */
    async getOtherConfig () {
      try {
        const res = await getCloudConfig({
          product_id: this.id
        })
        const temp = res.data.data
        temp.support_normal_network = Boolean(temp.support_normal_network)
        temp.support_vpc_network = Boolean(temp.support_vpc_network)
        // 处理快照备份的数据
        this.backList = temp.backup_data.map(item => {
          item.status = false
          item.price = item.price * 1
          return item
        })
        if (temp.backup_data.length === 0) {
          this.otherForm.backup_enable = 0
        }
        // 处理快照数据
        this.snapList = temp.snap_data.map(item => {
          item.status = false
          item.price = item.price * 1
          return item
        })
        if (temp.snap_data.length === 0) {
          this.otherForm.snap_enable = 0
        }
        // 处理资源包数据
        this.resourceList = temp.resource_package.map(item => {
          item.status = false
          return item
        })

        // 默认允许公网IP
        temp.support_public_ip = 1
        this.otherForm = temp
        this.store_limit = temp.disk_limit_enable * 1
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    changeLenth (e) {
      if (e - this.otherForm.host_prefix.length > 25) {
        this.otherForm.host_length = 25 - this.otherForm.host_prefix.length
      } else if (e * 1 + this.otherForm.host_prefix.length * 1 < 6) {
        this.otherForm.host_length = 6 - this.otherForm.host_prefix.length
      }
    },
    addGroup (type) {
      const temp = {
        num: 1,
        type: type,
        price: 0.00,
        status: true // 编辑状态
      }
      this.backAllStatus = true
      if (type === 'backup') {
        this.backList.push(temp)
      } else if (type === 'snap') {
        this.snapList.push(temp)
      } else if (type === 'resource') {
        this.resourceList.push({
          rid: '',
          name: '',
          status: true
        })
      }
    },
    // 添加资源包
    addResourece () {
      this.resourceList.push({
        rid: '',
        name: '',
        status: true // 编辑状态
      })
    },
    openEdit (type, index) {
      this.backAllStatus = true
      if (type === 'backup') {
        this.backList[index].status = true
      } else if (type === 'snap') {
        this.snapList[index].status = true
      } else if (type === 'resource') {
        this.resourceList[index].status = true
      }
    },
    closeEdit (row, index, type) {
      if (row.id) { // 取消已有数据的编辑
        if (type === 'backup') {
          this.backList[index].status = false
        } else if (type === 'snap') {
          this.snapList[index].status = false
        } else if (type === 'resource') {
          this.resourceList[index].status = false
        }
      } else { // 新增未加入数据库的
        if (type === 'backup') {
          this.backList.splice(index, 1)
        } else if (type === 'snap') {
          this.snapList.splice(index, 1)
        } else if (type === 'resource') {
          this.resourceList.splice(index, 1)
        }
      }
      this.backAllStatus = false
    },

    // 删除 备份/快照
    deleteBackup (type, index) {
      if (type === 'backup') {
        this.backList.splice(index, 1)
      } else if (type === 'snap') {
        this.snapList.splice(index, 1)
      } else if (type === 'resource') {
        this.resourceList.splice(index, 1)
      }
    },
    async submitConfig ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.otherForm))
          params.product_id = this.id
          params.backup_data = this.backList
          params.snap_data = this.snapList
          params.resource_package = this.resourceList
          params.support_normal_network = params.support_normal_network ? 1 : 0
          params.support_vpc_network = params.support_vpc_network ? 1 : 0
          if (!params.support_normal_network && !params.support_vpc_network) {
            return this.$message.warning(`${lang.select}${lang.net_type}`)
          }
          this.submitLoading = true
          const res = await saveCloudConfig(params)
          this.$message.success(res.data.msg)
          this.submitLoading = false
          this.dataModel = false
          this.getOtherConfig()
        } catch (error) {
          this.submitLoading = false
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },
    /* 通用删除按钮 */
    comDel (type, row, index) {
      this.delId = row.id
      if (type === 'cycle') {
        this.delTit = lang.sure_del_cycle
      }
      this.delTit = lang.sureDelete
      this.delType = type
      // 新增的时候，本地删除线路子项
      if ((this.lineType === 'add') &&
        (this.subType === 'line_bw' || this.subType === 'line_flow' || this.subType === 'line_defence' || this.subType === 'line_ip')
      ) {
        this.delSubIndex = index
        this.delSubItem()
        return
      }
      this.delVisible = true
    },
    // 通用删除
    sureDelete () {
      switch (this.delType) {
        case 'cycle':
          return this.deleteCycle()
        case 'c_cpu':
          return this.deleteCpu()
        case 'memory':
          return this.deleteMemory()
        case 'system': // 删除镜像
          return this.deleteSystem()
        case 'group': // 删除镜像分类
          return this.deleteGroup()
        case 'system_disk':
          return this.deleteStore('system_disk')
        case 'data_disk':
          return this.deleteStore('data_disk')
        case 'system_disk_limit':
          return this.deleteStoreLimit('system_disk_limit')
        case 'data_disk_limit':
          return this.deleteStoreLimit('data_disk_limit')
        case 'data':
          return this.deleteData()
        case 'c_line':
          return this.deleteLine()
        case 'line_bw':
        case 'line_flow':
        case 'line_defence':
        case 'line_ip':
          return this.delSubItem()
        case 'recommend':
          return this.delRecommend()
        case 'cpu':
        case 'data_center':
        case 'line':
          return this.delLimit()
        default:
          return null
      }
    },
    formatPrice (val) {
      return (val * 1).toFixed(2)
    },
  },
  created () {
    this.id = location.href.split('?')[1].split('=')[1]
    this.iconSelecet = this.iconList.reduce((all, cur) => {
      all.push({
        value: cur,
        label: `${this.host}/plugins/server/mf_cloud/template/admin/img/${cur}.svg`
      })
      return all
    }, []);
    this.diskColumns = JSON.parse(JSON.stringify(this.systemDiskColumns))
    this.diskColumns[0].title = `${lang.data_disk}（GB）`
    const temp = JSON.parse(JSON.stringify(this.cpu_columns))
    this.data_center_columns = [{
      colKey: 'fir',
      title: lang.data_center,
    }].concat(temp)
    this.line_columns = [{
      colKey: 'fir',
      title: lang.bw,
    }].concat(temp)
    // 默认拉取数据
    this.getDurationList()
    this.getMemoryList()
  },
}).$mount(template)
