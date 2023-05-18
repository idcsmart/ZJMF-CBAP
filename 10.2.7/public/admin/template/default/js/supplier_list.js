(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('supplier_list')[0]
        Vue.prototype.lang = window.lang
        Vue.prototype.moment = window.moment
        new Vue({
            data() {
                return {
                    // 分页相关
                    params: {
                        keywords: '',
                        page: 1,
                        limit: 20,
                        orderby: 'id',
                        sort: 'desc'
                    },
                    total: 0,
                    editId: null,
                    delId: null,
                    pageSizeOptions: [20, 50, 100],
                    // 表格相关
                    data: [],
                    delVisible: false,
                    formData: {

                    },
                    configData: [
                        {
                            title: '供应商名称',
                            type: 'text',
                            name: 'name'
                        },
                        {
                            title: '接口地址',
                            type: 'text',
                            name: 'url',
                            tip: '上游业务系统的访问地址或ip'
                        },
                        {
                            title: '用户名',
                            type: 'text',
                            name: 'username',
                            tip: '您在上游注册的账号，手机/邮箱',
                        },
                        {
                            title: 'API密钥',
                            type: 'text',
                            name: 'token',
                            tip: '您在上游获取的api密钥（上游用户中心-安全中心-API）'

                        },
                        {
                            title: 'API私钥',
                            type: 'textarea',
                            name: 'secret',
                            tip: '您在上游获取的api私钥（上游用户中心-安全中心-API）'
                        },
                        {
                            title: '联系方式',
                            type: 'text',
                            name: 'contact'
                        },
                        {
                            title: '备注',
                            type: 'textarea',
                            name: 'notes'
                        },

                    ],
                    rules: {
                        name: [{ required: true, message: lang.required, type: 'error' }],
                        url: [{ required: true, message: lang.required, type: 'error' }],
                        username: [{ required: true, message: lang.required, type: 'error' }],
                        token: [{ required: true, message: lang.required, type: 'error' }],
                        secret: [{ required: true, message: lang.required, type: 'error' }],
                    },
                    columns: [
                        {
                            title: 'ID',
                            width: '100',
                            align: 'let',
                            colKey: 'id',
                            sortType: 'all',
                            sorter: true
                        },
                        {
                            title: '名称',
                            width: '150',
                            colKey: 'name',
                            cell: 'name',
                            ellipsis: true
                        },
                        {
                            title: '链接地址',
                            colKey: 'url',
                            ellipsis: true

                        },
                        {
                            title: '产品数量/商品数量',
                            cell: 'num',
                            width: '200',
                            ellipsis: true

                        },
                        {
                            title: '状态',
                            cell: 'status',
                            width: '150',
                            ellipsis: true

                        },
                        {
                            title: '操作',
                            width: '120',
                            cell: 'op',
                        },
                    ],
                    currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix || '¥',
                    currency_suffix: JSON.parse(localStorage.getItem('common_set')).currency_suffix,
                    tableLayout: false,
                    hover: true,
                    loading: false,
                    configVisble: false,
                }
            },
            filters: {
                filterMoney(money) {
                    if (isNaN(money)) {
                        return '0.00'
                    } else {
                        const temp = `${money}`.split('.')
                        return parseInt(temp[0]).toLocaleString() + '.' + (temp[1] || '00')
                    }
                }
            },
            computed: {

            },
            mounted() {

            },
            created() {
                // 获取提现列表
                this.getSupplierList()
            },
            methods: {
                // 编辑/添加
                onSubmit({ validateResult, firstError }) {
                    if (validateResult === true) {
                        if (this.editId !== null) {
                            editSupplier(this.editId, this.formData).then((res) => {
                                this.$message.success(res.data.msg)
                                this.configVisble = false
                                this.getSupplierList()
                            }).catch((err) => {
                                this.$message.error(err.data.msg)
                            })
                        } else {
                            addSupplier(this.formData).then((res) => {
                                this.$message.success(res.data.msg)
                                this.configVisble = false
                                this.getSupplierList()
                            }).catch((err) => {
                                this.$message.error(err.data.msg)
                            })
                        }
                    }
                },
                sortChange(val) {
                    if (!val) {
                        this.params.orderby = 'id'
                        this.params.sort = 'desc'
                    } else {
                        this.params.orderby = val.sortBy
                        this.params.sort = val.descending ? 'desc' : 'asc'
                    }
                    this.getSupplierList()
                },
                handelEdit(row) {
                    this.editId = row.id
                    this.getSupplierDrtail(row.id)
                },
                getSupplierDrtail(id) {
                    supplierDrtail(id).then((res) => {
                        this.formData = res.data.data.supplier
                        this.configVisble = true
                    })
                },
                goDetail(id) {
                    location.href = `supplier_order.htm?id=${id}`
                },
                diaClose() {
                    this.editId = null
                    this.$refs.userDialog.reset()
                    this.formData = {}
                },
                // 添加供应商
                addSupplier() {
                    this.editId = null
                    this.configVisble = true
                },
                handelDel(id) {
                    this.delId = id
                    this.delVisible = true
                },
                sureDel() {
                    delSupplier(this.delId).then((res) => {
                        this.$message.success(res.data.msg)
                        this.delVisible = false
                        this.getSupplierList()
                    }).catch((error) => {
                        this.$message.error(error.data.msg)
                    })
                },
                // 
                // 搜索框 搜索
                seacrh() {
                    this.params.page = 1
                    // 重新拉取申请列表
                    this.getSupplierList()
                },
                // 清空搜索框
                clearKey() {
                    this.params.keywords = ''
                    this.params.page = 1
                    // 重新拉取申请列表
                    this.getSupplierList()
                },
                // 底部分页 页面跳转事件
                changePage(e) {
                    this.params.page = e.current
                    this.params.limit = e.pageSize
                    this.getSupplierList()
                },
                // 获取申请列表
                async getSupplierList() {
                    this.loading = true
                    const res = await supplierList(this.params)
                    this.total = res.data.data.count
                    this.data = res.data.data.list.map((item) => {
                        item.status = false
                        item.resgen = ''
                        return item
                    })
                    this.getSupplierStatus()
                    this.loading = false
                },
                // 检查供应商接口连接状态
                getSupplierStatus() {
                    this.data.forEach((item) => {
                        supplierStatus(item.id).then(() => {
                            item.status = true
                        }).catch((err) => {
                            item.resgen = err.data.msg
                        })
                    })
                },
            },
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
