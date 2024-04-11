<?php
namespace app\admin\controller;

use app\common\model\MenuModel;
use app\admin\validate\MenuValidate;

/**
 * @title 导航管理
 * @desc 导航管理
 * @use app\admin\controller\MenuController
 */
class MenuController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new MenuValidate();
    }

    /**
     * 时间 2022-08-05
     * @title 获取后台导航
     * @desc 获取后台导航
     * @author theworld
     * @version v1
     * @url /admin/v1/menu/admin
     * @method  GET
     * @return array menu - 菜单
     * @return int menu[].id - 菜单ID
     * @return string menu[].type - 菜单类型system系统plugin插件custom自定义
     * @return string menu[].name - 名称
     * @return object menu[].language - 语言
     * @return string menu[].url - 网址
     * @return string menu[].icon - 图标
     * @return int menu[].nav_id - 导航ID
     * @return int menu[].parent_id - 父ID
     * @return array menu[].child - 子菜单
     * @return int menu[].child[].id - 菜单ID
     * @return string menu[].child[].type - 菜单类型system系统plugin插件custom自定义
     * @return string menu[].child[].name - 名称
     * @return object menu[].child[].language - 语言
     * @return string menu[].child[].icon - 图标
     * @return string menu[].child[].url - 网址 
     * @return int menu[].child[].nav_id - 导航ID 
     * @return int menu[].child[].parent_id - 父ID
     * @return array language - 语言
     * @return string language[].display_name - 语言名称
     * @return string language[].display_flag - 国家代码
     * @return string language[].display_img - 图片
     * @return string language[].display_lang - 语言标识
     * @return array system_nav - 系统默认导航
     * @return string system_nav[].id - 导航ID
     * @return string system_nav[].name - 名称
     * @return string system_nav[].url - 网址
     * @return array plugin_nav - 插件默认导航
     * @return string plugin_nav[].title - 插件标题
     * @return array plugin_nav[].nav - 插件导航
     * @return int plugin_nav[].nav[].id - 导航ID
     * @return string plugin_nav[].nav[].name - 名称
     * @return string plugin_nav[].nav[].url - 网址
     */
    public function getAdminMenu()
    {
        // 实例化模型类
        $MenuModel = new MenuModel();
        
        // 获取后台导航
        $data = $MenuModel->getAdminMenu();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
       return json($result);
    }

    /**
     * 时间 2022-08-05
     * @title 获取前台导航
     * @desc 获取前台导航
     * @author theworld
     * @version v1
     * @url /admin/v1/menu/home
     * @method  GET
     * @return array menu - 菜单
     * @return int menu[].id - 菜单ID
     * @return string menu[].type - 菜单类型system系统plugin插件custom自定义module模块
     * @return string menu[].name - 名称
     * @return object menu[].language - 语言
     * @return string menu[].url - 网址
     * @return int menu[].second_reminder - 二次提醒0否1是
     * @return string menu[].icon - 图标
     * @return int menu[].nav_id - 导航ID
     * @return int menu[].parent_id - 父ID
     * @return string menu[].module - 模块类型
     * @return array menu[].product_id - 包含商品
     * @return array menu[].child - 子菜单
     * @return int menu[].child[].id - 菜单ID
     * @return string menu[].child[].type - 菜单类型system系统plugin插件custom自定义module模块
     * @return string menu[].child[].name - 名称
     * @return object menu[].child[].language - 语言
     * @return string menu[].child[].url - 网址
     * @return int menu[].child[].second_reminder - 二次提醒0否1是
     * @return string menu[].child[].icon - 图标
     * @return int menu[].child[].nav_id - 导航ID
     * @return int menu[].child[].parent_id - 父ID
     * @return string menu[].child[].module - 模块类型
     * @return array menu[].child[].product_id - 包含商品
     * @return array language - 语言
     * @return string language[].display_name - 语言名称
     * @return string language[].display_flag - 国家代码
     * @return string language[].display_img - 图片
     * @return string language[].display_lang - 语言标识
     * @return array system_nav - 系统默认导航
     * @return string system_nav[].id - 导航ID
     * @return string system_nav[].name - 名称
     * @return string system_nav[].url - 网址
     * @return array plugin_nav - 插件默认导航
     * @return string plugin_nav[].title - 插件标题
     * @return array plugin_nav[].nav - 插件导航
     * @return int plugin_nav[].nav[].id - 导航ID
     * @return string plugin_nav[].nav[].name - 名称
     * @return string plugin_nav[].nav[].url - 网址
     * @return array module - 模块
     * @return string module[].name - 模块名称
     * @return string module[].display_name - 模块显示名称
     * @return array res_module - 上游模块
     * @return string res_module[].name - 上游模块名称
     * @return string res_module[].display_name - 上游模块显示名称
     */
    public function getHomeMenu()
    {
        // 实例化模型类
        $MenuModel = new MenuModel();
        
        // 获取前台导航
        $data = $MenuModel->getHomeMenu();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
       return json($result);
    }

    /**
     * 时间 2022-08-05
     * @title 保存后台导航
     * @desc 保存后台导航
     * @author theworld
     * @version v1
     * @url /admin/v1/menu/admin
     * @method  PUT
     * @param array menu - 菜单 required
     * @param string menu[].type - 菜单类型system系统plugin插件custom自定义 required
     * @param string menu[].name - 名称 required
     * @param object menu[].language - 语言 required
     * @param string menu[].url - 网址 菜单类型为自定义时需要传递
     * @param string menu[].icon - 图标
     * @param int menu[].nav_id - 导航ID 菜单类型不为自定义时需要传递
     * @param array menu[].child - 子菜单 required
     * @param string menu[].child[].type - 菜单类型system系统plugin插件custom自定义 required
     * @param string menu[].child[].name - 名称 required
     * @param object menu[].child[].language - 语言 required
     * @param string menu[].child[].url - 网址 菜单类型为自定义时需要传递
     * @param string menu[].child[].icon - 图标
     * @param int menu[].child[].nav_id - 导航ID 菜单类型不为自定义时需要传递
     */
    public function saveAdminMenu()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('save')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
        
        // 实例化模型类
        $MenuModel = new MenuModel();
        
        // 保存后台导航
        $result = $MenuModel->saveAdminMenu($param);   
        
        return json($result);
    }

    /**
     * 时间 2022-08-05
     * @title 保存前台导航
     * @desc 保存前台导航
     * @author theworld
     * @version v1
     * @url /admin/v1/menu/home
     * @method  PUT
     * @param array menu - 菜单 required
     * @param string menu[].type - 菜单类型system系统plugin插件custom自定义module模块res_module上游模块 required
     * @param string menu[].name - 名称 required
     * @param object menu[].language - 语言 required
     * @param string menu[].url - 网址 菜单类型为自定义时需要传递
     * @param int menu[].second_reminder - 二次提醒0否1是 菜单类型为自定义时需要传递
     * @param string menu[].icon - 图标
     * @param int menu[].nav_id - 导航ID 菜单类型为系统或插件时需要传递
     * @param string menu[].module - 模块类型 菜单类型为模块或上游模块时需要传递
     * @param array menu[].product_id - 商品ID 菜单类型为模块或上游模块时需要传递
     * @param array menu[].child - 子菜单 required
     * @param string menu[].child[].type - 菜单类型system系统plugin插件custom自定义module模块res_module上游模块 required
     * @param string menu[].child[].name - 名称 required
     * @param object menu[].child[].language - 语言 required
     * @param string menu[].child[].url - 网址 菜单类型为自定义时需要传递
     * @param int menu[].child[].second_reminder - 二次提醒0否1是 菜单类型为自定义时需要传递
     * @param string menu[].child[].icon - 图标
     * @param int menu[].child[].nav_id - 导航ID 菜单类型为系统或插件时需要传递
     * @param string menu[].child[].module - 模块类型 菜单类型为模块或上游模块时需要传递
     * @param array menu[].child[].product_id - 商品ID 菜单类型为模块或上游模块时需要传递
     */
    public function saveHomeMenu()
    {
        // 接收参数
        $param = $this->request->param();
        $param['menu2'] = $param['menu'] ?? [];
        // 参数验证
        if (!$this->validate->scene('save_home')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
        unset($param['menu2']);
        
        // 实例化模型类
        $MenuModel = new MenuModel();
        
        // 保存前台导航
        $result = $MenuModel->saveHomeMenu($param);   
        
        return json($result);
    }
}