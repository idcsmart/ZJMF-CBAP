<?php 
namespace server\idcsmart_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use server\idcsmart_cloud\logic\ToolLogic;

class DataCenterModel extends Model{

	protected $name = 'module_idcsmart_cloud_data_center';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'country'       => 'string',
        'country_code'  => 'string',
        'city'          => 'string',
        'area'          => 'string',
        'order'         => 'int',
        'product_id'    => 'int',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2022-06-17
     * @title 数据中心列表
     * @desc 数据中心列表
     * @author hh
     * @version v1
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @param   string param.orderby - 排序字段(id,country,order)
     * @param   string param.sort - 升降序
     * @param   int param.product_id - 商品ID
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 数据中心ID
     * @return  string data.list[].country - 国家
     * @return  string data.list[].country_code - 国家代码
     * @return  string data.list[].city - 城市
     * @return  string data.list[].area - 区域
     * @return  int data.list[].order - 排序
     * @return  int data.list[].server[].server_id - 接口ID
     * @return  string data.list[].server[].server_param - 接口参数
     * @return  string data.list[].server[].server_name - 接口名称
     * @return  int data.count - 总条数
     */
    public function dataCenterList($param)
    {
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','country','order'])){
            $param['orderby'] = 'id';
        }

        $where = function (Query $query) use($param) {
            if(!empty($param['product_id'])){
                $query->where('product_id', $param['product_id']);
            }
        };

        $dataCenter = $this
                ->field('id,country,country_code,city,area,order')
                ->where($where)
                ->limit($param['limit'])
                ->page($param['page'])
                ->order($param['orderby'], $param['sort'])
                ->select()
                ->toArray();

