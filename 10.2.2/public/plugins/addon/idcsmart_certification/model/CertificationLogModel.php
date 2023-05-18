<?php
namespace addon\idcsmart_certification\model;

use addon\idcsmart_certification\logic\IdcsmartCertificationLogic;
use app\common\logic\ModuleLogic;
use app\common\logic\UploadLogic;
use app\common\model\ClientModel;
use app\common\model\HostModel;
use think\db\Query;
use think\Model;

/**
 * @title 实名认证记录模型
 * @desc 实名认证记录模型
 * @use addon\idcsmart_certification\model\CertificationLogModel
 */
class CertificationLogModel extends Model
{
	protected $name = 'addon_idcsmart_certification_log';

	// 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'client_id'     => 'int',
        'card_name'     => 'string',
        'card_type'     => 'string',
        'card_number'   => 'string',
        'phone'         => 'string',
        'status'        => 'int',
        'company'       => 'string',
        'company_organ_code' => 'string',
        'certify_id'    => 'string',
        'auth_fail'     => 'string',
        'img'           => 'string',
        'create_time'   => 'int',
        'type'          => 'int',
        'plugin_name'   => 'string',
        'custom_fields_json' => 'string',
        'notes'         => 'string',
        'refresh'       => 'int',
    ];

    public $isAdmin=false;

    /**
     * 时间 2022-9-23
     * @title 实名认证列表
     * @desc 实名认证列表
     * @author wyh
     * @version v1
     * @param string keywords - 关键字
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 实名认证列表
     * @return int list[].id - ID
     * @return int list[].username - 申请人
     * @return int list[].card_name - 提交人姓名
     * @return int list[].company - 公司
     * @return int list[].type - 认证类型1个人，2企业，3个人转企业
     * @return int list[].status - 1已认证，2未通过，3待审核，4已提交资料
     * @return int list[].create_time - 提交时间
     * @return int list[].company_organ_code - 营业执照号
     * @return int count - 实名认证总数
     */
    public function certificationList($param)
    {
        if (!isset($param['orderby']) || !in_array($param['orderby'],['id','card_name','type','status','create_time'])){
            $param['orderby'] = 'cl.id';
        }else{
            $param['orderby'] = 'cl.'.$param['orderby'];
        }

        $idsPerson = $this->field('max(id) as ids')
            ->where('type',1)
            ->group('client_id')
            ->order('id','desc')
            ->select()
            ->toArray();

        $idsPerson && $idsPerson = array_column($idsPerson, 'ids');

        $idsCompany =  $this->field('max(id) as ids')
            ->whereIn('type',[2,3])
            ->group('client_id')
            ->order('id','desc')
            ->select()
            ->toArray();
        $idsCompany && $idsCompany = array_column($idsCompany,'ids');

        $ids = array_merge($idsPerson,$idsCompany);

        $where = function (Query $query) use($param,$ids) {
            if(!empty($param['keywords'])){
                $query->where('cl.id|cl.card_name|cl.company', 'like', "%{$param['keywords']}%");
            }

            $query->whereIn('cl.id',$ids);

        };

        $logs = $this->alias('cl')
            ->field('cl.id,cl.card_name,cl.company,cl.type,cl.status,cl.create_time,c.username,cl.client_id,cl.auth_fail,cl.company_organ_code')
            ->leftJoin('client c','c.id=cl.client_id')
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        $count = $this->alias('cl')
            ->leftjoin('client c','c.id=cl.client_id')
            ->where($where)
            ->count();

        return ['list'=>$logs,'count'=>$count];
    }

    /**
     * 时间 2022-9-23
     * @title 获取实名认证
     * @desc 获取实名认证
     * @author wyh
     * @version v1
     * @param int id - 实名认证ID required
     * @return object log - 实名认证
     * @return int log.id - ID
     * @return string log.name - 申请人
     * @return string log.company - 公司
     * @return int log.create_time - 申请时间
     * @return string log.title - 认证方式
     * @return string log.card_name - 认证名称
     * @return string log.card_type - 卡类型:id_card身份证,hk_macao_residence_permits港澳居住证,hk_macao_ entry_permit港澳通行证,taiwan_residence_permits台湾居住证,taiwan_entry_permit台湾通行证
     * @return string log.card_number - 证件号
     * @return array log.img - 图片地址,依次为:正,反,营业执照
     * @return array log.company_organ_code - 营业执照号
     */
    public function certificationIndex($param)
    {
        $id = intval($param['id']);

        $log = $this->alias('cl')
            ->field('cl.id,c.username,cl.company,cl.create_time,p.title,cl.card_name,cl.card_type,cl.card_number,cl.img,cl.company_organ_code,cl.type,cl.plugin_name,cl.custom_fields_json')
            ->leftJoin('plugin p','p.name=cl.plugin_name')
            ->leftJoin('client c','c.id=cl.client_id')
            ->where('cl.id',$id)
            ->find();

        if (empty($log)){
            return ['status'=>400,'msg'=>lang_plugins('id_error')];
        }

        // 做特殊处理
        if (in_array($log['type'],[2,3])){
            $customFields = json_decode($log['custom_fields_json'],true);
            $log['card_name'] = $customFields['custom_fields1']??$log['card_name'];
            $log['card_number'] = $customFields['custom_fields2']??$log['card_number'];
        }
        unset($log['plugin_name'],$log['custom_fields_json'],$log['type']);

        if (!empty($log['img'])){
            $imgs = explode(',',$log['img']);

            $certificationUrl = IdcsmartCertificationLogic::getDefaultConfig('get_certification_upload_url');

            $tmp  = [];
            foreach ($imgs as $img){
                $imgUrl = $certificationUrl . $img;
                $tmp[] = $imgUrl;
            }

            $log['img'] = $tmp;
        }else{
            $log['img'] = [];
        }

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'log' => $log?:(object)[]
            ]
        ];

        return $result;
    }

    /**
     * 时间 2022-9-23
     * @title 通过
     * @desc 通过
     * @author wyh
     * @version v1
     * @param int id - 实名认证ID required
     */
    public function approve($param)
    {
        $id = intval($param['id']);

        $log = $this->find($id);

        if (empty($log)){
            return ['status'=>400,'msg'=>lang_plugins('id_error')];
        }

        if (!in_array($log['status'],[2,3,4])){
            return ['status'=>400,'msg'=>lang_plugins('certification_approve')];
        }

        $this->startTrans();

        try{
            $log->save([
                'status' => 1,
            ]);

            if ($log['type'] == 1){ # 个人认证
                $CertificationPersonModel = new CertificationPersonModel();

                $CertificationPersonModel->where('client_id',$log['client_id'])
                    ->update([
                        'status' => 1,
                        'update_time' => time()
                    ]);

            }else{ # 企业认证
                $CertificationCompanyModel = new CertificationCompanyModel();
                $CertificationCompanyModel->where('client_id',$log['client_id'])
                    ->update([
                        'status' => 1,
                        'update_time' => time()
                    ]);
            }

            # 自动更新姓名
            if (IdcsmartCertificationLogic::getDefaultConfig('certification_update_client_name') && $log['type'] == 1){
                $ClientModel = new ClientModel();
                $ClientModel->where('id',$log['client_id'])->update([
                    'username' => $log['card_name'],
                    'update_time' => time()
                ]);
            }

            # 解除暂停
            $this->certificationUnsuspend($log['client_id']);

            $clientId = $log['client_id'];
            $client = ClientModel::find($clientId);
            # 记录日志
            active_log(lang_plugins('addon_idcsmart_certification_approve', ['{admin}'=>'admin#'.request()->admin_id.'#'.request()->admin_name.'#','{client}'=>'client#'.$clientId.'#'.$client['username'].'#']), 'addon_idcsmart_certification_log', $id, $clientId);

            if(IdcsmartCertificationLogic::getDefaultConfig('certification_notice')){
                //实名认证通过通知短信添加到任务队列
                add_task([
                    'type' => 'sms',
                    'description' => '实名认证通过,发送短信',
                    'task_data' => [
                        'name'=>'idcsmart_certification_pass',//发送动作名称
                        'client_id'=>get_client_id(),//客户ID
                        'template_param'=>[
                        ],
                    ],
                ]);
                //实名认证通过通知添加到任务队列
                add_task([
                    'type' => 'email',
                    'description' => '实名认证通过,发送邮件',
                    'task_data' => [
                        'name'=>'idcsmart_certification_pass',//发送动作名称
                        'client_id'=>get_client_id(),//客户ID
                        'template_param'=>[
                        ],
                    ],
                ]);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-9-23
     * @title 驳回
     * @desc 驳回
     * @author wyh
     * @version v1
     * @param int id - 实名认证ID required
     */
    public function reject($param)
    {
        $id = intval($param['id']);

        $log = $this->find($id);

        if (empty($log)){
            return ['status'=>400,'msg'=>lang_plugins('id_error')];
        }

        if (!in_array($log['status'],[1,3,4])){
            return ['status'=>400,'msg'=>lang_plugins('certification_reject')];
        }

        $this->startTrans();

        try{
            $log->save([
                'status' => 2,
            ]);

            if ($log['type'] == 1){ # 个人认证
                $CertificationPersonModel = new CertificationPersonModel();

                $CertificationPersonModel->where('client_id',$log['client_id'])
                    ->update([
                        'status' => 2,
                        'update_time' => time()
                    ]);

            }else{ # 企业认证
                $CertificationCompanyModel = new CertificationCompanyModel();
                $CertificationCompanyModel->where('client_id',$log['client_id'])
                    ->update([
                        'status' => 2,
                        'update_time' => time()
                    ]);
            }

            $clientId = $log['client_id'];
            $client = ClientModel::find($clientId);
            # 记录日志
            active_log(lang_plugins('addon_idcsmart_certification_reject', ['{admin}'=>'admin#'.request()->admin_id.'#'.request()->admin_name.'#','{client}'=>'client#'.$clientId.'#'.$client['username'].'#']), 'addon_idcsmart_certification_log', $id, $clientId);


            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang('success_message')];
    }

    /**
     * 时间 2022-9-23
     * @title 获取实名认证信息
     * @desc 获取实名认证信息
     * @author wyh
     * @version v1
     * @return int certification_open - 实名认证是否开启:1开启默认,0关
     * @return int certification_upload - 是否需要上传证件照:1是,0否默认
     * @return int certification_uncertified_cannot_buy_product - 未认证无法购买产品:1是,0否默认
     * @return int is_certification - 是否实名认证:1是,0否默认
     * @return object person - 个人认证信息
     * @return string person.username - 申请人
     * @return string person.company - 公司
     * @return string person.card_name - 姓名(带星号显示)
     * @return string person.card_number - 证件号(带星号显示)
     * @return string person.status - 状态:1已认证，2未通过，3待审核，4已提交资料
     * @return object company - 企业认证信息
     * @return string company.username - 申请人
     * @return string company.company - 公司
     * @return string company.card_name - 姓名(带星号显示)
     * @return string company.card_number - 证件号(带星号显示)
     * @return string company.certification_company - 实名认证企业
     * @return string company.company_organ_code - 企业代码
     * @return string company.status - 状态:1已认证，2未通过，3待审核，4已提交资料
     */
    public function certificationInfo($param)
    {
        $clientId = $param['client_id']??get_client_id();

        $config = IdcsmartCertificationLogic::getDefaultConfig();
        $configuration = [
            'certification_open' => $config['certification_open']??0,
            'certification_upload' => $config['certification_upload']??0,
            'certification_uncertified_cannot_buy_product' => $config['certification_uncertified_cannot_buy_product']??0,
        ];

        $CertificationPersonModel = new CertificationPersonModel();
        $person = $CertificationPersonModel->alias('cp')
            ->field('c.username,c.company,cp.card_name,cp.card_number,cp.status,cp.create_time')
            ->leftJoin('client c','c.id=cp.client_id')
            ->where('cp.client_id',$clientId)
            ->withAttr('card_name',function ($value){
                if (!empty($value)){
                    return mb_substr($value,0,1) . '**';
                }
                return $value;
            })
            ->withAttr('card_number',function ($value){
                if (!empty($value) && strlen($value)>1){
                    return mb_substr($value,0,1) . str_repeat('*',strlen($value)-2) . mb_substr($value,strlen($value)-1,1);
                }
                return $value;
            })
            ->find();

        $CertificationLogModel = new CertificationLogModel();
        $lastPersonLog = $CertificationLogModel->where('client_id',$clientId)
            ->where('type',1)
            ->order('id','desc')
            ->find();
        if (!empty($person)){
            $person->create_time = $lastPersonLog['create_time']??$person->create_time;
        }

        $CertificationCompanyModel = new CertificationCompanyModel();
        $company = $CertificationCompanyModel->alias('cc')
            ->field('c.username,c.company,cc.card_name,cc.card_number,cc.company as certification_company,cc.company_organ_code,cc.status,cc.create_time')
            ->leftJoin('client c','c.id=cc.client_id')
            ->where('cc.client_id',$clientId)
            ->withAttr('card_name',function ($value){
                if (!empty($value)){
                    return mb_substr($value,0,1) . '**';
                }
                return $value;
            })
            ->withAttr('card_number',function ($value){
                if (!empty($value)){
                    return mb_substr($value,0,1) . str_repeat('*',strlen($value)-2) . mb_substr($value,strlen($value)-1,1);
                }
                return $value;
            })
            ->find();
        $lastCompanyLog = $CertificationLogModel->where('client_id',$clientId)
            ->whereIn('type',[2,3])
            ->order('id','desc')
            ->find();
        if (!empty($company)){
            $company->create_time = $lastCompanyLog['create_time']??$company->create_time;
        }

        $data = [
            'is_certification' => $this->checkCertification($clientId),
            'person' => $person??(object)[],
            'company' => $company??(object)[],
        ];

        $data = array_merge($configuration,$data);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];

        return $result;
    }

    /**
     * 时间 2022-9-23
     * @title 获取实名认证自定义字段
     * @desc 获取实名认证自定义字段
     * @author wyh
     * @version v1
     * @param string name - 实名接口标识 required
     * @param string type - 验证类型:person个人,company企业 required
     * @return array
     * @return string title - 名称
     * @return string type -  字段类型:text文本,select下拉,file文件
     * @return string options - 字段类型为checkbox复选框,select下拉,radio单选时的选项:选项也是键值,传键
     * @return string tip - 提示
     * @return string required - 是否必填:bool
     * @return string field - 字段名,提交时的键值
     */
    public function getCertificationCustomFields($name,$type='person')
    {
        $name = parse_name($name,1);

        $class = get_plugin_class($name, 'certification');

        $customfields_filter = [];

        $method = $name . 'CollectionInfo';

        if (method_exists($class,$method)){
            $obj = new $class;
            $customfields = $obj->$method($type);
            foreach ($customfields as $key=>$customfield){
                $customfield['field'] = $key;
                $customfields_filter[] = $customfield; # 整合自定义字段
            }
        }

        return $customfields_filter;
    }

    /**
     * 时间 2022-9-23
     * @title 个人认证
     * @desc 个人认证
     * @author wyh
     * @version v1
     * @param string plugin_name - 实名接口 required
     * @param string card_name - 姓名 required
     * @param string card_type - 证件类型:1大陆,0非大陆 required
     * @param string card_number - 证件号码 required
     * @param string img_one - 身份证正面照,调系统上传文件接口(console/v1/upload获取到savename)
     * @param string img_two - 身份证反面照,调系统上传文件接口(console/v1/upload获取到savename)
     * @param object custom_fields - 其他自定义字段,例{"cert_type":"IDENTITY_CARD"},文件类型先调系统上传文件接口(console/v1/upload获取到savename),
     */
    public function certificationPerson($param)
    {
        $this->startTrans();

        try{
            $time = time();

            $config = IdcsmartCertificationLogic::getDefaultConfig();

            $clientId = $this->isAdmin?$param['client_id']:get_client_id();
            # 验证身份证号是否被其他人使用
            $this->checkOtherClientUsed($param['card_number'],$clientId);
            # 验证是否企业认证
            $this->checkCompanyCertification($clientId);
            # 验证自定义字段
            $customFieldsFilter = $this->checkCustomFields($param['plugin_name'],$param,'person');
            # 手机一致性
            $this->checkPhoneConsistency($param['phone']??'',$clientId);
            # 验证是否已认证
            $CertificationPersonModel = new CertificationPersonModel();
            $certificationPerson = $CertificationPersonModel->where('client_id',$clientId)->find();
            if (!empty($certificationPerson) && $certificationPerson['status']==1){
                throw new \Exception(lang_plugins('certification_person_completed'));
            }

            # 是否大陆
            $cardType = isset($param['card_type']) ? intval($param['card_type']) : 1;

            # 需要上传图片:后台设置必传,或者非大陆
            $imgLog = '';
            if ($config['certification_upload']){
                if (!isset($param['img_one']) || empty($param['img_one'])){
                    throw new \Exception(lang_plugins('certification_img_one'));
                }
                if (!isset($param['img_two']) || empty($param['img_two'])){
                    throw new \Exception(lang_plugins('certification_img_two'));
                }
                $UploadLogic = new UploadLogic($config['certification_upload_url']);

                $imgOne = $UploadLogic->moveTo($param['img_one']);
                if (isset($imgOne['error'])){
                    throw new \Exception($imgOne['error']);
                }

                $imgTwo = $UploadLogic->moveTo($param['img_two']);
                if (isset($imgTwo['error'])){
                    throw new \Exception($imgTwo['error']);
                }
                $imgLog = $param['img_one'] . ',' .$param['img_two'];
            }

            $person = [
                'client_id' => $clientId,
                'card_name' => $param['card_name'],
                'card_type' => $cardType,
                'card_number' => $param['card_number'],
                'phone' => $param['phone']??'',
                'status' => 4,//$cardType?4:3, # 提交资料/待审核
                'img_one' => $param['img_one']??'',
                'img_two' => $param['img_two']??'',
                'img_three' => '',
                'certify_id' => '',
                'auth_fail' => '',
            ];
            # 合并自定义字段
            $person = array_merge($person,$customFieldsFilter);

            if (!empty($certificationPerson)){
                $person['update_time'] = $time;
                $certificationPerson->save($person);
            }else{
                $person['create_time'] = $time;
                $CertificationPersonModel->insert($person);
            }

            $log = [
                'client_id' => $clientId,
                'card_name' => $param['card_name'],
                'card_type' => $cardType,
                'card_number' => $param['card_number'],
                'phone' => $param['phone']??'',
                'status' => 4,//$cardType?4:3, # 提交资料/待审核
                'company' => $param['company']??'',
                'company_organ_code' => $param['company_organ_code']??'',
                'certify_id' => '',
                'auth_fail' => '',
                'img' => $imgLog,
                'create_time' => $time,
                'type' => 1, # 个人认证
                'plugin_name' => $param['plugin_name'],
                'custom_fields_json' => json_encode($customFieldsFilter),
                'notes' => ''
            ];

            $id = $this->insertGetId($log);

            # 记录日志
            active_log(lang_plugins('addon_idcsmart_certification_post', ['{client}'=>'client#'.$clientId.'#'.request()->client_name.'#']), 'addon_idcsmart_certification_log', $id, $clientId);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-9-24
     * @title 企业认证
     * @desc 企业认证
     * @url /console/v1/certification/company
     * @method  post
     * @author wyh
     * @version v1
     * @param string plugin_name - 实名接口 required
     * @param string card_name - 姓名 required
     * @param string card_type - 证件类型:1大陆,0非大陆 required
     * @param string card_number - 证件号码 required
     * @param string company - 公司 required
     * @param string company_organ_code - 公司代码 required
     * @param string phone - 手机号
     * @param string img_one - 身份证正面照,调系统上传文件接口(console/v1/upload获取到savename)
     * @param string img_two - 身份证反面照,调系统上传文件接口(console/v1/upload获取到savename)
     * @param string img_three - 营业执照,调系统上传文件接口(console/v1/upload获取到savename)
     * @param object custom_fields - 其他自定义字段,例{"cert_type":"IDENTITY_CARD"},文件类型先调系统上传文件接口(console/v1/upload获取到savename),
     */
    public function certificationCompany($param)
    {
        $this->startTrans();

        try{
            $time = time();

            $config = IdcsmartCertificationLogic::getDefaultConfig();

            $clientId = $this->isAdmin?$param['client_id']:get_client_id();

            # 个人转企业
            $convert = isset($param['convert'])?intval($param['convert']):0;
            if ($convert){
                # 验证是否已个人认证
                $CertificationPersonModel = new CertificationPersonModel();
                $certificationPerson = $CertificationPersonModel->where('client_id',$clientId)
                    ->where('status',1)
                    ->find();
                if (empty($certificationPerson)){
                    throw new \Exception(lang_plugins('certification_person_uncompleted'));
                }
            }

            # 验证身份证号是否被其他人使用
            $this->checkOtherClientUsed($param['card_number'],$clientId);
            # 验证是否企业认证
            # $this->checkCompanyCertification($clientId);
            # 验证自定义字段
            $customFieldsFilter = $this->checkCustomFields($param['plugin_name'],$param,'company');
            # 手机一致性
            $this->checkPhoneConsistency($param['phone']??'',$clientId,'company');
            # 验证是否已认证
            $CertificationCompanyModel = new CertificationCompanyModel();
            $certificationCompany = $CertificationCompanyModel->where('client_id',$clientId)->find();
            if (!empty($certificationCompany) && $certificationCompany['status']==1){
                throw new \Exception(lang_plugins('certification_company_completed'));
            }

            # 是否大陆
            $cardType = isset($param['card_type']) ? intval($param['card_type']) : 1;

            # 需要上传图片:后台设置必传,或者非大陆
            $imgLog = '';
            if ($config['certification_upload']){
                /*if (!isset($param['img_one']) || empty($param['img_one'])){
                    throw new \Exception(lang_plugins('certification_img_one'));
                }
                if (!isset($param['img_two']) || empty($param['img_two'])){
                    throw new \Exception(lang_plugins('certification_img_two'));
                }*/
                if (!isset($param['img_three']) || empty($param['img_three'])){
                    throw new \Exception(lang_plugins('certification_img_three'));
                }
                $UploadLogic = new UploadLogic($config['certification_upload_url']);

                /*$imgOne = $UploadLogic->moveTo($param['img_one']);
                if (isset($imgOne['error'])){
                    throw new \Exception($imgOne['error']);
                }

                $imgTwo = $UploadLogic->moveTo($param['img_two']);
                if (isset($imgTwo['error'])){
                    throw new \Exception($imgTwo['error']);
                }*/

                $imgThree = $UploadLogic->moveTo($param['img_three']);
                if (isset($imgThree['error'])){
                    throw new \Exception($imgThree['error']);
                }
                $imgLog = /*$param['img_one'] . ',' .$param['img_two'] . ',' . */$param['img_three'];
            }

            $company = [
                'client_id' => $clientId,
                'card_name' => $param['card_name'],
                'card_type' => $cardType,
                'card_number' => $param['card_number'],
                'phone' => $param['phone']??'',
                'status' => 4,//$cardType?4:3, # 提交资料/待审核
                'company' => $param['company']??'',
                'company_organ_code' => $param['company_organ_code']??'',
                'img_one' => $param['img_one']??'',
                'img_two' => $param['img_two']??'',
                'img_three' => $param['img_three']??'',
                'certify_id' => '',
                'auth_fail' => '',
            ];
            # 合并自定义字段
            $company = array_merge($company,$customFieldsFilter);

            if (!empty($certificationCompany)){
                $company['update_time'] = $time;
                $certificationCompany->save($company);
            }else{
                $company['create_time'] = $time;
                $CertificationCompanyModel->insert($company);
            }

            $log = [
                'client_id' => $clientId,
                'card_name' => $param['card_name'],
                'card_type' => $cardType,
                'card_number' => $param['card_number'],
                'phone' => $param['phone']??'',
                'status' => 4,//$cardType?4:3, # 提交资料/待审核
                'company' => $param['company']??'',
                'company_organ_code' => $param['company_organ_code']??'',
                'certify_id' => '',
                'auth_fail' => '',
                'img' => $imgLog,
                'create_time' => $time,
                'type' => $convert?3:2, # 3个人转企业,2企业认证
                'plugin_name' => $param['plugin_name'],
                'custom_fields_json' => json_encode($customFieldsFilter),
                'notes' => ''
            ];

            $id = $this->insertGetId($log);

            # 记录日志
            active_log(lang_plugins('addon_idcsmart_certification_post', ['{client}'=>'client#'.$clientId.'#'.request()->client_name.'#']), 'addon_idcsmart_certification_log', $id, $clientId);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-9-24
     * @title 实名认证验证页面
     * @desc 实名认证验证页面
     * @author wyh
     * @version v1
     * @return array
     * @return string code - status==400时,返回data.code：code==10000时,重定向至提交资料页面;code==10001时,调基础信息/console/v1/certification/info,并加载相应页面,比如已通过页面/待审核页面/未通过页面
     * @return string html - status==200时,返回data.html文档,由实名接口放回(返回页面正确,默认认证方式的html里需要轮询调接口,/certification/idcsmartali/index/status获取状态);同时轮询调系统状态接口/console/v1/certification/status
     */
    public function certificationAuth()
    {
        $certificationLog = $this->where('client_id',get_client_id())
            ->order('id','desc')
            ->find();

        if (empty($certificationLog)){
            return ['status'=>400,'msg'=>lang_plugins('certification_info_post_again'),'data'=>['code'=>10000]];
        }

        $type = $certificationLog['type'];

        if ($type == 1){ # 1个人认证
            $action = 'person';
            $CertificationPersonModel = new CertificationPersonModel();
            $tmp = $CertificationPersonModel->where('client_id',get_client_id())->find();
        }else{ # 2企业，3个人转企业
            $action = 'company';
            $CertificationCompanyModel = new CertificationCompanyModel();
            $tmp = $CertificationCompanyModel->where('client_id',get_client_id())->find();
        }
        if (empty($tmp)){
            return ['status'=>400,'msg'=>lang_plugins('certification_info_post_again'),'data'=>['code'=>10000]];
        }

        $config = IdcsmartCertificationLogic::getDefaultConfig();

        if (in_array($certificationLog['status'],[2,3,4])){ # 已提交资料
            $postData = [
                'card_type' => $certificationLog['card_type'],
                'name' => $certificationLog['card_name'],
                'card' => $certificationLog['card_number'],
                'phone' => $certificationLog['phone'],
                'company_name' => $certificationLog['company'],
                'company_organ_code' => $certificationLog['company_organ_code'],
            ];

            $plugin = $certificationLog['plugin_name'];

            $customFields = $this->getCertificationCustomFields($plugin,$action);
            $customFieldData = [];
            if (!empty($customFields)){
                foreach ($customFields as $key=>$customField){
                    if ($customField['type'] == 'file'){
                        if (!empty($tmp['custom_fields' . ($key+1)])){
                            $customFieldData[$customField['field']] = $config['certification_upload'] . $tmp['custom_fields' . ($key+1)];
                        }else{
                            $customFieldData[$customField['field']] = '';
                        }
                    }else {
                        $customFieldData[$customField['field']] = $tmp['custom_fields' . ($key+1)]?:"";
                    }
                }
            }
            $postData = array_merge($postData,$customFieldData);

            $html = plugin_reflection($plugin,$postData,'certification',$action);

            $result = [
                'status' => 200,
                'msg' => lang_plugins('success_message'),
                'data' => [
                    'html' => $html
                ]
            ];

            return $result;
        }

        return ['status'=>400,'msg'=>lang_plugins('error_message'),'data'=>['code'=>10001]];
    }

    /**
     * 时间 2022-9-24
     * @title 获取实名认证状态
     * @desc 获取实名认证状态,在验证页面轮询调用
     * @author wyh
     * @version v1
     * @return array
     * @return int status - 当status==400时,表示无认证信息,直接跳转至提交资料页面;
     * @return string code - 当status==200,code:1通过,2未通过,3待审核,4提交资料;code==2,refersh==0时继续轮询调接口,其他所有情况都终止轮询;
     */
    public function certificationStatus()
    {
        $clientId = get_client_id();

        $certificationLog = $this->where('client_id',$clientId)->order('id','desc')->find();

        if (empty($certificationLog)){
            return ['status'=>400,'msg'=>lang_plugins('error_message')];
        }

        $config = IdcsmartCertificationLogic::getDefaultConfig();

        # 通过或者未通过时,
        $status = $certificationLog['status'];
        if ($status==1){ # 通过

            # 开启人工复审或者为企业认证时,需要人工审核
            if ($config['certification_approval'] || in_array($certificationLog['type'],[2,3])){
                $status = 3;

                if ($certificationLog['type']==1){
                    $this->updateCertificationPerson([
                        'client_id' => $clientId,
                        'status' => $status
                    ]);
                }else{
                    $this->updateCertificationCompany([
                        'client_id' => $clientId,
                        'status' => $status
                    ]);
                }

            }
        }

        if ($status==1){
            # 自动更新姓名(个人认证)
            if ($config['certification_update_client_name'] && $certificationLog['type']==1){
                $ClientModel = new ClientModel();
                $ClientModel->where('id',$clientId)->update([
                    'username' => $certificationLog['card_name'],
                    'update_time' => time()
                ]);
            }

            # 实名认证完成,自动解除暂停产品
            $this->certificationUnsuspend($clientId);
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['code'=>$status,'refresh'=>$certificationLog['refresh']]];
    }

    # 实名认证完成,自动解除暂停产品
    public function certificationUnsuspend($client_id)
    {
        if ($this->checkCertification($client_id)){

            $HostModel = new HostModel();

            $where = function (Query $query){
                $query->where('due_time',time())
                    ->whereOr('billing_cycle','free')
                    ->whereOr('billing_cycle','onetime');
            };

            $hosts = $HostModel->where('status','Suspended')
                ->where('client_id',$client_id)
                ->where('suspend_type','certification_not_complete')
                ->where($where)
                ->select();

            $ModuleLogic = new ModuleLogic();

            foreach ($hosts as $host){
                $res = $ModuleLogic->unsuspendAccount($host);
                if ($res['status'] == 200){
                    active_log(lang_plugins('module_unsuspend_success'),'host',$host->id);
                }else{
                    active_log(lang_plugins('module_unsuspend_fail').":".$res['msg'],'host',$host->id);
                }
            }
        }

        return true;
    }
    
    # 更新个人实名认证信息
    public function updateCertificationPerson($param)
    {
        $this->startTrans();

        try{
            $clientId = $param['client_id']??0;

            $CertificationPersonModel = new CertificationPersonModel();

            $person = [
                'status' => $param['status']??4,
                'auth_fail' => $param['auth_fail']??'',
                'update_time' => time()
            ];

            $log = [
                'status' => $param['status']??4,
                'auth_fail' => $param['auth_fail']??'',
                'notes' =>$param['notes']??'',
            ];

            if (isset($param['certify_id']) && !empty($param['certify_id'])){
                $person['certify_id'] = $param['certify_id'];
                $log['certify_id'] = $param['certify_id'];
            }
            $CertificationPersonModel->where('client_id',$clientId)->update($person);

            if (isset($param['refresh'])){
                $log['refresh'] = intval($param['refresh']);
            }

            $lastLog = $this->where('client_id',$clientId)
                ->where('type',1)
                ->order('id','desc')
                ->find();

            $lastLog->save($log);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return false;
        }

        return true;
    }

    # 更新企业实名认证信息
    public function updateCertificationCompany($param)
    {
        $this->startTrans();

        try{
            $clientId = $param['client_id']??0;

            $CertificationCompanyModel = new CertificationCompanyModel();

            $company = [
                'status' => $param['status']??4,
                'auth_fail' => $param['auth_fail']??'',
                'update_time' => time()
            ];

            $log = [
                'status' => $param['status']??4,
                'auth_fail' => $param['auth_fail']??'',
                'notes' =>$param['notes']??''
            ];

            if (isset($param['certify_id']) && !empty($param['certify_id'])){
                $company['certify_id'] = $param['certify_id'];
                $log['certify_id'] = $param['certify_id'];
            }
            $CertificationCompanyModel->where('client_id',$clientId)->update($company);

            if (isset($param['refresh'])){
                $log['refresh'] = intval($param['refresh']);
            }

            $lastLog = $this->where('client_id',$clientId)
                ->whereIn('type',[2,3])
                ->order('id','desc')
                ->find();

            $lastLog->save($log);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return false;
        }

        return true;
    }

    # 检查客户是否实名认证
    public function checkCertification($client_id)
    {
        $CertificationPersonModel = new CertificationPersonModel();

        $person = $CertificationPersonModel->where('client_id',$client_id)
            ->where('status',1)
            ->find();

        $CertificationCompanyModel = new CertificationCompanyModel();
        $company = $CertificationCompanyModel->where('client_id',$client_id)
            ->where('status',1)
            ->find();
        if (!empty($person) || !empty($company)){
            return true;
        }

        return false;
    }

    # 检查身份证是否被他人使用(个人和企业分开)
    protected function checkOtherClientUsed($card_number,$client_id,$type='person')
    {
        if ($type == 'person'){
            $CertificationPersonModel = new CertificationPersonModel();
            $tmp = $CertificationPersonModel->where('card_number',$card_number)
                ->where('status','<>',2)
                ->where('client_id','<>',$client_id)
                ->find();
        }else{
            $CertificationCompanyModel = new CertificationCompanyModel();
            $tmp = $CertificationCompanyModel->where('card_number',$card_number)
                ->where('status','<>',2)
                ->where('client_id','<>',$client_id)
                ->find();
        }

        if (!empty($tmp)){
            throw new \Exception(lang_plugins('certification_card_number_other_client_used'));
        }

        return true;
    }

    # 验证此用户如果企业认证通过，则无法申请个人认证信息
    protected function checkCompanyCertification($client_id)
    {
        $CertificationCompanyModel = new CertificationCompanyModel();
        $certificationCompany = $CertificationCompanyModel->where('client_id',$client_id)->find();
        if (!empty($certificationCompany) && $certificationCompany['status']!=2){
            if ($certificationCompany['status'] == 1){
                throw new \Exception(lang_plugins('certification_company_completed'));
            }elseif ($certificationCompany['status'] == 3){
                throw new \Exception(lang_plugins('certification_company_pending'));
            }elseif ($certificationCompany['status'] == 4){
                throw new \Exception(lang_plugins('certification_company_post'));
            }
        }

        return true;
    }

    # 验证自定义字段
    protected function checkCustomFields($plugin,$param,$type='person')
    {
        $customFields = $this->getCertificationCustomFields($plugin,$type);

        $customFieldsFilter = [];

        $config = IdcsmartCertificationLogic::getDefaultConfig();

        if (!empty($customFields)){

            $i = 0;

            foreach ($customFields as $customField){
                if ($customField['type'] == 'text'){
                    if ($customField['required'] && (!isset($param['custom_fields'][$customField['field']]) || empty($param['custom_fields'][$customField['field']]))){ # text必填 值为空
                        throw new \Exception($customField['title'] . lang_plugins('certification_must'));
                    }

                } elseif($customField['type'] == 'file'){ # 文件类型

                    if ($customField['required'] && (!isset($param['custom_fields'][$customField['field']]) || empty($param['custom_fields'][$customField['field']]))) {
                        throw new \Exception($customField['title'] . lang_plugins('certification_upload'));
                    }

                    $UploadLogic = new UploadLogic($config['certification_upload_url']);

                    if (isset($param['custom_fields'][$customField['field']]) && !empty($param['custom_fields'][$customField['field']])){
                        $res = $UploadLogic->moveTo($param['custom_fields'][$customField['field']]);
                        if (isset($res['error'])){
                            throw new \Exception($res['error']);
                        }
                    }

                } elseif ($customField['type'] == 'select' && !in_array($param['custom_fields'][$customField['field']]??"",array_keys($customField['options']))){ # select类型
                    throw new \Exception(lang_plugins('certification_customfields_select_options',['{title}'=>$customField['title'],'{options}'=>implode(',',array_values($customField['options']))]));
                }

                $i++;
                # 自定义字段存储 至多支持10个自定义字段
                if ($i <= 10){
                    $customFieldsFilter['custom_fields' . $i] = $param['custom_fields'][$customField['field']]??'';
                }
            }
        }

        return $customFieldsFilter;
    }

    # 验证客户手机一致性(仅验证个人认证)
    protected function checkPhoneConsistency($phone,$client_id,$type='person')
    {
        if (IdcsmartCertificationLogic::getDefaultConfig('certification_update_client_phone') && $phone){

            $ClientModel = new ClientModel();

            $client = $ClientModel->find($client_id);

            if (!empty($client['phone']) && $client['phone']!=$phone && $type=='person'){

                throw new \Exception(lang_plugins('certification_phone_consistency'));
            }

        }

        return true;
    }

}
