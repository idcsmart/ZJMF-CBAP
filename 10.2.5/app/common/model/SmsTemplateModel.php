<?php
namespace app\common\model;

use think\Model;
use think\Db;
use app\admin\model\PluginModel;
use app\common\model\CountryModel;
/**
 * @title 短信模板管理
 * @desc 短信模板管理
 * @use app\common\model\SmsTemplateModel
 */
class SmsTemplateModel extends Model
{
	protected $name = 'sms_template';

    /**
     * 时间 2022-05-17
     * @title 获取短信模板
     * @desc 获取短信模板
     * @author xiong
     * @version v1
     * @param string name - 短信接口标识名称 required
     * @return array list - 短信模板
     * @return string list[].id - 短信模板ID 	 
     * @return string list[].template_id - 短信接口模板ID 
     * @return string list[].type - 模板类型（0大陆，1国际）
     * @return string list[].sms_name - 接口标识名称
     * @return string list[].title - 模板标题 
     * @return string list[].content - 模版内容 
     * @return string list[].notes - 备注 
     * @return string list[].status - 状态（0未提交审核，1审核中，2通过审核，3未通过审核）
     */
    public function smsTemplateList($name)
    {
        $sms_template = $this->field('id,template_id,type,title,content,notes,status,sms_name')
		->where('sms_name',$name)
		->select()
		->toArray();		
        return ['list' => $sms_template];
    }

    /**
     * 时间 2022-05-17
     * @title 获取单个短信模板
     * @desc 获取单个短信模板
     * @author xiong
     * @version v1
     * @param string param.name - 短信接口标识名称 required
     * @param int param.id - 短信模板id required
     * @return string id - ID 
     * @return string template_id - 模板ID 
     * @return string type - 模板类型（0大陆，1国际）
     * @return string title - 模板标题 
     * @return string content - 模版内容 
     * @return string notes - 备注 
     * @return string status - 状态（0未提交审核，1审核中，2通过审核，3未通过审核）
     */
    public function indexSmsTemplate($param)
    {
        $sms_template = $this->field('id,template_id,type,title,content,notes,status')
		->where('id',$param['id'])
		->where('sms_name',$param['name'])
		->select()
		->toArray();
        if (empty($sms_template)){
            return (object)[]; #转换为对象
        }
        return $sms_template[0];
    }  
	/**
     * 时间 2022-05-17
     * @title 更新短信模板状态
     * @desc 更新短信模板状态
     * @author xiong
     * @version v1
     * @param string param.name - 短信接口标识名称 required
     */
    public function statusSmsTemplate($param)
    {
        $sms_template = $this->field('id,template_id,type,title,content,notes,status')
		->where('status',1)
		->where('sms_name',$param['name'])
		->select()
		->toArray();
        if (empty($sms_template)){
            return (object)[]; #转换为对象
        }
		foreach($sms_template as $template){
			$data=[
				'name'=>$param['name'],
				'template_id'=>$template['template_id'],
			];
			if($template['type']==0){
				$sms_methods=$this->smsMethods('getCnTemplate',$data);
			}else if($template['type']==1){
				$sms_methods=$this->smsMethods('getGlobalTemplate',$data);
			}
			if($sms_methods['status'] == 'success'){
				$this->update([
					'status' => $sms_methods['template']['template_status'],
					'error' => !empty($sms_methods['msg'])?$sms_methods['msg']:'',
					'update_time' => time()
				], ['id' => $template['id']]);
			}
		}
		if ($sms_methods['status'] == 'success'){
            $result = ['status' => 200 , 'msg' => lang('success_message')];
        }else{
            $result = ['status' => 400 , 'msg' => lang('fail_message')];
        }
        return $result;
    }
	/**
     * 时间 2022-05-17
     * @title 批量提交短信模板
     * @desc 批量提交短信模板
     * @author xiong
     * @version v1
     * @param string param.name - 短信接口标识名称 required
     * @param array param.ids[] - 模板ID required
     */
    public function auditSmsTemplate($param)
    {
		$ids = $param['ids'];
		//批量处理
		if (is_string($ids) && !is_array($ids)){
			$ids = [$ids];
		}
        $sms_template = $this->field('id,template_id,type,title,content,notes,status')
		->where('status',0)
		->whereIn('id',$ids)
		->where('sms_name',$param['name'])
		->select()
		->toArray();
        if (empty($sms_template)){
            return (object)[]; #转换为对象
        }
		foreach($sms_template as $template){
			$data=[
				'name'=>$param['name'],
				'title'=>$template['title'],
				'content'=>$template['content'],
			];			
			if($template['type']==0){
				$sms_methods=$this->smsMethods('createCnTemplate',$data);
			}else if($template['type']==1){
				$sms_methods=$this->smsMethods('createGlobalTemplate',$data);
			}
			if($sms_methods['status'] == 'success'){
				$this->update([
					'template_id' => $sms_methods['template']['template_id']?:'',
					'status' => $sms_methods['template']['template_status']?:1,
					'error' => !empty($sms_methods['msg'])?:'', 
					'update_time' => time()
				], ['id' => $template['id']]);
			}
		}
        $result = ['status' => 200 , 'msg' => lang('success_message')];
        return $result;
    }

