{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/navigation.css">
<div id="content" class="navigation table" v-cloak>
    <div class="navigation_all">
        <div class="navigation_top">
            <t-radio-group variant="primary-filled" default-value="1" @change="menuChange" class="nav_radio_group">
                <t-radio-button class="nav_radio_btn" value="1">后台导航</t-radio-button>
                <t-radio-button class="nav_radio_btn" value="2">前台导航</t-radio-button>
            </t-radio-group>
        </div>
        <t-button class="new_menu_btn">新增页面</t-button>
        <div class="navigation_main">
            <div class="main_left">
                <div class="main_left_b">
                    <div class="system_menu">
                        <t-table :columns="columns" :data="menuList.system_nav" :loading="loading" dragSort="row-handler" @drag-sort="onDragSort"></t-table>
                    </div>
                    <div class="plugin_menu"></div>
                    <div class="custom_menu"></div>
                </div>
            </div>
            <div class="main_right">
                这是菜单详情
            </div>
        </div>
    </div>

</div>
<script src="/{$template_catalog}/template/{$themes}/api/navigation.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/navigation.js"></script>
{include file="footer"}