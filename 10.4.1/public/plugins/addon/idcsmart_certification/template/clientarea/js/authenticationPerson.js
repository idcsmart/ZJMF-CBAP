(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    new Vue({
      components: {
        asideMenu,
        topMenu,
      },
      created() {
        this.plugin_name = location.href.split("?")[1].split("=")[1];
        this.getCommonData();
        this.getcustom_fields();
        this.getCertificationInfo();
      },
      mounted() {},
      updated() {
        // 关闭loading
        document.getElementById("mainLoading").style.display = "none";
        document.getElementsByClassName("template")[0].style.display = "block";
      },
      destroyed() {},
      data() {
        return {
          commonData: {},
          dialogVisible: false,
          sunmitBtnLoading: false,
          certificationInfoObj: {},
          dialogImageUrl: "",
          jwt: `Bearer ${localStorage.jwt}`,
          uploadTipsText1: "",
          uploadTipsText2: "",
          upload1_progress: "0%", // 身份证正面上传进度
          upload2_progress: "0%",
          plugin_name: "", // 实名接口
          certificationPerson: {
            // 个人实名认证信息对象
            card_name: "", //姓名
            card_type: 1, // 证件类型:1大陆,0非大陆
            card_number: "", // 证件号码
            phone: "", // 手机号
            custom_fields: {},
          },
          custom_fieldsObj: [], // 其他自定义字段
          img_one: "", // 身份证正面照
          img_two: "", // 身份证反面照
          personRules: {
            card_name: [
              {
                required: true,
                message: lang.realname_text13,
                trigger: "blur",
              },
            ],
            card_type: [
              {
                required: true,
                message: lang.realname_text66,
                trigger: "blur",
              },
            ],
            card_number: [
              {
                required: true,
                message: lang.realname_text67,
                trigger: "blur",
              },
            ],
          },
          id_card_type: [
            {
              label: lang.realname_text68,
              value: 1,
            },
            {
              label: lang.realname_text70,
              value: 2,
            },
            {
              label: lang.realname_text72,
              value: 3,
            },
            {
              label: lang.realname_text69,
              value: 4,
            },
            {
              label: lang.realname_text71,
              value: 5,
            },
            {
              label: lang.realname_text76,
              value: 6,
            },
            {
              label: lang.realname_text77,
              value: 7,
            },
            {
              label: lang.realname_text78,
              value: 8,
            },
          ],
          custom_fileList: [], // 自定义上传列表
          filelist: [],
          card_one_fileList: [],
          card_two_fileList: [],
        };
      },
      filters: {
        formateTime(time) {
          if (time && time !== 0) {
            return formateDate(time * 1000);
          } else {
            return "--";
          }
        },
      },
      methods: {
        // 返回按钮
        backTicket() {
          location.href = "/account.htm";
        },
        goSelect() {
          location.href = "authentication_select.htm";
        },
        onUpload(file, val) {
          this.sunmitBtnLoading = true;
          if (val === "img_one") {
            this.img_one = "padding";
          }
          if (val === "img_two") {
            this.img_two = "padding";
          }
        },
        // 身份证第一张上传成功回调
        handleSuccess1(response, file, fileList) {
          if (response.status === 200) {
            this.img_one = response.data.save_name;
            this.sunmitBtnLoading = false;
            this.uploadTipsText1 = "";
          } else {
            this.$message.warning(response.msg);
            this.uploadTipsText1 = response.msg;
            this.card_one_fileList = [];
            this.img_one = "";
          }
        },
        onProgress(event, file, fileList, val) {
          if (val === "img_one") {
            this.upload1_progress = event.percent.toFixed(2) + "%";
          }
          if (val === "img_two") {
            this.upload2_progress = event.percent.toFixed(2) + "%";
          }
        },
        // 身份证第一张删除
        handleRemove1(file, fileList) {
          this.card_one_fileList = [];
          this.img_one = "";
          this.upload1_progress = "0%";
          this.sunmitBtnLoading = false;
        },
        // 身份证第二张上传成功回调
        handleSuccess2(response, file, fileList) {
          if (response.status === 200) {
            this.img_two = response.data.save_name;
            this.sunmitBtnLoading = false;
            this.uploadTipsText2 = "";
          } else {
            this.$message.warning(response.msg);
            this.uploadTipsText2 = response.msg;
            this.card_two_fileList = [];
            this.img_two = "";
          }
        },
        // 自定义上传文件相关
        handleSuccess(response, file, fileList, item) {
          this.sunmitBtnLoading = false;
          if (response.status === 200) {
            this.custom_fileList.push(response.data.save_name);
            this.certificationPerson.custom_fields[`${item.field}`] =
              this.custom_fileList;
          }
        },
        // 自定义上传删除
        beforeRemove(file, fileList) {
          // 获取到删除的 save_name
          let save_name = file.response.data.save_name;
          this.custom_fileList = this.custom_fileList.filter((item) => {
            return item != save_name;
          });
        },
        // 身份证第二张删除
        handleRemove2() {
          this.card_two_fileList = [];
          this.img_two = "";
          this.upload2_progress = "0%";
          this.sunmitBtnLoading = false;
        },
        // 预览
        handlePictureCardPreview(file) {
          this.dialogImageUrl = file.url;
          this.dialogVisible = true;
        },
        // 获取自定义字段
        getcustom_fields() {
          custom_fields({ name: this.plugin_name, type: "person" }).then(
            (res) => {
              this.custom_fieldsObj = res.data.data.custom_fields;
            }
          );
        },
        // 获取配置信息
        getCertificationInfo() {
          certificationInfo().then(async (res) => {
            this.certificationInfoObj = res.data.data;
          });
        },
        // 个人认证提交
        personSumit() {
          this.$refs.certificationPerson.validate(async (valid) => {
            this.custom_fieldsObj.forEach((item) => {
              if (
                item.required &&
                !this.certificationPerson.custom_fields[item.field]
              ) {
                valid = false;
              }
            });
            if (!valid) {
              this.$message.warning(lang.realname_text73);
              return;
            }
            if (this.certificationInfoObj.certification_upload === "1") {
              if (this.img_one == "") {
                this.$message.warning(lang.realname_text79);
                return;
              }
              if (this.img_two == "") {
                this.$message.warning(lang.realname_text80);
                return;
              }
            }
            this.sunmitBtnLoading = true;
            this.certificationPerson.img_one = this.img_one;
            this.certificationPerson.img_two = this.img_two;
            this.certificationPerson.plugin_name = this.plugin_name;
            uploadPerson(this.certificationPerson)
              .then((ress) => {
                if (ress.data.status === 200) {
                  location.href = "authentication_thrid.htm?type=1";
                }
              })
              .catch((err) => {
                this.$message.warning(err.data.msg);
              })
              .finally(() => {
                this.sunmitBtnLoading = false;
              });
          });
        },
        // 获取通用配置
        getCommonData() {
          getCommon().then((res) => {
            if (res.data.status === 200) {
              this.commonData = res.data.data;
              localStorage.setItem(
                "common_set_before",
                JSON.stringify(res.data.data)
              );
              document.title =
                this.commonData.website_name + "-" + lang.realname_text81;
            }
          });
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
