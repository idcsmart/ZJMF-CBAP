(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('template')[0]
        Vue.prototype.lang = window.lang
        new Vue({
            components: {
                asideMenu,
                topMenu,
                pagination,
            },
            created() {
                this.plugin_name = location.href.split('?')[1].split('=')[1]
                this.getCommonData()
                this.getcustom_fields()
                this.getCertificationInfo()
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
                    commonData: {},
                    dialogVisible: false,
                    sunmitBtnLoading: false,
                    certificationInfoObj: {},
                    dialogImageUrl: '',
                    uploadTipsText1: '',
                    uploadTipsText2: '',
                    plugin_name: '',// 实名接口
                    certificationPerson: { // 个人实名认证信息对象
                        card_name: '', //姓名
                        card_type: 1, // 证件类型:1大陆,0非大陆
                        card_number: '',// 证件号码
                        phone: '', // 手机号  
                        custom_fields: {}
                    },
                    custom_fieldsObj: [], // 其他自定义字段
                    img_one: '', // 身份证正面照
                    img_two: '', // 身份证反面照
                    personRules: {
                        card_name: [
                            { required: true, message: '请输入姓名', trigger: 'blur' },
                        ],
                        card_type: [
                            { required: true, message: '请选择证件类型', trigger: 'blur' },
                        ],
                        card_number: [
                            { required: true, message: '请输入证件号码', trigger: 'blur' },
                        ],
                    },
                    id_card_type: [
                        {
                            label: '身份证',
                            value: 1
                        },
                        {
                            label: '港澳居住证',
                            value: 0
                        },
                        {
                            label: '港澳同行证',
                            value: 0
                        },
                        {
                            label: '台湾居住证',
                            value: 0
                        },
                        {
                            label: '台湾通行证',
                            value: 0
                        },

                    ],
                    custom_fileList: [], // 自定义上传列表
                    filelist: [],
                    card_one_fileList: [],
                    card_two_fileList: [],
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
                // 返回按钮
                backTicket() {
                    location.href = 'account.html'
                },

                onUpload(file, val) {
                    this.sunmitBtnLoading = true
                    if (val === 'img_one') {
                        this.img_one = 'padding'
                    }
                    if (val === 'img_two') {
                        this.img_two = 'padding'
                    }
                },
                // 身份证第一张上传成功回调
                handleSuccess1(response, file, fileList) {
                    this.sunmitBtnLoading = false
                    if (response.status === 200) {
                        this.img_one = response.data.save_name
                        this.uploadTipsText1 = ''
                    } else {
                        this.$message.warning(response.msg)
                        this.uploadTipsText1 = response.msg
                        this.card_one_fileList = []
                        this.img_one = ''
                    }
                },
                // 身份证第一张删除
                handleRemove1(file, fileList) {
                    this.card_one_fileList = []
                    this.img_one = ''
                },
                // 身份证第二张上传成功回调
                handleSuccess2(response, file, fileList) {
                    this.sunmitBtnLoading = false
                    if (response.status === 200) {
                        this.img_two = response.data.save_name
                        this.uploadTipsText2 = ''
                    } else {
                        this.$message.warning(response.msg)
                        this.uploadTipsText2 = response.msg
                        this.card_two_fileList = []
                        this.img_two = ''
                    }
                },
                // 自定义上传文件相关 
                handleSuccess(response, file, fileList, item) {
                    this.sunmitBtnLoading = false
                    if (response.status === 200) {
                        this.custom_fileList.push(response.data.save_name)
                        this.certificationPerson.custom_fields[`${item.field}`] = this.custom_fileList
                    }
                },
                // 自定义上传删除
                beforeRemove(file, fileList) {
                    // 获取到删除的 save_name
                    let save_name = file.response.data.save_name
                    this.custom_fileList = this.custom_fileList.filter(item => {
                        return item != save_name
                    })
                },
                // 身份证第二张删除
                handleRemove2() {
                    this.card_two_fileList = []
                    this.img_two = ''
                },
                // 预览
                handlePictureCardPreview(file) {
                    this.dialogImageUrl = file.url;
                    this.dialogVisible = true;
                },
                // 获取自定义字段
                getcustom_fields() {
                    custom_fields({ name: this.plugin_name }).then((res) => {
                        this.custom_fieldsObj = res.data.data.custom_fields
                    })
                },
                // 获取配置信息
                getCertificationInfo() {
                    certificationInfo().then(async (res) => {
                        this.certificationInfoObj = res.data.data
                    })
                },
                // 个人认证提交
                personSumit() {
                    this.$refs.certificationPerson.validate(async (valid) => {
                        this.custom_fieldsObj.forEach((item) => {
                            if (item.required && !this.certificationPerson.custom_fields[item.field]) {
                                valid = false
                            }
                        })
                        if (!valid) {
                            this.$message.warning("请填写带*的必填项!")
                            return
                        }
                        if (this.certificationInfoObj.certification_upload === '1') {
                            if (this.img_one == '') {
                                this.$message.warning("请上传身份证人像页!")
                                return
                            }
                            if (this.img_two == '') {
                                this.$message.warning("请上传身份证国徽页!")
                                return
                            }
                        }
                        this.sunmitBtnLoading = true
                        this.certificationPerson.img_one = this.img_one
                        this.certificationPerson.img_two = this.img_two
                        this.certificationPerson.plugin_name = this.plugin_name
                        uploadPerson(this.certificationPerson).then((ress) => {
                            if (ress.data.status === 200) {
                                location.href = 'authentication_thrid.html?type=1'
                            }
                        }).catch((err) => {
                            this.$message.warning(err.data.msg)
                        }).finally(() => {
                            this.sunmitBtnLoading = false
                        })
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
