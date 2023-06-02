<?php
namespace app\api\controller;

use app\common\logic\UpstreamLogic;
use app\common\model\ClientModel;
use app\common\model\HostModel;
use app\common\model\NoticeSettingModel;
use function Symfony\Component\String\u;

/*
 * API鉴权登录
 *
 * */
class AuthController
{
    /**
     * 时间 2023-02-16
     * @title API鉴权登录
     * @desc API鉴权登录
     * @author wyh
     * @version v1
     * @url /api/v1/auth
     * @method  POST
     * @param string username - 用户名(用户注册时的邮箱或手机号)
     * @param string password - 密码(api信息的token)
     */
    public function auth()
    {
        $param = request()->param();

        $validate = new \think\Validate([
            'username' => 'require|length:4,20',
            'password' => 'require'
        ]);
        $validate->message([
            'username.require' => '用户不能为空',
            'username.length' => '用户名4-20位',
            'password.require' => '密码不能为空',
        ]);
        if (!$validate->check($param)) {
            return json(['status' => 400, 'msg' => '鉴权失败']);
        }

        $ClientModel = new ClientModel();

        $result = $ClientModel->apiAuth($param);

        return json($result);
    }

    /**
     * 时间 2023-02-16
     * @title API同步信息
     * @desc API同步信息：上游同步产品信息至本地
     * @author wyh
     * @version v1
     * @url /api/v1/host/sync
     * @method  POST
     */
    public function hostSync()
    {
        $param = request()->param();

        $id = $param['id'];

        if (empty($param['signature'])){
            return json(['status'=>400,'msg'=>'签名错误']);
        }

        $HostModel = new HostModel();
        $host = $HostModel->alias('h')
            ->field('h.id,uh.upstream_info,h.status,h.name,h.client_id,h.due_time,h.suspend_type,h.suspend_reason')
            ->leftJoin('upstream_host uh','uh.host_id=h.id')
            ->where('h.id',$id)
            ->find();
        $stream_info = json_decode($host['upstream_info'], true);
        $token = $stream_info['token'];
        if(empty($token)){
            $result['status'] = 400;
            $result['msg'] = '该产品不能使用该接口';
            return json($result);
        }
        $param['password'] = html_entity_decode($param['password'], ENT_QUOTES);
        $param['token'] = $token;
        $UpstreamLogic = new UpstreamLogic();
        if(!$UpstreamLogic->validateSign($param, $param['signature'])){
            $result['status'] = 400;
            $result['msg'] = '签名验证失败';
            return json($result);
        }

        $hostStatus = $host['status'];

        $update = [
            'name'=>$param['domain'],
        ];
        if (!empty($param['nextduedate'])){
            $update['due_time'] = $param['nextduedate'];# wyh 20210817 上游推送 到期时间
        }
        if(isset($param['domainstatus']) && in_array($param['domainstatus'], ['Pending','Active','Cancelled','Deleted','Suspended'])){
            $update['status'] = $param['domainstatus'];
        }
        if(isset($param['suspendreason'])){
            $reason = explode("-",$param['suspendreason'])??[];
            if (!empty($reason[0])){
                if ($reason[0]=='due'){
                    $update['suspend_type'] = 'overdue';
                }elseif ($reason[0]=='flow'){
                    $update['suspend_type'] = 'overtraffic';
                }elseif ($reason[0]=='uncertifi'){
                    $update['suspend_type'] = 'certification_not_complete';
                }else{
                    $update['suspend_type'] = 'other';
                }

                $update['suspend_reason'] = $reason[1]??'';
            }

        }
        $updateResult = $HostModel->where('id',$id)->update($update);
        if ($param['type']=='create'){
            // 一开始为开通状态
            if($hostStatus=="Active"){
                $result['status'] = 200;
                $result['msg'] = '更新成功';
                return json($result);
            }else{
                // 发送开通通知
                if ($param['domainstatus']=='Active' && $hostStatus=='Pending'){
                    $host_active = (new NoticeSettingModel())->indexSetting('host_active');
                    if($host_active['sms_enable']==1){
                        add_task([
                            'type' => 'email',
                            'description' => '产品开通成功,发送邮件',
                            'task_data' => [
                                'name'=>'host_active',//发送动作名称
                                'host_id'=>$id,//主机ID
                            ],
                        ]);
                    }
                    if($host_active['email_enable']==1){
                        add_task([
                            'type' => 'sms',
                            'description' => '产品开通成功,发送短信',
                            'task_data' => [
                                'name'=>'host_active',//发送动作名称
                                'host_id'=>$id,//主机ID
                            ],
                        ]);
                    }

                    $description = lang('log_module_create_account_success', [
                        '{host}'=> 'host#'.$id.'#'.$host['name'].'#',
                    ]);
                    active_log($description, 'host', $id);
                }elseif ($param['domainstatus']=="Pending"){ // 开通失败
                    $description = lang('log_module_create_account_failed', [
                        '{host}'=>'host#'.$host->id.'#'.$host['name'].'#',
                        '{reason}'=>"上游开通失败" ?? '',
                    ]);
                    active_log($description, 'host', $id);
                }else{
                    $result['status'] = 400;
                    $result['msg'] = '更新失败';
                    return json($result);
                }
            }
        }
        if ($updateResult){
            $map = [
                'name' => '主机名',
                'status' => '主机状态',
                'due_time' => '到期时间',
                'suspend_type' => '暂停类型',
                'suspend_reason' => '暂停原因'
            ];
            $desc = "";
            foreach ($update as $k=>$v){
                if (isset($host[$k]) && $host[$k]!=$v){
                    $desc .= $map[$k] . "修改为:" . $v . ',';
                }
            }
            active_log(rtrim($desc,','), 'host', $id);
        }
        $result['status'] = 200;
        $result['msg'] = '更新成功';
        return json($result);
    }
}