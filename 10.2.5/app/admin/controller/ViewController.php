<?php
namespace app\admin\controller;
use think\Request;
use think\facade\View;
use think\template\exception\TemplateNotFoundException;

class ViewController extends AdminBaseController
{
	public function index()
    {
    	$param = $this->request->param();
		$data = [
			'title'=>'首页-智简魔方',		
		];
		$data['template_catalog'] = DIR_ADMIN;
		$data['themes'] = configuration('admin_theme');
		$tplName = $param['view_html'];
		View::config(['view_path' => '../public/'.DIR_ADMIN.'/template/default/']);
		return View::fetch('/'.$tplName,$data);
    }
	
	public function plugin()
    {
    	$param = $this->request->param();
		$data['template_catalog'] = DIR_ADMIN;
		$data['themes'] = configuration('admin_theme');
		$name = $param['name'];
		$tplName = $param['view_html'];
		
		$tpl = '../public/plugins/addon/'.$name.'/template/admin/';
		
		if(file_exists($tpl.$tplName.".php")){
			$content=$this->view('header',$data);
			$content.=$this->pluginView($tplName,$data,$name);
			$content.=$this->view('footer',$data);
			return $content;
			//View::config(['view_path' => $tpl]);		
			//return View::fetch('/'.$tplName,$data);
		}else{
            throw new TemplateNotFoundException(lang('not_found'), $tpl);
		}
		
    }
	
	public function view($tplName, $data){
        View::config(['view_path' => '../public/'.DIR_ADMIN.'/template/default/']);
		return View::fetch('/'.$tplName,$data);
    }
	
	public function pluginView($tplName, $data, $name){
        View::config(['view_path' => '../public/plugins/addon/'.$name.'/template/admin/']);
		return View::fetch('/'.$tplName,$data);
    }
}
