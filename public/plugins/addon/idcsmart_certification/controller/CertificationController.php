<?php
namespace addon\idcsmart_certification\controller;

use addon\idcsmart_certification\model\CertificationLogModel;
use addon\idcsmart_certification\logic\IdcsmartCertificationLogic;

use addon\idcsmart_certification\validate\CertificationValidate;
use app\event\controller\PluginBaseController;

/**
 * @title 实名认证
 * @desc 实名认证
 * @use addon\idcsmart_certification\controller\CertificationController
 */
class CertificationController extends PluginBaseController
{
    /**
     * 时间 2022-10-13
     * @title 实名设置页面
     * @desc 实名设置页面
     * @url /admin/v1/certification/config
     * @method  GET
     * @author wyh
     * @version v1
     * @return int certification_open - 实名认证是否开启:1开启默认,0关
     * @return int certification_approval - 是否人工复审:1开启默认，0关
     * @return int certification_notice - 审批通过后,是否通知客户:1通知默认,0否
     * @return int certification_update_client_name - 是否自动更新姓名:1是,0否默认
     * @return int certification_upload - 是否需要上传证件照:1是,0否默认
     * @return int certification_update_client_phone - 手机一致性:1是,0否默认
     * @return int certification_uncertified_suspended_host - 未认证暂停产品:1是,0否默认
     */
    public function getConfig()
    {
        $IdcsmartCertificationLogic = new IdcsmartCertificationLogic();

        return json([
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $IdcsmartCertificationLogic->getConfig()
        ]);
    }

    /**
     * 时间 2022-10-13
     * @title 保存实名设置
     * @desc 保存实名设置
     * @url /admin/v1/certification/config
     * @method  POST
     * @author theworld
     * @version v1
     * @param int certification_open - 实名认证是否开启:1开启默认,0关 required
     * @param int certification_approval - 是否人工复审:1开启默认，0关 required
     * @param int certification_notice - 审批通过后,是否通知客户:1通知默认,0否 required
     * @param int certification_update_client_name - 是否自动更新姓名:1是,0否默认 required
     * @param int certification_upload - 是否需要上传证件照:1是,0否默认 required
     * @param int certification_update_client_phone - 手机一致性:1是,0否默认 required
     * @param int certification_uncertified_suspended_host - 未认证暂停产品:1是,0否默认 required
     */
    public function setConfig()
    {
        $param = $this->request->only(['certification_open', 'certification_approval', 'certification_notice' ,'certification_update_client_name',
            'certification_upload', 'certification_update_client_phone', 'certification_uncertified_suspended_host']);

        $validate = new CertificationValidate();
        if (!$validate->scene('set_config')->check($param)){
            return json(['status'=>400,'msg'=>$validate->getError()]);
        }

        $IdcsmartCertificationLogic = new IdcsmartCertificationLogic();

        $IdcsmartCertificationLogic->setConfig($param);

        return json(['status'=>200,'msg'=>lang_plugins('success_message')]);
    }

    /**
     * 时间 2022-9-23
     * @title 实名认证列表
     * @desc 实名认证列表
     * @url /admin/v1/certification
     * @method  GET
     * @author wyh
     * @version v1
     * @param string keywords - 关键字
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 实名认证列表
     * @return int list[].id - ID
     * @return int list[].name - 申请人
     * @return int list[].company - 公司
     * @return int list[].type - 认证类型1个人，2企业，3个人转企业
     * @return int list[].status - 1已认证，2未通过，3待审核，4已提交资料
     * @return int list[].auth_fail - 失败原因
     * @return int list[].create_time - 提交时间
     * @return int list[].company_organ_code - 营业执照号
     * @return int count - 实名认证总数
     */
    public function certificationList()
    {
        # 合并分页参数
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $CertificationLogModel = new CertificationLogModel();

        $result = $CertificationLogModel->certificationList($param);

        return json([
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $result
        ]);
    }

    /**
     * 时间 2022-9-23
     * @title 获取实名认证
     * @desc 获取实名认证
     * @url /admin/v1/certification/:id
     * @method  GET
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
    public function index()
    {
        $param = $this->request->param();

        $CertificationLogModel = new CertificationLogModel();

        $result = $CertificationLogModel->certificationIndex($param);

        return json($result);
    }

    /**
     * 时间 2022-9-23
     * @title 通过
     * @desc 通过
     * @url /admin/v1/certification/:id/approve
     * @method  PUT
     * @author wyh
     * @version v1
     * @param int id - 实名认证ID required
     */
    public function approve()
    {
        $param = $this->request->param();

        $CertificationLogModel = new CertificationLogModel();

        $result = $CertificationLogModel->approve($param);

        return json($result);
    }

    /**
     * 时间 2022-9-23
     * @title 驳回
     * @desc 驳回
     * @url /admin/v1/certification/:id/reject
     * @method  PUT
     * @author wyh
     * @version v1
     * @param int id - 实名认证ID required
     */
    public function reject()
    {
        $param = $this->request->param();

        $CertificationLogModel = new CertificationLogModel();

        $result = $CertificationLogModel->reject($param);

        return json($result);
    }

}