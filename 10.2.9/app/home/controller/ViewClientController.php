<?php
namespace app\home\controller;

use think\facade\View;
use app\admin\model\PluginModel;
use think\template\exception\TemplateNotFoundException;

class ViewClientController extends HomeBaseController
{
    public function index()
    {
        $param = $this->request->param();
        $data = [
            'title'=>'首页-智简魔方',
        ];

        $data['template_catalog'] = 'clientarea';
        $tplName = empty($param['view_html'])?'index':$param['view_html'];

        if (isset($param['theme']) && !empty($param['theme'])){
            cookie('clientarea_theme',$param['theme']);
            $data['themes'] = $param['theme'];
        } elseif (cookie('clientarea_theme')){
            $data['themes'] = cookie('clientarea_theme');
        } else{
            $data['themes'] = configuration('clientarea_theme');
        }

        if($tplName=='index'){
            $view_path = '../public/clientarea/template/'.$data['themes'].'/';
            //header('location:/theme/index.html');die;
            //$view_path = '../public/theme/';
        }else{
            $view_path = '../public/clientarea/template/'.$data['themes'].'/';
        }

        if(!file_exists($view_path.$tplName)){
            $theme_config=$this->themeConfig($view_path);
            if(!empty($theme_config['config-parent-theme'])){
                $view_path = '../public/clientarea/template/'.$theme_config['config-parent-theme'].'/';
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
        $tpl = '../public/plugins/addon/'.$name.'/template/clientarea/';

        $data['template_catalog'] = 'clientarea';

        if (isset($param['theme']) && !empty($param['theme'])){
            cookie('clientarea_theme',$param['theme']);
            $data['themes'] = $param['theme'];
        } elseif (cookie('clientarea_theme')){
            $data['themes'] = cookie('clientarea_theme');
        } else{
            $data['themes'] = configuration('clientarea_theme');
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
        View::config(['view_path' => '../public/clientarea/template/default/', 'view_suffix' => 'php']);
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
