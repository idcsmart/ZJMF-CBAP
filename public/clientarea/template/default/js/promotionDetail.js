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

          formData: {
            name: '',
            startTime: '',
            endTime: '',
            aaa: '',//活动描述
            type: '',
            price: '',
          },
          rules: {
            name: { required: true, message: '请输入活动名称', trigger: 'blur' },
            startTime: { required: true, message: '请选择活动开始时间', trigger: 'change' },
            endTime: { required: true, message: '请选择活动结束时间', trigger: 'change' },
            price: { required: true, message: '请输入折扣价格', trigger: 'blur' },
          },
          tableData: [],
          loading: false
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
          document.title = this.commonData.website_name + '-模板页面'
        },
        goBack () {
          history.back()
        },
        btn () {
          this.$refs.ruleForm.validate((valid) => {
            if (valid) {
              alert('submit!');
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
