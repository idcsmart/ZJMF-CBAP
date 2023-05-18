<?php
namespace server\mf_dcim\controller\admin;

use server\mf_dcim\model\LineModel;
use server\mf_dcim\model\OptionModel;
use server\mf_dcim\validate\LineValidate;
use server\mf_dcim\validate\LineBwValidate;
use server\mf_dcim\validate\LineFlowValidate;
use server\mf_dcim\validate\LineDefenceValidate;
use server\mf_dcim\validate\LineIpValidate;

/**
 * @title DCIM(自定义配置)-线路
 * @desc DCIM(自定义配置)-线路
 * @use server\mf_dcim\controller\admin\LineController
 */
class LineController{

	/**
	 * 时间 2023-02-02
	 * @title 添加线路
	 * @desc 添加线路
	 * @url /admin/v1/mf_dcim/line
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int data_center_id - 数据中心ID require
     * @param   string name - 名称 require
     * @param   string bill_type - 计费类型(bw=带宽计费,flow=流量计费) require
     * @param   string bw_ip_group - 带宽计费IP分组
     * @param   int defence_enable - 启用防护价格配置(0=关闭,1=开启) require
     * @param   string defence_ip_group - 防护IP分组
     * @param   array bw_data - 带宽计费数据 requireIf,bill_type=bw
     * @param   string bw_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @param   int bw_data[].value - 带宽
     * @param   int bw_data[].min_value - 最小值
     * @param   int bw_data[].max_value - 最大值
     * @param   int bw_data[].step - 步长
     * @param   object bw_data[].price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @param   string bw_data[].other_config.in_bw - 进带宽
     * @param   array flow_data - 流量计费数据 requireIf,bill_type=flow
     * @param   int flow_data[].value - 流量(GB,0=无限流量) require
     * @param   object flow_data[].price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @param   int flow_data[].other_config.in_bw - 进带宽 require
     * @param   int flow_data[].other_config.out_bw - 出带宽 require
     * @param   string flow_data[].other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环) require
     * @param   array defence_data - 防护数据
     * @param   int defence_data[].value - 防御峰值(G) require
     * @param   object defence_data[].price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @param   array ip_data - 公网IP数据
     * @param   int ip_data[].value - 公网IP数量 require
     * @param   object ip_data[].price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @return  int id - 线路ID
	 */
	public function create(){
		$param = request()->param();

		$LineValidate = new LineValidate();
		if (!$LineValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineValidate->getError())]);
        }
		$LineModel = new LineModel();

		$result = $LineModel->lineCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 修改线路
	 * @desc 修改线路
	 * @url /admin/v1/mf_dcim/line/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 线路ID require
     * @param   string name - 线路名称 require
     * @param   string bw_ip_group - 带宽计费IP分组
     * @param   int defence_enable - 启用防护价格配置(0=关闭,1=开启) require
     * @param   string defence_ip_group - 防护IP分组
	 */
	public function update(){
		$param = request()->param();

		$LineValidate = new LineValidate();
		if (!$LineValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineValidate->getError())]);
        }        
		$LineModel = new LineModel();

		$result = $LineModel->lineUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 删除线路
	 * @desc 删除线路
	 * @url /admin/v1/mf_dcim/line/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 线路ID require
	 */
	public function delete(){
		$param = request()->param();

		$LineModel = new LineModel();

		$result = $LineModel->lineDelete((int)$param['id']);
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 线路详情
	 * @desc 线路详情
	 * @url /admin/v1/mf_dcim/line/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 线路ID require
     * @return  int id - 线路ID
     * @return  string name - 线路名称
     * @return  string bill_type - 计费类型(bw=带宽计费,flow=流量计费)
     * @return  string bw_ip_group - 带宽计费IP分组
     * @return  int defence_enable - 启用防护价格配置(0=关闭,1=开启)
     * @return  string defence_ip_group - 防护IP分组
     * @return  int bw_data[].id - 配置ID
     * @return  string bw_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  string bw_data[].value - 带宽
     * @return  int bw_data[].min_value - 带宽
     * @return  int bw_data[].max_value - 带宽
     * @return  string bw_data[].price - 价格
     * @return  string bw_data[].duration - 周期
     * @return  int flow_data[].id - 配置ID
     * @return  string flow_data[].value - 流量
     * @return  string flow_data[].price - 价格
     * @return  string flow_data[].duration - 周期
     * @return  int defence_data[].id - 配置ID
     * @return  string defence_data[].value - 流量
     * @return  string defence_data[].price - 价格
     * @return  string defence_data[].duration - 周期
     * @return  int ip_data[].id - 配置ID
     * @return  string ip_data[].value - 流量
     * @return  string ip_data[].price - 价格
     * @return  string ip_data[].duration - 周期
	 */
	public function index(){
		$param = request()->param();

		$LineModel = new LineModel();

		$data = $LineModel->lineIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 线路带宽配置详情
	 * @desc 线路带宽配置详情
	 * @url /admin/v1/mf_dcim/line_bw/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  string type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int value - 带宽
     * @return  int min_value - 最小值
     * @return  int max_value - 最大值
     * @return  int step - 最小变化值
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     * @return  string other_config.in_bw - 流入带宽
	 */
	public function lineBwIndex(){
		$param = request()->param();

		$OptionModel = new OptionModel();

		$data = $OptionModel->lineBwIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 添加线路带宽配置
	 * @desc 添加线路带宽配置
	 * @url /admin/v1/mf_dcim/line/:id/line_bw
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 线路ID require
     * @param   string type - 配置方式(radio=单选,step=阶梯,total=总量) require
     * @param   int value - 带宽 requireIf,type=radio
     * @param   int min_value - 最小值
     * @param   int max_value - 最大值
     * @param   int step - 步长
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @param   string other_config.in_bw - 进带宽
	 */
	public function lineBwCreate(){
		$param = request()->param();

		$LineBwValidate = new LineBwValidate();
		if (!$LineBwValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineBwValidate->getError())]);
        }
        $param['rel_type'] = OptionModel::LINE_BW;
        $param['rel_id'] = $param['id'];

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 修改线路带宽配置
	 * @desc 修改线路带宽配置
	 * @url /admin/v1/mf_dcim/line_bw/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
     * @param   int value - 带宽
     * @param   int min_value - 最小值
     * @param   int max_value - 最大值
     * @param   int step - 步长
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @param   string other_config.in_bw - 进带宽
	 */
	public function lineBwUpdate(){
		$param = request()->param();

		$LineBwValidate = new LineBwValidate();
		if (!$LineBwValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineBwValidate->getError())]);
        }

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 删除线路带宽配置
	 * @desc 删除线路带宽配置
	 * @url /admin/v1/mf_dcim/line_bw/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
	 */
	public function lineBwDelete(){
		$param = request()->param();

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionDelete((int)$param['id'], OptionModel::LINE_BW);
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 线路流量配置详情
	 * @desc 线路流量配置详情
	 * @url /admin/v1/mf_dcim/line_flow/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  int value - 流量
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     * @return  int other_config.in_bw - 入站带宽
     * @return  int other_config.out_bw - 出站带宽
     * @return  string other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环)
	 */
	public function lineFlowIndex(){
		$param = request()->param();

		$OptionModel = new OptionModel();

		$data = $OptionModel->lineFlowIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 添加线路流量配置
	 * @desc 添加线路流量配置
	 * @url /admin/v1/mf_dcim/line/:id/line_flow
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 线路ID require
     * @param   int value - 流量
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @param  	int other_config.in_bw - 入站带宽 require
     * @param  	int other_config.out_bw - 出站带宽 require
     * @param  	string other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环) require
	 */
	public function lineFlowCreate(){
		$param = request()->param();

		$LineFlowValidate = new LineFlowValidate();
		if (!$LineFlowValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineFlowValidate->getError())]);
        }
        $param['rel_type'] = OptionModel::LINE_FLOW;
        $param['rel_id'] = $param['id'];

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 修改线路流量配置
	 * @desc 修改线路流量配置
	 * @url /admin/v1/mf_dcim/line_flow/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
     * @param   int value - 带宽
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @param  	int other_config.in_bw - 入站带宽 require
     * @param  	int other_config.out_bw - 出站带宽 require
     * @param  	string other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环) require
	 */
	public function lineFlowUpdate(){
		$param = request()->param();

		$LineFlowValidate = new LineFlowValidate();
		if (!$LineFlowValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineFlowValidate->getError())]);
        }

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 删除线路流量配置
	 * @desc 删除线路流量配置
	 * @url /admin/v1/mf_dcim/line_flow/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
	 */
	public function lineFlowDelete(){
		$param = request()->param();

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionDelete((int)$param['id'], OptionModel::LINE_FLOW);
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 线路防护配置详情
	 * @desc 线路防护配置详情
	 * @url /admin/v1/mf_dcim/line_defence/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  int value - 防御峰值
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
	 */
	public function lineDefenceIndex(){
		$param = request()->param();

		$OptionModel = new OptionModel();

		$data = $OptionModel->lineDefenceIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 添加线路防护配置
	 * @desc 添加线路防护配置
	 * @url /admin/v1/mf_dcim/line/:id/line_defence
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 线路ID require
     * @param   int value - 防御峰值
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
	 */
	public function lineDefenceCreate(){
		$param = request()->param();

		$LineDefenceValidate = new LineDefenceValidate();
		if (!$LineDefenceValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineDefenceValidate->getError())]);
        }
        $param['rel_type'] = OptionModel::LINE_DEFENCE;
        $param['rel_id'] = $param['id'];

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 修改线路防护配置
	 * @desc 修改线路防护配置
	 * @url /admin/v1/mf_dcim/line_defence/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
     * @param   int value - 防御峰值
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
	 */
	public function lineDefenceUpdate(){
		$param = request()->param();

		$LineDefenceValidate = new LineDefenceValidate();
		if (!$LineDefenceValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineDefenceValidate->getError())]);
        }

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 删除线路防护配置
	 * @desc 删除线路防护配置
	 * @url /admin/v1/mf_dcim/line_defence/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
	 */
	public function lineDefenceDelete(){
		$param = request()->param();

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionDelete((int)$param['id'], OptionModel::LINE_DEFENCE);
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 线路IP配置详情
	 * @desc 线路IP配置详情
	 * @url /admin/v1/mf_dcim/line_ip/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  int value - IP数量
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
	 */
	public function lineIpIndex(){
		$param = request()->param();

		$OptionModel = new OptionModel();

		$data = $OptionModel->lineIpIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 添加线路IP配置
	 * @desc 添加线路IP配置
	 * @url /admin/v1/mf_dcim/line/:id/line_ip
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 线路ID require
     * @param   int value - IP数量
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
	 */
	public function lineIpCreate(){
		$param = request()->param();

		$LineIpValidate = new LineIpValidate();
		if (!$LineIpValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineIpValidate->getError())]);
        }
        $param['rel_type'] = OptionModel::LINE_IP;
        $param['rel_id'] = $param['id'];

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 修改线路IP配置
	 * @desc 修改线路IP配置
	 * @url /admin/v1/mf_dcim/line_ip/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
     * @param   int value - IP数量
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
	 */
	public function lineIpUpdate(){
		$param = request()->param();

		$LineIpValidate = new LineIpValidate();
		if (!$LineIpValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineIpValidate->getError())]);
        }

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 删除线路IP配置
	 * @desc 删除线路IP配置
	 * @url /admin/v1/mf_dcim/line_ip/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
	 */
	public function lineIpDelete(){
		$param = request()->param();

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionDelete((int)$param['id'], OptionModel::LINE_IP);
		return json($result);
	}






}