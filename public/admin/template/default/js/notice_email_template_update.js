(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('notice-email-template-create')[0]
    Vue.prototype.lang = window.lang
    const host = location.origin
    const fir = location.pathname.split('/')[1]
    const str = `${host}/${fir}/`
    new Vue({
      data () {
        return {
          formData: {
            id: '',
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
        this.formData.id = location.href.split('?')[1].split('=')[1]
        this.getEmailDetail()
      },
      mounted () {
        this.initTemplate()
        document.title = lang.email_notice + '-' + lang.template_manage + '-' + localStorage.getItem('back_website_name')
      },
      computed: {
        calStr () {
          return (str) => {
            const temp = str && str.replace(/\\n/g, '').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').
              replace(/&amp;lt;/g, '<').replace(/&amp;gt;/g, '>').replace(/ &amp;lt;/g, '<').replace(/&amp;gt; /g, '>')
              .replace(/&amp;gt; /g, '>').replace(/&amp;quot;/g, '"').replace(/&amp;amp;nbsp;/g, ' ').replace(/&amp;#039;/g, '\'');
              return temp
          }
        }
      },
      methods: {
        async getEmailDetail () {
          try {
            const res = await getEmailTemplateDetail(this.formData.id)
            Object.assign(this.formData, res.data.data.email_template)
           // tinymce.editors['emailTemp'].setContent(this.formData.message)
          } catch (error) {
            console.log(error)
          }
        },
        setContent () {
          this.formData.message = tinymce.editors['emailTemp'].getContent()
        },

        submit () {
          this.setContent()
          this.$refs.userDialog.validate().then(async res => {
            try {
              const res = await createEmailTemplate('update', this.formData)
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
            plugins: 'link lists image code table colorpicker textcolor wordcount contextmenu fullpage paste',
            paste_data_images: true,
            toolbar:
              'bold italic underline strikethrough | fontsizeselect | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent blockquote | undo redo | link unlink image fullpage code | removeformat',
            images_upload_url:  str + 'v1/upload',
            images_upload_handler: this.handlerAddImg,
            content_css: '../css/setting.css',
          });
        },
        handlerAddImg (blobInfo, success, failure) {
          return new Promise((resolve, reject) => {
            const formData = new FormData()
            formData.append('file', blobInfo.blob())
            axios.post(str + 'v1/upload', formData, {
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
              json.location = res.data.data?.image_url

              if (!json || typeof json.location !== 'string') {
                failure('Error:' + res.data.msg)
                return
              }
              success(json.location)
            })
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
