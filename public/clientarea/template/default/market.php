{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/market.css">
</head>

<body>
    <!-- mounted之前显示 -->
    <div id="mainLoading">
        <div class="ddr ddr1"></div>
        <div class="ddr ddr2"></div>
        <div class="ddr ddr3"></div>
        <div class="ddr ddr4"></div>
        <div class="ddr ddr5"></div>
    </div>
    <div class="template market">
        <el-container>
            <aside-menu></aside-menu>
            <el-container>
                <top-menu></top-menu>
                <el-main>
                    <!-- 自己的东西 -->
                    <div class="main-card">
                        <div class="main-card-title">开发者管理</div>
                        <el-tabs v-model="activeName" @tab-click="handleClick">
                            <!-- 应用管理开始 -->
                            <el-tab-pane label="应用管理" name="1">
                                <div class="top-search">
                                    <div class="search-left">
                                        <el-button class="create-btn">创建应用</el-button>
                                    </div>
                                    <div class="search-right">
                                        <el-input placeholder="请输入名称/标识/分类"></el-input>
                                        <el-select placeholder="请选择系统类型"></el-select>
                                        <el-select placeholder="请选择应用分类"></el-select>
                                        <el-select placeholder="请选择应用状态"></el-select>
                                        <el-button class="search-btn">查询</el-button>
                                    </div>
                                </div>
                                <div class="content-table">
                                    <el-table v-loading="loading1" :data="data1" style="width: 100%;margin-bottom: .2rem;">
                                        <el-table-column prop="id" label="ID" width="100" :show-overflow-tooltip="true" align="left">
                                        </el-table-column>
                                        <el-table-column prop="id" label="应用名称" width="100" :show-overflow-tooltip="true" align="left">
                                        </el-table-column>
                                        <el-table-column prop="id" label="应用标识" width="100" :show-overflow-tooltip="true" align="left">
                                        </el-table-column>
                                        <el-table-column prop="id" label="应用分类" width="100" :show-overflow-tooltip="true" align="left">
                                        </el-table-column>
                                        <el-table-column prop="id" label="系统类型" min-width="100" :show-overflow-tooltip="true" align="left">
                                        </el-table-column>
                                        <el-table-column prop="id" label="销售价格" width="100" :show-overflow-tooltip="true" align="left">
                                        </el-table-column>
                                        <el-table-column prop="id" label="提交时间" width="100" :show-overflow-tooltip="true" align="left">
                                        </el-table-column>
                                        <el-table-column prop="id" label="状态" width="100" :show-overflow-tooltip="true" align="left">
                                        </el-table-column>
                                        <el-table-column prop="operation" label="操作" width="100" :show-overflow-tooltip="true" align="left">
                                        </el-table-column>
                                    </el-table>
                                    <pagination :page-data="params1" @sizechange="sizeChange1" @currentchange="currentChange1">
                                    </pagination>
                                </div>
                            </el-tab-pane>
                            <!-- 应用管理结束 -->

                            <!-- 服务管理开始 -->
                            <el-tab-pane label="服务管理" name="2">
                                <div class="top-search">
                                    <div class="search-left">
                                        <el-button type="primary">创建服务</el-button>
                                    </div>
                                    <div class="search-right">
                                        <el-input placeholder="请输入名称"></el-input>
                                        <el-select placeholder="请选择服务状态"></el-select>
                                        <el-select placeholder="请选择应用分类"></el-select>
                                        <el-button class="search-btn">查询</el-button>
                                    </div>
                                </div>
                                <div class="content-table">
                                    <el-table v-loading="loading1" :data="data1" style="width: 100%;margin-bottom: .2rem;">
                                        <el-table-column prop="id" label="订单ID" width="100" :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="购买商品"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="商品类型"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="购买用户"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="交易时间"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="金额"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="状态"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="operation" label="操作" width="100" :show-overflow-tooltip="true" align="left">
                                        </el-table-column>
                                    </el-table>
                                    <pagination :page-data="params1" @sizechange="sizeChange1" @currentchange="currentChange1">
                                    </pagination>
                                </div>
                            </el-tab-pane>
                            <!-- 服务管理结束 -->

                            <!-- 订单管理开始 -->
                            <el-tab-pane label="订单管理" name="3">
                                <div class="top-search">
                                    <div class="search-left">
                                       
                                    </div>
                                    <div class="search-right">
                                        <el-input placeholder="请输入名称/购买用户"></el-input>
                                        <el-select placeholder="请选择商品类型"></el-select>
                                        <el-button class="search-btn">查询</el-button>
                                    </div>
                                </div>
                                <div class="content-table">
                                    <el-table v-loading="loading1" :data="data1" style="width: 100%;margin-bottom: .2rem;">
                                        <el-table-column prop="id" label="订单ID" width="100" :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="购买商品"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="商品类型"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="购买用户"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="交易时间"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="金额"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="状态"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="operation" label="操作" width="100" :show-overflow-tooltip="true" align="left">
                                        </el-table-column>
                                    </el-table>
                                    <pagination :page-data="params1" @sizechange="sizeChange1" @currentchange="currentChange1">
                                    </pagination>
                                </div>
                            </el-tab-pane>
                            <!-- 订单管理结束 -->

                            <!-- 提现管理开始 -->
                            <el-tab-pane label="提现管理" name="4">
                                <div class="tx-box">
                                    <div class="tx-item">
                                        <div class="tx-item-top">
                                            <span></span>
                                            <span>可提现金额(元)</span>
                                            <span>体现</span>
                                            </div>
                                        <div class="tx-item-bom">
                                            <p> 126,560</p>
                                            <div class="question-icon">?</div>
                                        </div>
                                    </div>
                                    <div class="tx-item">
                                        <div class="tx-item-top">
                                            <span style="border-color:#1D57FF"></span>
                                            <span>已提现金额(元)</span>
                                            </div>
                                        <div class="tx-item-bom">
                                            <p> 126,560</p>
                                        </div>
                                    </div>
                                    <div class="tx-item">
                                        <div class="tx-item-top">
                                            <span style="border-color:#55D2B7"></span>
                                            <span>待收款金额(元)</span>
                                            </div>
                                        <div class="tx-item-bom">
                                            <p> 126,560</p>
                                        </div>
                                    </div>
                                    <div class="tx-item">
                                        <div class="tx-item-top">
                                            <span style="border-color:#666EFF"></span>
                                            <span>总销售额(元)</span>
                                            </div>
                                        <div class="tx-item-bom">
                                            <p> 126,560</p>
                                        </div>
                                    </div>
                                    <div class="tx-item">
                                        <div class="tx-item-top">
                                            <span style="border-color:#01AEFF"></span>
                                            <span>本月销售额(元)</span>
                                            </div>
                                        <div class="tx-item-bom">
                                            <p> 126,560</p>
                                        </div>
                                    </div>
                                    <div class="tx-item">
                                        <div class="tx-item-top">
                                            <span style="border-color:#C7E316"></span>
                                            <span>本年销售额(元)</span>
                                            </div>
                                        <div class="tx-item-bom">
                                            <p> 126,560</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="top-search">
                                    <div class="search-left">
                                    </div>
                                    <div class="search-right">
                                        <el-select placeholder="请选择状态"></el-select>
                                        <el-button class="search-btn">查询</el-button>
                                    </div>
                                </div>
                                <div class="content-table">
                                    <el-table v-loading="loading1" :data="data1" style="width: 100%;margin-bottom: .2rem;">
                                        <el-table-column prop="id" label="ID" width="100" :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="提现日期"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="提现金额"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="提现方式"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="operation" label="操作" width="100" :show-overflow-tooltip="true" align="left">
                                        </el-table-column>
                                    </el-table>
                                    <pagination :page-data="params1" @sizechange="sizeChange1" @currentchange="currentChange1">
                                    </pagination>
                                </div>
                            </el-tab-pane>
                            <!-- 提现管理结束 -->

                            <!-- 活动促销开始 -->
                            <el-tab-pane label="活动促销" name="5">
                                <div class="top-statistics">
                                    <div class="statistics-item">
                                        <div class="statistics-item-left">
                                            <div class="row">
                                                126,560 <span class="help-icon">?</span>
                                            </div>
                                            <div class="row">
                                                可提现金额({{commonData.currency_suffix}})
                                                <span class="tx-btn">提现</span>
                                            </div>
                                        </div>
                                        <div class="statistics-item-right">
                                            <img src="/{$template_catalog}/template/{$themes}/img/market/statistics-back.png" alt="">
                                        </div>
                                    </div>
                                    <div class="statistics-item">
                                        <div class="statistics-item-left">
                                            <div class="row">
                                                126,560 <span class="help-icon">?</span>
                                            </div>
                                            <div class="row">
                                                已提现金额({{commonData.currency_suffix}})
                                                <span class="tx-list-btn">查看记录</span>
                                            </div>
                                        </div>
                                        <div class="statistics-item-right">
                                            <img src="/{$template_catalog}/template/{$themes}/img/market/statistics-back.png" alt="">
                                        </div>
                                    </div>
                                    <div class="statistics-item">
                                        <div class="statistics-item-left">
                                            <div class="row">
                                                6,560 <span class="help-icon">?</span>
                                            </div>
                                            <div class="row">
                                                待提现金额({{commonData.currency_suffix}})
                                                <span class="tx-btn">提现</span>
                                            </div>
                                        </div>
                                        <div class="statistics-item-right">
                                            <img src="/{$template_catalog}/template/{$themes}/img/market/statistics-back.png" alt="">
                                        </div>
                                    </div>
                                    <div class="statistics-item">
                                        <div class="statistics-item-left">
                                            <div class="row">
                                                8,846 <span class="help-icon">?</span>
                                            </div>
                                            <div class="row">
                                                总销售额({{commonData.currency_suffix}})
                                                <span class="tx-btn">提现</span>
                                            </div>
                                        </div>
                                        <div class="statistics-item-right">
                                            <img src="/{$template_catalog}/template/{$themes}/img/market/statistics-back.png" alt="">
                                        </div>
                                    </div>
                                </div>
                                <div class="top-search">
                                    <div class="search-left">
                                    </div>
                                    <div class="search-right">
                                        <el-input placeholder="请输入名称/标识/分类"></el-input>
                                        <el-select placeholder="请选择商品类型"></el-select>
                                        <el-select placeholder="请选择状态"></el-select>
                                        <el-button class="search-btn">查询</el-button>
                                    </div>
                                </div>
                                <div class="content-table">
                                    <el-table v-loading="loading1" :data="data1" style="width: 100%;margin-bottom: .2rem;">
                                        <el-table-column prop="id" label="订单ID" width="100" :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="购买商品"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="商品类型"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="购买用户"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="交易时间"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="金额"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="状态"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="operation" label="操作" width="100" :show-overflow-tooltip="true" align="left">
                                        </el-table-column>
                                    </el-table>
                                    <pagination :page-data="params1" @sizechange="sizeChange1" @currentchange="currentChange1">
                                    </pagination>
                                </div>
                            </el-tab-pane>
                            <!-- 活动促销结束 -->

                            <!-- 投诉举报开始 -->
                            <el-tab-pane label="投诉举报" name="6">
                                <div class="top-search">
                                    <div class="search-left">
                                        <el-select placeholder="请选择投诉人"></el-select>
                                        <el-select placeholder="请选择商品类型"></el-select>
                                        <el-input placeholder="请输入名称"></el-input>
                                        <el-button class="search-btn">查询</el-button>
                                    </div>
                                    <div class="search-right">
                                    </div>
                                </div>
                                <div class="content-table">
                                    <el-table v-loading="loading1" :data="data1" style="width: 100%;margin-bottom: .2rem;">
                                        <el-table-column prop="id" label="订单ID" width="100" :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="购买商品"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="商品类型"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="购买用户"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="交易时间"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="金额"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="id" label="状态"  :show-overflow-tooltip="true" >
                                        </el-table-column>
                                        <el-table-column prop="operation" label="操作" width="100" :show-overflow-tooltip="true" align="left">
                                        </el-table-column>
                                    </el-table>
                                    <pagination :page-data="params1" @sizechange="sizeChange1" @currentchange="currentChange1">
                                    </pagination>
                                </div>
                            </el-tab-pane>
                            <!-- 投诉举报结束 -->
                            
                            <!-- 信息管理开始 -->
                            <el-tab-pane label="信息管理" name="7">
                                <div class="info-box">
                                    <p class="title">基础信息</p>
                                    <el-form :model="infoForm" label-width="1rem" ref="infoRef" :rules="infoRules">

                                        <div class="top">
                                            <el-form-item label="入驻类型">
                                                <el-radio v-model="infoForm.type" label="1">开发者</el-radio>
                                                <el-radio v-model="infoForm.type" label="2">服务商</el-radio>
                                                <el-radio v-model="infoForm.type" label="2">开发者和服务商</el-radio>
                                            </el-form-item>

                                            <el-form-item label="用户昵称" prop="name">
                                                <el-input v-model="infoForm.name"></el-input>
                                            </el-form-item>

                                            <el-form-item label="联系QQ" prop="qq">
                                                <el-input v-model="infoForm.qq"></el-input>
                                            </el-form-item>

                                            <el-form-item label="电子邮箱" prop="email">
                                                <el-input v-model="infoForm.email"></el-input>
                                            </el-form-item>

                                            <el-form-item label="网站网址" prop="address">
                                                <el-input v-model="infoForm.address"></el-input>
                                            </el-form-item>
                                        </div>

                                        <p class="title">商店信息</p>

                                        <el-form-item label="商店logo">
                                            <p  style="margin:0.01rem 0;" class="text"> 允许的后缀名: .jpg .gif .jpeg .png， 图片比例1:1，建议尺寸40px*40px</p>
                                            <el-upload
                                            action="#"
                                            :auto-upload="false"
                                            list-type="picture-card">
                                            <i class="el-icon-plus"></i>
                                            </el-upload>
                                        </el-form-item>

                                        <el-form-item label="商店头部">
                                            <div class="upload-type-box">
                                                <el-radio v-model="infoForm.uploadType"  label="1">banner图</el-radio>
                                                <el-radio v-model="infoForm.uploadType" label="2">自定义</el-radio>
                                                <el-radio v-model="infoForm.uploadType" label="3">关</el-radio>
                                            </div>
                                             <template v-if="infoForm.uploadType == 1">
                                                <p  style="margin:0.01rem 0;" class="text"> 允许的后缀名: .jpg .gif .jpeg .png，建议尺寸960px*396px</p>
                                                <el-upload
                                                    style="margin-top:0.1rem"
                                                    action="#"
                                                    :auto-upload="false"
                                                    list-type="picture-card">
                                                    <i class="el-icon-plus"></i>
                                                </el-upload>
                                                <el-input style="margin-top:0.2rem" v-model="infoForm.name" placeholder="请输入点击banner后的跳转路径，为空时不跳转"></el-input>
                                             </template>
                                             <template v-else-if="infoForm.uploadType == 2">
                                                <el-input type="textarea" :rows="4" v-model="infoForm.name" placeholder="允许输入HTML"></el-input>
                                             </template>
                                        </el-form-item>

                                        <el-form-item label="商店简介">
                                            <el-input type="textarea" :rows="4" v-model="infoForm.name"></el-input>
                                        </el-form-item>

                                        <el-form-item label=" ">
                                           <el-button @click="saveInfo">申请变更</el-button>
                                        </el-form-item>
                                    </el-form>
                                </div>
                            </el-tab-pane>
                            <!-- 信息管理结束 -->
                        </el-tabs>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/market.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/market.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}