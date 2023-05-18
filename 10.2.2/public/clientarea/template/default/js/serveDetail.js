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
      created () {
        this.getCommonData()
        let obj = this.getUrlParams()
        if (obj) {
          this.serveId = obj.id
          this.getServeDetail()
        }
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
          commonData: {},
          serveId: '',
          formData: {
            name: '',
            system_type: '',
            instruction: "",
            pay_type: 0,
            onetime: '',
            monthly: '',
            quarterly: '',
            semiannually: '',
            annually: '',
            icon: '',
            images: []
          },
          isUploadIcon: false,
          rules: {
            name: { required: true, message: '请输入活动名称', trigger: 'blur' },
            system_type: { required: true, message: '请输入系统类型', trigger: 'blur' },
            instruction: { required: true, message: '请输入应用分类', trigger: 'blur' },
            icon: { required: true, validator: valiIcon },
            info: { required: true, message: '请输入服务介绍', trigger: 'blur' },
          },
          fileIconList: [],
          fileImagesList: [],
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
        async getServeDetail () {
          const res = await queryServeDetailApi({ id: this.serveId })
          if (res.status == 200) {
            this.isUploadIcon = true
            const { data } = res.data
            let obj = data.service
            console.log(obj);
            for (var key in obj) {
              this.formData[key] = obj[key]
            }
            this.formData.info = obj.instruction
            this.fileIconList = [{ name: obj.icon, url: obj.icon }]
            this.fileImagesList = obj.images.map(item => ({ name: item, url: item }))
          }
        },
        goBack () {
          history.back()
        },
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
        onSuccessIcon (response, file, fileList) {
          const res = response
          if (res.status == 200) {
            this.formData.icon = res.data.image_url
            this.fileIconList = fileList
            this.isUploadIcon = true
          }
        },
        handlePictureCardPreview (file) {
          this.dialogImageUrl = file.url;
          this.dialogVisible = true;

        },
        handleRemoveICon (file, fileList) {
          this.formData.icon = ''
          this.isUploadIcon = false
        },
        onExceedIcon (files, fileList) {
          this.$message({
            message: '上传图片个数不能超过一个!',
            type: 'warning'
          })
        },

        // 服务图片上传
        onSuccessImages (response, file, fileList) {
          if (response.status == 200) {
            const data = response.data
            const obj = {}
            obj.name = data.save_name
            obj.url = data.image_url
            this.formData.images.push(data.image_url)
            this.fileImagesList.push((obj))
          }
        },
        handleRemoveImages (file, fileList) {
          let url = file.url
          let index = this.formData.images.findIndex(item => item === url)
          this.formData.images.splice(index, 1)
        },
        beforeUploadImags (file) {
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
        },
        saveApp () {
          this.$refs.ruleForm.validate(async (valid) => {
            if (valid) {
              try {
                let API = this.serveId ? changeServeApi : createServeApi
                let str = this.serveId ? '修改成功' : '创建成功'
                this.formData.instruction = this.formData.info
                const res = await API(this.formData)
                if (res.status == 200) {
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
