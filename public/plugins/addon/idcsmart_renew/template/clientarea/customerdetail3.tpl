
  <section class="admin-main">
    <div class="container-fluid">
      <div class="page-container">
        <div class="card">
          <div class="card-body">
            <div class="card-title row"> <div style="padding:0 15px;">{$Title}</div>
              <div class="col-lg-8 col-md-12 col-sm-12">
                {foreach $PluginsAdminMenu as $v}
                  {if $v['custom']}
                    <span  class="ml-2"><a  class="h5" href="{$v.url}" target="_blank">{$v.name}</a></span>
                  {else/}
                    <span  class="ml-2"> <a  class="h5" href="{$v.url}">{$v.name}</a></span>
                  {/if}
                {/foreach}
              </div>
            </div>
            <div class="tabs">
              <div class="tab-item">客户摘要</div>
              <div class="tab-item selected">个人资料</div>
              <div class="tab-item">产品/服务</div>
              <div class="tab-item">账单</div>
              <div class="tab-item">交易记录</div>
              <div class="tab-item">信用管理</div>
              <div class="tab-item">工单</div>
              <div class="tab-item">日志</div>
              <div class="tab-item">通知日志</div>
              <div class="tab-item">附件</div>
              <div class="tab-item">跟进状态</div>
            </div>
            <div class="tab-content mt-4">
              <div class="table-body fit">
                <div class="category">
                  <div class="category-title">基本信息</div>
                  <div class="category-body">
                    <div class="form-row">
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label class="require">员工角色</label>
                        <select class="form-control">
                          <option value="1">00:00</option>
                        </select>
                      </div>
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label>用户名</label>
                        <input type="text" class="form-control">
                      </div>
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label>真实姓名</label>
                        <input type="text" class="form-control">
                      </div>
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label>邮箱</label>
                        <input type="email" class="form-control">
                      </div>
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label>密码</label>
                        <input type="text" class="form-control">
                      </div>
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label>确认密码</label>
                        <input type="text" class="form-control">
                      </div>
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label>语言</label>
                        <input type="text" class="form-control">
                      </div>
                    </div>
                  </div>
                </div>

                <div class="category">
                  <div class="category-title">工单设置</div>
                  <div class="category-body">
                    <div class="form-row">
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label class="require">工单部门</label>
                        <input type="text" class="form-control">
                      </div>
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label>技术部门 <i class="far fa-question-circle" style="color: blue;" aria-hidden="true"
                            data-toggle="tooltip" data-placement="top" title="Tooltip on top"></i></label>
                        <input type="text" class="form-control">
                      </div>
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label>工单签名</label>
                        <input type="text" class="form-control">
                      </div>
                    </div>
                  </div>
                </div>

                <div class="category">
                  <div class="category-title">基本信息</div>
                  <div class="category-body">
                    <div class="form-row">
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label class="require">员工角色</label>
                        <select class="form-control">
                          <option value="1">00:00</option>
                        </select>
                      </div>
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label>用户名</label>
                        <input type="text" class="form-control">
                      </div>
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label>真实姓名</label>
                        <input type="text" class="form-control">
                      </div>
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label>邮箱</label>
                        <input type="email" class="form-control">
                      </div>
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label>密码</label>
                        <input type="text" class="form-control">
                      </div>
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label>确认密码</label>
                        <input type="text" class="form-control">
                      </div>
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label>语言</label>
                        <input type="text" class="form-control">
                      </div>
                    </div>
                  </div>
                </div>
                

                <div class="category">
                  <div class="category-title">账户设置</div>
                  <div class="category-body">
                    <div class="form-row">
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label class="require">手机</label>
                        <input type="text" class="form-control">
                      </div>
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label>邮箱</label>
                        <input type="text" class="form-control">
                      </div>
                      <div class="form-group col-lg-2 col-md-3 col-sm-4">
                        <label>QQ</label>
                        <input type="text" class="form-control">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="fix-bottom">
                  <div class="form-group row">
                    <div class="col-sm-10">
                      <button type="submit" class="btn btn-primary w-md">保存更改</button>
                      <button type="submit" class="btn btn-outline-secondary w-md">取消更改</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
 