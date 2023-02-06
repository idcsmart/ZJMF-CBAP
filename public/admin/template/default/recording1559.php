{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/recording1559.css">
<div id="content" class="recording1559 table hasCrumb" v-cloak>
    <!-- crumb -->
    <div class="com-crumb">
        <span>设置</span>
        <t-icon name="chevron-right"></t-icon>
        <span class="cur">查询记录</span>
    </div>
    <t-card class="list-card-container">
        <!-- 主体 -->
        <div class="box scrollbar">
            <!-- title 开始 -->
            <div class="recording-title">
                <span class="title-text">查询记录</span>
                <div class="title-line"></div>
            </div>
            <!-- title 结束 -->
            <!-- search 开始 -->
            <div class="search-way">
                <t-date-picker mode="date" class="time1" v-model="params.start_time" placeholder="起始时间" value-type="YYYY-MM-DD" clearable allow-input @change="handleChange"></t-date-picker>
                <t-date-picker mode="date" class="time2" v-model="params.end_time" placeholder="结束时间" value-type="YYYY-MM-DD" clearable allow-input @change="handleChange"></t-date-picker>
                <t-input class="search-input" v-model="params.keywords"></t-input>
                <t-button class="search-btn" @click="getSeacrhLog">立刻查询</t-button>
            </div>
            <!-- search 结束 -->
            <!-- 表格开始 -->
            <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="true" :loading="loading" table-layout="fixed" display-type="fixed-width" :hide-sort-tips="true" >
                <template #create_time="{row}">
                    {{row.create_time ? moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm') : '--'}}
                </template>
            </t-table>
            <!-- 表格结束 -->
            <!-- 分页开始 -->
            <t-pagination :total="total" :page-size="params.limit" :current="params.page" :page-size-options="pageSizeOptions" @change="changePage" />
            <!-- 分页结束 -->
        </div>

    </t-card>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/recording1559.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/recording1559.js"></script>
{include file="footer"}