<?php
namespace app\common\model;

use think\Model;
use think\Db;

/**
 * @title 用户信息记录模型
 * @desc 用户信息记录模型
 * @use app\common\model\ClientRecordModel
 */
class ClientRecordModel extends Model
{
	protected $name = 'client_record';

	// 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'client_id'     => 'int',
        'admin_id'      => 'int',
        'content'       => 'string',
        'attachment'    => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2023-03-21
     * @title 用户信息记录列表
     * @desc 用户信息记录列表
     * @author theworld
     * @version v1
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list - 用户信息记录
     * @return int list[].id - 用户信息记录ID 
     * @return string list[].content - 内容
     * @return array list[].attachment - 附件
     * @return int list[].admin_id - 管理员ID 
     * @return string list[].admin_name - 管理员名称
     * @return int list[].create_time - 创建时间
     * @return int count - 用户信息记录总数
     */
    public function clientRecordList($param)
    {
        $count = $this->alias('a')
            ->field('a.id')
            ->where('a.client_id', $param['id'])
            ->count();

        $url = request()->domain() . '/upload/common/default/';

        $list = $this->alias('a')
            ->field('a.id,a.content,a.attachment,a.admin_id,b.name admin_name,a.create_time')
            ->leftjoin('admin b', 'b.id=a.admin_id')
            ->where('a.client_id', $param['id'])
            ->limit($param['limit'])
            ->page($param['page'])
            ->order('a.create_time', 'desc')
            ->select()
            ->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['attachment'] = !empty($value['attachment']) ? explode(',', $value['attachment']) : [];
        }

        return ['list' => $list, 'count' => $count];
    }

    /**
     * 时间 2023-03-21
     * @title 新增用户信息记录
     * @desc 新增用户信息记录
     * @author theworld
     * @version v1
     * @param int id - 用户ID required
     * @param string content - 名称 required
     * @param array attachment - 附件
     */
    public function createClientRecord($param)
    {
        $adminId = get_admin_id();

        $client = ClientModel::find($param['id']);
        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('client_is_not_exist')];
        }
        $param['attachment'] = $param['attachment'] ?? [];
        foreach ($param['attachment'] as $key => $value) {
            if(!file_exists(UPLOAD_DEFAULT.$value)){
                return ['status' => 400, 'msg' => lang('upload_file_is_not_exist')];
            }
        }

        $this->startTrans();
        try{
            $this->create([
                'client_id' => $param['id'],
                'admin_id' => $adminId, 
                'content' => $param['content'] ?? '',
                'attachment' => implode(',', $param['attachment']),
                'create_time' => time(),
            ]);
            
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('create_fail')];
        }

        return ['status'=>200, 'msg'=>lang('create_success')];
    }

    /**
     * 时间 2023-03-21
     * @title 编辑用户信息记录
     * @desc 编辑用户信息记录
     * @author theworld
     * @version v1
     * @param int id - 用户信息记录ID required
     * @param string content - 名称 required
     * @param array attachment - 附件
     */
    public function updateClientRecord($param)
    {
        $adminId = get_admin_id();

        $record = $this->find($param['id']);
        if (empty($record)){
            return ['status'=>400, 'msg'=>lang('client_record_is_not_exist')];
        }
        $param['attachment'] = $param['attachment'] ?? [];
        foreach ($param['attachment'] as $key => $value) {
            if(!file_exists(UPLOAD_DEFAULT.$value)){
                return ['status' => 400, 'msg' => lang('upload_file_is_not_exist')];
            }
        }

        $this->startTrans();
        try{
            $this->update([
                'admin_id' => $adminId, 
                'content' => $param['content'] ?? '',
                'attachment' => implode(',', $param['attachment']),
                'update_time' => time(),
            ], ['id' => $param['id']]);
            
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('update_fail')];
        }

        return ['status'=>200, 'msg'=>lang('update_success')];
    }

    /**
     * 时间 2023-03-21
     * @title 删除用户信息记录
     * @desc 删除用户信息记录
     * @author theworld
     * @version v1
     * @param int id - 用户信息记录ID required
     */
    public function deleteClientRecord($id)
    {
        $record = $this->find($id);
        if (empty($record)){
            return ['status'=>400, 'msg'=>lang('client_record_is_not_exist')];
        }

        $this->startTrans();
        try{
            $this->destroy($id);
            
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('delete_fail')];
        }

        return ['status'=>200, 'msg'=>lang('delete_success')];
    }

}
