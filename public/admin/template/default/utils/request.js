const host = location.host
const hostUrl = location.pathname.split('/')[1]
const baseURL = `/${hostUrl}/v1`
const Axios = axios.create({
  baseURL,
  timeout: 6000
})
Axios.defaults.withCredentials = true

// 请求拦截器
Axios.interceptors.request.use(
  config => {
    config.headers.Authorization = 'Bearer' + ' ' + localStorage.getItem('backJwt')
    // config.headers.lang = localStorage.getItem('lang') || 'zh-cn'
    return config
  }, error => {
    return Promise.reject(error)
  }
)

// 响应拦截器
Axios.interceptors.response.use(
  response => {
    const code = response.data.status
    if (response.data.rule) { // 返回有rule的时候, 才执行缓存操作
      localStorage.setItem('menuList', JSON.stringify(response.data.rule)) // 权限菜单
    }
    if (code) {
      switch (code) {
        case 200:
          break
        case 302:
          location.href = `${baseURL}/install`
          break
        case 307:
          break
        case 400:
          return Promise.reject(response)
        case 401: // 未授权:2个小时未操作自动退出登录
          if (location.href.indexOf('login.html')===-1) {
           location.href = 'login.html'
          }
          break
        case 403:
          break
        case 404:
          location.href = '404.html'
          break
        case 405:
          location.href = 'login.html'
          break
        case 406:
          break
        case 409: // 该管理没有该客户, 跳转首页
          location.href = ''
          break
        case 410:
          break
        case 422:
          break
        case 500:
          this.TDesign.MessagePlugin.error('访问失败, 请重试!')
          break
        case 501:
          break
        case 502:
          break
        case 503:
          // console.log('axios-超时????')
          break
        case 504:
          break
        case 505:
          break
      }
    }

    return response
  },
  error => {
    // console.log('error:', error)
    // 1016 断网提示
    if (error.toString().indexOf('Network Error') !== -1) {
      this.TDesign.MessagePlugin.error('网络开小差啦，请更换网络或者稍后再试')
    }

    if (error.config) {
      if (error.config.url.indexOf('system/autoupdate') !== -1) { // 系统更新接口
        if (error.message === 'Network Error') {
          this.TDesign.MessagePlugin.error('网络开小差啦，请更换网络或者稍后再试')
          setTimeout(() => {
            location.reload()
          }, 2000)
        }
      }
    }
    if (error.response) {
      if (error.response.status === 302) {
        location.href = `${baseURL}/install`
      }
    }
    return Promise.reject(error)
  })
