<?php

namespace app\admin\controller;

use app\admin\model\PluginModel;
use app\common\model\CountryModel;
use app\admin\model\AuthModel;
use app\common\logic\UploadLogic;
use app\common\model\ClientModel;
use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\ServerModel;
use app\common\model\MenuModel;
use app\home\model\ClientareaAuthModel;
use app\common\model\OrderModel;
use app\admin\model\AdminFieldModel;
use app\admin\validate\AdminFieldValidate;
use app\common\logic\TemplateLogic;

/**
 * @title 公共接口
 * @desc 公共接口
 * @use app\admin\controller\CommonController
 */
class CommonController extends AdminBaseController
{
    /**
     * 时间 2022-5-17
     * @title 支付接口
     * @desc 支付接口
     * @url /admin/v1/gateway
     * @method  GET
     * @author wyh
     * @version v1
     * @return array list - 支付接口
     * @return int list[].id - ID
     * @return int list[].title - 名称
     * @return int list[].name - 标识
     * @return int list[].url - 图片:base64格式或者自定义图片路径(支付接口使用此参数)
     * @return int count - 总数
     */
    public function gateway()
    {
        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>gateway_list()
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-18
     * @title 短信接口
     * @desc 短信接口
     * @url /admin/v1/sms
     * @method  GET
     * @author wyh
     * @version v1
     * @return array list - 短信接口
     * @return int list[].id - ID
     * @return int list[].title - 名称
     * @return int list[].name - 标识
     * @return array list[].sms_type - 1国际,0国内
     * @return int count - 总数
     */
    public function sms()
    {
        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->plugins('sms')
        ];
        return json($result);
    }
    /**
     * 时间 2022-5-18
     * @title 邮件接口
     * @desc 邮件接口
     * @url /admin/v1/email
     * @method  GET
     * @author wyh
     * @version v1
     * @return array list - 邮件接口
     * @return int list[].id - ID
     * @return int list[].title - 名称
     * @return int list[].name - 标识
     * @return int count - 总数
     */
    public function email()
    {
        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->plugins('mail')
        ];
        return json($result);
    }

    /**
     * 时间 2022-9-7
     * @title 验证码接口
     * @desc 验证码接口
     * @url /admin/v1/captcha_list
     * @method  GET
     * @author wyh
     * @version v1
     * @return array list - 邮件接口
     * @return int list[].id - ID
     * @return int list[].title - 名称
     * @return int list[].name - 标识
     * @return int count - 总数
     */
    public function captchaList()
    {
        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->plugins('captcha')
        ];
        return json($result);
    }


    /**
     * 时间 2022-5-19
     * @title 公共配置
     * @desc 公共配置
     * @url /admin/v1/common
     * @method  GET
     * @author xiong
     * @version v1
     * @return string currency_code CNY 货币代码
     * @return string currency_prefix ￥ 货币符号
     * @return string currency_suffix 元 后缀
     * @return string website_name 智简魔方 网站名称
     * @return string system_logo 系统LOGO
     * @return array lang_admin - 后台语言列表
     * @return array lang_home - 前台语言列表
     * @return array admin_enforce_safe_method - 后台强制安全选项(operate_password=操作密码)
     */
    public function common()
    {
        $setting = [
            'currency_code',
            'currency_prefix',
            'currency_suffix',
            'website_name',
            'system_logo',
            'admin_enforce_safe_method',
        ];
		
		$lang = [ 
			'lang_admin'=> lang_list('admin') ,
			'lang_home'=> lang_list('home') ,
		];
		
		$data = array_merge($lang,configuration($setting));
        $data['admin_enforce_safe_method'] = !empty($data['admin_enforce_safe_method']) ? explode(',', $data['admin_enforce_safe_method']) : [];

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
			
        ];
        return json($result);
    }
    /**
     * 时间 2022-5-16
     * @title 国家列表
     * @desc 国家列表,包括国家名，中文名，区号
     * @url /admin/v1/country
     * @method  GET
     * @author theworld
     * @version v1
     * @param string keywords - 关键字,搜索范围:国家名,中文名,区号
     * @return array list - 国家列表
     * @return string list[].name - 国家名
     * @return string list[].name_zh - 中文名
     * @return int list[].phone_code - 区号
     * @return string list[].iso - 国家英文缩写
     * @return int count - 国家总数
     */
    public function countryList()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $CountryModel = new CountryModel();

        // 获取国家列表
        $data = $CountryModel->countryList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-27
     * @title 权限列表
     * @desc 权限列表
     * @author theworld
     * @version v1
     * @url /admin/v1/auth
     * @method  GET
     * @return array list - 权限列表
     * @return int list[].id - 权限ID
     * @return string list[].title - 权限标题
     * @return string list[].url - 地址
     * @return int list[].order - 排序
     * @return int list[].parent_id - 父级ID
     * @return string list[].module - 插件模块路径,如:gateway支付接口/sms短信接口/mail邮件接口/addon插件
     * @return string list[].plugin - 插件标识名
     * @return array list[].child - 权限子集
     * @return int list[].child[].id - 权限ID
     * @return string list[].child[].title - 权限标题
     * @return string list[].child[].url - 地址
     * @return int list[].child[].order - 排序
     * @return int list[].child[].parent_id - 父级ID
     * @return string list[].child[].module - 插件模块路径,如:gateway支付接口/sms短信接口/mail邮件接口/addon插件
     * @return string list[].child[].plugin - 插件标识名
     * @return array list[].child[].child - 权限子集
     * @return int list[].child[].child[].id - 权限ID
     * @return string list[].child[].child[].title - 权限标题
     * @return string list[].child[].child[].url - 地址
     * @return int list[].child[].child[].order - 排序
     * @return int list[].child[].child[].parent_id - 父级ID
     * @return string list[].child[].child[].module - 插件模块路径,如:gateway支付接口/sms短信接口/mail邮件接口/addon插件
     * @return string list[].child[].child[].plugin - 插件标识名
     * @return string widget[].id - 挂件标识
     * @return string widget[].title - 挂件标题
     */
    public function authList()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new AuthModel())->authList()
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-27
     * @title 当前管理员权限列表
     * @desc 当前管理员权限列表
     * @author theworld
     * @version v1
     * @url /admin/v1/admin/auth
     * @method  GET
     * @return array list - 权限列表
     * @return int list[].id - 权限ID
     * @return string list[].title - 权限标题
     * @return string list[].url - 地址
     * @return int list[].order - 排序
     * @return int list[].parent_id - 父级ID
     * @return string list[].module - 插件模块路径,如:gateway支付接口/sms短信接口/mail邮件接口/addon插件
     * @return string list[].plugin - 插件标识名
     * @return array list[].child - 权限子集
     * @return int list[].child[].id - 权限ID
     * @return string list[].child[].title - 权限标题
     * @return string list[].child[].url - 地址
     * @return int list[].child[].order - 排序
     * @return int list[].child[].parent_id - 父级ID
     * @return string list[].child[].module - 插件模块路径,如:gateway支付接口/sms短信接口/mail邮件接口/addon插件
     * @return string list[].child[].plugin - 插件标识名
     * @return array list[].child[].child - 权限子集
     * @return int list[].child[].child[].id - 权限ID
     * @return string list[].child[].child[].title - 权限标题
     * @return string list[].child[].child[].url - 地址
     * @return int list[].child[].child[].order - 排序
     * @return int list[].child[].child[].parent_id - 父级ID
     * @return string list[].child[].child[].module - 插件模块路径,如:gateway支付接口/sms短信接口/mail邮件接口/addon插件
     * @return string list[].child[].child[].plugin - 插件标识名
     * @return array auths - 权限
     */
    public function adminAuthList()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new AuthModel())->adminAuthList()
        ];
        return json($result);
    }

    /**
     * 时间 2022-08-10
     * @title 获取后台导航
     * @desc 获取后台导航
     * @author theworld
     * @version v1
     * @url /admin/v1/menu
     * @method  GET
     * @return array menu - 菜单
     * @return int menu[].id - 菜单ID
     * @return string menu[].name - 名称
     * @return string menu[].url - 网址
     * @return string menu[].icon - 图标
     * @return int menu[].parent_id - 父ID
     * @return array menu[].child - 子菜单
     * @return int menu[].child[].id - 菜单ID
     * @return string menu[].child[].name - 名称
     * @return string menu[].child[].url - 网址
     * @return string menu[].child[].icon - 图标
     * @return int menu[].child[].parent_id - 父ID
     * @return int menu_id - 选中菜单ID
     * @return string url - 登录后跳转地址
     */
    public function adminMenu(){
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new MenuModel())->adminMenu()
        ];
        return json($result);
    }

    /**
     * 时间 2022-6-20
     * @title 文件上传
     * @desc 公共配置
     * @url /admin/v1/upload
     * @method POST
     * @author wyh
     * @version v1
     * @param resource file - 文件资源 required
     * @return string save_name - 文件名
     * @return string data.image_base64 - 图片base64,文件为图片才返回
     * @return string data.image_url - 图片地址,文件为图片才返回
     */
    public function upload()
    {
        $filename = $this->request->file('file');

        if (!isset($filename)){
            return json(['status'=>400,'msg'=>lang('param_error')]);
        }
        if (empty($filename->getOriginalExtension())){
            return json(['status'=>400,'msg'=>lang('param_error')]);
        }

        $str=explode($filename->getOriginalExtension(),$filename->getOriginalName())[0];
        if(preg_match("/['!@^&]|\/|\\\|\"/",substr($str,0,strlen($str)-1))){
            return json(['status'=>400,'msg'=>lang('file_name_error')]);
        }

        $UploadLogic = new UploadLogic();

        $result = $UploadLogic->uploadHandle($filename);

        return json($result);
    }

    /**
     * 时间 2022-07-22
     * @title 全局搜索
     * @desc 全局搜索
     * @url /admin/v1/global_search
     * @method GET
     * @author theworld
     * @version v1
     * @param keywords string - 关键字,搜索范围:用户姓名,公司,邮箱,手机号,备注,商品名称,商品一级分组名称,商品二级分组名称,产品ID,标识,商品名称 required
     * @return array clients - 用户
     * @return int clients[].id - 用户ID 
     * @return string clients[].username - 姓名
     * @return string clients[].company - 公司
     * @return string clients[].email - 邮箱
     * @return string clients[].phone_code - 国际电话区号
     * @return string clients[].phone - 手机号
     * @return array products - 商品
     * @return int products[].id - 商品ID 
     * @return string products[].name - 商品名称
     * @return string products[].product_group_name_first - 商品一级分组名称
     * @return string products[].product_group_name_second - 商品二级分组名称
     * @return array hosts - 产品
     * @return int hosts[].id - 产品ID 
     * @return string hosts[].name - 标识
     * @return string hosts[].product_name - 商品名称
     * @return int hosts[].client_id - 用户ID
     */
    public function globalSearch()
    {
        // 接收参数
        $param = $this->request->param();
        $keywords = $param['keywords'] ?? '';
        if(!empty($keywords)){
            $clients = (new ClientModel())->searchClient($param, 'global');
            $products = (new ProductModel())->searchProduct($keywords);
            $hosts = (new HostModel())->searchHost($keywords);
            $data = [
                'clients' => $clients['list'],
                'products' => $products['list'],
                'hosts' => $hosts['list'],
            ];
        }else{
            $data = [
                'clients' => [],
                'products' => [],
                'hosts' => [],
            ];
        }

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取已激活插件
     * @desc 获取已激活插件
     * @url /admin/v1/active_plugin
     * @method  GET
     * @author wyh
     * @version v1
     * @return array list - 插件列表
     * @return int list[].id - ID
     * @return string list[].title - 名称
     * @return string list[].name - 标识
     */
    public function activePluginList()
    {
        $param = $this->request->param();

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->activePluginList()
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-27
     * @title 会员中心权限列表
     * @desc 会员中心权限列表
     * @author theworld
     * @version v1
     * @url /admin/v1/clientarea_auth
     * @method  GET
     * @return array list - 权限列表
     * @return int list[].id - 权限ID
     * @return string list[].title - 权限标题
     * @return string list[].url - 地址
     * @return int list[].order - 排序
     * @return int list[].parent_id - 父级ID
     * @return string list[].module - 插件模块路径,如:gateway支付接口/sms短信接口/mail邮件接口/addon插件
     * @return string list[].plugin - 插件标识名
     * @return array list[].rules - 权限规则标题
     * @return array list[].child - 权限子集
     * @return int list[].child[].id - 权限ID
     * @return string list[].child[].title - 权限标题
     * @return string list[].child[].url - 地址
     * @return int list[].child[].order - 排序
     * @return int list[].child[].parent_id - 父级ID
     * @return string list[].child[].module - 插件模块路径,如:gateway支付接口/sms短信接口/mail邮件接口/addon插件
     * @return string list[].child[].plugin - 插件标识名
     * @return string list[].child[].rules - 权限规则标题
     * @return array list[].child[].child - 权限子集
     * @return int list[].child[].child[].id - 权限ID
     * @return string list[].child[].child[].title - 权限标题
     * @return string list[].child[].child[].url - 地址
     * @return int list[].child[].child[].order - 排序
     * @return int list[].child[].child[].parent_id - 父级ID
     * @return string list[].child[].child[].module - 插件模块路径,如:gateway支付接口/sms短信接口/mail邮件接口/addon插件
     * @return string list[].child[].child[].plugin - 插件标识名
     * @return string list[].child[].child[].rules - 权限规则标题
     */
    public function clientareaAuthList()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new ClientareaAuthModel())->authList()
        ];
        return json($result);
    }

    /**
     * 时间 2024-03-20
     * @title 获取全局单表单字段搜索选项
     * @desc 获取全局单表单字段搜索选项
     * @url /admin/v1/common_search_table
     * @method GET
     * @author theworld
     * @version v1
     * @return array list - 搜索框显示数据
     * @return string list[].table - 表
     * @return string list[].name - 显示名称
     * @return array list[].field - 字段
     * @return string list[].field[].key - 搜索字段
     * @return string list[].field[].name - 搜索字段显示名称
     * @return string list[].field[].type - 搜索框类型input输入框date时间select选择框
     * @return array list[].field[].option - 选择框选项，搜索框类型为选择框时会返回
     * @return array list[].field[].option[].name - 选项显示名称，搜索字段不为product_id时
     * @return array list[].field[].option[].value - 选项值，搜索字段不为product_id时
     * @return string list[].field[].option[].name - 商品一级分组名称，搜索字段为product_id时
     * @return array list[].field[].option[].child - 商品二级分组，搜索字段为product_id时
     * @return string list[].field[].option[].child[].name - 商品二级分组名称，搜索字段为product_id时
     * @return array list[].field[].option[].child[].product - 商品，搜索字段为product_id时
     * @return int list[].field[].option[].child[].product[].id - 商品ID，搜索字段为product_id时
     * @return string list[].field[].option[].child[].product[].name - 商品名称，搜索字段为product_id时
     */
    public function commonSearchTableList()
    {
        $lang = lang();

        $orderStatus = [];
        $status = config('idcsmart.order_status');
        foreach ($status as $key => $value) {
            $orderStatus[] = ['name' => $lang['order_status_'.$value], 'value' => $value];
        }

        $orderType = [];
        $type = config('idcsmart.order_type');
        foreach ($type as $key => $value) {
            $orderType[] = ['name' => $lang['order_type_'.$value], 'value' => $value];
        }

        $hostStatus = [];
        $status = config('idcsmart.host_status');
        foreach ($status as $key => $value) {
            $hostStatus[] = ['name' => $lang['host_status_'.$value], 'value' => $value];
        }

        $product = (new ProductModel())->getProductList();

        $server = (new ServerModel())->getAllServer();
        foreach ($server as $key => $value) {
            $server[$key]['value'] = $value['id'];
            unset($server[$key]['id']);
        }

        $data = [
            [
                'table' => 'client',
                'name' => $lang['common_search_client'],
                'field' => [
                    ['key' => 'id', 'name' => $lang['common_search_client_id'], 'type' => 'input'],
                    ['key' => 'username', 'name' => $lang['common_search_client_username'], 'type' => 'input'],
                    ['key' => 'email', 'name' => $lang['common_search_client_email'], 'type' => 'input'],
                    ['key' => 'phone', 'name' => $lang['common_search_client_phone'], 'type' => 'input'],
                    ['key' => 'company', 'name' => $lang['common_search_client_company'], 'type' => 'input'],
                ],
            ],
            [
                'table' => 'host',
                'name' => $lang['common_search_host'],
                'field' => [
                    ['key' => 'host_id', 'name' => $lang['common_search_host_id'], 'type' => 'input'],
                    ['key' => 'status', 'name' => $lang['common_search_host_status'], 'type' => 'select', 'option' => $hostStatus],
                    ['key' => 'product_id', 'name' => $lang['common_search_host_product_id'], 'type' => 'select', 'option' => $product],
                    ['key' => 'name', 'name' => $lang['common_search_host_name'], 'type' => 'input'],
                    ['key' => 'due_time', 'name' => $lang['common_search_host_due_time'], 'type' => 'date'],
                    ['key' => 'username', 'name' => $lang['common_search_host_username'], 'type' => 'input'],
                    ['key' => 'email', 'name' => $lang['common_search_host_email'], 'type' => 'input'],
                    ['key' => 'phone', 'name' => $lang['common_search_host_phone'], 'type' => 'input'],
                    ['key' => 'billing_cycle', 'name' => $lang['common_search_host_billing_cycle'], 'type' => 'input'],
                    ['key' => 'server_id', 'name' => $lang['common_search_host_server_id'], 'type' => 'select', 'option' => $server],
                ],
            ],
            [
                'table' => 'order',
                'name' => $lang['common_search_order'],
                'field' => [
                    ['key' => 'order_id', 'name' => $lang['common_search_order_id'], 'type' => 'input'],
                    ['key' => 'product_id', 'name' => $lang['common_search_order_product_id'], 'type' => 'select', 'option' => $product],
                    ['key' => 'username', 'name' => $lang['common_search_order_username'], 'type' => 'input'],
                    ['key' => 'email', 'name' => $lang['common_search_order_email'], 'type' => 'input'],
                    ['key' => 'phone', 'name' => $lang['common_search_order_phone'], 'type' => 'input'],
                    ['key' => 'amount', 'name' => $lang['common_search_order_amount'], 'type' => 'input'],
                    ['key' => 'pay_time', 'name' => $lang['common_search_order_pay_time'], 'type' => 'date'],
                    ['key' => 'status', 'name' => $lang['common_search_order_status'], 'type' => 'select', 'option' => $orderStatus],
                    ['key' => 'type', 'name' => $lang['common_search_order_type'], 'type' => 'select', 'option' => $orderType],
                ],
            ],
        ];

        $result = [
            'status' => 200,
            'msg' => $lang['success_message'],
            'data' => [
                'list' => $data
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2024-03-20
     * @title 全局单表单字段搜索
     * @desc 全局单表单字段搜索
     * @url /admin/v1/common_search
     * @method GET
     * @author theworld
     * @version v1
     * @param string table - 表 required
     * @param string key - 搜索字段 required
     * @param string value - 搜索传递的值 required
     * @return string table - 表
     * @return array list - 搜索后返回的列表，用于跳转到对应页面显示
     * @return int count - 总数，当数量为1是跳转到对应内页
     */
    public function commonSearch()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        $table = $param['table'] ?? '';
        $key = $param['key'] ?? '';
        $value = $param['value'] ?? '';

        if($table=='client'){
            $param['type'] = $key;
            $param['keywords'] = $value;
            $data = (new ClientModel())->clientList($param);
        }else if($table=='host'){
            if($key=='due_time'){
                $param['start_time'] = $value;
                $param['end_time'] = $value;
            }else{
                $param[$key] = $value;
            }
            $data = (new HostModel())->hostList($param);
        }else if($table=='order'){
            $param[$key] = $value;
            $data = (new OrderModel())->orderList($param);
        }else{
            $data = [
                'list' => [],
                'count' => 0,
            ];
        }

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-05-15
     * @title 获取字段设置
     * @desc  获取字段设置
     * @url /admin/v1/field
     * @method  GET
     * @author hh
     * @version v1
     * @param   string view - 页面标识(client=用户管理,order=订单管理,host=产品管理,transaction=交易流水) require
     * @return  string field[].name - 字段分组名称
     * @return  string field[].field[].key - 字段标识
     * @return  string field[].field[].name - 字段名称
     * @return  array select_field - 当前选定字段标识
     */
    public function adminFieldIndex()
    {
        $param = $this->request->param();

        $AdminFieldModel = new AdminFieldModel();
        $data = $AdminFieldModel->adminFieldIndex($param);

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2024-05-15
     * @title 保存字段设置
     * @desc  保存字段设置
     * @url /admin/v1/field
     * @method  PUT
     * @author hh
     * @version v1
     * @param   string view - 页面标识(client=用户管理,order=订单管理,host=产品管理,transaction=交易流水) require
     * @param   array  select_field - 选定字段标识 require
     */
    public function adminFieldSave()
    {
        $param = $this->request->param();

        $AdminFieldValidate = new AdminFieldValidate();
        if (!$AdminFieldValidate->scene('save')->check($param)){
            return json(['status' => 400 , 'msg' => lang($AdminFieldValidate->getError())]);
        }

        $AdminFieldModel = new AdminFieldModel();
        $result = $AdminFieldModel->adminFieldSave($param);

        return json($result);
    }

    /**
     * 时间 2024-05-21
     * @title 模板控制器Tab
     * @desc 模板控制器Tab
     * @author theworld
     * @version v1
     * @url /admin/v1/template_tab
     * @method  GET
     * @param string theme - 主题标识,不传递时默认为当前系统设置的主题
     * @return array list - 模板控制器Tab列表
     * @return string list[].name - 标识
     * @return string list[].title - 标题
     * @return string list[].url - 地址
     */
    public function templateTabList()
    {
        $param = $this->request->param();

        $TemplateLogic = new TemplateLogic();
        $result = $TemplateLogic->templateTabList($param);

        return json($result);
    }
}