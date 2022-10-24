<?php
namespace app\home\controller;

use think\facade\Db;
use think\Request;
use think\facade\View;
use app\common\model\TaskWaitModel;
use app\common\logic\SmsLogic;
use app\common\logic\EmailLogic;
use app\admin\model\PluginModel;
class ViewController extends HomeBaseController
{
	public function index()
    {
    	$param = $this->request->param();
		$data = [
			'title'=>'首页-智简魔方',		
		];

		$data['template_catalog'] = 'clientarea';
		$data['themes'] = configuration('clientarea_theme');
		$tplName = empty($param['view_html'])?'index':$param['view_html'];
		$view_path = '../public/clientarea/template/'.$data['themes'].'/';
		if(!file_exists($view_path.$tplName)){
			$theme_config=$this->themeConfig($view_path);
			if(!empty($theme_config['config-parent-theme'])){
				$view_path = '../public/clientarea/template/'.$theme_config['config-parent-theme'].'/';
			}
		}

        $PluginModel = new PluginModel();
        $addons = $PluginModel->plugins('addon');

		$data['addons'] = $addons['list'];
		
		View::config(['view_path' => $view_path]);
		return View::fetch("/".$tplName,$data);
    }	
	
	public function plugin()
    {
    	$param = $this->request->param();
		$data['themes'] = configuration('clientarea_theme');
		$plugin_id = $param['plugin_id'];
		$tplName = empty($param['view_html'])?'index':$param['view_html'];
		$addon = (new PluginModel())->plugins('addon')['list'];
	    $addon = array_column($addon,'name','id');
		$name=parse_name($addon[$plugin_id]??'');
		if(empty($name)){
		    exit('not found template1');
		}
		$tpl = '../public/plugins/addon/'.$name.'/template/clientarea/';

        $data['template_catalog'] = 'clientarea';
        $data['themes'] = configuration('clientarea_theme');

        $PluginModel = new PluginModel();
        $addons = $PluginModel->plugins('addon');

		$data['addons'] = $addons['list'];
		
		if(file_exists($tpl.$tplName.".php")){
			$content=$this->view('header',$data);
			$content.=$this->pluginView($tplName,$data,$name);
			$content.=$this->view('footer',$data);
			return $content;
		}else{
			exit('not found template');
		}
		
    }
	
	private function view($tplName, $data){
        View::config(['view_path' => '../public/clientarea/template/default/']);
		return View::fetch('/'.$tplName,$data);
    }
	
	private function pluginView($tplName, $data, $name){
        View::config(['view_path' => '../public/plugins/addon/'.$name.'/template/clientarea/']);
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
