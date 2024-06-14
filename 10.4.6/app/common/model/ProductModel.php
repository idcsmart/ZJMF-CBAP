<?php
namespace app\common\model;

use app\admin\model\PluginModel;
use think\db\Query;
use think\facade\Db;
use think\Model;
use app\common\logic\ModuleLogic;
use app\common\logic\ResModuleLogic;

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
        'price'                            => 'float',
        'cycle'                            => 'string',
        'agentable'                        => 'int',
    ];

    public $isAdmin = false;

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
     * @return string list[].pay_type - 付款类型免费free,一次onetime,周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return int list[].stock_control - 是否开启库存控制:1开启,0关闭
     * @return string list[].pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return int list[].qty - 库存
     * @return int list[].hidden - 是否隐藏:1隐藏,0显示
     * @return int list[].product_group_name_second - 二级分组名称
     * @return int list[].product_group_id_second - 二级分组ID
     * @return int list[].product_group_name_first - 一级分组名称
     * @return int list[].product_group_id_first - 一级分组ID
     * @return string list[].price - 商品最低价格
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
                $query->where("pg.hidden",0);
                $query->where("pgf.hidden",0);
            }
            $query->where('p.product_id',0);
        };

        $products = $this->alias('p')
            ->field('p.id,p.name,p.description,p.stock_control,p.qty,p.hidden,p.pay_type,p.price,p.cycle,s.module,ss.module module1,
            pg.name as product_group_name_second,pg.id as product_group_id_second,
            pgf.name as product_group_name_first,pgf.id as product_group_id_first,pr.name as parent_name,pr.id as parent_id')
            ->leftJoin('product pr','pr.id=p.product_id')
            ->leftjoin('product_group pg','p.product_group_id=pg.id')
            ->leftjoin('product_group pgf','pg.parent_id=pgf.id')
            ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id')
            ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
            ->leftjoin('server ss','ss.server_group_id=sg.id')
            ->withAttr('description',function ($value){
                if (!empty($value)){
                    return htmlspecialchars_decode($value);
                }
                return $value;
            })
            /*->whereIn('s.module|ss.module',['idcsmart_common','common_cloud','idcsmart_dcim','baidu_cloud','room_box','idcsmart_common_finance','idcsmart_common_dcim','idcsmart_common_cloud','idcsmart_common_business','idcsmart_cert','idcsmart_email','idcsmart_sms','zjmfapp','dcimapp','mf_cloud','mf_dcim'])*/
            ->where($where)
            ->limit((isset($param['limit']) && !empty($param['limit']))?intval($param['limit']):1000000)
            ->page((isset($param['page']) && !empty($param['page']))?intval($param['page']):1)
            #->order($param['orderby'], (isset($param['sort']) && !empty($param['sort']))?$param['sort']:"desc")
            ->order('p.order','desc')
            ->group('p.id')
            ->select()
            ->toArray();

        foreach ($products as $key => $value) {
            $products[$key]['price'] = amount_format($value['price']);
            if ($value['parent_id'] && $value['parent_id']>0){
                $products[$key]['id'] = $value['parent_id'];
            }
            if($app=='home'){
                unset($products[$key]['stock_control'], $products[$key]['qty'], $products[$key]['hidden'], $products[$key]['product_group_name_second'], $products[$key]['product_group_id_second'], $products[$key]['product_group_name_first'], $products[$key]['product_group_id_first'], $products[$key]['parent_name'], $products[$key]['parent_id']);
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
            /*->whereIn('s.module|ss.module',['idcsmart_common','common_cloud','idcsmart_dcim','baidu_cloud','room_box','idcsmart_common_finance','idcsmart_common_dcim','idcsmart_common_cloud','idcsmart_common_business','idcsmart_cert','idcsmart_email','idcsmart_sms','zjmfapp','dcimapp','mf_cloud','mf_dcim'])*/
            ->where($where)
            ->group('p.id')
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
            }/*else{
                $query->where('pgf.name','<>','应用商店');
            }*/
        };

        $products = $this->alias('p')
            ->field('p.id,p.name,p.description,p.stock_control,p.qty,p.hidden,p.pay_type,
            pg.name as product_group_name_second,pg.id as product_group_id_second,
            pgf.name as product_group_name_first,pgf.id as product_group_id_first')
            ->leftjoin('product_group pg','p.product_group_id=pg.id')
            ->leftjoin('product_group pgf','pg.parent_id=pgf.id')
            ->withAttr('description',function ($value){
                if (!empty($value)){
                    return htmlspecialchars_decode($value);
                }
                return $value;
            })
            ->where($where)
            ->limit((isset($param['limit']) && !empty($param['limit']))?intval($param['limit']):1000000)
            ->page((isset($param['page']) && !empty($param['page']))?intval($param['page']):1)
            #->order($param['orderby'], (isset($param['sort']) && !empty($param['sort']))?$param['sort']:"desc")
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
     * 时间 2022-10-12
     * @title 根据模块获取商品列表
     * @desc 根据模块获取商品列表
     * @author theworld
     * @version v1
     * @param string param.module - 模块名称
     * @return array list - 一级分组列表
     * @return int list[].id - 一级分组ID
     * @return string list[].name - 一级分组名称
     * @return array list[].child - 二级分组
     * @return int list[].child[].id - 二级分组ID
     * @return string list[].child[].name - 二级分组名称
     * @return array list[].child[].child - 商品
     * @return int list[].child[].child[].id - 商品ID
     * @return string list[].child[].child[].name - 商品名称
     */
    public function moduleProductList($param)
    {
        $where = function (Query $query) use($param) {
            //$query->where('p.hidden', 0);
            if(!empty($param['module'])){
                if(is_array($param['module'])){
                    $query->whereIn('s.module|ss.module', $param['module']);
                }else{
                    $query->where('s.module|ss.module', $param['module']);
                }
            }
        };

        $ProductGroupModel = new ProductGroupModel();
        $firstGroup = $ProductGroupModel->productGroupFirstList();
        $firstGroup = $firstGroup['list'];

        $secondGroup = $ProductGroupModel->productGroupSecondList([]);
        $secondGroup = $secondGroup['list'];

        $products = $this->alias('p')
            ->field('p.id,p.name,p.product_group_id')
            ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id')
            ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
            ->leftjoin('server ss','ss.server_group_id=sg.id')
            ->where($where)
            ->order('p.order','desc')
            ->select()
            ->toArray();
        $productArr = [];
        foreach ($products as $key => $value) {
            $productArr[$value['product_group_id']][] = ['id' => $value['id'], 'name' => $value['name']];
        }
        $secondGroupArr = [];
        foreach ($secondGroup as $key => $value) {
            if(isset($productArr[$value['id']])){
                $secondGroupArr[$value['parent_id']][] = ['id' => $value['id'], 'name' => $value['name'], 'child' => $productArr[$value['id']]];
            }
        }
        $list = [];
        foreach ($firstGroup as $key => $value) {
            if(isset($secondGroupArr[$value['id']])){
                $list[] = ['id' => $value['id'], 'name' => $value['name'], 'child' => $secondGroupArr[$value['id']]];
            }
        }

        return ['list'=>$list];
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
     * @return array plugin_custom_fields - 自定义字段{is_link:是否已有子商品,是,置灰}
     * @return int show - 是否将商品展示在会员中心对应模块的列表中:0否1是
     */
    public function indexProduct($id)
    {
        // 获取当前应用
        $app = app('http')->getName();

        $product = $this->field('id,name,product_group_id,description,hidden,stock_control,qty,
        creating_notice_sms,creating_notice_sms_api,creating_notice_sms_api_template,created_notice_sms,
        created_notice_sms_api,created_notice_sms_api_template,creating_notice_mail,creating_notice_mail_api,creating_notice_mail_template,
        created_notice_mail,created_notice_mail_api,created_notice_mail_template,pay_type,auto_setup,type,rel_id,product_id,price,cycle')
            ->find($id);

        if (!empty($product->description)){
            $product->description = htmlspecialchars_decode($product->description,ENT_QUOTES);
        }

        $ProductUpgradeProductModel = new ProductUpgradeProductModel();
        $upgrades = $ProductUpgradeProductModel->where('product_id',$id)->select()->toArray();
        $upgradeProducts = array_column($upgrades?:[],'upgrade_product_id');
        if (!empty($product)){
            $product['upgrade'] = $upgradeProducts;
            if($app=='home'){
                $product = ['id' => $product['id'], 'name' => $product['name'], 'pay_type' => $product['pay_type'], 'price' => $product['price'], 'cycle' => $product['cycle']];
            }
        }

        # 自定义字段
        $result = hook('product_detail_custom_fields',['id'=>$id]);
        foreach ($result as $item){
            if (is_array($item)){
                foreach ($item as $key=>$value){
                    $customFields[$key] = $value;
                }
            }
        }
        if (!empty($product)){
            $product['plugin_custom_fields'] = $customFields??[];
        }

        $product['show'] = 0;

        $module = $this->alias('p')
            ->field('p.id,p.name,p.product_group_id,s.module,ss.module module2')
            ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id')
            ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
            ->leftjoin('server ss','ss.server_group_id=sg.id')
            ->where('p.id', $id)
            ->find();

        $module = !empty($module['module']) ? $module['module'] : $module['module2'];
        $menu = MenuModel::where('type', 'home')->where('menu_type', 'module')->where('module', $module)->find();
        if(!empty($menu)){
            $menu['product_id'] = json_decode($menu['product_id'], true);
            if(in_array($id, $menu['product_id'])){
                $product['show'] = 1;
            }
        }

        if($app=='home'){
            $product['customfield'] = [];
            $hookRes = hook('home_product_index', ['id'=>$id]);
            foreach($hookRes as $v){
                if(isset($v['status']) && $v['status'] == 200){
                    $product['customfield'] = array_merge($product['customfield'], $v['data'] ?? []);
                }
            }
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

        // 双删
        idcsmart_cache('product:list',null);

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

        idcsmart_cache('product:list',null);

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
     * @param string price - 商品起售价格
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

        $moduleSetPrice = true;
        $price = $product['price'];
        if($param['pay_type'] == 'free'){
            $price = 0;
            $moduleSetPrice = false;
        }else if(isset($param['price'])){
            if(is_numeric($param['price'])){
                $price = $param['price'];
                $moduleSetPrice = false;
            }
        }

        // 双删
        idcsmart_cache('product:list',null);

        $this->startTrans();

        try{
            $this->update([
                'name' => $param['name']??'',
                'product_group_id' => $param['product_group_id'],
                'description' => isset($param['description'])?htmlspecialchars($param['description']):'',
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
                'update_time' => time(),
                'price' => $price,
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

        $upstreamProduct = UpstreamProductModel::where('product_id', $id)->find();
        if(empty($upstreamProduct)){
            $ModuleLogic = new ModuleLogic();
            $priceCycle = $ModuleLogic->getPriceCycle($product->id);
            $this->setPriceCycle($priceCycle['product'], $moduleSetPrice ? $priceCycle['price'] : NULL, $priceCycle['cycle']);
        }
        idcsmart_cache('product:list',null);

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
            if($param['show']==1){
                # 创建导航
                $MenuModel = new MenuModel();
                $MenuModel->createHomeModuleMenu($id);
            }else{
                # 创建导航
                $MenuModel = new MenuModel();
                $MenuModel->deleteHomeModuleMenu($id);
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
        $hostCount = $HostModel->where('product_id',$id)->where('is_delete', 0)->count();
        if ($hostCount>0){
            return ['status'=>400,'msg'=>lang('product_has_host')];
        }

        idcsmart_cache('product:list',null);

        $module = $product->getModule();

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

            # 删除导航
            $MenuModel = new MenuModel();
            $MenuModel->deleteHomeModuleMenu($id);

            UpstreamProductModel::where('product_id', $id)->delete();

            # 删除自定义字段
            $SelfDefinedFieldModel = new SelfDefinedFieldModel();
            $SelfDefinedFieldModel->withDelete('product', $id);

            # 记录日志
            active_log(lang('log_admin_delete_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$id.'#'.$product['name'].'#']),'product',$id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('delete_fail') . $e->getMessage()];
        }

        idcsmart_cache('product:list',null);

        hook('after_product_delete', ['id'=>$id, 'module'=>$module]);

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

        idcsmart_cache('product:list',null);

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

        idcsmart_cache('product:list',null);

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

        idcsmart_cache('product:list',null);

        # 排序处理
        $this->startTrans();
        try{
            if ($preProductId){
                $preOrder = $preProduct['order'];

                if (isset($param['backward']) && $param['backward']){
                    $products = $this->where('product_group_id',$productGroupId)
                        ->where('order','>=',$preOrder)
                        ->where('id','<>',$id)
                        ->select();
                    foreach ($products as $v){
                        $v->save([
                            'order' => $v['order']+1,
                            'update_time' => time()
                        ]);
                    }

                    $product->save([
                        'order' => $preOrder,
                        'product_group_id' => $productGroupId,
                        'update_time' => time()
                    ]);
                }else{
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
                        'order' => $preOrder+1,
                        'product_group_id' => $productGroupId,
                        'update_time' => time()
                    ]);
                }


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

        idcsmart_cache('product:list',null);

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
     * @param array param.domain - 域名
     * @param int relId 当前关联ID 关联ID
     * @param string type 当前关联类型 关联类型(server=接口,server_group=接口分组)
     * @return  string
     */
    public function getModule($param=[], $relId = 0, $type = 'server')
    {
        // 自定义获取模块
        $ProductGroupModel = new ProductGroupModel();
        $productGroupType = $ProductGroupModel->where('id',$this->getAttr("product_group_id"))->value("type");
        if ($productGroupType=="domain" && $customGetModule = hook_one("custom_get_module",['domain'=>$param['domain']??""])){
            return $customGetModule;
        }

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
     * @param   int param.id - 商品ID require
     * @param   string param.type - 关联类型(server=接口,server_group=接口分组) require
     * @param   int param.rel_id - 关联ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.content - 模块输出内容
     */
    public function moduleServerConfigOption($param)
    {
        $ProductModel = $this->find((int)$param['id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        $content = '';
        $upstreamProduct = UpstreamProductModel::where('product_id', $ProductModel['product_id'])->find();

        if(empty($upstreamProduct)){
            // 商品
            // $oldModule = $ProductModel->getModule();
            $newModule = $this->getModule([],$param['rel_id'], $param['type']);

            $ModuleLogic = new ModuleLogic();
            $content = $ModuleLogic->serverConfigOption($newModule, $ProductModel);
        }

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => [
                'content'       => $content,
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
     * @param   int param.id - 商品ID require
     * @param   bool param.flag - 是否获取隐藏隐藏商品的模块内容(true=是,false=否)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.product_name - 商品名称
     * @return  string data.content - 模块输出内容
     */
    public function moduleClientConfigOption($param)
    {
        $id = (int)$param['id'];
        $ProductModel = $this->find($id);
        // flag用于后台创建订单,可以显示隐藏商品的模块内容
        if (isset($param['flag']) && $param['flag']=='true'){

        }else{
            if(empty($ProductModel) || $ProductModel['hidden'] == 1){
                return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
            }
        }

        $upstreamProduct = UpstreamProductModel::where('product_id', $ProductModel['id'])->find();

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $content = $ResModuleLogic->clientProductConfigOption($ProductModel);
        }else{
            $ModuleLogic = new ModuleLogic();
            $content = $ModuleLogic->clientProductConfigOption($ProductModel);
        }

        $result = [
            'status'=> 200,
            'msg'   => lang('success_message'),
            'data'  => [
                'product_name'  => $ProductModel['name'],
                'content'       => $content,
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
     * @param  object param.customfield - 自定义参数,比如优惠码参数传:{"promo_code":["pr8nRQOGbmv5"]}
     * @param  int param.qty - 数量 required
     * @param  object param.self_defined_field - 自定义字段({"5":"123"},5是自定义字段ID,123是填写的内容)
     * @return object data - 数据
     * @return int data.order_id - 订单ID
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function settle($param,$isAdmin=false)
    {
        $product = $this->find($param['product_id']);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        // 非后台
        if (!$isAdmin){
            if($product['hidden']==1){
                return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
            }
        }
        if(!empty($product['product_id'])){
            if(!isset($param['config_options']['host_id'])){
                return ['status'=>400, 'msg'=>lang('cannot_only_buy_son_product')];
            }
            $host = HostModel::find($param['config_options']['host_id']);
            if($host['product_id']!=$product['product_id']){
                return ['status'=>400, 'msg'=>lang('cannot_only_buy_son_product')];
            }
        }
        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
        $SelfDefinedFieldValueModel = new SelfDefinedFieldValueModel();
        $checkSelfDefinedField = $SelfDefinedFieldModel->checkAndFilter([
            'product_id'          => $product['id'], 
            'self_defined_field'  => $param['self_defined_field'] ?? [],
        ]);
        if($checkSelfDefinedField['status'] != 200){
            return $checkSelfDefinedField;
        }
        $param['self_defined_field'] = $checkSelfDefinedField['data'];

        $appendOrderItem = [];
        $param['config_options'] = $param['config_options'] ?? [];
        // 非后台
        if ($isAdmin){
            $clientId = $param['client_id']??0;
        }else{
            $clientId = get_client_id();
        }

        $certification = check_certification($clientId);

        $result = hook('before_order_create', ['client_id'=>$clientId, 'param' => $param]);

        foreach ($result as $value){
            if (isset($value['status']) && $value['status']==400){
                return ['status'=>400, 'msg'=>$value['msg'] ?? lang('fail_message')];
            }
        }
        
        $upstreamProduct = UpstreamProductModel::where('product_id', $product['id'])->find();

        if($upstreamProduct){
            // 非后台
            if (!$isAdmin){
                if($upstreamProduct['certification']==1 && !$certification){
                    return ['status'=>400, 'msg'=>lang('certification_uncertified_cannot_buy_product')];
                }
            }
            $param['config_options']['customfield'] = $param['config_options']['self_defined_field'] = $SelfDefinedFieldModel->toUpstreamId([
                'product_id'          => $product['id'],
                'self_defined_field'  => $param['self_defined_field'],
            ]);

            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->cartCalculatePrice($product, $param['config_options'],$param['qty'],'',true);
        }else{
            $ModuleLogic = new ModuleLogic();
            $result = $ModuleLogic->cartCalculatePrice($product, $param['config_options'],$param['qty'],'buy',0,$clientId);
        }

        if($result['status']!=200){
            return $result;
        }
        if($product['pay_type']=='free'){
            $result['data']['price'] = 0;
        }
        $appendOrderItem = $result['data']['order_item'] ?? [];
        // wyh 20240226 上下游商品，价格已算上数量
        $amount = $upstreamProduct?$result['data']['price']:$result['data']['price']*$param['qty'];
        $param['price'] = $upstreamProduct?bcdiv($result['data']['price'],$param['qty'],2):$result['data']['price'];

        $param['discount'] = $result['data']['discount'] ?? 0;
        $param['renew_price'] = $result['data']['renew_price'] ?? $param['price'];
        $param['billing_cycle'] = $result['data']['billing_cycle'];
        $param['duration'] = $result['data']['duration'];
        $param['description'] = $result['data']['description'];
        $param['config_options'] = $param['config_options'] ?? [];
        $param['base_price'] = $result['data']['base_price']??$param['price'];
        if($upstreamProduct){
            $param['profit'] = $result['data']['profit'] ?? 0;
        }

        if(empty($param['description'])){
            if($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment'){
                $param['description'] = $product['name'].'('.date("Y-m-d H:i:s").'-'.date("Y-m-d H:i:s",time()+$param['duration']).')';
            }else{
                $param['description'] = $product['name'];
            }
        }
        

        $this->startTrans();
        try {
            // 创建订单
            /*$gateway = gateway_list();
            $gateway = $gateway['list'][0]??[];*/

            $time = time();
            $order = OrderModel::create([
                'client_id' => $clientId,
                'type' => 'new',
                'status' => $amount>0 ? 'Unpaid' : 'Paid',
                'amount' => $amount,
                'credit' => 0,
                'amount_unpaid' => $amount,
                //'gateway' => $gateway['name'] ?? '',
                //'gateway_name' => $gateway['title'] ?? '',
                'pay_time' => $amount>0 ? 0 : $time,
                'create_time' => $time
            ]);

            // 创建产品
            $orderItem = [];
            $hostIds = [];
            $product = $this->find($param['product_id']);
            if($product['stock_control']==1){
                if($product['qty']<$param['qty']){
                    throw new \Exception(lang('product_inventory_shortage'));
                }
                $this->where('id', $param['product_id'])->dec('qty', $param['qty'])->update();
            }
            if($product['type']=='server_group'){
                // 域名相关
                $ProductGroupModel = new ProductGroupModel();
                $productGroupType = $ProductGroupModel->where('id',$product['product_group_id'])->value("type");
                if ($productGroupType=="domain"){
                    $customGetModul = hook_one("custom_get_module",['domain'=>$value['config_options']['domain']??""]);
                    $server = ServerModel::where('server_group_id', $product['rel_id'])->where('status', 1)->where("module",$customGetModul)->find();
                    $serverId = $server['id'] ?? 0;
                }else{
                    $server = ServerModel::where('server_group_id', $product['rel_id'])->where('status', 1)->find();
                    $serverId = $server['id'] ?? 0;
                }
            }else{
                $serverId = $product['rel_id'];
            }

            $upstreamProduct = UpstreamProductModel::where('product_id', $param['product_id'])->find();
            for ($i=1; $i<=$param['qty']; $i++) {
                $host = HostModel::create([
                    'client_id' => $clientId,
                    'order_id' => $order->id,
                    'product_id' => $param['product_id'],
                    'server_id' => $serverId,
                    'name' => generate_host_name(),
                    'status' => 'Unpaid',
                    'first_payment_amount' => $param['price'],
                    'renew_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $param['renew_price'] : 0,
                    'billing_cycle' => $product['pay_type'],
                    'billing_cycle_name' => $param['billing_cycle'],
                    'billing_cycle_time' => $param['duration'],
                    'active_time' => $time,
                    'due_time' => $product['pay_type']!='onetime' ? $time : 0,
                    'create_time' => $time,
                    'base_price' => $param['base_price'],
                    'base_renew_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $param['renew_price'] : 0,
                ]);

                if($upstreamProduct){
                    UpstreamHostModel::create([
                        'supplier_id' => $upstreamProduct['supplier_id'],
                        'host_id' => $host->id,
                        'upstream_configoption' => json_encode($param['config_options']),
                        'create_time' => $time
                    ]);
                    UpstreamOrderModel::create([
                        'supplier_id' => $upstreamProduct['supplier_id'],
                        'order_id' => $order->id,
                        'host_id' => $host->id,
                        'amount' => $param['price'],
                        'profit' => $param['profit'],
                        'create_time' => $time
                    ]);
                    $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                    $ResModuleLogic->afterSettle($product, $host->id, $param['config_options']);
                }else{
                    $param['config_options']['customfield'] = $param['customfield']??[];
                    $ModuleLogic->afterSettle($product, $host->id, $param['config_options']);
                }
                $hostIds[] = $host->id;
                //$des = $product['name'] . '(' .$host['name']. '),购买时长:'.$host['billing_cycle_name'] .'(' . date('Y/m/d H',$host['active_time']) . '-'. date('Y/m/d H',$host['active_time']) .')';
                if (in_array($host['billing_cycle'],['onetime','free'])){
                    $desDueTime = '∞';
                }else{
                    $desDueTime = date('Y/m/d',time() + intval($host['billing_cycle_time']));
                }
                $des = lang('order_description_append',['{product_name}'=>$product['name'],'{name}'=>$host['name'],'{billing_cycle_name}'=>$host['billing_cycle_name'],'{time}'=>date('Y/m/d',$host['active_time']) . '-' . $desDueTime]);
                if (is_array($param['description'])){
                    $param['description'] = implode("\n",$param['description']);
                }

                $orderItem[] = [
                    'order_id' => $order->id,
                    'client_id' => $clientId,
                    'host_id' => $host->id,
                    'product_id' => $param['product_id'],
                    'type' => 'host',
                    'rel_id' => $host->id,
                    'amount' => bcadd($param['price'], $param['discount']),
                    'description' => $param['description'] . "\n" . $des,
                    'create_time' => $time,
                ];

                foreach($appendOrderItem as $v){
                    $v['order_id'] = $order->id;
                    $v['client_id'] = $clientId;
                    $v['host_id'] = $host->id;
                    $v['product_id'] = $param['product_id'];
                    $v['create_time'] = $time;
                    $orderItem[] = $v;
                }

                // 保存自定义字段
                $selfDefinedFieldValue = [];
                foreach($param['self_defined_field'] as $k=>$v){
                    $selfDefinedFieldValue[] = [
                        'self_defined_field_id' => $k,
                        'relid'                 => $host->id,
                        'value'                 => (string)$v,
                        'order_id'              => $order->id,
                        'create_time'           => $time,
                    ];
                }
                $SelfDefinedFieldValueModel->insertAll($selfDefinedFieldValue);
            }

            // 创建订单子项
            $OrderItemModel = new OrderItemModel();
            $OrderItemModel->saveAll($orderItem);

            hook('after_order_create',['id'=>$order->id,'customfield'=>$param['customfield']??[]]);

            update_upstream_order_profit($order->id);

            $OrderModel = new OrderModel();
            # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
            $amount = $OrderModel->where('id',$order->id)->value('amount');

            if($amount<=0){
                $OrderModel = new OrderModel();
                $OrderModel->processPaidOrder($order->id);
            }

            # 记录日志
            active_log(lang('submit_order', ['{client}'=>'#'.$clientId.request()->client_name, '{order}'=>$order->id, '{product}'=>'product#'.$product['id'].'#'.$product['name'].'#']), 'order', $order->id);

            // wyh 20240402 新增 支付后跳转地址
            $domain = configuration('website_url');
            if (count($hostIds)>1){
                $returnUrl = "{$domain}/finance.htm";
            }else{
                if (isset($hostIds[0]) && !empty($hostIds[0])){
                    $returnUrl = "{$domain}/productdetail.htm?id=".$hostIds[0];
                }else{
                    $returnUrl = "{$domain}/finance.htm";
                }
            }
            $order->save([
                'return_url' => $returnUrl,
            ]);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();

            return ['status' => 400, 'msg' => $e->getMessage()];
        }
        return ['status' => 200, 'msg' => lang('success_message'), 'data' => ['order_id' => $order->id, 'amount' => $amount]];
    }

    /**
     * 时间 2022-05-28
     * @title 商品价格计算
     * @desc 商品价格计算
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID required
     * @param   int param.qty - 数量 required
     * @param   array config_options - 模块自定义配置参数,格式{"configoption":{1:1,2:[2]},"cycle":2,"promo_code":"Af13S1ACj","event_promotion":12,"qty":1}
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.price - 价格
     * @return  string data.renew_price - 续费价格
     * @return  string data.billing_cycle - 周期名称
     * @return  int data.duration - 周期时长(秒)
     * @return  string data.description - 订单子项描述
     * @return  string data.base_price - 基础价格
     * @return  float data.price_total - 折扣后金额（各种优惠折扣处理后的金额，没有就是price价格）
     * @return  float data.price_promo_code_discount - 优惠码折扣金额（当使用优惠码，且有效时，才返回此字段）
     * @return  float data.price_client_level_discount - 客户等级折扣金额（当客户等级有效时，才返回此字段）
     * @return  float data.price_event_promotion_discount - 活动促销折扣金额（当活动促销有效时，才返回此字段）
     */
    public function productCalculatePrice($param)
    {
        $ProductModel = $this->find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        /*if(app('http')->getName() == 'home' && $ProductModel['hidden'] == 1){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }*/
        $param['config_options'] = $param['config_options'] ?? [];
        
        $upstreamProduct = UpstreamProductModel::where('product_id', $ProductModel['id'])->find();

        $qty = $param['qty']??1;

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->cartCalculatePrice($ProductModel, $param['config_options'], $qty, 'cal_price');
        }else{
            $ModuleLogic = new ModuleLogic();
            $result = $ModuleLogic->cartCalculatePrice($ProductModel, $param['config_options'], 1, 'cal_price');
        }
        if(isset($result['data']['profit'])) unset($result['data']['profit']);
        if(isset($result['data']['order_item'])) unset($result['data']['order_item']);

        // wyh 20240220 后端处理价格数据返回显示
        if ($result['status']==200){
            $paramRequest = request()->param();
            $isDownstream = isset($paramRequest['is_downstream']) && $paramRequest['is_downstream']==1;
            if ($isDownstream){
                $price = $result['data']['price']??0;
            }else{ // 返会给下游时，不做处理
                $price = bcadd($result['data']['price']??0,$result['data']['discount']??0,2);
            }

            // 上下游已处理数量
            if ($upstreamProduct){
                $result['data']['price'] = $price;
            }else{
                $result['data']['price'] = bcmul($price,$qty,2);
            }

            $result['data']['price_total'] = $result['data']['price'];

            $baseRenewPrice = $result['data']['renew_price'];

            // 1、代理老财务的上下游时，不做任何处理，ResModuleLogic会处理
            if ($isDownstream){

            }else{
                // 1、优惠码
                if (isset($param['config_options']['promo_code']) && !empty($param['config_options']['promo_code'])){
                    $hookPromoCodeResultsOrgins = hook('apply_promo_code', [
                            'host_id'=>0,
                            'price'=>bcdiv($result['data']['price'],$qty,2),
                            'scene'=>'new',
                            'qty'=>$qty,
                            'duration'=>$result['data']['duration']??0,
                            'product_id'=>$param['product_id'],
                            'promo_code'=>$param['config_options']['promo_code']
                        ]
                    );
                    foreach ($hookPromoCodeResultsOrgins as $hookPromoCodeResultsOrgin){
                        if ($hookPromoCodeResultsOrgin['status']==200){
                            $promocodeDiscount = $hookPromoCodeResultsOrgin['data']['discount']??0;
                            $result['data']['price_promo_code_discount'] = $promocodeDiscount;
                            $result['data']['price_total'] = bcsub($result['data']['price_total'],$result['data']['price_promo_code_discount'],2);
                        }
                    }
                    // 续费单独处理
                    $hookPromoCodeResultsOrginsRenew = hook('apply_promo_code', [
                            'host_id'=>0,
                            'price'=>$result['data']['renew_price'],
                            'scene'=>'new',
                            'qty'=>1,
                            'duration'=>$result['data']['duration']??0,
                            'product_id'=>$param['product_id'],
                            'promo_code'=>$param['config_options']['promo_code']
                        ]
                    );
                    foreach ($hookPromoCodeResultsOrginsRenew as $hookPromoCodeResultsOrginRenew){
                        if ($hookPromoCodeResultsOrginRenew['status']==200){
                            //$result['data']['price_promo_code_discount'] = $hookPromoCodeResultsOrginRenew['data']['discount']??0;
                            $baseRenewPrice = bcsub($baseRenewPrice,$hookPromoCodeResultsOrginRenew['data']['discount']??0,2);
                        }
                    }
                }
                // 2、客户等级，考虑到DCIM配置设置客户等级，需要模块里面返回折扣金额
                if (isset($result['data']['discount'])){
                    $result['data']['price_client_level_discount'] = bcmul($result['data']['discount'],$qty,2);
                    $result['data']['price_total'] = bcsub($result['data']['price_total'],$result['data']['price_client_level_discount'],2);
                }else{
                    $hookClientLevelResultsOrgins = hook("client_discount_by_amount",[
                        'client_id'=>get_client_id(),
                        'product_id'=>$param['product_id'],
                        'amount'=>$result['data']['price']
                    ]);
                    foreach ($hookClientLevelResultsOrgins as $hookClientLevelResultsOrgin){
                        if ($hookClientLevelResultsOrgin['status']==200){
                            $clientLevelDiscount = $hookClientLevelResultsOrgin['data']['discount']??0;
                            $result['data']['price_client_level_discount'] = $clientLevelDiscount;
                            $result['data']['price_total'] = bcsub($result['data']['price_total'],$clientLevelDiscount,2);
                        }
                    }
                    // wyh 20240521 续费单独处理
                    $hookClientLevelResultsOrginsRenew = hook("client_discount_by_amount",[
                        'client_id'=>get_client_id(),
                        'product_id'=>$param['product_id'],
                        'amount'=>$result['data']['renew_price']
                    ]);
                    foreach ($hookClientLevelResultsOrginsRenew as $hookClientLevelResultsOrginRenew){
                        if ($hookClientLevelResultsOrginRenew['status']==200){
                            $baseRenewPrice = bcsub($baseRenewPrice,$hookClientLevelResultsOrginRenew['data']['discount']??0,2);
                        }
                    }
                }

                // 3、活动
                if (isset($param['config_options']['event_promotion']) && !empty($param['config_options']['event_promotion'])){
                    $hookEventPromotionResultsOrgins = hook("event_promotion_by_amount",[
                        'event_promotion' => $param['config_options']['event_promotion'],
                        'product_id' => $param['product_id'],
                        'qty' => $qty,
                        'amount' => bcdiv($result['data']['price'],$qty,2),
                        'billing_cycle_time' => $result['data']['duration']??0,
                    ]);
                    foreach ($hookEventPromotionResultsOrgins as $hookEventPromotionResultsOrgin){
                        if ($hookEventPromotionResultsOrgin['status']==200){
                            // 活动促销已计算数量
                            $eventPromotionDiscount = $hookEventPromotionResultsOrgin['data']['discount']??0;
                            $result['data']['price_event_promotion_discount'] = $eventPromotionDiscount;
                            $result['data']['price_total'] = bcsub($result['data']['price_total'],$result['data']['price_event_promotion_discount'],2);
                        }
                    }
                    // 续费单独处理
                    $hookEventPromotionResultsOrginsRenew = hook("event_promotion_by_amount",[
                        'event_promotion' => $param['config_options']['event_promotion'],
                        'product_id' => $param['product_id'],
                        'qty' => 1,
                        'amount' => $result['data']['renew_price'],
                        'billing_cycle_time' => $result['data']['duration']??0,
                    ]);
                    foreach ($hookEventPromotionResultsOrginsRenew as $hookEventPromotionResultsOrginRenew){
                        if ($hookEventPromotionResultsOrginRenew['status']==200){
                            //$result['data']['price_event_promotion_discount'] = $hookEventPromotionResultsOrginRenew['data']['discount']??0;
                            $baseRenewPrice = bcsub($baseRenewPrice,$hookEventPromotionResultsOrginRenew['data']['discount']??0,2);
                        }
                    }
                }
            }

            $result['data']['renew_price'] = $baseRenewPrice;
        }

        return $result;
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
        
        $upstreamProduct = UpstreamProductModel::where('product_id', $ProductModel['id'])->find();

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->allConfigOption($ProductModel);
        }else{
            $ModuleLogic = new ModuleLogic();
            $result = $ModuleLogic->allConfigOption($ProductModel);
        }
        return $result;
    }

    /**
     * 时间 2022-10-11
     * @title 获取商品库存
     * @desc 获取商品库存
     * @author theworld
     * @version v1
     * @param int id - 商品ID
     * @return int id - ID
     * @return int stock_control - 库存控制0:关闭1:启用
     * @return int qty - 库存数量
     */
    public function productStock($id)
    {
        $product = $this->field('id,stock_control,qty')
            ->where('hidden', 0)
            ->where('id', $id)
            ->find();

        return $product?:(object)[];
    }

    /**
     * 时间 2023-01-29
     * @title 商品搜索
     * @desc 商品搜索
     * @author hh
     * @version v1
     * @param string param.keywords - 关键字,搜索范围:商品ID,商品名,描述
     * @param int param.product_group_id - 商品分组ID
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,name
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 商品列表
     * @return int list[].id - 商品ID
     * @return string list[].name - 商品名
     * @return int list[].stock_control - 是否开启库存控制:1开启,0关闭
     * @return int list[].qty - 库存
     * @return string list[].pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return int list[].hidden - 是否隐藏:1隐藏,0显示
     * @return int list[].product_group_name_second - 二级分组名称
     * @return int list[].product_group_id_second - 二级分组ID
     * @return int list[].product_group_name_first - 一级分组名称
     * @return int list[].product_group_id_first - 一级分组ID
     * @return int list[].agentable - 是否可代理商品0否1是
     * @return int list[].agent - 代理商品0否1是
     * @return int list[].host_num - 产品数量
     * @return int count - 商品总数
     */
    public function productListSearch($param)
    {
        // 获取当前应用
       $app = app('http')->getName();

        if (!isset($param['orderby']) || !in_array($param['orderby'],['id','name'])){
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
            if(!empty($param['type']) && !empty($param['rel_id'])){
                $query->where('p.type', $param['type'])->where('p.rel_id', $param['rel_id']);
            }
            if($app=='home'){
                $query->where('p.hidden', 0);
            }/*else{
                $query->where('pgf.name','<>','应用商店');
            }*/
        };
        if($app=='home'){
            $field = 'p.id,p.name';
        }else{
            $field = 'p.id,p.name,p.stock_control,p.qty,p.hidden,p.pay_type,pg.name as product_group_name_second,pg.id as product_group_id_second,pgf.name as product_group_name_first,pgf.id as product_group_id_first,up.id upstream_product_id,p.agentable';
            $hostNum =  HostModel::field('COUNT(id) host_num,product_id')
                ->where('is_delete', 0)
                ->group('product_id')
                ->select()
                ->toArray();
            $hostNum = array_column($hostNum, 'host_num', 'product_id');
        }
        
        $products = $this->alias('p')
            ->field($field)
            ->leftjoin('product_group pg','p.product_group_id=pg.id')
            ->leftjoin('product_group pgf','pg.parent_id=pgf.id')
            ->leftjoin('upstream_product up','up.product_id=p.id')
            ->where($where)
            ->limit((isset($param['limit']) && !empty($param['limit']))?intval($param['limit']):1000000)
            ->page((isset($param['page']) && !empty($param['page']))?intval($param['page']):1)
            #->order($param['orderby'], (isset($param['sort']) && !empty($param['sort']))?$param['sort']:"desc")
            ->order('p.order','desc')
            ->select()
            ->toArray();

        $count = $this
            ->alias('p')
            ->where($where)
            ->count();
        if($app!='home'){
            foreach ($products as $key => $value) {
                $products[$key]['agent'] = !empty($value['upstream_product_id']) ? 1 : 0;
                $products[$key]['host_num'] = $hostNum[$value['id']] ?? 0;
                unset($products[$key]['upstream_product_id']);
            }
        }

        return ['list'=>$products, 'count'=>$count];
    }

    /**
     * 时间 2023-01-29
     * @title 设置商品最低周期价格
     * @desc 设置商品最低周期价格
     * @author hh
     * @version v1
     * @param   int|ProductModel id - 商品ID|ProductModel实例
     * @param   float price - 价格
     * @param   string cycle - 周期
     * @return  bool|array - false=未修改,array=修改的数据
     * @return  float price - 价格
     * @return  string cycle - 周期
     */
    public function setPriceCycle($id = null, $price = null, $cycle = null)
    {
        if(is_numeric($id)){
            $ProductModel = ProductModel::find($id);
        }else if($id instanceof ProductModel){
            $ProductModel = $id;
        }else{
            $ProductModel = $this;
        }
        if(!isset($ProductModel->id) || empty($ProductModel)){
            return false;
        }
        $update = [];
        if($ProductModel['pay_type'] == 'free'){
            $update['price'] = 0;
            $update['cycle'] = '免费';
        }else if($ProductModel['pay_type'] == 'onetime'){
            if(is_numeric($price)){
                $update['price'] = $price;
            }
            $update['cycle'] = '一次性';
        }else{
            if(is_numeric($price)){
                $update['price'] = $price;
            }
            if(!is_null($cycle)){
                $update['cycle'] = $cycle;
            }
        }
        if(empty($update)){
            return false;
        }
        $this->where('id', $ProductModel->id)->update($update);
        return $update;
    }

    /**
     * 时间 2023-02-16
     * @title 获取上游模块资源
     * @desc 获取上游模块资源
     * @author hh
     * @version v1
     * @param   int param.id - 商品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.module - resmodule名称
     * @return  string data.url - zip包完整下载路径
     * @return  string data.version - 版本号
     */
    public function downloadResource($param)
    {
        $ProductModel = $this->find((int)$param['id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        $upstreamProduct = UpstreamProductModel::where('product_id', $ProductModel['id'])->find();

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->downloadResource($ProductModel);
        }else{
            $ModuleLogic = new ModuleLogic();
            $result = $ModuleLogic->downloadResource($ProductModel);
        }
        return $result;
    }

    /**
     * 时间 2023-02-20
     * @title 保存可代理商品
     * @desc 保存可代理商品
     * @author theworld
     * @version v1
     * @param array param.id - 商品ID require
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */ 
    public function saveAgentableProduct($param)
    {
        $param['id'] = $param['id'] ?? [];
        if(!is_array($param['id']) || empty($param['id'])){
            return ['status'=>400,'msg'=>lang('param_error')];
        }
        $product = $this->whereIn('id', $param['id'])->select()->toArray();
        if (empty($product)){
            return ['status'=>400,'msg'=>lang('param_error')];
        }

        idcsmart_cache('product:list',null);

        $this->startTrans();
        try{
            $this->where('agentable', 1)->update(['agentable' => 0]);
            $this->whereIn('id', $param['id'])->update(['agentable' => 1]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('fail_message') . $e->getMessage()];
        }

        idcsmart_cache('product:list',null);

        return ['status'=>200,'msg'=>lang('success_message')];
    }


    /**
     * 时间 2022-10-12
     * @title 根据模块获取商品列表
     * @desc 根据模块获取商品列表
     * @author theworld
     * @version v1
     * @param string param.module - 模块名称
     * @return array list - 一级分组列表
     * @return int list[].id - 一级分组ID
     * @return string list[].name - 一级分组名称
     * @return array list[].child - 二级分组
     * @return int list[].child[].id - 二级分组ID
     * @return string list[].child[].name - 二级分组名称
     * @return array list[].child[].child - 商品
     * @return int list[].child[].child[].id - 商品ID
     * @return string list[].child[].child[].name - 商品名称
     */
    public function resModuleProductList($param)
    {
        $where = function (Query $query) use($param) {
            $query->where('p.hidden', 0)->where('up.res_module', '<>', '');
            if(!empty($param['module'])){
                $query->where('up.res_module', $param['module']);
            }
        };

        $ProductGroupModel = new ProductGroupModel();
        $firstGroup = $ProductGroupModel->productGroupFirstList();
        $firstGroup = $firstGroup['list'];

        $secondGroup = $ProductGroupModel->productGroupSecondList([]);
        $secondGroup = $secondGroup['list'];

        $products = $this->alias('p')
            ->field('p.id,p.name,p.product_group_id')
            ->leftjoin('upstream_product up','up.product_id=p.id')
            ->where($where)
            ->order('p.order','desc')
            ->select()
            ->toArray();
        $productArr = [];
        foreach ($products as $key => $value) {
            $productArr[$value['product_group_id']][] = ['id' => $value['id'], 'name' => $value['name']];
        }
        $secondGroupArr = [];
        foreach ($secondGroup as $key => $value) {
            if(isset($productArr[$value['id']])){
                $secondGroupArr[$value['parent_id']][] = ['id' => $value['id'], 'name' => $value['name'], 'child' => $productArr[$value['id']]];
            }
        }
        $list = [];
        foreach ($firstGroup as $key => $value) {
            if(isset($secondGroupArr[$value['id']])){
                $list[] = ['id' => $value['id'], 'name' => $value['name'], 'child' => $secondGroupArr[$value['id']]];
            }
        }

        return ['list'=>$list];
    }

    /**
     * 时间 2023-10-16
     * @title 复制商品
     * @desc  复制商品
     * @author theworld
     * @version v1
     * @param   int param.id - 商品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function copyProduct($param)
    {
        $id = intval($param['id']);

        $product = $this->find($id);
        if (empty($product)){
            return ['status'=>400,'msg'=>lang('product_is_not_exist')];
        }

        if (!empty($product['product_id'])){
            return ['status'=>400,'msg'=>lang('son_product_cannot_copy')];
        }

        $UpstreamProductModel = new UpstreamProductModel();
        $upstreamProduct = $UpstreamProductModel->where('product_id', $id)->find();
        if (!empty($upstreamProduct)){
            return ['status'=>400,'msg'=>lang('agent_product_cannot_copy')];
        }

        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
        $selfDefinedField = SelfDefinedFieldModel::where('relid', $id)->where('type', 'product')->select()->toArray();

        // 双删
        idcsmart_cache('product:list',null);

        $this->startTrans();

        try{

            $data = $product->toArray();
            unset($data['id']);
            $data['name'] = $data['name'].'(1)';

            $product = $this->create($data);

            $sonProduct = $this->where('product_id', $id)->select()->toArray();
            $sonProductIdArr = [];
            foreach ($sonProduct as $key => $value) {
                $sonProductIdArr[$value['id']] = 0;
                unset($value['id']);
                $value['product_id'] = $product->id;
                $r = $this->create($value);
                $sonProductIdArr[$value['id']] = $r->id;
            }

            $ProductUpgradeProductModel = new ProductUpgradeProductModel();
            $upgrade = $ProductUpgradeProductModel->where('product_id', $id)->select()->toArray();
            $upgradeIds = [];
            foreach ($upgrade as $key => $value) {
                $upgradeIds[] = $value['upgrade_product_id'];
            }
            if(!empty($upgradeIds)){
                foreach ($upgradeIds as $upgradeId){
                    $insert[] = [
                        'product_id' => $product->id,
                        'upgrade_product_id' => $upgradeId
                    ];
                }
                $ProductUpgradeProductModel->saveAll($insert);
            }
            
            // 自定义字段
            if(!empty($selfDefinedField)){
                $selfDefinedFieldArr = [];
                foreach($selfDefinedField as $key=>$value){
                    unset($value['id']);
                    $value['relid'] = $product->id;
                    $selfDefinedFieldArr[] = $value;
                }
                $SelfDefinedFieldModel->insertAll($selfDefinedFieldArr);
            }
            
            # 记录日志
            active_log(lang('log_admin_copy_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$product->id.'#'.$data['name'].'#']),'product',$product->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        idcsmart_cache('product:list',null);

        $res = hook('after_product_copy',['id'=>$product->id, 'product_id' => $param['id'], 'son_product_id' => $sonProductIdArr, 'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('success_message')];

    }

    /**
     * 时间 2024-02-02
     * @title 商品名称获取器
     * @desc  商品名称name字段获取器,输出时可通过多语言插件修改
     * @author hh
     * @version v1
     * @param   string $value - 获取的商品名称 require
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

    /**
     * 时间 2024-03-20
     * @title 获取所有商品
     * @desc  获取所有商品，树形图结构
     * @author theworld
     * @version v1
     * @return array [] - 商品一级分组
     * @return string [].name - 商品一级分组名称
     * @return array [].child - 商品二级分组
     * @return string [].child[].name - 商品二级分组名称
     * @return array [].child[].product - 商品
     * @return int [].child[].product[].id - 商品ID
     * @return string [].child[].product[].name - 商品名称
     */
    public function getProductList()
    {
        $ProductGroupModel = new ProductGroupModel();
        $firstGroup =  $ProductGroupModel->field('id,name')
            ->where('parent_id', 0)
            ->select()
            ->toArray();
        $secondGroup =  $ProductGroupModel->field('id,name,parent_id')
            ->where('parent_id', '>', 0)
            ->select()
            ->toArray();
        $product =  $this->field('id,name,product_group_id')
            ->select()
            ->toArray();
        
        $productArr = [];
        foreach ($product as $key => $value) {
            $productArr[$value['product_group_id']][] = ['id' => $value['id'], 'name' => $value['name']];
        }

        $secondGroupArr = [];
        foreach ($secondGroup as $key => $value) {
            if(isset($productArr[$value['id']])){
                $secondGroupArr[$value['parent_id']][] = ['id' => 'sg'.$value['id'], 'name' => $value['name'], 'children' => $productArr[$value['id']]];
            }
        }    
        
        $list = [];
        foreach ($firstGroup as $key => $value) {
            if(isset($secondGroupArr[$value['id']])){
                $list[] = ['id' => 'fg'.$value['id'], 'name' => $value['name'], 'children' => $secondGroupArr[$value['id']]];
            }
        }   
        return $list;     
    }
}
