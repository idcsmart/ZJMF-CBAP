<?php
namespace app\admin\controller;
use think\Request;
use think\facade\View;
use think\template\exception\TemplateNotFoundException;

/**
 * @title 模板视图控制器
 * @desc 模板视图控制器
 * @use app\admin\controller\ViewController
 */
class ViewController extends AdminBaseController
{
    /**
     * 时间 2023-05-04
     * @title 后台首页模板统一入口
     * @desc 后台首页模板统一入口
     * @url /admin
     * @method  GET
     * @author wyh
     * @version v1
     */
	public function index()
    {
    	$param = $this->request->param();
		$data = [
			'title'=>'首页-智简魔方',		
		];
		$data['template_catalog'] = DIR_ADMIN;
		$data['themes'] = configuration('admin_theme');
		$data['themes'] = !empty($data['themes']) ? $data['themes'] : 'default';
		$tplName = $param['view_html'];
		// 当不在安全中心时
		if(!in_array($tplName, ['login','security_center'])){
			$check = check_admin_enforce_safe_method_redirect();
			if($check['redirect']){
				header('Location: '.$check['url']);die;
			}
		}
		View::config(['view_path' => '../public/'.DIR_ADMIN.'/template/'.$data['themes'].'/']);
		return View::fetch('/'.$tplName,$data);
    }

    /**
     * 时间 2023-05-04
     * @title 插件页面模板统一入口
     * @desc 插件页面模板统一入口
     * @url /admin/plugin/:name/:view_html
     * @method  GET
     * @author wyh
     * @version v1
     * @param string name - 插件名称，小写下划线格式
     * @param string view_html - 模板名
     */
	public function plugin()
    {
    	$check = check_admin_enforce_safe_method_redirect();
		if($check['redirect']){
			header('Location: '.$check['url']);die;
		}
    	
    	$param = $this->request->param();
		$data['template_catalog'] = DIR_ADMIN;
		$data['themes'] = configuration('admin_theme');
		$data['themes'] = !empty($data['themes']) ? $data['themes'] : 'default';
		$name = $param['name'];
		$tplName = $param['view_html'];
		
		$tpl = '../public/plugins/addon/'.$name.'/template/admin/';
		
		if(file_exists($tpl.$tplName.".html")){
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

    /**
     * 时间 2024-05-21
     * @title 模板控制器页面模板统一入口
     * @desc 模板控制器页面模板统一入口
     * @url /admin/template/:name/:view_html
     * @method  GET
     * @author theworld
     * @version v1
     * @param string name - 插件名称，小写下划线格式
     * @param string view_html - 模板名
     */
	public function template()
    {
    	$param = $this->request->param();
		$data['template_catalog'] = DIR_ADMIN;
		$data['themes'] = configuration('admin_theme');
		$data['themes'] = !empty($data['themes']) ? $data['themes'] : 'default';
		$name = $param['name'];
		$tplName = $param['view_html'];
		
		$tpl = '../public/web/'.$name.'/controller/template/admin/';
		
		if(file_exists($tpl.$tplName.".html")){
			$content=$this->view('header',$data);
			$content.=$this->templateView($tplName,$data,$name);
			$content.=$this->view('footer',$data);
			return $content;
			//View::config(['view_path' => $tpl]);		
			//return View::fetch('/'.$tplName,$data);
		}else{
            throw new TemplateNotFoundException(lang('not_found'), $tpl);
		}
		
    }
	
	public function view($tplName, $data){
        View::config(['view_path' => '../public/'.DIR_ADMIN.'/template/'.$data['themes'].'/', 'view_suffix' => 'php']);
		return View::fetch('/'.$tplName,$data);
    }
	
	public function pluginView($tplName, $data, $name){
        View::config(['view_path' => '../public/plugins/addon/'.$name.'/template/admin/', 'view_suffix' => 'html']);
		return View::fetch('/'.$tplName,$data);
    }

    public function templateView($tplName, $data, $name){
        View::config(['view_path' => '../public/web/'.$name.'/controller/template/admin/', 'view_suffix' => 'html']);
		return View::fetch('/'.$tplName,$data);
    }
}
