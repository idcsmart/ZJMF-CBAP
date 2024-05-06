<?php
namespace app\home\controller;

use think\facade\View;
use app\admin\model\PluginModel;

class ViewCartController extends HomeBaseController
{
    /**
     * 时间 2024-02-22
     * @title 前台会员中心购物车模板统一入口
     * @desc 前台会员中心购物车模板统一入口
     * @url /cart
     * @method  GET
     * @author wyh
     * @version v1
     * @param string theme - 会员中心主题模板
     * @param string view_html - 模板名称
     */
    public function index()
    {
        $param = $this->request->param();
        $data = [
            'title'=>'首页-智简魔方',
        ];

        $data['template_catalog_cart'] = 'cart';
        $tplName = empty($param['view_html'])?'home':$param['view_html'];

        if (isset($param['theme']) && !empty($param['theme'])){
            cookie('clientarea_theme',$param['theme']);
            cookie('cart_theme_mobile',$param['theme_mobile']??"default");
            $data['themes'] = $clientareaPcTheme = $param['theme'];
            $data['themes_mobile'] = $param['theme_mobile']??"default";
        } elseif (cookie('clientarea_theme')){
            $data['themes'] = $clientareaPcTheme = cookie('clientarea_theme');
            $data['themes_mobile'] = cookie('clientarea_theme_mobile');
        } else{
            $data['themes'] = $clientareaPcTheme = configuration('clientarea_theme')??"default";
            $data['themes_mobile'] = configuration('clientarea_theme_mobile');
        }
        $PluginModel = new PluginModel();
        $addons = $PluginModel->plugins('addon');
        $data['addons'] = $addons['list'];

        $mobile = use_mobile();
        $type = $mobile?'mobile':'pc';
        // 会员中心手机主题或者pc主题
        $data['themes'] = $mobile?$data['themes_mobile']:$data['themes'];
        $data['themes'] = $type."/".$data['themes'];

        $clientareaData = $data;

        // 购物车手机主题或者pc主题
        $cartTheme = $mobile?configuration('cart_theme_mobile'):configuration("cart_theme");
        $cartTheme = $type . "/" . $cartTheme;
        $view_path = "../public/cart/template/".$cartTheme.'/';
        // 模板文件不存在，使用默认购物车主题模板文件
        if(!file_exists(IDCSMART_ROOT."public/cart/template/{$cartTheme}/".$tplName.".php")){
            $view_path = "../public/cart/template/{$type}/default/";
            //$data['themes'] = $type."/default";
        }

        $clientareaData['template_catalog'] = 'clientarea';
        $clientareaData['themes_cart'] = $cartTheme; // 购物车主题

        // 引用会员中心header，footer
        $clientareaThemeHeader = "../public/clientarea/template/".$data['themes'].'/header.php';
        $clientareaThemeFooter = "../public/clientarea/template/".$data['themes'].'/footer.php';

        $clientareaData['themes'] = $data['themes'];
        $header = View::fetch($clientareaThemeHeader,$clientareaData);
        $footer = View::fetch($clientareaThemeFooter,$clientareaData);
        $config['view_path'] = $view_path;
        View::config($config);

        return $header.View::fetch("/".$tplName,$data).$footer;
    }

}
