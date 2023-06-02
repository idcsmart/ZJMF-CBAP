<!-- 页面独有样式 -->
<link rel="stylesheet" href="/plugins/server/idcsmart_dcim/template/clientarea/css/dcimOrder.css">

<div class="template">
    <!-- 自己的东西 -->
    <div class="main-card">
        <div class="order-name">{{name}}</div>
        <div class="order-main">
            <div class="order-left">
                <!-- 区域 -->
                <div class="order-item">
                    <div class="label">区域</div>
                    <div class="area-content">
                        <div class="area-item" :class="item.id==orderData.centerId?'active':null" v-for="item in centerData" :key="item.id" @click="centerChange(item)">
                            <img class="country-img" :src="'/upload/common/country/' + item.iso + '.png'" alt="">
                            <span class="country-text">{{item.countryName}}</span>
                            <span class="city-text">{{item.cityName}}</span>
                            <i class="check el-icon-check"></i>
                        </div>
                    </div>
                </div>
                <!-- 套餐选择 -->
                <div class="order-item">
                    <div class="label">选择套餐</div>
                    <div class="package-content" v-loading="packageLoading">
                        <div class="package-item" :class="item.id==orderData.packageId?'active':null" v-for="item in packageDataPage" :key="item.id" @click="packageItemClick(item)">
                            <div class="money">
                                {{commonData.currency_prefix}}{{item | showFee(pageType)}}
                            </div>
                            <div class="package-line"></div>
                            <span class="description" v-html="item.description" style="white-space: pre-wrap;">
                            </span>
                            <i class="check el-icon-check"></i>
                        </div>
                    </div>
                    <div class="package-page" v-show="packageDataParams.total > 1">
                        <div class="page-item" :class="index == packageDataParams.page? 'page-item-active' : ''" @click="pageChange(index)" v-for="index of packageDataParams.total" :key="index"></div>
                    </div>
                </div>
                <!-- 额外数据盘 -->
                <div class="order-item disk-item" v-if="configData.buy_data_disk == 1">
                    <div class="left">
                        <span class="left-text">额外数据盘</span>
                        <el-switch v-model="isMoreDisk" active-color="#0052D9" @change="diskChange">
                        </el-switch>
                    </div>
                    <div class="right" v-show="isMoreDisk" style="align-items: flex-start;">
                        <div class="right-item" v-for="item in moreDiskData" :key="item.id">
                            <span class="item-name">数据盘{{item.index}}</span>
                            <span class="item-min-size">{{configData.disk_min_size}}</span>
                            <el-slider :step="10" :min="configData.disk_min_size" :max="configData.disk_max_size" v-model="item.size"></el-slider>
                            <span class="item-max-size">{{configData.disk_max_size}}</span>
                            <el-input class="disk-input" v-model.number.lazy="item.size"></el-input>
                            <span class="disk-cycle">G</span>
                            <i class="el-icon-remove-outline del" @click="delMoreDisk(item.id)"></i>
                            <i class="el-icon-circle-plus-outline add" @click="addMoreDisk" v-if="item.index === moreDiskData.length"></i>
                        </div>
                    </div>
                </div>
                <!-- 备份 -->
                <div class="order-item bs-item" v-if="configData.backup_enable == 1">
                    <div class="left">
                        <span class="left-text">备份功能</span>
                        <el-switch v-model="isBack" active-color="#0052D9">
                        </el-switch>
                    </div>
                    <div class="right" v-show="isBack">
                        <span class="text">开启创建{{backNum}}个备份</span>
                        <!-- 只有一个备份选择时直接显示 -->
                        <!-- <span class="num-price">{{backNum + '个备份' + commonData.currency_prefix + backPrice}}</span> -->
                        <!-- 有多个时下拉框选择 -->
                        <el-select class="num-price-select" v-if="configData.backup_option.length>1" v-model="orderData.backId" @change="backSelectChange">
                            <el-option v-for="item in configData.backup_option" :key='item.id' :value="item.id" :label="item.num+'个备份'+ commonData.currency_prefix +item.price + '/月'"></el-option>
                        </el-select>
                    </div>
                </div>
                <!-- 快照 -->
                <div class="order-item bs-item" v-if="configData.snap_enable == 1">
                    <div class="left">
                        <span class="left-text">快照功能</span>
                        <el-switch v-model="isSnapshot" active-color="#0052D9">
                        </el-switch>
                    </div>
                    <div class="right" v-show="isSnapshot">
                        <span class="text">开启创建{{snapNum}}个快照</span>
                        <!-- 只有一个备份选择时直接显示 -->
                        <!-- <span class="num-price">{{snapNum + '个快照' + commonData.currency_prefix + snapPrice}}</span> -->
                        <!-- 有多个时下拉框选择 -->
                        <el-select class="num-price-select" v-if="configData.snap_option.length>1" v-model="orderData.snapId" @change="snapSelectChange">
                            <el-option v-for="item in configData.snap_option" :key='item.id' :value="item.id" :label="item.num+'个快照'+ commonData.currency_prefix +item.price + '/月'"></el-option>
                        </el-select>
                    </div>
                </div>
                <!-- 操作系统 -->
                <div class="order-item os-item">
                    <div class="label">操作系统</div>
                    <div class="os-content">
                        <!-- 镜像组选择框 -->
                        <el-select class="os-select os-group-select" v-model="orderData.osGroupId" @change="osSelectGroupChange">
                            <img v-if="osIcon" class="os-group-img" :src="osIcon" slot="prefix" alt="">
                            <el-option v-for="item in osData" :key='item.id' :value="item.id" :label="item.name">
                                <div class="option-label">
                                    <img class="option-img" :src="'/plugins/server/common_cloud/view/img/' + item.name + '.png'" alt="">
                                    <span class="option-text">{{item.name}}</span>
                                </div>
                            </el-option>
                        </el-select>
                        <!-- 镜像实际选择框 -->
                        <el-select class="os-select" v-model="orderData.osId" @change="osSelectChange">
                            <el-option v-for="item in osSelectData" :key="item.id" :value="item.id" :label="item.name +'-' + commonData.currency_prefix + item.price"></el-option>
                        </el-select>
                    </div>
                </div>
                <!-- 密码 -->
                <div class="order-item pass-item">
                    <div class="item-top" v-if="configData.support_ssh_key == 1">
                        <el-radio v-model="isPassOrKey" label="pass">密码</el-radio>
                        <el-radio v-model="isPassOrKey" label="key">SSH KEY</el-radio>
                    </div>
                    <div class="item-bottom">
                        <div class="pass" v-show="isPassOrKey == 'pass'">
                            <div class="pass-label">密码</div>
                            <el-input class="pass-input" v-model="orderData.password" placeholder="请输入内容">
                                <div class="pass-btn" slot="suffix" @click="autoPass">随机生成</div>
                            </el-input>
                            <div class="text">
                                <!-- <span class="text-1">英文开头;</span> -->
                                <span class="text-2">英文开头;长度5-15个字符；支持英文和数字；必须含有特殊字符</span>
                            </div>
                        </div>
                        <div class="key" v-show="isPassOrKey == 'key'">
                            <div class="key-label">SSH KEY</div>
                            <div class="key-select">
                                <el-select v-model="orderData.key">
                                    <el-option v-for="item in sshKeyData" :key="item.id" :value="item.id" :label="item.name"></el-option>
                                </el-select>
                                <div class="key-btn" @click="toCreateSshKey">上传新KEY</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 付款周期 -->
                <div class="order-item pay-item">
                    <div class="label">付款周期</div>
                    <div class="pay-content">
                        <div class="more-time">
                            <!-- <div class="pay-content-item" v-for="item in showCircleData" :key="item.duration" :class="orderData.duration == item.duration?'active':''" @click="feeItemClick(item)">
                                <div class="item-top">{{item.durationName}}</div>
                                <div class="item-bottom">
                                    {{commonData.currency_prefix}}{{item.money}}
                                </div>
                                <i class="check el-icon-check"></i>
                            </div> -->
                            <div class="pay-content-item" v-for="item in durationPrice" :key="item.duration" :class="orderData.duration == item.duration?'active':''" @click="feeItemClick(item)">
                                <div class="item-top">{{item.name}}</div>
                                <div class="item-bottom">
                                    {{commonData.currency_prefix}}{{item.price.total}}
                                </div>
                                <i class="check el-icon-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 右侧价格详情 -->
            <div class="order-right">
                <div class="right-main">
                    <div class="right-title">
                        配置预览
                    </div>
                    <div class="order-right-item">
                        <div class="row" v-for="item in priceData.preview">
                            <div class="label">{{item.name}}</div>
                            <div class="value">{{item.value}}{{commonData.currency_prefix}}{{item.price}}/{{item.name=='系统'?'一次性': priceData.billing_cycle}}</div>
                        </div>

                    </div>
                    <div class="order-right-item">
                        <div class="row">
                            <div class="label">小计</div>
                            <div class="value" v-loading="priceLoading">
                                {{commonData.currency_prefix}}{{ onePrice | filterMoney}}
                            </div>
                        </div>
                    </div>

                </div>

                <!-- 合计 优惠码 购买按钮 -->
                <div class="order-right-footer">
                    <div class="order-right-item" style="border-bottom:none">
                        <div class="row">
                            <div class="label">数量</div>
                            <div class="value del-add">
                                <span class="del" @click="delQty">-</span>
                                <el-input-number class="num" :controls="false" v-model="orderData.qty" :min="1"></el-input-number>
                                <span class="add" @click="addQty">+</span>
                            </div>
                        </div>
                    </div>
                    <!-- 合计 -->
                    <div class="footer-total">
                        <div class="left">合计</div>
                        <div class="right" v-loading="priceLoading" v-if="commonData.currency_prefix">
                            <span>{{commonData.currency_prefix }} {{ totalPrice | filterMoney}}</span>
                                <el-popover placement="top-start" width="200" trigger="hover" v-if="isShowLevel || (isShowPromo && isUseDiscountCode)">
                                    <div class="show-config-list">
                                        <p v-if="isShowLevel">{{lang.shoppingCar_tip_text2}}：{{commonData.currency_prefix}} {{ clDiscount | filterMoney}}</p>
                                        <p v-if="isShowPromo && isUseDiscountCode">{{lang.shoppingCar_tip_text4}}：{{commonData.currency_prefix}} {{ code_discount | filterMoney}}</p>
                                    </div>
                                     <i class="el-icon-warning-outline total-icon" slot="reference"></i>
                                </el-popover>
                                <p class="original-price" v-if="original_price != totalPrice">{{commonData.currency_prefix}} {{original_price.toFixed(2) | filterMoney}}</p>
                                <!-- 优惠码 -->
                                <div class="discount-box" v-show=" isShowPromo && !customfield.promo_code">
                                    <discount-code @get-discount="getDiscount(arguments)" scene='new' :product_id='id' :qty="orderData.qty" :amount="onePrice" :billing_cycle_time="billing_cycle_time">
                                    <discount-code>
                                </div>
                                <div v-show="customfield.promo_code" class="discount-codeNumber">
                                    {{ customfield.promo_code }}
                                    <i class="el-icon-circle-close remove-discountCode" @click="removeDiscountCode()"></i>
                                </div>
                        </div>
                    </div>
                    <!-- 优惠码 -->
                    <div class="footer-code" v-if="false">
                        <div class="code-main">
                            <el-popover trigger="click" placement="bottom" v-model="codeVisible">
                                <div class="code-input-btn">
                                    <el-input v-model="inputValue"></el-input>
                                    <div class="code-sub-btn" @click="checkCode">确定</div>
                                </div>
                                <div class="left" slot="reference" @click="inputValue=''">使用优惠码<i class="el-icon-circle-plus-outline"></i></div>
                            </el-popover>
                            <div class="right">-{{commonData.currency_prefix + codePrice}}</div>
                        </div>

                        <div class="code-detail">
                            <div class="code-detail-item" v-for="item in discountList" :key="item.name">
                                <span class="code">{{item.name}}</span>
                                <span class="num">-{{commonData.currency_prefix + item.num}}</span>
                                <i class="el-icon-circle-close btn" @click="delCode(item.name)"></i>
                            </div>
                        </div>
                    </div>
                    <!-- 需读 -->
                    <!-- <div class="read">
                        <el-checkbox v-model="isRead">已阅读并同意</el-checkbox>
                        <a class="service" @click="toService">《服务协议》</a>
                        和
                        <a class="privacy" @click="toPrivacy">《隐私协议》</a>
                    </div> -->

                    <!-- 确认修改 -->
                    <div class="bottom-btns" v-if="backConfig.duration">
                        <el-button class="buy-btn" type="primary" style="width:100%;margin-left:0" @click="changeCart" :loading="submitLoading">确认修改</el-button>
                    </div>
                    <!-- 购买按钮 -->
                    <div class="bottom-btns" v-else>
                        <el-button class="car-btn" @click="addCart" v-loading="cartBtnLoading">加入购物车</el-button>
                        <el-button class="buy-btn" type="primary" @click="buyNow">立即购买</el-button>
                    </div>
                </div>
            </div>
        </div>
        <!-- 支付弹窗 -->
        <pay-dialog ref="payDialog" @payok="paySuccess" @paycancel="payCancel"></pay-dialog>
        <!-- 加入购物车成功弹窗 -->
        <el-dialog title="" :visible.sync="cartDialog" custom-class="cartDialog" :show-close="false">
            <span class="tit">您已成功加入购物车！</span>
            <span slot="footer" class="dialog-footer">
                <el-button type="primary" @click="cartDialog = false">继续购物</el-button>
                <el-button @click="goToCart">去购物车结算</el-button>
            </span>
        </el-dialog>
    </div>
</div>
<!-- =======页面独有======= -->
<script src="/plugins/server/idcsmart_dcim/template/clientarea/api/common.js"></script>
<script src="/plugins/server/idcsmart_dcim/template/clientarea/api/dcimOrder.js"></script>
<script src="/plugins/server/idcsmart_dcim/template/clientarea/utils/util.js"></script>
<script src="/plugins/server/idcsmart_dcim/template/clientarea/js/dcimOrder.js"></script>
<script src="/plugins/server/idcsmart_dcim/template/clientarea/components/payDialog/payDialog.js"></script>