    /**
     * 时间 2022-05-17
     * @title 创建短信模板
     * @desc 创建短信模板
     * @author xiong
     * @version v1
     * @param string param.name - 短信接口标识名称 required
     * @param string param.template_id - 模板ID 
     * @param string param.type - 模板类型（0大陆，1国际） required
     * @param string param.title - 模板标题 required
     * @param string param.content - 模版内容 required
     * @param string param.notes - 备注 
     * @param string param.status - 状态（0未提交审核，2通过审核，3未通过审核）
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createSmsTemplate($param)
    {
		$sms_interface = $this->smsInterface($param);
		if($sms_interface['status']==400){
			return $sms_interface;
		}
		
		if($param['status']!=2){
			//提交到接口
			if($param['type']==0){
				$sms_methods=$this->smsMethods('createCnTemplate',$param);
			}else if($param['type']==1){
				$sms_methods=$this->smsMethods('createGlobalTemplate',$param);
			}
			if(!empty($sms_methods['status']) && $sms_methods['status']==400){
				return $sms_methods;
			}
			if(!empty($sms_methods['status']) && $sms_methods['status']=='success'){
				$param['template_id']=$sms_methods['template']['template_id']?:'';
				$param['status']=$sms_methods['template']['template_status']?:1;
			}
			
		}
        $this->startTrans();
        try {
            $sms_id =$this->create([
                'sms_name' => $param['name'],
                'template_id' => $param['template_id'] ? trim($param['template_id']) : '',
                'type' => $param['type'] ? intval($param['type']) : 0,
                'title' => $param['title'] ? trim($param['title']) : '',
                'content' => $param['content'] ? trim($param['content']) : '',
                'notes' => $param['notes'] ? trim($param['notes']) : '',
                'status' => $param['status'] ? trim($param['status']) : '',
                'create_time' => time()
            ]);
			//日志
			$module_sms = (new PluginModel)->pluginList(['module'=>'sms']);				
			$sms_list = array_column($module_sms['list'],"title","name");	
			$sms_name=$sms_list[$param['name']];
			$sms_title=$param['title'];
            active_log(lang('admin_sms_template_log_create', ['{admin}'=>request()->admin_name, '{sms_name}'=>$sms_name, '{sms_title}'=>$sms_title]), 'admin', request()->admin_id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('create_fail')];
        }
        return ['status' => 200, 'msg' => lang('create_success') , 'id' => $sms_id->id];
    }

    /**
     * 时间 2022-05-17
     * @title 修改短信模板
     * @desc 修改短信模板
     * @author xiong
     * @version v1
     * @param string param.name - 短信接口标识名称 required
     * @param int param.id - 短信模板id required
     * @param string param.template_id - 模板ID 
     * @param string param.type - 模板类型（0大陆，1国际）required
     * @param string param.title - 模板标题 required
     * @param string param.content - 模版内容 required
     * @param string param.notes - 备注 
     * @param string param.status - 状态（0未提交审核，2通过审核，3未通过审核）
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateSmsTemplate($param)
    {
        $sms = $this->find($param['id']);
        if (empty($sms)){
            return ['status'=>400, 'msg'=>lang('sms_template_is_not_exist')];
        }
		if ($sms['status']==1){
            return ['status'=>400, 'msg'=>lang('sms_template_cannot_be_modified')];
        }
		$sms_interface = $this->smsInterface($param);
		if($sms_interface['status']==400){
			return $sms_interface;
		}
		if($param['status']!=2){
			if($param['status']==0){
				//提交到接口
				if($param['type']==0){
					$sms_methods=$this->smsMethods('createCnTemplate',$param);
				}else if($param['type']==1){
					$sms_methods=$this->smsMethods('createGlobalTemplate',$param);
				}
				if(!empty($sms_methods['status']) && $sms_methods['status']==400){
					return $sms_methods;
				}
				if(!empty($sms_methods['status']) && $sms_methods['status']=='success'){
					$param['template_id']=$sms_methods['template']['template_id']?:'';
					$param['status']=$sms_methods['template']['template_status']?:1;
				}
			}else{
				//提交到接口
				if($param['type']==0){
					$sms_methods=$this->smsMethods('putCnTemplate',$param);
				}else if($param['type']==1){
					$sms_methods=$this->smsMethods('putGlobalTemplate',$param);
				}
				if(!empty($sms_methods['status']) && $sms_methods['status']==400){
					return $sms_methods;
				}
				if(!empty($sms_methods['status']) && $sms_methods['status']=='success'){
					$param['status']=$sms_methods['template']['template_status']?:1;
				}
				
			}
		}
		# 日志
		$description = [];
		$sms=$sms->toArray();
		$new_param=['template_id'=>$param['template_id'],'type'=>$param['type'],'title'=>$param['title'],'content'=>$param['content'],'notes'=>$param['notes'],'status'=>$param['status']];
		$desc = array_diff_assoc($new_param,$sms);
		foreach($desc as $k=>$v){
			$lang = '"'.lang("sms_template_log_{$k}").'"';
			if($k=='type'){
				$lang_old = lang("sms_template_log_type_{$sms[$k]}");
				$lang_new = lang("sms_template_log_type_{$v}");
			}else if($k=='status'){
				$lang_old = lang("sms_template_log_status_{$sms[$k]}");
				$lang_new = lang("sms_template_log_status_{$v}");
			}else{				
				$lang_old = $sms[$k];
				$lang_new = $v;		
			}
			
			$description[] = lang('admin_old_to_new',['{field}'=>$lang, '{old}'=>'"'.$lang_old.'"', '{new}'=>'"'.$lang_new.'"']);
		}
		$description = implode(',', $description);
        $this->startTrans();
        try {
            $this->update([
				'sms_name' => $param['name'],
                'template_id' => $param['template_id'] ? trim($param['template_id']) : '',
                'type' => $param['type'] ? intval($param['type']) : 0,
                'title' => $param['title'] ? trim($param['title']) : '',
                'content' => $param['content'] ? trim($param['content']) : '',
                'notes' => $param['notes'] ? trim($param['notes']) : '',
                'status' => $param['status'] ? trim($param['status']) : '',
                'update_time' => time()
            ], ['id' => $param['id']]);
            //日志
			$module_sms = (new PluginModel)->pluginList(['module'=>'sms']);				
			$sms_list = array_column($module_sms['list'],"title","name");	
			$sms_name=$sms_list[$param['name']];
            active_log(lang('admin_sms_template_log_update', ['{admin}'=>request()->admin_name, '{sms_name}'=>$sms_name, '{description}'=>$description]), 'admin', request()->admin_id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-05-17
     * @title 删除短信模板
     * @desc 删除短信模板
     * @author xiong
     * @version v1
     * @param int id - 短信模板id required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteSmsTemplate($id)
    {
        $sms = $this->find($id);
        if (empty($sms)){
            return ['status'=>400, 'msg'=>lang('sms_template_is_not_exist')];
        }
		
		$data=[
			'type' => $sms['type'],
			'name' => $sms['sms_name'],
			'template_id' => $sms['template_id'],
		];
		$sms_interface = $this->smsInterface($data);
		if($sms_interface['status']==400){
			return $sms_interface;
		}
		//提交到接口
		if($sms['type']==0){
			$sms_methods=$this->smsMethods('deleteCnTemplate',$data);
		}else if($sms['type']==1){
			$sms_methods=$this->smsMethods('deleteGlobalTemplate',$data);
		}
		if(!empty($sms_methods['status']) && $sms_methods['status']==400){
			return $sms_methods;
		}
		
        $this->startTrans();
        try {
            $this->destroy($id);
			//日志
			$module_sms = (new PluginModel)->pluginList(['module'=>'sms']);				
			$sms_list = array_column($module_sms['list'],"title","name");	
			$sms_name=$sms_list[$sms['sms_name']];
			$sms_title=$sms['title'];
            active_log(lang('admin_sms_template_log_delete', ['{admin}'=>request()->admin_name, '{sms_name}'=>$sms_name, '{sms_title}'=>$sms_title]), 'admin', request()->admin_id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang('delete_success')];
    }
    /**
     * 时间 2022-05-17
     * @title 测试短信模板
     * @desc 测试短信模板
     * @author xiong
     * @version v1
     * @param string param.name - 短信接口标识名称 required
     * @param int param.id - 短信模板id required
     * @param string param.phone_code - 手机区号 
     * @param string param.phone - 手机号 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function test($param)
    {
		$sms_template = $this->field('template_id,type,title,content,notes,status')
		->where('id',$param['id'])
		->where('sms_name',$param['name'])
		->select()
		->toArray();
        if (empty($sms_template)){
            return ['status'=>400, 'msg'=>lang('sms_template_is_not_exist')];
        }
		
		$sms_template = $sms_template[0];   
		if ($sms_template['status'] != 2){
            return ['status'=>400, 'msg'=>lang('sms_template_review_before_sending')];
        }
		if(!empty($param['phone_code'])){
			$param['phone_code'] = str_replace('+','',$param['phone_code']);	
			if($param['phone_code'] == '86' || empty($param['phone_code'])){
				$type = 0;
			}else{
				$type = 1;
			}
			$country = (new CountryModel())->checkPhoneCode($param['phone_code']);
			if(!$country){
				return ['status'=>400, 'msg'=>lang('send_sms_area_code_error')];//区号错误
			}
		}else{
			$param['phone_code'] = '';
			$type = 0;
		}
		if($sms_template['type'] != $type){
			return ['status'=>400, 'msg'=>lang('sms_template_is_not_exist')];//模板id错误
		}
		$smsInterface = [
			'name' => $param['name'],
			'type' => $sms_template['type'],
		]; 
		$sms_interface = $this->smsInterface($smsInterface);
		if($sms_interface['status']==400){
			return $sms_interface;
		}
		$template_param = [
			'code'=> rand(10000,99999),
		];

		$data = [
			'content' => $sms_template['content'],
			'template_param' => $template_param,
			'name' => $param['name'],
            'template_id' => $sms_template['template_id'],
            'test' => true // 测试模板
		];
		if($sms_template['type'] == 0){	
			$data['mobile'] = $param['phone'];
			$sms_methods = $this->smsMethods('sendCnSms',$data);
		}else{
			$data['mobile'] = $param['phone_code'].$param['phone'];
			$sms_methods = $this->smsMethods('sendGlobalSms',$data);
		}
		if($sms_methods['status'] == 'success'){
			return ['status'=>200, 'msg'=>lang('send_sms_success')];//短信发送成功
		}else{
			return ['status'=>400, 'msg'=>lang('send_sms_error').' : '.$sms_methods['msg']];//短信发送失败
		}
    }	
	//短信接口判断
	private function smsInterface($param){
		$sms = (new PluginModel)->pluginList(['module'=>'sms']);				
		$sms_type = array_column($sms['list'],"sms_type","name");	
		$sms_status = array_column($sms['list'],"status","name");
		if($param['type']==0){
		    if(empty($sms_type[$param['name']])){
				return ['status'=>400, 'msg'=>lang('send_sms_interface_is_not_exist_domestic')];//国内短信接口不存在
			}
			if($sms_status[$param['name']]==0){
				return ['status'=>400, 'msg'=>lang('send_sms_interface_is_disabled_domestic')];//国内短信接口已禁用
			}else if($sms_status[$param['name']]==3){
			    return ['status'=>400, 'msg'=>lang('send_sms_interface_not_installed_domestic')];//国内短信接口未安装
			}
			
		}else if($param['type']==1){
		    if(empty($sms_type[$param['name']])){
				return ['status'=>400, 'msg'=>lang('send_sms_interface_is_not_exist_global')];//国际短信接口不存在
			}
		    if($sms_status[$param['name']]==0){
				return ['status'=>400, 'msg'=>lang('send_sms_interface_is_disabled_global')];//国际短信接口已禁用
			}else if($sms_status[$param['name']]==3){
			    return ['status'=>400, 'msg'=>lang('send_sms_interface_not_installed_global')];//国际短信接口未安装
			}
			
		}
		return ['status' => 200];
	}
	//短信接口调用
	private function smsMethods($cmd,$param){
		//提交到接口
		
		$class = get_plugin_class($param['name'],'sms');
		if (!class_exists($class)) {
			return ['status'=>400, 'msg'=>lang('send_sms_interface_is_not_exist')];//短信接口不存在
		}
		$methods = get_class_methods($class)?:[];
		if(!in_array($cmd,$methods)){
			return ['status'=>400, 'msg'=>lang('send_sms_interface_not_supported')];//短信接口不支持
		}
		$sms = new $class;
		$config = $sms->getConfig();
		//获取模板
		if(strpos($cmd,"get")!==false){
			$data = [
				'template_id' => $param['template_id'] ? trim($param['template_id']) : '',
				'config' => $config?:[],
			];
		}else if(strpos($cmd,"create")!==false){
			$data = [
				'config' => $config?:[],
				'title' => $param['title'] ? trim($param['title']) : '',
				'content' => $param['content'] ? trim($param['content']) : '',
                'notes' => $param['notes']??''
			];
		}else if(strpos($cmd,"put")!==false){
			$data = [
				'template_id' => $param['template_id'] ? trim($param['template_id']) : '',
				'config' => $config?:[],
				'title' => $param['title'] ? trim($param['title']) : '',
				'content' => $param['content'] ? trim($param['content']) : '',
                'notes' => $param['notes']??''
			];
		}else if(strpos($cmd,"delete")!==false){
			$data = [
				'template_id' => $param['template_id'] ? trim($param['template_id']) : '',
				'config' => $config?:[],
			];
		}else if(strpos($cmd,"send")!==false){
			$data = [
				'mobile' => $param['mobile'],
				'content' => $param['content'],
				'templateParam' => $param['template_param'],
                'template_id' => $param['template_id'],
				'config' => $config?:[],
			];
			if (isset($param['test'])){
			    $data['test'] = $param['test'];
            }
		}

		return $sms->$cmd($data);
		
	}
	
}
