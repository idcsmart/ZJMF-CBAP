{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/agreement.css">
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
                        {{lang.agreement_text1}}：{{detailData.create_time | formateTime}}
                    </div>
                    <div class="right-keywords">
                        {{lang.agreement_text2}}：{{detailData.keywords}}
                    </div>
                </div>

                <!-- 主体内容 -->
                <div class="right-content" v-html="formatHtml(detailData.content)">
                </div>
                <!-- 附件 -->
                <div class="right-attachment">
                    {{lang.agreement_text3}}：
                    <div class="right-attachment-item" v-for="(f,i) in detailData.attachment" :key="i"
                        @click="downloadfile(f)">
                        <span :title="f.split('^')[1]">
                            <i class="el-icon-tickets"></i><span>{{f.split('^')[1]}}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="/{$template_catalog}/template/{$themes}/js/agreement.js"></script>
    {include file="footer"}