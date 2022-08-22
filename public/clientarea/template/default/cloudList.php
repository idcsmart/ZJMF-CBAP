{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/cloudList.css">
</head>

<body>
    <div id="mainLoading">
        <div class="ddr ddr1"></div>
        <div class="ddr ddr2"></div>
        <div class="ddr ddr3"></div>
        <div class="ddr ddr4"></div>
        <div class="ddr ddr5"></div>
    </div>
    <div id="cloudList" class="template">
        <el-container>
            <aside-menu :menu-active-id="2"></aside-menu>
            <el-container>
                <top-menu ></top-menu>
                <el-main>
                    <!-- 自己的东西 -->
                    <div class="main-card">
                        <div class="main-card-title">{{lang.cloud_title}}</div>
                        <div class="main-card-menu">
                            <span v-for="item in menuList" :key="item.id" class="card-menu-item" :class="menuActiveId === item.id?'active':''" @click="menuActiveId = item.id">
                                {{ item.text }}
                            </span>
                        </div>
                        <!-- 筛选 -->
                        <div class="main-card-search">
                            <el-select v-model="params.data_center_id" style="width:3.2rem" :filterable="true" @change="selectChange" :clearable="true" :placeholder="lang.cloud_tip_1">
                                <el-option v-for="item in center" :key="item.id" :value="item.id" :label="item.label">
                                </el-option>
                            </el-select>
                            <!-- <el-input suffix-icon="el-input__icon el-icon-search" @input="inputChange" v-model="params.keywords" style="width: 3.2rem;margin-left: .2rem;" :placeholder="lang.cloud_tip_2"></el-input> -->
                            <el-input v-model="params.keywords" style="width: 3.2rem;margin-left: .2rem;" :placeholder="lang.cloud_tip_2" @keyup.enter.native="inputChange" clearable @clear="getCloudList">
                                <i class="el-icon-search input-search" slot="suffix" @Click="inputChange"></i>
                            </el-input>
                        </div>
                        <!-- 表格 -->
                        <!-- 分页 -->
                        <div class="main-card-table">
                            <div class="table">
                                <el-table v-loading="loading" :data="cloudData" style="width: 100%;margin-bottom: .2rem;">
                                    <el-table-column prop="id" label="ID" width="150" align="left">
                                    </el-table-column>
                                    <el-table-column :label="lang.cloud_table_head_1" width="180">
                                        <template slot-scope="scope">
                                            <div class="area">
                                                <img :src=" imgUrl + '/img/cloud/' + scope.row.country_code + '.png'" class="area-img">
                                                <span class="area-country">{{scope.row.country}}</span>
                                                <span class="area-city">{{scope.row.city}}</span>
                                                <span>&nbsp;{{scope.row.area}}</span>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="name" :label="lang.cloud_table_head_2" min-width="300">
                                        <template slot-scope="scope">
                                            <div class="cloud-name">
                                                <span class="packge-name">{{ scope.row.package_name }}</span>
                                                <span class="name">{{ scope.row.name }}</span>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="ip" label="IP" width="150">
                                    </el-table-column>
                                    <el-table-column prop="power_status" :label="lang.cloud_table_head_3" width="150">
                                        <template slot-scope="scope">
                                            <div v-if="scope.row.status === 'Active'" class="power-status">
                                                <img :src="powerStatus[scope.row.power_status].icon">
                                                <span class="status-text">{{powerStatus[scope.row.power_status].text}}</span>
                                            </div>
                                            <div v-if="scope.row.status === 'Suspended'" class="power-status">
                                                <img :src="powerStatus[scope.row.status].icon">
                                                <span class="status-text">{{powerStatus[scope.row.status].text}}</span>
                                            </div>
                                            <div v-if="scope.row.status !== 'Active' && scope.row.status !== 'Suspended'" class="status" :style="'color:'+status[scope.row.status].color + ';background:' + status[scope.row.status].bgColor">
                                                {{status[scope.row.status].text }}
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="id" label="OS" width="200">
                                        <template slot-scope="scope">
                                            <img v-if="scope.row.icon" class="os-img" :src="'http://kfc.idcsmart.com/'+scope.row.icon">
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="due_time" :label="lang.cloud_table_head_4" width="180">
                                        <template slot-scope="scope">
                                            {{scope.row.due_time | formateTime}}
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="id" :label="lang.cloud_table_head_5" width="100" align="center">
                                        <template slot-scope="scope">
                                            <div class="operation">
                                                <span class="dot">...</span>
                                                <span class="more"></span>
                                            </div>
                                        </template>
                                    </el-table-column>

                                </el-table>
                            </div>
                            <div class="page">
                                <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange" />
                            </div>
                        </div>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/cloud.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/cloudList.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
    {include file="footer"}