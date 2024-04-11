<?php
namespace app\home\controller;

use think\facade\View;
use app\admin\model\PluginModel;
use think\template\exception\TemplateNotFoundException;

class ViewController extends HomeBaseController
{
    /**
     * 时间 2023-05-04
     * @title 前台首页模板统一入口
     * @desc 前台首页模板统一入口
     * @url /console
     * @method  GET
     * @author wyh
     * @version v1
     * @param string theme - 会员中心主题模板
     * @param string view_html - 模板名称
     */
    public function index()
    {
        $web_switch = configuration('web_switch');
        if($web_switch){
            $param = $this->request->param();
            $data = [
                'title'=>'首页-智简魔方',
            ];

            $data['template_catalog'] = 'web';
            //$tplName = empty($param['view_html'])?'index':$param['view_html'];
            
            if(empty($param['html'])){ 
                $tplName = 'index';
            }else if(!empty($param['html3'])){
                $tplName = $param['html']."/".$param['html2']."/".$param['html3'];  
            }else if(!empty($param['html2'])){
                $tplName = $param['html']."/".$param['html2'];  
            }else{
                $tplName = $param['html'];  
            }

            if (isset($param['theme']) && !empty($param['theme'])){
                cookie('web_theme',$param['theme']);
                $data['themes'] = $param['theme'];
            } elseif (cookie('web_theme')){
                $data['themes'] = cookie('web_theme');
            } else{
                $data['themes'] = configuration('web_theme');
            }

            if($tplName=='index'){
                $view_path = '../public/web/'.$data['themes'].'/';
                //header('location:/theme/index.html');die;
                //$view_path = '../public/theme/';
            }else{
                $view_path = '../public/web/'.$data['themes'].'/';
            }

            if(!file_exists($view_path.$tplName.'.html')){
                $theme_config=$this->themeConfig($view_path);
                if(!empty($theme_config['config-parent-theme'])){
                    $view_path = '../public/web/'.$theme_config['config-parent-theme'].'/';
                }
            }

            $PluginModel = new PluginModel();
            $addons = $PluginModel->plugins('addon');

            $data['addons'] = $addons['list'];

            $config['view_path'] = $view_path;
            /*if($tplName=='index'){
                $config['view_suffix'] = 'html';
            }*/
            $config['view_suffix'] = 'html';

            View::config($config);

            $data['url'] = request()->url(true);  

            //seo
            $data['title'] = lang('web_seo_default_title_'.$tplName);
            if(empty($data['title']) || $data['title']==('web_seo_default_title_'.$tplName)){
                $data['title'] = lang('web_seo_default_title_index').(!empty(configuration('website_name')) ? ('-'.configuration('website_name')) : '');
            }else{
                $data['title'] = $data['title'].(!empty(configuration('website_name')) ? ('-'.configuration('website_name')) : '');
            }
            $data['keywords'] = lang('web_seo_default_keywords_'.$tplName);
            if(empty($data['keywords']) || $data['keywords']==('web_seo_default_keywords_'.$tplName)){
                $data['keywords'] = lang('web_seo_default_keywords_index');
            }
            $data['description'] = lang('web_seo_default_description_'.$tplName);
            if(empty($data['description']) || $data['description']==('web_seo_default_description_'.$tplName)){
                $data['description'] = lang('web_seo_default_description_index');
            }
            $data['pub_date'] = date('Y-m-d\TH:i:s', strtotime('2023-01-01 09:00:00'));
            $data['up_date'] = date('Y-m-d\TH:i:s', strtotime('2023-01-01 09:00:00'));

            $result_hook = hook('web_seo_custom', ['tpl_name' => $tplName, 'url' => $data['url']]);
            $result_hook = array_values(array_filter($result_hook ?? []));
            foreach ($result_hook as $key => $value) {
                if(isset($value['title']) && !empty($value['title'])){
                    $data['title'] = $value['title'];
                }
                if(isset($value['keywords']) && !empty($value['keywords'])){
                    $data['keywords'] = $value['keywords'];
                }
                if(isset($value['description']) && !empty($value['description'])){
                    $data['description'] = $value['description'];
                }
                if(isset($value['pub_date']) && !empty($value['pub_date'])){
                    $data['pub_date'] = date('Y-m-d\TH:i:s', $value['pub_date']);
                }
                if(isset($value['up_date']) && !empty($value['up_date'])){
                    $data['up_date'] = date('Y-m-d\TH:i:s', $value['up_date']);
                }
            }

            return View::fetch("/".$tplName,$data);
        }else{
            $param = $this->request->param();
            $data = [
                'title'=>'首页-智简魔方',
            ];

            $data['template_catalog'] = 'clientarea';
            $tplName = empty($param['view_html'])?'home':$param['view_html'];

            if (isset($param['theme']) && !empty($param['theme'])){
                cookie('clientarea_theme',$param['theme']);
                $data['themes'] = $param['theme'];
            } elseif (cookie('clientarea_theme')){
                $data['themes'] = cookie('clientarea_theme');
            } else{
                $data['themes'] = configuration('clientarea_theme');
            }

            if($tplName=='home'){
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
        
    }

    /*public function plugin()
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
        $tpl = '../public/plugins/addon/'.$name.'/template/web/';

        $data['template_catalog'] = 'web';

        if (isset($param['theme']) && !empty($param['theme'])){
            cookie('web_theme',$param['theme']);
            $data['themes'] = $param['theme'];
        } elseif (cookie('web_theme')){
            $data['themes'] = cookie('web_theme');
        } else{
            $data['themes'] = configuration('web_theme');
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
        View::config(['view_path' => '../public/web/default/', 'view_suffix' => 'html']);
        return View::fetch('/'.$tplName,$data);
    }

    private function pluginView($tplName, $data, $name){
        View::config(['view_path' => '../public/plugins/addon/'.$name.'/template/web/', 'view_suffix' => 'html']);
        return View::fetch('/'.$tplName,$data);
    }*/
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
