<?php
namespace addon\idcsmart_ticket\model;

use addon\idcsmart_ticket\logic\IdcsmartTicketLogic;
use app\admin\model\AdminRoleLinkModel;
use app\admin\model\AdminRoleModel;
use app\admin\model\PluginModel;
use app\common\logic\UploadLogic;
use app\common\model\FileLogModel;
use app\common\model\HostModel;
use app\common\model\OrderModel;
use app\common\model\SystemLogModel;
use think\db\Query;
use think\Model;

/*
 * @author wyh
 * @time 2022-06-20
 */
class IdcsmartTicketModel extends Model
{
    protected $name = 'addon_idcsmart_ticket';

    # 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'ticket_num'                       => 'string',
        'num'                              => 'int',
        'client_id'                        => 'int',
        'title'                            => 'string',
        'ticket_type_id'                   => 'int',
        'content'                          => 'string',
        'status'                           => 'int',
        'attachment'                       => 'string',
        'last_reply_time'                  => 'int',
        'post_time'                        => 'int',
        'notes'                            => 'string',
        'admin_id'                         => 'int',
        'create_time'                      => 'int',
        'update_time'                      => 'int',
        'last_reply_admin_id'              => 'int',
        'post_admin_id'                    => 'int',
    ];

    # 是否后台
    public $isAdmin = false;

    # 检查是否工单所属部门的管理人员
    private function checkAdmin($id)
    {
        if (!$this->isAdmin){
            return true;
        }

        # 超级管理员查看所有?目前
        if (get_admin_id() == 1){
            return true;
        }

        $ticket = $this->find($id);
        if (empty($ticket)){
            return false;
        }

        $allowAdmin = IdcsmartTicketTypeAdminLinkModel::where('ticket_type_id', $ticket['ticket_type_id'])->column('admin_id');
        if(!in_array(get_admin_id(), $allowAdmin)){
            return false;
        }
        return true;
    }

    /**
     * 时间 2022-06-20
     * @title 工单列表
     * @desc 工单列表
     * @author wyh
     * @version v1
     * @param string keywords - 关键字
     * @param int status - 状态搜索(/console/v1/ticket/status get获取状态列表)
     * @param int ticket_type_id - 工单类型搜索(/console/v1/ticket/type get获取类型列表)
     * @param int client_ -
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list - 工单列表
     * @return int list[].id - ID
     * @return string list[].ticket_num - 工单号
     * @return string list[].title - 标题
     * @return string list[].name - 类型
     * @return int list[].post_time - 提交时间
     * @return string list[].client_id - 用户ID
     * @return string list[].username - 客户名称
     * @return array list[].hosts - 关联产品,数组
     * @return array list[].host_ids - 关联产品ID,数组(作跳转使用)
     * @return int list[].last_reply_time - 最近回复时间
     * @return string list[].status - 状态
     * @return string list[].color - 状态颜色
     * @return string list[].admin_name - 跟进人,为null时显示-
     * @return int list[].ticket_internal - 是否有内部工单插件:1是(显示新建内部工单按钮),0否
     * @return int list[].client_level -  客户等级客户ID
     * @return int list[].last_time -  最近操作时间
     * @return int count - 工单总数
     */
    public function ticketList($param)
    {
        $where = function (Query $query) use ($param){

            if (!$this->isAdmin){
                $query->where('t.client_id',get_client_id());
            }else{
                if (get_admin_id() != 1){ # 超级管理员查看所有?目前
                    $ticketTypeId = IdcsmartTicketTypeAdminLinkModel::where('admin_id', get_admin_id())->column('ticket_type_id');
                    $ticketTypeId = array_unique($ticketTypeId);

                    if(!empty($ticketTypeId)){
                        $query->whereIn('t.ticket_type_id', $ticketTypeId);
                    }else{
                        $query->where('t.id', 0);
                    }
                }

            }
            if (isset($param['status']) && $param['status'] && is_array($param['status'])){
                $query->whereIn('t.status',$param['status']);
            }else{
                if ($this->isAdmin){
                    $query->whereNotIn('t.status',[3,4]);
                }
            }

            if (isset($param['keywords']) && !empty($param['keywords'])){
                $query->where('t.ticket_num|t.title','like',"%{$param['keywords']}%");
            }

            if (isset($param['ticket_type_id']) && !empty($param['ticket_type_id'])){
                $query->where('t.ticket_type_id',$param['ticket_type_id']);
            }

            if ($this->isAdmin){
                if (isset($param['client_id']) && !empty($param['client_id'])){
                    $query->where('t.client_id',$param['client_id']);
                }
                if (isset($param['admin_id']) && !empty($param['admin_id'])){
                    $query->where('t.last_reply_admin_id',$param['admin_id']);
                }
                if (isset($param['host_id']) && !empty($param['host_id'])){
                    $query->where('h.id',$param['host_id']);
                }
            }

        };

        if ($this->isAdmin){
            // wyh 20230510 增加 关联订单 子商品订单或父商品订单
            $whereOr = function (Query $query)use($param){
                if (!empty($param['host_id'])){
                    if (class_exists('server\idcsmart_common_dcim\model\IdcsmartCommonSonHost')){
                        $IdcsmartCommonSonHost = new \server\idcsmart_common_dcim\model\IdcsmartCommonSonHost();
                        $links = $IdcsmartCommonSonHost->where('host_id',$param['host_id'])
                            ->whereOr('son_host_id',$param['host_id'])
                            ->select()->toArray();
                        $dcimOrderIds = array_column($links,'order_id');
                        $dcimHostIds = array_column($links,'host_id');
                        $dcimSonHostIds = array_column($links,'son_host_id');
                    }
                    if (class_exists('server\idcsmart_common_finance\model\IdcsmartCommonSonHost')){
                        $IdcsmartCommonSonHost = new \server\idcsmart_common_finance\model\IdcsmartCommonSonHost();
                        $links = $IdcsmartCommonSonHost->where('host_id',$param['host_id'])
                            ->whereOr('son_host_id',$param['host_id'])
                            ->select()->toArray();
                        $financeOrderIds = array_column($links,'order_id');
                        $financeHostIds = array_column($links,'host_id');
                        $financeSonHostIds = array_column($links,'son_host_id');
                    }
                    if (class_exists('server\idcsmart_common_cloud\model\IdcsmartCommonSonHost')){
                        $IdcsmartCommonSonHost = new \server\idcsmart_common_cloud\model\IdcsmartCommonSonHost();
                        $links = $IdcsmartCommonSonHost->where('host_id',$param['host_id'])
                            ->whereOr('son_host_id',$param['host_id'])
                            ->select()->toArray();
                        $cloudOrderIds = array_column($links,'order_id');
                        $cloudHostIds = array_column($links,'host_id');
                        $cloudSonHostIds = array_column($links,'son_host_id');
                    }
                    if (class_exists('server\idcsmart_common_business\model\IdcsmartCommonSonHost')){
                        $IdcsmartCommonSonHost = new \server\idcsmart_common_business\model\IdcsmartCommonSonHost();
                        $links = $IdcsmartCommonSonHost->where('host_id',$param['host_id'])
                            ->whereOr('son_host_id',$param['host_id'])
                            ->select()->toArray();
                        $businessOrderIds = array_column($links,'order_id');
                        $businessHostIds = array_column($links,'host_id');
                        $businessSonHostIds = array_column($links,'son_host_id');
                    }
                    // 续费 和 升降级订单
                    $hostIds = array_merge($dcimHostIds??[],$dcimSonHostIds??[],$financeHostIds??[],$financeSonHostIds??[],$cloudHostIds??[],$cloudSonHostIds??[],
                        $businessHostIds??[],$businessSonHostIds??[]);

                    if (!empty($hostIds)){
                        $query->whereIn('h.id',$hostIds);
                    }
                }
            };

            $PluginModel = new PluginModel();
            $plugin = $PluginModel->where('status',1)
                ->where('name','IdcsmartTicketInternal')
                ->where('module','addon')
                ->find();

            $tickets = $this->alias('t')
                ->field('t.id,t.client_id,t.ticket_num,t.title,tt.name,t.post_time,c.username,GROUP_CONCAT(p.name Separator \'^#@^\') as hosts,GROUP_CONCAT(h.id Separator \'^#@^\') as host_ids,t.last_reply_time,ts.name as status,ts.color,a.name as admin_name,c.id as client_level,tt.id as ticket_internal,(CASE WHEN t.last_reply_time=0 THEN t.post_time WHEN t.last_reply_time>0 THEN t.last_reply_time END) last_time')
                ->leftJoin('addon_idcsmart_ticket_type tt','t.ticket_type_id=tt.id')
                ->leftJoin('addon_idcsmart_ticket_status ts','ts.id=t.status')
                ->leftJoin('admin a','a.id=t.last_reply_admin_id')
                ->leftJoin('client c','c.id=t.client_id')
                ->leftJoin('addon_idcsmart_ticket_host_link thl','t.id=thl.ticket_id')
                ->leftJoin('host h','h.id=thl.host_id')
                ->leftJoin('product p','h.product_id=p.id')
                ->where($where)
                ->whereOr($whereOr)
                ->withAttr('hosts',function ($value){
                    if (!is_null($value)){
                        return explode('^#@^',$value);
                    }
                    return [];
                })
                ->withAttr('host_ids',function ($value){
                    if (!is_null($value)){
                        return explode('^#@^',$value);
                    }
                    return [];
                })
                ->withAttr('client_level',function ($value){
                    $hookResults = hook_one('client_level',['id'=>$value]);
                    if (!empty($hookResults)){
                        return $hookResults->background_color??"";
                    }else{
                        return '';
                    }
                })
                ->withAttr('ticket_internal',function ($value) use ($plugin){
                    if (!empty($plugin)){
                        return 1;
                    }
                    return 0;
                })
                ->group('t.id')
                ->limit($param['limit'])
                ->page($param['page'])
                ->order('last_time','desc')
                ->order('t.last_reply_time','desc')
                ->order('t.post_time','desc')
                ->order('t.id','desc')
                ->select()
                ->toArray();

            $count = $this->alias('t')
                ->leftJoin('addon_idcsmart_ticket_type tt','t.ticket_type_id=tt.id')
                ->leftJoin('addon_idcsmart_ticket_status ts','ts.id=t.status')
                ->leftJoin('admin a','a.id=t.last_reply_admin_id')
                ->leftJoin('client c','c.id=t.client_id')
                ->leftJoin('addon_idcsmart_ticket_host_link thl','t.id=thl.ticket_id')
                ->leftJoin('host h','h.id=thl.host_id')
                ->leftJoin('product p','h.product_id=p.id')
                ->where($where)
                ->whereOr($whereOr)
                ->group('t.id')
                ->count();

        }else{
            $tickets = $this->alias('t')
                ->field('t.id,t.ticket_num,t.title,tt.name,t.post_time,t.last_reply_time,ts.name as status,ts.color')
                ->leftJoin('addon_idcsmart_ticket_type tt','t.ticket_type_id=tt.id')
                ->leftJoin('addon_idcsmart_ticket_status ts','ts.id=t.status')
                ->where($where)
                ->group('t.id')
                ->limit($param['limit'])
                ->page($param['page'])
                ->order('t.status','desc')
                ->order('t.last_reply_time','desc')
                ->select()
                ->toArray();

            foreach($tickets as $k=>$v){
                $tickets[$k]['last_urge_time'] = cache('ticket_urge_time_limit_'.$v['id']) ?? '0';
            }

            $count = $this->alias('t')
                ->leftJoin('addon_idcsmart_ticket_type tt','t.ticket_type_id=tt.id')
                ->where($where)
                ->group('t.id')
                ->count();
        }

        $data = [
            'list' => $tickets,
            'count' => $count
        ];

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    /**
     * 时间 2022-06-21
     * @title 工单统计
     * @desc 工单统计
     * @author wyh
     * @version v1
     * @return int 1 - 待接单数量
     * @return int 2 - 待回复数量
     * @return int 3 - 已回复数量
     * @return int 5 - 处理中数量
     */
    public function statisticTicket()
    {
        $status = [1,2,3,5];

        $tickets = $this->where('client_id',get_client_id())
            ->whereIn('status',$status)
            ->column('status');

        $data = [];

        $statistics = array_count_values($tickets);
        foreach ($status as $item){
            $data[strtolower($item)] = $statistics[$item]??0;
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    /**
     * 时间 2022-06-20
     * @title 查看工单
     * @desc 查看工单
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     * @return object ticket - 工单详情
     * @return int ticket.client_id - 用户ID
     * @return int ticket.id - 工单ID
     * @return string ticket.title - 工单标题
     * @return string ticket.content - 内容
     * @return int ticket.ticket_type_id - 类型ID
     * @return string ticket.status - 状态,直接显示,结合color
     * @return string ticket.color - 状态颜色
     * @return int ticket.create_time - 创建时间
     * @return array ticket.attachment - 工单附件,数组,返回所有附件(附件以^符号分割,取最后一个值获取文件原名)
     * @return int ticket.last_reply_time - 工单最后回复时间
     * @return string ticket.username - 用户名
     * @return array ticket.host_ids - 关联产品ID,数组
     * @return array ticket.replies - 沟通记录,数组
     * @return string ticket.replies[].content - 内容
     * @return array ticket.replies[].attachment - 附件访问地址,数组
     * @return int ticket.replies[].create_time - 时间
     * @return string ticket.replies[].type - 类型:Client用户回复,Admin管理员回复
     * @return string ticket.replies[].client_name - 用户名,type==Client时用此值
     * @return string ticket.replies[].admin_name - 管理员名,type==Admin时用此值
     */
    public function indexTicket($id)
    {
        if (!$this->checkAdmin($id)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_current_admin_cannot_operate')];
        }

        $ticket = $this->alias('t')
            ->field('t.id,t.ticket_num,t.client_id,t.title,t.content,t.ticket_type_id,ts.name as status,ts.color,t.create_time,t.attachment,t.last_reply_time,c.username,t.post_admin_id,a.name as admin_name')
            ->leftJoin('client c','c.id=t.client_id')
            ->leftJoin('admin a','t.post_admin_id=a.id')
            ->leftJoin('addon_idcsmart_ticket_status ts','ts.id=t.status')
            ->withAttr('content',function ($value){
                if (!empty($value)){
                    return htmlspecialchars_decode($value);
                }
                return $value;
            })
            ->where('t.id',$id)
            ->find();
        if (empty($ticket)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_is_not_exist')];
        }
        $IdcsmartTicketLogic = new IdcsmartTicketLogic();
        $config = $IdcsmartTicketLogic->getDefaultConfig();

        $attachments = !empty($ticket->attachment)?explode(',',$ticket->attachment):[];
        foreach ($attachments as &$attachment){
            $attachment = $config['get_ticket_upload'] . $attachment;
            // TODO 对象存储,1.30日打包隐藏
            /*$FileLogModel = new FileLogModel();
            $fileLog = $FileLogModel->where('save_name',$attachment)->find();
            $res = generate_signature(['fid'=>$fileLog['uuid']],AUTHCODE);
            $attachment = request()->domain(). "/console/v1/resource/".$fileLog['name']."?fid=".$fileLog['uuid']."&rand_str=". $res['rand_str'] ."&sign=".$res['signature'];*/
        }
        $ticket->attachment = $attachments;

        $IdcsmartTicketHostLinkModel = new IdcsmartTicketHostLinkModel();
        $ticket['host_ids'] = $IdcsmartTicketHostLinkModel->where('ticket_id',$id)->column('host_id');

        $IdcsmartTicketReplyModel = new IdcsmartTicketReplyModel();

        if ($this->isAdmin){
            $field = 'tr.id,tr.content,tr.attachment,tr.create_time,tr.type,c.username as client_name,a.name as admin_name,c.id as client_id';
        }else{
            $field = 'tr.id,tr.content,tr.attachment,tr.create_time,tr.type,c.username as client_name,a.nickname as admin_name,c.id as client_id';
        }

        $ticketReplies = $IdcsmartTicketReplyModel->alias('tr')
            ->field($field)
            ->leftJoin('client c','c.id=tr.rel_id AND tr.type=\'Client\'')
            ->leftJoin('admin a','a.id=tr.rel_id AND tr.type=\'Admin\'')
            ->withAttr('attachment',function ($value) use ($config){
                if (!empty($value)){
                    $attachments = explode(',',$value);
                }else{
                    $attachments = [];
                }
                if (!empty($attachments)){
                    foreach ($attachments as &$attachment){
                        $attachment = $config['get_ticket_upload'] . $attachment;
                        // TODO 对象存储,1.30日打包隐藏
                        /*$FileLogModel = new FileLogModel();
                        $fileLog = $FileLogModel->where('save_name',$attachment)->find();
                        $res = generate_signature(['fid'=>$fileLog['uuid']],AUTHCODE);
                        $attachment = request()->domain(). "/console/v1/resource/".$fileLog['name']."?fid=".$fileLog['uuid']."&rand_str=". $res['rand_str'] ."&sign=".$res['signature'];*/
                    }
                }
                return $attachments;
            })
            ->withAttr('content',function ($value){
                if (!empty($value)){
                    return htmlspecialchars_decode($value);
                }
                return $value;
            })
            ->withAttr('admin_name',function ($value){
                if (is_null($value)){
                    return '';
                }

                return $value;
            })
            ->withAttr('client_name',function ($value){
                if (is_null($value)){
                    return '';
                }

                return $value;
            })
            ->where('tr.ticket_id',$id)
            ->order('tr.create_time','desc')
            ->select()->toArray();

        array_push($ticketReplies,['id'=>0,'content'=>$ticket->content,'attachment'=>$ticket->attachment,'create_time'=>$ticket->create_time,'type'=>'Client','client_name'=>$ticket->post_admin_id?$ticket['admin_name']:$ticket->username,'admin_name'=>'','client_id'=>$ticket['client_id']]);

        $ticket['replies'] = $ticketReplies;
        $data = [
            'ticket' => $ticket
        ];

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    /**
     * 时间 2022-06-20
     * @title 创建工单
     * @desc 创建工单
     * @author wyh
     * @version v1
     * @param string title - 工单标题 required
     * @param int ticket_type_id - 工单类型ID,/console/v1/ticket/type接口获取 required
     * @param array host_ids - 关联产品ID,数组(id从产品列表接口获取)
     * @param string content - 问题描述
     * @param array attachment - 附件,数组(后台调admin/v1/upload(前台调console/v1/upload)上传文件,取返回值save_name)
     */
    public function createTicket($param)
    {
        $this->startTrans();

        try{
            $IdcsmartTicketLogic = new IdcsmartTicketLogic();
            $ticketNum = $IdcsmartTicketLogic->ticketNum();

            $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();
            $idcsmartTicketType = $IdcsmartTicketTypeModel->find($param['ticket_type_id']);

            $clientId = $this->isAdmin?$param['client_id']:get_client_id();

            $ticket = $this->create([
                'ticket_num' => $ticketNum[0],
                'num' => $ticketNum[1],
                'client_id' => $clientId,
                'title' => $param['title'],
                'ticket_type_id' => $param['ticket_type_id'],
                'content' => isset($param['content'])?htmlspecialchars($param['content']):'',
                'status' => 1,
                'attachment' => (isset($param['attachment']) && !empty($param['attachment']))?implode(',',$param['attachment']):'',
                'last_reply_time' => 0,
                'create_time' => time(),
                'post_time' => time(),
                'notes' => $param['notes']??'',
                'post_admin_id' => get_admin_id()??0,
            ]);

            $IdcsmartTicketHostLinkModel = new IdcsmartTicketHostLinkModel();
            $hostIds = $param['host_ids']?:[];
            $insert = [];
            foreach ($hostIds as $item){
                $insert[] = [
                    'ticket_id' => $ticket->id,
                    'host_id' => $item
                ];
            }
            $IdcsmartTicketHostLinkModel->insertAll($insert);

            # 移动附件
            $UploadLogic = new UploadLogic($IdcsmartTicketLogic->getDefaultConfig('ticket_upload'));
            if (isset($param['attachment']) && !empty($param['attachment'])){
                $result = $UploadLogic->moveTo($param['attachment'],'','ticket');
                if (isset($result['error'])){
                    throw new \Exception($result['error']);
                }
            }

            if (!$this->isAdmin){
                # 记录日志
                active_log(lang_plugins('ticket_log_client_create_ticket', ['{client}'=>'client#'.$clientId.'#' .request()->client_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);
                //客户新增工单短信添加到任务队列
                add_task([
                    'type' => 'sms',
                    'description' => lang_plugins('client_create_ticket_send_sms'),
                    'task_data' => [
                        'name'=>'client_create_ticket',//发送动作名称
                        'client_id'=>get_client_id(),//客户ID
                        'template_param'=>[
                            'subject' => $param['title'],//工单名称
                        ],
                    ],
                ]);
                //客户新增工单邮件添加到任务队列
                add_task([
                    'type' => 'email',
                    'description' => lang_plugins('client_create_ticket_send_mail'),
                    'task_data' => [
                        'name'=>'client_create_ticket',//发送动作名称
                        'client_id'=>get_client_id(),//客户ID
                        'template_param'=>[
                            'subject' => $param['title'],//工单名称
                        ],
                    ],
                ]);
            }else{
                # 管理员创建工单日志
                active_log(lang_plugins('ticket_log_admin_create_ticket', ['{admin}'=>'admin#'.get_admin_id().'#' .request()->admin_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);
            }

            $this->commit();

        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['id'=>$ticket->id]];
    }

    /**
     * 时间 2022-06-21
     * @title 回复工单
     * @desc 回复工单
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     * @param string content - 回复内容,不超过3000个字符 required
     * @param array attachment - 附件,数组(后台调admin/v1/upload(前台调console/v1/upload)上传文件,取返回值save_name)
     * @return int ticket_reply_id - 回复ID
     */
    public function replyTicket($param)
    {
        $this->startTrans();

        try{
            $time = time();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticket = $this->find($id);
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            if ($this->isAdmin){
                $clientId = $ticket->client_id;
            }else{
                $clientId = get_client_id();
                if ($clientId != $ticket->client_id){
                    throw new \Exception(lang_plugins('ticket_is_not_exist'));
                }
                // 20240122 新增my的逻辑：根据关联产品的到期时间判断，到期后用户无法再回复
                if (class_exists('server\idcsmart_common_finance\model\IdcsmartCommonSonHost')){
                    $IdcsmartTicketHostLinkModel = new IdcsmartTicketHostLinkModel();
                    $hostId = $IdcsmartTicketHostLinkModel->where('ticket_id',$id)
                        ->order("ticket_id","desc")
                        ->value("host_id");
                    $HostModel = new HostModel();
                    $host = $HostModel->find($hostId);
                    if (!empty($host) && $host['due_time']<=$time){
                        throw new \Exception(lang_plugins("ticket_host_due_can_not_reply"));
                    }
                }
            }

            # 移动附件
            $IdcsmartTicketLogic = new IdcsmartTicketLogic();
            $UploadLogic = new UploadLogic($IdcsmartTicketLogic->getDefaultConfig('ticket_upload'));
            if (isset($param['attachment']) && !empty($param['attachment'])){
                $result = $UploadLogic->moveTo($param['attachment'],'','ticket');
                if (isset($result['error'])){
                    throw new \Exception($result['error']);
                }
            }

            $IdcsmartTicketReplyModel = new IdcsmartTicketReplyModel();
            $ticketReply = $IdcsmartTicketReplyModel->create([
                'ticket_id' => $id,
                'type' => $this->isAdmin?'Admin':'Client',
                'rel_id' => $this->isAdmin?get_admin_id():$clientId,
                'content' => htmlspecialchars($param['content']),
                'attachment' => (isset($param['attachment']) && !empty($param['attachment']))?implode(',',$param['attachment']):'',
                'create_time' => $time
            ]);

            # 状态逻辑
            $status = $ticket->status;
            if ($this->isAdmin){
                $status = 3;
            }else{
                if (!in_array($ticket->status,[1,2])){ # 待接收(处理中)工单用户回复,不改变状态
                    $status = 2;
                }
            }

            $update = [
                'last_reply_time' => $time,
                'status' => $status
            ];

            if ($this->isAdmin){
                $update['last_reply_admin_id'] = get_admin_id(); # 最近一次回复管理员ID(跟进人)
            }

            $ticket->save($update);

            # 记录日志
            if ($this->isAdmin){
                active_log(lang_plugins('ticket_log_admin_reply_ticket_admin', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);
                active_log(lang_plugins('ticket_log_admin_reply_ticket', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);
				//管理员回复工单短信添加到任务队列
				add_task([
					'type' => 'sms',
					'description' => lang_plugins('admin_reply_ticket_send_sms'),
					'task_data' => [
						'name'=>'admin_reply_ticket',//发送动作名称
						'client_id'=>$clientId,//客户ID
						'template_param'=>[
							'subject' => $ticket['title'],//工单名称
						],
					],		
				]);
				//管理员回复工单邮件添加到任务队列
				add_task([
					'type' => 'email',
					'description' => lang_plugins('admin_reply_ticket_send_mail'),
					'task_data' => [
						'name'=>'admin_reply_ticket',//发送动作名称
						'client_id'=>$clientId,//客户ID
						'template_param'=>[
							'subject' => $ticket['title'],//工单名称
						],
					],		
				]);
            }else{
                active_log(lang_plugins('ticket_log_client_reply_ticket', ['{client}'=>'client#'.get_client_id() .'#' .request()->client_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#','{content}'=>$ticketReply->content]), 'addon_idcsmart_ticket', $ticket->id);
            }
			
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['ticket_reply_id'=>$ticketReply->id]];
    }

    /**
     * 时间 2022-06-21
     * @title 催单
     * @desc 催单
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     */
    public function urgeTicket($param)
    {
        $this->startTrans();

        try{
            $time = time();
            $clientId = get_client_id();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticket = $this->where('id',$id)->where('client_id',$clientId)->find();
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            $lastUrgeTime = cache('ticket_urge_time_limit_'.$id);

            if ($lastUrgeTime && ($lastUrgeTime+15*60)>$time){
                throw new \Exception(lang_plugins('ticket_urge_time_limit_15_m'));
            }

            if ($ticket->status == 1){
                $ticket->save([
                    'post_time' => $time,
                    'update_time' => $time
                ]);
                cache('ticket_urge_time_limit_'.$id,$time);
            }elseif (in_array($ticket->status,[2,3])){
                # 发送站内通知

                cache('ticket_urge_time_limit_'.$id,$time);
            }else{ # 已解决或已关闭不可催单
                throw new \Exception(lang_plugins('ticket_status_is_not_allowed_urge'));
            }

            active_log(lang_plugins('ticket_log_client_urge_ticket', ['{client}'=>'client#'.get_client_id() .'#' .request()->client_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('ticket_urge_success')];
    }

    /**
     * 时间 2022-06-21
     * @title 关闭工单
     * @desc 关闭工单
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     */
    public function closeTicket($param)
    {
        $this->startTrans();

        try{
            $time = time();
            $clientId = get_client_id();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticket = $this->where('id',$id)->where('client_id',$clientId)->find();
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            if ($ticket->status == 4){
                throw new \Exception(lang_plugins('ticket_is_closed'));
            }

            $ticket->save([
                'status' => 4,
                'update_time' => $time
            ]);

            active_log(lang_plugins('ticket_log_client_close_ticket', ['{client}'=>'client#'.get_client_id() .'#' .request()->client_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);
			//客户关闭工单短信添加到任务队列
				add_task([
					'type' => 'sms',
					'description' => lang_plugins('client_close_ticket_send_sms'),
					'task_data' => [
						'name'=>'client_close_ticket',//发送动作名称
						'client_id'=>$clientId,//客户ID
						'template_param'=>[
							'subject' => $ticket['title'],//工单名称
						],
					],		
				]);
				//客户关闭工单邮件添加到任务队列
				add_task([
					'type' => 'email',
					'description' => lang_plugins('client_close_ticket_send_mail'),
					'task_data' => [
						'name'=>'client_close_ticket',//发送动作名称
						'client_id'=>$clientId,//客户ID
						'template_param'=>[
							'subject' => $ticket['title'],//工单名称
						],
					],		
				]);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('ticket_close_success')];
    }

    /**
     * 时间 2022-06-22
     * @title 接收工单
     * @desc 接收工单
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     */
    public function receiveTicket($param)
    {
        $this->startTrans();

        try{
            $time = time();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticket = $this->where('id',$id)->find();
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            if ($ticket->status != 1){
                throw new \Exception(lang_plugins('ticket_is_pending_can_handling'));
            }

            $ticket->save([
                'status' => 2,
                'update_time' => $time
            ]);

            active_log(lang_plugins('ticket_log_admin_receive_ticket', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('ticket_handle_success')];
    }

    /**
     * 时间 2022-06-22
     * @title 已解决工单
     * @desc 已解决工单
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     */
    public function resolvedTicket($param)
    {
        $this->startTrans();

        try{
            $time = time();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticket = $this->where('id',$id)->find();
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            if ($ticket->status == 1){
                throw new \Exception(lang_plugins('ticket_is_pending_cannot_resolved'));
            }

            if ($ticket->status == 4){
                throw new \Exception(lang_plugins('cannot_repeat_opreate'));
            }

            $ticket->save([
                'status' => 4,
                'update_time' => $time
            ]);

            active_log(lang_plugins('ticket_log_admin_resolved_ticket', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('ticket_resolved_success')];
    }

    /**
     * 时间 2022-07-22
     * @title 工单附件下载
     * @desc 工单附件下载
     * @author wyh
     * @version v1
     * @param string name - 附件名称 required
     */
    public function download($param)
    {
        if (!isset($param['name']) || empty($param['name'])){
            return json(['status'=>400,'msg'=>lang_plugins('ticket_attachment_name_require')]);
        }
        $filename = $param['name'];

        $address = IdcsmartTicketLogic::getDefaultConfig('ticket_upload');

        $file = $address . $filename;
        if (!file_exists($file)){
            return json(['status'=>400,'msg'=>lang_plugins('ticket_attachment_is_not_exist')]);
        }

        $orginName = explode('^',$filename)[1]?explode('^',$filename)[1]:$filename;

        return download($file,$orginName);
    }

    /**
     * 时间 2022-09-21
     * @title 转内部工单
     * @desc 转内部工单
     * @author wyh
     * @version v1
     * @param int ticket_id - 工单ID(转内部工单时需要传此参数)
     * @param string title - 内部工单标题 required
     * @param int ticket_type_id - 内部工单类型ID(调admin/v1/ticket/internal/type获取列表) required
     * @param string priority - 紧急程度:medium一般,high紧急 required
     * @param int client_id - 关联用户
     * @param int admin_role_id - 指定部门 required
     * @param int admin_id - 管理员ID
     * @param array host_ids - 关联产品ID,数组(/admin/v1/host?client_id= 获取所选客户的产品列表,取产品ID)
     * @param string content - 问题描述
     * @param array attachment - 附件,数组(后台调admin/v1/upload(前台调console/v1/upload)上传文件,取返回值save_name)
     */
    public function convert($param)
    {
        $ticket = $this->find($param['ticket_id']);

        $ticketAttachments = explode(',',$ticket->attachment??"")[0]?explode(',',$ticket->attachment):[];

        $param['attachment_old'] = $ticketAttachments;

        $IdcsmartTicketLogic = new IdcsmartTicketLogic();

        $param['ticket_upload'] = $IdcsmartTicketLogic->getDefaultConfig('ticket_upload');

        $result = plugin_api('idcsmart_ticket_internal','TicketInternal','create',$param);

        return $result;
    }

    /**
     * 时间 2022-06-21
     * @title 内部工单类型列表
     * @desc 内部工单类型列表
     * @author wyh
     * @version v1
     * @return array list - 工单类型列表
     * @return int list[].id - 工单类型ID
     * @return int list[].name - 工单类型名称
     * @return int list[].role_name - 默认接受部门
     */
    public function ticketInternalType()
    {
        $result = plugin_api('idcsmart_ticket_internal','TicketInternalType','ticketTypeList',[]);

        return $result;
    }

    /**
     * 时间 2022-09-23
     * @title 转交工单
     * @desc 转交工单
     * @author wyh
     * @version v1
     * @param int admin_id - 管理员ID required
     * @param int notes - 备注 required
     * @param int ticket_type_id - 部门ID required
     */
    public function forward($param)
    {
        $this->startTrans();

        try{
            $id = $param['id']??'';

            $ticket = $this->find($id);
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }
            $adminId = IdcsmartTicketTypeAdminLinkModel::where('ticket_type_id', $param['ticket_type_id'])->column('admin_id');
            if(!in_array($param['admin_id'], $adminId)){
                throw new \Exception(lang_plugins('ticket_admin_is_not_exist'));
            }
            $ticket->save([
                'admin_id' => $param['admin_id'],
                'notes' => $param['notes']??'',
                'ticket_type_id' => $param['ticket_type_id'],
                'update_time' => time()
            ]);

            $IdcsmartTicketTypeModel = IdcsmartTicketTypeModel::find($param['ticket_type_id']);

            active_log(lang_plugins('ticket_log_admin_ticket_forwad', ['{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#','admin_role'=>$IdcsmartTicketTypeModel['name']]), 'addon_idcsmart_ticket', $ticket->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-09-23
     * @title 修改工单状态
     * @desc 修改工单状态
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     * @param int status - 状态ID required
     * @param int ticket_type_id - 工单类型ID
     * @param array host_ids - 产品ID,数组
     */
    public function status($param)
    {
        $this->startTrans();

        try{
            $id = $param['id']??'';

            $ticket = $this->find($id);
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            $IdcsmartTicketStatusModel = new IdcsmartTicketStatusModel();
            $ticketStatus = $IdcsmartTicketStatusModel->where('id',$param['status']??0)->find();
            if (empty($ticketStatus)){
                throw new \Exception(lang_plugins('ticket_status_is_not_exist'));
            }

            $oldStatus = $ticket['status'];

            $oldType = $ticket['ticket_type_id'];

            $ticket->save([
                'status' => $param['status'],
                'ticket_type_id' => $param['ticket_type_id']??0,
                'update_time' => time()
            ]);

            $IdcsmartTicketHostLinkModel = new IdcsmartTicketHostLinkModel();
            $IdcsmartTicketHostLinkModel->where('ticket_id',$id)->delete();
            $hostIds = $param['host_ids']?:[];
            $insert = [];
            foreach ($hostIds as $item){
                $insert[] = [
                    'ticket_id' => $ticket->id,
                    'host_id' => $item
                ];
            }
            $IdcsmartTicketHostLinkModel->insertAll($insert);

            if ($oldStatus!=$param['status']){
                active_log(lang_plugins('ticket_log_admin_update_ticket_status', ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{ticket}'=>'ticket#'.$ticket['ticket_num'],'{status}'=>$ticketStatus['name']]), 'addon_idcsmart_ticket', $id);
            }

            if ($oldType!=$param['ticket_type_id']){
                $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();
                $ticketType = $IdcsmartTicketTypeModel->where('id',$param['ticket_type_id'])->find();
                active_log(lang_plugins('ticket_log_admin_update_ticket_type', ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{ticket}'=>'ticket#'.$ticket['ticket_num'],'{type}'=>$ticketType['name']]), 'addon_idcsmart_ticket', $id);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-09-23
     * @title 工单日志
     * @desc 工单日志
     * @author wyh
     * @version v1
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list -
     * @return int list[].id - ID
     * @return int list[].create_time - 记录时间
     * @return int list[].description - 描述
     */
    public function ticketLog($param)
    {
        $ticketId = $param['id']??0;
        $SystemLogModel = new SystemLogModel();
        $list = $SystemLogModel->field('id,description,create_time')
            ->where('type','addon_idcsmart_ticket')
            ->where('rel_id',$ticketId)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order('id','desc')
            ->select()->toArray();
        $count = $SystemLogModel->field('id,description,create_time')
            ->where('type','addon_idcsmart_ticket')
            ->where('rel_id',$ticketId)
            ->count();
        return [
            'status' => 200,
            'msg' =>lang_plugins('success_message'),
            'data' => [
                'list' => $list,
                'count' => $count
            ]
        ];
    }

    /**
     * 时间 2022-09-23
     * @title 修改工单内容
     * @desc 修改工单内容
     * @author wyh
     * @version v1
     * @param int id - 工单ID required
     * @param int content - 内容 required
     */
    public function updateContent($param)
    {
        $this->startTrans();

        try{
            $id = $param['id']??'';

            $ticket = $this->find($id);
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            $ticket->save([
                'content' => htmlspecialchars($param['content']),
                'update_time' => time()
            ]);

            active_log(lang_plugins('ticket_log_admin_update_ticket_content', ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{content}'=>$param['content']]), 'addon_idcsmart_ticket', $id);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    //
    public function afterAdminDelete($param){
        IdcsmartTicketTypeAdminLinkModel::where('admin_id', $param['id']??0)->delete();
    }

    /**
     * 时间 2024-01-22
     * @title 工单通知设置
     * @desc 工单通知设置
     * @author wyh
     * @version v1
     * @return int ticket_notice_open - 是否开启工单通知，1是默认，0否
     * @return string ticket_notice_description - 工单通知描述
     */
    public function ticketConfig(){
        $config = IdcsmartTicketLogic::getDefaultConfig();
        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'ticket_notice_open' => $config['ticket_notice_open']??1,
                'ticket_notice_description' => !empty($config['ticket_notice_description'])?htmlspecialchars_decode($config['ticket_notice_description']):"",
            ]
        ];
    }

}
