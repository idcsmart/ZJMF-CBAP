{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting1559.css">
<div id="content" class="setting1559 table hasCrumb" v-cloak>
    <!-- crumb -->
    <div class="com-crumb">
        <span>设置</span>
        <t-icon name="chevron-right"></t-icon>
        <span class="cur">系统设置</span>
    </div>
    <t-card class="list-card-container">
        <!-- 主体 -->
        <div class="box scrollbar">
            <!-- title 开始 -->
            <div class="recording-title">
                <span class="title-text">系统设置</span>
                <div class="title-line"></div>
            </div>
            <!-- title 结束 -->
            <!-- 表格开始 -->
            <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="true" :loading="loading" table-layout="fixed" display-type="fixed-width" :hide-sort-tips="true" >
                <template #search_limit="{row}">
                    <div class="limit" v-show="editId !== row.id">
                        <div class="left-text">
                            {{row.search_limit}}
                        </div>
                        <t-icon @click="edit(row)" class="limit-icon" name="edit-1"></t-icon>
                    </div>
                    <div class="limit" v-show="editId === row.id">
                        <t-input :maxlength="10" :style=`width:${inputWidth}px` @change="inputChange" class="limit-input" v-model="editNum"></t-input>
                        <t-icon @click="sub" class="limit-icon" name="check"></t-icon>
                    </div>
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
<script src="/{$template_catalog}/template/{$themes}/js/setting1559.js"></script>
{include file="footer"}