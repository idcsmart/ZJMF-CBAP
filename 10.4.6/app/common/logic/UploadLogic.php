<?php

namespace app\common\logic;

use app\common\model\FileLogModel;
use app\common\validate\UploadValidate;
use think\file\UploadedFile;

/**
 * @title 文件上传公共类
 * @desc 文件上传公共类
 * @use app\common\logic\UploadLogic
 */
class UploadLogic
{
    # 文件保存路径
    private $fileSave;

    public function __construct($fileSave = '')
    {
        $this->fileSave = ($fileSave && is_string($fileSave)) ? $fileSave : UPLOAD_DEFAULT;
        if (!is_dir($this->fileSave)) mkdir($this->fileSave);
        if(!is_writable($this->fileSave)) chmod( $this->fileSave,0755);
    }

    /**
     * 时间 2022-06-20
     * @title 文件上传
     * @desc 文件上传
     * @author wyh
     * @version v1
     * @param resource file - 文件资源
     * @param bool origin true 文件名是否加入原文件名
     * @param string split ^ 系统文件名+原文件名的连接符号,默认^
     * @return  array
     * @return  int status - 状态,200=成功,400=失败
     * @return  string msg - 信息
     * @return string data.save_name - 文件名
     * @return string data.image_base64 - 图片base64,文件为图片才返回
     * @return string data.image_url - 图片地址,文件为图片才返回
     */
    public function uploadHandle(UploadedFile $file, $origin = true, $split = '^')
    {
        $param = [
            'file' => $file
        ];

        $validate = new UploadValidate();
        if (!$validate->scene('file')->check($param)) {
            return ['status'=>400,'msg'=>lang($validate->getError())];
        }

        $originalName = $file->getOriginalName();

        if ($origin) {
            $info = $file->move($this->fileSave, md5(uniqid()) . time() . $split . $originalName);
        } else {
            $info = $file->move($this->fileSave, md5(uniqid()) . time());
        }

        if ($info instanceof \SplFileInfo) {

            $saveName = $info->getFilename();

            $data = [
                'save_name' => $saveName,
            ];

            if (is_image(UPLOAD_DEFAULT . $saveName)){
                $data['image_base64'] = base64_encode_image(UPLOAD_DEFAULT . $saveName);
                $data['image_url'] = request()->domain() . '/upload/common/default/' . $saveName;
            }

            $adminId = get_admin_id();
            $clientId = get_client_id();
            if(!empty($adminId)){
                active_log(lang('log_admin_upload_file', ['{admin}'=>request()->admin_name, '{file}' => $saveName]), 'admin', $adminId);
            }else if(!empty($clientId)){
                active_log(lang('log_client_upload_file', ['{client}'=>'client#'.$clientId.'#'.request()->client_name.'#', '{file}' => $saveName]), 'client', $clientId);
            }
            

            return ['status'=>200,'msg'=>lang('upload_success'),'data'=>$data];
        } else {
            return ['status'=>400,'msg'=>lang('upload_fail')];
        }

    }

    /**
     * 时间 2022-06-20
     * @title 文件上传(仅支持图片)
     * @desc 文件上传(仅支持图片)
     * @author wyh
     * @version v1
     * @param resource file - 文件资源(仅支持图片)
     * @param bool origin true 文件名是否加入原文件名
     * @param string split ^ 系统文件名+原文件名的连接符号,默认^
     * @return  array
     * @return  int status - 状态,200=成功,400=失败
     * @return  string msg - 信息
     * @return string data.save_name - 文件名
     * @return string data.image_base64 - 图片base64,文件为图片才返回
     */
    public function uploadImgHandle(UploadedFile $file, $origin = true, $split = '^')
    {
        $param = [
            'image' => $file
        ];

        $validate = new UploadValidate();
        if (!$validate->scene('image')->check($param)) {
            return ['status'=>400,'msg'=>lang($validate->getError())];
        }

        $originalName = $file->getOriginalName();

        if ($origin) {
            $info = $file->move($this->fileSave, md5(uniqid()) . time() . $split . $originalName);
        } else {
            $info = $file->move($this->fileSave, md5(uniqid()) . time());
        }

        if ($info instanceof \SplFileInfo) {

            $saveName = $info->getFilename();

            $data = [
                'save_name' => $saveName,
            ];

            if (is_image(UPLOAD_DEFAULT . $saveName)){
                $data['image_base64'] = base64_encode_image(UPLOAD_DEFAULT . $saveName);
            }

            return ['status'=>200,'msg'=>lang('upload_success'),'data'=>$data];
        } else {
            return ['status'=>400,'msg'=>lang('upload_fail')];
        }

    }

    /**
     * 时间 2022-06-20
     * @title 移动文件至目标地址
     * @desc 移动文件至目标地址
     * @author wyh
     * @version v1
     * @param string|array file - 文件名,可传文件名数组
     * @param string path - 移动地址
     * @param string type - 文件类型：defautl系统默认、ticket工单、app应用等
     * @return  array
     * @return  int status - 状态,200=成功,400=失败
     * @return  string msg - 信息
     */
    function moveTo($file, $path='',$type='default')
    {
        $path = $path?:$this->fileSave;

        if(is_array($file)){
            $ret = [];
            foreach ($file as $v){
                if(stripos($v, '.php')!==false || stripos($v, '/')!==false || stripos($v, '\\')!==false){
                    return ['error' => lang('move_fail')];
                }
                $tmp = $this->moveTo($v,$path,$type);
                if(isset($tmp['error'])){
                    return $tmp;
                }

                $ret[] = $tmp;
            }
            return $ret;
        }

        $file = htmlspecialchars_decode($file);

        if(stripos($file, '.php')!==false || stripos($file, '/')!==false || stripos($file, '\\')!==false){
            return ['error' => lang('move_fail')];
        }

        $filepath = UPLOAD_DEFAULT . $file;

        if (!file_exists($filepath)){
            return $file;
        }

        // TODO 20240125 修改为对象存储 1.30更新隐藏
        $ossMethod = configuration("oss_method");
        $result = plugin_reflection($ossMethod,[
            'file_path' => $path,
            'file_name' => $file,
        ],'oss','upload');
        if (empty($result)){
            return ['error'=>lang("non_existent_storage_method")];
        }
        // 不管是否成功，删除临时文件
        if (file_exists($filepath)){
            unlink($filepath);
        }
        // 保存数据至数据库
        $FileLogModel = new FileLogModel();
        $uuid = explode("^",$file)[0]??explode(".",$file)[0];
        $FileLogModel->insert([
            'uuid' => $uuid,
            'save_name' => $file,
            'name' => explode("^",$file)[1]??"",
            'type' => $type,
            'oss_method' => $ossMethod,
            'create_time' => time(),
            'client_id' => get_client_id(),
            'admin_id' => get_admin_id(),
            'source' => get_client_id()>0?"client":"admin",
            'url' => $result['data']['url']??""
        ]);
        if (isset($result['status']) && $result['status']==200){
            return $file;
        }else{
            return ['error'=>$result['msg']??lang('move_fail')];
        }
    }

}