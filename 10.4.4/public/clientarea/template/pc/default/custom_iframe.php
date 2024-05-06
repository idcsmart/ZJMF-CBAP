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
                    <iframe src="{$iframe_url}" allowfullscreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" oallowfullscreen="true" msallowfullscreen="true" width="100%" height="100%" frameborder="0"></iframe>
                </div>
            </el-main>
        </el-container>
    </el-container>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/goods.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/product_list.js"></script>
{include file="footer"}