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
      updated () {
        // // 关闭loading
        // document.getElementById('mainLoading').style.display = 'none';
        // document.getElementsByClassName('template')[0].style.display = 'block'
      },
      destroyed () {

      },
      data () {
        return {
          params: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 0,
            orderby: 'id',
            sort: 'desc',
            keywords: '',
          },
          commonData: {},
          isShowAPI: false,
          isShowAPILog: false,
          activeName: "4",
          loading: false,
          dataList: [],
          isShowDel: false,
          delName: '',
          delId: '',
          submitLoading: false,
          isShowCj: false,
          createForm: {
            id: '',
            name: '',
            description: ''
          },
          errText: '',
          optType: ''
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
      created () {
        this.getCommonData()
        this.getGroupList()
      },

      methods: {
        // 安全组列表
        async getGroupList () {
          try {
            const res = await getGroup(this.params)
            this.dataList = res.data.data.list
            this.params.total = res.data.data.count
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        // 删除安全组
        deleteItem (row) {
          this.delName = row.name;
          this.delId = row.id;
          this.isShowDel = true;
        },
        async delSub () {
          try {
            this.submitLoading = true
            const res = await deleteGroup(this.delId)
            if (res.data.status === 200) {
              this.$message.success(res.data.msg)
              this.getGroupList()
              this.isShowDel = false
              this.submitLoading = false
            }
          } catch (error) {
            this.submitLoading = false
            this.$message.error(error.data.msg)
          }
        },
        // 添加安全组
        createSecurity () {
          this.isShowCj = true
          this.optType = 'add'
          this.createForm.name = ''
          this.createForm.description = ''
          this.errText = ''
        },
        // 创建API秘钥 提交
        async cjSub () {
          let isPass = true;
          if (!this.createForm.name) {
            this.errText = `${lang.placeholder_pre1}${lang.security_label1}`;
            isPass = false;
          }
          if (isPass) {
            this.errText = "";
            const params = JSON.parse(JSON.stringify(this.createForm))
            if ( this.optType === 'add') {
              delete params.id
            }
            try {
              this.submitLoading = true
              const res = await addAndUpdateGroup(this.optType, params)
              if (res.data.status === 200) {
                // 关闭弹窗
                this.isShowCj = false;
                // 获取返回信息 并在新弹窗进行展示
                this.apiData = res.data.data;
                this.getGroupList()
                this.submitLoading = false
              }
            } catch (error) {
              this.submitLoading = false
              this.errText = error.data.msg;
            }
          }
        },
        editItem (row) {
          this.optType = 'update'
          this.createForm = JSON.parse(JSON.stringify(row))
          this.isShowCj = true
          this.errText = ''
        },
        cjClose () {
          this.isShowCj = false
        },
        delClose () {
          this.isShowDel = false
        },
        getRule (arr) {
          let isShow1 = this.showFun(arr, "ApiController::list");
          let isShow2 = this.showFun(arr, "LogController::list");
          if (isShow1) {
            this.isShowAPI = true;
            this.activeName = this.activeName;
          } else {
            this.activeName = "2";
          }
          if (isShow2) {
            this.isShowAPILog = true;
          }
          this.handleClick();
        },
        showFun (arr, str) {
          if (typeof arr == "string") {
            return true;
          } else {
            let isShow = "";
            isShow = arr.find((item) => {
              let isHave = item.includes(str);
              if (isHave) {
                return isHave;
              }
            });
            return isShow;
          }
        },
        handleClick (tap, event) {
          if (this.activeName == 1) {
            location.href = "security.htm";
          }
          if (this.activeName == 3) {
            location.href = "security_log.htm";
          }
          if (this.activeName == 2) {
            location.href = "security_ssh.htm";
          }
        },
        // 每页展示数改变
        sizeChange (e) {
          this.params.limit = e
          this.params.page = 1
          // 获取列表
          this.getGroupList()
        },
        // 当前页改变
        currentChange (e) {
          this.params.page = e
          this.getGroupList()
        },

        // 获取通用配置
        getCommonData () {
          this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
          document.title = this.commonData.website_name + '-' + lang.security_group
        }
      },

    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
