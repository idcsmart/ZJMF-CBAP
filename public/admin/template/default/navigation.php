{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/navigation.css">
<div id="content" class="navigation " v-cloak>
    <t-card class="list-card-container table">
        <t-tabs v-model="value" @change="menuChange">
            <t-tab-panel value="1" label="前台导航管理" :destroy-on-hide="false">
                <t-button class="new_menu_btn" @click="showNewMenuDialog">新建页面</t-button>
                <div class="nav_main">
                    <div class="nav_left">
                        <t-loading :loading="homeMenuLoading">
                            <draggable animation="300" :move="onMove" v-model="menuList" force-fallback="true" @start="onStart" @end="onEnd" group="level2" chosen-class="chosen" ghost-class="ghost">
                                <transition-group style="min-height: 10px;display:block;">
                                    <div class="item" @click="moveId = 0" v-for="item in menuList" :key="item.id">
                                        <!-- 一级导航 -->
                                        <div v-show="item.id === moveId" class="before" :class="isLv1?'lv1-padding':'lv2-padding'">
                                            <div class="circle"></div>
                                            <div class="line"></div>
                                        </div>
                                        <div class="level_1" :class="activeId === item.id?'active':''" @click="itemClick(item)" @mousedown.stop="getMouseDown($event,item)" @mousemove.stop=" getMouseMove($event)">
                                            <img v-if="item.icon && value==1" class="front-icon" :src="'/{$template_catalog}/template/{$themes}/img/menu/'+item.icon +'.png'" />
                                            <span v-else class="level_icon"></span>
                                            <span class="lv1-text" :title="item.name.length >7?item.name:''">{{item.name}}</span>
                                        </div>
                                        <draggable animation="300" force-fallback="true" v-model="item.child" group="level2" :move="lv2OnMove" @start="onStart" @end="onEnd" chosen-class="chosen" ghost-class="ghost">
                                            <transition-group style="min-height: 10px;display:block;">
                                                <!-- 二级导航 -->
                                                <div class="lv-2-item" @click="moveId = 0" v-for="children in item.child" :key="children.id">
                                                    <div v-show="children.id === moveId" class="before" :class="isLv1?'lv1-padding':'lv2-padding'">
                                                        <div class="circle"></div>
                                                        <div class="line"></div>
                                                    </div>
                                                    <div :title="children.name.length >7?children.name:''" class="level_2 lv2-text" :class="activeId === children.id?'active':''" @click="itemClick(children)" @mousedown.stop="getMouseDown($event,children)" @mousemove.stop=" getMouseMove($event)">
                                                        {{children.name}}
                                                    </div>
                                                </div>

                                            </transition-group>
                                        </draggable>
                                    </div>
                                </transition-group>
                            </draggable>
                        </t-loading>
                    </div>
                    <div class="nav_right">
                        <div class="menu_set" v-show="isShowSet">
                            <t-form :data="formData" :rules="setRules" label-align="top" :label-width="60" @submit="saveSet">
                                <t-form-item name="type" label="页面类型">
                                    <t-select v-model="formData.type" @change="typeChange">
                                        <t-option v-for="item in menuType" :key="item.id" :label="item.label" :value="item.value" />
                                    </t-select>
                                </t-form-item>
                                <t-form-item v-show="formData.type !== 'custom'" name="url" label="目标页面">
                                    <!-- 系统页面 -->
                                    <t-select v-if="formData.type == 'system'" v-model="formData.url" @change="urlSelectChange">
                                        <t-option v-for="item in selectList" :key="item.id" :value="item.url" :label="item.name" />
                                    </t-select>
                                    <!-- 插件 -->
                                    <t-select v-if="formData.type == 'plugin'" v-model="formData.url" @change="urlSelectChange">
                                        <t-option-group v-for="(list,index) in selectList" :key="index" :label="list.title" divider>
                                            <t-option v-for="item in list.navs" :value="item.url" :key="item.id" :label="item.name"></t-option>
                                        </t-option-group>
                                    </t-select>
                                </t-form-item>
                                <t-form-item v-show="formData.type === 'custom'" name="url" label="目标页面">
                                    <t-input v-model="formData.url"></t-input>
                                </t-form-item>
                                <t-form-item name="icon" label="导航图标代码">
                                    <t-input v-model="formData.icon"></t-input>
                                </t-form-item>
                                <t-form-item name="name" label="导航名称">
                                    <t-input v-model="formData.name"></t-input>
                                </t-form-item>
                                <t-checkbox v-model="formData.isChecked" v-show="language.length>1">多语言</t-checkbox>
                                <div v-show="formData.isChecked">
                                    <t-form-item name="language" v-for="item in language" v-show="item.display_lang != commonLang" :key="item.display_flag" :label="item.display_name">
                                        <t-input v-model="formData.language[item.display_lang]"></t-input>
                                    </t-form-item>
                                </div>
                                <t-form-item>
                                    <div class="form-footer">
                                        <t-button theme="primary" type="submit" class="btn-ok">保存</t-button>
                                        <t-button theme="default" @click="delNav" class="btn-no">删除</t-button>
                                    </div>
                                </t-form-item>
                            </t-form>
                        </div>
                        <t-button class="sure_sub" @click="subMenu">应用导航</t-button>
                    </div>
                </div>
            </t-tab-panel>
            <t-tab-panel value="2" label="后台导航管理" :destroy-on-hide="false">
                <t-button class="new_menu_btn" @click="showNewMenuDialog">新建页面</t-button>
                <div class="nav_main">
                    <div class="nav_left">
                        <t-loading :loading="adminMenuLoading">
                            <draggable animation="300" :move="onMove" v-model="menuList" force-fallback="true" @start="onStart" @end="onEnd" group="level2" chosen-class="chosen" ghost-class="ghost">
                                <transition-group style="min-height: 10px;display:block;">
                                    <div @click="moveId = 0" class="item" v-for="item in menuList" :key="item.id">
                                        <!-- 一级导航 -->
                                        <div v-show="item.id === moveId" class="before" :class="isLv1?'lv1-padding':'lv2-padding'">
                                            <div class="circle"></div>
                                            <div class="line"></div>
                                        </div>
                                        <div class="level_1" :class="activeId === item.id?'active':''" @click.stop="itemClick(item)" @mousedown.stop="getMouseDown($event,item)" @mousemove.stop=" getMouseMove($event)">
                                            <t-icon v-if="item.icon" class="back-icon" :name="item.icon"></t-icon>
                                            <span v-else class="level_icon"></span>
                                            <span class="lv1-text" :title="item.name.length >7?item.name:''">{{item.name}}</span>
                                        </div>
                                        <draggable animation="300" force-fallback="true" v-model="item.child" group="level2" :move="lv2OnMove" @start="onStart" @end="onEnd" chosen-class="chosen" ghost-class="ghost">
                                            <transition-group style="min-height: 10px;display:block;">
                                                <!-- 二级导航 -->
                                                <div @click="moveId = 0" class="lv-2-item" v-for="children in item.child" :key="children.id">
                                                    <div v-show="children.id === moveId" class="before" :class="isLv1?'lv1-padding':'lv2-padding'">
                                                        <div class="circle"></div>
                                                        <div class="line"></div>
                                                    </div>
                                                    <div class="level_2 lv2-text" :title="children.name.length >7?children.name:''" :class="activeId === children.id?'active':''" @click="itemClick(children)" @mousedown.stop="getMouseDown($event,children)" @mousemove.stop=" getMouseMove($event)">
                                                        {{children.name}}
                                                    </div>
                                                </div>

                                            </transition-group>
                                        </draggable>
                                    </div>
                                </transition-group> 
                            </draggable>
                        </t-loading>
                    </div>
                    <div class="nav_right">
                        <div class="menu_set" v-show="isShowSet">
                            <t-form :data="formData" :rules="setRules" label-align="top" :label-width="60" @submit="saveSet">
                                <t-form-item name="type" label="页面类型">
                                    <t-select v-model="formData.type" @change="typeChange">
                                        <t-option v-for="item in menuType" :key="item.id" :label="item.label" :value="item.value" />
                                    </t-select>
                                </t-form-item>
                                <t-form-item v-show="formData.type !== 'custom'" name="url" label="目标页面">
                                    <!-- 系统页面 -->
                                    <t-select v-if="formData.type == 'system'" v-model="formData.url" @change="urlSelectChange">
                                        <t-option v-for="item in selectList" :key="item.id" :value="item.url" :label="item.name" />
                                    </t-select>
                                    <!-- 插件 -->
                                    <t-select v-if="formData.type == 'plugin'" v-model="formData.url" @change="urlSelectChange">
                                        <t-option-group v-for="(list,index) in selectList" :key="index" :label="list.title" divider>
                                            <t-option v-for="item in list.navs" :value="item.url" :key="item.id" :label="item.name"></t-option>
                                        </t-option-group>
                                    </t-select>
                                </t-form-item>
                                <t-form-item v-show="formData.type === 'custom'" name="url" label="目标页面">
                                    <t-input v-model="formData.url"></t-input>
                                </t-form-item>
                                <t-form-item name="icon" label="导航图标代码">
                                    <t-input v-model="formData.icon"></t-input>
                                </t-form-item>
                                <t-form-item name="name" label="导航名称">
                                    <t-input v-model="formData.name"></t-input>
                                </t-form-item>
                                <t-checkbox v-model="formData.isChecked" v-show="language.length>1">多语言</t-checkbox>
                                <div v-show="formData.isChecked">
                                    <t-form-item name="language"  v-for="item in language" v-show="item.display_lang != commonLang" :key="item.display_flag" :label="item.display_name">
                                        <t-input v-model="formData.language[item.display_lang]"></t-input>
                                    </t-form-item>
                                </div>
                                <t-form-item>
                                    <div class="form-footer">
                                        <t-button theme="primary" type="submit" class="btn-ok">保存</t-button>
                                        <t-button theme="default" @click="delNav" class="btn-no">删除</t-button>
                                    </div>
                                </t-form-item>
                            </t-form>
                        </div>
                        <t-button class="sure_sub" @click="subMenu">应用导航</t-button>
                    </div>
                </div>
            </t-tab-panel>
        </t-tabs>

    </t-card>

    <!-- 新增页面弹窗 -->
    <t-dialog :visible.sync="visible" header="新增页面" :footer="false" @close="close">
        <t-form :data="newFormData" label-align="right" :rules="newRules" :label-width="120" @submit="confirmNewMenu">
            <t-form-item name="type" label="页面类型">
                <t-select v-model="newFormData.type" @change="newTypeChange">
                    <t-option v-for="item in menuType" :key="item.id" :label="item.label" :value="item.value" />
                </t-select>
            </t-form-item>
            <t-form-item v-show="newFormData.type !== 'custom'" name="url" label="目标页面">
                <!-- <t-select v-model="newFormData.url" @change="newUrlSelectChange">
                    <t-option v-for="item in selectList" :key="item.id" :value="item.url" :label="item.name" />
                </t-select> -->
                <!-- 系统页面 -->
                <t-select v-if="newFormData.type == 'system'" v-model="newFormData.url" @change="urlSelectChange">
                    <t-option v-for="item in selectList" :key="item.id" :value="item.url" :label="item.name" />
                </t-select>
                <!-- 插件 -->
                <t-select v-if="newFormData.type == 'plugin'" v-model="newFormData.url" @change="urlSelectChange">
                    <t-option-group v-for="(list,index) in selectList" :key="index" :label="list.title" divider>
                        <t-option v-for="item in list.navs" :value="item.url" :key="item.id" :label="item.name"></t-option>
                    </t-option-group>
                </t-select>
            </t-form-item>
            <t-form-item v-show="newFormData.type === 'custom'" name="url" label="目标页面">
                <t-input v-model="newFormData.url"></t-input>
            </t-form-item>
            <t-form-item name="icon" label="导航图标代码">
                <t-input v-model="newFormData.icon"></t-input>
            </t-form-item>
            <t-form-item name="name" label="导航名称">
                <t-input v-model="newFormData.name"></t-input>
            </t-form-item>
            <t-checkbox class="new_menu_checkbox" v-model="newFormData.isChecked" v-show="language.length>1">多语言</t-checkbox>
            <div v-show="newFormData.isChecked">
                <t-form-item name="language"  v-for="item in language" v-show="item.display_lang != commonLang" :key="item.display_flag" :label="item.display_name">
                    <t-input v-model="newFormData.language[item.display_lang]"></t-input>
                </t-form-item>
            </div>
            <t-form-item>
                <div class="form-footer">
                    <t-button theme="primary" type="submit" class="btn-ok">保存</t-button>
                    <t-button theme="default" class="btn-no" @click="close">取消</t-button>
                </div>
            </t-form-item>
        </t-form>
    </t-dialog>
</div>
<script src="/{$template_catalog}/template/{$themes}/api/navigation.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/navigation.js"></script>
<!-- vue.draggable -->
<script src="/{$template_catalog}/template/{$themes}/js/common/Sortable.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/common/vuedraggable.umd.min.js"></script>
{include file="footer"}