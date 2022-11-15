(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('market')[0]
        Vue.prototype.lang = window.lang
        new Vue({
            components: {
                asideMenu,
                topMenu,
                pagination,
            },
            created () {
                this.getCommonData()
            },
            mounted () {

            },
            updated () {

            },
            destroyed () {

            },
            data () {
                return {
                    commonData: {},
                    activeName: "6",
                    // 应用管理开始
                    params1: {
                        page: 1,
                        limit: 20,
                        pageSizes: [20, 50, 100],
                        total: 200,
                        orderby: 'id',
                        sort: 'desc',
                        keywords: '',
                    },
                    loading1: false,
                    data1: [],
                    // 应用管理结束
                    // 信息设置
                    infoForm: {
                        type: '1',
                        name: '',
                        qq: '',
                        email: '',
                        address: '',
                        text: '',
                        uploadType: '1',
                        name: '',
                    },
                    infoRules: {
                        name: { required: true, message: '请输入用户昵称', trigger: 'blur' },
                        qq: { required: true, message: '请输入联系QQ', trigger: 'blur' },
                        email: { required: true, message: '请输入电子邮箱', trigger: 'blur' },
                        address: { required: true, message: '请输入网站地址', trigger: 'blur' },
                    }
                    
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
                // 获取通用配置
                getCommonData () {
                    this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
                    document.title = this.commonData.website_name + '-开发者入驻'
                },
                handleClick (e) {

                },
                // 应用管理开始
                // 每页展示数改变
                sizeChange1 (e) {
                    this.params1.limit = e
                    this.params1.page = 1
                    // 获取列表
                },
                // 当前页改变
                currentChange1 (e) {
                    this.params1.page = e
                },
                // 应用管理
                getData1 () {

                },
                // 应用管理结束


                // 信息设置开始
                saveInfo () {
                    this.$refs.infoRef.validate((valid) => {
                        if (valid) {
                            alert('submit!');
                        } else {
                            console.log('error submit!!');
                            return false;
                        }
                    });
                }



            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
