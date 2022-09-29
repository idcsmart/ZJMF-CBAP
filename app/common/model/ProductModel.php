<?php
namespace app\common\model;

use app\admin\model\PluginModel;
use think\db\Query;
use think\facade\Db;
use think\Model;
use app\common\logic\ModuleLogic;

/**
 * @title 商品模型
 * @desc 商品模型
 * @use app\common\model\ProductModel
 */
class ProductModel extends Model
{
    protected $name = 'product';

    // 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'name'                             => 'string',
        'product_group_id'                 => 'int',
        'description'                      => 'string',
        'hidden'                           => 'int',
        'stock_control'                    => 'int',
        'qty'                              => 'int',
        'pay_type'                         => 'string',
        'auto_setup'                       => 'int',
        'type'                             => 'string',
        'rel_id'                           => 'int',
        'order'                            => 'int',
        'creating_notice_sms'              => 'int',
        'creating_notice_sms_api'          => 'int',
        'creating_notice_sms_api_template' => 'int',
        'created_notice_sms'               => 'int',
        'created_notice_sms_api'           => 'int',
        'created_notice_sms_api_template'  => 'int',
        'creating_notice_mail'             => 'int',
        'creating_notice_mail_api'         => 'int',
        'creating_notice_mail_template'    => 'int',
        'created_notice_mail'              => 'int',
        'created_notice_mail_api'          => 'int',
        'created_notice_mail_template'     => 'int',
        'product_id'                       => 'int',
        'create_time'                      => 'int',
        'update_time'                      => 'int',
    ];

    /**
     * 时间 2022-5-17
     * @title 商品列表
     * @desc 商品列表
     * @author wyh
     * @version v1
     * @param string param.keywords - 关键字,搜索范围:商品ID,商品名,描述
     * @param int param.product_group_id - 商品分组ID
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,name,description
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 商品列表
     * @return int list[].id - ID
     * @return int list[].name - 商品名
     * @return int list[].description - 描述
     * @return int list[].stock_control - 是否开启库存控制:1开启,0关闭
     * @return string list[].pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return int list[].qty - 库存
     * @return int list[].hidden - 是否隐藏:1隐藏,0显示
     * @return int list[].product_group_name_second - 二级分组名称
     * @return int list[].product_group_id_second - 二级分组ID
     * @return int list[].product_group_name_first - 一级分组名称
     * @return int list[].product_group_id_first - 一级分组ID
     * @return int count - 商品总数
     */
    public function productList($param)
    {
        // 获取当前应用
        $app = app('http')->getName();

        if (!isset($param['orderby']) || !in_array($param['orderby'],['id','name','description'])){
            $param['orderby'] = 'p.id';
        }else{
            $param['orderby'] = 'p.'.$param['orderby'];
        }

        $where = function (Query $query) use($param, $app) {
            if(!empty($param['keywords'])){
                $query->where('p.id|p.name|p.description', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['id'])){
                $query->where('p.product_group_id', $param['id']);
            }
            if($app=='home'){
                $query->where('p.hidden', 0);
            }
        };

        $products = $this->alias('p')
            ->field('p.id,p.name,p.description,p.stock_control,p.qty,p.hidden,p.pay_type,s.module,ss.module module1,
            pg.name as product_group_name_second,pg.id as product_group_id_second,
            pgf.name as product_group_name_first,pgf.id as product_group_id_first')
            ->leftjoin('product_group pg','p.product_group_id=pg.id')
            ->leftjoin('product_group pgf','pg.parent_id=pgf.id')
            ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id')
            ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
            ->leftjoin('server ss','ss.server_group_id=sg.id')
            ->whereIn('s.module|ss.module',['idcsmart_common','common_cloud'])
            ->where($where)
            ->limit((isset($param['limit']) && !empty($param['limit']))?intval($param['limit']):1000000)
            ->page((isset($param['page']) && !empty($param['page']))?intval($param['page']):1)
            ->order($param['orderby'], (isset($param['sort']) && !empty($param['sort']))?$param['sort']:"desc")
            ->order('p.order','desc')
            ->select()
            ->toArray();

        foreach ($products as $key => $value) {
            if($app=='home'){
                unset($products[$key]['stock_control'], $products[$key]['qty'], $products[$key]['hidden'], $products[$key]['product_group_name_second'], $products[$key]['product_group_id_second'], $products[$key]['product_group_name_first'], $products[$key]['product_group_id_first']);
            }
        }

        $count = $this->alias('p')
            ->field('p.id,p.name,p.description,p.stock_control,p.qty,p.hidden,
            pg.name as product_group_name_second,pg.id as product_group_id_second,
            pgf.name as product_group_name_first,pgf.id as product_group_id_first')
            ->leftjoin('product_group pg','p.product_group_id=pg.id')
            ->leftjoin('product_group pgf','pg.parent_id=pgf.id')
            ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id')
            ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
            ->leftjoin('server ss','ss.server_group_id=sg.id')
            ->where($where)
            ->count();

        return ['list'=>$products,'count'=>$count];
    }

    public function productList1($param)
    {
        // 获取当前应用
        $app = app('http')->getName();

        if (!isset($param['orderby']) || !in_array($param['orderby'],['id','name','description'])){
            $param['orderby'] = 'p.id';
        }else{
            $param['orderby'] = 'p.'.$param['orderby'];
        }

        $where = function (Query $query) use($param, $app) {
            if(!empty($param['keywords'])){
                $query->where('p.id|p.name|p.description', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['id'])){
                $query->where('p.product_group_id', $param['id']);
            }
            if($app=='home'){
                $query->where('p.hidden', 0);
            }
        };

        $products = $this->alias('p')
            ->field('p.id,p.name,p.description,p.stock_control,p.qty,p.hidden,p.pay_type,
            pg.name as product_group_name_second,pg.id as product_group_id_second,
            pgf.name as product_group_name_first,pgf.id as product_group_id_first')
            ->leftjoin('product_group pg','p.product_group_id=pg.id')
            ->leftjoin('product_group pgf','pg.parent_id=pgf.id')
            ->where($where)
            ->limit((isset($param['limit']) && !empty($param['limit']))?intval($param['limit']):1000000)
            ->page((isset($param['page']) && !empty($param['page']))?intval($param['page']):1)
            ->order($param['orderby'], (isset($param['sort']) && !empty($param['sort']))?$param['sort']:"desc")
            ->order('p.order','desc')
            ->select()
            ->toArray();

        foreach ($products as $key => $value) {
            if($app=='home'){
                unset($products[$key]['stock_control'], $products[$key]['qty'], $products[$key]['hidden'], $products[$key]['product_group_name_second'], $products[$key]['product_group_id_second'], $products[$key]['product_group_name_first'], $products[$key]['product_group_id_first']);
            }
        }

        $count = $this->alias('p')
            ->leftjoin('product_group pg','p.product_group_id=pg.id')
            ->leftjoin('product_group pgf','pg.parent_id=pgf.id')
            ->where($where)
            ->count();

        return ['list'=>$products,'count'=>$count];
    }

    /**
     * 时间 2022-5-17
     * @title 商品详情
     * @desc 商品详情
     * @author wyh
     * @version v1
     * @param int id - 商品ID required
     * @return int id - ID
     * @return string name - 商品名称
     * @return int product_group_id - 所属商品组ID
     * @return string description - 商品描述
     * @return int hidden - 0显示默认，1隐藏
     * @return int stock_control - 库存控制(1:启用)默认0
     * @return int qty - 库存数量(与stock_control有关)
     * @return int pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return int auto_setup - 是否自动开通:1是默认,0否
     * @return int type - 关联类型:server,server_group
     * @return int rel_id - 关联ID
     * @return int creating_notice_sms - 开通中短信通知是否开启:1开启默认,0关闭
     * @return int creating_notice_sms_api - 开通中短信通知接口,默认0
     * @return int creating_notice_sms_api_template - 开通中短信通知接口模板,默认0
     * @return int created_notice_sms - 已开通短信通知是否开启:1开启默认,0关闭
     * @return int created_notice_sms_api - 已开通短信通知接口,默认0
     * @return int created_notice_sms_api_template - 已开通短信通知接口模板,默认0
     * @return int creating_notice_mail - 开通中邮件通知是否开启:1开启默认,0关闭
     * @return int creating_notice_mail_api - 开通中邮件通知接口
     * @return int creating_notice_mail_template - 开通中邮件通知模板,默认0
     * @return int created_notice_mail - 已开通邮件通知模板,默认0
     * @return int created_notice_mail_api - 已开通邮件通知接口
     * @return int created_notice_mail_template - 已开通邮件通知模板,默认0
     * @return array upgrade - 可升降级商品ID,数组
     * @return int product_id - 父商品ID
     */
    public function indexProduct($id)
    {
        $product = $this->field('id,name,product_group_id,description,hidden,stock_control,qty,
        creating_notice_sms,creating_notice_sms_api,creating_notice_sms_api_template,created_notice_sms,
        created_notice_sms_api,created_notice_sms_api_template,creating_notice_mail,creating_notice_mail_api,creating_notice_mail_template,
        created_notice_mail,created_notice_mail_api,created_notice_mail_template,pay_type,auto_setup,type,rel_id,product_id')
            ->find($id);

        if (!empty($product->description)){
            $product->description = htmlspecialchars_decode($product->description,ENT_QUOTES);
        }

        $ProductUpgradeProductModel = new ProductUpgradeProductModel();
        $upgrades = $ProductUpgradeProductModel->where('product_id',$id)->select()->toArray();
        $upgradeProducts = array_column($upgrades?:[],'upgrade_product_id');
        if (!empty($product)){
            $product['upgrade'] = $upgradeProducts;
        }

        return $product?:(object)[];
    }

    /**
     * 时间 2022-07-22
     * @title 搜索商品
     * @desc 搜索商品
     * @author theworld
     * @version v1
     * @param string keywords - 关键字,搜索范围:商品名称,商品一级分组,商品二级分组
     * @return array list - 商品
     * @return int list[].id - 商品ID 
     * @return string list[].name - 商品名称
     * @return string list[].product_group_name_first - 商品一级分组名称
     * @return string list[].product_group_name_second - 商品二级分组名称
     */
    public function searchProduct($keywords)
    {   
        //全局搜索
        $products = $this->alias('p')
            ->field('p.id,p.name,pgf.name as product_group_name_first,pg.name as product_group_name_second')
            ->leftjoin('product_group pg','p.product_group_id=pg.id')
            ->leftjoin('product_group pgf','pg.parent_id=pgf.id')
            ->where(function ($query) use($keywords) {
                if(!empty($keywords)){
                    $query->where('p.name|pg.name|pgf.name', 'like', "%{$keywords}%");
                }
            })
            ->order('p.order','desc')
            ->select()
            ->toArray();

        return ['list' => $products];
    }

    /**
     * 时间 2022-5-17
     * @title 新建商品
     * @desc 新建商品
     * @author wyh
     * @version v1
     * @param string param.name 测试商品 商品名称 required
     * @param int param.product_group_id 1 分组ID(只传二级分组ID) required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return int data.product_id - 商品ID,成功时返回
     */
    public function createProduct($param)
    {
        $productGroupId = intval($param['product_group_id']);

        $ProductGroupModel = new ProductGroupModel();
        $productGroup = $ProductGroupModel->where('id',$productGroupId)
            ->where('parent_id','>',0)
            ->find();
        if (empty($productGroup)){
            return ['status'=>400,'msg'=>lang('please_select_product_group_second')];
        }

        $maxOrder = $this->max('order');

        $this->startTrans();

        try{
            $product=$this->create([
                'name' => $param['name']??'',
                'order' => $maxOrder+1,
                'product_group_id' => $productGroupId,
                'description' => '',
                'pay_type' => 'recurring_prepayment',
                'create_time' => time()
            ]);

            # 记录日志
            active_log(lang('log_admin_create_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$product->id.'#'.$param['name'].'#']),'product',$product->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('create_fail')];
        }

        hook('after_product_create',['id'=>$product->id,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('create_success'),'data' => ['product_id' => $product->id]];
    }

    /**
     * 时间 2022-5-17
     * @title 编辑商品
     * @desc 编辑商品
     * @author wyh
     * @version v1
     * @param string name 测试商品 商品名称 required
     * @param int product_group_id 1 分组ID(只传二级分组ID) required
     * @param string description 1 描述 required
     * @param int hidden 1 是否隐藏:1隐藏默认,0显示 required
     * @param int stock_control 1 库存控制(1:启用)默认0 required
     * @param int qty 1 库存数量(与stock_control有关) required
     * @param int creating_notice_sms 1 开通中短信通知是否开启:1开启默认,0关闭 required
     * @param int creating_notice_sms_api 1 开通中短信通知接口,默认0 required
     * @param int creating_notice_sms_api_template 1 开通中短信通知接口模板,默认0 required
     * @param int created_notice_sms 1 已开通短信通知是否开启:1开启默认,0关闭 required
     * @param int created_notice_sms_api 1 已开通短信通知接口,默认0 required
     * @param int created_notice_sms_api_template 1 已开通短信通知接口模板,默认0 required
     * @param int creating_notice_mail 1 开通中邮件通知是否开启:1开启默认,0关闭 required
     * @param int creating_notice_mail_api 1 开通中邮件通知接口 required
     * @param int creating_notice_mail_template 1 开通中邮件通知模板,默认0 required
     * @param int created_notice_mail 1 已开通邮件通知是否开启:1开启默认,0关闭 required
     * @param int created_notice_mail_api 1 已开通邮件通知接口 required
     * @param int created_notice_mail_template 1 已开通邮件通知模板,默认0 required
     * @param string pay_type recurring_prepayment 付款类型(免费free，一次onetime，周期先付recurring_prepayment(默认),周期后付recurring_postpaid required
     * @param int auto_setup 1 是否自动开通:1是默认,0否 required
     * @param string type server_group 关联类型:server,server_group required
     * @param int rel_id 1 关联ID required
     * @param array upgrade [1,3,4] 可升降级商品ID,数组
     * @param int product_id 1 父级商品ID
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateProduct($param)
    {
        $id = intval($param['id']);

        $product = $this->find($id);
        if (empty($product)){
            return ['status'=>400,'msg'=>lang('product_is_not_exist')];
        }

        $ProductGroupModel = new ProductGroupModel();
        $productGroup = $ProductGroupModel->where('id',intval($param['product_group_id']))
            ->where('parent_id','>',0)
            ->find();
        if (empty($productGroup)){
            return ['status'=>400,'msg'=>lang('product_group_is_not_exist')];
        }

        $PluginModel = new PluginModel();

        $SmsTemplateModel = new SmsTemplateModel();

        $EmailTemplateModel = new EmailTemplateModel();
        # 开通中短信通知接口验证
        if (!empty($param['creating_notice_sms_api'])){
            $smsApiCreating = $PluginModel->where('id',intval($param['creating_notice_sms_api']))
                ->where('status',1)
                ->where('module','sms')
                ->find();
            if (empty($smsApiCreating)){
                return ['status'=>400,'msg'=>lang('product_creating_notice_sms_cannot_use')];
            }
        }else{
            $param['creating_notice_sms_api'] = 0;
        }
        # 已开通短信通知接口验证
        if (!empty($param['created_notice_sms_api'])){
            $smsApiCreated = $PluginModel->where('id',intval($param['created_notice_sms_api']))
                ->where('status',1)
                ->where('module','sms')
                ->find();
            if (empty($smsApiCreated)){
                return ['status'=>400,'msg'=>lang('product_created_notice_sms_cannot_use')];
            }
        }else{
            $param['created_notice_sms_api'] = 0;
        }
        # 开通中短信通知模板验证
        if (!empty($param['creating_notice_sms_api_template'])){
            if (empty($smsApiCreating)){
                return ['status'=>400,'msg'=>lang('product_creating_notice_sms_cannot_use')];
            }else{
                $creatingTemplate = $SmsTemplateModel->where('id',$param['creating_notice_sms_api_template'])
                    ->where('sms_name',$smsApiCreating['name'])
                    ->find();
                if (empty($creatingTemplate)){
                    return ['status'=>400,'msg'=>lang('product_creating_notice_sms_api_template_is_not_exist')];
                }
            }
        }else{
            $param['creating_notice_sms_api_template'] = 0;
        }
        # 已开通短信通知模板验证
        if (!empty($param['created_notice_sms_api_template'])){
            if (empty($smsApiCreated)){
                return ['status'=>400,'msg'=>lang('product_created_notice_sms_cannot_use')];
            }else{
                $createdTemplate = $SmsTemplateModel->where('id',$param['created_notice_sms_api_template'])
                    ->where('sms_name',$smsApiCreated['name'])
                    ->find();
                if (empty($createdTemplate)){
                    return ['status'=>400,'msg'=>lang('product_created_notice_sms_api_template_is_not_exist')];
                }
            }
        }else{
            $param['created_notice_sms_api_template'] = 0;
        }
        # 开通中通知邮件接口验证
        if (!empty($param['creating_notice_mail_api'])){
            $mailApiCreating = $PluginModel->where('id',intval($param['creating_notice_mail_api']))
                ->where('status',1)
                ->where('module','mail')
                ->find();
            if (empty($mailApiCreating)){
                return ['status'=>400,'msg'=>lang('product_creating_notice_mail_cannot_use')];
            }
        }else{
            $param['creating_notice_mail_api'] = 0;
        }
        # 开通中通知邮件模板验证
        if (!empty($param['creating_notice_mail_template'])){
            $creatingEmailTemplate = $EmailTemplateModel->where('id',$param['creating_notice_mail_template'])
                ->find();
            if (empty($creatingEmailTemplate)){
                return ['status'=>400,'msg'=>lang('product_creating_notice_mail_template_is_not_exist')];
            }
        }else{
            $param['creating_notice_mail_template'] = 0;
        }
        # 已开通通知邮件接口验证
        if (!empty($param['created_notice_mail_api'])){
            $mailApiCreated = $PluginModel->where('id',intval($param['created_notice_mail_api']))
                ->where('status',1)
                ->where('module','mail')
                ->find();
            if (empty($mailApiCreated)){
                return ['status'=>400,'msg'=>lang('product_created_notice_mail_cannot_use')];
            }
        }else{
            $param['created_notice_mail_api'] = 0;
        }
        # 已开通通知邮件模板验证
        if (!empty($param['created_notice_mail_template'])){
            $createdEmailTemplate = $EmailTemplateModel->where('id',$param['created_notice_mail_template'])
                ->find();
            if (empty($createdEmailTemplate)){
                return ['status'=>400,'msg'=>lang('product_created_notice_mail_template_is_not_exist')];
            }
        }else{
            $param['created_notice_mail_template'] = 0;
        }
        # 验证升降级商品ID
        if (isset($param['upgrade']) && !is_array($param['upgrade'])){
            return ['status'=>400,'msg'=>lang('param_error')];
        }
        $upgradeIds = $param['upgrade']??[];
        if (!empty($upgradeIds)){
            foreach ($upgradeIds as $upgradeId){
                $upgradeProduct = $this->find($upgradeId);
                if (empty($upgradeProduct)){
                    return ['status'=>400,'msg'=>lang('product_upgrade_product_is_not_exist')];
                }
                if ($upgradeId == $id){
                    return ['status'=>400,'msg'=>lang('product_upgrade_product_cannot_self')];
                }
            }
        }
        $param['product_id'] = $param['product_id']??0;
        if(!empty($param['product_id'])){
            $parentProduct = $this->find($param['product_id']);
            if (empty($parentProduct)){
                return ['status'=>400,'msg'=>lang('parent_product_is_not_exist')];
            }
        }
        

        # 日志描述
        $logDescription = log_description($product->toArray(),$param,'product');

        $ProductUpgradeProductModel = new ProductUpgradeProductModel();
        $old = $ProductUpgradeProductModel->where('product_id',$id)->select()->toArray();
        if (count(array_diff($upgradeIds,array_column($old,'upgrade_product_id')))>0 || count(array_diff(array_column($old,'upgrade_product_id'),$upgradeIds))>0){
            $logDescription .= ',' . lang('log_admin_update_product_upgrade_product',['{old}'=>implode(',',array_column($old,'upgrade_product_id')),'{new}'=>implode(',',$upgradeIds)]);
        }

        $this->startTrans();

        try{
            $this->update([
                'name' => $param['name']??'',
                'product_group_id' => $param['product_group_id'],
                'description' => $param['description']??'',
                'hidden' => $param['hidden'],
                'stock_control' => $param['stock_control'],
                'qty' => $param['qty'],
                'creating_notice_sms' => $param['creating_notice_sms'],
                'creating_notice_sms_api' => $param['creating_notice_sms_api'],
                'creating_notice_sms_api_template' => $param['creating_notice_sms_api_template'],
                'created_notice_sms' => $param['created_notice_sms'],
                'created_notice_sms_api' => $param['created_notice_sms_api'],
                'created_notice_sms_api_template' => $param['created_notice_sms_api_template'],
                'creating_notice_mail' => $param['creating_notice_mail'],
                'creating_notice_mail_api' => $param['creating_notice_mail_api'],
                'creating_notice_mail_template' => $param['creating_notice_mail_template'],
                'created_notice_mail_template' => $param['created_notice_mail_template'],
                'created_notice_mail' => $param['created_notice_mail'],
                'created_notice_mail_api' => $param['created_notice_mail_api'],
                'pay_type' => $param['pay_type'],
                'product_id' => $param['product_id'],
                'update_time' => time()
            ],['id'=>$id]);

            # 升级关联
            $ProductUpgradeProductModel->where('product_id',$id)->delete();
            if (!empty($upgradeIds)){
                $insert = [];
                foreach ($upgradeIds as $upgradeId){
                    $insert[] = [
                        'product_id' => $id,
                        'upgrade_product_id' => $upgradeId
                    ];
                }
                $ProductUpgradeProductModel->saveAll($insert);
            }

            # 记录日志
            if (!empty($logDescription)){
                active_log(lang('log_admin_update_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$product->id.'#'.$product['name'].'#','{description}'=>$logDescription]),'product',$product->id);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail') . ':' . $e->getMessage()];
        }

        hook('after_product_edit',['id'=>$product->id,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2022-6-10
     * @title 编辑商品接口
     * @desc 编辑商品接口
     * @author wyh
     * @version v1
     * @param int auto_setup 1 是否自动开通:1是默认,0否 required
     * @param string type server_group 关联类型:server,server_group required
     * @param int rel_id 1 关联ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateServer($param)
    {
        $id = intval($param['id']);

        $product = $this->find($id);
        if (empty($product)){
            return ['status'=>400,'msg'=>lang('product_is_not_exist')];
        }
        # 验证接口
        if ($param['type'] == 'server_group'){
            $ServerGroupModel = new ServerGroupModel();
            $ServerGroup = $ServerGroupModel->find(intval($param['rel_id']));
            if (empty($ServerGroup)){
                return ['status'=>400,'msg'=>lang('server_group_not_found')];
            }
        }else{
            $ServerModel = new ServerModel();
            $Server = $ServerModel->find(intval($param['rel_id']));
            if (empty($Server)){
                return ['status'=>400,'msg'=>lang('server_is_not_exist')];
            }
        }

        # 日志描述
        $logDescription = log_description($product->toArray(),$param,'product');

        $this->startTrans();

        try{
            $this->update([
                'auto_setup' => $param['auto_setup'],
                'type' => $param['type'],
                'rel_id' => $param['rel_id'],
                'update_time' => time()
            ],['id'=>$id]);

            # 记录日志
            if (!empty($logDescription)){
                active_log(lang('log_admin_update_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$product->id.'#'.$product['name'].'#','{description}'=>$logDescription]),'product',$product->id);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail') . ':' . $e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2022-5-17
     * @title 删除商品
     * @desc 删除商品
     * @author wyh
     * @version v1
     * @param int id 1 商品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteProduct($id)
    {
        $product = $this->find($id);
        if (empty($product)){
            return ['status'=>400,'msg'=>lang('product_is_not_exist')];
        }

        $HostModel = new HostModel();
        $hostCount = $HostModel->where('product_id',$id)->count();
        if ($hostCount>0){
            return ['status'=>400,'msg'=>lang('product_has_host')];
        }

        $this->startTrans();
        try{
            # 删除商品
            $product->delete();
            # 删除配置以及子项
            $ConfigOptionModel = new ConfigOptionModel();
            $configoption = $ConfigOptionModel->field('id')->where('product_id',$id)->select()->toArray();
            if (!empty($configoption)){
                $ConfigOptionSubModel = new ConfigOptionSubModel();
                $ConfigOptionSubModel->where('config_option_id',array_column($configoption,'id'))->delete();
                $ConfigOptionModel->where('product_id',$id)->delete();
            }
            # 删除商品关联的升降级ID
            $ProductUpgradeProductModel = new ProductUpgradeProductModel();
            $ProductUpgradeProductModel->where('product_id',$id)->delete();

            # 删除其他商品升降级至此商品的ID
            $ProductUpgradeProductModel->where('upgrade_product_id',$id)->delete();

            # 记录日志
            active_log(lang('log_admin_delete_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$id.'#'.$product['name'].'#']),'product',$id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('delete_fail') . $e->getMessage()];
        }
        hook('after_product_delete', ['id'=>$id]);

        return ['status'=>200,'msg'=>lang('delete_success')];
    }

    /**
     * 时间 2022-5-17
     * @title 隐藏/显示商品
     * @desc 隐藏/显示商品
     * @url /admin/v1/product/:id/:hidden
     * @method  put
     * @author wyh
     * @version v1
     * @param int param.id 1 商品ID required
     * @param int param.hidden 1 商品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function hiddenProduct($param)
    {
        $product = $this->find(intval($param['id']));
        if (empty($product)){
            return ['status'=>400,'msg'=>lang('product_is_not_exist')];
        }

        $hidden = intval($param['hidden']);

        if ($product['hidden'] == $hidden){
            return ['status'=>400,'msg'=>lang('cannot_repeat_opreate')];
        }

        $this->startTrans();
        try{
            $this->update([
                'hidden' => $hidden,
                'update_time' => time(),
            ],['id'=>intval($param['id'])]);

            # 记录日志
            if ($hidden == 1){
                active_log(lang('log_admin_hidden_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$product->id.'#'.$product['name'].'#']),'product',$product->id);
            }else{
                active_log(lang('log_admin_show_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$product->id.'#'.$product['name'].'#']),'product',$product->id);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('fail_message')];
        }

        return ['status'=>200,'msg'=>lang('success_message')];
    }

    /**
     * 时间 2022-5-18
     * @title 商品拖动排序
     * @desc 商品拖动排序
     * @author wyh
     * @version v1
     * @param int id 1 商品ID required
     * @param int pre_product_id 1 移动后前一个商品ID(没有则传0) required
     * @param int product_group_id 1 移动后的商品组ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function orderProduct($param)
    {
        $id = $param['id'];

        $preProductId = intval($param['pre_product_id']);

        $productGroupId = intval($param['product_group_id']);

        # 基础验证
        $product = $this->find($id);
        if (empty($product)){
            return ['status'=>400,'msg'=>lang('product_is_not_exist')];
        }

        if ($preProductId){
            $preProduct = $this->find($preProductId);
            if (empty($preProduct)){
                return ['status'=>400,'msg'=>lang('product_is_not_exist')];
            }

            if (!empty($preProduct) && $preProduct->product_group_id != $productGroupId){
                return ['status'=>400,'msg'=>lang('product_is_not_in_product_group')];
            }
        }

        $ProductGroupModel = new ProductGroupModel();
        $productGroup = $ProductGroupModel->where('id',$productGroupId)
            ->where('parent_id','>',0)
            ->find();
        if (empty($productGroup)){
            return ['status'=>400,'msg'=>lang('product_group_is_not_exist')];
        }
        # 排序处理
        $this->startTrans();
        try{
            if ($preProductId){
                $preOrder = $preProduct['order'];
                $products = $this->where('product_group_id',$productGroupId)
                    ->where('order','<=',$preOrder)
                    ->select();
                foreach ($products as $v){
                    $v->save([
                        'order' => $v['order']-1,
                        'update_time' => time()
                    ]);
                }

                $product->save([
                    'order' => $preOrder,
                    'product_group_id' => $productGroupId,
                    'update_time' => time()
                ]);

            }else{
                $minOrder = $this->where('product_group_id',$productGroupId)->min('order');

                $product->save([
                    'order' => $minOrder-1,
                    'product_group_id' => $productGroupId,
                    'update_time' => time()
                ]);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>lang('move_fail') . ':' . $e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang('move_success')];
    }

    /**
     * 时间 2022-5-31
     * @title 获取商品关联的升降级商品
     * @desc 获取商品关联的升降级商品
     * @author wyh
     * @version v1
     * @param int id 1 商品ID required
     * @return array list - 商品列表
     * @return int list[].id - ID
     * @return string list[].name - 商品名
     */
    public function upgradeProduct($id)
    {
        $products = $this->alias('p')
            ->field('p.id,p.name')
            ->leftJoin('product_upgrade_product pup','p.id=pup.upgrade_product_id')
            ->where('pup.product_id',$id)
            ->select()->toArray();
        return ['status'=>200,'msg'=>lang('success_message'),'data'=>['list'=>$products]];
    }

    /**
     * 时间 2022-05-27
     * @title 获取当前商品关联模块类型(需要先实例化)
     * @desc 获取当前商品关联模块类型
     * @author hh
     * @version v1
     * @param int relId - 关联ID,不传使用当前
     * @return  string
     */
    public function getModule($relId = 0, $type = 'server')
    {
        if(empty($relId)){
            $relId = $this->getAttr('rel_id');
            $type = $this->getAttr('type');
        }
        $module = '';
        if(empty($relId)){
            return $module;
        }
        if($type == 'server_group'){
            $server = ServerModel::where('server_group_id', $relId)->find();
            $module = $server['module'] ?? '';
        }else if($type == 'server'){
            $server = ServerModel::find($relId);
            $module = $server['module'] ?? '';
        }
        return $module;
    }

    /**
     * 时间 2022-05-31
     * @title 选择接口获取配置
     * @desc 选择接口获取配置
     * @author hh
     * @version v1
     * @param   int param.id - 商品ID
     * @param   string param.type - 关联类型(server=接口,server_group=接口分组)
     * @param   int param.rel_id - 关联ID
     */
    public function moduleServerConfigOption($param){
        $ProductModel = $this->find((int)$param['id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        // 商品
        // $oldModule = $ProductModel->getModule();
        $newModule = $this->getModule($param['rel_id'], $param['type']);

        $ModuleLogic = new ModuleLogic();
        $content = $ModuleLogic->serverConfigOption($newModule, $ProductModel);

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => [
                'content' => $content,
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-05-30
     * @title 前台商品配置页面
     * @desc 前台商品配置页面模块输出
     * @author hh
     * @version v1
     * @param   int param.id - 商品ID
     * @param   string param.tag - 商品价格显示标识
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.content - 模块输出内容
     */
    public function moduleClientConfigOption($param){
        $id = (int)$param['id'];
        $ProductModel = $this->find($id);
        if(empty($ProductModel) || $ProductModel['hidden'] == 1){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        $param['tag'] = $param['tag'] ?? '';

        $ModuleLogic = new ModuleLogic();
        $content = $ModuleLogic->clientProductConfigOption($ProductModel, $param['tag']);

        $result = [
            'status'=> 200,
            'msg'   => lang('success_message'),
            'data'  => [
                'content' => $content,
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-05-30
     * @title 后台商品配置页面
     * @desc 后台商品配置页面模块输出
     * @author hh
     * @version v1
     * @param   int param.id - 商品ID
     * @param   string param.tag - 商品价格显示标识
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.content - 模块输出内容
     */
    public function moduleAdminConfigOption($param){
        $id = (int)$param['id'];

        $ProductModel = $this->find($id);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        $param['tag'] = $param['tag'] ?? '';

        $ModuleLogic = new ModuleLogic();
        $content = $ModuleLogic->adminProductConfigOption($ProductModel, $param['tag']);

        $result = [
            'status'=> 200,
            'msg'   => lang('success_message'),
            'data'  => [
                'content' => $content,
            ]
        ];
        return $result;
    }


    /**
     * 时间 2022-05-31
     * @title 结算商品
     * @desc 结算商品
     * @author theworld
     * @version v1
     * @param  int param.product_id - 商品ID required
     * @param  object param.config_options - 自定义配置
     * @param  object customfield - 自定义参数,比如优惠码参数传:{"promo_code":["pr8nRQOGbmv5"]}
     * @return object data - 数据
     * @return int data.order_id - 订单ID
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function settle($param)
    {
        $product = $this->find($param['product_id']);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        if($product['hidden']==1){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        $param['config_options'] = $param['config_options'] ?? [];
        
        $ModuleLogic = new ModuleLogic();
        $result = $ModuleLogic->cartCalculatePrice($product, $param['config_options'],$param['qty']);

        if($result['status']!=200){
            return $result;
        }
        if($product['pay_type']=='free'){
            $result['data']['price'] = 0;
        }
        $amount = $result['data']['price']*$param['qty'];
        $param['price'] = $result['data']['price'];
        $param['billing_cycle'] = $result['data']['billing_cycle'];
        $param['duration'] = $result['data']['duration'];
        $param['description'] = $result['data']['description'];
        $param['config_options'] = $param['config_options'] ?? [];

        $this->startTrans();
        try {
            // 创建订单
            $clientId = get_client_id();
            $time = time();
            $order = OrderModel::create([
                'client_id' => $clientId,
                'type' => 'new',
                'status' => $amount>0 ? 'Unpaid' : 'Paid',
                'amount' => $amount,
                'credit' => 0,
                'amount_unpaid' => $amount,
                'gateway' => '',
                'gateway_name' => '',
                'pay_time' => $amount>0 ? 0 : $time,
                'create_time' => $time
            ]);

            // 创建产品
            $orderItem = [];
            $product = $this->find($param['product_id']);
            if($product['stock_control']==1){
                if($product['qty']<$param['qty']){
                    throw new \Exception(lang('product_inventory_shortage'));
                }
                $this->where('id', $param['product_id'])->dec('qty', $param['qty'])->update();
            }
            if($product['type']=='server_group'){
                $server = ServerModel::where('server_group_id', $product['rel_id'])->where('status', 1)->find();
                $serverId = $server['id'] ?? 0;
            }else{
                $serverId = $product['rel_id'];
            }
            for ($i=1; $i<=$param['qty']; $i++) {
                $host = HostModel::create([
                    'client_id' => $clientId,
                    'order_id' => $order->id,
                    'product_id' => $param['product_id'],
                    'server_id' => $serverId,
                    'name' => generate_host_name(),
                    'status' => 'Unpaid',
                    'first_payment_amount' => $param['price'],
                    'renew_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $param['price'] : 0,
                    'billing_cycle' => $product['pay_type'],
                    'billing_cycle_name' => $param['billing_cycle'],
                    'billing_cycle_time' => $param['duration'],
                    'active_time' => $time,
                    'due_time' => $product['pay_type']!='onetime' ? $time : 0,
                    'create_time' => $time
                ]);
                $ModuleLogic->afterSettle($product, $host->id, $param['config_options']);
                $orderItem[] = [
                    'order_id' => $order->id,
                    'client_id' => $clientId,
                    'host_id' => $host->id,
                    'product_id' => $param['product_id'],
                    'type' => 'host',
                    'rel_id' => $host->id,
                    'amount' => $param['price'],
                    'description' => $param['description'],
                    'create_time' => $time,
                ];
            }

            // 创建订单子项
            $OrderItemModel = new OrderItemModel();
            $OrderItemModel->saveAll($orderItem);

            hook('after_order_create',['id'=>$order->id,'customfield'=>$param['customfield']??[]]);

            $OrderModel = new OrderModel();
            # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
            $amount = $OrderModel->where('id',$order->id)->value('amount');

            if($amount<=0){
                $OrderModel = new OrderModel();
                $OrderModel->processPaidOrder($order->id);
            }

            # 记录日志
            active_log(lang('submit_order', ['{client}'=>'#'.$clientId.request()->client_name, '{order}'=>$order->id, '{product}'=>'product#'.$product['id'].'#'.$product['name'].'#']), 'order', $order->id);
            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
        return ['status' => 200, 'msg' => lang('success_message'), 'data' => ['order_id' => $order->id]];
    }

    /**
     * 时间 2022-05-28
     * @title 商品价格计算
     * @desc 商品价格计算
     * @author hh
     * @version v1
     * @param  int param.product_id - 商品ID required
     * @param  object param.config_options - 自定义配置 required
     * @return  int status - 状态码,200=成功,400=失败
     * @return  array data - 计算后数据
     * @return  float data.price - 配置项金额
     * @return  string data.billing_cycle - 周期名称
     * @return  int data.duration - 周期时长(秒)
     * @return  string data.description - 子项描述
     * @return  string data.content - 配置选择预览输出
     */
    public function productCalculatePrice($param)
    {
        $ProductModel = $this->find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        if(app('http')->getName() == 'home' && $ProductModel['hidden'] == 1){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        $param['config_options'] = $param['config_options'] ?? [];
        
        $ModuleLogic = new ModuleLogic();
        return $ModuleLogic->cartCalculatePrice($ProductModel, $param['config_options']);
    }

    /**
     * 时间 2022-07-25
     * @title 获取商品所有配置项
     * @desc 获取商品所有配置项
     * @author hh
     * @version v1
     * @param   int id - 商品ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data[].name - 配置名称
     * @return  string data[].field - 配置标识
     * @return  string data[].type - 配置形式(dropdown=下拉,目前只有这个)
     * @return  string data[].option[].name - 选项名称
     * @return  mixed data[].option[].value - 选项值
     */
    public function productAllConfigOption($id)
    {
        $ProductModel = $this->find($id ?? 0);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        
        $ModuleLogic = new ModuleLogic();
        return $ModuleLogic->allConfigOption($ProductModel);
    }





}
