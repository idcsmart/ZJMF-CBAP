<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 合作伙伴模型
 * @desc 合作伙伴模型
 * @use app\common\model\PartnerModel
 */
class PartnerModel extends Model
{
    protected $name = 'partner';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'name'          => 'string',
        'img'           => 'string',
        'description'   => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2023-02-28
     * @title 获取合作伙伴
     * @desc 获取合作伙伴
     * @author theworld
     * @version v1
     * @return array list - 合作伙伴
     * @return int list[].id - 合作伙伴ID 
     * @return string list[].name - 名称 
     * @return string list[].img - 图片地址 
     * @return string list[].description - 描述
     */
    public function partnerList()
    {
        $list = $this->field('id,name,img,description')
            ->select()
            ->toArray();

        return ['list' => $list];
    }

    /**
     * 时间 2023-02-28
     * @title 添加合作伙伴
     * @desc 添加合作伙伴
     * @author theworld
     * @version v1
     * @param string name - 名称 required
     * @param string img - 图片地址 required
     * @param string description - 描述 required
     */
    public function createPartner($param)
    {
        $this->startTrans();
        try{
            $partner = $this->create([
                'name' => $param['name'],
                'img' => $param['img'],
                'description' => $param['description'],
                'create_time' => time(),
            ]);

            # 记录日志
            active_log(lang('log_add_partner', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $param['name']]), 'partner', $partner->id);
            
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('create_fail')];
        }

        return ['status'=>200, 'msg'=>lang('create_success')];
    }

    /**
     * 时间 2023-02-28
     * @title 编辑合作伙伴
     * @desc 编辑合作伙伴
     * @author theworld
     * @version v1
     * @param int id - 合作伙伴ID required
     * @param string name - 名称 required
     * @param string img - 图片地址 required
     * @param string description - 描述 required
     */
    public function updatePartner($param)
    {
        $partner = $this->find($param['id']);
        if(empty($partner)){
            return ['status'=>400, 'msg'=>lang('partner_is_not_exist')];
        }

        $this->startTrans();
        try{
            $this->update([
                'name' => $param['name'],
                'img' => $param['img'],
                'description' => $param['description'],
                'update_time' => time(),
            ], ['id' => $param['id']]);

            $description = [];

            $desc = [
                'name'          => lang('partner_name'),
                'img'           => lang('partner_img'),
                'description'   => lang('partner_description'),
            ];

            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $partner[$k] != $param[$k]){
                    $old = $partner[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_partner', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{name}'   => $partner['name'],
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description, 'partner', $partner->id);
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
     * @title 删除合作伙伴
     * @desc 删除合作伙伴
     * @author theworld
     * @version v1
     * @param int id - 合作伙伴ID required
     */
    public function deletePartner($id)
    {
        $partner = $this->find($id);
        if(empty($partner)){
            return ['status'=>400, 'msg'=>lang('partner_is_not_exist')];
        }

        $this->startTrans();
        try{
            # 记录日志
            active_log(lang('log_delete_partner', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $partner['name']]), 'partner', $partner->id);
            
            $this->destroy($id);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('delete_fail')];
        }

        return ['status'=>200,'msg'=>lang('delete_success')];
    }
}