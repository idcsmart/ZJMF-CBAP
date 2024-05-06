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
     * @title 获取实名认证接口列表
     * @desc 获取实名认证接口列表
     * @url /admin/v1/plugin/certification
     * @method  GET
     * @author wyh
     * @version v1
     * @return array list - 实名认证接口列表
     * @return int list[].id - ID
     * @return string list[].title - 名称
     * @return string list[].description - 描述
     * @return string list[].name - 标识
     * @return string list[].version - 版本
     * @return string list[].author - 开发者
     * @return string list[].author_url - 开发者链接
     * @return int list[].status - 状态;0:禁用,1:正常,3:未安装
     * @return string list[].help_url - 申请链接
     * @return string list[].menu_id - 导航ID
     * @return string list[].url - 导航链接
     * @return int count - 总数
     */
    public function certificationPluginList()
    {
        $param = $this->request->param();
        $param['module'] = 'certification';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->pluginList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 实名认证接口安装
     * @desc 实名认证接口安装
     * @url /admin/v1/plugin/certification/:name
     * @method  POST
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     */
    public function certificationInstall()
    {
        $param = $this->request->param();
        $param['module'] = 'certification';

        $result = (new PluginModel())->install($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 实名认证接口卸载
     * @desc 实名认证接口卸载
     * @url /admin/v1/plugin/certification/:name
     * @method  DELETE
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     */
    public function certificationUninstall()
    {
        $param = $this->request->param();
        $param['module'] = 'certification';

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 禁用(启用)实名认证接口
     * @desc 禁用(启用)实名认证接口
     * @url /admin/v1/plugin/certification/:name/:status
     * @method  PUT
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @param string status - 状态:1启用,0禁用 required
     */
    public function certificationStatus()
    {
        $param = $this->request->param();
        $param['module'] = 'certification';

        $result = (new PluginModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取单个实名认证接口配置
     * @desc 获取单个实名认证接口配置
     * @url /admin/v1/plugin/certification/:name
     * @method  GET
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @return object plugin - 实名认证接口
     * @return int plugin.id - 实名认证接口ID
     * @return int plugin.status - 状态:0禁用,1启用,3未安装
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
    public function certificationSetting()
    {
        $param = $this->request->param();
        $param['module'] = 'certification';

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
     * @title 保存实名认证接口配置
     * @desc 保存实名认证接口配置
     * @url /admin/v1/plugin/certification/:name
     * @method  PUT
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @param array config.field - 配置:field为返回的配置字段 required
     */
    public function certificationSettingPost()
    {
        $param = $this->request->param();
        $param['module'] = 'certification';

        $result = (new PluginModel())->settingPost($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取验证码接口列表
     * @desc 获取验证码接口列表
     * @url /admin/v1/plugin/captcha
     * @method  GET
     * @author wyh
     * @version v1
     * @return array list - 验证码接口列表
     * @return int list[].id - ID
     * @return string list[].title - 名称
     * @return string list[].description - 描述
     * @return string list[].name - 标识
     * @return string list[].version - 版本
     * @return string list[].author - 开发者
     * @return string list[].author_url - 开发者链接
     * @return int list[].status - 状态;0:禁用,1:正常,3:未安装
     * @return string list[].help_url - 申请链接
     * @return string list[].menu_id - 导航ID
     * @return string list[].url - 导航链接
     * @return int count - 总数
     */
    public function captchaPluginList()
    {
        $param = $this->request->param();
        $param['module'] = 'captcha';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->pluginList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 验证码接口安装
     * @desc 验证码接口安装
     * @url /admin/v1/plugin/captcha/:name
     * @method  POST
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     */
    public function captchaInstall()
    {
        $param = $this->request->param();
        $param['module'] = 'captcha';

        $result = (new PluginModel())->install($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 验证码接口卸载
     * @desc 验证码接口卸载
     * @url /admin/v1/plugin/captcha/:name
     * @method  DELETE
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     */
    public function captchaUninstall()
    {
        $param = $this->request->param();
        $param['module'] = 'captcha';

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 禁用(启用)验证码接口
     * @desc 禁用(启用)验证码接口
     * @url /admin/v1/plugin/captcha/:name/:status
     * @method  PUT
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @param string status - 状态:1启用,0禁用 required
     */
    public function captchaStatus()
    {
        $param = $this->request->param();
        $param['module'] = 'captcha';

        $result = (new PluginModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取单个验证码接口配置
     * @desc 获取单个验证码接口配置
     * @url /admin/v1/plugin/captcha/:name
     * @method  GET
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @return object plugin - 验证码接口
     * @return int plugin.id - 验证码接口ID
     * @return int plugin.status - 状态:0禁用,1启用,3未安装
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
    public function captchaSetting()
    {
        $param = $this->request->param();
        $param['module'] = 'captcha';

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
     * @title 保存验证码接口配置
     * @desc 保存验证码接口配置
     * @url /admin/v1/plugin/captcha/:name
     * @method  PUT
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @param array config.field - 配置:field为返回的配置字段 required
     */
    public function captchaSettingPost()
    {
        $param = $this->request->param();
        $param['module'] = 'captcha';

        $result = (new PluginModel())->settingPost($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取三方登录接口列表
     * @desc 获取三方登录接口列表
     * @url /admin/v1/plugin/oauth
     * @method  GET
     * @author wyh
     * @version v1
     * @return array list - 三方登录接口列表
     * @return int list[].id - ID
     * @return string list[].title - 名称
     * @return string list[].description - 描述
     * @return string list[].name - 标识
     * @return string list[].version - 版本
     * @return string list[].author - 开发者
     * @return string list[].author_url - 开发者链接
     * @return int list[].status - 状态;0:禁用,1:正常,3:未安装
     * @return string list[].help_url - 申请链接
     * @return string list[].menu_id - 导航ID
     * @return string list[].url - 导航链接
     * @return int count - 总数
     */
    public function oauthPluginList()
    {
        $param = $this->request->param();
        $param['module'] = 'oauth';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->pluginList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 三方登录接口安装
     * @desc 三方登录接口安装
     * @url /admin/v1/plugin/oauth/:name
     * @method  POST
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     */
    public function oauthInstall()
    {
        $param = $this->request->param();
        $param['module'] = 'oauth';

        $result = (new PluginModel())->install($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 三方登录接口卸载
     * @desc 三方登录接口卸载
     * @url /admin/v1/plugin/oauth/:name
     * @method  DELETE
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     */
    public function oauthUninstall()
    {
        $param = $this->request->param();
        $param['module'] = 'oauth';

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 禁用(启用)三方登录接口
     * @desc 禁用(启用)三方登录接口
     * @url /admin/v1/plugin/oauth/:name/:status
     * @method  PUT
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @param string status - 状态:1启用,0禁用 required
     */
    public function oauthStatus()
    {
        $param = $this->request->param();
        $param['module'] = 'oauth';

        $result = (new PluginModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取单个三方登录接口配置
     * @desc 获取单个三方登录接口配置
     * @url /admin/v1/plugin/oauth/:name
     * @method  GET
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @return object plugin - 三方登录接口
     * @return int plugin.id - 三方登录接口ID
     * @return int plugin.status - 状态:0禁用,1启用,3未安装
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
    public function oauthSetting()
    {
        $param = $this->request->param();
        $param['module'] = 'oauth';

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
     * @title 保存三方登录接口配置
     * @desc 保存三方登录接口配置
     * @url /admin/v1/plugin/oauth/:name
     * @method  PUT
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @param array config.field - 配置:field为返回的配置字段 required
     */
    public function oauthSettingPost()
    {
        $param = $this->request->param();
        $param['module'] = 'oauth';

        $result = (new PluginModel())->settingPost($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取短信接口列表
     * @desc 获取短信接口列表
     * @url /admin/v1/plugin/sms
     * @method  GET
     * @author wyh
     * @version v1
     * @return array list - 短信接口列表
     * @return int list[].id - ID
     * @return string list[].title - 名称
     * @return string list[].description - 描述
     * @return string list[].name - 标识
     * @return string list[].version - 版本
     * @return string list[].author - 开发者
     * @return string list[].author_url - 开发者链接
     * @return int list[].status - 状态;0:禁用,1:正常,3:未安装
     * @return string list[].help_url - 申请链接
     * @return array list[].sms_type - 1国际,0国内
     * @return string list[].menu_id - 导航ID
     * @return string list[].url - 导航链接
     * @return int count - 总数
     */
    public function smsPluginList()
    {
        $param = $this->request->param();
        $param['module'] = 'sms';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->pluginList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 短信接口安装
     * @desc 短信接口安装
     * @url /admin/v1/plugin/sms/:name
     * @method  POST
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     */
    public function smsInstall()
    {
        $param = $this->request->param();
        $param['module'] = 'sms';

        $result = (new PluginModel())->install($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 短信接口卸载
     * @desc 短信接口卸载
     * @url /admin/v1/plugin/sms/:name
     * @method  DELETE
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     */
    public function smsUninstall()
    {
        $param = $this->request->param();
        $param['module'] = 'sms';

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 禁用(启用)短信接口
     * @desc 禁用(启用)短信接口
     * @url /admin/v1/plugin/sms/:name/:status
     * @method  PUT
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @param string status - 状态:1启用,0禁用 required
     */
    public function smsStatus()
    {
        $param = $this->request->param();
        $param['module'] = 'sms';

        $result = (new PluginModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取单个短信接口配置
     * @desc 获取单个短信接口配置
     * @url /admin/v1/plugin/sms/:name
     * @method  GET
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @return object plugin - 短信接口
     * @return int plugin.id - 短信接口ID
     * @return int plugin.status - 状态:0禁用,1启用,3未安装
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
    public function smsSetting()
    {
        $param = $this->request->param();
        $param['module'] = 'sms';

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
     * @title 保存短信接口配置
     * @desc 保存短信接口配置
     * @url /admin/v1/plugin/sms/:name
     * @method  PUT
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @param array config.field - 配置:field为返回的配置字段 required
     */
    public function smsSettingPost()
    {
        $param = $this->request->param();
        $param['module'] = 'sms';

        $result = (new PluginModel())->settingPost($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取邮件接口列表
     * @desc 获取邮件接口列表
     * @url /admin/v1/plugin/mail
     * @method  GET
     * @author wyh
     * @version v1
     * @return array list - 邮件接口列表
     * @return int list[].id - ID
     * @return string list[].title - 名称
     * @return string list[].description - 描述
     * @return string list[].name - 标识
     * @return string list[].version - 版本
     * @return string list[].author - 开发者
     * @return string list[].author_url - 开发者链接
     * @return int list[].status - 状态;0:禁用,1:正常,3:未安装
     * @return string list[].help_url - 申请链接
     * @return string list[].menu_id - 导航ID
     * @return string list[].url - 导航链接
     * @return int count - 总数
     */
    public function mailPluginList()
    {
        $param = $this->request->param();
        $param['module'] = 'mail';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->pluginList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 邮件接口安装
     * @desc 邮件接口安装
     * @url /admin/v1/plugin/mail/:name
     * @method  POST
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     */
    public function mailInstall()
    {
        $param = $this->request->param();
        $param['module'] = 'mail';

        $result = (new PluginModel())->install($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 邮件接口卸载
     * @desc 邮件接口卸载
     * @url /admin/v1/plugin/mail/:name
     * @method  DELETE
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     */
    public function mailUninstall()
    {
        $param = $this->request->param();
        $param['module'] = 'mail';

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 禁用(启用)邮件接口
     * @desc 禁用(启用)邮件接口
     * @url /admin/v1/plugin/mail/:name/:status
     * @method  PUT
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @param string status - 状态:1启用,0禁用 required
     */
    public function mailStatus()
    {
        $param = $this->request->param();
        $param['module'] = 'mail';

        $result = (new PluginModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取单个邮件接口配置
     * @desc 获取单个邮件接口配置
     * @url /admin/v1/plugin/mail/:name
     * @method  GET
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @return object plugin - 邮件接口
     * @return int plugin.id - 邮件接口ID
     * @return int plugin.status - 状态:0禁用,1启用,3未安装
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
    public function mailSetting()
    {
        $param = $this->request->param();
        $param['module'] = 'mail';

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
     * @title 保存邮件接口配置
     * @desc 保存邮件接口配置
     * @url /admin/v1/plugin/mail/:name
     * @method  PUT
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @param array config.field - 配置:field为返回的配置字段 required
     */
    public function mailSettingPost()
    {
        $param = $this->request->param();
        $param['module'] = 'mail';

        $result = (new PluginModel())->settingPost($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取支付接口列表
     * @desc 获取支付接口列表
     * @url /admin/v1/plugin/gateway
     * @method  GET
     * @author wyh
     * @version v1
     * @return array list - 支付接口列表
     * @return int list[].id - ID
     * @return string list[].title - 名称
     * @return string list[].description - 描述
     * @return string list[].name - 标识
     * @return string list[].version - 版本
     * @return string list[].author - 开发者
     * @return string list[].author_url - 开发者链接
     * @return int list[].status - 状态;0:禁用,1:正常,3:未安装
     * @return string list[].help_url - 申请链接
     * @return string list[].menu_id - 导航ID
     * @return string list[].url - 导航链接
     * @return int count - 总数
     */
    public function gatewayPluginList()
    {
        $param = $this->request->param();
        $param['module'] = 'gateway';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->pluginList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 支付接口安装
     * @desc 支付接口安装
     * @url /admin/v1/plugin/gateway/:name
     * @method  POST
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     */
    public function gatewayInstall()
    {
        $param = $this->request->param();
        $param['module'] = 'gateway';

        $result = (new PluginModel())->install($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 支付接口卸载
     * @desc 支付接口卸载
     * @url /admin/v1/plugin/gateway/:name
     * @method  DELETE
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     */
    public function gatewayUninstall()
    {
        $param = $this->request->param();
        $param['module'] = 'gateway';

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 禁用(启用)支付接口
     * @desc 禁用(启用)支付接口
     * @url /admin/v1/plugin/gateway/:name/:status
     * @method  PUT
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @param string status - 状态:1启用,0禁用 required
     */
    public function gatewayStatus()
    {
        $param = $this->request->param();
        $param['module'] = 'gateway';

        $result = (new PluginModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取单个支付接口配置
     * @desc 获取单个支付接口配置
     * @url /admin/v1/plugin/gateway/:name
     * @method  GET
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @return object plugin - 支付接口
     * @return int plugin.id - 支付接口ID
     * @return int plugin.status - 状态:0禁用,1启用,3未安装
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
    public function gatewaySetting()
    {
        $param = $this->request->param();
        $param['module'] = 'gateway';

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
     * @title 保存支付接口配置
     * @desc 保存支付接口配置
     * @url /admin/v1/plugin/gateway/:name
     * @method  PUT
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @param array config.field - 配置:field为返回的配置字段 required
     */
    public function gatewaySettingPost()
    {
        $param = $this->request->param();
        $param['module'] = 'gateway';

        $result = (new PluginModel())->settingPost($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取插件列表
     * @desc 获取插件列表
     * @url /admin/v1/plugin/addon
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
     * @return string list[].menu_id - 导航ID
     * @return string list[].url - 导航链接
     * @return int count - 总数
     */
    public function addonPluginList()
    {
        $param = $this->request->param();
        $param['module'] = 'addon';

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
     * @desc 插件安装
     * @url /admin/v1/plugin/addon/:name
     * @method  POST
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     */
    public function addonInstall()
    {
        $param = $this->request->param();
        $param['module'] = 'addon';

        $result = (new PluginModel())->install($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 插件卸载
     * @desc 插件卸载
     * @url /admin/v1/plugin/addon/:name
     * @method  DELETE
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     */
    public function addonUninstall()
    {
        $param = $this->request->param();
        $param['module'] = 'addon';

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 禁用(启用)插件
     * @desc 禁用(启用)插件
     * @url /admin/v1/plugin/addon/:name/:status
     * @method  PUT
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @param string status - 状态:1启用,0禁用 required
     */
    public function addonStatus()
    {
        $param = $this->request->param();
        $param['module'] = 'addon';

        $result = (new PluginModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取对象存储接口列表
     * @desc 获取对象存储接口列表
     * @url /admin/v1/plugin/oss
     * @method  GET
     * @author wyh
     * @version v1
     * @return array list - 对象存储接口列表
     * @return int list[].id - ID
     * @return string list[].title - 名称
     * @return string list[].description - 描述
     * @return string list[].name - 标识
     * @return string list[].version - 版本
     * @return string list[].author - 开发者
     * @return string list[].author_url - 开发者链接
     * @return int list[].status - 状态;0:禁用,1:正常,3:未安装
     * @return string list[].help_url - 申请链接
     * @return string list[].menu_id - 导航ID
     * @return string list[].url - 导航链接
     * @return int count - 总数
     */
    public function ossPluginList()
    {
        $param = $this->request->param();
        $param['module'] = 'oss';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->pluginList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 对象存储接口安装
     * @desc 对象存储接口安装
     * @url /admin/v1/plugin/oss/:name
     * @method  POST
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     */
    public function ossInstall()
    {
        $param = $this->request->param();
        $param['module'] = 'oss';

        $result = (new PluginModel())->install($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 对象存储接口卸载
     * @desc 对象存储接口卸载
     * @url /admin/v1/plugin/oss/:name
     * @method  DELETE
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     */
    public function ossUninstall()
    {
        $param = $this->request->param();
        $param['module'] = 'oss';

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 禁用(启用)对象存储接口
     * @desc 禁用(启用)对象存储接口
     * @url /admin/v1/plugin/oss/:name/:status
     * @method  PUT
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @param string status - 状态:1启用,0禁用 required
     */
    public function ossStatus()
    {
        $param = $this->request->param();
        $param['module'] = 'oss';

        $result = (new PluginModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取单个对象存储接口配置
     * @desc 获取单个对象存储接口配置
     * @url /admin/v1/plugin/oss/:name
     * @method  GET
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @return object plugin - 对象存储接口
     * @return int plugin.id - 对象存储接口ID
     * @return int plugin.status - 状态:0禁用,1启用,3未安装
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
    public function ossSetting()
    {
        $param = $this->request->param();
        $param['module'] = 'oss';

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
     * @title 保存对象存储接口配置
     * @desc 保存对象存储接口配置
     * @url /admin/v1/plugin/oss/:name
     * @method  PUT
     * @author wyh
     * @version v1
     * @param string name - 标识 required
     * @param array config.field - 配置:field为返回的配置字段 required
     */
    public function ossSettingPost()
    {
        $param = $this->request->param();
        $param['module'] = 'oss';

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
     * @param string module - 模块:addon插件gateway支付接口,sms短信接口,mail邮件接口,certification实名接口,server模块,oauth第三方登录,sub_server子模块,widget首页挂件 required
     * @return array list - 插件列表
     * @return int list[].id - ID
     * @return string list[].name - 名称
     * @return string list[].type - 应用类型addon插件gateway支付接口,sms短信接口,mail邮件接口,certification实名接口,server模块,oauth第三方登录,sub_server子模块,widget首页挂件
     * @return string list[].version - 版本
     * @return string list[].uuid - 标识
     * @return int list[].create_time - 创建时间
     * @return int list[].downloaded - 是否已下载0否1是
     * @return int list[].upgrade - 是否可升级0否1是
     * @return string list[].error_msg - 错误信息，该信息不为空代表不可下载和升级插件
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

