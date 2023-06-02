(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('template')[0]
        Vue.prototype.lang = window.lang
        new Vue({
            components: {
                asideMenu,
                topMenu,
                payDialog,
                pagination,
            },
            created() {
                this.getCommonData()
                // 获取产品id
                this.id = location.href.split('?')[1].split('=')[1]
                this.params.order_id = location.href.split('?')[1].split('=')[1]
                this.getOrderDetail()
            },
            mounted() {

            },
            updated() {
                // 关闭loading
                document.getElementById('mainLoading').style.display = 'none';
                document.getElementsByClassName('template')[0].style.display = 'block'
            },
            destroyed() {

            },
            data() {
                return {
                    commonData: {},
                    orderList: [],
                    transactionList: [],
                    id: '',
                    orderData: {},
                    params: {
                        keywords: '',
                        order_id: '',
                        limit: 99999,
                        pageSizes: [20, 50, 100],
                        total: 0,
                        orderby: 'id',
                        sort: 'desc',
                    },
                    creditType: {
                        Artificial: '人工',
                        Recharge: '充值',
                        Applied: '应用至订单',
                        Refund: '退款',
                        Withdraw: '提现',
                    }
                }
            },
            filters: {
                formateTime(time) {
                    if (time && time !== 0) {
                        return formateDate(time * 1000)
                    } else {
                        return "--"
                    }
                }
            },
            methods: {
                handelPdf() {
                    // const printDom = this.$refs.orderPageRef
                    // html2canvas(printDom, {
                    //     scale: 2,
                    //     dpi: 190, // 设置dpi，会使图片高清一些
                    // }).then(canvas => {
                    //     //内容的宽度
                    //     let contentWidth = canvas.width;
                    //     //内容高度
                    //     let contentHeight = canvas.height;
                    //     //一页pdf显示html页面生成的canvas高度,a4纸的尺寸[595.28,841.89];
                    //     let pageHeight = contentWidth / 595.28 * 841.89;
                    //     //未生成pdf的html页面高度
                    //     let leftHeight = contentHeight;
                    //     //页面偏移
                    //     let position = 0;
                    //     //a4纸的尺寸[595.28,841.89]，html页面生成的canvas在pdf中图片的宽高
                    //     const imgWidth = 595.28;
                    //     const imgHeight = 592.28 / contentWidth * contentHeight;
                    //     //canvas转图片数据
                    //     const pageData = canvas.toDataURL('image/jpeg', 1.0);
                    //     //新建JsPDF对象
                    //     let PDF = new jsPDF('', 'px', 'a4');
                    //     console.log(PDF);
                    //     //判断是否分页
                    //     if (leftHeight < pageHeight) {
                    //         PDF.addImage(pageData, 'JPEG', 0, 0, imgWidth, imgHeight)
                    //     } else {
                    //         while (leftHeight > 0) {
                    //             PDF.addImage(pageData, 'JPEG', 0, position, imgWidth, imgHeight);
                    //             leftHeight -= pageHeight;
                    //             position -= 841.89;
                    //             if (leftHeight > 0) {
                    //                 PDF.addPage()
                    //             }
                    //         }
                    //     }
                    //     //保存文件
                    //     PDF.save(`${this.id}-订单详情.pdf`)
                    // })
                    window.scrollTo(0, 0);
                    if (this.$refs.payBtnRef) {
                        this.$refs.payBtnRef.style.display = "none"
                    }
                    const element = this.$refs.orderPageRef
                    // 处理打印出来的表格宽度不对

                    const opt = {
                        // 转换后的pdf的外边距分别为：上: 10px、右: 20px、下: 10px、左:20px
                        margin: [20, 10, 20, 10],
                        filename: `${this.id}-订单详情.pdf`,
                        image: { type: 'jpeg', quality: 1 },
                        html2canvas: { scale: 2 },
                        jsPDF: { orientation: 'portrait', unit: 'pt', format: 'a4' },
                    };
                    // 调用html2pdf库的方法生成PDF文件并下载
                    html2pdf().set(opt).from(element).save().then(() => {
                        if (this.$refs.payBtnRef) {
                            this.$refs.payBtnRef.style.display = "block"
                        }
                    });

                },
                goBack() {
                    // 回退到上一个页面
                    history.back();
                },
                // 余额变更记录
                getCreditList() {
                    creditList({ order_id: this.id, page: 1, limit: 99999 }).then((res) => {
                        this.params.total = this.params.total + res.data.data.count
                        const arr = res.data.data.list.map((item) => {
                            return {
                                create_time: item.create_time,
                                transaction_number: `余额${this.creditType[item.type]}`,
                                amount: Math.abs(item.amount).toFixed(2),
                            }
                        })
                        this.transactionList = this.transactionList.concat(arr)
                    })
                },
                // 每页展示数改变
                sizeChange(e) {
                    this.params.limit = e
                    this.params.page = 1
                    // 获取列表
                    this.getTransactionDetail()
                },
                // 当前页改变
                currentChange(e) {
                    this.params.page = e
                    this.getTransactionDetail()
                },
                goPay() {
                    this.$refs.payDialog.showPayDialog(this.id);
                },
                // 支付成功回调
                paySuccess(e) {
                    this.getOrderDetail();
                    this.getTransactionDetail()
                },
                // 取消支付回调
                payCancel(e) {
                },
                getOrderDetail() {
                    orderDetail(this.id).then((res) => {
                        this.orderData = res.data.data.order
                        this.getTransactionDetail()
                    })
                },
                getTransactionDetail() {
                    transactionDetail(this.params).then((res) => {
                        this.transactionList = res.data.data.list
                        this.params.total = res.data.data.count
                        if (this.orderData.type !== 'recharge') {
                            this.getCreditList()
                        }
                    })
                },
                // 获取通用配置
                getCommonData() {
                    this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
                    document.title = this.commonData.website_name + '-订单详情'
                }
            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
