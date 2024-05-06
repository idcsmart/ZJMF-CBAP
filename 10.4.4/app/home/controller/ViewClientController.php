<?php
namespace app\home\controller;

use think\facade\View;
use app\admin\model\PluginModel;
use think\template\exception\TemplateNotFoundException;

class ViewClientController extends HomeBaseController
{
    /**
     * 时间 2023-05-04
     * @title 前台会员中心首页模板统一入口
     * @desc 前台会员中心首页模板统一入口
     * @url /console
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

        $data['template_catalog'] = 'clientarea';
        $tplName = empty($param['view_html'])?'home':$param['view_html'];

        if (isset($param['theme']) && !empty($param['theme'])){
            cookie('clientarea_theme',$param['theme']);
            cookie('clientarea_theme_mobile',$param['theme_mobile']??"default");
            $data['themes'] = $param['theme'];
            $data['themes_mobile'] = $param['theme_mobile']??"default";
        } elseif (cookie('clientarea_theme')){
            $data['themes'] = cookie('clientarea_theme');
            $data['themes_mobile'] = cookie('clientarea_theme_mobile');
        } else{
            $data['themes'] = configuration('clientarea_theme');
            $data['themes_mobile'] = configuration('clientarea_theme_mobile');
        }

        $mobile = use_mobile();
        $cartTheme = configuration("cart_theme");
        if ($mobile){
            $view_path = '../public/clientarea/template/mobile/'.$data['themes_mobile'].'/';
            $data['themes'] = 'mobile/' . $data['themes_mobile'];
            if(!file_exists(IDCSMART_ROOT."public/clientarea/template/{$data['themes']}/{$tplName}.php")){
                $view_path = '../public/clientarea/template/mobile/default/';
            }
            $data['themes_cart'] = 'mobile/'.$cartTheme; // 购物车主题
        }else{
            $view_path = '../public/clientarea/template/pc/'.$data['themes'].'/';
            $data['themes'] = 'pc/' . $data['themes'];
            if(!file_exists(IDCSMART_ROOT."public/clientarea/template/{$data['themes']}/{$tplName}.php")){
                $view_path = '../public/clientarea/template/pc/default/';
            }
            $data['themes_cart'] = 'pc/'.$cartTheme; // 购物车主题
        }

        $PluginModel = new PluginModel();
        $addons = $PluginModel->plugins('addon');
        $data['addons'] = $addons['list'];

        $data['iframe_url'] = $param['iframe_url']??"";

        $config['view_path'] = $view_path;

        View::config($config);

        return View::fetch("/".$tplName,$data);
    }

    /**
     * 时间 2023-05-04
     * @title 前台会员中心插件模板统一入口
     * @desc 前台会员中心插件模板统一入口
     * @url /console/plugin/:plugin_id/:view_html
     * @method  GET
     * @author wyh
     * @version v1
     * @param int plugin_id - 插件ID
     * @param string theme - 会员中心主题模板
     * @param string view_html - 模板名称
     */
    public function plugin()
    {
        $param = $this->request->param();
        $plugin_id = $param['plugin_id'];
        $tplName = empty($param['view_html'])?'index':$param['view_html'];
        $addon = (new PluginModel())->plugins('addon')['list'];
        $addon = array_column($addon,'name','id');
        $name=parse_name($addon[$plugin_id]??'');
        if(empty($name)){
            throw new TemplateNotFoundException(lang('not_found'), $name);
            #exit('not found template1');
        }
        $mobile = use_mobile();
        $data['template_catalog'] = 'clientarea';

        if (isset($param['theme']) && !empty($param['theme'])){
            cookie('clientarea_theme',$param['theme']);
            cookie('clientarea_theme_mobile',$param['theme_mobile']??"default");
            $data['themes'] = $param['theme'];
            $data['themes_mobile'] = $param['theme_mobile']??"default";
        } elseif (cookie('clientarea_theme')){
            $data['themes'] = cookie('clientarea_theme');
            $data['themes_mobile'] = cookie('clientarea_theme_mobile');
        } else{
            $data['themes'] = configuration('clientarea_theme');
            $data['themes_mobile'] = configuration('clientarea_theme_mobile');
        }

        // TODO wyh 与国际版有区别
        if ($mobile){
            $tpl = '../public/plugins/addon/'.$name.'/template/clientarea/'; // mobile/ 插件先不处理手机端
            $data['themes'] = 'mobile/' . $data['themes_mobile'];
        }else{
            $tpl = '../public/plugins/addon/'.$name.'/template/clientarea/'; // pc/
            $data['themes'] = 'pc/' . $data['themes'];
        }

        $PluginModel = new PluginModel();
        $addons = $PluginModel->plugins('addon');

        $data['addons'] = $addons['list'];

        if(file_exists($tpl.$tplName.".html")){
            $content=$this->view('header',$data);
            $content.=$this->pluginView($tplName,$data,$name);
            $content.=$this->view('footer',$data);
            return $content;
        }else{
            throw new TemplateNotFoundException(lang('not_found'), $tpl);
            #exit('not found template');
        }

    }

    private function view($tplName, $data){
        View::config(['view_path' => '../public/clientarea/template/'.$data['themes'].'/', 'view_suffix' => 'php']);
        return View::fetch('/'.$tplName,$data);
    }

    private function pluginView($tplName, $data, $name){
        View::config(['view_path' => '../public/plugins/addon/'.$name.'/template/clientarea/', 'view_suffix' => 'html']);
        return View::fetch('/'.$tplName,$data);
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
