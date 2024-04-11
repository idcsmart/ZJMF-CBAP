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
            $data['themes'] = $param['theme'];
        } elseif (cookie('clientarea_theme')){
            $data['themes'] = cookie('clientarea_theme');
        } else{
            $data['themes'] = configuration('clientarea_theme')??"default";
        }

        $cartTheme = configuration("cart_theme");

        $view_path = '../public/cart/template/'.$cartTheme.'/';

        // 模板文件不存在，使用默认购物车主题模板文件
        if(!file_exists(IDCSMART_ROOT."public/cart/template/{$cartTheme}/".$tplName.".php")){
            $view_path = '../public/cart/template/default/';
        }

        $PluginModel = new PluginModel();
        $addons = $PluginModel->plugins('addon');

        $data['addons'] = $addons['list'];

        $config['view_path'] = $view_path;

        // 引用会员中心header，footer
        $clientareaThemeHeader = '../public/clientarea/template/'.$data['themes'].'/header.php';
        $clientareaThemeFooter = '../public/clientarea/template/'.$data['themes'].'/footer.php';
        $clientareaData = $data;
        $clientareaData['template_catalog'] = 'clientarea';
        $clientareaData['themes'] = $data['themes'];
        $header = View::fetch($clientareaThemeHeader,$clientareaData);
        $footer = View::fetch($clientareaThemeFooter,$clientareaData);

        View::config($config);

        return $header.View::fetch("/".$tplName,$data).$footer;
    }

}
