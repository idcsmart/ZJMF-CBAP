{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/rc_order.css">
<div id="content" class="re-order " v-cloak>
    <div class="top-card">
        <div class="com-h-box">
            <ul class="common-tab">
                <li class="active">
                    <a>订单列表</a>
                </li>
                <li>
                    <a href="rc_order_config.htm">基础配置</a>
                </li>
            </ul>
        </div>
        <div class="top-statistics">
            <div class="statistics-item">
                <div class="l">
                    <span class="num">{{staticsticsData.sale_total}}</span>
                    <span class="text">总销售额({{currency_suffix}})</span>
                </div>
                <div class="r">
                    <img :src="`${urlPath}/img/rc/statistics-01.png`" alt="">
                </div>
            </div>
            <div class="statistics-item">
                <div class="l">
                    <span class="num">{{staticsticsData.arrived_total}}</span>
                    <span class="text">已到账金额({{currency_suffix}})</span>
                </div>
                <div class="r">
                    <img :src="`${urlPath}/img/rc/statistics-02.png`" alt="">
                </div>
            </div>
            <div class="statistics-item">
                <div class="l">
                    <span class="num">{{staticsticsData.final_total}}</span>
                    <span class="text">剩余尾款金额({{currency_suffix}})</span>
                </div>
                <div class="r">
                    <img :src="`${urlPath}/img/rc/statistics-03.png`" alt="">
                </div>
            </div>
        </div>
    </div>
    <t-card class="list-card-container table">
        <div class="top-search">
            <t-input clearable class="key-input" v-model="params.keywords" placeholder="请输入商品信息">
            </t-input>
            <t-select clearable :loading="loading" :options="options" :on-search="remoteMethod" filterable class="client-select" v-model="params.client_id" placeholder="请搜索购买用户">
            </t-select>
            <t-button class="search-btn" @click="search">查询</t-button>
        </div>
        <t-table row-key="id" :data="dataList" :loading="dataLoading" size="medium" :columns="columns" @row-click="rowClick">
            <template #buy_amount="{row}">
                {{currency_prefix}}{{row.buy_amount}}
            </template>
            <template #create_time="{row}">
                {{moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm')}}
            </template>
            <template #cycle_min="{row}">
                {{row.cycle_min}}-{{row.cycle_max}}周
                <span v-if="(row.status != 'Unpaid') && (row.status != 'Production') && (row.status != 'Ordered')">(已生产)</span>
            </template>
            <template #distribution="{row}">
                <div class="distribution" @click="stopPop">
                    <div v-if="row.distribution" class="text" :title="row.distribution">{{row.distribution}}</div>
                    <t-icon v-if="row.distribution" class="copy-icon" name="file-copy" @click="copyMsg(row.distribution)"></t-icon>
                    <span v-if="!row.distribution">--</span>
                </div>
            </template>
            <template #status="{row}">
                <span v-if="row.status != 'Unpaid' && row.status != 'FinalUnpaid'" class="status" :class="row.status">{{stataus[row.status]}}</span>
                <span v-if="row.status == 'Unpaid'" class="status" :class="row.status">{{stataus[row.status]}}({{currency_prefix}}{{row.amount}})</span>
                <span v-if="row.status == 'FinalUnpaid'" class="status" :class="row.status">{{stataus[row.status]}}({{currency_prefix}}{{row.final_amount}})</span>
            </template>
            <template #operation="{row}">
                <div @click="stopPop">
                    <t-tooltip content="开始生产" :show-arrow="false" theme="light">
                        <img v-if="row.status == 'Ordered'" class="operation-icon" :src="`${urlPath}/img/rc/operation1.png`" alt="" @click="showProduction('production',row)">
                    </t-tooltip>
                    <t-tooltip content="生产完成" :show-arrow="false" theme="light">
                        <img v-if="row.status == 'Production'" class="operation-icon" :src="`${urlPath}/img/rc/operation2.png`" alt="" @click="showSure('finish',row.id)">
                    </t-tooltip>
                    <t-tooltip content="修改预计周期" :show-arrow="false" theme="light">
                        <img v-if="row.status == 'Production'" class="operation-icon" :src="`${urlPath}/img/rc/operation3.png`" alt="" @click="showProduction('edit',row)">
                    </t-tooltip>
                    <t-tooltip content="交付商品" :show-arrow="false" theme="light">
                        <img v-if="row.status == 'Delivery'" class="operation-icon" :src="`${urlPath}/img/rc/operation4.png`" alt="" @click="showDelivery(row.id)">
                    </t-tooltip>
                    <t-tooltip content="已付尾款" :show-arrow="false" theme="light">
                        <img v-if="row.status == 'FinalUnpaid'" class="operation-icon" :src="`${urlPath}/img/rc/operation5.png`" alt="" @click="showSure('failPaid',row.id)">
                    </t-tooltip>
                    <t-tooltip content="已支付" :show-arrow="false" theme="light">
                        <img v-if="row.status == 'Unpaid'" class="operation-icon" :src="`${urlPath}/img/rc/operation6.png`" alt="" @click="showSure('paid',row.id)">
                    </t-tooltip>
                    <span v-if="row.status == 'Delivered' || row.status == 'Cancelled'">--</span>
                </div>
            </template>
        </t-table>
        <t-pagination :total="total" :page-size="params.limit" :current="params.page" :page-size-options="pageSizeOptions" @change="changePage" />
    </t-card>

    <!-- 二次确认弹窗 -->
    <t-dialog theme="warning" :header="header" :visible.sync="visible">
        <template slot="footer">
            <t-button theme="primary" @click="sure">{{lang.sure}}</t-button>
            <t-button theme="default" @click="visible=false">{{lang.cancel}}</t-button>
        </template>
    </t-dialog>

    <!-- 开始生产弹窗 -->
    <t-dialog :header="productionHead" :visible.sync="productionVisible" :footer="false">
        <div class="dialog-main">
            <t-form :data="productionForm" label-align="left" :rules="productionRules" @submit="productionSub">
                <t-form-item name="cycle" label="预计交付周期">
                    <div class="cycle-item">
                        <t-input-number v-model="productionForm.cycle_min" theme="normal" :min="0"></t-input-number>
                        -
                        <t-input-number v-model="productionForm.cycle_max" theme="normal" :min="0"></t-input-number>
                    </div>
                </t-form-item>
                <t-form-item>
                    <div class="dialog-footer">
                        <t-button theme="primary" type="submit">{{lang.sure}}</t-button>
                        <t-button theme="default" @click="productionVisible=false">{{lang.cancel}}</t-button>
                    </div>
                </t-form-item>
            </t-form>
        </div>
    </t-dialog>

    <!-- 交付商品 -->
    <t-dialog header="交付商品" :visible.sync="deliveryVisible" :footer="false">
        <div class="dialog-main">
            <t-form :data="deliveryForm" label-align="left" :rules="deliveryRules" @submit="deliverySub">
                <t-form-item name="logistic" label="物流信息">
                    <t-textarea v-model="deliveryForm.logistic" placeholder="请输入物流信息" :autosize="{ minRows: 3, maxRows: 5 }" />
                </t-form-item>
                <t-form-item>
                    <div class="dialog-footer">
                        <t-button theme="primary" type="submit">{{lang.sure}}</t-button>
                        <t-button theme="default" @click="deliveryVisible=false">{{lang.cancel}}</t-button>
                    </div>
                </t-form-item>
            </t-form>
        </div>
    </t-dialog>

</div>
<!-- =======内容区域======= -->
<script src="/{$template_catalog}/template/{$themes}/api/rc.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/rc_order.js"></script>
{include file="footer"}