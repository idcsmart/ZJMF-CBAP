const template = document.getElementsByClassName('template')[0]
Vue.prototype.lang = window.lang
new Vue({
  components: {
    asideMenu,
    topMenu,
    pagination,
  },
  created () {
    this.analysisUrl()
    this.getCommonData()
    this.getList()
  },
  mounted () {

  },
  updated () {
    // // 关闭loading
    document.getElementById('mainLoading').style.display = 'none';
    document.getElementsByClassName('template')[0].style.display = 'block'
  },
  destroyed () {

  },
  data () {
    return {
      id: 109,
      params: {
        page: 1,
        limit: 20,
        pageSizes: [20, 50, 100],
        total: 0,
        orderby: 'id',
        sort: 'desc',
        keywords: '',
        status: '',
        m: null
      },
      commonList: [],
      commonData: {},
      loading: false,
      status: {
        Unpaid: { text: "未付款", color: "#F64E60", bgColor: "#FFE2E5" },
        Pending: { text: "开通中", color: "#3699FF", bgColor: "#E1F0FF" },
        Active: { text: "正常", color: "#1BC5BD", bgColor: "#C9F7F5" },
        Suspended: { text: "已暂停", color: "#F0142F", bgColor: "#FFE2E5" },
        Deleted: { text: "已删除", color: "#9696A3", bgColor: "#F2F2F7" },
        Failed: { text: "开通中", color: "#FFA800", bgColor: "#FFF4DE" }
      },
      statusSelect: [
        {
          id: 1,
          status: 'Unpaid',
          label: "未付款"
        },
        {
          id: 2,
          status: 'Pending',
          label: "开通中"
        },
        {
          id: 3,
          status: 'Active',
          label: "正常"
        },
        {
          id: 4,
          status: 'Suspended',
          label: "已暂停"
        },
        {
          id: 5,
          status: 'Deleted',
          label: "已删除"
        }
      ],
      submitLoading: false
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
  methods: {
    analysisUrl () {
      let url = window.location.href
      let getqyinfo = url.split('?')[1]
      let getqys = new URLSearchParams('?' + getqyinfo)
      let m = getqys.get('m')
      this.params.m = m
    },
    // 获取列表
    async getList () {
      try {
        this.loading = true
        const res = await getCommonList(this.params)
        this.commonList = res.data.data.list
        this.params.total = res.data.data.count
        this.loading = false
        this.submitLoading = false
      } catch (error) {
        this.loading = false
      }
    },
    inputChange () {
      this.submitLoading = true
      this.params.page = 1
      this.getList()
    },
    // 跳转产品详情
    toDetail (row) {
      // if (row.status !== 'Active') {
      //   return false
      // }
      location.href = `productdetail.htm?id=${row.id}`
    },
    // 跳转订购页
    toOrder () {
      const id = this.id
      location.href = `goods.htm?id=${id}`
    },

    // 每页展示数改变
    sizeChange (e) {
      this.params.limit = e
      this.params.page = 1
      // 获取列表
      this.getList()
    },
    // 当前页改变
    currentChange (e) {
      this.params.page = e
      this.getList()
    },

    // 获取通用配置
    getCommonData () {
      this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
      document.title = this.commonData.website_name + '-通用产品'

    }
  },

}).$mount(template)
typeof old_onload == 'function' && old_onload()