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
        // 验证规则
        const validatePort = (rule, value, callback) => {
          if (value === '') {
            callback(new Error(`${lang.placeholder_pre1}${lang.common_cloud_label13}`));
          } else {
            const reg = /^[0-9-]*$/
            if (reg.test(value)) {
              callback();
            } else {
              callback(new Error(`${lang.placeholder_pre1}${lang.security_tip8}${lang.common_cloud_label13}`));
            }

          }
        };
        const validatIp = (rule, value, callback) => {
          if (value === "") {
            callback(new Error(`${lang.placeholder_pre1}${lang.auth_ip}`));
          } else {
            const val = value.split('/')
            if (/^((25[0-5]|2[0-4]\d|[01]?\d\d?)($|(?!\.$)\.)){4}$/.test(val[0]) && val.length === 1) {
              callback()
            } else if (/^((25[0-5]|2[0-4]\d|[01]?\d\d?)($|(?!\.$)\.)){4}$/.test(val[0]) && val.length === 2 && parseInt(val[1]) <= 65535 && parseInt(val[1]) >= 0) {
              callback()
            } else {
              callback(
                new Error(
                  `${lang.placeholder_pre1}${lang.security_tip8}${lang.auth_ip}`
                )
              );
            }
          }
        };
        return {
          id: '',
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
          activeName: "in",
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
          optType: '',
          protocol: [ // 协议选项
            {
              label: 'all',
              value: 'all'
            },
            {
              label: 'all_tcp',
              value: 'all_tcp'
            },
            {
              label: 'all_udp',
              value: 'all_udp'
            },
            {
              label: 'tcp',
              value: 'tcp'
            },
            {
              label: 'udp',
              value: 'udp'
            },
            {
              label: 'icmp',
              value: 'icmp'
            },
            {
              label: 'ssh',
              value: 'ssh'
            },
            {
              label: 'telnet',
              value: 'telnet'
            },
            {
              label: 'http',
              value: 'http'
            },
            {
              label: 'https',
              value: 'https'
            },
            {
              label: 'mssql',
              value: 'mssql'
            },
            {
              label: 'Oracle',
              value: 'oracle'
            },
            {
              label: 'mysql',
              value: 'mysql'
            },
            {
              label: 'rdp',
              value: 'rdp'
            },
            {
              label: 'postgresql',
              value: 'postgresql'
            },
            {
              label: 'redis',
              value: 'redis'
            }
          ],
          inList: [], // 入方向数据
          outList: [],
          inParams: {
            id: '',
            keywords: '',
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 0,
            orderby: 'id',
            sort: 'desc',
            direction: 'in'
          },
          outParams: {
            id: '',
            keywords: '',
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 0,
            orderby: 'id',
            sort: 'desc',
            direction: 'out'
          },
          multipleSelection: [],
          delTile: '',
          delType: '',
          optTitle: '',
          singleForm: { // 单个添加规则
            id: '',
            description: '',
            protocol: '',
            ip: '',
            port: '',
            direction: '' // in,out
          },
          rules: {
            protocol: [
              { required: true, message: `${lang.placeholder_pre2}${lang.protocol}`, trigger: 'change' },
            ],
            port: [
              { required: true, message: `${lang.placeholder_pre1}${lang.common_cloud_label13}`, trigger: 'blur' },
              { validator: validatePort, trigger: 'blur' },
            ],
            ip: [
              { required: true, message: `${lang.placeholder_pre1}${lang.auth_ip}`, trigger: 'blur' },
              { validator: validatIp, trigger: 'blur' },
            ],
          },
          batchForm: { // 批量增加规则
            id: '',
            ip: '',
            description: ''
          },
          batchRules: {
            ip: [
              { required: true, message: `${lang.placeholder_pre1}${lang.auth_ip}`, trigger: 'blur' },
              { validator: validatIp, trigger: 'blur' },
            ],
          },
          // 批量规则
          batchArr: [
            {
              tit: lang.remote_login,
              check: false,
              child: [
                {
                  tit: 'SSH',
                  protocol: 'ssh',
                  port: 22,
                  check: false
                },
                {
                  tit: 'RDP',
                  protocol: 'rdp',
                  port: 3389,
                  check: false
                },
                {
                  tit: 'Telnet',
                  protocol: 'telnet',
                  port: 23,
                  check: false
                },
                {
                  tit: 'ICMP',
                  protocol: 'icmp',
                  port: 0,
                  check: false
                }
              ]
            },
            {
              tit: lang.web_server,
              check: false,
              child: [
                {
                  tit: 'HTTP',
                  protocol: 'http',
                  port: 80,
                  check: false
                },
                {
                  tit: 'HTTPS',
                  protocol: 'https',
                  port: 443,
                  check: false
                }
              ]
            },
            {
              tit: lang.database,
              check: false,
              child: [
                {
                  tit: 'MySQL',
                  protocol: 'mysql',
                  port: 3306,
                  check: false
                },
                {
                  tit: 'MS SQL',
                  protocol: 'mssql',
                  port: 1433,
                  check: false
                },
                {
                  tit: 'PostgreSQL',
                  protocol: 'postgresql',
                  port: 5432,
                  check: false
                },
                {
                  tit: 'Oracle',
                  protocol: 'oracle',
                  port: 1521,
                  check: false
                },
                {
                  tit: 'Redls',
                  protocol: 'redis',
                  port: 6379,
                  check: false
                }
              ]
            }
          ],
          batchVisible: false,
          checkedRules: [],
          // 关联实例
          availableCloud: [],
          relationVisible: false,
          relationForm: {
            host_id: ''
          },
          relationRules: {
            host_id: [
              { required: true, message: `${lang.placeholder_pre2}${lang.cloud_menu_1}`, trigger: 'change' },
            ],
          },
          baseUrl: url
        }
      },
      watch: {
        "singleForm.protocol"(val) {
          switch (val) {
            case "ssh":
              return (this.singleForm.port = "22");
            case "telnet":
              return (this.singleForm.port = "23");
            case "http":
              return (this.singleForm.port = "80");
            case "https":
              return (this.singleForm.port = "443");
            case "mssql":
              return (this.singleForm.port = "1433");
            case "oracle":
              return (this.singleForm.port = "1521");
            case "mysql":
              return (this.singleForm.port = "3306");
            case "rdp":
              return (this.singleForm.port = "3389");
            case "postgresql":
              return (this.singleForm.port = "5432");
            case "redis":
              return (this.singleForm.port = "6379");
            case "tcp":
            case "udp":
              return (this.singleForm.port = "");
            default:
              return (this.singleForm.port = "1-65535");
          }
        },
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
        this.id = this.params.id = this.inParams.id = this.outParams.id = location.href.split('?')[1].split('=')[1]
        this.getCommonData()
        this.getInRules()
      },
      methods: {
        back () {
          location.href = 'security_group.htm'
        },
        // 切换tab
        handleClick () {
          this.multipleSelection = []
          this.$refs.multipleTable.clearSelection();
          switch (this.activeName) {
            case 'in':
              this.getInRules()
              return
            case 'out':
              this.getOutRules()
              return
            default:
              this.getRelation()
              this.getAllCloudList()
          }
        },
        // 入规则
        async getInRules () {
          try {
            this.loading = true
            const res = await getGroupRules(this.inParams)
            this.inList = res.data.data.list
            this.inParams.total = res.data.data.count
            this.loading = false
          } catch (error) {
            this.$message.error(error.data.msg)
            this.loading = false
          }
        },
        handleSelectionChange (val) {
          this.multipleSelection = val;
        },
        // 批量删除
        batchDelete () {
          if (this.multipleSelection.length === 0) {
            return this.$message.warning(`${lang.placeholder_pre2}${lang.rules}`)
          }
          this.delType = 'batch'
          if (this.activeName === 'in') {
            this.delTile = `${lang.batch_delete}${lang.in_rules}`
          } else if (this.activeName === 'out') {
            this.delTile = `${lang.batch_delete}${lang.out_rules}`
          }

          this.isShowDel = true
        },
        // 出方向规则
        async getOutRules () {
          try {
            this.loading = true
            const res = await getGroupRules(this.outParams)
            this.outList = res.data.data.list
            this.outParams.total = res.data.data.count
            this.loading = false
          } catch (error) {
            this.$message.error(error.data.msg)
            this.loading = false
          }
        },
        // 关联安全组实例列表
        async getRelation () {
          try {
            const res = await getGroupCloud(this.params)
            this.dataList = res.data.data.list
            this.params.total = res.data.data.count
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        // 获取所有可用实例
        async getAllCloudList () {
          try {
            const res = await getAllCloud({
              status: 'Active',
              page: 1,
              limit: 10000,
              scene: 'security_group'
            })
            let temp = res.data.data.list
            this.availableCloud = temp.sort((a, b) => {
              return b.id - a.id
            })
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        // 关联实例
        relationCloud () {
          this.relationVisible = true
          this.optTitle = lang.add_cloud_to_group
          this.relationForm.host_id = ''
        },
        submitRelation () {
          this.$refs.relationForm.validate(async (valid) => {
            if (valid) {
              try {
                this.submitLoading = true
                const params = {
                  id: this.id,
                  host_id: this.relationForm.host_id
                }
                const res = await concatCloud(params)
                this.submitLoading = false
                this.relationVisible = false
                this.$message.success(lang.add_cloud_success)
                this.getRelation()
              } catch (error) {
                this.submitLoading = false
                this.$message.error(error.data.msg)
              }
            } else {
              console.log('error submit!!');
              return false;
            }
          });
        },
        reClose () {
          this.relationVisible = false
          this.submitLoading = false
        },
        // 删除安全组
        deleteItem (row, type) {
          this.delName = row.name;
          this.delId = row.id;
          this.isShowDel = true;
          this.delType = 'one'
          if (type === 'in') {
            this.delTile = `${lang.referral_title9}${lang.in_rules}`
          } else if (type === 'out') {
            this.delTile = `${lang.referral_title9}${lang.out_rules}`
          } else if (type === 'relation') {
            this.delTile = `${lang.referral_title9}${row.name}`
          }
        },
        // 单个删除
        delSub () {
          if (this.delType === 'one') {
            if (this.activeName === 'relation') {
              this.delRelation() // 取消关联实例
            } else {
              this.delOne()
            }
          } else if (this.delType === 'batch') {
            this.batchDel()
          }
        },
        async delOne () {
          try {
            this.submitLoading = true
            const res = await deleteGroupRules(this.delId)
            if (res.data.status === 200) {
              this.$message.success(res.data.msg)
              this.isShowDel = false
              this.submitLoading = false
              if (this.activeName === 'in') {
                this.inList = this.inList.filter(item => item.id !== this.delId)
                if (this.inList.length === 0 && this.inParams.page > 1) {
                  this.inParams.page -= 1
                }
                this.getInRules()
              } else if (this.activeName === 'out') {
                this.outList = this.outList.filter(item => item.id !== this.delId)
                if (this.outParams.length === 0 && this.outParams.page > 1) {
                  this.outParams.page -= 1
                }
                this.getOutRules()
              }
            }
          } catch (error) {
            this.submitLoading = false
            this.$message.error(error.data.msg)
          }
        },
        async delRelation () {
          try {
            const params = {
              id: this.id,
              host_id: this.delId
            }
            this.submitLoading = true
            const res = await cancelConcatCloud(params)
            this.$message.success(lang.delete_cloud_success)
            this.submitLoading = false
            this.isShowDel = false
            this.getRelation()
          } catch (error) {
            this.submitLoading = false
            this.$message.error(error.data.msg)
          }
        },
        // 批量删除
        batchDel () {
          let delArr = []
          this.multipleSelection.forEach((item, index) => {
            delArr[index] = deleteGroupRules(item.id)
          })
          this.submitLoading = true
          const idTemp = this.multipleSelection.reduce((all, cur) => {
            all.push(cur.id)
            return all
          }, [])
          Promise.all(delArr).then(res => {
            this.$message.success(`${lang.referral_tips4}`)
            this.isShowDel = false
            this.submitLoading = false
            if (this.activeName === 'in') {
              this.inList = this.inList.filter(item => !idTemp.includes(item.id))
              if (this.inList.length === 0 && this.inParams.page > 1) {
                this.inParams.page -= 1
              }
              this.getInRules()
            } else if (this.activeName === 'out') {
              this.outList = this.outList.filter(item => !idTemp.includes(item.id))
              if (this.outList.length === 0 && this.outParams.page > 1) {
                this.outParams.page -= 1
              }
              this.getOutRules()
            }
          }).catch(error => {
            this.submitLoading = false
            this.$message.error(error.data.msg)
          })
        },
        // 添加安全组
        createSecurity () {
          this.isShowCj = true
          this.optType = 'add'
          this.singleForm.id = this.id
          this.singleForm.protocol = 'tcp'
          this.singleForm.ip = ''
          this.singleForm.port = ''
          this.singleForm.description = ''
          this.optTitle = `${lang.com_config.add}${lang.rules}`
          this.$refs.singleForm && this.$refs.singleForm.clearValidate()
        },

        submitForm () {
          this.$refs.singleForm.validate(async (valid) => {
            if (valid) {
              try {
                this.submitLoading = true
                const params = JSON.parse(JSON.stringify(this.singleForm))
                params.direction = this.activeName
                const res = await addAndUpdateGroupRules(this.optType, params)
                this.submitLoading = false
                this.isShowCj = false
                this.$message.success(res.data.msg)
                if (this.activeName === 'in') {
                  this.getInRules()
                } else if (this.activeName === 'out') {
                  this.getOutRules()
                }
              } catch (error) {
                this.submitLoading = false
                this.$message.error(error.data.msg)
              }
            } else {
              console.log('error submit!!');
              return false;
            }
          });
        },
        // 批量添加规则
        batchCreateSecurity () {
          this.batchVisible = true
          this.optTitle = lang.batch_add_rules
          this.batchForm.id = this.id
          this.batchForm.ip = '0.0.0.0/0'
          this.batchForm.description = ''
          let temp = JSON.parse(JSON.stringify(this.batchArr))
          temp = temp.map(item => {
            item.check = false
            item.child = item.child.map(el => {
              el.check = false
              return el
            })
            return item
          })
          this.batchArr = temp
        },
        // 点击勾选子项
        changePar (e, index) {
          const temp = JSON.parse(JSON.stringify(this.batchArr))
          temp[index].check = e
          temp[index].child = temp[index].child.map(item => {
            item.check = e
            return item
          })
          this.batchArr = temp
        },
        // 子项点击
        changeChild (e, index, ind) {
          const temp = JSON.parse(JSON.stringify(this.batchArr))
          temp[index].child[ind].check = e
          const len = temp[index].child.length
          const checkNum = temp[index].child.reduce((all, cur) => {
            if (cur.check === true) {
              all += 1
            }
            return all
          }, 0)
          if (len === checkNum) {
            temp[index].check = true
          } else {
            temp[index].check = false
          }
          this.batchArr = temp
        },
        batchSubmitForm () {
          this.$refs.batchForm.validate(async (valid) => {
            if (valid) {
              try {
                let arr = []
                arr = this.batchArr.reduce((all, cur) => {
                  all = all.concat(cur.child.flat())
                  return all
                }, [])
                arr = arr.filter(item => item.check === true)
                arr.map(item => {
                  item.protocol = item.protocol
                  item.direction = this.activeName
                  item.ip = this.batchForm.ip
                  item.description = this.batchForm.description
                })
                const params = {
                  id: this.batchForm.id,
                  rule: arr
                }
                if (params.rule.length === 0) {
                  return this.$message.warning(lang.placeholder_pre2 + lang.common_port)
                }
                this.submitLoading = true
                const res = await batchRules(params)
                this.submitLoading = false
                this.batchVisible = false
                this.$message.success(res.data.msg)
                if (this.activeName === 'in') {
                  this.getInRules()
                } else if (this.activeName === 'out') {
                  this.getOutRules()
                }
              } catch (error) {
                console.log('error', error)
                this.submitLoading = false
                this.$message.error(error.data.msg)
              }
            } else {
              console.log('error submit!!');
              return false;
            }
          });
        },
        editItem (row) {
          this.optType = 'update'
          this.singleForm = JSON.parse(JSON.stringify(row))
          this.isShowCj = true
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
          // this.handleClick();
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
        // 每页展示数改变
        sizeChange (e) {
          switch (this.activeName) {
            case 'in':
              this.inParams.limit = e
              this.inParams.page = 1
              this.getInRules()
              break;
            case 'out':
              this.outParams.limit = e
              this.outParams.page = 1
              this.getOutRules()
              break;
            case 'relation':
              this.params.limit = e
              this.params.page = 1
              this.getRelation()
              break;
          }
        },
        // 当前页改变
        currentChange (e) {
          switch (this.activeName) {
            case 'in':
              this.inParams.page = e
              this.getInRules()
              break;
            case 'out':
              this.outParams.page = e
              this.getOutRules()
              break;
            case 'relation':
              this.params.page = e
              this.getRelation()
              break;
          }
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
