(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('template')[0]
        Vue.prototype.lang = window.lang
        new Vue({
            components: {
                asideMenu,
                topMenu,
                cloudTop,
            },
            created() {
                // 获取产品id
                this.id = location.href.split('?')[1].split('=')[1]
                this.getstarttime(1)

            },
            mounted() {
                this.getCpuList()
                this.getBwList()
                this.getDiskLIoList()
                this.getMemoryList()
            },
            updated() {

            },
            destroyed() {

            },
            data() {
                return {
                    commonData: {},
                    id: null,
                    // 开始时间
                    startTime: '',
                    selectValue: '1',
                    loading1: false,
                    loading2: false,
                    loading3: false,
                    loading4: false,
                }
            },
            filters: {
                formateTime(time) {
                    if (time && time !== 0) {
                        return formateDate(time * 1000)
                    } else {
                        return "--"
                    }
                },

            },
            methods: {
                // 获取通用配置
                getCommonData() {
                    getCommon().then(res => {
                        if (res.data.status === 200) {
                            this.commonData = res.data.data
                            localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
                        }
                    })
                },
                // 获取cpu用量数据
                getCpuList() {
                    this.loading1 = true
                    const params = {
                        id: this.id,
                        start_time: this.startTime,
                        type: 'cpu'
                    }
                    chartList(params).then(res => {
                        if (res.data.status === 200) {
                            const list = res.data.data.list
                            let x = []
                            let y = []
                            list.forEach(item => {
                                x.push(formateDate(item.time * 1000))
                                y.push((item.value * 100).toFixed(2))
                            });

                            const cpuOption = {
                                title: {
                                    text: 'CPU占用量',
                                },
                                tooltip: {
                                    show: true,
                                    trigger: "axis",
                                },
                                grid: {
                                    left: '5%',
                                    right: '4%',
                                    bottom: '5%',
                                    containLabel: true
                                },
                                xAxis: {
                                    type: "category",
                                    boundaryGap: false,
                                    data: x,
                                },
                                yAxis: {
                                    type: "value",
                                },
                                series: [
                                    {
                                        name: "占用量(%)",
                                        data: y,
                                        type: "line",
                                        areaStyle: {},
                                    },
                                ],
                            }

                            var CpuChart = echarts.init(document.getElementById('cpu-echart'));
                            CpuChart.setOption(cpuOption);
                        }
                        this.loading1 = false
                    }).catch(err => {
                        this.loading1 = false
                    })




                },
                // 获取网络宽度
                getBwList() {
                    this.loading2 = true
                    const params = {
                        id: this.id,
                        start_time: this.startTime,
                        type: 'bw'
                    }
                    chartList(params).then(res => {
                        if (res.data.status === 200) {
                            const list = res.data.data.list

                            let xAxis = []
                            let yAxis = []
                            let yAxis2 = []

                            list.forEach(item => {
                                xAxis.push(formateDate(item.time * 1000))
                                yAxis.push(item.in_bw.toFixed(2))
                                yAxis2.push(item.out_bw.toFixed(2));
                            });


                            const options = {
                                title: {
                                    text: '网络宽带',
                                },
                                tooltip: {
                                    show: true,
                                    trigger: "axis",
                                },
                                grid: {
                                    left: '5%',
                                    right: '4%',
                                    bottom: '5%',
                                    containLabel: true
                                },
                                xAxis: {
                                    type: "category",
                                    boundaryGap: false,
                                    data: xAxis,
                                },
                                yAxis: {
                                    type: "value",
                                },
                                series: [
                                    {
                                        name: "进带宽(bps)",
                                        data: yAxis,
                                        type: "line",
                                        areaStyle: {},
                                    },
                                    {
                                        name: "出带宽(bps)",
                                        data: yAxis2,
                                        type: "line",
                                        areaStyle: {},
                                    },
                                ],
                            }



                            var bwChart = echarts.init(document.getElementById('bw-echart'));
                            bwChart.setOption(options);
                        }
                        this.loading2 = false
                    }).catch(err => {
                        this.loading2 = false
                    })
                },
                // 获取磁盘IO
                getDiskLIoList() {
                    this.loading3 = true
                    const params = {
                        id: this.id,
                        start_time: this.startTime,
                        type: 'disk_io'
                    }

                    chartList(params).then(res => {
                        if (res.data.status === 200) {
                            const list = res.data.data.list

                            let xAxis = []
                            let yAxis = []
                            let yAxis2 = []
                            let yAxis3 = []
                            let yAxis4 = []

                            list.forEach(item => {
                                xAxis.push(formateDate(item.time * 1000))
                                yAxis.push((item.read_bytes / 1024 / 1024).toFixed(2));
                                yAxis2.push((item.read_iops / 1024 / 1024).toFixed(2));
                                yAxis3.push(item.write_bytes.toFixed(2));
                                yAxis4.push(item.write_iops.toFixed(2));
                            });

                            const options = {
                                title: {
                                    text: '磁盘IO',
                                },
                                tooltip: {
                                    show: true,
                                    trigger: "axis",
                                },
                                grid: {
                                    left: '5%',
                                    right: '4%',
                                    bottom: '5%',
                                    containLabel: true
                                },
                                xAxis: {
                                    type: "category",
                                    boundaryGap: false,
                                    data: xAxis,
                                },
                                yAxis: {
                                    // name: "单位（B/s）",
                                    type: "value",
                                },
                                series: [
                                    {
                                        name: "读取速度(MB/s)",
                                        data: yAxis,
                                        type: "line",
                                        areaStyle: {},
                                    },
                                    {
                                        name: "写入速度(MB/s)",
                                        data: yAxis2,
                                        type: "line",
                                        areaStyle: {},
                                    },
                                    {
                                        name: "读取IOPS",
                                        data: yAxis3,
                                        type: "line",
                                        areaStyle: {},
                                    },
                                    {
                                        name: "写入IOPS",
                                        data: yAxis4,
                                        type: "line",
                                        areaStyle: {},
                                    },
                                ],
                            }



                            var diskIoChart = echarts.init(document.getElementById('disk-io-echart'));
                            diskIoChart.setOption(options);
                        }
                        this.loading3 = false
                    }).catch(err => {
                        this.loading3 = false
                    })
                },
                // 获取内存用量
                getMemoryList() {
                    this.loading4 = true
                    const params = {
                        id: this.id,
                        start_time: this.startTime,
                        type: 'memory'
                    }
                    chartList(params).then(res => {
                        if (res.data.status === 200) {
                            const list = res.data.data.list

                            let xAxis = []
                            let yAxis = []
                            let yAxis2 = []

                            list.forEach(item => {
                                xAxis.push(formateDate(item.time * 1000))
                                yAxis.push((item.total / 1024 / 1024).toFixed(2));
                                yAxis2.push((item.used / 1024 / 1024).toFixed(2));
                            });


                            const options = {
                                title: {
                                    text: '内存用量',
                                },
                                tooltip: {
                                    show: true,
                                    trigger: "axis",
                                },
                                grid: {
                                    left: '5%',
                                    right: '4%',
                                    bottom: '5%',
                                    containLabel: true
                                },
                                xAxis: {
                                    type: "category",
                                    boundaryGap: false,
                                    data: xAxis,
                                },
                                yAxis: {
                                    type: "value",
                                },
                                series: [
                                    {
                                        name: "总内存(MB)",
                                        data: yAxis,
                                        type: "line",
                                        areaStyle: {},
                                    },
                                    {
                                        name: "内存使用量(MB)",
                                        data: yAxis2,
                                        type: "line",
                                        areaStyle: {},
                                    },
                                ],
                            }



                            var memoryChart = echarts.init(document.getElementById('memory-echart'));
                            memoryChart.setOption(options);
                        }
                        this.loading4 = false
                    }).catch(err => {
                        this.loading4 = false
                    })
                },
                //时间转换
                getstarttime(type) {
                    // 1: 过去24小时 2：过去三天 3：过去七天
                    let nowtime = parseInt(new Date().getTime() / 1000);
                    if (type == 1) {
                        this.startTime = nowtime - 24 * 60 * 60;
                    } else if (type == 2) {
                        this.startTime = nowtime - 24 * 60 * 60 * 3;
                    } else if (type == 3) {
                        this.startTime = nowtime - 24 * 60 * 60 * 7;
                    }
                },
                // 时间选择框
                selectChange(e) {
                    // 计算开始时间
                    this.getstarttime(e)

                    // 重新拉取图表数据
                    this.getCpuList()
                    this.getBwList()
                    this.getDiskLIoList()
                    this.getMemoryList()
                },

            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
