<?php
namespace app\common\model;

use think\Model;

/**
 * @title 自定义字段模型
 * @desc 自定义字段模型
 * @use app\common\model\SelfDefinedFieldModel
 */
class SelfDefinedFieldModel extends Model
{
    protected $name = 'self_defined_field';

    // 设置字段信息
    protected $schema = [
        'id'                        => 'int',
        'type'                      => 'string',
        'relid'                     => 'int',
        'field_name'                => 'string',
        'field_type'                => 'string',
        'description'               => 'string',
        'regexpr'                   => 'string',
        'field_option'              => 'string',
        'order'                     => 'int',
        'is_required'               => 'int',
        'show_order_page'           => 'int',
        'show_order_detail'         => 'int',
        'show_client_host_detail'   => 'int',
        'show_admin_host_detail'    => 'int',
        'show_client_host_list'     => 'int',
        'show_admin_host_list'      => 'int',
        'upstream_id'               => 'int',
        'create_time'               => 'int',
        'update_time'               => 'int',
    ];

    // 缓存自定义字段
    protected $productField = [];

    /**
     * 时间 2023-12-29
     * @title 添加自定义字段
     * @desc  添加自定义字段
     * @author hh
     * @version v1
     * @param   string param.type - 类型(product=商品) require
     * @param   int param.relid - 关联ID(商品ID) require
     * @param   string param.field_name - 字段名称 require
     * @param   int param.is_required - 是否必填(0=否,1=是) require
     * @param   string param.field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区) require
     * @param   string param.description - 字段描述
     * @param   string param.regexpr - 验证规则
     * @param   string param.field_option - 下拉选项 field_type=dropdown,require
     * @param   int param.show_order_page - 订单页可见(0=否,1=是) require
     * @param   int param.show_order_detail - 订单详情可见(0=否,1=是) require
     * @param   int param.show_client_host_detail - 前台产品详情可见(0=否,1=是) require
     * @param   int param.show_admin_host_detail - 后台产品详情可见(0=否,1=是) require
     * @param   int param.show_client_host_list - 会员中心列表显示(0=否,1=是) require
     * @param   int param.show_admin_host_list - 后台产品列表显示(0=否,1=是) require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.id - 自定义字段ID
     */
    public function selfDefinedFieldCreate($param)
    {
        if($param['type'] == 'product'){
            $product = ProductModel::find($param['relid']);
            if(empty($product)){
                return ['status'=>400, 'msg'=>lang('product_not_found')];
            }
            // 是否是代理商品,
            $isUpstream = UpstreamProductModel::where('product_id', $product->id)->value('upstream_product_id');
            if($isUpstream){
                return ['status'=>400, 'msg'=>lang('self_defined_field_upstream_product_cannot_create')];
            }
        }

        $param['create_time'] = time();
        $param['description'] = $param['description'] ?? '';
        $param['regexpr'] = $param['regexpr'] ?? '';
        if(!in_array($param['field_type'], ['dropdown'])){
            $param['field_option'] = '';
        }
        // 链接,密码不能再订单详情显示
        if(in_array($param['field_type'], ['link','password'])){
            $param['show_order_detail'] = 0;
        }

        $selfDefinedField = $this->create($param, ['type','relid','field_name','is_required','field_type','description','regexpr','field_option','show_order_page','show_order_detail','show_client_host_detail','show_admin_host_detail','show_client_host_list','show_admin_host_list','create_time']);

        if($param['type'] == 'product'){
            $description = lang('log_product_self_defined_field_create_success', [
                '{product}'     => 'product#'.$product->id.'#'.$product->name,
                '{field_name}'  => $param['field_name'],
            ]);
            active_log($description, 'product', $product->id);
        }
        // 置顶
        $this->where('relid', $param['relid'])->where('type', $param['type'])->where('order', '>=', 0)->inc('order', 1)->update();

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => [
                'id' => (int)$selfDefinedField->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2023-12-29
     * @title 自定义字段修改
     * @desc  自定义字段修改
     * @author hh
     * @version v1
     * @param   int param.id - 自定义字段ID require
     * @param   string param.field_name - 字段名称 require
     * @param   int param.is_required - 是否必填(0=否,1=是) require
     * @param   string param.field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区) require
     * @param   string param.description - 字段描述
     * @param   string param.regexpr - 验证规则
     * @param   string param.field_option - 下拉选项 field_type=dropdown,require
     * @param   int param.show_order_page - 订单页可见(0=否,1=是) require
     * @param   int param.show_order_detail - 订单详情可见(0=否,1=是) require
     * @param   int param.show_client_host_detail - 前台产品详情可见(0=否,1=是) require
     * @param   int param.show_admin_host_detail - 后台产品详情可见(0=否,1=是) require
     * @param   int param.show_client_host_list - 会员中心列表显示(0=否,1=是) require
     * @param   int param.show_admin_host_list - 后台产品列表显示(0=否,1=是) require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function selfDefinedFieldUpdate($param)
    {
        $selfDefinedField = $this->find($param['id']);
        if(empty($selfDefinedField)){
            return ['status'=>400, 'msg'=>lang('self_defined_field_not_found')];
        }
        if($selfDefinedField['upstream_id'] > 0){
            return ['status'=>400, 'msg'=>lang('upstream_self_defined_field_cannot_update')];
        }
        if($selfDefinedField['type'] == 'product'){
            $product = ProductModel::find($selfDefinedField['relid']);
            if(empty($product)){
                return ['status'=>400, 'msg'=>lang('self_defined_field_not_found')];
            }
        }

        if(!in_array($param['field_type'], ['dropdown'])){
            $param['field_option'] = '';
        }
        // 链接,密码不能再订单详情显示
        if(in_array($param['field_type'], ['link','password'])){
            $param['show_order_detail'] = 0;
        }
        $param['update_time'] = time();

        $this->update($param, ['id'=>$selfDefinedField['id'] ], ['field_name','is_required','field_type','description','regexpr','field_option','show_order_page','show_order_detail','show_client_host_detail','show_admin_host_detail','show_client_host_list','show_admin_host_list','update_time']);

        $description = [];

        $desc = [
            'field_name'                => lang('self_defined_field_field_name'),
            'is_required'               => lang('self_defined_field_is_required'),
            'field_type'                => lang('self_defined_field_field_type'),
            'description'               => lang('self_defined_field_description'),
            'regexpr'                   => lang('self_defined_field_regexpr'),
            'field_option'              => lang('self_defined_field_field_option'),
            'show_order_page'           => lang('self_defined_field_show_order_page'),
            'show_order_detail'         => lang('self_defined_field_show_order_detail'),
            'show_client_host_detail'   => lang('self_defined_field_show_client_host_detail'),
            'show_admin_host_detail'    => lang('self_defined_field_show_admin_host_detail'),
            'show_client_host_list'     => lang('self_defined_field_show_client_host_list'),
            'show_admin_host_list'      => lang('self_defined_field_show_admin_host_list'),
        ];

        $fieldType = [
            'text'      => lang('self_defined_field_type_text'),
            'link'      => lang('self_defined_field_type_link'),
            'password'  => lang('self_defined_field_type_password'),
            'dropdown'  => lang('self_defined_field_type_dropdown'),
            'tickbox'   => lang('self_defined_field_type_tickbox'),
            'textarea'  => lang('self_defined_field_type_textarea'),
        ];

        $tickbox = [
            lang('self_defined_field_tickbox_no_check'),
            lang('self_defined_field_tickbox_check'),
        ];

        foreach($desc as $k=>$v){
            if(isset($param[$k]) && $selfDefinedField[$k] != $param[$k]){
                $old = $selfDefinedField[$k];
                $new = $param[$k];

                if(in_array($k, ['is_required','show_order_page','show_order_detail','show_client_host_detail','show_admin_host_detail','show_client_host_list','show_admin_host_list'])){
                    $old = $tickbox[ $old ];
                    $new = $tickbox[ $new ];
                }else if($k == 'field_type'){
                    $old = $fieldType[ $old ];
                    $new = $fieldType[ $new ];
                }

                $description[] = lang('log_admin_update_description', [
                    '{field}'   => $v,
                    '{old}'     => $old,
                    '{new}'     => $new,
                ]);
            }
        }
        
        if(!empty($description)){
            if($selfDefinedField['type'] == 'product'){
                $description = lang('log_product_self_defined_field_update_success', [
                    '{product}' => 'product#'.$product->id.'#'.$product->name,
                    '{detail}'  => implode(',', $description),
                ]);
                active_log($description, 'product', $product->id);
            }
        }

        $result = [
            'status' => 200,
            'msg'    => lang('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-12-29
     * @title 自定义字段列表
     * @desc  自定义字段列表
     * @author hh
     * @version v1
     * @param   string param.type product 类型(product=商品)
     * @param   int param.relid - 关联ID(商品ID) require
     * @return  int list[].id - 自定义字段ID
     * @return  string list[].field_name - 字段名称
     * @return  int list[].is_required - 是否必填(0=否,1=是)
     * @return  string list[].field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区)
     * @return  string list[].description - 字段描述
     * @return  string list[].regexpr - 验证规则
     * @return  string list[].field_option - 下拉选项
     * @return  int list[].show_order_page - 订单页可见(0=否,1=是)
     * @return  int list[].show_order_detail - 订单详情可见(0=否,1=是)
     * @return  int list[].show_client_host_detail - 前台产品详情可见(0=否,1=是)
     * @return  int list[].show_admin_host_detail - 后台产品详情可见(0=否,1=是)
     * @return  int list[].show_client_host_list - 会员中心列表显示(0=否,1=是)
     * @return  int list[].show_admin_host_list - 后台产品列表显示(0=否,1=是)
     * @return  int list[].upstream_id - 上游ID(大于0不能修改删除)
     * @return  int count - 总条数
     */
    public function selfDefinedFieldList($param)
    {
        $param['type'] = $param['type'] ?? 'product';

        $where = [];
        $where[] = ['relid', '=', $param['relid']];
        $where[] = ['type', '=', $param['type']];

        $list = $this
                ->field('id,field_name,field_type,description,regexpr,field_option,is_required,show_order_page,show_order_detail,show_client_host_detail,show_admin_host_detail,show_client_host_list,show_admin_host_list,upstream_id')
                ->where($where)
                ->order('order,id', 'asc')
                ->select()
                ->toArray();

        $count = $this->where($where)->count();

        return ['list'=>$list, 'count'=>$count];
    }

    /**
     * 时间 2023-12-29
     * @title 删除自定义字段
     * @desc  删除自定义字段
     * @author hh
     * @version v1
     * @param   int param.id - 自定义字段ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function selfDefinedFieldDelete($param)
    {
        $selfDefinedField = $this->find($param['id']);
        if(empty($selfDefinedField)){
            return ['status'=>400, 'msg'=>lang('self_defined_field_not_found')];
        }
        if($selfDefinedField['upstream_id'] > 0){
            return ['status'=>400, 'msg'=>lang('upstream_self_defined_field_cannot_delete')];
        }

        $this->startTrans();
        try{
            $selfDefinedField->delete();
            SelfDefinedFieldValueModel::where('self_defined_field_id', $selfDefinedField->id)->delete();

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }

        if($selfDefinedField['type'] == 'product'){
            $productName = ProductModel::where('id', $selfDefinedField->id)->value('name');

            $description = lang('log_product_self_defined_field_delete_success', [
                '{product}'     => 'product#'.$selfDefinedField->relid.'#'.$productName.'#',
                '{field_name}'  => $selfDefinedField['field_name'],
            ]);

            active_log($description, 'product', $selfDefinedField->relid);
        }

        $result = [
            'status' => 200,
            'msg'    => lang('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2024-01-02
     * @title 拖动排序
     * @desc 拖动排序
     * @author hh
     * @version v1
     * @param   int param.prev_id - 前一个自定义字段ID(0=表示置顶) require
     * @param   int param.id - 当前自定义字段ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function dragToSort($param)
    {
        $selfDefinedField = $this->find($param['id']);
        if(empty($selfDefinedField)){
            return ['status'=>400, 'msg'=>lang_plugins('self_defined_field_not_found')];
        }
        if($selfDefinedField['upstream_id'] > 0){
            return ['status'=>400, 'msg'=>lang_plugins('log_product_self_defined_field_update_success')];
        }
        if($param['prev_id'] == 0){
            $preOrder = -1;
            $order = 0;
        }else{
            $preSelfDefinedField = $this->find($param['prev_id']);
            if(empty($preSelfDefinedField)){
                return ['status'=>400, 'msg'=>lang_plugins('self_defined_field_not_found')];
            }
            $preOrder = $preSelfDefinedField['order'];
            $order = $preSelfDefinedField['order']+1;
        }
        $this->where('relid', $selfDefinedField['relid'])->where('type', $selfDefinedField['type'])->where('order', '>=', $preOrder)->where('id', '>', $param['prev_id'])->inc('order', 2)->update();
        $this->where('id', $param['id'])->update(['order'=>$order]);

        return ['status'=>200, 'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2024-01-02
     * @title 删除自定义字段
     * @desc  删除自定义字段
     * @author hh
     * @version v1
     * @param   string type product 类型(product=商品) require
     * @param   int relid - 关联ID(商品ID) require
     */
    public function withDelete($type, $relid)
    {
        $selfDefinedFieldId = $this->where('relid', $relid)->where('type', $type)->column('id');
        if(!empty($selfDefinedFieldId)){
            $this->whereIn('id', $selfDefinedFieldId)->delete();
            SelfDefinedFieldValueModel::whereIn('self_defined_field_id', $selfDefinedFieldId)->delete();
        }
    }

    /**
     * 时间 2024-01-02
     * @title 订单页自定义字段
     * @desc  订单页自定义字段
     * @author hh
     * @version v1
     * @param   int param.id - 商品ID require
     * @param   bool param.need_upstream_id - 是否需要上游ID
     * @return  int data[].id - 自定义字段ID
     * @return  string data[].field_name - 字段名称
     * @return  string data[].field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区)
     * @return  string data[].description - 字段描述
     * @return  string data[].regexpr - 验证规则
     * @return  string data[].field_option - 下拉选项
     * @return  int data[].is_required - 是否必填(0=否,1=是)
     * @return  int data[].show_client_host_list - 会员中心列表显示(0=否,1=是)
     * @return  int data[].upstream_id - 上游ID(need_upstream_id=true返回)
     */
    public function showOrderPageField($param)
    {
        if(isset($param['need_upstream_id']) && $param['need_upstream_id']){
            $field = 'id,field_name,field_type,description,regexpr,field_option,is_required,show_client_host_list,upstream_id';
        }else{
            $field = 'id,field_name,field_type,description,regexpr,field_option,is_required,show_client_host_list';
        }
        $data = $this
                ->field($field)
                ->where('relid', $param['id'])
                ->where('type', 'product')
                ->where('show_order_page', 1)
                ->order('order,id', 'asc')
                ->select()
                ->toArray();

        return ['data'=>$data];
    }

    /**
     * 时间 2024-01-02
     * @title 验证并过滤
     * @desc  验证并过滤
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   array param.self_defined_field - 自定义字段值(键是自定义字段ID,值是填的内容) require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  array data - 过滤后自定义字段值(键是自定义字段ID,值是填的内容)
     */
    public function checkAndFilter($param)
    {
        $productId = $param['product_id'];
        $selfDefinedFieldValue = $param['self_defined_field'];

        if(!is_array($selfDefinedFieldValue)){
            $selfDefinedFieldValue = [];
        }
        if(!isset($this->productField[ $productId ])){
            $selfDefinedField = $this->showOrderPageField(['id'=>$productId, 'need_upstream_id'=>true]);
            $selfDefinedField = $selfDefinedField['data'];
            $this->productField[ $productId ] = $selfDefinedField;
        }else{
            $selfDefinedField = $this->productField[ $productId ];
        }
        $data = [];
        foreach($selfDefinedField as $v){
            $value = $selfDefinedFieldValue[ $v['id'] ] ?? '';
            $value = (string)$value;
            // 输入类型
            if(in_array($v['field_type'], ['text','link','password','textarea'])){
                if($v['is_required'] == 1 && $value === ''){
                    return ['status'=>400, 'msg'=>lang('self_defined_field_please_input', ['{field_name}'=>$v['field_name']])];
                }
                // 验证规则
                if($value !== '' && !empty($v['regexpr']) && !preg_match("{$v['regexpr']}", $value)){
                    return ['status'=>400, 'msg'=>lang('self_defined_field_value_not_match_regexpr', ['{field_name}'=>$v['field_name']])];
                }
                $length = mb_strlen($value);
                if($length > 500){
                    return ['status'=>400, 'msg'=>lang('self_defined_field_text_length_error', ['{field_name}'=>$v['field_name']])];
                }
                if($value !== '' && $v['field_type'] == 'link' && !filter_var($value, FILTER_VALIDATE_URL)){
                    return ['status'=>400, 'msg'=>lang('self_defined_field_link_error', ['{field_name}'=>$v['field_name']])];
                }
            }else if($v['field_type'] == 'dropdown'){
                if($v['is_required'] == 1 && $value === ''){
                    return ['status'=>400, 'msg'=>lang('self_defined_field_please_select', ['{field_name}'=>$v['field_name']])];
                }
                if($value !== ''){
                    $option = explode(',', $v['field_option']);
                    if(!in_array($value, $option, true)){
                        return ['status'=>400, 'msg'=>lang('self_defined_field_option_error', ['{field_name}'=>$v['field_name'],'{value}'=>$value])];
                    }
                }
            }else if($v['field_type'] == 'tickbox'){
                $value = $value == 1 ? '1' : '0';
            }else{
                continue;
            }
            $data[ $v['id'] ] = $value;
        }

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data,
        ];
        return $result;
    }

    /**
     * 时间 2024-01-22
     * @title 把键转为上游ID
     * @desc  调用checkAndFilter后,调用该方法可以把键转为上游ID
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   array param.self_defined_field - 自定义字段值(键是自定义字段ID,值是填的内容) require
     * @return  array - - 自定义字段值(键是上游字段ID,值是填的内容)
     */
    public function toUpstreamId($param)
    {
        $productId = $param['product_id'];
        $selfDefinedFieldValue = $param['self_defined_field'];

        if(!isset($this->productField[ $productId ])){
            $selfDefinedField = $this->showOrderPageField(['id'=>$productId, 'need_upstream_id'=>true]);
            $selfDefinedField = $selfDefinedField['data'];
            $this->productField[ $productId ] = $selfDefinedField;
        }else{
            $selfDefinedField = $this->productField[ $productId ];
        }
        $data = [];
        foreach($selfDefinedField as $v){
            $value = $selfDefinedFieldValue[ $v['id'] ] ?? '';
            $value = (string)$value;
            if(!empty($v['upstream_id'])){
                $data[ $v['upstream_id'] ] = $value;
            }
        }
        return $data;
    }

    /**
     * 时间 2024-01-03
     * @title 订单内页自定义字段
     * @desc  订单内页自定义字段
     * @author hh
     * @version v1
     * @param   int param.order_id - 订单ID require
     * @return  int [].id - 自定义字段ID
     * @return  string [].field_name - 字段名称
     * @return  string [].field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区)
     * @return  string [].value - 填写的内容
     */
    public function showOrderDetailField($param)
    {
        $orderId = $param['order_id'];

        $data = SelfDefinedFieldValueModel::alias('sdfv')
                ->field('sdf.id,sdf.field_name,sdf.field_type,sdfv.value')
                ->join('self_defined_field sdf', 'sdfv.self_defined_field_id=sdf.id')
                ->where('sdfv.order_id', $orderId)
                ->where('sdf.type', 'product')
                ->where('sdf.show_order_detail', 1)
                ->withAttr('value', function($value, $row){
                    if($row['field_type'] == 'tickbox'){
                        $value = $value == '1' ? lang('self_defined_field_tickbox_yes') : lang('self_defined_field_tickbox_no');
                    }
                    return $value;
                })
                ->order('sdf.order,sdf.id', 'asc')
                ->group('sdf.id')
                ->select()
                ->toArray();

        return $data;
    }

    /**
     * 时间 2024-01-03
     * @title 后台产品内页自定义字段
     * @desc  后台产品内页自定义字段
     * @author hh
     * @version v1
     * @param   int param.host_id - 产品ID require
     * @return  int [].id - 自定义字段ID
     * @return  string [].field_name - 字段名称
     * @return  string [].field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区)
     * @return  string [].description - 字段描述
     * @return  string [].field_option - 下拉选项
     * @return  int [].is_required - 是否必填(0=否,1=是)
     * @return  string [].value - 填写的内容
     */
    public function showAdminHostDetailField($param)
    {
        $id = $param['host_id'];
        $productId = HostModel::where('id', $id)->value('product_id');

        $data = $this
                ->alias('sdf')
                ->field('sdf.id,sdf.field_name,sdf.field_type,sdf.description,sdf.field_option,sdf.is_required,sdfv.value')
                ->leftJoin('self_defined_field_value sdfv', 'sdf.id=sdfv.self_defined_field_id AND sdfv.relid='.$id)
                ->where('sdf.relid', $productId)
                ->where('sdf.type', 'product')
                ->where('sdf.show_admin_host_detail', 1)
                ->withAttr('value', function($value, $row){
                    if($row['field_type'] == 'tickbox'){
                        $value = $value == '1' ? '1' : '0';
                    }
                    return $value;
                })
                ->order('sdf.order,sdf.id', 'asc')
                ->group('sdf.id')
                ->select()
                ->toArray();

        return $data;
    }

    /**
     * 时间 2024-01-03
     * @title 前台产品内页自定义字段
     * @desc  前台产品内页自定义字段
     * @author hh
     * @version v1
     * @param   int param.host_id - 产品ID require
     * @return  int [].id - 自定义字段ID
     * @return  string [].field_name - 字段名称
     * @return  string [].field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区)
     * @return  string [].value - 填写的内容
     */
    public function showClientHostDetailField($param)
    {
        $id = $param['host_id'];
        $productId = HostModel::where('id', $id)->value('product_id');

        $data = $this
                ->alias('sdf')
                ->field('sdf.id,sdf.field_name,sdf.field_type,sdfv.value')
                ->leftJoin('self_defined_field_value sdfv', 'sdf.id=sdfv.self_defined_field_id AND sdfv.relid='.$id)
                ->where('sdf.relid', $productId)
                ->where('sdf.type', 'product')
                ->where('sdf.show_client_host_detail', 1)
                ->withAttr('value', function($value, $row){
                    if($row['field_type'] == 'tickbox'){
                        $value = $value == '1' ? lang('self_defined_field_tickbox_yes') : lang('self_defined_field_tickbox_no');
                    }
                    return $value;
                })
                ->order('sdf.order,sdf.id', 'asc')
                ->group('sdf.id')
                ->select()
                ->toArray();

        return $data;
    }

    /**
     * 时间 2024-01-02
     * @title 验证并格式化
     * @desc  验证并格式化,便于后续处理
     * @author hh
     * @version v1
     * @param   int param.host_id - 产品ID require
     * @param   array param.self_defined_field - 自定义字段值(键是自定义字段ID,值是填的内容) require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data[].id - 自定义字段ID
     * @return  int data[].relid - 产品ID
     * @return  string data[].value - 填的内容
     * @return  string data[].log - 变更日志
     */
    public function adminHostUpdateFormat($param)
    {
        $hostId = $param['host_id'];
        $selfDefinedFieldValue = $param['self_defined_field'] ?? [];

        if(!is_array($selfDefinedFieldValue)){
            $selfDefinedFieldValue = [];
        }
        $selfDefinedField = $this->showAdminHostDetailField(['host_id'=>$hostId]);

        $tickbox = [
            lang('self_defined_field_tickbox_no'),
            lang('self_defined_field_tickbox_yes'),
        ];

        $data = [];
        foreach($selfDefinedField as $v){
            $value = $selfDefinedFieldValue[ $v['id'] ] ?? '';
            $value = (string)$value;

            $old = $v['value'] ?? '';
            $new = $value;
            // 输入类型
            if(in_array($v['field_type'], ['text','link','password','textarea'])){
                // 后台不验证规则
                // if($v['is_required'] == 1 && $value === ''){
                //     return ['status'=>400, 'msg'=>lang('请输入'.$v['field_name'])];
                // }
                // // 验证规则
                // if($value !== '' && !empty($v['regexpr']) && !preg_match("{$v['regexpr']}", $value)){
                //     return ['status'=>400, 'msg'=>lang($v['field_name'].'格式错误')];
                // }
                $length = mb_strlen($value);
                if($length > 500){
                    return ['status'=>400, 'msg'=>lang('self_defined_field_text_length_error', ['{field_name}'=>$v['field_name']])];
                }
                // if($value !== '' && $v['field_type'] == 'link' && !filter_var($value, FILTER_VALIDATE_URL)){
                //     return ['status'=>400, 'msg'=>lang('请输入正确的链接')];
                // }
            }else if($v['field_type'] == 'dropdown'){
                // if($v['is_required'] == 1 && $value === ''){
                //     return ['status'=>400, 'msg'=>lang('请选择'.$v['field_name'])];
                // }
                // if($value !== ''){
                //     $option = explode(',', $v['field_option']);
                //     if(!in_array($value, $option, true)){
                //         return ['status'=>400, 'msg'=>lang('请选择正确的'.$v['field_name'])];
                //     }
                // }
            }else if($v['field_type'] == 'tickbox'){
                $value = $value == '1' ? '1' : '0';

                $old = $tickbox[ $v['value'] ] ?? lang('self_defined_field_tickbox_no');
                $new = $tickbox[ $value ];
            }else{
                continue;
            }
            if($old == $new){
                continue;
            }
            $log = lang('log_admin_update_description', [
                '{field}'     => $v['field_name'],
                '{old}'       => $old,
                '{new}'       => $new,
            ]);

            $data[] = [
                'id'            => $v['id'],
                'relid'         => $hostId,
                'value'         => $value,
                'log'           => $log,
            ];
        }

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data,
        ];
        return $result;
    }

    /**
     * 时间 2024-01-03
     * @title 保存后台产品自定义字段
     * @desc  保存后台产品自定义字段
     * @author hh
     * @version v1
     * @param   array param.data - adminHostUpdateFormat的返回值
     */
    public function adminHostUpdateSave($param)
    {
        foreach($param['data'] as $v){
            $exist = SelfDefinedFieldValueModel::where('relid', $v['relid'])->where('self_defined_field_id', $v['id'])->find();
            if($exist){
                SelfDefinedFieldValueModel::where('id', $exist['id'])->update([
                    'value'                 => $v['value'],
                    'update_time'           => time(),
                ]);
            }else{
                SelfDefinedFieldValueModel::create([
                    'self_defined_field_id' => $v['id'],
                    'relid'                 => $v['relid'],
                    'value'                 => $v['value'],
                    'create_time'           => time(),
                ]);
            }
        }
    }

    /**
     * 时间 2024-01-04
     * @title 保存上游商品自定义字段
     * @desc  保存上游商品自定义字段
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   string param.type - 上游类型(whmcs=WHMCS,finance=财务系统,default=V10) require
     * @param   array param.self_defined_field - 上游商品订单可见自定义字段 require
     * @param   int param.self_defined_field[].id - 上游ID
     * @param   string param.self_defined_field[].fieldname - 字段名称(whmcs)
     * @param   string param.self_defined_field[].fieldtype - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区,whmcs)
     * @param   string param.self_defined_field[].description - 描述(whmcs)
     * @param   string param.self_defined_field[].regexpr - 正则验证(whmcs)
     * @param   string param.self_defined_field[].fieldoptions - 下拉选项(whmcs)
     * @param   string param.self_defined_field[].required - 是否必填(on=必填,whmcs)
     * @param   string param.self_defined_field[].showinvoice - 订单显示(on=显示,whmcs)
     * @param   string param.self_defined_field[].fieldname - 字段名称(finance)
     * @param   string param.self_defined_field[].fieldtype - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区,finance)
     * @param   string param.self_defined_field[].description - 描述(finance)
     * @param   string param.self_defined_field[].regexpr - 正则验证(finance)
     * @param   string param.self_defined_field[].fieldoptions - 下拉选项(finance)
     * @param   int param.self_defined_field[].required - 是否必填(0=非必填,1=必填,finance)
     * @param   int param.self_defined_field[].showorder - 订单显示(0=隐藏,1=显示,finance)
     * @param   int param.self_defined_field[].showdetail - 产品内页显示(0=隐藏,1=显示,finance)
     * @param   string param.self_defined_field[].field_name - 字段名称(default)
     * @param   string param.self_defined_field[].field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区,default)
     * @param   string param.self_defined_field[].description - 描述(default)
     * @param   string param.self_defined_field[].regexpr - 正则验证(default)
     * @param   string param.self_defined_field[].field_option - 下拉选项(default)
     * @param   int param.self_defined_field[].is_required - 是否必填(0=非必填,1=必填,default)
     * @param   int param.self_defined_field[].show_client_host_list - 产品列表显示(0=隐藏,1=显示,default)
     * @return  bool
     */
    public function saveUpstreamSelfDefinedField($param)
    {
        // 可能是获取上游信息超时,只有返回数组时才处理
        if(!is_array($param['self_defined_field'])){
            return false;
        }
        $time = time();
        $productId = $param['product_id'];

        // 获取当前商品字段
        $current = $this
            ->field('id,field_name,field_type,description,regexpr,field_option,order,is_required,show_order_page,show_order_detail,show_admin_host_detail,show_client_host_detail,show_client_host_list,upstream_id')
            ->where('relid', $productId)
            ->where('type', 'product')
            ->where('upstream_id', '>', 0)
            ->select()
            ->toArray();

        $old = [];
        foreach($current as $v){
            $id = $v['id'];
            $upstreamId = $v['upstream_id'];

            unset($v['id']);
            $old[ $upstreamId ] = [
                'id'    => $id,
                'md5'   => md5(json_encode($v)),
            ];
        }

        // $data = [];
        // 根据上游格式化数据
        if($param['type'] == 'whmcs'){
            $order = 0;
            foreach($param['self_defined_field'] as $v){
                if($v['regexpr'] !== ''){
                    try{
                        $match = preg_match("{$v['regexpr']}", '');
                        if($match === false){
                            $v['regexpr'] = '';
                        }
                    }catch(\Exception $e){
                        $v['regexpr'] = '';
                    }
                }
                $data = [
                    'field_name'                => $v['fieldname'],
                    'field_type'                => $v['fieldtype'],
                    'description'               => $v['description'],
                    'regexpr'                   => $v['regexpr'],
                    'field_option'              => $v['fieldtype'] == 'dropdown' ? $v['fieldoptions'] : '',
                    'order'                     => $order,
                    'is_required'               => $v['required'] == 'on' ? 1 : 0,
                    'show_order_page'           => 1,
                    'show_order_detail'         => !in_array($v['fieldtype'], ['link','password']) ? ($v['showinvoice'] == 'on' ? 1 : 0) : 0,
                    'show_admin_host_detail'    => 1,
                    'show_client_host_detail'   => 1,
                    'show_client_host_list'     => 0,
                    'upstream_id'               => $v['id'],
                ];
                $order++;
                if(!isset($old[ $v['id'] ])){
                    $data['type'] = 'product';
                    $data['relid'] = $productId;
                    $data['create_time'] = $time;
                    $this->create($data);
                }else{
                    if($old[ $v['id'] ]['md5'] !== md5(json_encode($data))){
                        $data['update_time'] = $time;
                        $this->where('id', $old[ $v['id'] ]['id'])->update($data);
                    }
                    unset($old[ $v['id'] ]);
                }
            }
        }else if($param['type'] == 'finance'){
            $order = 0;
            foreach($param['self_defined_field'] as $v){
                if($v['regexpr'] !== ''){
                    try{
                        $match = preg_match("{$v['regexpr']}", '');
                        if($match === false){
                            $v['regexpr'] = '';
                        }
                    }catch(\Exception $e){
                        $v['regexpr'] = '';
                    }
                }
                $data = [
                    'field_name'                => $v['fieldname'],
                    'field_type'                => $v['fieldtype'],
                    'description'               => $v['description'],
                    'regexpr'                   => $v['regexpr'],
                    'field_option'              => $v['fieldtype'] == 'dropdown' ? $v['fieldoptions'] : '',
                    'order'                     => $order,
                    'is_required'               => $v['required'],
                    'show_order_page'           => 1,
                    'show_order_detail'         => !in_array($v['fieldtype'], ['link','password']) ? $v['showorder'] : 0,
                    'show_admin_host_detail'    => $v['showdetail'],
                    'show_client_host_detail'   => $v['showdetail'],
                    'show_client_host_list'     => 0,
                    'upstream_id'               => $v['id'],
                ];
                $order++;
                if(!isset($old[ $v['id'] ])){
                    $data['type'] = 'product';
                    $data['relid'] = $productId;
                    $data['create_time'] = $time;
                    $this->create($data);
                }else{
                    if($old[ $v['id'] ]['md5'] !== md5(json_encode($data))){
                        $data['update_time'] = $time;
                        $this->where('id', $old[ $v['id'] ]['id'])->update($data);
                    }
                    unset($old[ $v['id'] ]);
                }
            }
        }else if($param['type'] == 'default'){
            $order = 0;
            foreach($param['self_defined_field'] as $v){
                $data = [
                    'field_name'                => $v['field_name'],
                    'field_type'                => $v['field_type'],
                    'description'               => $v['description'],
                    'regexpr'                   => $v['regexpr'],
                    'field_option'              => $v['field_type'] == 'dropdown' ? $v['field_option'] : '',
                    'order'                     => $order,
                    'is_required'               => $v['is_required'],
                    'show_order_page'           => 1,
                    'show_order_detail'         => !in_array($v['field_type'], ['link','password']) ? 1 : 0,
                    'show_admin_host_detail'    => 1,
                    'show_client_host_detail'   => 1,
                    'show_client_host_list'     => $v['show_client_host_list'] ?? 0,
                    'upstream_id'               => $v['id'],
                ];
                $order++;
                if(!isset($old[ $v['id'] ])){
                    $data['type'] = 'product';
                    $data['relid'] = $productId;
                    $data['create_time'] = $time;
                    $this->create($data);
                }else{
                    if($old[ $v['id'] ]['md5'] !== md5(json_encode($data))){
                        $data['update_time'] = $time;
                        $this->where('id', $old[ $v['id'] ]['id'])->update($data);
                    }
                    unset($old[ $v['id'] ]);
                }
            }
        }
        // 上游已删除的
        if(!empty($old)){
            $id = array_column($old, 'id');
            $this->whereIn('id', $id)->delete();
            SelfDefinedFieldValueModel::whereIn('self_defined_field_id', $id)->delete();
        }
        return true;
    }

    /**
     * 时间 2024-01-05
     * @title 获取上游自定义字段值
     * @desc  获取上游自定义字段值,用于下单
     * @author hh
     * @version v1
     * @param   int param.host_id - 产品ID require
     * @param   string param.type - 上游类型(whmcs=WHMCS,finance=财务系统,default=V10) require
     * @return  array - - 键是上游ID,值是内容
     */
    public function getUpstreamSelfDefinedFieldValue($param)
    {
        $hostId = $param['host_id'];
        $productId = HostModel::where('id', $hostId)->value('product_id');

        $data = SelfDefinedFieldValueModel::alias('sdfv')
                ->field('sdf.upstream_id,sdf.field_type,sdfv.value')
                ->join('self_defined_field sdf', 'sdfv.self_defined_field_id=sdf.id')
                ->where('sdfv.relid', $hostId)
                ->where('sdf.relid', $productId)
                ->where('sdf.type', 'product')
                ->where('sdf.upstream_id', '>', 0)
                ->select();

        $value = [];
        foreach($data as $v){
            $value[ $v['upstream_id'] ] = $v['value'];
            if($v['field_type'] == 'tickbox'){
                if($param['type'] == 'whmcs'){
                    $value[ $v['upstream_id'] ] = $v['value'] == '1' ? 'on' : '';
                }
            }
        }
        return $value;
    }

    /**
     * 时间 2024-01-17
     * @title 获取前台产品列表显示自定义字段和值
     * @desc  获取前台产品列表显示自定义字段和值
     * @author hh
     * @version v1
     * @param   array param.product_id - 商品ID require
     * @param   array param.host_id - 产品ID require
     * @param   int param.limit 20 获取数量
     * @return  int self_defined_field[].id - 自定义字段ID
     * @return  string self_defined_field[].field_name - 自定义字段名称
     * @return  string self_defined_field[].field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区)
     * @return  array self_defined_field_value - 如['1'=>['2'=>'hello']],1=产品ID,2=自定义字段ID,hello=填的内容
     */
    public function getHostListSelfDefinedFieldValue($param)
    {
        $result = [
            'self_defined_field'        => [],
            'self_defined_field_value'  => [],
        ];
        if(!isset($param['product_id']) || empty($param['product_id'])){
            return $result;
        }
        $param['limit'] = $param['limit'] ?? 20;

        $where = [];
        $where[] = ['relid', 'IN', $param['product_id']];
        $where[] = ['type', '=', 'product'];
        $where[] = ['show_client_host_list', '=', 1];

        $selfDefinedField = $this
                            ->field('id,field_name,field_type')
                            ->where($where)
                            ->order('order,id', 'asc')
                            ->limit($param['limit'])
                            ->select()
                            ->toArray();

        $result['self_defined_field'] = $selfDefinedField;
        if(!empty($selfDefinedField)){
            $selfDefinedFieldId = array_column($selfDefinedField, 'id');
            $selfDefinedFieldType = array_column($selfDefinedField, 'field_type', 'id');

            $selfDefinedFieldValue = SelfDefinedFieldValueModel::field('id,self_defined_field_id,relid,value')
                                ->whereIn('self_defined_field_id', $selfDefinedFieldId)
                                ->whereIn('relid', $param['host_id'])
                                ->select()
                                ->toArray();
                                
            $data = [];
            foreach($selfDefinedFieldValue as $v){
                if($selfDefinedFieldType[ $v['self_defined_field_id'] ] == 'tickbox'){
                    $v['value'] = $v['value'] == 1 ? lang('self_defined_field_tickbox_yes') : lang('self_defined_field_tickbox_no');
                }
                $data[ $v['relid'] ][ $v['self_defined_field_id'] ] = $v['value'];
            }
            $result['self_defined_field_value'] = $data;
        }
        return $result;
    }



}