        $dataCenterServerLinkArr = [];
        if(!empty($dataCenter)){
            $dataCenterServerLink = DataCenterServerLinkModel::alias('dcsl')
                                    ->field('dcsl.*,s.name server_name')
                                    ->leftJoin('server s', 'dcsl.server_id=s.id')
                                    ->whereIn('dcsl.module_idcsmart_cloud_data_center_id', array_column($dataCenter, 'id'))
                                    ->select()
                                    ->toArray();
            foreach($dataCenterServerLink as $v){
                $dataCenterId = $v['module_idcsmart_cloud_data_center_id'];
                unset($v['module_idcsmart_cloud_data_center_id']);
                $dataCenterServerLinkArr[$dataCenterId][] = $v;
            }

            foreach($dataCenter as $k=>$v){
                $dataCenter[$k]['server'] = $dataCenterServerLinkArr[$v['id']] ?? [];
            }
        }
        $count = $this
                ->where($where)
                ->group('id')
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
     * @param   string param.country - 国家 require
     * @param   string param.country_code - 国家代码 require
     * @param   string param.city - 城市 require
     * @param   string param.area - 区域 require
     * @param   array param.server - 接口和接口参数的数组 require
     * @param   int param.server[].server_id - 接口ID require
     * @param   string param.server[].server_param - 接口参数 require
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
        if($ProductModel->getModule() != 'idcsmart_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        // 是否添加了相同的数据中心
        $same = $this
                ->where('product_id', $param['product_id'])
                ->where('country', $param['country'])
                ->where('city', $param['city'])
                ->where('area', $param['area'])
                ->find();
        if(!empty($same)){
            return ['status'=>400, 'msg'=>lang_plugins('the_same_data_center_already_add')];
        }

        $server = $param['server'] ?? [];
        unset($param['server']);

        $link_data = [];
        $server_id = [];
        foreach($server as $v){
            $ServerModel = ServerModel::find($v['server_id']);
            if(empty($ServerModel)){
                return ['status'=>400, 'msg'=>lang_plugins('server_error')];
            }
            if($ServerModel['module'] != 'idcsmart_cloud'){
                return ['status'=>400, 'msg'=>lang_plugins('only_can_select_idcsmart_cloud_server')];
            }
            if(in_array($v['server_id'], $server_id)){
                return ['status'=>400, 'msg'=>lang_plugins('cannot_select_duplicate_server')];
            }
            $server_id[] = $v['server_id'];
            $link_data[] = [
                'server_id'=>$v['server_id'],
                'server_param'=>$v['server_param'] ?? '',
            ];
        }
        $param['order'] = $param['order'] ?? 0;
        $param['create_time'] = time();

        $this->startTrans();
        try{
            $dataCenter = $this->create($param, ['country','country_code','city','area','order','product_id','create_time']);

            foreach($link_data as $k=>$v){
                $link_data[$k]['module_idcsmart_cloud_data_center_id'] = $dataCenter->id;
            }
            $DataCenterServerLinkModel = new DataCenterServerLinkModel();
            $DataCenterServerLinkModel->saveAll($link_data);

            $image = ImageModel::field('id')->where('product_id', $ProductModel['id'])->select()->toArray();
            $imageDataCenterLink = [];
            foreach($image as $v){
                $imageDataCenterLink[] = [
                    'module_idcsmart_cloud_image_id'=>$v['id'],
                    'module_idcsmart_cloud_data_center_id'=>$dataCenter->id,
                ];
            }
            $ImageDataCenterLinkModel = new ImageDataCenterLinkModel();

            $ImageDataCenterLinkModel->saveAll($imageDataCenterLink);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        $description = lang_plugins('log_create_data_center_success', ['{name}'=>$param['country'].$param['city'].$param['area'] ]);
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
     * @param   string param.country - 国家 required
     * @param   string param.country_code - 国家代码 required
     * @param   string param.city - 城市 required
     * @param   string param.area - 区域 required
     * @param   array param.server - 接口和接口参数的数组 required
     * @param   int param.server[].server_id - 接口ID required
     * @param   string param.server[].server_param - 接口参数 required
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function updateDataCenter($param)
    {
        $dataCenter = $this->find($param['id']);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }
        $server = $param['server'] ?? [];
        unset($param['server']);

        // 是否添加了相同的数据中心
        $same = $this
                ->where('product_id', $param['product_id'])
                ->where('country', $param['country'])
                ->where('city', $param['city'])
                ->where('area', $param['area'])
                ->where('id', '<>', $dataCenter['id'])
                ->find();
        if(!empty($same)){
            return ['status'=>400, 'msg'=>lang_plugins('the_same_data_center_already_add')];
        }

        $link_data = [];
        $server_id = [];
        foreach($server as $v){
            $ServerModel = ServerModel::find($v['server_id']);
            if(empty($ServerModel)){
                return ['status'=>400, 'msg'=>lang_plugins('server_error')];
            }
            if($ServerModel['module'] != 'idcsmart_cloud'){
                return ['status'=>400, 'msg'=>lang_plugins('only_can_select_idcsmart_cloud_server')];
            }
            if(in_array($v['server_id'], $server_id)){
                return ['status'=>400, 'msg'=>lang_plugins('cannot_select_duplicate_server')];
            }
            $server_id[] = $v['server_id'];
            $link_data[] = [
                'server_id'=>$v['server_id'],
                'server_param'=>$v['server_param'] ?? '',
            ];
        }
        $param['update_time'] = time();

        $this->startTrans();
        try{
            $dataCenter = $this->update($param, ['id'=>$param['id']], ['country','country_code','city','area','order','update_time']);

            foreach($link_data as $k=>$v){
                $link_data[$k]['module_idcsmart_cloud_data_center_id'] = $dataCenter->id;
            }
            DataCenterServerLinkModel::where('module_idcsmart_cloud_data_center_id', $param['id'])->delete();

            $DataCenterServerLinkModel = new DataCenterServerLinkModel();
            $DataCenterServerLinkModel->saveAll($link_data);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        // $desc = [
        //     'country'=>lang_plugins('country'),
        //     'country_code'=>lang_plugins('country_code'),
        //     'city'=>lang_plugins('city'),
        //     'area'=>lang_plugins('area'),
        //     'server_id'=>lang_plugins('server'),
        // ];

        // $old = $dataCenter;


        // $cal['module_idcsmart_cloud_cal_group_id'] = CalGroupModel::where('id', $cal['module_idcsmart_cloud_cal_group_id'])->value('name');
        // $param['module_idcsmart_cloud_cal_group_id'] = $calGroup['name'];

        // $description = ToolLogic::createEditLog($cal, $param, $desc);
        // if(!empty($description)){
        //     $description = lang_plugins('log_modify_data_center_success', [
        //         '{name}'=>$cal['name'],
        //         '{detail}'=>$description,
        //     ]);
        //     active_log($description, 'product', $calGroup['product_id']);
        // }

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
     * @param   int id - 数据中心ID
     */
    public function deleteDataCenter($id)
    {
        $dataCenter = $this->find($id);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }
        // TODO 不能删除的情况
        $use = HostLinkModel::where('module_idcsmart_cloud_data_center_id', $id)->find();
        if($use){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_is_using')];
        }
        
        $this->startTrans();
        try{
            $dataCenter->delete();

            DataCenterServerLinkModel::where('module_idcsmart_cloud_data_center_id', $id)->delete();
            BwDataCenterLinkModel::where('module_idcsmart_cloud_data_center_id', $id)->delete();
            ImageDataCenterLinkModel::where('module_idcsmart_cloud_data_center_id', $id)->delete();
            PackageDataCenterLinkModel::where('module_idcsmart_cloud_data_center_id', $id)->delete();
            
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        $description = lang_plugins('log_delete_data_center_success', ['{name}'=>$dataCenter['country'].$dataCenter['city'].$dataCenter['area'] ]);
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
        $param['update_time'] = time();

        $this->update($param, ['id'=>$param['id']], ['order','update_time']);

        $desc = [
            'order'=>lang_plugins('order'),
        ];

        $description = ToolLogic::createEditLog($dataCenter, $param, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_data_center_success', [
                '{name}'=>$dataCenter['country'].$dataCenter['city'].$dataCenter['area'],
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
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array data.list - 列表数据
     * @return  string data.list[].country - 国家
     * @return  string data.list[].country_code - 国家代码
     * @return  string data.list[].city - 城市
     * @return  int data.list[].area[].id - 数据中心ID
     * @return  string data.list[].area[].area - 区域名称
     */
    public function formatDisplay($param){
        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>[]
        ];
        $where = function(Query $query) use ($param){
            if(!empty($param['id'])){
                $query->where('product_id', $param['id']);
            }
        };

        // $country = $this
        //             ->field('country,country_code')
        //             ->group('country')
        //             ->where($where)
        //             ->order('order', 'asc')
        //             ->order('id', 'asc')
        //             ->select()
        //             ->toArray();

        $city = $this
                ->field('country,country_code,city')
                ->group('country,city')
                ->where($where)
                ->order('order', 'asc')
                ->order('id', 'asc')
                ->select()
                ->toArray();

        $area = $this
                ->field('id,country,city,area')
                ->where($where)
                ->order('order', 'asc')
                ->order('id', 'asc')
                ->select()
                ->toArray();

        $areaArr = [];
        foreach($area as $k=>$v){
            $areaArr[$v['country']][$v['city']][] = [
                'id'=>$v['id'],
                'area'=>$v['area']
            ];
        }

        // $cityArr = [];
        // foreach($city as $k=>$v){
        //     $cityArr[$v['country']][] = [
        //         'city'=>$v['city'],
        //         'area'=>$areaArr[$v['country']][$v['city']] ?? [],
        //     ];
        // }
        foreach($city as $k=>$v){
            $city[$k]['area'] = $areaArr[$v['country']][$v['city']] ?? [];
        }
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list' => $city
            ]
        ];
        return $result;
    }


}