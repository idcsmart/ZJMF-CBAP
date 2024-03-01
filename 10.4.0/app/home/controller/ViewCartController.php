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
    public function cart()
    {
        $param = $this->request->param();
        $data = [
            'title'=>'首页-智简魔方',
        ];

        $data['template_catalog'] = 'cart';
        $tplName = empty($param['view_html'])?'home':$param['view_html'];

        if (isset($param['theme']) && !empty($param['theme'])) {
            cookie('cart_theme',$param['theme']);
            $data['themes'] = $param['theme'];
        } elseif (cookie('cart_theme')) {
            $data['themes'] = cookie('cart_theme');
        } else {
            $data['themes'] = configuration('cart_theme');
        }

        $view_path = '../public/cart/template/'.$data['themes'].'/';

        if(!file_exists($view_path.$tplName)){
            $theme_config=$this->themeConfig($view_path);
            if(!empty($theme_config['config-parent-theme'])){
                $view_path = '../public/cart/template/'.$theme_config['config-parent-theme'].'/';
            }
        }

        $PluginModel = new PluginModel();
        $addons = $PluginModel->plugins('addon');

        $data['addons'] = $addons['list'];

        $config['view_path'] = $view_path;
        /*if($tplName=='index'){
            $config['view_suffix'] = 'html';
        }*/

        View::config($config);

        return View::fetch("/".$tplName,$data);
    }

    //模板继承文件读取
    private function themeConfig($file){
        $theme=$file.'/theme.config';$themes=[];
        if(file_exists($theme)){
            $theme=file_get_contents($theme);

            $theme=explode("\r\n",$theme);
            $theme=array_filter($theme);

            foreach($theme as $v){
                $theme_config=explode(":",$v);
                $themes[trim($theme_config[0])]=trim(trim(trim($theme_config[1],"'"),'"'));
            }
        }
        return $themes;
    }
}
