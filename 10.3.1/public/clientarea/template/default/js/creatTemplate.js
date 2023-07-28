(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('template')[0]
        Vue.prototype.lang = window.lang
        var { pinyin } = pinyinPro;
        new Vue({
            components: {
                asideMenu,
                topMenu,
            },
            created() {
                this.getCommonData()
                this.getCountry()
                this.getAccountInfo()
                this.getDomainSet()
            },
            mounted() {
            },
            updated() {
                // 关闭loading
                document.getElementById('mainLoading').style.display = 'none';
                document.getElementsByClassName('template')[0].style.display = 'block'
            },
            destroyed() {

            },
            data() {
                return {
                    isAgree: false,
                    commonData: {},
                    carList: [],
                    domainConfig: {},
                    citys: [],
                    countryList: [],
                    ruleForm: {
                        cityArr: [],
                        type: 'personal',
                        zh_owner: '',
                        zh_all_name: '',
                        zh_last_name: '',
                        zh_first_name: '',
                        country: 'CN',
                        zh_province: '',
                        zh_city: '',
                        zh_address: '',
                        postal_code: '',
                        phone: '',
                        email: '',
                        en_owner: '',
                        en_all_name: '',
                        en_last_name: '',
                        en_first_name: '',
                        en_province: '',
                        en_city: '',
                        en_address: '',
                        idtype: '',
                        idnum: '',
                    },
                    subLoading: false,
                    rules: {
                        type: [
                            { required: true, message: lang.template_text46, trigger: 'change' }
                        ],
                        zh_owner: [
                            { required: true, message: lang.template_text47, trigger: 'change' }
                        ],
                        zh_all_name: [
                            { required: true, message: lang.template_text48, trigger: 'change' }
                        ],
                        zh_last_name: [
                            { required: true, message: lang.template_text49, trigger: 'change' }
                        ],
                        zh_first_name: [
                            { required: true, message: lang.template_text50, trigger: 'change' }
                        ],
                        cityArr: [
                            { required: true, message: lang.template_text52, trigger: 'change' }
                        ],
                        postal_code: [
                            { required: true, message: lang.template_text53, trigger: 'change' }
                        ],
                        phone: [
                            { required: true, message: lang.template_text54, trigger: 'change' }
                        ],
                        email: [
                            { required: true, message: lang.template_text55, trigger: 'change' }
                        ],
                        en_owner: [
                            { required: true, message: lang.template_text56, trigger: 'change' }
                        ],
                        en_all_name: [
                            { required: true, message: lang.template_text57, trigger: 'change' }
                        ],
                        en_last_name: [
                            { required: true, message: lang.template_text58, trigger: 'change' }
                        ],
                        en_first_name: [
                            { required: true, message: lang.template_text59, trigger: 'change' }
                        ],
                        zh_address: [
                            { required: true, message: lang.template_text60, trigger: 'change' }
                        ],
                        en_address: [
                            { required: true, message: lang.template_text61, trigger: 'change' }
                        ],
                        idtype: [
                            { required: true, message: lang.template_text62, trigger: 'change' }
                        ],
                        idnum: [
                            { required: true, message: lang.template_text63, trigger: 'change' }
                        ]
                    },
                    checked: false,
                    options: [],
                    accountInfo: {},
                    selectIdTypeOption: [
                        { label: lang.id_type_SFZ, value: 'SFZ' },
                        { label: lang.id_type_HZ, value: 'HZ' },
                        { label: lang.id_type_GAJMTX, value: 'GAJMTX' },
                        { label: lang.id_type_TWJMTX, value: 'TWJMTX' },
                        { label: lang.id_type_WJLSFZ, value: 'WJLSFZ' },
                        { label: lang.id_type_GAJZZ, value: 'GAJZZ' },
                    ],
                    perTypeList: [
                        { label: lang.id_type_SFZ, value: 'SFZ' },
                        { label: lang.id_type_HZ, value: 'HZ' },
                        { label: lang.id_type_GAJMTX, value: 'GAJMTX' },
                        { label: lang.id_type_TWJMTX, value: 'TWJMTX' },
                        { label: lang.id_type_WJLSFZ, value: 'WJLSFZ' },
                        { label: lang.id_type_GAJZZ, value: 'GAJZZ' },
                    ],
                    entTypeList: [
                        { label: lang.id_type_ORG, value: 'ORG' },
                        { label: lang.id_type_YYZZ, value: 'YYZZ' },
                        { label: lang.id_type_TYDM, value: 'TYDM' },
                        { label: lang.id_type_BDDM, value: 'BDDM' },
                        { label: lang.id_type_JDDWFW, value: 'JDDWFW' },
                        { label: lang.id_type_SYDWFR, value: 'SYDWFR' },
                        { label: lang.id_type_WGCZJG, value: 'WGCZJG' },
                        { label: lang.id_type_SHTTFR, value: 'SHTTFR' },
                        { label: lang.id_type_ZJCS, value: 'ZJCS' },
                        { label: lang.id_type_MBFQY, value: 'MBFQY' },
                        { label: lang.id_type_JJHFR, value: 'JJHFR' },
                        { label: lang.id_type_LSZY, value: 'LSZY' },
                        { label: lang.id_type_WGZHWH, value: 'WGZHWH' },
                        { label: lang.id_type_WLCZJG, value: 'WLCZJG' },
                        { label: lang.id_type_SFJD, value: 'SFJD' },
                        { label: lang.id_type_SHFWJG, value: 'SHFWJG' },
                        { label: lang.id_type_MBXXBX, value: 'MBXXBX' },
                        { label: lang.id_type_YLJGZY, value: 'YLJGZY' },
                        { label: lang.id_type_JWJG, value: 'JWJG' },
                        { label: lang.id_type_GZJGZY, value: 'GZJGZY' },
                        { label: lang.id_type_BJWSXX, value: 'BJWSXX' },
                        { label: lang.id_type_QTTYDM, value: 'QTTYDM' },
                    ]
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
            watch: {
                'ruleForm.zh_owner': function (val) {
                    if (this.ruleForm.type === 'personal') {
                        this.ruleForm.zh_all_name = val
                    }
                    this.ruleForm.en_owner = this.firstUpperCase(pinyin(val, { toneType: 'none', nonZh: 'removed' }))
                },
                'ruleForm.zh_all_name': function (val) {
                    if (this.ruleForm.type === 'personal') {
                        this.ruleForm.zh_owner = val
                    }
                    this.ruleForm.en_all_name = this.firstUpperCase(pinyin(val, { toneType: 'none' }))
                    // 拆分名字，二三字默认 第一个是姓  四字以上默认 一二是姓
                    if (val.length > 3) {
                        this.ruleForm.zh_last_name = val.slice(0, 2)
                        this.ruleForm.zh_first_name = val.slice(2)
                    } else {
                        this.ruleForm.zh_last_name = val.slice(0, 1)
                        this.ruleForm.zh_first_name = val.slice(1)
                    }
                },
                'ruleForm.zh_last_name': function (val) {
                    this.ruleForm.en_last_name = this.firstUpperCase(pinyin(val, { toneType: 'none', nonZh: 'removed' }))
                },
                'ruleForm.zh_first_name': function (val) {
                    this.ruleForm.en_first_name = this.firstUpperCase(pinyin(val, { toneType: 'none', nonZh: 'removed' }))
                },
                'ruleForm.zh_province': function (val) {
                    this.ruleForm.en_province = this.firstUpperCase(pinyin(val, { toneType: 'none' }))
                },
                'ruleForm.zh_city': function (val) {
                    this.ruleForm.en_city = this.firstUpperCase(pinyin(val, { toneType: 'none' }))
                },
                'ruleForm.zh_address': function (val) {
                    this.ruleForm.en_address = this.firstUpperCase(pinyin(val, { toneType: 'none' }))
                },
                'ruleForm.cityArr': function (val) {
                    this.ruleForm.zh_province = val[1]
                    this.ruleForm.zh_city = val[2]
                    this.ruleForm.en_province = this.firstUpperCase(pinyin(val[1], { toneType: 'none' }))
                    this.ruleForm.en_city = this.firstUpperCase(pinyin(val[2], { toneType: 'none' }))
                }
            },
            computed: {
                calcCountry() {
                    return name => {
                        return this.countryList.filter(item => item.iso === name)[0]?.name_zh
                    }
                }
            },
            methods: {
                // 首字母大写
                firstUpperCase(str) {
                    return str.toLowerCase().replace(/( |^)[a-z]/g, (L) => L.toUpperCase());
                },
                goUrl() {
                    window.open(this.domainConfig.domain_information_service_agreement_url)
                },
                // 获取域名设置
                getDomainSet() {
                    domainSetting().then((res) => {
                        this.domainConfig = res.data.data
                    })
                },
                submitForm(formName) {
                    this.$refs[formName].validate((valid) => {
                        if (!valid) {
                            return false;
                        }
                        if (!this.isAgree) {
                            this.$message.error(lang.template_text64)
                            return false
                        }
                        this.subLoading = true
                        templateAdd(this.ruleForm).then((res) => {
                            this.$message.success(res.data.msg)
                            this.subLoading = false
                            // 返回上一页
                            window.history.go(-1)
                        }).catch((err) => {
                            this.$message.error(err.data.msg)
                            this.subLoading = false
                        })

                    });
                },
                // 获取账户详情
                getAccountInfo() {
                    account().then((res) => {
                        this.accountInfo = res.data.data.account
                    })
                },
                typeChange(val) {
                    if (this.ruleForm.type === val) return
                    this.ruleForm.type = val
                    this.ruleForm.idtype = ''
                    if (val === 'personal') {
                        this.selectIdTypeOption = this.perTypeList
                    } else {
                        this.selectIdTypeOption = this.entTypeList
                    }
                },
                handleChange(value) {
                    console.log(value);
                },
                useAccont(val) {
                    if (val) {
                        this.ruleForm.zh_owner = this.accountInfo.username
                        this.ruleForm.email = this.accountInfo.email
                        this.ruleForm.phone = this.accountInfo.phone
                        this.ruleForm.zh_address = this.accountInfo.address
                    } else {
                        this.ruleForm.zh_owner = ''
                        this.ruleForm.email = ''
                        this.ruleForm.phone = ''
                        this.ruleForm.zh_address = ''
                    }
                },
                getCountry() {
                    this.citys = [
                        {
                            value: '中国',
                            label: '中国',
                            children: citys.map((item) => {
                                return {
                                    value: item.label,
                                    label: item.label,
                                    children: item.children.map((item2) => {
                                        return {
                                            value: item2.label,
                                            label: item2.label,
                                        }
                                    })
                                }
                            })
                        }
                    ]
                },
                goBack() {
                    window.history.go(-1)
                },
                // 获取通用配置
                getCommonData() {
                    this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
                    document.title = this.commonData.website_name + '-' + lang.template_text1
                },

            },
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
