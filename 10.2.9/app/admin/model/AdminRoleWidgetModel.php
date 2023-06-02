<?php
namespace app\admin\model;

use think\Model;
use think\db\Query;
use app\common\logic\WidgetLogic;

/**
 * @title 管理员分组挂件模型
 * @desc 管理员分组挂件模型
 * @use app\admin\model\AdminRoleWidgetModel
 */
class AdminRoleWidgetModel extends Model
{
    protected $name = 'admin_role_widget';

    protected $pk = 'admin_role_id';

    // 设置字段信息
    protected $schema = [
        'admin_role_id'       => 'int',
        'widget'              => 'string',
    ];

    /**
     * 时间 2023-05-04
     * @title 获取管理组挂件权限
     * @desc  获取管理组挂件权限
     * @author hh
     * @version v1
     * @param   int $adminRoleId - 管理组ID require
     * @return  array
     */
    public function getAllWidget($adminRoleId)
    {
        $WidgetLogic = new WidgetLogic();
        $allWidget = $WidgetLogic->getAllWidgetId();
        $adminRoleWidget = $this->where('admin_role_id', $adminRoleId)->value('widget') ?? '';
        $adminRoleWidget = explode(',', $adminRoleWidget) ?? [];
        return array_values(array_intersect($allWidget, $adminRoleWidget));
    }

    /**
     * 时间 2023-05-04
     * @title 保存管理员组挂件权限
     * @desc  保存管理员组挂件权限
     * @author hh
     * @version v1
     * @param   int param.admin_role_id - 管理员分组ID
     * @param   array param.widget - 挂件标识数组
     */
    public function adminRoleWidgetSave($param)
    {
        $WidgetLogic = new WidgetLogic();
        $allWidget = $WidgetLogic->getAllWidgetId();

        $param['widget'] = array_intersect(array_unique($param['widget']), $allWidget);

        $exist = $this->where('admin_role_id', $param['admin_role_id'])->find();
        if(empty($exist)){
            $this->create([
                'admin_role_id' => $param['admin_role_id'],
                'widget'        => implode(',', $param['widget']),
            ]);
        }else{
            $this->update([
                // 'admin_role_id' => $param['admin_role_id'],
                'widget'        => implode(',', $param['widget']),
            ], ['admin_role_id'=>$param['admin_role_id']]);
        }

        $result = [
            'status' => 200,
            'msg'    => lang('update_success'),
        ];
        return $result;
    }


}