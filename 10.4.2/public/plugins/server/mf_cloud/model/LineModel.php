<?php 
namespace server\mf_cloud\model;

use think\Model;
use server\mf_cloud\logic\ToolLogic;

/**
 * @title 线路模型
 * @use server\mf_cloud\model\LineModel
 */
class LineModel extends Model
{
	protected $name = 'module_mf_cloud_line';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'data_center_id'    => 'int',
        'name'              => 'string',
        'bill_type'         => 'string',
        'bw_ip_group'       => 'string',
        'defence_enable'    => 'int',
        'defence_ip_group'  => 'string',
        'ip_enable'         => 'int',
        'link_clone'        => 'int',
        'order'             => 'int',
        'create_time'       => 'int',
        'gpu_name'          => 'string',
        'gpu_enable'        => 'int',
    ];

    /**
     * 时间 2023-02-02
     * @title 添加线路
     * @desc 添加线路
     * @author hh
     * @version v1
     * @param   int param.data_center_id - 数据中心ID require
     * @param   string param.name - 名称 require
     * @param   string param.bill_type - 计费类型(bw=带宽计费,flow=流量计费) require
     * @param   string param.bw_ip_group - 计费IP分组
     * @param   int param.defence_enable - 启用防护价格配置(0=关闭,1=开启) require
     * @param   string param.defence_ip_group - 防护IP分组
     * @param   int param.ip_enable - 启用附加IP(0=关闭,1=开启) require
     * @param   int param.link_clone - 链接创建(0=关闭,1=开启) require
     * @param   int param.order 0 排序
     * @param   int param.gpu_enable 0 启用显卡配置(0=关闭,1=开启)
     * @param   string param.gpu_name - 显卡名称
     * @param   array param.bw_data - 带宽计费数据 requireIf,bill_type=bw
     * @param   string param.bw_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @param   int param.bw_data[].value - 带宽
     * @param   int param.bw_data[].min_value - 最小值
     * @param   int param.bw_data[].max_value - 最大值
     * @param   array param.bw_data[].price - 周期价格(如["5"=>"12"],5是周期ID,12是价格)
     * @param   string param.bw_data[].other_config.in_bw - 进带宽
     * @param   string param.bw_data[].other_config.advanced_bw - 智能带宽规则ID
     * @param   array param.flow_data - 流量计费数据 requireIf,bill_type=flow
     * @param   int param.flow_data[].value - 流量(GB,0=无限流量) require
     * @param   array param.flow_data[].price - 周期价格(如["5"=>"12"],5是周期ID,12是价格)
     * @param   int param.flow_data[].other_config.in_bw - 进带宽 require
     * @param   int param.flow_data[].other_config.out_bw - 出带宽 require
     * @param   int param.flow_data[].other_config.traffic_type - 计费方向(1=进,2=出,3=进+出) require
     * @param   string param.flow_data[].other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环) require
     * @param   array param.defence_data - 防护数据
     * @param   int param.defence_data[].value - 防御峰值(G) require
     * @param   array param.defence_data[].price - 周期价格(如["5"=>"12"],5是周期ID,12是价格)
     * @param   array param.ip_data - 附加IP数据
     * @param   int param.ip_data[].value - IP数量 require
     * @param   array param.ip_data[].price - 周期价格(如["5"=>"12"],5是周期ID,12是价格)
     * @param   int param.gpu_data[].value - GPU数量
     * @param   array param.gpu_data[].price - 周期价格(如["5"=>"12"],5是周期ID,12是价格)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.id - 线路ID
     */
    public function lineCreate($param)
    {
        $dataCenter = DataCenterModel::find($param['data_center_id']);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }
        $exist = $this
                ->where('data_center_id', $dataCenter['id'])
                ->where('name', $param['name'])
                ->find();
        if(!empty($exist)){
            return ['status'=>400, 'msg'=>lang_plugins('line_name_exist')];
        }
        $productId = $dataCenter['product_id'];

        $duration = DurationModel::where('product_id', $productId)->column('id');
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$productId]);
        $config = $config['data'];
        // hyperv不能添加流量线路
        if($config['type'] == 'hyperv' && $param['bill_type'] != 'bw'){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_hyperv_cannot_add_flow_line')];
        }

        $time = time();
        $param['bw_ip_group'] = $param['bw_ip_group'] ?? '';
        $param['defence_ip_group'] = $param['defence_ip_group'] ?? '';
        $param['create_time'] = $time;
        $param['order'] = $param['order'] ?? 0;
        $param['gpu_name'] = $param['gpu_name'] ?? '';

        $this->startTrans();
        try{
            $line = $this->create($param, ['data_center_id','name','bill_type','bw_ip_group','defence_enable','defence_ip_group','ip_enable','link_clone','order','create_time','gpu_enable','gpu_name']);

            $priceArr = [];
            if($param['bill_type'] == 'bw'){
                // 带宽计费
                foreach($param['bw_data'] as $v){
                    $v['product_id'] = $productId;
                    $v['rel_type'] = OptionModel::LINE_BW;
                    $v['rel_id'] = $line->id;
                    if($v['type'] == 'radio'){
                        $v['min_value'] = 0;
                        $v['max_value'] = 0;
                    }else{
                        $v['value'] = 0;
                    }
                    $v['step'] = 1;
                    $v['create_time'] = $time;

                    // hyperv没有高级配置
                    if($config['type'] == 'hyperv'){
                        $v['other_config'] = json_encode([
                            'in_bw'         => '',
                            'advanced_bw'   => '',
                        ]);
                    }else{
                        $v['other_config'] = json_encode([
                            'in_bw'         => $v['other_config']['in_bw'] ?? '',
                            'advanced_bw'   => $v['other_config']['advanced_bw'] ?? '',
                        ]);
                    }

                    if(isset($v['id'])) unset($v['id']);

                    $option = OptionModel::create($v, ['product_id','rel_type','rel_id','type','value','min_value','max_value','step','other_config','create_time']);

                    foreach($duration as $vv){
                        if(isset($v['price'][$vv])){
                            $priceArr[] = [
                                'product_id'    => $productId,
                                'rel_type'      => PriceModel::REL_TYPE_OPTION,
                                'rel_id'        => $option->id,
                                'duration_id'   => $vv,
                                'price'         => $v['price'][$vv],
                            ];
                        }
                    }
                }
            }else{
                // 流量计费
                foreach($param['flow_data'] as $v){
                    $v['product_id'] = $productId;
                    $v['rel_type'] = OptionModel::LINE_FLOW;
                    $v['rel_id'] = $line->id;
                    $v['type'] = 'radio';
                    $v['create_time'] = $time;
                    $v['other_config'] = json_encode([
                        'in_bw' => $v['other_config']['in_bw'],
                        'out_bw' => $v['other_config']['out_bw'],
                        'traffic_type' => $v['other_config']['traffic_type'],
                        'bill_cycle' => $v['other_config']['bill_cycle'],
                    ]);

                    if(isset($v['id'])) unset($v['id']);

                    $option = OptionModel::create($v, ['product_id','rel_type','rel_id','type','value','other_config','create_time']);

                    foreach($duration as $vv){
                        if(isset($v['price'][$vv])){
                            $priceArr[] = [
                                'product_id'    => $productId,
                                'rel_type'      => PriceModel::REL_TYPE_OPTION,
                                'rel_id'        => $option->id,
                                'duration_id'   => $vv,
                                'price'         => $v['price'][$vv],
                            ];
                        }
                    }
                }
            }
            // 防护配置
            if(isset($param['defence_data']) && is_array($param['defence_data'])){
                foreach($param['defence_data'] as $v){
                    $v['product_id'] = $productId;
                    $v['rel_type'] = OptionModel::LINE_DEFENCE;
                    $v['rel_id'] = $line->id;
                    $v['type'] = 'radio';
                    $v['create_time'] = $time;
                    $v['other_config'] = json_encode([]);

                    if(isset($v['id'])) unset($v['id']);

                    $option = OptionModel::create($v, ['product_id','rel_type','rel_id','type','value','other_config','create_time']);

                    foreach($duration as $vv){
                        if(isset($v['price'][$vv])){
                            $priceArr[] = [
                                'product_id'    => $productId,
                                'rel_type'      => PriceModel::REL_TYPE_OPTION,
                                'rel_id'        => $option->id,
                                'duration_id'   => $vv,
                                'price'         => $v['price'][$vv],
                            ];
                        }
                    }
                }
            }
            // 附加IP
            if(isset($param['ip_data']) && is_array($param['ip_data'])){
                foreach($param['ip_data'] as $v){
                    $v['product_id'] = $productId;
                    $v['rel_type'] = OptionModel::LINE_IP;
                    $v['rel_id'] = $line->id;
                    $v['type'] = 'radio';
                    $v['create_time'] = $time;
                    $v['other_config'] = json_encode([]);

                    if(isset($v['id'])) unset($v['id']);
                    
                    $option = OptionModel::create($v, ['product_id','rel_type','rel_id','type','value','other_config','create_time']);

                    foreach($duration as $vv){
                        if(isset($v['price'][$vv])){
                            $priceArr[] = [
                                'product_id'    => $productId,
                                'rel_type'      => PriceModel::REL_TYPE_OPTION,
                                'rel_id'        => $option->id,
                                'duration_id'   => $vv,
                                'price'         => $v['price'][$vv],
                            ];
                        }
                    }
                }
            }
            // GPU数量
            if(isset($param['gpu_data']) && is_array($param['gpu_data'])){
                foreach($param['gpu_data'] as $v){
                    $v['product_id'] = $productId;
                    $v['rel_type'] = OptionModel::LINE_GPU;
                    $v['rel_id'] = $line->id;
                    $v['type'] = 'radio';
                    $v['create_time'] = $time;
                    $v['other_config'] = json_encode([]);

                    if(isset($v['id'])) unset($v['id']);
                    
                    $option = OptionModel::create($v, ['product_id','rel_type','rel_id','type','value','other_config','create_time']);

                    foreach($duration as $vv){
                        if(isset($v['price'][$vv])){
                            $priceArr[] = [
                                'product_id'    => $productId,
                                'rel_type'      => PriceModel::REL_TYPE_OPTION,
                                'rel_id'        => $option->id,
                                'duration_id'   => $vv,
                                'price'         => $v['price'][$vv],
                            ];
                        }
                    }
                }
            }
            if(!empty($priceArr)){
                $PriceModel = new PriceModel();
                $PriceModel->insertAll($priceArr);
            }

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }

        $description = lang_plugins('log_mf_cloud_add_line_success', ['{name}'=>$param['name']]);
        active_log($description, 'product', $productId);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$line->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2023-02-02
     * @title 线路详情
     * @desc 线路详情
     * @author hh
     * @version v1
     * @param   int id - 线路ID require
     * @return  int id - 线路ID
     * @return  string name - 线路名称
     * @return  string bill_type - 计费类型(bw=带宽计费,flow=流量计费)
     * @return  string bw_ip_group - 计费IP分组
     * @return  int defence_enable - 启用防护价格配置(0=关闭,1=开启)
     * @return  string defence_ip_group - 防护IP分组
     * @return  int ip_enable - 启用附加IP(0=关闭,1=开启)
     * @return  int link_clone - 链接创建(0=关闭,1=开启)
     * @return  int order - 排序
     * @return  int gpu_enable - 启用显卡配置(0=关闭,1=开启)
     * @return  string gpu_name - 显卡名称
     * @return  int bw_data[].id - 配置ID
     * @return  string bw_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  string bw_data[].value - 带宽
     * @return  int bw_data[].min_value - 最小值
     * @return  int bw_data[].max_value - 最大值
     * @return  int bw_data[].product_id - 商品ID
     * @return  string bw_data[].price - 价格
     * @return  string bw_data[].duration - 周期
     * @return  int flow_data[].id - 配置ID
     * @return  string flow_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int flow_data[].value - 流量
     * @return  int flow_data[].product_id - 商品ID
     * @return  string flow_data[].price - 价格
     * @return  string flow_data[].duration - 周期
     * @return  int defence_data[].id - 配置ID
     * @return  string defence_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int defence_data[].value - 防御峰值(G)
     * @return  int defence_data[].product_id - 商品ID
     * @return  string defence_data[].price - 价格
     * @return  string defence_data[].duration - 周期
     * @return  int ip_data[].id - 配置ID
     * @return  string ip_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int ip_data[].value - IP数量
     * @return  int ip_data[].product_id - 商品ID
     * @return  string ip_data[].price - 价格
     * @return  string ip_data[].duration - 周期
     * @return  int gpu_data[].id - 配置ID
     * @return  string gpu_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int gpu_data[].value - GPU数量
     * @return  int gpu_data[].product_id - 商品ID
     * @return  string gpu_data[].price - 价格
     * @return  string gpu_data[].duration - 周期
     */
    public function lineIndex($id)
    {
        $line = $this
                ->field('id,name,bill_type,bw_ip_group,defence_enable,defence_ip_group,ip_enable,link_clone,order,gpu_enable,gpu_name')
                ->find($id);
        if(empty($line)){
            return (object)[];
        }
        $data = $line->toArray();

        $OptionModel = new OptionModel();

        $param = [];
        $param['rel_id'] = $id;
        $param['sort'] = 'asc';
        $param['page'] = 1;
        $param['limit'] = 999;

        if($line['bill_type'] == 'bw'){
            $param['rel_type'] = OptionModel::LINE_BW;
            $param['orderby'] = 'value,min_value';

            $field = 'id,type,value,min_value,max_value';
            $result = $OptionModel->optionList($param, $field);

            $data['bw_data'] = $result['list'];
        }else{
            $param['rel_type'] = OptionModel::LINE_FLOW;
            $param['orderby'] = 'value';
            
            $field = 'id,type,value';
            $result = $OptionModel->optionList($param, $field);

            $data['flow_data'] = $result['list'];
        }

        $param['rel_type'] = OptionModel::LINE_DEFENCE;
        $param['orderby'] = 'value';
        
        $field = 'id,type,value';
        $result = $OptionModel->optionList($param, $field);

        $data['defence_data'] = $result['list'];

        $param['rel_type'] = OptionModel::LINE_IP;
        $param['orderby'] = 'value';
        
        $field = 'id,type,value';
        $result = $OptionModel->optionList($param, $field);

        $data['ip_data'] = $result['list'];

        $param['rel_type'] = OptionModel::LINE_GPU;
        $param['orderby'] = 'value';

        $field = 'id,type,value';
        $result = $OptionModel->optionList($param, $field);

        $data['gpu_data'] = $result['list'];
        return $data;
    }

    /**
     * 时间 2023-02-03
     * @title 修改线路
     * @desc 修改线路
     * @author hh
     * @version v1
     * @param   int param.id - 线路ID require
     * @param   string param.name - 线路名称 require
     * @param   string param.bw_ip_group - 计费IP分组
     * @param   int param.defence_enable - 启用防护价格配置(0=关闭,1=开启) require
     * @param   string param.defence_ip_group - 防护IP分组
     * @param   int param.ip_enable - 启用附加IP(0=关闭,1=开启) require
     * @param   int param.link_clone - 链接创建(0=关闭,1=开启) require
     * @param   int param.order - 排序
     * @param   int param.gpu_enable - 启用GPU价格配置(0=关闭,1=开启) require
     * @param   string param.gpu_name - 启用GPU价格配置(0=关闭,1=开启) requireIf,gpu_enable=1
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function lineUpdate($param)
    {
        $line = $this->find($param['id']);
        if(empty($line)){
            return ['status'=>400, 'msg'=>lang_plugins('line_not_found')];
        }
        $exist = $this
                ->where('data_center_id', $line['data_center_id'])
                ->where('name', $param['name'])
                ->where('id', '<>', $param['id'])
                ->find();
        if(!empty($exist)){
            return ['status'=>400, 'msg'=>lang_plugins('line_name_exist')];
        }
        $param['bw_ip_group'] = $param['bw_ip_group'] ?? '';
        $param['defence_ip_group'] = $param['defence_ip_group'] ?? '';
        if(!is_numeric($param['order'])){
            unset($param['order']);
        }
        if($line['gpu_enable'] == 1 && $param['gpu_enable'] == 0){
            $use = RecommendConfigModel::where('line_id', $line['id'])->where('gpu_num', '>', 0)->value('id');
            if(!empty($use)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_line_cannot_disable_gpu_for_package_use')];
            }
        }
        
        $this->update($param, ['id'=>$line['id']], ['name','bw_ip_group','defence_enable','defence_ip_group','ip_enable','link_clone','order','gpu_enable','gpu_name']);

        $switch = [lang_plugins('switch_off'), lang_plugins('switch_on')];

        $des = [
            'name'              => lang_plugins('mf_cloud_line_name'),
            'bw_ip_group'       => lang_plugins('mf_cloud_line_bw_ip_group'),
            'defence_enable'    => lang_plugins('mf_cloud_line_defence_enable'),
            'defence_ip_group'  => lang_plugins('mf_cloud_line_defence_ip_group'),
            'ip_enable'         => lang_plugins('mf_cloud_line_ip_enable'),
            'link_clone'        => lang_plugins('mf_cloud_line_link_clone'),
            'gpu_enable'        => lang_plugins('mf_cloud_line_gpu_enable'),
            'gpu_name'          => lang_plugins('mf_cloud_line_gpu_name'),
        ];
        $old = $line->toArray();
        $old['defence_enable'] = $switch[ $old['defence_enable'] ];
        $old['ip_enable'] = $switch[ $old['ip_enable'] ];
        $old['link_clone'] = $switch[ $old['link_clone'] ];
        $old['gpu_enable'] = $switch[ $old['gpu_enable'] ];

        $param['defence_enable'] = $switch[ $param['defence_enable'] ];
        $param['ip_enable'] = $switch[ $param['ip_enable'] ];
        $param['link_clone'] = $switch[ $param['link_clone'] ];
        $param['gpu_enable'] = $switch[ $param['gpu_enable'] ];

        $description = ToolLogic::createEditLog($old, $param, $des);
        if(!empty($description)){
            $description = lang_plugins('log_mf_cloud_modify_line_success', ['{detail}'=>$description]);
            active_log($description);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-03
     * @title 删除线路
     * @desc 删除线路
     * @author hh
     * @version v1
     * @param   int id - 线路ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function lineDelete($id)
    {
        $line = $this->find($id);
        if(empty($line)){
            return ['status'=>400, 'msg'=>lang_plugins('line_not_found')];
        }
        $productId = DataCenterModel::where('id', $line['data_center_id'])->value('product_id') ?? 0;

        $recommendConfig = RecommendConfigModel::where('line_id', $id)->find();
        if(!empty($recommendConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('config_conflict_please_edit_recommend_config')];
        }
        $this->startTrans();
        try{
            $this->where('id', $id)->delete();

            // 获取线路配置
            $optionId = OptionModel::where('product_id', $productId)->whereIn('rel_type', [OptionModel::LINE_BW,OptionModel::LINE_FLOW,OptionModel::LINE_DEFENCE,OptionModel::LINE_IP,OptionModel::LINE_GPU])->where('rel_id', $id)->column('id');
            if(!empty($optionId)){
                OptionModel::whereIn('id', $optionId)->delete();
                PriceModel::where('product_id', $productId)->where('rel_type', PriceModel::REL_TYPE_OPTION)->whereIn('rel_id', $optionId)->delete();
            }
            // 删除限制
            ConfigLimitModel::where('type', 'line')->where('line_id', $id)->delete();

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('delete_fail')];
        }

        $description = lang_plugins('log_mf_cloud_delete_line_success', ['{name}'=>$line['name']]);
        active_log($description);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-14
     * @title 前台获取线路配置
     * @desc 前台获取线路配置
     * @author hh
     * @version v1
     * @param   int id - 线路ID require
     * @return  string bill_type - 计费类型(bw=带宽计费,flow=流量计费)
     * @return  string gpu_name - 显卡名称
     * @return  string bw[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int bw[].value - 带宽
     * @return  int bw[].min_value - 最小值
     * @return  int bw[].max_value - 最大值
     * @return  int bw[].step - 步长
     * @return  int flow[].value - 流量
     * @return  int defence[].value - 防御峰值(G)
     * @return  int ip[].value - IP数量
     * @return  int gpu[].value - 显卡数量
     */
    public function homeLineConfig($id)
    {
        $line = $this->find($id);
        if(empty($line)){
            return (object)[];
        }
        $data = [
            'bill_type' => $line['bill_type'],
            'gpu_name'  => '',
        ];
        $productId = DataCenterModel::where('id', $line['data_center_id'])->value('product_id') ?? 0;

        if($line['bill_type'] == 'bw'){
            // 带宽计费
            $bw = OptionModel::field('type,value,min_value,max_value,step')->where('product_id', $productId)->where('rel_type', OptionModel::LINE_BW)->where('rel_id', $id)->order('value,min_value', 'asc')->select()->toArray();
            $data['bw'] = $bw;
        }else{
            // 流量计费
            $flow = OptionModel::field('value')->where('product_id', $productId)->where('rel_type', OptionModel::LINE_FLOW)->where('rel_id', $id)->order('value', 'asc')->select()->toArray();
            $data['flow'] = $flow;
        }
        if($line['defence_enable'] == 1){
            $data['defence'] = OptionModel::field('value')->where('product_id', $productId)->where('rel_type', OptionModel::LINE_DEFENCE)->where('rel_id', $id)->order('value', 'asc')->select()->toArray();
        }
        if($line['ip_enable'] == 1){
            $data['ip'] = OptionModel::field('value')->where('product_id', $productId)->where('rel_type', OptionModel::LINE_IP)->where('rel_id', $id)->order('value', 'asc')->select()->toArray();
        }
        if($line['gpu_enable'] == 1){
            $gpu = OptionModel::field('value')->where('product_id', $productId)->where('rel_type', OptionModel::LINE_GPU)->where('rel_id', $id)->order('value', 'asc')->select()->toArray();
            if(!empty($gpu)){
                $data['gpu_name'] = $line['gpu_name'];
                $data['gpu'] = $gpu;
            }
        }
        return $data;
    }

    /**
     * 时间 2024-02-19
     * @title 线路名称获取器
     * @desc  线路名称获取器
     * @author hh
     * @version v1
     * @param   string value - 线路名称 require
     * @return  string
     */
    public function getNameAttr($value)
    {
        if(app('http')->getName() == 'home'){
            $multiLanguage = hook_one('multi_language', [
                'replace' => [
                    'name' => $value,
                ],
            ]);
            if(isset($multiLanguage['name'])){
                $value = $multiLanguage['name'];
            }
        }
        return $value;
    }

}