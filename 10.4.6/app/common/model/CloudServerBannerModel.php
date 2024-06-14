<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 模板控制器-云服务器轮播图模型
 * @desc 模板控制器-云服务器轮播图模型
 * @use app\common\model\CloudServerBannerModel
 */
class CloudServerBannerModel extends Model
{
    protected $name = 'cloud_server_banner';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'img'           => 'string',
        'url'           => 'string',
        'start_time'    => 'int',
        'end_time'      => 'int',
        'show'          => 'int',
        'notes'         => 'string',
        'order'         => 'int',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2024-04-02
     * @title 云服务器轮播图列表
     * @desc 云服务器轮播图列表
     * @author theworld
     * @version v1
     * @return array list - 轮播图
     * @return int list[].id - 轮播图ID
     * @return string list[].img - 图片
     * @return string list[].url - 跳转链接
     * @return int list[].start_time - 展示开始时间
     * @return int list[].end_time - 展示结束时间
     * @return int list[].show - 是否展示0否1是
     * @return string list[].notes - 备注
     */
    public function bannerList()
    {
        $banners = $this->field('id,img,url,start_time,end_time,show,notes')
            ->order('order', 'asc')
            ->select()->toArray();

        return ['list' => $banners];
    }

    /**
     * 时间 2024-04-02
     * @title 添加云服务器轮播图
     * @desc 添加云服务器轮播图
     * @author theworld
     * @version v1
     * @param string param.img - 图片 required
     * @param string param.url - 跳转链接 required
     * @param int param.start_time - 展示开始时间 required
     * @param int param.end_time - 展示结束时间 required
     * @param int param.show - 是否展示0否1是 required
     * @param string param.notes - 备注
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createBanner($param)
    {
        $this->startTrans();
        try{
            $order = $this->max('order');
            $banner = $this->create([
                'img' => $param['img'],
                'url' => $param['url'],
                'start_time' => $param['start_time'],
                'end_time' => $param['end_time'],
                'show' => $param['show'],
                'notes' => $param['notes'] ?? '',
                'order' => $order+1,
                'create_time' => time(),
            ]);

            # 记录日志
            active_log(lang('log_add_cloud_server_banner', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{id}' => $banner['id']]), 'cloud_server_banner', $banner->id);

            $this->commit();
            $result = ['status'=>200, 'msg'=>lang('create_success')];
        }catch (\Exception $e){
            $this->rollback();
            $result = ['status'=>400, 'msg'=>lang('create_fail')];
        }
        return $result;
    }

    /**
     * 时间 2024-04-02
     * @title 修改云服务器轮播图
     * @desc 修改云服务器轮播图
     * @author theworld
     * @version v1
     * @param int param.id - 轮播图ID required
     * @param string param.img - 图片 required
     * @param string param.url - 跳转链接 required
     * @param int param.start_time - 展示开始时间 required
     * @param int param.end_time - 展示结束时间 required
     * @param int param.show - 是否展示0否1是 required
     * @param string param.notes - 备注
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateBanner($param)
    {
        $banner = $this->find($param['id']);
        if(empty($banner)){
            return ['status'=>400, 'msg'=>lang('cloud_server_banner_not_exist')];
        }

        $this->startTrans();
        try{
            $order = $this->max('order');
            $this->update([
                'img' => $param['img'],
                'url' => $param['url'],
                'start_time' => $param['start_time'],
                'end_time' => $param['end_time'],
                'show' => $param['show'],
                'notes' => $param['notes'] ?? '',
                'update_time' => time(),
            ], ['id' => $param['id']]);

            $description = [];

            $desc = [
                'img'           => lang('cloud_server_banner_img'),
                'url'           => lang('cloud_server_banner_url'),
                'start_time'    => lang('cloud_server_banner_start_time'),
                'end_time'      => lang('cloud_server_banner_end_time'),
                'show'          => lang('cloud_server_banner_show'),
                'notes'         => lang('cloud_server_banner_notes'),
            ];

            $param['start_time'] = date("Y-m-d", $param['start_time']);
            $banner['start_time'] = date("Y-m-d", $banner['start_time']);

            $param['end_time'] = date("Y-m-d", $param['end_time']);
            $banner['end_time'] = date("Y-m-d", $banner['end_time']);

            $param['show'] = lang('whether_'.$param['show']);
            $banner['show'] = lang('whether_'.$banner['show']);

            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $banner[$k] != $param[$k]){
                    $old = $banner[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_cloud_server_banner', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{id}'   => $banner['id'],
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description, 'cloud_server_banner', $banner->id);
            }

            $this->commit();
            $result = ['status'=>200, 'msg'=>lang('update_success')];
        }catch (\Exception $e){
            $this->rollback();
            $result = ['status'=>400, 'msg'=>lang('update_fail')];
        }
        return $result;
    }

    /**
     * 时间 2024-04-02
     * @title 删除云服务器轮播图
     * @desc 删除云服务器轮播图
     * @author theworld
     * @version v1
     * @param int id - 轮播图ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteBanner($id)
    {
        $banner = $this->find($id);
        if(empty($banner)){
            return ['status'=>400, 'msg'=>lang('cloud_server_banner_not_exist')];
        }
        $this->startTrans();
        try{
            # 记录日志
            active_log(lang('log_delete_cloud_server_banner', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{id}' => $banner['id']]), 'cloud_server_banner', $banner->id);

            $this->destroy($id);
            $this->commit();
            $result = ['status'=>200, 'msg'=>lang('delete_success')];
        }catch (\Exception $e){
            $this->rollback();
            $result = ['status'=>400, 'msg'=>lang('delete_fail')];
        }
        return $result;
    }

    /**
     * 时间 2024-04-02
     * @title 展示云服务器轮播图
     * @desc 展示云服务器轮播图
     * @author theworld
     * @version v1
     * @param int param.id - 轮播图ID required
     * @param int param.show - 是否展示0否1是 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function showBanner($param)
    {
        $banner = $this->find($param['id']);
        if(empty($banner)){
            return ['status'=>400, 'msg'=>lang('cloud_server_banner_not_exist')];
        }

        $this->startTrans();
        try{
            $this->update([
                'show' => $param['show'],
                'update_time' => time(),
            ], ['id' => $param['id']]);

            # 记录日志
            if($param['show']==1){
                active_log(lang('log_show_cloud_server_banner', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{id}' => $banner['id']]), 'cloud_server_banner', $banner->id);
            }else{
                active_log(lang('log_hide_cloud_server_banner', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{id}' => $banner['id']]), 'cloud_server_banner', $banner->id);
            }

            $this->commit();
            $result = ['status'=>200, 'msg'=>lang('success_message')];
        }catch (\Exception $e){
            $this->rollback();
            $result = ['status'=>400, 'msg'=>lang('fail_message')];
        }
        return $result;
    }

    /**
     * 时间 2024-04-02
     * @title 云服务器轮播图排序
     * @desc 云服务器轮播图排序
     * @author theworld
     * @version v1
     * @param array param.id - 轮播图ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function orderBanner($param)
    {
        $id = $param['id'] ?? [];

        # 基础验证
        $banner = $this->column('id');
        if(count($id)!=count($banner) || count($id)!=count(array_intersect($banner, $id))){
            return ['status'=>400,'msg'=>lang('cloud_server_banner_not_exist')];
        }

        # 排序处理
        $this->startTrans();
        try{
            foreach ($id as $key => $value) {
                $this->update([
                    'order' => $key
                ], ['id' => $value]);
            }

            # 记录日志
            active_log(lang('log_order_cloud_server_banner', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#']));

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>lang('move_fail') . ':' . $e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang('move_success')];
    }
}