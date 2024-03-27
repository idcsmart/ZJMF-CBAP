<?php
namespace app\common\model;

use think\Model;

/**
 * @title 方案咨询模型
 * @desc 方案咨询模型
 * @use app\common\model\ConsultModel
 */
class ConsultModel extends Model
{
    protected $name = 'consult';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'client_id'     => 'int',
        'matter'        => 'string',
        'contact'       => 'string',
        'company'       => 'string',
        'phone'         => 'string',
        'email'         => 'string',
        'create_time'   => 'int',
    ];

    /**
     * 时间 2023-02-28
     * @title 方案咨询列表
     * @desc 方案咨询列表
     * @author theworld
     * @version v1
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 方案咨询
     * @return int list[].id - 方案咨询ID 
     * @return string list[].matter - 咨询事项 
     * @return string list[].contact - 联系人 
     * @return string list[].company - 公司名称
     * @return string list[].phone - 联系电话 
     * @return string list[].email - 联系邮箱
     * @return int list[].client_id - 用户ID
     * @return string list[].username - 用户名
     * @return int list[].create_time - 咨询时间
     * @return int count - 方案咨询总数
     */
    public function consultList($param)
    {
        
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'a.'.$param['orderby'] : 'a.id';

        $count = $this->alias('a')
            ->field('a.id')
            ->count();

        $list = $this->alias('a')
            ->field('a.id,a.matter,a.contact,a.company,a.phone,a.email,a.client_id,c.username,a.create_time')
            ->leftjoin('client c', 'c.id=a.client_id')
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();

        return ['list' => $list, 'count' => $count];
    }

    /**
     * 时间 2023-02-28
     * @title 提交方案咨询
     * @desc 提交方案咨询
     * @author theworld
     * @version v1
     * @param string param.contact - 联系人 required
     * @param string param.company - 公司名称
     * @param string param.phone - 手机号码 手机号码和邮箱二选一必填
     * @param string param.email - 联系邮箱 手机号码和邮箱二选一必填
     * @param string param.matter - 咨询产品 required
     * @return  int status - 状态,200=成功,400=失败
     * @return  string msg - 信息
     */
    public function createConsult($param)
    {
        $clientId = get_client_id();

        $this->startTrans();
        try{
            $this->create([
                'client_id' => $clientId,
                'matter' => $param['matter'],
                'contact' => $param['contact'],
                'company' => $param['company'] ?? '',
                'phone' => $param['phone'] ?? '',
                'email' => $param['email'] ?? '',
                'create_time' => time(),
            ]);
            
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('create_fail')];
        }

        return ['status'=>200, 'msg'=>lang('create_success')];
    }


}