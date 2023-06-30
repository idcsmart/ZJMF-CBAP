/*
 * @Author: zhaoxiaolong 1411373683@qq.com
 * @Date: 2022-09-30 14:24:07
 * @LastEditors: zhaoxiaolong 1411373683@qq.com
 * @LastEditTime: 2022-10-08 14:29:56
 * @FilePath: \public\admin\template\default\js\index.js
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */
(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('index-page')[0]
        const host = location.origin
        const fir = location.pathname.split('/')[1]
        const url = `${host}/${fir}/`
        Vue.prototype.lang = window.lang
        new Vue({
            data() {
                return {
                    indexData: {},
                    chartDom: null,
                    myChart: null,
                    clients: [], // 大客户
                    userName: localStorage.getItem('userName') || '-',
                    this_year_month_amount: [],// 柱状图数据
                    onlineAdminList: [],
                    visitClientList: [],
                    XmonthList: [],
                    YamountList: [],
                    option: {},
                    onlinePage: {
                        params: { page: 1, limit: 5 },
                        isLoading: false,
                        isEnd: false
                    },
                    visitPage: {
                        params: { page: 1, limit: 5 },
                        isLoading: false,
                        isEnd: false
                    },
                    showOnline: false,
                    showVisit: false,
                    showOnLoad: false,
                    showClient: false,
                    showSale: false,
                    showIndexPage: false,
                    authList: [],
                    firstNav: {}
                }
            },
            created() {
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
                    localStorage.setItem('curValue', goId)
                    location.href = url + 'client.htm'
                }
                if (indexWebkit) {
                    this.showIndexPage = true
                    this.showOnLoad = this.findById(indexWebkit.child, 101, true)
                    this.showSale = this.findById(indexWebkit.child, 102, true)
                    this.showClient = this.findById(indexWebkit.child, 103, true)
                    this.showVisit = this.findById(indexWebkit.child, 104, true)
                    this.showOnline = this.findById(indexWebkit.child, 105, true)
                }
                // 在线管理员
                this.showOnline && this.getOnline_admin()
                // 最近访问统计
                this.showVisit && this.getVisit_client()
                // 销售额
                this.showOnLoad && this.onLoadInit()
                // 大客户统计
                this.showClient && this.getClientData()
            },
            mounted() {
                const website_name = localStorage.getItem('back_website_name')
                document.title = lang.homepage + '-' + website_name
                // 本年销售详情
                if (this.showSale) {
                    this.getSaleData()
                    window.addEventListener("resize", () => {
                        this.myChart.resize();
                    })
                }
            },
            methods: {
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
                async getSaleData() {
                    await saleData().then((res) => {
                        this.this_year_month_amount = res.data.data.this_year_month_amount
                        res.data.data.this_year_month_amount.forEach(item => {
                            this.XmonthList.push(item.month + '月')
                            this.YamountList.push(item.amount)
                        });
                    })
                    this.chartDom = document.getElementById('echars-box');
                    this.myChart = echarts.init(this.chartDom);
                    this.option = {
                        title: {
                            text: lang.sale_text126,
                            left: 30,
                            top: 26,
                            textStyle: {
                                fontSize: 18,
                                fontWeight: 'bold',
                                color: 'rgba(0, 0, 0, 0.9)',
                            }
                        },
                        tooltip: {},
                        color: '#0052D9',
                        xAxis: {
                            type: 'category',
                            data: this.XmonthList // x轴
                        },
                        yAxis: {
                            type: 'value'
                        },
                        series: [{
                            data: this.YamountList, // y轴
                            type: 'bar',
                        }]
                    }
                    this.myChart.setOption(this.option);
                },
                async getClientData() {
                    await clientData().then((res) => {
                        this.clients = res.data.data.clients.slice(0, 6)
                    })
                },
                async onLoadInit() {
                    await getIndex().then((res) => {
                        this.indexData = res.data.data
                    })
                },
                thousandth(num) {
                    if (!num) {
                        num = 0.00
                    }
                    let str = num.toString() // 数字转字符串
                    let str2 = null
                    // 如果带小数点
                    if (str.indexOf('.') !== -1) { // 带小数点只需要处理小数点左边的
                        const strArr = str.split('.') // 根据小数点切割字符串
                        str = strArr[0] // 小数点左边
                        str2 = strArr[1] // 小数点右边
                        //如12345.678  str=12345，str2=678
                    }
                    let result = '' // 结果
                    while (str.length > 3) { // while循环 字符串长度大于3就得添加千分位
                        // 切割法 ，从后往前切割字符串 ⬇️
                        result = ',' + str.slice(str.length - 3, str.length) + result
                        // 切割str最后三位，用逗号拼接 比如12345 切割为 ,345
                        // 用result接收，并拼接上一次循环得到的result
                        str = str.slice(0, str.length - 3) // str字符串剥离上面切割的后三位，比如 12345 剥离成 12
                    }

                    if (str.length <= 3 && str.length > 0) {
                        // 长度小于等于3 且长度大于0，直接拼接到result
                        // 为什么可以等于3 因为上面result 拼接时候在前面带上了‘,’
                        // 相当于123456 上一步处理完之后 result=',456' str='123'
                        result = str + result
                    }
                    // 最后判断是否带小数点（str2是小数点右边的数字）
                    // 如果带了小数点就拼接小数点右边的str2 ⬇️
                    str2 ? result = result + '.' + str2 : ''
                    return result
                },
                getOnline_admin() {
                    if (this.onlinePage.isEnd || this.onlinePage.isLoading) return
                    this.onlinePage.isLoading = true
                    online_admin(this.onlinePage.params).then((res) => {
                        if (res.data.data.list.length > 0) {
                            this.onlineAdminList = this.onlineAdminList.concat(res.data.data.list)
                        } else {
                            this.onlinePage.isEnd = true
                        }
                        this.onlinePage.isLoading = false
                    })
                },
                getVisit_client() {
                    if (this.visitPage.isEnd || this.visitPage.isLoading) return
                    this.visitPage.isLoading = true
                    visit_client(this.visitPage.params).then((res) => {
                        if (res.data.data.list.length > 0) {
                            this.visitClientList = this.visitClientList.concat(res.data.data.list)
                        } else {
                            this.visitPage.isEnd = true
                        }
                        this.visitPage.isLoading = false
                    })
                },
                visitScrollHandler(e) {
                    if (e.scrollBottom < 150 && !this.visitPage.isEnd && !this.visitPage.isLoading) {
                        this.visitPage.params.page++
                        this.getVisit_client()
                    }
                },
                onlineScrollHandler(e) {
                    if (e.scrollBottom < 150 && !this.onlinePage.isEnd && !this.onlinePage.isLoading) {
                        this.onlinePage.params.page++
                        this.getOnline_admin()
                    }
                },
            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
