<?php
namespace app\admin\controller;

use app\common\logic\UpgradeSystemLogic;

/**
 * @title 系统升级
 * @desc 系统升级
 * @use app\admin\controller\UpgradeSystemController
 */
class UpgradeSystemController extends AdminBaseController
{
    /**
     * 时间 2022-07-21
     * @title 获取系统版本
     * @desc 获取系统版本
     * @author theworld
     * @version v1
     * @url /admin/v1/system/version
     * @method  GET
     * @return string version - 当前系统版本 
     * @return string last_version - 最新系统版本 
     * @return string last_version_check - 最新系统版本检测结果 
     * @return int is_download - 更新包是否下载完毕:0否1是 
     * @return string license - 授权码
     * @return string service_due_time - 服务到期时间
     * @return string due_time - 授权到期时间
     * @return string system_version_type - 系统升级版本beta内测版stable正式版
     * @return string system_version_type_last - 最后一次系统升级版本beta内测版stable正式版
     */
    public function systemVersion()
    {
        $UpgradeSystemLogic = new UpgradeSystemLogic();
        $result = $UpgradeSystemLogic->getSystemVersion();
        return json($result);
    }

    /**
     * 时间 2024-06-05
     * @title 更改系统升级版本
     * @desc 更改系统升级版本
     * @author theworld
     * @version v1
     * @url /admin/v1/system/system_version_type
     * @method  PUT
     * @param string system_version_type - 系统升级版本beta内测版stable正式版 required
     */
    public function updateSystemVersionType()
    {
        // 接收参数
        $param = $this->request->param();
        
        $UpgradeSystemLogic = new UpgradeSystemLogic();
        $result = $UpgradeSystemLogic->updateSystemVersionType($param);
        return json($result);
    }

    /**
     * 时间 2022-07-21
     * @title 获取更新内容
     * @desc 获取更新内容
     * @author theworld
     * @version v1
     * @url /admin/v1/system/upgrade_content
     * @method  GET
     * @return string warning - 必读内容 
     * @return string content - 更新内容 
     */
    public function upgradeContent()
    {
        $UpgradeSystemLogic = new UpgradeSystemLogic();
        $result = $UpgradeSystemLogic->getUpgradeContent();
        return json($result);
    }

    /**
     * 时间 2022-07-21
     * @title 更新下载
     * @desc 更新下载
     * @author theworld
     * @version v1
     * @url /admin/v1/system/upgrade_download
     * @method  GET
     */
    public function upgradeDownload()
    {
        $UpgradeSystemLogic = new UpgradeSystemLogic();
        $result = $UpgradeSystemLogic->upgradeDownload();
        return json($result);
    }

    /**
     * 时间 2022-07-21
     * @title 获取更新下载进度
     * @desc 获取更新下载进度
     * @author theworld
     * @version v1
     * @url /admin/v1/system/upgrade_download_progress
     * @method  GET
     * @return string progress - 下载百分比 
     * @return string moment_size - 已下载大小,MB
     * @return string origin_size - 文件总大小,MB
     */
    public function upgradeDownloadProgress()
    {
        $UpgradeSystemLogic = new UpgradeSystemLogic();
        $result = $UpgradeSystemLogic->getUpgradeDownloadProgress();
        return json($result);
    }

    /**
     * 时间 2023-10-13
     * @title 获取授权信息
     * @desc 获取授权信息
     * @author theworld
     * @version v1
     * @url /admin/v1/system/auth
     * @method  GET
     */
    public function getAuth()
    {
        $UpgradeSystemLogic = new UpgradeSystemLogic();
        $result = $UpgradeSystemLogic->getAuth();
        return json($result);
    }

    /**
     * 时间 2023-10-13
     * @title 更换授权码
     * @desc 更换授权码
     * @author theworld
     * @version v1
     * @url /admin/v1/system/license
     * @method  PUT
     * @param string license - 授权码 required
     */
    public function updateLicense()
    {
        // 接收参数
        $param = $this->request->param();
        
        $UpgradeSystemLogic = new UpgradeSystemLogic();
        $result = $UpgradeSystemLogic->updateLicense($param);
        return json($result);
    }
}