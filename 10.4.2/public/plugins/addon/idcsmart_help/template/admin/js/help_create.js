(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const documents = document.getElementsByClassName("document")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}/`;
    new Vue({
      components: {
        comTinymce,
        comConfig,
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
          id: "",
          total: 100,
          uploadHeaders: {
            Authorization: "Bearer" + " " + localStorage.getItem("backJwt"),
          },
          pageSizeOptions: [20, 50, 100],
          detialform: {
            id: "",
            title: "",
            addon_idcsmart_help_type_id: "",
            content: "",
            keywords: "",
            cron_release: false,
            cron_release_time: "",
          },
          attachment: [],
          files: [],
          uploadTip: "",
          typelist: [],
          requiredRules: {
            title: [{ required: true, message: lang.input + lang.doc_name }],
            addon_idcsmart_help_type_id: [
              { required: true, message: lang.help_text21 },
            ],
          },
          uploadUrl: str + "v1/upload",
          submitLoading: false,
        };
      },
      methods: {
        transformHtml(str) {
          const temp =
            str &&
            str
              .replace(/&lt;/g, "<")
              .replace(/&gt;/g, ">")
              .replace(/&quot;/g, '"')
              .replace(/&amp;lt;/g, "<")
              .replace(/&amp;gt;/g, ">")
              .replace(/ &amp;lt;/g, "<")
              .replace(/&amp;gt; /g, ">")
              .replace(/&amp;gt; /g, ">")
              .replace(/&amp;quot;/g, '"')
              .replace(/&amp;amp;nbsp;/g, " ")
              .replace(/&amp;#039;/g, "'");
          return temp;
        },
        viewNew() {
          this.$refs.myform.validate(this.requiredRules).then((res) => {
            if (res === true) {
              const arr = [];
              this.files.map((item) => {
                if (item.response) {
                  arr.push(item.response.save_name);
                } else {
                  arr.push(item.save_name);
                }
              });
              const viewNewObjData = {
                title: this.detialform.title,
                keywords: this.detialform.keywords,
                content: tinyMCE.activeEditor.getContent(),
                attachment: arr,
              };
              sessionStorage.viewNewObjData = JSON.stringify(viewNewObjData);
              window.open("/newsView.htm");
            }
          });
        },
        // 切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.params.keywords = "";
        },
        //上传失败
        handleFail({ file }) {
          // this.$message.error(`文件 ${file.name} 上传失败`);
        },
        //文档类型
        async gettype() {
          let resdata = await gethelptype();
          this.typelist = resdata.data.data.list;
        },
        //上传文件之前
        beforeUploadfile(e) {
          let isrepeat = false;
          this.files.map((item) => {
            if (item.name === e.name) {
              this.$message.error(lang.help_text22);
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
        },
        formatResponse(res) {
          if (res.status !== 200) {
            this.$nextTick(() => {
              this.files = [];
            });
            this.uploadTip = "";
            this.files = []
            this.$message.error(res.msg);
            return { error: res.msg, url: res.url };
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
              if (!this.detialform.content) {
                this.$message.warning(lang.help_text23);
                return;
              }
              this.submitLoading = true;
              if (this.id) {
                if (hidden === 1) {
                  //保存编辑
                  this.detialform.hidden = 1;
                } else {
                  //提交编辑
                  this.detialform.hidden = 0;
                }
                const temp = JSON.parse(JSON.stringify(this.detialform));
                temp.cron_release = temp.cron_release ? 1 : 0;
                temp.cron_release_time = parseInt(
                  new Date(temp.cron_release_time).getTime() / 1000
                );
                edithelp({ ...temp, id: this.id })
                  .then((res) => {
                    if (res.data.status === 200) {
                      this.$message.success(res.data.msg);
                      setTimeout(() => {
                        location.href = "index.htm";
                      }, 1000);
                    }
                  })
                  .catch((err) => {
                    this.getdetialcon();
                    this.$message.error(err.data.msg);
                  })
                  .finally(() => {
                    this.submitLoading = false;
                  });
              } else {
                if (hidden === 1) {
                  //保存新增
                  this.detialform.hidden = 1;
                } else {
                  //提交新增
                  this.detialform.hidden = 0;
                }
                const temp = JSON.parse(JSON.stringify(this.detialform));
                temp.cron_release = temp.cron_release ? 1 : 0;
                temp.cron_release_time = parseInt(
                  new Date(temp.cron_release_time).getTime() / 1000
                );
                addhelp(temp)
                  .then((res) => {
                    if (res.data.status === 200) {
                      this.$message.success(res.data.msg);
                      setTimeout(() => {
                        location.href = "index.htm";
                      }, 1000);
                    }
                  })
                  .catch((err) => {
                    this.$message.error(err.data.msg);
                  }).finally(() => {
                    this.submitLoading = false;
                  });
              }
            }
          });
        },
        getdetialcon() {
          helpdetial({ id: this.id }).then((res) => {
            if (res.data.status === 200) {
              let obj = res.data.data.help;
              obj.cron_release = obj.cron_release === 1 ? true : false;
              obj.cron_release_time = obj.cron_release_time * 1000;
              obj.content = this.transformHtml(obj.content);
              Object.assign(this.detialform, obj);
              this.$refs.tinymce.setContent(this.detialform.content);
              //  tinyMCE.activeEditor.setContent(this.detialform.content);
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
