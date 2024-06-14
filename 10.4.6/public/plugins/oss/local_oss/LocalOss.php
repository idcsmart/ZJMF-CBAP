<?php
namespace oss\local_oss;

use app\common\lib\Plugin;
use app\common\model\FileLogModel;
use think\facade\Db;

/**
 * @desc 本地存储
 * @author wyh
 * @version 1.0
 * @time 2024-01-25
 */
class LocalOss extends Plugin
{
    public $info = array(
        'name'        => 'LocalOss',//Demo插件英文名，改成你的插件英文就行了
        'title'       => '本地存储',
        'description' => '本地存储',
        'status'      => 1,
        'author'      => '智简魔方',
        'version'     => '1.0.0',
        'module'      => 'oss',
        'help_url'    => ''
    );

    public $hasAdmin = 0;//插件是否有后台管理界面

    // 插件安装
    public function install()
    {
        return true;//安装成功返回true，失败false
    }

    // 插件卸载
    public function uninstall()
    {
        // 默认本地存储不可卸载
        return false;
    }

    /**
     * 时间 2024-04-16
     * @title 检测对象存储是否联通
     * @desc 检测对象存储是否联通
     * @author wyh
     * @version v1
     * @return boolean
     */
    public function LocalOssLink()
    {
        return true;
    }

    /**
     * 时间 2024-04-16
     * @title 对象存储是否有数据
     * @desc 对象存储是否有数据
     * @author wyh
     * @version v1
     * @return boolean
     */
    public function LocalOssData()
    {
        // TODO 本地存储一直存在文件
        return true;
    }

    /*
     * @title 实现上传(移动文件)
     * @desc 实现上传，调用系统/console/v1/upload或者/admin/v1/upload上传接口，
     * 会将文件保存到默认路径public/upload/common/default/（可由UPLOAD_DEFAULT常量表示）目录下；
     * @param string file_path - 文件保存路径
     * @param string file_name - 文件名，系统上传接口返回的save_name；UPLOAD_DEFAULT . $file_name即为文件路径
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return string data.url - 文件访问地址
     * */
    public function LocalOssUpload($param)
    {
        $file = $param['file_name']??"";

        $path = $param['file_path']??"";

        $filepath = UPLOAD_DEFAULT . $file;

        $newfile = rtrim($path,'/') . "/" . $file;

        if(file_exists($newfile)){
            return ['error' => lang('file_is_exist')];
        }

        if (!file_exists(UPLOAD_DEFAULT.$param['file_name'])) {
            return ['error' => lang('file_is_not_exist')];
        }

        // 查看路径是否存在
        if (!file_exists($path)) {
            mkdir($path,0777,true);
        }

        try {
            if (copy($filepath,$newfile)) {
                // 查找 'public' 字符串的位置
                $pos = strpos($newfile, "public");
                if ($pos !== false) {
                    // 截取 'public' 后面的所有字符串
                    $access = substr($newfile, $pos + strlen("public"));
                } else {
                    $access = $newfile;
                }
                return ['status'=>200,'msg'=>lang("success_message"),'data'=>['url'=>request()->domain().$access]];
            }
        } catch (\Exception $e) {
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>400,'msg'=>lang('move_fail')];
    }

    /**
     * 时间 2024-04-16
     * @title 获取文件下载地址
     * @desc 获取文件下载地址
     * @author wyh
     * @version v1
     * @param string param.file_path - 文件保存路径 required
     * @param string param.file_name - 文件名 required
     * @param string param.action - 动作：preview预览，download下载 required
     * @return array
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return string data.url - 文件下载地址
     */
    public function LocalOssDownload($param)
    {
        // 返回临时地址(需要考虑兼容旧的文件数据)，设置过期时间5分钟
        $FileLogModel = new FileLogModel();
        $fileLog = $FileLogModel->where('save_name',$param['file_name'])->find();
        $timeout = 300;
        $expires = time()+$timeout;
        if (!empty($fileLog)){
            $tmp = $fileLog['id'];
        }else{
            // 兼容老数据
            $tmp = $param['file_name'];
        }
        $str = md5($tmp);
        idcsmart_cache('file_tmp_url_timeout_'.$str,json_encode($param),$timeout);
        $res = generate_signature(['fid'=>$str],AUTHCODE.$expires);
        $url = request()->domain()."/console/v1/resource?fid={$str}&expires={$expires}&rand_str={$res['rand_str']}&sign={$res['signature']}";

        return ['status'=>200,'msg'=>lang_plugins("success_message"),'data'=>['url'=>$url]];
    }

    // 获取配置
    public function config()
    {
        $config = Db::name('plugin')->where('name', $this->info['name'])->value('config');
        if (!empty($config) && $config != "null") {
            $config = json_decode($config, true);
        } else {
            $config = [];
        }
        $con = require dirname(__DIR__).'/local_oss/config/config.php';
        $config = array_merge($con,$config);

        return $config;
    }

}