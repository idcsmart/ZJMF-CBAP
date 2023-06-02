{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/rc_order_config.css">
<div id="content" class="re-config " v-cloak>
    <t-card class="list-card-container table">
        <div class="com-h-box">
            <ul class="common-tab">
                <li>
                    <a href="rc_order.htm">订单列表</a>
                </li>
                <li class="active">
                    <a>基础配置</a>
                </li>
            </ul>
        </div>
        <div class="config">
            <t-form :data="formData" label-align="top" :rules="rules" @submit="submit" reset-type="initial" @reset="onReset">
                <t-form-item label="协议" name="purchase">
                    <div class="length-item">
                        <t-input style="width: 320px;" v-model.number="formData.purchase"></t-input>
                    </div>
                </t-form-item>
                <t-form-item label="房箱识别码长度" name="length">
                    <div class="length-item">
                        <t-input-number v-model.number="formData.length" theme="normal" :min="0" :decimal-places="0"></t-input-number>
                        <span class="text">购买房箱时按照设定长度为房箱设定由大写字母和数据随机组成的识别码。识别码具有唯一性</span>
                    </div>
                </t-form-item>
                <t-form-item label="第一笔付款金额设置" name="downpayment">
                    <div class="amount-item">
                        <t-input-number v-model="formData.downpayment" theme="normal" :min="0" :decimal-places="2"></t-input-number>
                        <span>{{currency_suffix}}</span>
                    </div>
                </t-form-item>
                <t-form-item label="小型房箱预期交付周期" name="small">
                    <div class="cycle-table">
                        <t-table row-key="id" :data="smallData" size="medium" :columns="columns">
                            <template #order="{row}">
                                <div v-show="!row.isEdit">{{row.order_min}}-{{row.order_max}}单</div>
                                <div v-show="row.isEdit">
                                    <t-input-number v-model="row.order_min_edit" theme="normal" :min="0" :decimal-places="0"></t-input-number>
                                    <span>-</span>
                                    <t-input-number v-model="row.order_max_edit" theme="normal" :min="0" :decimal-places="0"></t-input-number>
                                    <span>单</span>
                                </div>
                            </template>
                            <template #cycle="{row}">
                                <div v-show="!row.isEdit">{{row.cycle_min}}-{{row.cycle_max}}周</div>
                                <div v-show="row.isEdit">
                                    <t-input-number v-model="row.cycle_min_edit" theme="normal" :min="0" :decimal-places="0"></t-input-number>
                                    <span>-</span>
                                    <t-input-number v-model="row.cycle_max_edit" theme="normal" :min="0" :decimal-places="0"></t-input-number>
                                    <span>周</span>
                                </div>
                            </template>
                            <template #operation="{row}">
                                <div class="operation">
                                    <t-icon class="operation-icon" v-show="!row.isEdit" name="edit-1" @click="edit('small',row)"></t-icon>
                                    <t-icon class="operation-icon" v-show="!row.isEdit" name="delete" @click="del('small',row)"></t-icon>
                                    <t-icon class="operation-icon" v-show="row.isEdit" name="save" @click="save('small',row)"></t-icon>
                                    <t-icon class="operation-icon" v-show="row.isEdit" name="close-circle-filled" @click="cancel('small',row)"></t-icon>
                                </div>
                            </template>
                        </t-table>
                        <div class="add-row">
                            <t-button theme="default" variant="text" @click="addCycle('small')">
                                <t-icon slot="icon" name="add"></t-icon>
                                新增周期
                            </t-button>
                        </div>
                    </div>
                </t-form-item>
                <t-form-item label="中型房箱预期交付周期" name="medium">
                    <div class="cycle-table">
                        <t-table row-key="id" :data="mediumData" size="medium" :columns="columns">
                            <template #order="{row}">
                                <div v-show="!row.isEdit">{{row.order_min}}-{{row.order_max}}单</div>
                                <div v-show="row.isEdit">
                                    <t-input-number v-model="row.order_min_edit" theme="normal" :min="0" :decimal-places="0"></t-input-number>
                                    <span>-</span>
                                    <t-input-number v-model="row.order_max_edit" theme="normal" :min="0" :decimal-places="0"></t-input-number>
                                    <span>单</span>
                                </div>
                            </template>
                            <template #cycle="{row}">
                                <div v-show="!row.isEdit">{{row.cycle_min}}-{{row.cycle_max}}周</div>
                                <div v-show="row.isEdit">
                                    <t-input-number v-model="row.cycle_min_edit" theme="normal" :min="0" :decimal-places="0"></t-input-number>
                                    <span>-</span>
                                    <t-input-number v-model="row.cycle_max_edit" theme="normal" :min="0" :decimal-places="0"></t-input-number>
                                    <span>周</span>
                                </div>
                            </template>
                            <template #operation="{row}">
                                <div class="operation">
                                    <t-icon class="operation-icon" v-show="!row.isEdit" name="edit-1" @click="edit('medium',row)"></t-icon>
                                    <t-icon class="operation-icon" v-show="!row.isEdit" name="delete" @click="del('medium',row)"></t-icon>
                                    <t-icon class="operation-icon" v-show="row.isEdit" name="save" @click="save('medium',row)"></t-icon>
                                    <t-icon class="operation-icon" v-show="row.isEdit" name="close-circle-filled" @click="cancel('medium',row)"></t-icon>
                                </div>
                            </template>
                        </t-table>
                        <div class="add-row">
                            <t-button theme="default" variant="text" @click="addCycle('medium')">
                                <t-icon slot="icon" name="add"></t-icon>
                                新增周期
                            </t-button>
                        </div>
                    </div>
                </t-form-item>
                <t-form-item label="大型房箱预期交付周期" name="big">
                    <div class="cycle-table">
                        <t-table row-key="id" :data="bigData" size="medium" :columns="columns">
                            <template #order="{row}">
                                <div v-show="!row.isEdit">{{row.order_min}}-{{row.order_max}}单</div>
                                <div v-show="row.isEdit">
                                    <t-input-number v-model="row.order_min_edit" theme="normal" :min="0" :decimal-places="0"></t-input-number>
                                    <span>-</span>
                                    <t-input-number v-model="row.order_max_edit" theme="normal" :min="0" :decimal-places="0"></t-input-number>
                                    <span>单</span>
                                </div>
                            </template>
                            <template #cycle="{row}">
                                <div v-show="!row.isEdit">{{row.cycle_min}}-{{row.cycle_max}}周</div>
                                <div v-show="row.isEdit">
                                    <t-input-number v-model="row.cycle_min_edit" theme="normal" :min="0" :decimal-places="0"></t-input-number>
                                    <span>-</span>
                                    <t-input-number v-model="row.cycle_max_edit" theme="normal" :min="0" :decimal-places="0"></t-input-number>
                                    <span>周</span>
                                </div>
                            </template>
                            <template #operation="{row}">
                                <div class="operation">
                                    <t-icon class="operation-icon" v-show="!row.isEdit" name="edit-1" @click="edit('big',row)"></t-icon>
                                    <t-icon class="operation-icon" v-show="!row.isEdit" name="delete" @click="del('big',row)"></t-icon>
                                    <t-icon class="operation-icon" v-show="row.isEdit" name="save" @click="save('big',row)"></t-icon>
                                    <t-icon class="operation-icon" v-show="row.isEdit" name="close-circle-filled" @click="cancel('big',row)"></t-icon>
                                </div>
                            </template>
                        </t-table>
                        <div class="add-row">
                            <t-button theme="default" variant="text" @click="addCycle('big')">
                                <t-icon slot="icon" name="add"></t-icon>
                                新增周期
                            </t-button>
                        </div>
                    </div>
                </t-form-item>
                <div class="item-row">
                    <t-form-item label="促销时间" name="promotion_time_min">
                        <div class="time-item">
                            <t-date-picker v-model="promotion_time_min" :first-day-of-week="1" @change='minHandleChange'></t-date-picker>
                            <span>-</span>
                            <t-date-picker v-model="promotion_time_max" :first-day-of-week="1" @change='maxHandleChange'></t-date-picker>
                        </div>
                    </t-form-item>
                    <div style="width: 40px;"></div>
                    <t-form-item label="促销金额">
                        <div class="amount-item">
                            <t-input-number v-model="formData.promotion_amount" theme="normal" :min="0" :decimal-places="2"></t-input-number>
                            <span>{{currency_suffix}}</span>
                        </div>
                    </t-form-item>
                </div>
                <t-form-item label="促销方案">
                    <t-textarea v-model="formData.promotion_copywritint" placeholder="请输入促销方案" :autosize="{ minRows: 3, maxRows: 5 }"></t-textarea>
                </t-form-item>
                <t-form-item>
                    <div>
                        <t-button type="submit">保存</t-button>
                        <!-- <t-button theme="default" type="reset">取消</t-button> -->
                    </div>
                </t-form-item>
            </t-form>
        </div>
    </t-card>
</div>
<!-- =======内容区域======= -->
<script src="/{$template_catalog}/template/{$themes}/api/rc.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/rc_order_config.js"></script>
{include file="footer"}