(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('home-drag')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      data() {
        return {
          allWidget: [],
          checkWidget: [],
          showList: [],
          loading: false,
          showIndexPage: false,
          userName: localStorage.getItem('userName'),
          firstNav: [],
          authList: []
        }
      },
      computed: {
      },
      methods: {
        onStart() { },
        async onEnd(e) {
          try {
            const widget_arr = this.showList.reduce((all, cur) => {
              all.push(cur.id)
              return all
            }, [])
            const res = await saveWidget({ widget_arr })
            this.$message.success(res.data.msg)
            //  this.getWidgetList()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        async changeCheck(e, item) {
          try {
            const res = await changeWidget({
              widget: item.id,
              status: item.checked
            })
            this.$message.success(res.data.msg)
            this.getWidgetList()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        async getWidgetList() {
          try {
            this.loading = true
            const res = await getWidget()
            this.checkWidget = res.data.data.show_widget
            this.allWidget = res.data.data.widget.map(item => {
              item.checked = this.checkWidget.includes(item.id)
              return item
            })
            this.showList = this.checkWidget.reduce((all, cur) => {
              const item = this.allWidget.filter(el => el.id === cur)
              all.push({
                id: cur,
                title: item[0]?.title,
                columns: item[0]?.columns
              })
              return all
            }, [])
            const iterable = this.checkWidget.map(item => {
              return getWidgetContent({ widget: item })
            })
            await Promise.allSettled(iterable).then((res) => {
              res.forEach((ress, index) => {
                if (ress.status === 'fulfilled') {
                  $(`#${this.checkWidget[index]}`).html(ress.value.data.data.content)
                }
              })
            })
            this.loading = false
          } catch (error) {
            this.loading = false
            this.$message.error(error.data.msg)
          }
        },
        async getContent(widget) {
          try {
            const res = await getWidgetContent({ widget })
            this.$nextTick(() => {
              $(`#${widget}`).html(res.data.data.content)
            })
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        findById(arr, id, returnFlag = false) {
          for (let i = 0; i < arr.length; i++) {
            if (arr[i].id === id) {
              if (returnFlag) {
                return true
              } else {
                return arr[i];
              }
            }
          }
          // 如果没有找到对应的对象，则返回 null 或者 undefined
          if (returnFlag) {
            return false
          } else {
            return null;
          }
        },
      },
      created() {
        this.getWidgetList()
        /* 权限相关 */
        this.firstNav = JSON.parse(localStorage.getItem('backMenus'))[0]
        this.authList = JSON.parse(localStorage.authList) || []
        const indexAuth = this.findById(this.authList, 99, true)
        const indexWebkit = this.findById(this.authList, 100)
        if (!indexAuth) {
          let goUrl = ''
          let goId = ''
          if (this.firstNav.url) {
            goUrl = this.firstNav.url
            goId = this.firstNav.id

          } else {
            if (this.firstNav.child.length !== 0) {
              goUrl = this.firstNav.child[0].url
              goId = this.firstNav.child[0].id
            }
          }
          const temp = location.origin + '/' + location.pathname.split('/')[1]
          localStorage.setItem('curValue', goId)
          location.href = temp + '/client.htm'
        }
        /* 权限相关 end */
      },
      computed: {
        calcSm() {
          return (num) => {
            switch (num) {
              case 1:
                return 6;
              default:
                return 12;
            }
          }
        },
        calcMd() {
          return (num) => {
            switch (num) {
              case 1:
                return 6;
              default:
                return 12;
            }
          }
        },
        calcXl() {
          return (num) => {
            switch (num) {
              case 1:
                return 3;
              case 2:
                return 6;
              case 3:
                return 9;
              default:
                return 12;
            }
          }
        }
      }
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);