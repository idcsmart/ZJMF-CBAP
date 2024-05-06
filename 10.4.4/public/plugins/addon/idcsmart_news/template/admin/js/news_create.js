(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const documents = document.getElementsByClassName("newscreat")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}/`;
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
            title: "",
            addon_idcsmart_news_type_id: "",
            keywords: "",
            content: "",
            cron_release: false,
            cron_release_time: "",
          },
          attachment: [],
          files: [],
          uploadTip: "",
          typelist: [],
          requiredRules: {
            title: [{ required: true, message: lang.help_text43 }],
            addon_idcsmart_news_type_id: [
              { required: true, message: lang.help_text44 },
            ],
          },
          uploadUrl: str + "v1/upload",
          systemUrl: url,
          submitLoading: false
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
        // 切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.params.keywords = "";
        },
        //上传失败
        handleFail({ file }) {
          this.$message.error(
            `${lang.help_text45} ${file.name} ${lang.help_text46}`
          );
        },
        //文档类型
        async gettype() {
          let resdata = await gethelptype();
          this.typelist = resdata.data.data.list;
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
        onSuccess() {},

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
              if (!this.detialform.content) {
                this.$message.warning(lang.help_text23);
                return;
              }
              const srcReg = /src=[\'\"]?([^\'\"]*)[\'\"]?/i;
              const arr = this.detialform.content.match(srcReg);
              if (arr !== null && arr.length > 0) {
                this.detialform.img = arr[1];
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
                const temp = JSON.parse(JSON.stringify(this.detialform));
                temp.cron_release = temp.cron_release ? 1 : 0;
                temp.cron_release_time = parseInt(
                  new Date(temp.cron_release_time).getTime() / 1000
                );
                edithelp({ ...temp, id: this.id })
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
                const temp = JSON.parse(JSON.stringify(this.detialform));
                temp.cron_release = temp.cron_release ? 1 : 0;
                temp.cron_release_time = parseInt(
                  new Date(temp.cron_release_time).getTime() / 1000
                );
                addhelp(temp)
                  .then((res) => {
                    if (res.data.status === 200) {
                      this.$message.success(`${lang.publish}${lang.success}`);
                      setTimeout(() => {
                        this.submitLoading = false;
                        location.href = "index.htm";
                      }, 500);
                    }
                  })
                  .catch((err) => {
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
              let obj = res.data.data.news;
              obj.cron_release = obj.cron_release === 1 ? true : false;
              obj.cron_release_time = obj.cron_release_time * 1000;
              obj.content = this.transformHtml(obj.content);
              this.$nextTick(() => {
                Object.assign(this.detialform, obj);
                this.$refs.tinymce.setContent(this.detialform.content);
                // tinymce.editors["tiny"].setContent(this.detialform.content);
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
              });
            }
          });
        },
        save() {
          this.submit(1);
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
