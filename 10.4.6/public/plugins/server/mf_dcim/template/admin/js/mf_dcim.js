const template = document.getElementsByClassName("common_config")[0];
Vue.prototype.lang = Object.assign(window.lang, window.module_lang);
new Vue({
  components: {
    comConfig,
  },
  data () {
    return {
      host: location.origin,
      id: "",
      tabs: "duration", // duration,data_center,model,hardware,flexible,limit,system,other
      hover: true,
      tableLayout: false,
      delVisible: false,
      loading: false,
      currency_prefix:
        JSON.parse(localStorage.getItem("common_set")).currency_prefix || "¥",
      currency_suffix:
        JSON.parse(localStorage.getItem("common_set")).currency_suffix || "",
      optType: "add", // 新增/编辑
      comTitle: "",
      delTit: "",
      delType: "",
      delId: "",
      submitLoading: false,
      ipChange: false,
      /* 周期 */
      cycleData: [],
      dataModel: false,
      cycleModel: false,
      cycleForm: {
        product_id: "",
        name: "",
        num: "",
        unit: "month",
        price_factor: null,
        price: null,
      },
      ratioModel: false,
      ratioData: [],
      ratioColumns: [
        {
          colKey: "name",
          title: lang.cycle_name,
          ellipsis: true,
        },
        {
          colKey: "unit",
          title: lang.cycle_time,
          ellipsis: true,
        },
        {
          colKey: "ratio",
          title: lang.mf_ratio,
          ellipsis: true,
        },
      ],
      cycleTime: [
        {
          value: "hour",
          label: lang.hour,
        },
        {
          value: "day",
          label: lang.day,
        },
        {
          value: "month",
          label: lang.natural_month,
        },
      ],
      cycleColumns: [
        {
          colKey: "name",
          title: lang.cycle_name,
          ellipsis: true,
        },
        {
          colKey: "unit",
          title: lang.cycle_time,
          ellipsis: true,
          ellipsis: true,
        },
        {
          colKey: "price_factor",
          title: lang.price_factor,
          ellipsis: true,
        },
        {
          colKey: "price",
          title: lang.cycle_price,
          ellipsis: true,
        },
        {
          colKey: "ratio",
          title: lang.cycle_ratio,
          ellipsis: true,
        },
        {
          colKey: "op",
          title: lang.operation,
          width: 120,
        },
      ],
      cycleRules: {
        name: [
          {
            required: true,
            message: lang.input + lang.cycle_name,
            type: "error",
          },
          {
            validator: (val) => val?.length <= 10,
            message: lang.verify8 + "1-10",
            type: "warning",
          },
        ],
        num: [
          {
            required: true,
            message: lang.input + lang.cycle_time,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + lang.verify16,
            type: "warning",
          },
          {
            validator: (val) => val > 0 && val <= 999,
            message: lang.cycle_time + "1-999",
            type: "warning",
          },
        ],
        // 系统相关
        image_group_id: [
          {
            required: true,
            message: lang.select + lang.system_classify,
            type: "error",
          },
        ],
        rel_image_id: [
          {
            required: true,
            message: lang.input + lang.opt_system + "ID",
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + lang.verify16,
            type: "warning",
          },
        ],
        price: [
          { required: true, message: lang.input + lang.price, type: "error" },
          {
            pattern: /^\d+(\.\d{0,2})?$/,
            message: lang.verify12,
            type: "warning",
          },
          {
            validator: (val) => val >= 0,
            message: lang.verify12,
            type: "warning",
          },
        ],
        icon: [
          {
            required: true,
            message: lang.select + lang.mf_icon,
            type: "error",
            trigger: "change",
          },
        ],
      },
      /* 操作系统 */
      systemGroup: [],
      systemList: [],
      selectedRowKeys: [],
      batchDelete: false,
      systemParams: {
        product_id: "",
        page: 1,
        limit: 1000,
        image_group_id: "",
        keywords: "",
      },
      systemModel: false,
      createSystem: {
        // 添加操作系统表单
        image_group_id: "",
        name: "",
        charge: 0,
        price: "",
        enable: 0,
        rel_image_id: "",
      },
      systemColumns: [
        // 套餐表格
        {
          colKey: "row-select",
          type: "multiple",
          width: 30,
        },
        {
          colKey: "id",
          title: lang.order_index,
          width: 100,
        },
        {
          colKey: "image_group_name",
          title: lang.system_classify,
          width: 200,
          ellipsis: true,
        },
        {
          colKey: "name",
          title: lang.system_name,
          ellipsis: true,
        },
        {
          colKey: "charge",
          title: lang.mf_charge,
          width: 200,
        },
        {
          colKey: "price",
          title: lang.price,
        },
        {
          colKey: "enable",
          title: lang.mf_enable,
          width: 200,
        },
        {
          colKey: "op",
          title: lang.operation,
          width: 120,
        },
      ],
      groupColumns: [
        // 套餐表格
        {
          // 列拖拽排序必要参数
          colKey: "drag",
          width: 20,
          className: "drag-icon",
        },
        {
          colKey: "image_group_name",
          title: lang.system_classify,
          ellipsis: true,
        },
        {
          colKey: "op",
          title: lang.operation,
          width: 120,
        },
      ],
      // 操作系统图标
      iconList: [
        "Windows",
        "CentOS",
        "Ubuntu",
        "Debian",
        "ESXi",
        "XenServer",
        "FreeBSD",
        "Fedora",
        "其他",
        "ArchLinux",
        "Rocky",
        "OpenEuler",
        "AlmaLinux",
      ],
      iconSelecet: [],
      classModel: false,
      classParams: {
        id: "",
        name: "",
        icon: "",
      },
      popupProps: {
        overlayClassName: `custom-select`,
        overlayInnerStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` }),
      },
      /* 其他设置 */
      otherForm: {
        product_id: "",
        optional_host_auto_create: 0,
        manual_resource: 0,
        rand_ssh_port: 0,
        reset_password_sms_verify: 0,
        reinstall_sms_verify: 0,
        level_discount_memory_order: 0,
        level_discount_memory_upgrade: 0,
        level_discount_disk_order: 0,
        level_discount_disk_upgrade: 0,
        level_discount_bw_upgrade: 0,
        level_discount_ip_num_upgrade: 0,
        level_discount_gpu_order: 0,
        level_discount_gpu_upgrade: 0,
      },
      rulesList: [
        // 平衡规则
        { value: 1, label: lang.mf_rule1 },
        { value: 2, label: lang.mf_rule2 },
        { value: 3, label: lang.mf_rule3 },
      ],
      dataRules: {
        data_center_id: [
          {
            required: true,
            message: `${lang.select}${lang.area}`,
            type: "error",
          },
        ],
        line_id: [
          {
            required: true,
            message: `${lang.select}${lang.line_name}`,
            type: "error",
          },
        ],
        flow: [
          { required: true, message: `${lang.input}CPU`, type: "error" },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "0-999999" + lang.verify2,
            type: "warning",
          },
          {
            validator: (val) => val >= 0 && val <= 999999,
            message: lang.input + "0-999999" + lang.verify2,
            type: "warning",
          },
        ],
        host_prefix: [
          {
            required: true,
            message: `${lang.input}${lang.host_prefix}`,
            type: "error",
          },
          {
            pattern: /^[A-Z][a-zA-Z0-9_.]{0,9}$/,
            message: lang.verify8 + "1-10",
            type: "warning",
          },
        ],
        host_length: [
          {
            required: true,
            message: `${lang.input}${lang.mf_tip2}`,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.mf_tip2,
            type: "warning",
          },
        ],
        country_id: [
          {
            required: true,
            message: lang.select + lang.country,
            type: "error",
          },
        ],
        city: [
          { required: true, message: lang.input + lang.city, type: "error" },
        ],
        cloud_config: [
          { required: true, message: lang.select + lang.city, type: "error" },
        ],
        cloud_config_id: [
          { required: true, message: lang.input + "ID", type: "error" },
        ],
        area: [
          {
            required: true,
            message: `${lang.input}${lang.area}${lang.nickname}`,
            type: "error",
          },
        ],
        name: [
          {
            required: true,
            message: `${lang.input}${lang.box_label23}`,
            type: "error",
          },
        ],
        description: [
          {
            required: true,
            message: `${lang.input}${lang.description}`,
            type: "error",
          },
        ],
        order: [
          {
            required: true,
            message: `${lang.input}${lang.sort}ID`,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.verify7,
            type: "warning",
          },
          {
            validator: (val) => val >= 0,
            message: lang.verify7,
            type: "warning",
          },
        ],
        network_type: [
          {
            required: true,
            message: lang.select + lang.net_type,
            type: "error",
          },
        ],
        bw: [
          { required: true, message: `${lang.input}${lang.bw}`, type: "error" },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "1-30000" + lang.verify2,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 30000,
            message: lang.input + "1-30000" + lang.verify2,
            type: "warning",
          },
        ],
        peak_defence: [
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "1-999999" + lang.verify2,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 999999,
            message: lang.input + "1-999999" + lang.verify2,
            type: "warning",
          },
        ],
        min_memory: [
          {
            required: true,
            message: `${lang.input}${lang.memory}`,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "1-512" + lang.verify2,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 512,
            message: lang.input + "1-512" + lang.verify2,
            type: "warning",
          },
        ],
        max_memory: [
          {
            required: true,
            message: `${lang.input}${lang.memory}`,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "1-512" + lang.verify2,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 512,
            message: lang.input + "1-512" + lang.verify2,
            type: "warning",
          },
        ],
        line_id: [
          {
            required: true,
            message: `${lang.select}${lang.bw_line}`,
            type: "error",
          },
        ],
        min_bw: [
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "1-30000" + lang.verify2,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 30000,
            message: lang.input + "1-30000" + lang.verify2,
            type: "warning",
          },
        ],
        max_bw: [
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "1-30000" + lang.verify2,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 30000,
            message: lang.input + "1-30000" + lang.verify2,
            type: "warning",
          },
        ],
        model_config_id: [
          {
            required: true,
            message: `${lang.select}${lang.box_title46}`,
            type: "error",
          },
        ],
      },
      backupColumns: [
        // 备份表格
        {
          colKey: "id",
          title: lang.order_index,
          width: 160,
        },
        {
          colKey: "num",
          title: lang.allow_back_num,
          ellipsis: true,
          width: 180,
        },
        {
          colKey: "price",
          title: lang.min_cycle_price,
          className: "back-price",
        },
      ],
      snappColumns: [
        // 快照表格
        {
          colKey: "id",
          title: lang.order_index,
          width: 160,
        },
        {
          colKey: "num",
          title: lang.allow_back_num,
          width: 180,
          ellipsis: true,
        },
        {
          colKey: "price",
          title: lang.min_cycle_price,
        },
      ],
      backList: [],
      snapList: [],
      backLoading: false,
      snapLoading: false,
      backAllStatus: false,
      /* 计算配置 */
      modelList: [],
      modelLoading: false,
      memoryList: [],
      memoryLoading: false,
      memoryType: "", // 内存方式
      modelColumns: [
        // model表格 order_text68
        {
          colKey: "drag",
          width: 20,
          className: "drag-icon",
        },
        {
          colKey: "id",
          title: lang.order_text68,
          width: 100,
        },
        {
          colKey: "name",
          title: lang.config_name,
          width: 200,
          ellipsis: true,
        },
        // {
        //   colKey: "group_id",
        //   title: `${lang.sale_group}ID`,
        //   width: 200,
        // },
        {
          colKey: "cpu",
          title: lang.mf_cpu,
          width: 200,
          ellipsis: true,
        },
        {
          colKey: "gpu",
          title: lang.mf_gpu,
          width: 150,
          ellipsis: true,
        },
        {
          colKey: "memory",
          title: lang.memory,
          width: 200,
          ellipsis: true,
        },
        {
          colKey: "disk",
          title: lang.disk,
          width: 200,
          ellipsis: true,
        },
        {
          colKey: "support_optional",
          title: lang.allow_optional,
          width: 100,
        },
        {
          colKey: "hidden",
          title: lang.mf_tip40,
          ellipsis: true,
          width: 150,
        },
        {
          colKey: "op",
          title: lang.operation,
          width: 120,
        },
      ],
      memoryColumns: [
        // memory表格
        {
          colKey: "value",
          title: `${lang.memory}（GB）`,
          width: 300,
        },
        {
          colKey: "price",
          title: lang.price,
        },
        {
          colKey: "op",
          title: lang.operation,
          width: 120,
        },
      ],
      calcType: "", // cpu, memory
      calcForm: {
        // model
        name: "",
        group_id: "",
        cpu: "",
        cpu_param: "",
        memory: "",
        disk: "",
        // 增值选配
        support_optional: 0,
        optional_only_for_upgrade: 0,
        optional_memory_id: [],
        leave_memory: undefined,
        max_memory_num: undefined,
        optional_disk_id: [],
        max_disk_num: undefined,
        gpu: "",
        optional_gpu_id: [],
        max_gpu_num: undefined,
        price: [],
      },
      calcModel: false,
      configType: [
        { value: "radio", label: lang.mf_radio },
        { value: "step", label: lang.mf_step },
        { value: "total", label: lang.mf_total },
      ],
      calcRules: {
        // 模型配置验证
        name: [
          {
            required: true,
            message: `${lang.input}${lang.config_name}`,
            type: "error",
          },
        ],
        group_id: [
          {
            required: true,
            message: `${lang.input}${lang.sale_group}ID`,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + lang.verify7,
            type: "warning",
          },
        ],
        cpu: [
          {
            required: true,
            message: `${lang.input}${lang.mf_cpu}`,
            type: "error",
          },
        ],
        cpu_param: [
          {
            required: true,
            message: `${lang.input}${lang.mf_cpu_param}`,
            type: "error",
          },
        ],
        memory: [
          {
            required: true,
            message: `${lang.input}${lang.memory}`,
            type: "error",
          },
        ],
        disk: [
          {
            required: true,
            message: `${lang.input}${lang.disk}`,
            type: "error",
          },
        ],
        value: [
          { required: true, message: `${lang.input}${lang.bw}`, type: "error" },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "0-30000" + lang.verify2,
            type: "warning",
          },
          {
            validator: (val) => val >= 0 && val <= 30000,
            message: lang.input + "0-30000" + lang.verify2,
            type: "warning",
          },
        ],
        type: [
          {
            required: true,
            message: `${lang.select}${lang.config}${lang.mf_way}`,
            type: "error",
          },
        ],
        price: [
          {
            pattern: /^\d+(\.\d{0,2})?$/,
            message: lang.input + lang.money,
            type: "warning",
          },
          {
            validator: (val) => val >= 0 && val <= 999999,
            message: lang.verify12,
            type: "warning",
          },
        ],
        min_value: [
          {
            required: true,
            message: `${lang.input}${lang.min_value}`,
            type: "error",
          },
          {
            pattern: /^([1-9][0-9]*)$/,
            message: lang.input + "1~1048576" + lang.verify2,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 1048576,
            message: lang.input + "1~1048576" + lang.verify2,
            type: "warning",
          },
        ],
        max_value: [
          {
            required: true,
            message: `${lang.input}${lang.max_value}`,
            type: "error",
          },
          {
            pattern: /^([1-9][0-9]*)$/,
            message: lang.input + "2~1048576" + lang.verify2,
            type: "warning",
          },
          {
            validator: (val) => val >= 2 && val <= 1048576,
            message: lang.input + "2~1048576" + lang.verify2,
            type: "warning",
          },
        ],
        step: [
          {
            required: true,
            message: `${lang.input}${lang.min_step}`,
            type: "error",
          },
          {
            pattern: /^([1-9][0-9]*)$/,
            message: lang.input + lang.verify16,
            type: "warning",
          },
        ],
        traffic_type: [
          {
            required: true,
            message: `${lang.select}${lang.traffic_type}`,
            type: "error",
          },
        ],
        bill_cycle: [
          {
            required: true,
            message: `${lang.select}${lang.billing_cycle}`,
            type: "error",
          },
        ],
      },
      isAdvance: false, // 是否展开高级配置
      /* 数据中心 */
      dataList: [],
      dataLoading: false,
      dataColumns: [
        {
          colKey: "order",
          title: lang.index_text8,
          width: 100,
          ellipsis: true,
        },
        {
          colKey: "country_name",
          title: lang.country,
          width: 150,
          ellipsis: true,
          className: "country-td",
        },
        {
          colKey: "city",
          title: lang.city,
          width: 150,
          ellipsis: true,
          className: "city-td",
        },
        {
          colKey: "area",
          title: `${lang.area}${lang.nickname}`,
          width: 150,
          ellipsis: true,
          className: "area-td",
        },
        {
          colKey: "line",
          title: lang.line_name,
          className: "line-td",
          width: 250,
          ellipsis: true,
        },
        {
          colKey: "price",
          title: lang.price,
          className: "line-td",
          ellipsis: true,
        },
        {
          colKey: "op",
          title: lang.operation,
          width: 120,
          className: "line-td",
        },
      ],
      dataForm: {
        // 新建数据中心
        country_id: "",
        city: "",
        area: "",
        order: 0,
      },
      countryList: [],
      // 配置选项
      dataConfig: [
        { value: "node", lable: lang.node + "ID" },
        { value: "area", lable: lang.area + "ID" },
        { value: "node_group", lable: lang.node_group + "ID" },
      ],
      /* 线路相关 */
      lineType: "", // 新增,编辑线路，新增的时候本地操作，保存一次性提交
      subType: "", // 线路子项类型， line_bw, line_flow, line_defence, line_ip
      lineForm: {
        country_id: "", // 线路国家
        city: "", // 线路城市
        data_center_id: "",
        name: "",
        bill_type: "", // bw, flow
        bw_ip_group: "",
        defence_ip_group: "",
        defence_enable: 0, // 防护开关
        bw_data: [], // 带宽
        flow_data: [], //流量
        defence_data: [], // 防护
        ip_data: [], // ip
        order: 0,
        /* 推荐配置 */
        line_id: "",
        flow: "",
        description: "",
        cpu: "",
        memory: "",
        system_disk_size: "",
        system_disk_type: "",
        data_disk_size: "",
        data_disk_type: "",
        network_type: "",
        bw: "",
        peak_defence: "",
        /* 配置限制 */
        line_id: "",
        model_config_id: [],
        min_bw: "",
        max_bw: "",
        min_memory: "",
        max_memory: "",
        min_flow: "",
        max_flow: "",
      },
      bw_ip_show: false, // bw 高级配置
      defence_ip_show: false, // 防护高级配置
      subForm: {
        // 线路子项表单
        type: "",
        value: "",
        price: [],
        min_value: "",
        value_show: "",
        max_value: "",
        step: 1,
        other_config: {
          in_bw: "",
          out_bw: "",
          bill_cycle: "",
        },
      },
      lineModel: false,
      lineRight: false,
      delSubIndex: 0,
      subId: "",
      countrySelect: [], // 国家三级联动
      billType: [
        { value: "bw", label: lang.mf_bw },
        { value: "flow", label: lang.mf_flow },
      ],
      bwColumns: [
        {
          colKey: "fir",
          title: lang.bw,
        },
        {
          colKey: "price",
          title: lang.price,
        },
        {
          colKey: "op",
          title: lang.operation,
          width: 120,
        },
      ],
      trafficTypes: [
        { value: "1", label: lang.in },
        { value: "2", label: lang.out },
        { value: "3", label: lang.in_out },
      ],
      billingCycle: [
        { value: "month", label: lang.natural_month },
        { value: "last_30days", label: lang.last_30days },
      ],
      /* 配置限制 */
      ruleLimit: [], // 条件
      resultLimit: [], // 结果
      limitColumns: [
        {
          colKey: "rule",
          title: lang.mf_fill_condition,
          ellipsis: true,
        },
        {
          colKey: "result",
          title: lang.mf_result,
          ellipsis: true,
        },
        {
          colKey: "op",
          title: lang.operation,
          width: 120,
        },
      ],
      limitTypeObj: {
        data_center: {
          type: "data_center",
          name: lang.data_center,
          config: { id: [] },
          checked: false,
        },
        model_config: {
          type: "model_config",
          name: lang.mf_model,
          config: { id: [] },
          checked: false,
        },
        // ipv4_num: {
        //   type: "ipv4_num",
        //   name: lang.mf_public_ip,
        //   config: { min: "", max: "" },
        //   checked: false,
        // },
        bw: {
          type: "bw",
          name: lang.bw,
          config: { min: "", max: "" },
          checked: false,
        },
        flow: {
          type: "flow",
          name: lang.cloud_flow,
          config: { min: "", max: "" },
          checked: false,
        },
        image: {
          type: "image",
          name: lang.opt_system,
          config: { id: [] },
          checked: false,
        },
        // duration: {
        //   type: "duration",
        //   name: lang.cycle,
        //   config: { id: [] },
        //   checked: false,
        // },
      },
      limitData: [],
      limitLoading: false,
      limitType: "",
      limitModel: false,
      originLimitForm: {
        rule: {
          data_center: { opt: "eq", id: [] },
          model_config: { opt: "eq", id: [] },
          // ipv4_num: { min: null, max: null },
          bw: { opt: "eq", min: null, max: null },
          flow: { opt: "eq", min: null, max: null },
          image: { opt: "eq", id: [] },
          // duration: { id: [] },
        },
        result: {
          data_center: { opt: "eq", id: [] },
          model_config: { opt: "eq", id: [] },
          bw: { opt: "eq", min: null, max: null },
          flow: { opt: "eq", min: null, max: null },
          image: { opt: "eq", id: [] },
        },
      },
      limitForm: {},
      limitRules: {
        'rule.data_center.id': [
          { required: true, message: `${lang.select}${lang.data_center}`, type: "error" }
        ],
        'rule.image.id': [
          { required: true, message: `${lang.select}${lang.opt_system}`, type: "error" }
        ],
        'rule.model_config.id': [
          { required: true, message: `${lang.select}${lang.mf_model}`, type: "error" }
        ],
        'result.image.id': [
          { required: true, message: `${lang.select}${lang.opt_system}`, type: "error" }
        ],
        'result.model_config.id': [
          { required: true, message: `${lang.select}${lang.mf_model}`, type: "error" }
        ],
      },
      limitType: "",
      limitModel: false,
      limitMemoryType: "", // 配置限制里面内存的方式
      bwValidator: "",
      /* 硬件配置 */
      hardwareArr: [
        { name: "cpu", label: `${lang.mf_cpu}` },
        { name: "gpu", label: `${lang.mf_gpu}` },
        { name: "memory", label: `${lang.memory}` },
        { name: "disk", label: `${lang.disk}` },
      ],
      cpuList: [],
      gpuList: [],
      memoryList: [],
      diskList: [],
      cpuLoading: false,
      gpuLoading: false,
      memoryLoading: false,
      diskLoading: false,
      cpu_columns: [
        {
          colKey: "value",
          title: lang.box_title46,
          ellipsis: true,
        },
        {
          colKey: "price",
          title: lang.price,
          width: 200,
          ellipsis: true,
        },
        {
          colKey: "op",
          title: lang.operation,
          width: 120,
        },
      ],
      gpu_columns: [],
      memory_columns: [],
      disk_columns: [],
      hardwareForm: {
        value: "",
        order: null,
        other_config: {
          memory_slot: null,
          memory: null,
        },
        price: [],
      },
      hardDialog: false,
      hardMode: "", // cpu memory disk
      hardRules: {
        name: [
          {
            required: true,
            message: `${lang.input}${lang.model_name}`,
            type: "error",
          },
        ],
        group_id: [
          {
            required: true,
            message: `${lang.input}${lang.sale_group}ID`,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + lang.verify7,
            type: "warning",
          },
        ],
        cpu_option_id: [
          {
            required: true,
            message: `${lang.select}${lang.mf_cpu}`,
            type: "error",
          },
        ],
        cpu_num: [
          {
            required: true,
            message: `${lang.input}${lang.auth_num}`,
            type: "error",
          },
        ],
        disk_option_id: [
          {
            required: true,
            message: `${lang.select}${lang.disk}`,
            type: "error",
          },
        ],
        disk_num: [
          {
            required: true,
            message: `${lang.input}${lang.auth_num}`,
            type: "error",
          },
        ],
        mem_option_id: [
          {
            required: true,
            message: `${lang.select}${lang.memory}`,
            type: "error",
          },
        ],
        mem_num: [
          {
            required: true,
            message: `${lang.input}${lang.auth_num}`,
            type: "error",
          },
        ],
        ip_num: [
          {
            required: true,
            message: `${lang.input}IP${lang.auth_num}`,
            type: "error",
          },
        ],
        bw: [
          {
            required: true,
            message: `${lang.input}${lang.bw}`,
            type: "error",
          },
        ],
        "other_config.memory_slot": [
          {
            required: true,
            message: `${lang.input}${lang.memory_slot_num}`,
            type: "error",
          },
        ],
        "other_config.memory": [
          {
            required: true,
            message: `${lang.input}${lang.memory}${lang.capacity}`,
            type: "error",
          },
        ],
        order: [
          {
            required: true,
            message: `${lang.input}${lang.sort}`,
            type: "error",
          },
        ],
        price: [
          {
            pattern: /^\d+(\.\d{0,2})?$/,
            message: lang.input + lang.money,
            type: "warning",
          },
          {
            validator: (val) => val >= 0 && val <= 999999,
            message: lang.verify12,
            type: "warning",
          },
        ],
      },
      /* 灵活机型 */
      packageList: [],
      packageLoading: [],
      flexList: [],
      flexLoading: false,
      flexColumns: [
        // model表格 order_text68
        {
          colKey: "order",
          title: lang.order_text68,
          width: 100,
        },
        {
          colKey: "name",
          title: lang.model_name,
          width: 200,
          ellipsis: true,
          className: "model-name",
        },
        {
          colKey: "cpu",
          title: lang.mf_cpu,
          width: 300,
          ellipsis: true,
        },
        {
          colKey: "memory",
          title: lang.memory,
          width: 200,
          ellipsis: true,
        },
        {
          colKey: "disk",
          title: lang.disk,
          width: 200,
          ellipsis: true,
        },
        {
          colKey: "bw",
          title: lang.bw,
          width: 100,
          ellipsis: true,
        },
        {
          colKey: "ip_num",
          title: `IP${lang.auth_num}`,
          width: 100,
          ellipsis: true,
        },
        {
          colKey: "hidden",
          title: lang.mf_tip40,
          ellipsis: true,
          width: 150,
        },
        {
          colKey: "op",
          title: lang.operation,
          width: 120,
        },
      ],
      flexModel: false,
      flexForm: {
        name: "",
        order: 0,
        group_id: "",
        cpu_option_id: "",
        cpu_num: undefined,
        mem_option_id: "",
        mem_num: undefined,
        disk_option_id: "",
        disk_num: undefined,
        bw: undefined,
        ip_num: undefined,
        description: "",
        optional_memory_id: [],
        mem_max: undefined,
        mem_max_num: undefined,
        optional_disk_id: [],
        disk_max_num: undefined,
        price: [],
      },
      hasMultiLanguage: false,
      isEn: localStorage.getItem("backLang") === "en-us" ? true : false
    };
  },
  watch: {
    store_limit: {
      immediate: true,
      handler (val) {
        if (val * 1) {
          this.getStoreLimitList("system_disk_limit");
          this.getStoreLimitList("data_disk_limit");
        }
      },
    },
  },
  computed: {
    // 处理级联数据
    calcCascadeImage () {
      const temp = this.systemGroup.reduce((all, cur) => {
        all.push({
          id: `f-${cur.id}`,
          name: cur.name,
          children: this.systemList.filter(item => item.image_group_id === cur.id)
        });
        return all;
      }, []).filter(item => item.children.length > 0);
      return temp;
    },
    calcDisabeld () {
      return key => {
        if ((key === "bw" && this.checkedLimit.includes("flow")) ||
          (key === "flow" && this.checkedLimit.includes("bw"))) {
          return true;
        } else {
          return false;
        }
      };
    },
    calcLimitRules () {
      return rule => {
        return Object.entries(rule);
      };
    },
    calcLimitName () {
      return type => {
        return this.limitTypeObj[type].name;
      };
    },
    disabeldCheck () {
      return (type, name) => {
        if (this.ruleLimit.includes('bw')) {
          if (name === 'bw' || name === 'flow') {
            if (type === 'rule' && name === 'bw') {
              return false
            } else {
              return true;
            }
          }
        }
        if (this.ruleLimit.includes('flow')) {
          if (name === 'bw' || name === 'flow') {
            if (type === 'rule' && name === 'flow') {
              return false
            } else {
              return true;
            }
          }
        }
        if (this.resultLimit.includes('bw')) {
          if (name === 'bw' || name === 'flow') {
            if (type === 'result' && name === 'bw') {
              return false
            } else {
              return true;
            }
          }
        }
        if (this.resultLimit.includes('flow')) {
          if (name === 'bw' || name === 'flow') {
            if (type === 'result' && name === 'flow') {
              return false
            } else {
              return true;
            }
          }
        }
        return this[`${type === 'rule' ? 'result' : 'rule'}Limit`].includes(name);
      };
    },
    showLimitItem () {
      return (type, name) => {
        return this[`${type}Limit`].includes(name);
      };
    },
    calcCheckbox () {
      return Object.values(this.limitTypeObj);
    },
    calcResultCheckbox () {
      const temp = JSON.parse(JSON.stringify(this.limitTypeObj));
      delete temp.data_center;
      return Object.values(temp);
    },
    isShowFill () {
      return (price) => {
        const index = price.findIndex((item) => item.price);
        return index === -1;
      };
    },
    calcName () {
      return (type) => {
        switch (type) {
          case "memory":
            return `${lang.memory_config}`;
          case "system_disk":
            return `${lang.system_disk_size}${lang.capacity}`;
          case "data_disk":
            return `${lang.data_disk}${lang.capacity}`;
          case "line_bw":
            return `${lang.bw}（Mbps）`;
        }
      };
    },
    calcIcon () {
      return (
        this.host +
        "/upload/common/country/" +
        this.countryList.filter(
          (item) => item.id === this.dataForm.country_id
        )[0]?.iso +
        ".png"
      );
    },
    calcIcon1 () {
      if (!this.countrySelect) {
        return;
      }
      return (
        this.host +
        "/upload/common/country/" +
        this.countrySelect.filter(
          (item) => item.id === this.lineForm.country_id
        )[0]?.iso +
        ".png"
      );
    },
    calcCity () {
      if (!this.countrySelect) {
        return;
      }
      const city =
        this.countrySelect.filter(
          (item) => item.id === this.lineForm.country_id
        )[0]?.city || [];
      if (city.length === 1) {
        this.lineForm.city = city[0].name;
      }
      return city || [];
    },
    calcArea () {
      if (!this.countrySelect) {
        return;
      }
      const area =
        this.countrySelect
          .filter((item) => item.id === this.lineForm.country_id)[0]
          ?.city.filter((item) => item.name === this.lineForm.city)[0]?.area ||
        [];
      if (area.length === 1) {
        this.lineForm.data_center_id = area[0].id;
      }
      return area;
    },
    calcSelectLine () {
      if (!this.countrySelect) {
        return;
      }
      const line =
        this.countrySelect
          .filter((item) => item.id === this.lineForm.country_id)[0]
          ?.city.filter((item) => item.name === this.lineForm.city)[0]
          ?.area.filter((item) => item.id === this.lineForm.data_center_id)[0]
          ?.line || [];
      // if (line.length === 1) {
      //   this.lineForm.line_id = line[0].id
      //   this.calcLineType = line[0].bill_type
      // }
      return line;
    },
    calcColums () {
      return (val) => {
        const temp = JSON.parse(JSON.stringify(this.bwColumns));
        switch (val) {
          case "flow":
            temp[0].title = lang.cloud_flow + "（GB）";
            return temp;
          case "defence":
            temp[0].title = lang.defence + "（GB）";
            return temp;
          case "ip":
            temp[0].title = "IP" + lang.auth_num + `（${lang.one}）`;
            return temp;
        }
      };
    },
    calcSubTitle () {
      // 副标题
      return (data) => {
        if (data.length > 0) {
          return lang[`mf_${data[0].type}`] + lang.mf_way;
        } else {
          return "";
        }
      };
    },
    calcPrice () {
      // 处理本地价格展示
      return (price) => {
        // 找到价格最低的
        const arr = Object.values(price)
          .sort((a, b) => {
            return a - b;
          })
          .filter(Number);
        if (arr.length > 0) {
          let temp = "";
          Object.keys(price).forEach((item) => {
            if (price[item] * 1 === arr[0] * 1) {
              const name = this.cycleData.filter((el) => el.id === item * 1)[0]
                ?.name;
              temp = (arr[0] * 1).toFixed(2) + "/" + name;
            }
          });
          return temp;
        } else {
          return "0.00";
        }
      };
    },
    // 子项的计费方式是否可选
    calcShow () {
      switch (this.subType) {
        case "line_bw":
          return this.lineForm.bw_data.length > 0 ? true : false;
      }
    },
    calcCpu () {
      return (val) => {
        return val.value + lang.cores;
      };
    },
    calcMemory () {
      return (val) => {
        return val.value + "GB";
      };
    },
    calcLine () {
      // 当前线路
      return this.dataList.filter(
        (item) =>
          item.country_id === this.lineForm.country_id &&
          item.city === this.lineForm.city
      )[0]?.line;
    },
    calcMemery () {
      return (data) => {
        return data.split(",");
      };
    },
    calcRange () {
      // 计算验证范围
      return (val) => {
        if (this.calcType === "memory") {
          // 内存
          return val >= 1 && val <= 512;
        } else {
          return val >= 1 && val <= 1048576;
        }
      };
    },

    calcReg () {
      // 动态生成规则
      return (name, min, max, tag) => {
        // tag 标识 NC
        let pattern = "";
        if (tag === "nc") {
          pattern = /^[0-9NC]*$/;
        } else if (tag === "ip") {
          pattern = /(^NC$)|(^\d+$)|(^\d+_\d*)(,(\d+_\d+)|(\d+))+([0-9]|$)/g;
        } else {
          pattern = /^[0-9]*$/;
        }
        const pass = (val) => {
          if (val === "NC") {
            return true;
          } else if (tag === "ip") {
            return true;
          } else {
            return val >= min && val <= max;
          }
        };
        return [
          { required: true, message: `${lang.input}${name}`, type: "error" },
          {
            pattern: pattern,
            message:
              tag === "ip" ? "" : lang.input + `${min}-${max}` + lang.verify18,
            type: "warning",
          },
          {
            validator: (val) => pass(val),
            message:
              tag === "ip" ? "" : lang.input + `${min}-${max}` + lang.verify18,
            type: "warning",
          },
        ];
      };
    },
    calcIpNum () {
      return (value) => {
        if (value.includes("_")) {
          value = value.split(",").reduce((all, cur) => {
            all += cur.split("_")[0] * 1;
            return all;
          }, 0);
          return value;
        } else {
          return value;
        }
      };
    },
    calcHardwareData () {
      return (type) => {
        return this[`${type}List`];
      };
    },
    calcHardwareColumns () {
      return (type) => {
        return this[`${type}_columns`];
      };
    },
    calcHardwareLoading () {
      return (type) => {
        return this[`${type}Loading`];
      };
    },
    calcHardValue () {
      switch (this.hardMode) {
        case "cpu":
          return lang.mf_cpu;
        case "gpu":
          return lang.mf_gpu;
        case "memory":
          return lang.memory;
        case "disk":
          return lang.disk;
      }
    },
    calcOriginData () {
      return (type) => {
        return this[`${type}`].filter((item) => item.id > 0);
      };
    },
    isShowMulTip () {
      return this.hasMultiLanguage ? `(${lang.support_multili_mark})` : "";
    },
  },
  methods: {
    changeLimitRange (val, name, type, model) {
      if (val < 0) {
        this.limitForm[model][name][type] = 0;
      }
      if (val > 99999999) {
        this.limitForm[model][name][type] = 99999999;
      }
      if (
        typeof this.limitForm[model][name].max !== "object" &&
        this.limitForm[model][name].max !== ""
      ) {
        if (type === "min" && val >= this.limitForm[model][name].max) {
          this.limitForm[model][name].max = this.limitForm[model][name].min;
        }
        if (type === "max" && val <= this.limitForm[model][name].min) {
          this.limitForm[model][name].min = this.limitForm[model][name].max;
        }
      }
    },
    async getPlugin () {
      try {
        const res = await getAddon();
        const temp = res.data.data.list
          .filter((item) => item.status === 1)
          .reduce((all, cur) => {
            all.push(cur.name);
            return all;
          }, []);
        this.hasMultiLanguage = temp.includes("MultiLanguage");
      } catch (error) { }
    },
    /* 硬件配置 */
    async getHardwareList (mod) {
      try {
        this[`${mod}Loading`] = true;
        const res = await getHardware(mod, { product_id: this.id });
        this[`${mod}List`] = res.data.data.list;
        this[`${mod}Loading`] = false;
      } catch (error) {
        this[`${mod}Loading`] = false;
      }
    },
    addHardware (mod) {
      this.optType = "add";
      if (mod === "cpu") {
        this.comTitle = `${lang.order_new}${lang.mf_cpu}`;
      }
      if (mod === "gpu") {
        this.comTitle = `${lang.order_new}${lang.mf_gpu}`;
      }
      if (mod === "memory") {
        this.comTitle = `${lang.order_new}${lang.memory}`;
      }
      if (mod === "disk") {
        this.comTitle = `${lang.order_new}${lang.disk}`;
      }
      const price = this.cycleData
        .reduce((all, cur) => {
          all.push({
            id: cur.id,
            name: cur.name,
            price: "",
          });
          return all;
        }, [])
        .sort((a, b) => {
          return a.id - b.id;
        });
      (this.hardwareForm = {
        value: "",
        order: 0,
        other_config: {
          memory_slot: undefined,
          memory: undefined,
        },
        price,
      }),
        (this.hardMode = mod);
      this.hardDialog = true;
    },
    async editHard (mod, row) {
      if (mod === "cpu") {
        this.comTitle = `${lang.edit}${lang.mf_cpu}`;
      }
      if (mod === "gpu") {
        this.comTitle = `${lang.edit}${lang.mf_gpu}`;
      }
      if (mod === "memory") {
        this.comTitle = `${lang.edit}${lang.memory}`;
      }
      if (mod === "disk") {
        this.comTitle = `${lang.edit}${lang.disk}`;
      }
      this.hardMode = mod;
      this.optType = "update";
      const res = await getHardwareDetails(mod, { id: row.id });
      temp = res.data.data;
      this.delId = row.id;
      const price = temp.duration
        .reduce((all, cur) => {
          all.push({
            id: cur.id,
            name: cur.name,
            price: cur.price,
          });
          return all;
        }, [])
        .sort((a, b) => {
          return a.id - b.id;
        });
      Object.assign(this.hardwareForm, temp);
      this.hardwareForm.price = price;
      this.hardDialog = true;
    },
    async submitHard ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.hardwareForm));
          params.price = params.price.reduce((all, cur) => {
            cur.price && (all[cur.id] = cur.price);
            return all;
          }, {});
          params.product_id = this.id;
          if (this.optType === "add") {
            delete params.id;
          }
          this.submitLoading = true;
          const res = await createAndUpdateHardware(
            this.hardMode,
            this.optType,
            params
          );
          this.$message.success(res.data.msg);
          this.getHardwareList(this.hardMode);
          this.hardDialog = false;
          this.submitLoading = false;
        } catch (error) {
          this.$message.error(error.data.msg);
          this.submitLoading = false;
        }
      } else {
        console.log("Errors: ", validateResult);
        this.$message.warning(firstError);
      }
    },
    async delHard () {
      try {
        this.submitLoading = true;
        const res = await delHardware(this.hardMode, { id: this.delId });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getHardwareList(this.hardMode);
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    /* 硬件配置 end */

    /* 灵活机型 */
    async onChange (row) {
      try {
        const res = await changePackageShow({
          id: row.id,
          hidden: row.hidden,
        });
        this.$message.success(res.data.msg);
        this.getHardwareList("package");
      } catch (error) {
        this.$message.error(error.data.msg);
        this.getHardwareList("package");
      }
    },
    changeRange (e) {
      // if (e[e.length - 1] === 0) {
      //   this.flexForm.optional_memory_id = [0];
      //   this.flexForm.mem_max = undefined;
      //   this.flexForm.mem_max_num = undefined;
      // } else {
      //   this.flexForm.optional_memory_id = e.filter((item) => item !== 0);
      // }
    },
    changeMemRange (e) {
      if (e[e.length - 1] === 0) {
        this.flexForm.optional_disk_id = [0];
        this.flexForm.disk_max_num = undefined;
      } else {
        this.flexForm.optional_disk_id = e.filter((item) => item !== 0);
      }
    },
    addFlex () {
      this.optType = "add";
      this.comTitle = `${lang.order_text53}${lang.model_specs}`;
      this.flexModel = true;
      const price = this.cycleData
        .reduce((all, cur) => {
          all.push({
            id: cur.id,
            name: cur.name,
            price: "",
          });
          return all;
        }, [])
        .sort((a, b) => {
          return a.id - b.id;
        });
      this.flexForm = {
        name: "",
        order: 0,
        group_id: "",
        cpu_option_id: "",
        cpu_num: undefined,
        mem_option_id: "",
        mem_num: undefined,
        disk_option_id: "",
        disk_num: undefined,
        bw: undefined,
        ip_num: undefined,
        description: "",
        optional_memory_id: [0],
        mem_max: undefined,
        mem_max_num: undefined,
        optional_disk_id: [0],
        disk_max_num: undefined,
        price,
      };
    },
    async editFlex (row) {
      this.comTitle = `${lang.edit}${lang.model_specs}`;
      this.optType = "update";
      const res = await getHardwareDetails("package", { id: row.id });
      temp = res.data.data;
      this.delId = row.id;
      const price = temp.duration
        .reduce((all, cur) => {
          all.push({
            id: cur.id,
            name: cur.name,
            price: cur.price,
          });
          return all;
        }, [])
        .sort((a, b) => {
          return a.id - b.id;
        });
      if (temp.optional_memory_id.length === 0) {
        temp.optional_memory_id.push(0);
        temp.mem_max = undefined;
        temp.mem_max_num = undefined;
      }
      if (temp.optional_disk_id.length === 0) {
        temp.optional_disk_id.push(0);
        temp.disk_max_num = undefined;
      }
      Object.assign(this.flexForm, temp);
      this.flexForm.price = price;
      this.flexModel = true;
    },
    async submitFlex ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.flexForm));
          params.price = params.price.reduce((all, cur) => {
            cur.price && (all[cur.id] = cur.price);
            return all;
          }, {});
          params.product_id = this.id;
          if (this.optType === "add") {
            delete params.id;
          }
          params.optional_memory_id = params.optional_memory_id.filter(
            (item) => item !== 0
          );
          params.optional_disk_id = params.optional_disk_id.filter(
            (item) => item !== 0
          );

          this.submitLoading = true;
          const res = await createAndUpdateHardware(
            "package",
            this.optType,
            params
          );
          this.$message.success(res.data.msg);
          this.getHardwareList("package");
          this.flexModel = false;
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);
        this.$message.warning(firstError);
      }
    },
    async delFlex () {
      try {
        const res = await delHardware("package", { id: this.delId });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getHardwareList("package");
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    /* 灵活机型 end */

    async autoFill (name, data) {
      try {
        const price = JSON.parse(JSON.stringify(data)).reduce((all, cur) => {
          if (cur.price) {
            all[cur.id] = cur.price;
          }
          return all;
        }, {});
        const params = {
          product_id: this.id,
          price,
        };
        const res = await fillDurationRatio(params);
        const fillPrice = res.data.data.list;
        this[name].price = this[name].price.map((item) => {
          item.price = fillPrice[item.id];
          return item;
        });
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    changeBw (e, val) {
      if (e !== "NC" || val !== "NO_CHANGE") {
        this.subForm.value_show = "";
      }
      setTimeout(() => {
        this.bwValidator = this.$refs[val].errorClasses === "t-is-success";
      }, 0);
    },
    async changeSort (e) {
      try {
        this.systemGroup = e.newData;
        const image_group_order = e.newData.reduce((all, cur) => {
          all.push(cur.id);
          return all;
        }, []);
        const res = await changeImageGroup({ image_group_order });
        this.$message.success(res.data.msg);
        this.getGroup();
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    // 切换选项卡
    changeTab (e) {
      this.allStatus = false;
      this.backAllStatus = false;
      this.lineType = "";
      switch (e) {
        case "duration":
          this.getDurationList();
          break;
        case "data_center":
          this.getDataList();
          this.getCountryList();
          this.chooseData();
          this.getDurationList();
          break;
        case "model":
          this.getModelList();
          this.getDurationList();
          this.getHardwareList("cpu");
          this.getHardwareList("gpu");
          this.getHardwareList("memory");
          this.getHardwareList("disk");
          break;
        case "hardware":
          // const temp = JSON.parse(JSON.stringify(this.cpu_columns));
          // temp[0].title = lang.box_title46;
          // this.memory_columns = temp;
          // this.disk_columns = temp;
          // this.gpu_columns = temp;
          this.getHardwareList("cpu");
          this.getHardwareList("gpu");
          this.getHardwareList("memory");
          this.getHardwareList("disk");
          break;
        case "flexible":
          this.getHardwareList("package");
          this.getHardwareList("cpu");
          this.getHardwareList("gpu");
          this.getHardwareList("memory");
          this.getHardwareList("disk");
          break;
        case "limit":
          this.getConfigLimitList();
          this.getDataList();
          this.getModelList();
          this.getGroup();
          this.getSystemList();
          break;
        case "system":
          this.getSystemList();
          this.getGroup();
          break;
        case "other":
          this.getOtherConfig();
          break;
        default:
          break;
      }
    },
    checkLimit (val) {
      const reg = /^[0-9]*$/;
      if (reg.test(val) && val >= 0 && val <= 99999999) {
        return { result: true };
      } else {
        return {
          result: false,
          message: lang.input + "0~99999999" + lang.verify2,
          type: "warning",
        };
      }
    },
    // 处理价格
    blurPrice (val, ind) {
      let temp = String(val).match(/^\d*(\.?\d{0,2})/g)[0] || "";
      if (temp && !isNaN(Number(temp))) {
        temp = Number(temp).toFixed(2);
      }
      if (temp >= 999999) {
        this.calcForm.price[ind].price = Number(999999).toFixed(2);
      } else {
        this.calcForm.price[ind].price = temp;
      }
    },
    blurSubPrice (val, ind) {
      let temp = String(val).match(/^\d*(\.?\d{0,2})/g)[0] || "";
      if (temp && !isNaN(Number(temp))) {
        temp = Number(temp).toFixed(2);
      }
      if (temp >= 999999) {
        val = 999999.0;
        this.subForm.price[ind].price = Number(999999).toFixed(2);
      } else {
        this.subForm.price[ind].price = temp;
      }
    },
    blurHardPrice (val, ind) {
      let temp = String(val).match(/^\d*(\.?\d{0,2})/g)[0] || "";
      if (temp && !isNaN(Number(temp))) {
        temp = Number(temp).toFixed(2);
      }
      if (temp >= 999999) {
        this.hardwareForm.price[ind].price = Number(999999).toFixed(2);
      } else {
        this.hardwareForm.price[ind].price = temp;
      }
    },
    blurFlexPrice (val, ind) {
      let temp = String(val).match(/^\d*(\.?\d{0,2})/g)[0] || "";
      if (temp && !isNaN(Number(temp))) {
        temp = Number(temp).toFixed(2);
      }
      if (temp >= 999999) {
        this.flexForm.price[ind].price = Number(999999).toFixed(2);
      } else {
        this.flexForm.price[ind].price = temp;
      }
    },

    changeAdvance () {
      this.isAdvance = !this.isAdvance;
    },
    /* 配置限制 */
    async getConfigLimitList () {
      try {
        this.limitLoading = true;
        const res = await getConfigLimit({
          product_id: this.id
        });
        this.limitData = res.data.data.list;
        this.limitLoading = false;
      } catch (error) {
        this.limitLoading = false;
      }
    },
    addLimit () {
      this.optType = "add";
      this.limitModel = true;
      this.ruleLimit = [];
      this.resultLimit = [];
      this.limitForm = JSON.parse(JSON.stringify(this.originLimitForm));
      this.comTitle = `${lang.order_text53}${lang.mf_rule}`;
    },
    handleData (temp) {
      const typeArr = [
        "memory",
        "system_disk",
        "data_disk",
        "bw",
        "flow",
        "ipv4_num",
        "ipv6_num",
      ];
      typeArr.forEach((item) => {
        if (temp[item]) {
          if (temp[item].min == "") {
            temp[item].min = null;
          } else {
            temp[item].min = temp[item].min * 1;
          }
          if (temp[item].max == "") {
            temp[item].max = null;
          } else {
            temp[item].max = temp[item].max * 1;
          }
        }
      });
      return temp;
    },
    editLimit (row, type) {
      this.comTitle = (type === "copy" ? lang.mf_copy : lang.edit) + lang.mf_rule;
      this.limitModel = true;
      this.optType = type;
      this.ruleLimit = Object.keys(row.rule);
      this.resultLimit = Object.keys(row.result);
      const tempRule = JSON.parse(JSON.stringify(row)).rule;
      const tempResult = JSON.parse(JSON.stringify(row)).result;
      let temp = JSON.parse(JSON.stringify(this.originLimitForm));
      temp.id = row.id;
      temp.rule = Object.assign(temp.rule, this.handleData(tempRule));
      temp.result = Object.assign(temp.result, this.handleData(tempResult));
      this.limitForm = temp;
    },
    async submitLimit ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const temp = JSON.parse(JSON.stringify(this.limitForm));
          const rule = this.ruleLimit.reduce((all, cur) => {
            if (temp.rule[cur]) {
              all[cur] = temp.rule[cur];
            }
            return all;
          }, {});
          const result = this.resultLimit.reduce((all, cur) => {
            if (temp.result[cur]) {
              all[cur] = temp.result[cur];
            }
            return all;
          }, {});
          let params = {
            id: this.limitForm.id,
            rule,
            result
          };
          params.product_id = this.id;
          if (this.optType === "add" || this.optType === "copy") {
            delete params.id;
          }
          this.submitLoading = true;
          const res = await createAndUpdateConfigLimit(this.optType, params);
          this.$message.success(res.data.msg);
          this.getConfigLimitList();
          this.limitModel = false;
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);
        this.$message.warning(firstError);
      }
    },
    async delLimit () {
      try {
        this.submitLoading = true;
        const res = await delConfigLimit({ id: this.delId });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getConfigLimitList(this.delType);
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    /* 线路 */
    addLine () {
      this.lineModel = true;
      this.lineType = "add";
      this.dataForm.country_id = "";
      this.lineForm = {
        country_id: "", // 线路国家
        city: "", // 线路城市
        data_center_id: "",
        name: "",
        bill_type: "bw", // bw, flow
        bw_ip_group: "",
        defence_ip_group: "",
        ip_enable: 0, // ip开关
        defence_enable: 0, // 防护开关
        bw_data: [], // 带宽
        flow_data: [], //流量
        defence_data: [], // 防护
        ip_data: [], // ip
        order: 0,
      };
      this.lineRight = false;
    },
    async editLine (row) {
      try {
        const res = await getLineDetails({ id: row.id });
        this.lineForm = JSON.parse(JSON.stringify(res.data.data));
        this.lineType = "update";
        this.optType = "update";
        this.lineRight = false;
        this.lineModel = true;
        this.bw_ip_show = this.lineForm.bw_ip_group ? true : false;
        this.defence_ip_show = this.lineForm.defence_ip_group ? true : false;
        this.subId = row.id;
      } catch (error) { }
    },
    changeCountry () {
      this.lineForm.city = "";
      this.lineForm.data_center_id = "";
      this.lineForm.line_id = "";
    },
    changeCity () {
      this.lineForm.data_center_id = "";
      this.lineForm.line_id = "";
    },
    // 编辑线路子项
    async editSubItem (row, index, type) {
      this.subType = type;
      this.optType = "update";
      this.delSubIndex = index;
      this.lineRight = true;
      let temp = "";
      this.bwValidator = true;
      if (this.lineType === "add") {
        temp = row;
      } else {
        const res = await getLineChildDetails(type, { id: row.id });
        temp = res.data.data;
        this.delId = row.id;
      }
      setTimeout(() => {
        const price = temp.duration
          .reduce((all, cur) => {
            all.push({
              id: cur.id,
              name: cur.name,
              price: cur.price,
            });
            return all;
          }, [])
          .sort((a, b) => {
            return a.id - b.id;
          });
        Object.assign(this.subForm, temp);
        this.subForm.price = price;
        if (
          this.subForm.other_config.in_bw ||
          this.subForm.other_config.advanced_bw
        ) {
          this.isAdvance = true;
        } else {
          this.isAdvance = false;
        }
      }, 0);
    },
    // 删除线路子项
    async delSubItem () {
      try {
        this.lineRight = false;
        if (this.lineType === "add") {
          // 本地删除
          switch (this.delType) {
            case "line_bw":
              return this.lineForm.bw_data.splice(this.delSubIndex, 1);
            case "line_flow":
              return this.lineForm.flow_data.splice(this.delSubIndex, 1);
            case "line_defence":
              return this.lineForm.defence_data.splice(this.delSubIndex, 1);
            case "line_ip":
              return this.lineForm.ip_data.splice(this.delSubIndex, 1);
          }
        } else {
          // 编辑的时候删除
          this.submitLoading = true;
          const res = await delLineChild(this.delType, { id: this.delId });
          this.$message.success(res.data.msg);
          this.delVisible = false;
          // this.editLine({ id: this.subId })
          this.submitLine({ validateResult: true, firstError: "" }, false);
          this.submitLoading = false;
        }
      } catch (error) { }
    },
    // 新增线路子项
    addLineSub (type) {
      this.subType = type;
      if (this.$refs["bw-item"]) {
        this.bwValidator =
          this.$refs["bw-item"].errorClasses === "t-is-success";
      } else if (this.$refs["ip-item"]) {
        this.bwValidator =
          this.$refs["ip-item"].errorClasses === "t-is-success";
      } else {
        this.bwValidator = true;
      }
      this.optType = "add";
      this.isAdvance = false;
      if (type === "line_bw") {
        this.subForm.type = this.lineForm.bw_data[0]?.type || "radio";
      }

      this.subForm.value = "";
      this.subForm.min_value = "";
      this.subForm.max_value = "";
      this.subForm.other_config = {
        in_bw: "",
        bill_cycle: "last_30days",
      };
      this.lineRight = true;
      const price = this.cycleData
        .reduce((all, cur) => {
          all.push({
            id: cur.id,
            name: cur.name,
            price: "",
          });
          return all;
        }, [])
        .sort((a, b) => {
          return a.id - b.id;
        });
      this.subForm.price = price;
      this.bw_ip_show = false;
      this.defence_ip_show = false;
    },
    // 保存线路子项
    async submitSub ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.subForm));
          params.step = 1;
          params.product_id = this.id;
          this.submitLoading = true;
          const duration = JSON.parse(JSON.stringify(params.price));
          params.price = params.price.reduce((all, cur) => {
            cur.price && (all[cur.id] = cur.price);
            return all;
          }, {});

          // 新增的时候本地处理
          if (this.lineType === "add") {
            params.duration = duration;
            switch (this.subType) {
              case "line_bw":
                this.optType === "add"
                  ? this.lineForm.bw_data.unshift(params)
                  : this.lineForm.bw_data.splice(this.delSubIndex, 1, params);
                break;
              case "line_flow":
                this.optType === "add"
                  ? this.lineForm.flow_data.unshift(params)
                  : this.lineForm.flow_data.splice(this.delSubIndex, 1, params);
                break;
              case "line_defence":
                this.optType === "add"
                  ? this.lineForm.defence_data.unshift(params)
                  : this.lineForm.defence_data.splice(
                    this.delSubIndex,
                    1,
                    params
                  );
                break;
              case "line_ip":
                this.optType === "add"
                  ? this.lineForm.ip_data.unshift(params)
                  : this.lineForm.ip_data.splice(this.delSubIndex, 1, params);
                break;
            }
            this.submitLoading = false;
            this.lineRight = false;
            return;
          }
          // 新增：传线路id，编辑传配置id
          params.id = this.optType === "add" ? this.subId : this.delId;
          const res = await createAndUpdateLineChild(
            this.subType,
            this.optType,
            params
          );
          this.$message.success(res.data.msg);
          // this.editLine({ id: this.subId })
          this.submitLine({ validateResult: true, firstError: "" }, false);
          this.submitLoading = false;
        } catch (error) {
          console.log("@@@@@line", error);
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        if (this.$refs["bw-item"]) {
          this.bwValidator =
            this.$refs["bw-item"].errorClasses === "t-is-success";
        }
        console.log("Errors: ", validateResult);
        this.$message.warning(firstError);
      }
    },

    async submitLine ({ validateResult, firstError }, bol = true) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.lineForm));
          params.product_id = this.id;
          const isAdd = params.id ? "update" : "add";
          this.submitLoading = true;
          const res = await createAndUpdateLine(isAdd, params);
          if (bol) {
            this.$message.success(res.data.msg);
            this.getDataList();
            this.lineModel = false;
            this.lineType = "";
          } else {
            this.editLine({ id: this.subId });
          }
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);
        this.$message.warning(firstError);
      }
    },

    /* 数据中心 */
    async getDataList () {
      try {
        this.dataLoading = true;
        const res = await getDataCenter({
          product_id: this.id,
          page: 1,
          limit: 1000,
        });
        this.dataList = res.data.data.list;
        this.dataLoading = false;
      } catch (error) {
        this.dataLoading = false;
      }
    },
    // 国家列表
    async getCountryList () {
      try {
        const res = await getCountry();
        this.countryList = res.data.data.list;
      } catch (error) { }
    },
    async chooseData () {
      try {
        const res = await chooseDataCenter({
          product_id: this.id,
        });
        this.countrySelect = res.data.data.list;
        if (this.countrySelect.length === 1) {
          this.lineForm.country_id = this.countrySelect[0].id;
        }
      } catch (error) { }
    },
    changeType () {
      this.$refs.dataForm.clearValidate(["cloud_config_id"]);
    },
    addData () {
      this.optType = "add";
      this.dataModel = true;
      this.dataForm.country_id = "";
      this.dataForm.city = "";
      this.dataForm.area = "";
      this.comTitle = lang.new_create + lang.data_center;
    },
    async deleteData () {
      try {
        this.submitLoading = true;
        const res = await deleteDataCenter({ id: this.delId });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getDataList();
        this.chooseData();
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    async deleteLine () {
      try {
        this.submitLoading = true;
        const res = await delLine({ id: this.delId });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getDataList();
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    editData (row) {
      this.comTitle = lang.edit + lang.data_center;
      this.optType = "update";
      this.dataModel = true;
      const { id, country_id, city, area, order } = row;
      this.dataForm = {
        id,
        country_id,
        city,
        area,
        order,
      };
    },
    // 保存数据中心
    async submitData ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.dataForm));
          params.product_id = this.id;
          if (this.optType === "add") {
            delete params.id;
          }
          this.submitLoading = true;
          const res = await createOrUpdateDataCenter(this.optType, params);
          this.$message.success(res.data.msg);
          this.getDataList();
          this.chooseData();
          this.dataModel = false;
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);
        this.$message.warning(firstError);
      }
    },
    /* 型号配置 */
    async getModelList () {
      try {
        this.modelLoading = true;
        const res = await getModel({
          product_id: this.id,
          page: 1,
          limit: 1000,
        });
        this.modelList = res.data.data.list;
        this.modelLoading = false;
      } catch (error) {
        this.modelLoading = false;
      }
    },
    createOptions (value, type) {
      this[`${type}List`].push({
        id: -this[`${type}List`].length,
        value,
      });
      this.calcForm[type] = value;
    },
    // 拖动排序
    async changeModelSort ({ current, targetIndex, newData }) {
      try {
        const targetId =
          targetIndex === 0
            ? 0
            : newData[newData.findIndex((item) => item.id === current.id) - 1]
              .id;
        const params = {
          id: current.id,
          prev_model_config_id: targetId,
        };
        const res = await changeModelSort(params);
        this.$message.success(res.data.msg);
        this.getModelList();
      } catch (error) {
        console.log("error", error);
        this.$message.error(error.data.msg);
      }
    },
    addCalc (type) {
      // 固定机型
      // 添加model
      this.calcType = type;
      this.optType = "add";
      let temp_type = "";
      switch (type) {
        case "model":
          this.comTitle = `${lang.order_text53}${lang.model_specs}`;
          break;
      }
      this.calcModel = true;
      const price = this.cycleData
        .reduce((all, cur) => {
          all.push({
            id: cur.id,
            name: cur.name,
            price: "",
          });
          return all;
        }, [])
        .sort((a, b) => {
          return a.id - b.id;
        });
      this.isAdvance = false;
      this.calcForm = {
        name: "",
        group_id: "",
        cpu: "",
        cpu_param: "",
        memory: "",
        disk: "",
        // 增值选配
        support_optional: 0,
        optional_only_for_upgrade: 0,
        optional_memory_id: [],
        leave_memory: undefined,
        max_memory_num: undefined,
        optional_disk_id: [],
        max_disk_num: undefined,
        optional_gpu_id: [],
        gpu: "",
        max_gpu_num: undefined,
        price,
      };
    },
    // 编辑 model
    async editCalc (row, type) {
      this.calcType = type;
      this.optType = "update";
      this.disabledWay = true;
      this.comTitle = `${lang.edit}${lang.model_specs}`;
      this.editModel(row);
      this.isAdvance = false;
    },
    async editModel (row) {
      try {
        const res = await getModelDetails({
          id: row.id,
        });
        this.calcModel = true;
        const temp = res.data.data;
        let price = temp.duration
          .reduce((all, cur) => {
            all.push({
              id: cur.id,
              name: cur.name,
              price: cur.price,
            });
            return all;
          }, [])
          .sort((a, b) => {
            return a.id - b.id;
          });
        temp.price = price;
        delete temp.duration;
        temp.max_memory_num = temp.max_memory_num || undefined;
        temp.leave_memory = temp.leave_memory || undefined;
        temp.max_disk_num = temp.max_disk_num || undefined;
        temp.max_gpu_num = temp.max_gpu_num || undefined;
        Object.assign(this.calcForm, temp);
        this.optType = "update";
        this.calcModel = true;
      } catch (error) { }
    },
    submitCalc ({ validateResult, firstError }) {
      if (validateResult === true) {
        switch (this.calcType) {
          case "model":
            return this.handlerModel();
        }
      } else {
        console.log("Errors: ", validateResult);
        this.$message.warning(firstError);
      }
    },
    async deleteModel () {
      try {
        this.submitLoading = true;
        const res = await delModel({
          id: this.delId,
        });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getModelList();
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.delVisible = false;
        this.$message.error(error.data.msg);
      }
    },
    // 提交model
    async handlerModel () {
      try {
        const params = JSON.parse(JSON.stringify(this.calcForm));
        params.price = params.price.reduce((all, cur) => {
          cur.price && (all[cur.id] = cur.price);
          return all;
        }, {});
        params.product_id = this.id;
        if (this.optType === "add") {
          delete params.id;
        }
        this.submitLoading = true;
        const res = await createAndUpdateModel(this.optType, params);
        this.$message.success(res.data.msg);
        this.getModelList();
        this.calcModel = false;
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    /* 改变最大最小值：内存，系统盘和数据盘
    根据calcType来区分：memory=512， 其他 1048576
     */
    changeMin (e) {
      const num = this.calcType === "memory" ? 512 : 1048576;
      if (e * 1 >= num) {
        this.subForm.min_value = 1;
      } else if (e * 1 >= this.subForm.max_value * 1) {
        if (this.subForm.max_value * 1) {
          this.subForm.max_value = e * 1;
        }
      }
    },
    changeMax (e) {
      const num = this.calcType === "memory" ? 512 : 1048576;
      if (e * 1 > num) {
        this.subForm.max_value = num;
      } else if (e * 1 <= this.subForm.min_value * 1) {
        if (this.subForm.min_value * 1) {
          this.subForm.min_value = e * 1;
        }
      }
    },
    changeStep (e) {
      if (e * 1 > this.calcForm.max_value * 1 - this.calcForm.min_value * 1) {
        this.calcForm.step = 1;
      }
    },
    /* 型号配置 end*/
    /* 周期相关 */
    async changeRadio () {
      try {
        const res = await getDurationRatio({
          product_id: this.id,
        });
        this.ratioData = res.data.data.list.map((item) => {
          item.ratio = item.ratio ? item.ratio * 1 : null;
          return item;
        });
        this.ratioModel = true;
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    async saveRatio () {
      try {
        const isAll = this.ratioData.every((item) => item.ratio);
        if (!isAll) {
          return this.$message.error(`${lang.input}${lang.mf_ratio}`);
        }
        const temp = JSON.parse(JSON.stringify(this.ratioData)).reduce(
          (all, cur) => {
            all[cur.id] = cur.ratio;
            return all;
          },
          {}
        );
        const params = {
          product_id: this.id,
          ratio: temp,
        };
        this.submitLoading = true;
        const res = await saveDurationRatio(params);
        this.submitLoading = false;
        this.ratioModel = false;
        this.$message.success(res.data.msg);
        this.getDurationList();
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    closeData () {
      this.dataModel = false;
      this.lineModel = false;
      this.lineType = "";
    },
    async getDurationList () {
      try {
        this.loading = true;
        const res = await getDuration({
          product_id: this.id,
          page: 1,
          limit: 100,
        });
        this.cycleData = res.data.data.list;
        this.loading = false;
      } catch (error) {
        this.loading = false;
      }
    },
    addCycle () {
      this.optType = "add";
      this.comTitle = lang.add_cycle;
      this.cycleForm.name = "";
      this.cycleForm.unit = "month";
      this.cycleForm.num = "";
      this.cycleForm.price_factor = 1;
      this.cycleForm.price = null;
      this.cycleModel = true;
    },
    editCycle (row) {
      this.optType = "update";
      this.comTitle = lang.update + lang.cycle;
      this.cycleForm = JSON.parse(JSON.stringify(row));
      this.cycleModel = true;
      if (this.cycleForm.price) {
        this.cycleForm.price = this.cycleForm.price * 1;
      }
    },
    async submitCycle ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.cycleForm));
          params.product_id = this.id;
          if (this.optType === "add") {
            delete params.id;
          }
          if (!params.price_factor && params.price_factor !== 0) {
            params.price_factor = "1.00";
          }
          this.submitLoading = true;
          const res = await createAndUpdateDuration(this.optType, params);
          this.$message.success(res.data.msg);
          this.getDurationList();
          this.cycleModel = false;
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);
        this.$message.warning(firstError);
      }
    },
    // 删除周期
    async deleteCycle () {
      try {
        this.submitLoading = true;
        const res = await delDuration({
          product_id: this.id,
          id: this.delId,
        });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getDurationList();
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    /* 操作系统 */
    rehandleSelectChange (value, { selectedRowData }) {
      this.selectedRowKeys = value;
    },
    // 批量删除
    deleteBatch () {
      if (this.selectedRowKeys.length == 0) {
        this.$message.warning(lang.mf_tip45);
        return;
      }
      this.delType = 'batchSystem';
      this.batchDelete = true;
      this.delVisible = true;
      this.delTit = lang.mf_tip46;
    },
    async handlerBatchSystem () {
      try {
        this.submitLoading = true;
        const id = this.selectedRowKeys;
        const res = await batchDelImage({ id });
        this.$message.success(res.data.msg);
        this.getSystemList();
        this.delVisible = false;
        this.submitLoading = false;
        this.batchDelete = false;
      } catch (error) {
        this.delVisible = false;
        this.submitLoading = false;
        this.$message.error(error.data.msg);
        this.batchDelete = false;
      }
    },
    clearKey () {
      this.systemParams.page = 1;
      this.getSystemList();
    },
    // 系统列表
    async getSystemList () {
      try {
        this.loading = true;
        const params = JSON.parse(JSON.stringify(this.systemParams));
        params.product_id = this.id;
        const res = await getImage(params);
        this.systemList = res.data.data.list;
        this.loading = false;
        this.selectedRowKeys = [];
      } catch (error) {
        this.loading = false;
      }
    },
    // 系统分类
    async getGroup () {
      try {
        const res = await getImageGroup({
          product_id: this.id,
          orderby: "id",
          sort: "desc",
        });
        this.systemGroup = res.data.data.list;
      } catch (error) { }
    },
    createNewSys () {
      // 新增
      this.systemModel = true;
      this.optType = "add";
      this.comTitle = `${lang.add}${lang.system}`;
      this.createSystem.image_group_id = "";
      this.createSystem.name = "";
      this.createSystem.charge = 0;
      this.createSystem.price = "";
      this.createSystem.enable = 0;
      this.createSystem.rel_image_id = "";
    },
    editSystem (row) {
      this.optType = "update";
      this.comTitle = lang.update + lang.system;
      this.createSystem = { ...row };
      this.systemModel = true;
    },
    async submitSystem ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.createSystem));
          params.product_id = this.id;
          if (this.optType === "add") {
            delete params.id;
          }
          this.submitLoading = true;
          const res = await createAndUpdateImage(this.optType, params);
          this.$message.success(res.data.msg);
          this.getSystemList();
          this.systemModel = false;
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);
        this.$message.warning(firstError);
      }
    },
    // 列表修改状态
    async changeSystemStatus (row) {
      try {
        const params = JSON.parse(JSON.stringify(row));
        params.product_id = this.id;
        const res = await createAndUpdateImage("update", params);
        this.$message.success(res.data.msg);
        this.getSystemList();
      } catch (error) { }
    },
    // 拉取系统
    async refeshImageHandler () {
      try {
        this.$message.success(lang.mf_tip);
        await refreshImage({
          product_id: this.id,
        });
        this.getSystemList();
        this.getGroup();
      } catch (error) { }
    },
    // 分类管理
    classManage () {
      this.classModel = true;
      this.classParams.name = "";
      this.classParams.icon = "";
      this.optType = "add";
    },
    async submitSystemGroup ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.classParams));
          if (this.optType === "add") {
            delete params.id;
            params.product_id = this.id;
          }
          this.submitLoading = true;
          const res = await createAndUpdateImageGroup(this.optType, params);
          this.$message.success(res.data.msg);
          this.getGroup();
          this.submitLoading = false;
          this.classParams.name = "";
          this.classParams.icon = "";
          this.$refs.classForm.reset();
          this.optType = "add";
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);
        this.$message.warning(firstError);
      }
    },
    editGroup (row) {
      this.optType = "update";
      this.classParams = JSON.parse(JSON.stringify(row));
    },
    async deleteGroup () {
      try {
        this.submitLoading = true;
        const res = await delImageGroup({
          id: this.delId,
        });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getGroup();
        this.classParams.name = "";
        this.classParams.icon = "";
        this.$refs.classForm.reset();
        this.optType = "add";
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    async deleteSystem () {
      try {
        this.submitLoading = true;
        const res = await delImage({
          id: this.delId,
        });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getSystemList();
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    /* 其他设置 */
    async getOtherConfig () {
      try {
        const res = await getCloudConfig({
          product_id: this.id,
        });
        this.otherForm = res.data.data;
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    async submitConfig ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.otherForm));
          params.product_id = this.id;
          this.submitLoading = true;
          const res = await saveCloudConfig(params);
          this.$message.success(res.data.msg);
          this.submitLoading = false;
          this.dataModel = false;
          this.getOtherConfig();
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);
        this.$message.warning(firstError);
      }
    },
    /* 通用删除按钮 */
    comDel (type, row, index, mod) {
      this.batchDelete = false;
      this.hardMode = mod;
      this.delId = row.id;
      if (type === "cycle") {
        this.delTit = lang.sure_del_cycle;
      }
      this.delTit = lang.sureDelete;
      this.delType = type;
      // 新增的时候，本地删除线路子项
      if (
        this.lineType === "add" &&
        (this.subType === "line_bw" ||
          this.subType === "line_flow" ||
          this.subType === "line_defence" ||
          this.subType === "line_ip")
      ) {
        this.delSubIndex = index;
        this.delSubItem();
        return;
      }
      this.delVisible = true;
    },
    // 通用删除
    sureDelete () {
      // cycle, c_line, data, model, hard, limit, system, group, line_bw, line_flow, line_ip, line_defence
      switch (this.delType) {
        case "cycle":
          return this.deleteCycle();
        case "model":
          return this.deleteModel();
        case "system": // 删除镜像
          return this.deleteSystem();
        case "group": // 删除镜像分类
          return this.deleteGroup();
        case "data":
          return this.deleteData();
        case "c_line":
          return this.deleteLine();
        case "line_bw":
        case "line_flow":
        case "line_defence":
        case "line_ip":
          return this.delSubItem();
        case "limit":
          return this.delLimit();
        case "hard":
          return this.delHard();
        case "flex":
          return this.delFlex();
        case 'batchSystem':
          return this.handlerBatchSystem();
        default:
          return null;
      }
    },
    formatPrice (val) {
      return (val * 1).toFixed(2);
    },
  },
  created () {
    this.id = location.href.split("?")[1].split("=")[1];
    this.iconSelecet = this.iconList.reduce((all, cur) => {
      all.push({
        value: cur,
        label: `${this.host}/plugins/server/mf_dcim/template/admin/img/${cur}.svg`,
      });
      return all;
    }, []);
    // 默认拉取数据
    this.getDurationList();
    this.getPlugin();
  },
}).$mount(template);
