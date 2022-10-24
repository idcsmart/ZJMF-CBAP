<?php

namespace app\admin\controller;

use app\admin\model\PluginModel;
use app\common\model\CountryModel;
use app\admin\model\AuthModel;
use app\common\logic\UploadLogic;
use app\common\model\ClientModel;
use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\MenuModel;

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
     * @return int list[].url - 图片:base64格式
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
     */
    public function common()
    {
        $setting = [
            'currency_code',
            'currency_prefix',
            'currency_suffix',
            'website_name',
            'system_logo',
        ];
		
		$lang = [ 
			'lang_admin'=> lang_list('admin') ,
			'lang_home'=> lang_list('home') ,
		];
		
		$data = array_merge($lang,configuration($setting));

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
     * @return array list[].rules - 权限规则标题
     * @return array list[].child - 权限子集
     * @return int list[].child[].id - 权限ID
     * @return string list[].child[].title - 权限标题
     * @return string list[].child[].url - 地址
     * @return int list[].child[].order - 排序
     * @return int list[].child[].parent_id - 父级ID
     * @return string list[].child[].rules - 权限规则标题
     * @return array list[].child[].child - 权限子集
     * @return int list[].child[].child[].id - 权限ID
     * @return string list[].child[].child[].title - 权限标题
     * @return string list[].child[].child[].url - 地址
     * @return int list[].child[].child[].order - 排序
     * @return int list[].child[].child[].parent_id - 父级ID
     * @return string list[].child[].child[].rules - 权限规则标题
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
     * @return array list[].child - 权限子集
     * @return int list[].child[].id - 权限ID
     * @return string list[].child[].title - 权限标题
     * @return string list[].child[].url - 地址
     * @return int list[].child[].order - 排序
     * @return int list[].child[].parent_id - 父级ID
     * @return array list[].child[].child - 权限子集
     * @return int list[].child[].child[].id - 权限ID
     * @return string list[].child[].child[].title - 权限标题
     * @return string list[].child[].child[].url - 地址
     * @return int list[].child[].child[].order - 排序
     * @return int list[].child[].child[].parent_id - 父级ID
     * @return array rules - 权限规则
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
     */
    public function upload()
    {
        $filename = $this->request->file('file');

        if (!isset($filename)){
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
     * @param keywords string - 关键字,搜索范围:用户姓名,公司,邮箱,手机号,商品名称,商品一级分组名称,商品二级分组名称,产品ID,标识,商品名称 required
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
            $clients = (new ClientModel())->searchClient($keywords, 'global');
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
}