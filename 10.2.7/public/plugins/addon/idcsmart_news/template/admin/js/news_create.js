// const { json } = require("stream/consumers");

(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const documents = document.getElementsByClassName("newscreat")[0];
    Vue.prototype.lang = window.lang;
    const host = location.origin
    const fir = location.pathname.split('/')[1]
    const str = `${host}/${fir}/`
    new Vue({
      data() {
        return {
          message: "template...",
          params: {
            keywords: "",
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
          },
          uploadHeaders: {
            Authorization: "Bearer" + " " + localStorage.getItem("backJwt"),
          },
          id: "",
          total: 100,
          pageSizeOptions: [20, 50, 100],
          detialform: {
            title: '',
            addon_idcsmart_news_type_id: '',
            keywords: '',
            content: ''
          },
          attachment: [],
          files: [],
          uploadTip: "",
          typelist: [],
          requiredRules: {
            title: [{ required: true, message: "文档名称必填" }],
            addon_idcsmart_news_type_id: [
              { required: true, message: "文档类型必填" },
            ],
          },
          uploadUrl: str + 'v1/upload'
        };
      },

      methods: {
        transformHtml(str) {
          const temp = str && str.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').
            replace(/&amp;lt;/g, '<').replace(/&amp;gt;/g, '>').replace(/ &amp;lt;/g, '<').replace(/&amp;gt; /g, '>')
            .replace(/&amp;gt; /g, '>').replace(/&amp;quot;/g, '"').replace(/&amp;amp;nbsp;/g, ' ').replace(/&amp;#039;/g, '\'');
          return temp
        },
        initTemplate() {
          tinymce.init({
            selector: '#tiny',
            language_url: '/tinymce/langs/zh_CN.js',
            language: 'zh_CN',
            min_height: 400,
            width: '100%',
            plugins: 'link lists image code table colorpicker textcolor wordcount contextmenu fullpage',
            toolbar:
              'bold italic underline strikethrough | fontsizeselect | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent blockquote | undo redo | link unlink image fullpage code | removeformat',
            images_upload_url: str + 'v1/upload',
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
        handlerAddImg(blobInfo, success, failure) {
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
                failure('HTTP Error: ' + res.data.msg)
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
        // 切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.params.keywords = "";
        },
        //上传失败
        handleFail({ file }) {
          this.$message.error(`文件 ${file.name} 上传失败`);
        },
        //文档类型
        async gettype() {
          let resdata = await gethelptype();
          this.typelist = resdata.data.data.list;
          console.log(this.resdata, " this.typelist");
        },
        formatResponse(res) {
          console.log(res, this.files, "res");
          if (res.status != 200) {
            return { error: res.msg };
          }
          return {
            save_name: res.data.save_name,
          };
        },
        //文件上传成功
        onSuccess() { },

        //上传文件之前
        beforeUploadfile(e) {
          let isrepeat = false;
          this.files.map((item) => {
            if (item.name === e.name) {
              console.log(1111);
              this.$message.error("请勿重复上传文件！");
              isrepeat = true;
            }
          });
          return !isrepeat;
        },
        //删除上传文件
        delfiles(name) {
          let arr = [];
          this.files.map((item) => {
            if (item.name !== name) {
              arr.push(item);
            }
          });
          this.files = arr;
          console.log(this.files, "delfiles");
        },
        // 上传附件-进度
        uploadProgress(val) {
          if (val.percent) {
            this.uploadTip = "uploaded" + val.percent + "%";
            if (val.percent === 100) {
              this.uploadTip = "";
            }
          }
        },
        submit(hidden) {
          this.detialform.content = tinyMCE.activeEditor.getContent();
          let arr = [];
          this.files.map((item) => {
            if (item.response) {
              arr.push(item.response.save_name);
            } else {
              arr.push(item.save_name);
            }
          });
          // this.files = arr;
          this.detialform.attachment = arr;
          this.$refs.myform.validate(this.requiredRules).then((res) => {
            if (res === true) {
              if (!this.detialform.content) {
                this.$message.warning("内容必填！");
                return;
              }
              const srcReg = /src=[\'\"]?([^\'\"]*)[\'\"]?/i;
              const arr = this.detialform.content.match(srcReg);
              if (arr !== null && arr.length > 0) {
                this.detialform.img = arr[1]
              }
              if (this.id) {
                if (hidden === 1) {
                  //保存编辑
                  this.detialform.hidden = hidden;
                } else {
                  //提交编辑
                  this.detialform.hidden = 0;
                }
                edithelp({ ...this.detialform, id: this.id })
                  .then((res) => {
                    if (res.data.status === 200) {
                      console.log(res, "res");
                      this.$message.success(res.data.msg);
                      setTimeout(() => {
                        location.href = "index.htm";
                      }, 500);
                    }
                  })
                  .catch((err) => {
                    this.getdetialcon();
                    this.$message.error(err.data.msg);
                  });
              } else {
                if (hidden === 1) {
                  this.detialform.hidden = hidden;
                } else {
                  this.detialform.hidden = 0;
                }
                addhelp(this.detialform).then((res) => {
                  if (res.data.status === 200) {
                    console.log(res, "res");
                    this.$message.success(lang.publish + lang.success);
                    setTimeout(() => {
                      location.href = "index.htm";
                    }, 500);
                  }
                }).catch((err) => {
                  this.$message.error(err.data.msg);
                });
              }
            }
          });
        },
        getdetialcon() {
          helpdetial({ id: this.id }).then((res) => {
            if (res.data.status === 200) {
              let obj = res.data.data.news;
              obj.content = this.transformHtml(obj.content)
              Object.assign(this.detialform, obj)
              tinymce.editors['tiny'].setContent(this.detialform.content)
              //  tinyMCE.activeEditor.setContent(this.detialform.content);
              // this.attachment = res.data.data.news.attachment;
              this.files = res.data.data.news.attachment;
              let arr = [];
              this.files.map((item) => {
                let obj = {};
                obj.name = item.split("^")[1];
                obj.save_name = item.split("upload/")[1];
                arr.push(obj);
              });
              this.files = arr;
              console.log(this.files, "this.detialform");
            }
          });
        },
        save() {
          this.submit(1);
        },
        viewNew() {
          this.$refs.myform.validate(this.requiredRules).then((res) => {
            if (res === true) {
              const arr = []
              this.files.map((item) => {
                if (item.response) {
                  arr.push(item.response.save_name);
                } else {
                  arr.push(item.save_name);
                }
              })
              const viewNewObjData = {
                title: this.detialform.title,
                keywords: this.detialform.keywords,
                content: tinyMCE.activeEditor.getContent(),
                attachment: arr
              }
              sessionStorage.viewNewObjData = JSON.stringify(viewNewObjData)
              window.open('/newsView.htm')
            }
          })
        },
        cancle() {
          window.history.go(-1);
        },
      },
      created() {
        console.log(window.location.search.slice(1), "pathname");
        this.id = window.location.search.slice(1).split("=")[1];
        console.log(this.id, "this.id");
        if (this.id) {
          this.getdetialcon();
        }
        this.gettype();
      },
      mounted() {
        this.initTemplate()
      },
    }).$mount(documents);
    typeof old_onload == "function" && old_onload();
  };
})(window);
