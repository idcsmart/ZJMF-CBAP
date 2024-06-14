<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 友情链接模型
 * @desc 友情链接模型
 * @use app\common\model\FriendlyLinkModel
 */
class FriendlyLinkModel extends Model
{
    protected $name = 'friendly_link';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'name'          => 'string',
        'url'           => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2023-02-28
     * @title 获取友情链接
     * @desc 获取友情链接
     * @author theworld
     * @version v1
     * @return array list - 友情链接
     * @return int list[].id - 友情链接ID 
     * @return string list[].name - 名称 
     * @return string list[].url - 链接地址 
     */
    public function friendlyLinkList()
    {
        $list = $this->field('id,name,url')
            ->select()
            ->toArray();

        return ['list' => $list];
    }

    /**
     * 时间 2023-02-28
     * @title 添加友情链接
     * @desc 添加友情链接
     * @author theworld
     * @version v1
     * @param string name - 名称 required
     * @param string url - 链接地址 required
     */
    public function createFriendlyLink($param)
    {
        $this->startTrans();
        try{
            $friendlyLink = $this->create([
                'name' => $param['name'],
                'url' => $param['url'],
                'create_time' => time(),
            ]);

            # 记录日志
            active_log(lang('log_add_friendly_link', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $param['name']]), 'friendly_link', $friendlyLink->id);
            
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('create_fail')];
        }

        return ['status'=>200, 'msg'=>lang('create_success')];
    }

    /**
     * 时间 2023-02-28
     * @title 编辑友情链接
     * @desc 编辑友情链接
     * @author theworld
     * @version v1
     * @param int id - 友情链接ID required
     * @param string name - 名称 required
     * @param string url - 链接地址 required
     */
    public function updateFriendlyLink($param)
    {
        $friendlyLink = $this->find($param['id']);
        if(empty($friendlyLink)){
            return ['status'=>400, 'msg'=>lang('friendly_link_is_not_exist')];
        }

        $this->startTrans();
        try{
            $this->update([
                'name' => $param['name'],
                'url' => $param['url'],
                'update_time' => time(),
            ], ['id' => $param['id']]);

            $description = [];

            $desc = [
                'name'  => lang('friendly_link_name'),
                'url'   => lang('friendly_link_url'),
            ];

            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $friendlyLink[$k] != $param[$k]){
                    $old = $friendlyLink[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_friendly_link', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{name}'   => $friendlyLink['name'],
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description, 'friendly_link', $friendlyLink->id);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail')];
        }

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2023-02-28
     * @title 删除友情链接
     * @desc 删除友情链接
     * @author theworld
     * @version v1
     * @param int id - 友情链接ID required
     */
    public function deleteFriendlyLink($id)
    {
        $friendlyLink = $this->find($id);
        if(empty($friendlyLink)){
            return ['status'=>400, 'msg'=>lang('friendly_link_is_not_exist')];
        }

        $this->startTrans();
        try{
            # 记录日志
            active_log(lang('log_delete_friendly_link', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $friendlyLink['name']]), 'friendly_link', $friendlyLink->id);
            
            $this->destroy($id);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('delete_fail')];
        }

        return ['status'=>200,'msg'=>lang('delete_success')];
    }
}