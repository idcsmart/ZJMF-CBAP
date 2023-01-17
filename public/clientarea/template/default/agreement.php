<!DOCTYPE html>
<html lang="en" theme-color="default" theme-mode>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <title></title>
    <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common/element.css">
    <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/agreement.css">
    <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common/common.css">
    <script>
        const url = "/{$template_catalog}/template/{$themes}/"
    </script>
    <script src="/{$template_catalog}/template/{$themes}/js/common/lang.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/common/common.js"></script>
</head>

<body>
    <div id="mainLoading">
        <div class="ddr ddr1"></div>
        <div class="ddr ddr2"></div>
        <div class="ddr ddr3"></div>
        <div class="ddr ddr4"></div>
        <div class="ddr ddr5"></div>
    </div>
    <div id="content" class="template">
        <div class="contnet-right-out">
            <div class="content-right" v-show="detailData.id" v-loading="contentLoading">
                <!-- 标题 -->
                <div class="right-title">
                    {{detailData.title}}
                </div>
                <!-- 更新时间 -->
                <div class="right-keywords-time">
                    <div class="right-time">
                        更新时间：{{detailData.create_time | formateTime}}
                    </div>
                    <div class="right-keywords">
                        关键字：{{detailData.keywords}}
                    </div>
                </div>

                <!-- 主体内容 -->
                <div class="right-content" v-html="detailData.content">
                </div>
                <!-- 附件 -->
                <div class="right-attachment">
                    附件：
                    <div class="right-attachment-item" v-for="(f,i) in detailData.attachment" :key="i" @click="downloadfile(f)">
                        <span :title="f.split('^')[1]">
                            <i class="el-icon-tickets"></i><span>{{f.split('^')[1]}}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- =======公共======= -->
    <script src="/{$template_catalog}/template/{$themes}/js/common/vue.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/common/element.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/common/axios.min.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/request.js"></script>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/agreement.js"></script>


</body>

</html>