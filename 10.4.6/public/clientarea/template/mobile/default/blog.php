{include file="header"}
<!-- 页面独有样式 -->

</head>

<body>
<!-- mounted之前显示 -->
<div class="template">
    <el-container>
        <aside-menu></aside-menu>
        <el-container>
            <top-menu></top-menu>
            <el-main>
                <!-- 自己的东西 -->
                <div class="main-card">
                    <iframe :src="iframeUrl" allowfullscreen="true" webkitallowfullscreen="true"
                            mozallowfullscreen="true" oallowfullscreen="true" msallowfullscreen="true" width="100%"
                            height="100%" frameborder="0"></iframe>
                </div>
            </el-main>
        </el-container>
    </el-container>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/js/blog.js"></script>
<style>

    /* 使用媒体查询定义手机端的样式 */
    @media (max-width: 768px) { /* 这里可以根据您的需求调整断点 */
        .main-card {
            height: calc(100vh - 2rem); /* 或者使用视窗单位，如100vh，但要注意内容可能会被裁剪 */
            width: 100%;
        }
        /* 其他需要适配到手机端的样式 */
    }
</style>
{include file="footer"}