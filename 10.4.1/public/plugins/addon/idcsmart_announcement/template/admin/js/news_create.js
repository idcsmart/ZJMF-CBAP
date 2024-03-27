// const { json } = require("stream/consumers");

(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const documents = document.getElementsByClassName("newscreat")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    const host = location.origin
    const fir = location.pathname.split('/')[1]
    const str = `${host}/${fir}/`
    new Vue({
      components: {
        comTinymce,
        comConfig
      },
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
            addon_idcsmart_announcement_type_id: '',
            keywords: '',
            content: ''
          },
          attachment: [],
          files: [],
          uploadTip: "",
          typelist: [],
          requiredRules: {
            title: [{ required: true, message: `${lang.input}${lang.file_name}` }],
            addon_idcsmart_announcement_type_id: [
              { required: true, message: `${lang.input}${lang.file_type}` },
            ],
          },
          uploadUrl: str + 'v1/upload',
          submitLoading: false
        };
      },

      methods: {
        transformHtml(str) {
          const temp = str && str.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').
            replace(/&amp;lt;/g, '<').replace(/&amp;gt;/g, '>').replace(/ &amp;lt;/g, '<').replace(/&amp;gt; /g, '>')
            .replace(/&amp;gt; /g, '>').replace(/&amp;quot;/g, '"').replace(/&amp;amp;nbsp;/g, ' ').replace(/&amp;#039;/g, '\'');
          return temp
        },
        // 切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.params.keywords = "";
        },
        //上传失败
        handleFail({ file }) {
          this.$message.error(lang.upload_fail);
        },
        //文档类型
        async gettype() {
          let resdata = await gethelptype();
          this.typelist = resdata.data.data.list;
        },
        formatResponse(res) {
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
              this.$message.error(lang.dont_upload_repeat_file);
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
          this.detialform.content = this.$refs.tinymce.getContent();
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
              const srcReg = /src=[\'\"]?([^\'\"]*)[\'\"]?/i;
              const arr = this.detialform.content.match(srcReg);
              if (arr !== null && arr.length > 0) {
                this.detialform.img = arr[1]
              }
              this.submitLoading = true
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
                        this.submitLoading = false;
                        location.href = "index.htm";
                      }, 500);
                    }
                  })
                  .catch((err) => {
                    this.submitLoading = false;
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
                    this.submitLoading = false;
                    this.$message.success(`${lang.publish}${lang.success}`);
                    setTimeout(() => {
                      this.submitLoading = false;
                      location.href = "index.htm";
                    }, 500);
                  }
                }).catch((err) => {
                  this.submitLoading = false;
                  this.$message.error(err.data.msg);
                });
              }
            }
          });
        },
        getdetialcon() {
          helpdetial({ id: this.id }).then((res) => {
            if (res.data.status === 200) {
              let obj = res.data.data.announcement;
              obj.content = this.transformHtml(obj.content)
              Object.assign(this.detialform, obj)
              this.$refs.tinymce.setContent(this.detialform.content);
              //  tinyMCE.activeEditor.setContent(this.detialform.content);
              // this.attachment = res.data.data.news.attachment;
              this.files = res.data.data.announcement.attachment;
              let arr = [];
              this.files.map((item) => {
                let obj = {};
                obj.name = item.split("^")[1];
                obj.save_name = item.split("upload/")[1];
                arr.push(obj);
              });
              this.files = arr;
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
        this.id = window.location.search.slice(1).split("=")[1];
        if (this.id) {
          this.getdetialcon();
        }
        this.gettype();
      },
    }).$mount(documents);
    typeof old_onload == "function" && old_onload();
  };
})(window);
