{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/search1559.css">
<div id="content" class="search1559 table hasCrumb" v-cloak>
    <!-- crumb -->
    <div class="com-crumb">
        <span>设置</span>
        <t-icon name="chevron-right"></t-icon>
        <span class="cur">资料查询</span>
    </div>
    <t-card class="list-card-container">
        <!-- 主体 -->
        <div class="box scrollbar">
            <!-- 查询方式开始 -->
            <div class="search-way">
                <t-select class="search-select" v-model="params.type">
                    <t-option v-for="item in options" :key="item.id" :label="item.label" :value="item.value"></t-option>
                </t-select>
                <t-input class="search-input" v-model="params.keywords"></t-input>
                <t-button class="search-btn" @click="search">立刻查询</t-button>
            </div>
            <!-- 查询方式结束 -->
            <!-- title 开始 -->
            <div class="recording-title">
                <span class="title-text">查询结果</span>
                <div class="title-line"></div>
            </div>
            <!-- title 结束 -->
            <!-- 主体开始 -->
            <!-- 次数耗尽开始 -->
            <div class="times-over" v-show="isTimesOver">
                <t-icon class="l-icon" name="error-circle"></t-icon>
                {{resultText}}
            </div>
            <!-- 次数耗尽结束 -->
            <!-- 无返回结果开始 -->
            <div class="no-result" v-show="isResult">
                <t-icon class="l-icon" name="search"></t-icon>{{resultText}}
            </div>
            <!-- 无返回结果结束 -->
            <!-- ip 查找返回结果展示 开始 -->
            <div v-show="isShowUser" class="user-result">
                <div class="user-item">
                    <div class="user-label">所属客户:</div>
                    <div class="user-value">{{userData.username}}</div>
                </div>
                <div class="user-item">
                    <div class="user-label">手机:</div>
                    <div class="user-value">{{userData.phonenumber}}</div>
                </div>
                <div class="user-item">
                    <div class="user-label">邮箱:</div>
                    <div class="user-value">{{userData.email}}</div>
                </div>
                <div class="user-item">
                    <div class="user-label">QQ:</div>
                    <div class="user-value">{{userData.qq}}</div>
                </div>
                <div class="user-item">
                    <div class="user-label">所属销售:</div>
                    <div class="user-value">{{userData.user_nickname}}</div>
                </div>
            </div>
            <!-- ip 查找返回结果展示 结束 -->
            <!-- 表格开始 -->
            <div v-show="isShowTable" class="resule-table">
                <!-- 表格开始 -->
                <t-table row-key="id" :data="showData" size="medium" :columns="columns" :hover="true" :loading="loading" table-layout="fixed" display-type="fixed-width" :hide-sort-tips="true" :max-height="maxHeight">
                    <template #domainstatus="{row}">
                        {{state[row.domainstatus].text}}
                    </template>
                    <template #regdate="{row}">
                        {{row.regdate ? moment(row.regdate * 1000).format('YYYY-MM-DD HH:mm') : '--'}}
                    </template>
                    <template #nextduedate="{row}">
                        {{row.nextduedate ? moment(row.nextduedate * 1000).format('YYYY-MM-DD HH:mm') : '--'}}
                    </template>
                </t-table>
                <!-- 表格结束 -->
                <!-- 分页开始 -->
                <t-pagination :total="total" :page-size="params.limit" :current="params.page" :page-size-options="pageSizeOptions" @change="changePage" />
                <!-- 分页结束 -->
            </div>
            <!-- 表格结束 -->
            <!-- 主体结束 -->
        </div>

    </t-card>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/recording1559.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/search1559.js"></script>
{include file="footer"}