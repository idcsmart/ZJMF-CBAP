(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementById('template');
    new Vue({
      created() {
        this.GetIndexData()
      },
      mounted() {
        window.addEventListener('message', this.onMessage)
      },
      data() {
        return {
          unLogin: true,
          isGetData: false,
          firstName: '',
          headBgcList: ['#3699FF', '#57C3EA', '#5CC2D7', '#EF8BA2', '#C1DB81', '#F1978C', '#F08968']
        };
      },
      filters: {

      },
      methods: {
        handleCommand(e) {
          if (e == 'account') {
            this.goAccountpage()
          }
          if (e == 'quit') {
            this.logOut()
          }
        },
        goAccountpage() {
          window.open('/account.htm')
        },
        // 退出登录
        logOut() {
          Axios.post('/logout').then(res => {
            localStorage.removeItem("jwt")
            this.isGetData = true
            this.unLogin = true
            this.firstName = ''
          })
        },
        onMessage(e) {
          if(e.origin !== `${window.location.protocol}//${window.location.host}`){
            return
          }
          console.log(e.data.token)
          if(e.data && e.data.token){
            localStorage.jwt = e.data.token
            this.GetIndexData()
          }
        },
        GetIndexData() {
          accountDetail().then((res) => {
            if (res.data.status == 200) {
              const nameDom = document.getElementById('firstName')
              this.firstName = res.data.data.account.username.substring(0, 1).toUpperCase()
              this.unLogin = false
              if (sessionStorage.headBgc) {
                nameDom.style.background = sessionStorage.headBgc
              } else {
                const index = Math.round(Math.random() * (this.headBgcList.length - 1))
                nameDom.style.background = this.headBgcList[index]
                sessionStorage.headBgc = this.headBgcList[index]
              }
            }
          }).finally(() => {
            this.isGetData = true
          })
        },
        goLogin() {
            window.open('/login.htm')
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
