<?php
namespace app\admin\controller;

use app\admin\model\PluginModel;

/**
 * @title 插件管理
 * @desc 插件管理
 * @use app\admin\controller\PluginController
 */
class PluginController extends AdminBaseController
{
    /**
     * 时间 2022-5-16
     * @title 获取支付/短信/邮件/插件列表
     * @desc 获取支付/短信/邮件/插件列表:module=gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表,captcha验证码接口列表,oauth=三方登录列表
     * @url /admin/v1/plugin/:module
     * @method  GET
     * @author wyh
     * @version v1
     * @return array list - 插件列表
     * @return int list[].id - ID
     * @return string list[].title - 名称
     * @return string list[].description - 描述
     * @return string list[].name - 标识
     * @return string list[].version - 版本
     * @return string list[].author - 开发者
     * @return string list[].author_url - 开发者链接
     * @return int list[].status - 状态;0:禁用,1:正常,3:未安装
     * @return string list[].help_url - 申请链接
     * @return array list[].sms_type - module=sms,才会有该数据,1国际,0国内
     * @return string list[].menu_id - 导航ID
     * @return string list[].url - 导航链接
     * @return int count - 总数
     */
    public function pluginList()
    {
        $param = $this->request->param();

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->pluginList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 插件安装
     * @desc 插件安装:module=gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表,oauth=三方登录列表
     * @url /admin/v1/plugin/:module/:name
     * @method  POST
     * @author wyh
     * @version v1
     * @param string module - 模块:gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表,oauth=三方登录列表 required
     * @param string name - 标识 required
     */
    public function install()
    {
        $param = $this->request->param();

        $result = (new PluginModel())->install($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 插件卸载
     * @desc 插件卸载:module=gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表,oauth=三方登录列表
     * @url /admin/v1/plugin/:module/:name
     * @method  DELETE
     * @author wyh
     * @version v1
     * @param string module - 模块:gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表,oauth=三方登录列表 required
     * @param string name - 插件标识 required
     */
    public function uninstall()
    {
        $param = $this->request->param();

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 禁用(启用)插件
     * @desc 禁用(启用)插件:module=gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表,oauth=三方登录列表
     * @url /admin/v1/plugin/:module/:name/:status
     * @method  PUT
     * @author wyh
     * @version v1
     * @param string module - 模块:gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表,oauth=三方登录列表 required
     * @param string name - 插件标识 required
     * @param string status - 状态:1启用,0禁用 required
     */
    public function status()
    {
        $param = $this->request->param();

        $result = (new PluginModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取单个插件配置
     * @desc 获取单个插件配置:module=gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表,oauth=三方登录列表
     * @url /admin/v1/plugin/:module/:name
     * @method  GET
     * @author wyh
     * @version v1
     * @param string module - 模块:gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表,oauth=三方登录列表 required
     * @param string id - 插件ID required
     * @return object plugin - 插件
     * @return int plugin.id - 插件ID
     * @return int plugin.status - 插件状态:0禁用,1启用,3未安装
     * @return int plugin.name - 标识
     * @return int plugin.title - 名称
     * @return int plugin.url - 图标地址
     * @return int plugin.author - 作者
     * @return int plugin.author_url - 作者链接
     * @return int plugin.version - 版本
     * @return int plugin.description - 描述
     * @return int plugin.module - 所属模块
     * @return int plugin.order - 排序
     * @return int plugin.help_url - 帮助链接
     * @return int plugin.create_time - 创建时间
     * @return int plugin.update_time - 更新时间
     * @return array plugin.config - 配置
     * @return string plugin.config[].title - 配置名称
     * @return string plugin.config[].type - 配置类型:text文本
     * @return string plugin.config[].value - 默认值
     * @return string plugin.config[].tip - 提示
     * @return string plugin.config[].field - 配置字段名,保存时传的键
     */
    public function setting()
    {
        $param = $this->request->param();

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' => [
                'plugin' => (new PluginModel())->setting($param)
            ]
        ];

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 保存配置
     * @desc 保存配置:module=gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表,oauth=三方登录列表
     * @url /admin/v1/plugin/:module/:name
     * @method  PUT
     * @author wyh
     * @version v1
     * @param string module - 模块:gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表,oauth=三方登录列表 required
     * @param string name - 插件标识 required
     * @param array config.field - 配置:field为返回的配置字段 required
     */
    public function settingPost()
    {
        $param = $this->request->param();

        $result = (new PluginModel())->settingPost($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 插件升级
     * @desc 插件升级:module=gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表
     * @url /admin/v1/plugin/:module/:name/upgrade
     * @method  POST
     * @author wyh
     * @version v1
     * @param string module - 模块:gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表 required
     * @param string name - 插件标识 required
     */
    public function upgrade()
    {
        $param = $this->request->param();

        $result = (new PluginModel())->upgrade($param);

        return json($result);
    }

    /**
     * 时间 2023-06-30
     * @title 插件同步
     * @desc 插件同步
     * @url /admin/v1/plugin/sync
     * @method  GET
     * @author theworld
     * @version v1
     * @param string module - 模块:gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表,certification实名接口列表,server模块列表 required
     * @return array list - 插件列表
     * @return int list[].id - ID
     * @return string list[].name - 名称
     * @return string list[].version - 版本
     * @return string list[].uuid - 标识
     * @return string list[].downloaded - 是否已下载0否1是
     */
    public function sync()
    {
        $param = $this->request->param();

        $result = (new PluginModel())->sync($param);

        return json($result);
    }

    /**
     * 时间 2023-06-30
     * @title 插件下载
     * @desc 插件下载
     * @url /admin/v1/plugin/:id/download
     * @method  GET
     * @author theworld
     * @version v1
     * @param string id - 插件ID required
     */
    public function download()
    {
        $param = $this->request->param();

        $result = (new PluginModel())->download($param);

        return json($result);
    }

    /**
     * 时间 2023-06-30
     * @title 带Hook插件列表
     * @desc 带Hook插件列表
     * @url /admin/v1/plugin/hook
     * @method  GET
     * @author theworld
     * @version v1
     * @return array list - 插件列表
     * @return int list[].id - ID
     * @return string list[].title - 名称
     * @return string list[].name - 标识
     * @return string list[].author - 开发者
     * @return int list[].status - 状态;0:禁用,1:正常
     */
    public function pluginHookList()
    {
        $param = $this->request->param();

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->pluginHookList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2023-06-30
     * @title 带Hook插件排序
     * @desc 带Hook插件排序
     * @url /admin/v1/plugin/hook/order
     * @method  PUT
     * @author theworld
     * @version v1
     * @param array id - 插件ID数组 required
     */
    public function pluginHookOrder()
    {
        $param = $this->request->param();

        $result = (new PluginModel())->pluginHookOrder($param);

        return json($result);
    }
}

