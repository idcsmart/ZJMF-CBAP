<?php
namespace app\admin\model;

use think\Model;
use think\db\Query;
use app\common\logic\WidgetLogic;

/**
 * @title 管理员挂件模型
 * @desc 管理员挂件模型
 * @use app\admin\model\AdminWidgetModel
 */
class AdminWidgetModel extends Model
{
    protected $name = 'admin_widget';

    protected $pk = 'admin_id';

    // 设置字段信息
    protected $schema = [
        'admin_id'       => 'int',
        'widget'         => 'string',
    ];

    /**
     * 时间 2023-05-04
     * @title 后台首页挂件
     * @desc  后台首页挂件
     * @author hh
     * @version v1
     * @return  string widget[].id - 挂件标识
     * @return  string widget[].title - 挂件名称
     * @return  int widget[].columns - 挂件列数
     * @return  array show_widget - 显示的挂件标识
     */
    public function adminWidgetIndex()
    {
    	$adminId = get_admin_id();
    	$adminRoleId = AdminRoleLinkModel::where('admin_id', $adminId)->value('admin_role_id');

        $adminRoleWidget = AdminRoleWidgetModel::where('admin_role_id', $adminRoleId)->value('widget') ?? '';
        $adminRoleWidget = explode(',', $adminRoleWidget) ?? [];
        $adminRoleWidget = array_flip($adminRoleWidget);

    	$WidgetLogic = new WidgetLogic();
    	$widgets = $WidgetLogic->getAllWidgets();

    	$data = [];

    	foreach($widgets as $widget){
            if(!isset($adminRoleWidget[$widget->getId()])){
                continue;
            }
    		$data[] = [
    			'id'			=> $widget->getId(),
    			'title'			=> $widget->getTitle(),
    			'columns'		=> $widget->getColumns(),
    		];
    	}
        $widget = array_column($data, 'id');

    	// 获取当前显示的
    	$adminWidget = $this->where('admin_id', $adminId)->value('widget');
    	if(!is_null($adminWidget)){
    		$adminWidget = explode(',', $adminWidget);
    		$adminWidget = array_values(array_intersect($adminWidget, $widget));
    	}else{
    		$adminWidget = $widget;
    	}

    	$data  = [
    		'widget' 		=> $data,
    		'show_widget'	=> $adminWidget
    	];
    	return $data;
    }


    /**
     * 时间 2023-05-04
     * @title 保存管理员挂件排序
     * @desc  保存管理员挂件排序
     * @author hh
     * @version v1
     * @param   array widget_arr - 挂件标识 require
     */
    public function adminWidgetSaveOrder($param)
    {
    	$adminId = get_admin_id();
    	$adminRoleId = AdminRoleLinkModel::where('admin_id', $adminId)->value('admin_role_id');

    	// 获取当前分组所有挂件
    	$AdminRoleWidgetModel = new AdminRoleWidgetModel();
    	$widget = $AdminRoleWidgetModel->getAllWidget($adminRoleId);

    	$param['widget_arr'] = array_intersect(array_unique($param['widget_arr']), $widget);
    	$param['widget_arr'] = array_values($param['widget_arr']);

    	$exist = $this->where('admin_id', $adminId)->find();
    	if(empty($exist)){
    		$this->create([
	    		'admin_id'	=> $adminId,
	    		'widget'	=> implode(',', $param['widget_arr']),
	    	]);
    	}else{
    		$this->update([
	    		'widget'	=> implode(',', $param['widget_arr']),
	    	], ['admin_id'=>$adminId]);
    	}

    	$result = [
    		'status' => 200,
    		'msg'	 => lang('update_success'),
    	];
    	return $result;
    }

    /**
     * 时间 2023-05-05
     * @title 显示/隐藏挂件
     * @desc  显示/隐藏挂件
     * @author hh
     * @version v1
     * @param   string param.widget - 挂件标识 require
     * @param   int param.status - 状态(0=隐藏,1=显示) require
     */
    public function toggleWidget($param)
    {
        $adminId = get_admin_id();
        $adminRoleId = AdminRoleLinkModel::where('admin_id', $adminId)->value('admin_role_id');

        // 获取当前分组所有挂件
        $AdminRoleWidgetModel = new AdminRoleWidgetModel();
        $widget = $AdminRoleWidgetModel->getAllWidget($adminRoleId);

        if(!in_array($param['widget'], $widget)){
            return ['status'=>400, 'msg'=>lang('挂件不存在') ];
        }

        $exist = $this->where('admin_id', $adminId)->find();
        if(!empty($exist)){
            $widget = explode(',', $exist['widget']);
        }
        if($param['status'] == 1){
            if(!in_array($param['widget'], $widget)){
                $widget[] = $param['widget'];
            }
        }else{
            $widget = array_diff($widget, [$param['widget']]);
        }

        if(empty($exist)){
            $this->create([
                'admin_id'  => $adminId,
                'widget'    => implode(',', $widget),
            ]);
        }else{
            $this->update([
                'widget'    => implode(',', $widget),
            ], ['admin_id'=>$adminId]);
        }

        $result = [
            'status' => 200,
            'msg'    => lang('update_success'),
        ];
        return $result;
    }





}