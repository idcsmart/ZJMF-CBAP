// const { json } = require("stream/consumers");

(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const documents = document.getElementsByClassName("document")[0];
    Vue.prototype.lang = window.lang;
    const host = location.host
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
          id: "",
          total: 100,
          uploadHeaders: {
            Authorization: "Bearer" + " " + localStorage.getItem("backJwt"),
          },
          pageSizeOptions: [20, 50, 100],
          detialform: {},
          attachment: [],
          files: [],
          uploadTip: "",
          typelist: [],
          requiredRules: {
            title: [{ required: true, message: lang.input + lang.doc_name }],
            addon_idcsmart_help_type_id: [
              { required: true, message: "文档类型必填" },
            ],
          },
          uploadUrl: 'http://' + str + 'v1/upload'
        };
      },

      methods: {
        initTemplate () {
          tinymce.init({
            selector: '#tiny',
            language_url: '/tinymce/langs/zh_CN.js',
            language: 'zh_CN',
            min_height: 400,
            width: '100%',
            plugins: 'link lists image code table colorpicker textcolor wordcount contextmenu fullpage',
            toolbar:
              'bold italic underline strikethrough | fontsizeselect | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent blockquote | undo redo | link unlink image fullpage code | removeformat',
            images_upload_url: 'http://' + str + 'v1/upload',
            convert_urls: false,
            images_upload_handler: this.handlerAddImg
          });
        },
        handlerAddImg (blobInfo, success, failure) {
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
        formatResponse(res) {
          console.log(res, "res");
          if (res.status != 200) {
            return { error: "上传失败，请重试" };
          }
          return { save_name: res.data.save_name, url: res.url };
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
          console.log(
            this.detialform,
            tinyMCE.activeEditor.getContent(),
            "detialform"
          );
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
          console.log(this.files, "this.attachment");
          this.detialform.attachment = arr;
          this.$refs.myform.validate(this.requiredRules).then((res) => {
            console.log(res, "validate");
            if (res === true) {
              if (!this.detialform.content) {
                this.$message.warning("内容必填！");
                return;
              }
              if (this.id) {
                if (hidden === 1) {
                  //保存编辑
                  this.detialform.hidden = 1;
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
                        location.href = "index.html";
                      }, 1000);
                    }
                  })
                  .catch((err) => {
                    this.getdetialcon();
                    this.$message.error(err.data.msg);
                  });
              } else {
                console.log(hidden, "hidden");
                if (hidden === 1) {
                  //保存新增
                  this.detialform.hidden = 1;
                } else {
                  //提交新增
                  this.detialform.hidden = 0;
                }
                addhelp(this.detialform)
                  .then((res) => {
                    if (res.data.status === 200) {
                      console.log(res, "res");
                      this.$message.success(res.data.msg);
                      setTimeout(() => {
                        location.href = "index.html";
                      }, 1000);
                    }
                  })
                  .catch((err) => {
                    this.$message.error(err.data.msg);
                  });
              }
            }
          });
        },
        getdetialcon() {
          helpdetial({ id: this.id }).then((res) => {
            if (res.data.status === 200) {
              this.detialform = res.data.data.help;
              tinyMCE.activeEditor.setContent(this.detialform.content);
              // this.attachment = res.data.data.news.attachment;
              this.files = res.data.data.help.attachment;
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
        cancle() {
          window.history.go(-1);
        },
        //防抖
        debounce(fn, ms) {
          //fn:要防抖的函数 ms:时间
          let timerId;
          return function () {
            timerId && clearTimeout(timerId);

            timerId = setTimeout(() => {
              fn.apply(this, arguments);
            }, ms);
          };
        },
      },
      created() {
        console.log(window.location.search.slice(1), "pathname");
        this.id = window.location.search.slice(1).split("=")[1];
        console.log(this.id, "this.id");
        if (this.id) {
          // helpdetial({ id: this.id }).then((res) => {
          //   if (res.data.status === 200) {
          //     this.detialform = res.data.data.help;
          //     tinyMCE.activeEditor.setContent(this.detialform.content);
          //     this.attachment = res.data.data.news.attachment;
          //     let attachment = [];
          //     this.attachment.map((item) => {
          //       if (item.url || item.save_name) {
          //         return;
          //       }
          //       let obj = {};
          //       obj.url = item;
          //       obj.name = item;
          //       attachment.push(obj);
          //     });
          //     this.attachment = attachment;
          //     console.log(this.detialform, this.attachment, "this.detialform");
          //   }
          // });
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
