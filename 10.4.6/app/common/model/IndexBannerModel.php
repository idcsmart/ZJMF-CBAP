<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 模板控制器-首页轮播图模型
 * @desc 模板控制器-首页轮播图模型
 * @use app\common\model\IndexBannerModel
 */
class IndexBannerModel extends Model
{
    protected $name = 'index_banner';

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
     * @title 首页轮播图列表
     * @desc 首页轮播图列表
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
     * @title 添加首页轮播图
     * @desc 添加首页轮播图
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
            active_log(lang('log_add_index_banner', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{id}' => $banner['id']]), 'index_banner', $banner->id);

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
     * @title 修改首页轮播图
     * @desc 修改首页轮播图
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
            return ['status'=>400, 'msg'=>lang('index_banner_not_exist')];
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
                'img'           => lang('index_banner_img'),
                'url'           => lang('index_banner_url'),
                'start_time'    => lang('index_banner_start_time'),
                'end_time'      => lang('index_banner_end_time'),
                'show'          => lang('index_banner_show'),
                'notes'         => lang('index_banner_notes'),
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
                $description = lang('log_update_index_banner', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{id}'   => $banner['id'],
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description, 'index_banner', $banner->id);
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
     * @title 删除首页轮播图
     * @desc 删除首页轮播图
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
            return ['status'=>400, 'msg'=>lang('index_banner_not_exist')];
        }
        $this->startTrans();
        try{
            # 记录日志
            active_log(lang('log_delete_index_banner', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{id}' => $banner['id']]), 'index_banner', $banner->id);

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
     * @title 展示首页轮播图
     * @desc 展示首页轮播图
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
            return ['status'=>400, 'msg'=>lang('index_banner_not_exist')];
        }

        $this->startTrans();
        try{
            $this->update([
                'show' => $param['show'],
                'update_time' => time(),
            ], ['id' => $param['id']]);

            # 记录日志
            if($param['show']==1){
                active_log(lang('log_show_index_banner', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{id}' => $banner['id']]), 'index_banner', $banner->id);
            }else{
                active_log(lang('log_hide_index_banner', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{id}' => $banner['id']]), 'index_banner', $banner->id);
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
     * @title 首页轮播图排序
     * @desc 首页轮播图排序
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
            return ['status'=>400,'msg'=>lang('index_banner_not_exist')];
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
            active_log(lang('log_order_index_banner', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#']));

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>lang('move_fail') . ':' . $e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang('move_success')];
    }

    /**
     * 时间 2024-04-02
     * @title 首页数据
     * @desc 首页数据
     * @author theworld
     * @version v1
     * @return array banner - 轮播图
     * @return int banner[].id - 轮播图ID
     * @return string banner[].img - 图片
     * @return string banner[].url - 跳转链接
     * @return string banner[].notes - 备注
     */
    public function webData()
    {
        $time = strtotime(date("Y-m-d"));
        $banners = $this->field('id,img,url,notes')
            ->where('show', 1)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->order('order', 'asc')
            ->select()->toArray();

        return ['banner' => $banners];
    }
}