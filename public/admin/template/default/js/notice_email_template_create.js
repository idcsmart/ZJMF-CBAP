(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('notice-email-template-create')[0]
    Vue.prototype.lang = window.lang
    const host = location.host
    const fir = location.pathname.split('/')[1]
    const str = `${host}/${fir}/`
    new Vue({
      data () {
        return {
          formData: {
            name: '',
            subject: '',
            message: ''
          },
          rules: {
            name: [
              { required: true, message: lang.input + lang.nickname, type: 'error' },
              { validator: val => val.length <= 100, message: lang.verify3 + 100, type: 'warning' }
            ],
            subject: [
              { required: true, message: lang.input + lang.title, type: 'error' },
              { validator: val => val.length <= 100, message: lang.verify3 + 100, type: 'warning' }
            ],
            message: [{ required: true, message: lang.input + lang.content, type: 'error' }],
          },
        }
      },
      created () {

      },
      mounted () {
        this.initTemplate()
      },
      methods: {
        setContent () {
          this.formData.message = tinymce.editors['emailTemp'].getContent()
        },
        submit () {
          this.setContent()
          this.$refs.userDialog.validate().then(async res => {
            try {
              const res = await createEmailTemplate('create', this.formData)
              this.$message.success(res.data.msg)
              setTimeout(() => {
                location.href = 'notice_email_template.html'
              }, 500)
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          }, error => {
            console.log(error)
          })
        },
        initTemplate () {
          tinymce.init({
            selector: '#emailTemp',
            language_url: '/tinymce/langs/zh_CN.js',
            language: 'zh_CN',
            min_height: 400,
            width: '100%',
            plugins: 'link lists image code table colorpicker textcolor wordcount contextmenu fullpage',
            toolbar:
              'bold italic underline strikethrough | fontsizeselect | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent blockquote | undo redo | link unlink image fullpage code | removeformat',
            // images_upload_url: 'http://' + str + 'v1/upload',
            convert_urls: false,
            // images_upload_url: 'http://' + str + 'v1/upload',
            // images_upload_handler: function (blobInfo, success, failure) {
            //   // 上传图片
            //   const formData = new FormData()
            //   formData.append('image', blobInfo.blob(), blobInfo.filename())
            //   console.log('@@@@', formData)
            //   axios.post('http://' + str + 'v1/upload', formData, {
            //     'Content-Type': 'multipart/form-data',
            //     headers: {
            //       Authorization: 'Bearer' + ' ' + localStorage.getItem('backJwt')
            //     }
            //   }).then(res => {
            //     const json = {}
            //     if (res.status !== 200) {
            //       failure('HTTP Error: ' + res.msg)
            //       return
            //     }
            //     // json = JSON.parse(res)
            //     json.location = res.data.data

            //     if (!json || typeof json.location !== 'string') {
            //       failure('Invalid JSON: ' + res)
            //       return
            //     }
            //     success(json.location)
            //   })
            // }
            images_upload_handler: this.handlerAddImg
          });
        },
        handlerAddImg (blobInfo, success) {
          return new Promise((resolve, reject) => {
            const formData = new FormData()
            formData.append('file', blobInfo.blob())
            axios.post('http://' + str + 'v1/upload', formData, {
              headers: {
                Authorization: 'Bearer' + ' ' + localStorage.getItem('backJwt')
              }
            }).then(res => {
              const json = {}
              if (res.status !== 200) {
                failure('HTTP Error: ' + res.msg)
                return
              }
              // json = JSON.parse(res)
              json.location = res.data.data.image_base64

              if (!json || typeof json.location !== 'string') {
                failure('Invalid JSON: ' + res)
                return
              }
              success(json.location)
            })
            // this.$api.addPicture(formData).then(res => { // 上传api 
            //   if (res.code === 200) {
            //     resolve('https://gimg2.baidu.com/image_search/src=http%3A%2F%2Ffile03.16sucai.com%2F2017%2F1100%2F16sucai_p20161119050_0fe.JPG&refer=http%3A%2F%2Ffile03.16sucai.com&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=auto?sec=1664341021&t=7bcac9962924f3d12102b1417f6a67d7')
            //   } else {
            //     reject(res?.msg)
            //   }
            // }).catch(res => {
            //   reject(res)
            // })
          })
        },
        close () {
          location.href = 'notice_email_template.html'
        },
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
