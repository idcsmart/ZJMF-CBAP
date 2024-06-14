<?php
namespace app\admin\model;

use think\Model;
use app\common\model\SelfDefinedFieldModel;
use addon\client_custom_field\model\ClientCustomFieldModel;

/**
 * @title 管理员字段设置模型
 * @desc  管理员字段设置模型
 * @use app\admin\model\AdminFieldModel
 */
class AdminFieldModel extends Model
{
    protected $name = 'admin_field';

    protected $pk = 'id';

    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'view'            => 'string',
        'admin_id'        => 'int',
        'select_field'    => 'string',
        'create_time'     => 'int',
        'update_time'     => 'int',
    ];

    // 所有字段
    protected $setting = [
        'client' => [
            [
                'name'  => 'admin_field_client_info',
                'field' => [
                    [
                        'key'   => 'id',
                        'name'  => 'admin_field_client_id',
                    ],
                    [
                        'key'   => 'username_company',
                        'name'  => 'admin_field_client_username_and_company',
                    ],
                    [
                        'key'   => 'certification',
                        'name'  => 'admin_field_client_certification',
                        'module'=> [ // 仅记录,方便看是哪个插件的
                            'type'  => 'addon',
                            'name'  => 'IdcsmartCertification',
                        ],
                    ],
                    [
                        'key'   => 'phone',
                        'name'  => 'admin_field_client_phone',
                    ],
                    [
                        'key'   => 'email',
                        'name'  => 'admin_field_client_email',
                    ],
                    [
                        'key'   => 'client_status',
                        'name'  => 'admin_field_client_status',
                    ],
                    [
                        'key'   => 'client_level',
                        'name'  => 'admin_field_client_level',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartClientLevel',
                        ],
                    ],
                    [
                        'key'   => 'reg_time',
                        'name'  => 'admin_field_client_reg_time',
                    ],
                    [
                        'key'   => 'country',
                        'name'  => 'admin_field_country',
                    ],
                    [
                        'key'   => 'address',
                        'name'  => 'admin_field_address',
                    ],
                    [
                        'key'   => 'language',
                        'name'  => 'admin_field_language',
                    ],
                    [
                        'key'   => 'notes',
                        'name'  => 'admin_field_notes',
                    ],
                ], 
            ],
            [
                'name'  => 'admin_field_host_about',
                'field' => [
                    [
                        'key'   => 'host_active_num_host_num',
                        'name'  => 'admin_field_host_active_num_and_host_num',
                    ],
                    [
                        'key'   => 'client_credit',
                        'name'  => 'admin_field_client_credit',
                    ],
                    [
                        'key'   => 'cost_price',
                        'name'  => 'admin_field_cost_price',
                    ],
                    [
                        'key'   => 'refund_price',
                        'name'  => 'admin_field_refund_price',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartRefund',
                        ],
                    ],
                    [
                        'key'   => 'withdraw_price',
                        'name'  => 'admin_field_withdraw_price',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartWithdraw',
                        ],
                    ],
                ],
            ],
        ],
        'order' => [
            [
                'name'  => 'admin_field_base_info',
                'field' => [
                    [
                        'key'   => 'id',
                        'name'  => 'admin_field_order_id',
                    ],
                    [
                        'key'   => 'username_company',
                        'name'  => 'admin_field_client_and_company',
                    ],
                    [
                        'key'   => 'product_name',
                        'name'  => 'admin_field_product_name',
                    ],
                    [
                        'key'   => 'order_amount',
                        'name'  => 'admin_field_order_amount',
                    ],
                    [
                        'key'   => 'gateway',
                        'name'  => 'admin_field_gateway',
                    ],
                    [
                        'key'   => 'order_time',
                        'name'  => 'admin_field_order_create_time',
                    ],
                    [
                        'key'   => 'order_status',
                        'name'  => 'admin_field_order_status',
                    ],
                    [
                        'key'   => 'order_type',
                        'name'  => 'admin_field_order_type',
                    ],
                    [
                        'key'   => 'order_use_credit',
                        'name'  => 'admin_field_order_use_credit',
                    ],
                    [
                        'key'   => 'order_refund_amount',
                        'name'  => 'admin_field_order_refund_amount',
                    ],
                ],
            ],
            [
                'name'  => 'admin_field_client_about',
                'field' => [
                    [
                        'key'   => 'client_id',
                        'name'  => 'admin_field_client_id',
                    ],
                    [
                        'key'   => 'certification',
                        'name'  => 'admin_field_client_certification',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartCertification',
                        ],
                    ],
                    [
                        'key'   => 'phone',
                        'name'  => 'admin_field_client_phone',
                    ],
                    [
                        'key'   => 'email',
                        'name'  => 'admin_field_client_email',
                    ],
                    [
                        'key'   => 'client_status',
                        'name'  => 'admin_field_order_client_status',
                    ],
                    [
                        'key'   => 'client_level',
                        'name'  => 'admin_field_client_level',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartClientLevel',
                        ],
                    ],
                    [
                        'key'   => 'reg_time',
                        'name'  => 'admin_field_client_reg_time',
                    ],
                    [
                        'key'   => 'country',
                        'name'  => 'admin_field_country',
                    ],
                    [
                        'key'   => 'address',
                        'name'  => 'admin_field_address',
                    ],
                    [
                        'key'   => 'language',
                        'name'  => 'admin_field_language',
                    ],
                    [
                        'key'   => 'notes',
                        'name'  => 'admin_field_notes',
                    ],
                ],
            ],
        ],
        'host'  => [
            [
                'name'  => 'admin_field_base_info',
                'field' => [
                    [
                        'key'   => 'id',
                        'name'  => 'admin_field_host_id',
                    ],
                    [
                        'key'   => 'product_name_status',
                        'name'  => 'admin_field_product_name_and_host_status',
                    ],
                    [
                        'key'   => 'username_company',
                        'name'  => 'admin_field_client_and_company',
                    ],
                    [
                        'key'   => 'ip',
                        'name'  => 'IP',
                    ],
                    [
                        'key'   => 'host_name',
                        'name'  => 'admin_field_host_name',
                    ],
                    [
                        'key'   => 'renew_amount_cycle',
                        'name'  => 'admin_field_host_renew_amount_cycle',
                    ],
                    [
                        'key'   => 'due_time',
                        'name'  => 'admin_field_due_time',
                    ],
                    [
                        'key'   => 'server_name',
                        'name'  => 'admin_field_product_interface',
                    ],
                    [
                        'key'   => 'admin_notes',
                        'name'  => 'admin_field_admin_notes',
                    ],
                    [
                        'key'   => 'first_payment_amount',
                        'name'  => 'admin_field_first_payment_amount',
                    ],
                    [
                        'key'   => 'billing_cycle_name',
                        'name'  => 'admin_field_billing_cycle_name',
                    ],
                    [
                        'key'   => 'base_price',
                        'name'  => 'admin_field_base_price',
                    ],
                    [
                        'key'   => 'billing_cycle',
                        'name'  => 'admin_field_billing_cycle',
                    ],
                    [
                        'key'   => 'active_time',
                        'name'  => 'admin_field_active_time',
                    ],
                ],
            ],
            [
                'name'  => 'admin_field_client_about',
                'field' => [
                    [
                        'key'   => 'client_id',
                        'name'  => 'admin_field_client_id',
                    ],
                    [
                        'key'   => 'certification',
                        'name'  => 'admin_field_client_certification',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartCertification',
                        ],
                    ],
                    [
                        'key'   => 'phone',
                        'name'  => 'admin_field_client_phone',
                    ],
                    [
                        'key'   => 'email',
                        'name'  => 'admin_field_client_email',
                    ],
                    [
                        'key'   => 'client_status',
                        'name'  => 'admin_field_order_client_status',
                    ],
                    [
                        'key'   => 'client_level',
                        'name'  => 'admin_field_client_level',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartClientLevel',
                        ],
                    ],
                    [
                        'key'   => 'reg_time',
                        'name'  => 'admin_field_client_reg_time',
                    ],
                    [
                        'key'   => 'country',
                        'name'  => 'admin_field_country',
                    ],
                    [
                        'key'   => 'address',
                        'name'  => 'admin_field_address',
                    ],
                    [
                        'key'   => 'language',
                        'name'  => 'admin_field_language',
                    ],
                    [
                        'key'   => 'notes',
                        'name'  => 'admin_field_notes',
                    ],
                ],
            ],
        ],
        'transaction'   => [
            [
                'name'  => 'admin_field_base_info',
                'field' => [
                    [
                        'key'   => 'id',
                        'name'  => 'admin_field_transaction_id',
                    ],
                    [
                        'key'   => 'amount',
                        'name'  => 'admin_field_transaction_amount',
                    ],
                    [
                        'key'   => 'gateway',
                        'name'  => 'admin_field_gateway',
                    ],
                    [
                        'key'   => 'username_company',
                        'name'  => 'admin_field_client_and_company',
                    ],
                    [
                        'key'   => 'transaction_number',
                        'name'  => 'admin_field_transaction_number',
                    ],
                    [
                        'key'   => 'order_id',
                        'name'  => 'admin_field_link_order_id',
                    ],
                    [
                        'key'   => 'order_type',
                        'name'  => 'admin_field_order_type',
                    ],
                    [
                        'key'   => 'transaction_time',
                        'name'  => 'admin_field_transaction_time',
                    ],
                ],
            ],
            [
                'name'  => 'admin_field_client_about',
                'field' => [
                    [
                        'key'   => 'client_id',
                        'name'  => 'admin_field_client_id',
                    ],
                    [
                        'key'   => 'certification',
                        'name'  => 'admin_field_client_certification',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartCertification',
                        ],
                    ],
                    [
                        'key'   => 'phone',
                        'name'  => 'admin_field_client_phone',
                    ],
                    [
                        'key'   => 'email',
                        'name'  => 'admin_field_client_email',
                    ],
                    [
                        'key'   => 'client_status',
                        'name'  => 'admin_field_order_client_status',
                    ],
                    [
                        'key'   => 'client_level',
                        'name'  => 'admin_field_client_level',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartClientLevel',
                        ],
                    ],
                    [
                        'key'   => 'reg_time',
                        'name'  => 'admin_field_client_reg_time',
                    ],
                    [
                        'key'   => 'country',
                        'name'  => 'admin_field_country',
                    ],
                    [
                        'key'   => 'address',
                        'name'  => 'admin_field_address',
                    ],
                    [
                        'key'   => 'language',
                        'name'  => 'admin_field_language',
                    ],
                    [
                        'key'   => 'notes',
                        'name'  => 'admin_field_notes',
                    ],
                ],
            ],
        ],
    ];

    // 当前已激活插件
    protected $plugin = [];

    /**
     * 时间 2024-05-08
     * @title 获取字段设置可选字段
     * @desc  获取字段设置可选字段
     * @author hh
     * @version v1
     * @param   string view - 页面标识(client=用户管理,order=订单管理,host=产品管理,transaction=交易流水) require
     * @return  string field[].name - 字段分组名称
     * @return  string field[].field[].key - 字段标识
     * @return  string field[].field[].name - 字段名称
     */
    public function enableField($view)
    {
        $this->getActivePlugin();
        $field = $this->setting[$view] ?? [];
        $lang = lang();

        // 根据插件来显示对应的选项
        foreach($field as $k=>$v){
            $field[$k]['name'] = $lang[ $v['name'] ] ?? $v['name'];
            foreach($v['field'] as $kk=>$vv){
                $field[$k]['field'][$kk]['name'] = $lang[ $vv['name'] ] ?? $vv['name'];
                if(isset($vv['module'])){
                    if( !isset($this->plugin[ $vv['module']['name'] ]) ){
                        unset($field[$k]['field'][$kk]);
                    }else{
                        // 删除插件记录的值
                        unset($field[$k]['field'][$kk]['module']);
                    }
                }
            }
            $field[$k]['field'] = array_values($field[$k]['field']);
        }
        // 追加商品自定义字段
        if(in_array($view, ['host'])){
            $selfDefinedField = SelfDefinedFieldModel::field('id `key`,field_name name')
                    ->withAttr('key', function($val){
                        return 'self_defined_field_'.$val;
                    })
                    ->where('show_admin_host_list', 1)
                    ->order('relid,order', 'asc')
                    ->select()
                    ->toArray();
            if(!empty($selfDefinedField)){
                $field[] = [
                    'name'  => $lang['admin_field_product_custom_field'] ?? 'admin_field_product_custom_field',
                    'field' => $selfDefinedField,
                ];
            }
        }
        // 追加用户自定义字段
        if(in_array($view, ['client','order','host','transaction'])){
            if(isset($this->plugin['ClientCustomField'])){
                $clientCustomField = $this->getClientCustomField();
                if(!empty($clientCustomField)){
                    $field[] = $clientCustomField;
                }
            }
        }
        return ['field'=>$field];
    }

    /**
     * 时间 2024-05-13
     * @title 获取用户自定义字段
     * @desc  获取用户自定义字段,需要先判断是否存在启用插件
     * @author hh
     * @version v1
     */
    protected function getClientCustomField()
    {
        $data = [];
        $field = ClientCustomFieldModel::field('id `key`,name')
                ->where('status', '1')
                ->withAttr('key', function($val){
                    return 'addon_client_custom_field_'.$val;
                })
                ->order('order', 'asc')
                ->select()
                ->toArray();
        if(!empty($field)){
            $data = [
                'name'  => lang('admin_field_client_custom_field'),
                'field' => $field,
            ];
        }
        return $data;
    }

    /**
     * 时间 2024-05-14
     * @title 获取列表默认字段标识
     * @desc  获取列表默认字段标识
     * @author hh
     * @version v1
     * @param   string view - 页面标识(client=用户管理,order=订单管理,host=产品管理,transaction=交易流水)
     * @return  array
     */
    public function adminFieldDefault($view)
    {
        $this->getActivePlugin();
        $data = [
            'client'        => ['id','username_company','certification','phone','email','host_active_num_host_num','client_status'],
            'order'         => ['id','username_company','product_name','order_amount','gateway','order_time','order_status'],
            'host'          => ['id','product_name_status','username_company','ip','renew_amount_cycle','due_time'],
            'transaction'   => ['id','amount','gateway','username_company','transaction_number','order_id','order_type','transaction_time'],
        ];
        if($view == 'client'){
            if(!isset($this->plugin['IdcsmartCertification'])){
                unset($data['client'][2]);
                $data['client'] = array_values($data['client']);
            }
        }
        return $data[ $view ] ?? ['id'];
    }

    /**
     * 时间 2024-05-14
     * @title 获取字段设置
     * @desc  获取字段设置
     * @author hh
     * @version v1
     * @param   string param.view - 页面标识(client=用户管理,order=订单管理,host=产品管理,transaction=交易流水) require
     * @return  string field[].name - 字段分组名称
     * @return  string field[].field[].key - 字段标识
     * @return  string field[].field[].name - 字段名称
     * @return  array select_field - 当前选定字段标识
     */
    public function adminFieldIndex($param)
    {
        $param['view'] = $param['view'] ?? '';
        $adminId = get_admin_id();
        $field = $this->enableField($param['view']);

        $selectField = $this->where('admin_id', $adminId)->where('view', $param['view'])->value('select_field');
        if(!empty($selectField)){
            $selectField = explode(',', $selectField);
            // 去掉不在可选字段的字段
            $enableField = [];
            foreach($field['field'] as $v){
                $enableField = array_merge($enableField, $v['field']);
            }
            $enableField = array_column($enableField, 'key');
            $selectField = array_values(array_intersect($selectField, $enableField));
        }else{
            $selectField = $this->adminFieldDefault($param['view']);
        }

        $result = [
            'field'         => $field['field'],
            'select_field'  => $selectField,
        ];
        return $result;
    }

    /**
     * 时间 2024-05-14
     * @title 保存字段设置
     * @desc  保存字段设置
     * @author hh
     * @version v1
     * @param   string param.view - 页面标识(client=用户管理,order=订单管理,host=产品管理,transaction=交易流水) require
     * @param   array  param.select_field - 选定字段标识 require
     * @return  int status - 状态,200=成功,400=失败
     * @return  string msg - 信息
     */
    public function adminFieldSave($param)
    {
        $adminId = get_admin_id();
        $currentField = $this->adminFieldIndex($param);

        $field = [];
        foreach($currentField['field'] as $v){
            $field = array_merge($field, $v['field']);
        }
        $field = array_column($field, 'name', 'key');

        // 直接排除不在可选字段中的值
        $selectField = [];
        foreach($param['select_field'] as $v){
            if(is_string($v) && isset($field[$v])){
                $selectField[] = $v;
            }
        }

        $adminField = $this->where('admin_id', $adminId)->where('view', $param['view'])->find();
        if(!empty($adminField)){
            $this->where('id', $adminField['id'])->update([
                'select_field'  => implode(',', $selectField),
                'update_time'   => time(),
            ]);
        }else{
            $this->create([
                'view'          => $param['view'],
                'admin_id'      => $adminId,
                'select_field'  => implode(',', $selectField),
                'create_time'   => time(),
            ]);
        }

        $result = [
            'status' => 200,
            'msg'    => lang('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2024-05-21
     * @title 获取已激活插件
     * @desc  获取已激活插件
     * @author hh
     * @version v1
     * @param   array
     */
    protected function getActivePlugin()
    {
        if(empty($this->plugin)){
            // 获取可用插件
            $PluginModel = new PluginModel();
            $activePluginList = $PluginModel->activePluginList();
            $this->plugin = array_column($activePluginList['list'], 'id', 'name');
        }
        return $this->plugin;
    }

}