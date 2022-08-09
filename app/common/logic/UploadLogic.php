<?php

namespace app\common\logic;

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
     * @return  array
     * @return  int status - 状态,200=成功,400=失败
     * @return  string msg - 信息
     */
    function moveTo($file, $path='')
    {
        $path = $path?:$this->fileSave;

        if(is_array($file)){
            $ret = [];
            foreach ($file as $v){
                $tmp = $this->moveTo($v,$path);
                if(isset($tmp['error'])){
                    return $tmp;
                }

                $ret[] = $tmp;
            }
            return $ret;
        }

        $file = htmlspecialchars_decode($file);

        $filepath = UPLOAD_DEFAULT . $file;

        $newfile = $path . $file;

        if(file_exists($newfile)){
            return ['error' => lang('file_is_not_exist')];
        }

        if (!file_exists($filepath)) {
            return ['error' => lang('file_is_not_exist')];
        }

        // 查看路径是否存在
        if (!file_exists($path)) {
            mkdir($path,0777,true);
        }

        try {
            if (copy($filepath,$newfile)) {
                unlink($filepath);
                return $file;
            }
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }

        return ['error' => lang('move_fail')];
    }

}