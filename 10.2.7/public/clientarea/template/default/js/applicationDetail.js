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
      async created () {
        this.getCommonData()
        let obj = this.getUrlParams();
        if (obj) {
          this.appId = obj.id
          await this.getAppDetail()
        }
        console.log(obj);

      },
      mounted () {

      },
      updated () {
        // // 关闭loading
        // document.getElementById('mainLoading').style.display = 'none';
        // document.getElementsByClassName('template')[0].style.display = 'block'
      },
      destroyed () {

      },
      data () {
        let valiIcon = (rule, value, callback) => {
          if (!this.isUploadIcon) {
            callback(new Error('请上传应用图标'))
          } else {
            callback()
          }
        }
        let valiFile = (rule, value, callback) => {
          if (!this.isUploadFile) {
            callback(new Error('请上传应用文件'))
          } else {
            callback()
          }
        }
        return {
          params: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: 'id',
            sort: 'desc',
            keywords: '',
          },
          appId: '',
          commonData: {},
          formData: {
            name: '',
            uuid: '',
            info: '',
            type: '',
            system_type: '',
            version: '',
            description: '',
            icon: '',
            images: [],
            pay_type: 0,
            onetime: '',
            monthly: '',
            quarterly: '',
            semiannually: '',
            annually: '',
            file: '',
          },
          isUploadIcon: false,
          isUploadFile: false,
          rules: {
            name: { required: true, message: '请输入应用名称名称', trigger: 'blur' },
            system_type: { required: true, message: '请输入系统类型', trigger: 'blur' },
            uuid: { required: true, message: '应用标识不能为空', trigger: 'blur' },
            type: { required: true, message: '请输入应用分类', trigger: 'blur' },
            instruction: { required: true, message: '应用介绍不能为空', trigger: 'blur' },
            icon: { required: true, validator: valiIcon },
            file: { required: true, validator: valiFile },
            version: { required: true, message: '请输入版本', trigger: 'blur' },
            description: { required: true, message: '请输入版本说明', trigger: 'blur' },
          },
          typeList: [
            { value: 'addon', label: '插件' },
            { value: 'captcha', label: '验证码接口' },
            { value: 'certification', label: '实名接口' },
            { value: 'gateway', label: '支付接口' },
            { value: 'mail', label: '邮件接口' },
            { value: 'sms', label: '短信接口' },
            { value: 'server', label: '模块' },
            { value: 'template', label: '主题' },
            { value: 'service', label: '服务' },
          ],
          fileIconList: [],
          fileAppList: [],
          fileZipList: [],
          dialogImageUrl: "",
          dialogVisible: false
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
        getUrlParams () {
          var urlObj = {};
          if (!window.location.search) {
            return false;
          }
          var urlParams = window.location.search.substring(1);
          var urlArr = urlParams.split("&");
          for (var i = 0; i < urlArr.length; i++) {
            var urlArrItem = urlArr[i].split("=");
            urlObj[urlArrItem[0]] = urlArrItem[1];
          }
          if (Object.keys(urlObj).length > 0) {
            return urlObj;
          }
        },
        // 每页展示数改变
        sizeChange (e) {
          this.params.limit = e
          this.params.page = 1
          // 获取列表
        },
        // 当前页改变
        currentChange (e) {
          this.params.page = e

        },

        // 获取通用配置
        getCommonData () {
          this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
          document.title = this.commonData.website_name + '-应用详情'
        },
        goBack () {
          history.back()
        },
        // 应用文件上传
        beforeUploadFile (file,) {
          const isZip = file.name.endsWith('.zip');
          if (!isZip) {
            this.$message.error('请选择zip文件!');
            return false;
          }
        },

        onSuccessFile (res, file, fileList) {
          console.log(fileList);
          if (res.status == 200) {
            this.formData.uuid = 'IdcsmartApp' + parseInt(10 * Math.random())
            this.formData.file = res.data.save_name
            this.isUploadFile = true
          }
        },
        handleRemoveZip (file) {
          this.formData.file = ""
          this.formData.uuid = ""
          this.isUploadFile = false
        },
        // 应用图标上传
        beforeUpload (file) {
          let isLt = file.size / 1024 < 500 // 判断是否小于500Kb
          if (!file) return
          let imgTypeList = ['jpg', 'png', 'gif', 'jpeg']
          imageType = file.type.split('/')  // ['image', 'jpeg']
          if (!imgTypeList.includes(imageType[1])) {
            this.$message({
              message: '上传文件只能是 .jpg .gif .png .jpeg格式!',
              type: 'warning'
            })
            return false
          }
          if (!isLt) {
            this.$message({
              message: '上传文件大小不能超过 500KB!',
              type: 'warning'
            })
            return false
          }
        },
        onSuccessIcon (res, file, fileList) {
          this.formData.icon = res.data.image_url
          this.fileIconList = fileList
          this.isUploadIcon = true
        },
        handlePictureCardPreview (file) {
          this.dialogImageUrl = file.url;
          this.dialogVisible = true;

        },
        handleRemoveIcon (file, fileList) {
          this.formData.icon = ''
          this.isUploadIcon = false
        },
        onExceedIcon (files, fileList) {
          this.$message({
            message: '上传图片个数不能超过一个!',
            type: 'warning'
          })
        },
        // 应用图片上传
        onSuccessApp (res) {
          if (res.status == 200) {
            this.formData.images.push(res.data.image_url)
          }
        },
        handleRemoveApp (file) {
          let url = file.url
          let index = this.formData.images.findIndex(item => item === url)
          this.formData.images.splice(index, 1)
        },

        // 获取详情
        async getAppDetail () {
          const res = await queryAppDetailApi({ id: this.appId })
          if (res.status == 200) {
            this.isUploadIcon = true
            this.isUploadFile = true
            const { data } = res.data
            let obj = data.app
            console.log(obj);
            for (var key in obj) {
              this.formData[key] = obj[key]
            }
            this.fileAppList = obj.images.map(item => ({ name: item, url: item }))
            this.fileIconList = [{ name: obj.icon, url: obj.icon }]
            this.fileZipList = [{ name: obj.file, url: obj.file }]
          }
        },

        saveApp () {
          this.$refs.ruleForm.validate(async (valid) => {
            if (valid) {
              let API = this.appId ? changeAppApi : creaetAppApi
              console.log(API, '-------');
              try {
                this.formData.info = this.formData.instruction
                if (this.formData.pay_type == 1) {
                  this.formData.onetime = ""
                  this.formData.monthly = ""
                  this.formData.quarterly = ""
                  this.formData.semiannually = ""
                  this.formData.annually = ""
                }
                const res = await API(this.formData)
                if (res.status == 200) {
                  let str = this.appId ? '修改成功' : '创建成功'
                  this.$message.success(str)
                  this.goBack()
                }
              } catch (err) {
                this.$message.error(err.data.msg)
              }
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
