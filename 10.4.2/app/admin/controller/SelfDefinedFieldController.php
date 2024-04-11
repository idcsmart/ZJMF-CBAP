<?php
namespace app\admin\controller;

use app\common\model\SelfDefinedFieldModel;
use app\common\validate\SelfDefinedFieldValidate;

/**
 * @title 自定义字段管理
 * @desc 自定义字段管理
 * @use app\admin\controller\SelfDefinedFieldController
 */
class SelfDefinedFieldController extends AdminBaseController
{
	public function initialize()
    {
        parent::initialize();
    }

    /**
     * 时间 2024-01-02
     * @title 自定义字段列表
     * @desc  自定义字段列表
     * @url /admin/v1/self_defined_field
     * @method  GET
     * @author hh
     * @version v1
     * @param   string type product 类型(product=商品)
     * @param   int relid - 关联ID(商品ID) require
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
     * @return  int list[].upstream_id - 上游ID(大于0不能修改删除)
     * @return  int count - 总条数
     */
	public function selfDefinedFieldList()
    {
        $param = $this->request->param();
        
        $SelfDefinedFieldModel = new SelfDefinedFieldModel();

        $data = $SelfDefinedFieldModel->selfDefinedFieldList($param);

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data
        ];
        return json($result);
	}

    /**
     * 时间 2024-01-02
     * @title 添加自定义字段
     * @desc 添加自定义字段
     * @url /admin/v1/self_defined_field
     * @method  POST
     * @author hh
     * @version v1
     * @param   string type - 类型(product=商品) require
     * @param   int relid - 关联ID(商品ID) require
     * @param   string field_name - 字段名称 require
     * @param   int is_required - 是否必填(0=否,1=是) require
     * @param   string field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区) require
     * @param   string description - 字段描述
     * @param   string regexpr - 验证规则
     * @param   string field_option - 下拉选项 field_type=dropdown,require
     * @param   int show_order_page - 订单页可见(0=否,1=是) require
     * @param   int show_order_detail - 订单详情可见(0=否,1=是) require
     * @param   int show_client_host_detail - 前台产品详情可见(0=否,1=是) require
     * @param   int show_admin_host_detail - 后台产品详情可见(0=否,1=是) require
     * @param   int show_client_host_list - 会员中心列表显示(0=否,1=是) require
     * @return  int id - 自定义字段ID
     */
	public function create()
    {
		$param = $this->request->param();

        $SelfDefinedFieldValidate = new SelfDefinedFieldValidate();
        if (!$SelfDefinedFieldValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($SelfDefinedFieldValidate->getError())]);
        }

        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
        
        $result = $SelfDefinedFieldModel->selfDefinedFieldCreate($param);
        return json($result);
	}

    /**
     * 时间 2024-01-02
     * @title 修改自定义字段
     * @desc  修改自定义字段
     * @url /admin/v1/self_defined_field/:id
     * @method  PUT
     * @author hh
     * @version v1
     * @param   int id - 自定义字段ID require
     * @param   string field_name - 字段名称 require
     * @param   int is_required - 是否必填(0=否,1=是) require
     * @param   string field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区) require
     * @param   string description - 字段描述
     * @param   string regexpr - 验证规则
     * @param   string field_option - 下拉选项 field_type=dropdown,require
     * @param   int show_order_page - 订单页可见(0=否,1=是) require
     * @param   int show_order_detail - 订单详情可见(0=否,1=是) require
     * @param   int show_client_host_detail - 前台产品详情可见(0=否,1=是) require
     * @param   int show_admin_host_detail - 后台产品详情可见(0=否,1=是) require
     * @param   int show_client_host_list - 会员中心列表显示(0=否,1=是) require
     */
    public function update()
    {
        $param = $this->request->param();

        $SelfDefinedFieldValidate = new SelfDefinedFieldValidate();
        if (!$SelfDefinedFieldValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($SelfDefinedFieldValidate->getError())]);
        }

        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
        
        $result = $SelfDefinedFieldModel->selfDefinedFieldUpdate($param);
        return json($result);
    }

    /**
    * 时间 2024-01-02
    * @title 删除自定义字段
    * @desc  删除自定义字段
    * @url /admin/v1/self_defined_field/:id
    * @method  DELETE
    * @author hh
    * @version v1
    * @param   int id - 自定义字段ID require
    */
	public function delete()
    {
        $param = $this->request->param();

        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
        
        $result = $SelfDefinedFieldModel->selfDefinedFieldDelete($param);
        return json($result);
	}

    /**
     * 时间 2024-01-02
     * @title 拖动排序
     * @desc 拖动排序
     * @url /admin/v1/self_defined_field/:id/drag
     * @method  PUT
     * @author hh
     * @version v1
     * @param   int prev_id - 前一个自定义字段ID(0=表示置顶) require
     * @param   int id - 当前自定义字段ID require
     */
    public function dragToSort()
    {
        $param = request()->param();

        $SelfDefinedFieldValidate = new SelfDefinedFieldValidate();
        if (!$SelfDefinedFieldValidate->scene('drag')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($SelfDefinedFieldValidate->getError())]);
        }        
        $SelfDefinedFieldModel = new SelfDefinedFieldModel();

        $result = $SelfDefinedFieldModel->dragToSort($param);
        return json($result);
    }

}