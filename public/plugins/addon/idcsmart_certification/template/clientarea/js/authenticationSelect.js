(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('template')[0]
        Vue.prototype.lang = window.lang
        new Vue({
            components: {
                asideMenu,
                topMenu,
            },
            created() {
                this.getCommonData()
                this.getCertificationInfo()
            },
            mounted() {

            },
            updated() {
                // // 关闭loading
                document.getElementById('mainLoading').style.display = 'none';
                document.getElementsByClassName('template')[0].style.display = 'block'
            },
            destroyed() {

            },
            data() {
                return {
                    certificationInfoObj: {
                        company: {},
                        person: {}
                    },
                    commonData: {},
                    authenticationType: '1', //   认证类型 1 个人  2 企业
                    custom_fieldsList: [], // 认证方式下拉框数组
                    checkedVlue: '', // 选择的认证方式
                    custom_fieldsObj: [], // 其他自定义字段
                    plugin_name: '',
                    personPluginList: [], // 个人认证方式数组
                    companyPluginList: [] // 企业认证方式数组
                }
            },
            filters: {
                formateTime(time) {
                    if (time && time !== 0) {
                        return formateDate(time * 1000)
                    } else {
                        return "--"
                    }
                }
            },
            methods: {
                clickType(val) {
                    if (val === '1' && this.certificationInfoObj.person.status === 1) {
                        return
                    }
                    if (val === '1') {
                        this.custom_fieldsList = [...this.personPluginList]
                    } else {
                        this.custom_fieldsList = [...this.companyPluginList]
                    }
                    this.authenticationType = val
                    this.checkedVlue = this.custom_fieldsList[0].value
                },
                selectChange(val) {
                    this.plugin_name = val
                },
                // 返回按钮
                backTicket() {
                    location.href = '/account.html'
                },
                // 点击下一步
                goUploadPage() {
                    if (!this.authenticationType) {
                        this.$message.warning('请先选择认证类型');
                        return
                    }
                    if (this.authenticationType === '1') {
                        location.href = `authentication_person.html?name=${this.checkedVlue}`
                    } else if (this.authenticationType === '2') {
                        location.href = `authentication_company.html?name=${this.checkedVlue}`
                    }
                },
                // 获取基础信息
                getCertificationInfo() {
                    certificationInfo().then(async (res) => {
                        this.certificationInfoObj = res.data.data
                        // 获取实名认证方式
                        await certificationPlugin().then((ress) => {
                            this.certificationPluginList = ress.data.data.list
                            this.certificationPluginList.forEach(item => {
                                const obj = {}
                                obj.value = item.name
                                obj.label = item.title
                                if (item.certification_type.includes('person')) {
                                    this.personPluginList.push(obj)
                                }
                                if (item.certification_type.includes('company')) {
                                    this.companyPluginList.push(obj)
                                }
                            })
                        })
                        if (this.certificationInfoObj.company.status === 1) {
                            location.href = `authentication_status.html?type=2`
                            return
                        }
                        if (this.certificationInfoObj.person.status === 1) {
                            this.authenticationType = '2'
                            this.custom_fieldsList = [...this.companyPluginList]
                        } else {
                            this.custom_fieldsList = [...this.personPluginList]
                        }
                        this.checkedVlue = this.custom_fieldsList[0].value ? this.custom_fieldsList[0].value : ''

                    })
                },
                // 获取通用配置
                getCommonData() {
                    getCommon().then(res => {
                        if (res.data.status === 200) {
                            this.commonData = res.data.data
                            localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
                            document.title = this.commonData.website_name + '-实名认证'
                        }
                    })
                }
            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
