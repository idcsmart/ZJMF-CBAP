<?php 
namespace server\common_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
use server\common_cloud\logic\ToolLogic;

class DataCenterModel extends Model{

	protected $name = 'module_common_cloud_data_center';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'country_id'        => 'int',
        'city'              => 'string',
        'cloud_config'      => 'string',
        'cloud_config_id'   => 'int',
        'create_time'       => 'int',
        'product_id'        => 'int',
        'order'             => 'int',
    ];

    /**
     * 时间 2022-06-17
     * @title 数据中心列表
     * @desc 数据中心列表
     * @author hh
     * @version v1
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @param   string param.orderby - 排序字段(id,order)
     * @param   string param.sort - 升降序
     * @param   int param.product_id - 商品ID
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 数据中心ID
     * @return  int data.list[].country_id - 国家ID
     * @return  string data.list[].city - 城市
     * @return  int data.list[].order - 排序
     * @return  string data.list[].cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID)
     * @return  int data.list[].cloud_config_id - 魔方云配置关联ID
     * @return  int data.list[].create_time - 创建时间
     * @return  int data.list[].product_id - 商品ID
     * @return  string data.list[].country_name - 国家名称
     * @return  int data.count - 总条数
     */
    public function dataCenterList($param)
    {
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','order'])){
            $param['orderby'] = 'id';
        }

        $where = function (Query $query) use($param) {
            if(!empty($param['product_id'])){
                $query->where('dc.product_id', $param['product_id']);
            }
        };

        $dataCenter = $this
                ->alias('dc')
                ->field('dc.*,c.name_zh country_name')
                ->where($where)
                ->leftJoin('country c', 'dc.country_id=c.id')
                ->limit($param['limit'])
                ->page($param['page'])
                ->order($param['orderby'], $param['sort'])
                ->select()
                ->toArray();
    
        $count = $this
                ->alias('dc')
                ->where($where)
                ->group('dc.id')
                ->count();

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list'=>$dataCenter,
                'count'=>$count
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-06-15
     * @title 创建数据中心
     * @desc 创建数据中心
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   int param.country_id - 国家 require
     * @param   string param.city - 城市 require
     * @param   string param.cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID) require
     * @param   int param.config_rel_id - 魔方云配置关联ID require
     * @param   int param.order - 排序
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  int data.id - 数据中心ID
     */
    public function createDataCenter($param)
    {
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'common_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $CountryModel = CountryModel::find($param['country_id']);
        if(empty($CountryModel)){
            return ['status'=>400, 'msg'=>lang_plugins('country_id_error')];
        }
        // 是否添加了相同的数据中心
        $same = $this
                ->where('product_id', $param['product_id'])
                ->where('country_id', $param['country_id'])
                ->where('city', $param['city'])
                ->find();
        if(!empty($same)){
            return ['status'=>400, 'msg'=>lang_plugins('the_same_data_center_already_add')];
        }

        $param['create_time'] = time();

        $this->startTrans();
        try{
            $dataCenter = $this->create($param, ['country_id','city','cloud_config','cloud_config_id','product_id','create_time','order']);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        $description = lang_plugins('log_create_data_center_success', ['{name}'=>$CountryModel['name_zh'].$param['city'] ]);
        active_log($description, 'product', $ProductModel['id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$dataCenter->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2022-06-15
     * @title 修改数据中心
     * @desc 修改数据中心
     * @author hh
     * @version v1
     * @param   int param.id - 数据中心ID required
     * @param   int param.country_id - 国家 require
     * @param   string param.city - 城市 require
     * @param   string param.cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID) require
     * @param   int param.config_rel_id - 魔方云配置关联ID require
     * @param   int param.order - 排序
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  int data.id - 数据中心ID
     */
    public function updateDataCenter($param)
    {
        $dataCenter = $this->find($param['id']);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }
        $CountryModel = CountryModel::find($param['country_id']);
        if(empty($CountryModel)){
            return ['status'=>400, 'msg'=>lang_plugins('country_id_error')];
        }

        $OldCountryModel = CountryModel::find($dataCenter['country_id']);

        // 是否添加了相同的数据中心
        $same = $this
                ->where('product_id', $dataCenter['product_id'])
                ->where('country_id', $param['country_id'])
                ->where('city', $param['city'])
                ->where('id', '<>', $dataCenter['id'])
                ->find();
        if(!empty($same)){
            return ['status'=>400, 'msg'=>lang_plugins('the_same_data_center_already_add')];
        }

        $this->startTrans();
        try{
            $this->update($param, ['id'=>$param['id']], ['country_id','city','cloud_config','cloud_config_id']);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        $type = [
            'node'=>lang_plugins('node_id'),
            'area'=>lang_plugins('area_id'),
            'node_group'=>lang_plugins('node_group_id'),
        ];

        $desc = [
            'country'=>lang_plugins('country'),
            'city'=>lang_plugins('city'),
            'cloud_config'=>lang_plugins('cloud_config'),
        ];
        $old = [
            'country'=>$OldCountryModel['name_zh'],
            'city'=>$dataCenter['city'],
            'cloud_config'=>$type[ $dataCenter['cloud_config'] ].$dataCenter['cloud_config_id'],
        ];
        $new = [
            'country'=>$CountryModel['name_zh'],
            'city'=>$param['city'],
            'cloud_config'=>$type[ $param['cloud_config'] ].$param['cloud_config_id'],
        ];

        $description = ToolLogic::createEditLog($old, $new, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_data_center_success', [
                '{name}'=>$old['country'].$old['city'],
                '{detail}'=>$description,
            ]);
            active_log($description, 'product', $dataCenter['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2022-06-15
     * @title 删除数据中心
     * @desc 删除数据中心
     * @author hh
     * @version v1
     * @param   int id - 数据中心ID require
     */
    public function deleteDataCenter($id)
    {
        $dataCenter = $this->find($id);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }
        // TODO 不能删除的情况
        // $use = HostLinkModel::where('data_center_id', $id)->find();
        // if($use){
        //     return ['status'=>400, 'msg'=>lang_plugins('data_center_is_using')];
        // }
        // 套餐正在使用
        $use = PackageModel::where('data_center_id', $id)->find();
        if($use){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_is_using')];
        }
        
        $this->startTrans();
        try{
            $dataCenter->delete();

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        $CountryModel = CountryModel::find($dataCenter['country_id']);

        $description = lang_plugins('log_delete_data_center_success', ['{name}'=>$CountryModel['name_zh'].$dataCenter['city'] ]);
        active_log($description, 'product', $dataCenter['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2022-06-17
     * @title 修改数据中心排序
     * @desc 修改数据中心排序
     * @author hh
     * @version v1
     * @param   int param.id - 数据中心ID required
     * @param   int param.order - 排序 required
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function updateOrder($param)
    {
        $dataCenter = $this->find($param['id']);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }

        $this->update($param, ['id'=>$param['id']], ['order']);

        $desc = [
            'order'=>lang_plugins('order'),
        ];

        $description = ToolLogic::createEditLog($dataCenter, $param, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_data_center_success', [
                '{name}'=>$dataCenter->getDataCenterName(),
                '{detail}'=>$description,
            ]);
            active_log($description, 'product', $dataCenter['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2022-06-21
     * @title 获取数据中心
     * @desc 获取数据中心格式化显示
     * @author hh
     * @version v1
     * @param   int param.id - 商品ID
     * @return  array data.list - 列表数据
     * @return  string data.list[].id - 国家ID
     * @return  string data.list[].iso - 图标
     * @return  string data.list[].name_zh - 国家名称
     * @return  int data.list[].city[].id - 数据中心ID
     * @return  string data.list[].city[].name - 城市
     */
    public function formatDisplay($param){
        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>[]
        ];

        $param['id'] = $param['id'] ?: 0;

        $where = [];

        if($param['id']>0){
            $where[] = ['product_id', '=', $param['id']];
        }

        $country = $this
                ->where($where)
                ->column('country_id');

        if(empty($country)){
            return $result;
        }
        
        // 有商品ID,限制获取
        if(!empty($param['id'])){

            $ProductModel = ProductModel::find($param['id']);

            $wherePackage = [];
            $wherePackage[] = ['product_id', '=', $param['id']];

            if($ProductModel['pay_type'] == 'free'){
                // $wherePackage[] = ['onetime_fee|month_fee|quarter_fee|year_fee|two_year|three_year', '<>', ''];
            }else if($ProductModel['pay_type'] == 'onetime'){
                $wherePackage[] = ['onetime_fee', '<>', ''];
            }else{
                $wherePackage[] = ['month_fee|quarter_fee|year_fee|two_year|three_year', '<>', ''];
            }

            $dataCenterId = PackageModel::where($wherePackage)
                        ->field('DISTINCT data_center_id')
                        ->select()
                        ->toArray();

            if(empty($dataCenterId)){
                return $result;
            }

            $where[] = ['id', 'IN', array_column($dataCenterId, 'data_center_id')];
        }else{


        }

        $country = CountryModel::field('id,iso,name_zh')
                    ->whereIn('id', $country)
                    ->select()
                    ->toArray();

        $city = $this
                ->field('id,country_id,city')
                ->where($where)
                ->order('order', 'asc')
                ->select()
                ->toArray();

        $cityArr = [];
        foreach($city as $k=>$v){
            $cityArr[$v['country_id']][] = [
                'id'=>$v['id'],
                'name'=>$v['city']
            ];
        }

        foreach($country as $k=>$v){
            $country[$k]['city'] = $cityArr[$v['id']] ?? [];
        }
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list' => $country
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-09-28
     * @title 获取数据中心名称
     * @desc 获取数据中心名称
     * @author hh
     * @version v1
     * @param   DataCenterModel $DataCenterModel 
     * @return  string
     */
    public function getDataCenterName($DataCenterModel = null){
        $DataCenterModel = $DataCenterModel ?? $this;

        $CountryModel = CountryModel::find($DataCenterModel['country_id']);

        return ($CountryModel['name_zh'] ?? '').$DataCenterModel['city'];
    }



}