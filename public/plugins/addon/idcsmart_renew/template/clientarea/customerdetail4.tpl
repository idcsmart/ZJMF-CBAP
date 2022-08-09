
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
              <div class="tab-item">个人资料</div>
              <div class="tab-item">产品/服务</div>
              <div class="tab-item">账单</div>
              <div class="tab-item selected">交易记录</div>
              <div class="tab-item">信用管理</div>
              <div class="tab-item">工单</div>
              <div class="tab-item">日志</div>
              <div class="tab-item">通知日志</div>
              <div class="tab-item">附件</div>
              <div class="tab-item">跟进状态</div>
            </div>
            <div class="tab-content mt-4">
              <div class="row mb-4">
                <div class="col-sm-3">
                  <div class="card">
                    <div class="card-body text-center">
                      <h2>10</h2>
                      <h5>本月打开工单</h5>
                    </div>
                  </div>
                </div>
                <div class="col-sm-3">
                  <div class="card">
                    <div class="card-body text-center">
                      <h2>10</h2>
                      <h5>本月打开工单</h5>
                    </div>
                  </div>
                </div>
                <div class="col-sm-3">
                  <div class="card">
                    <div class="card-body text-center">
                      <h2>10</h2>
                      <h5>本月打开工单</h5>
                    </div>
                  </div>
                </div>
                <div class="col-sm-3">
                  <div class="card">
                    <div class="card-body text-center">
                      <h2>10</h2>
                      <h5>本月打开工单</h5>
                    </div>
                  </div>
                </div>
              </div>
              <div class="table-header">
                <div class="table-tools">
                  <a href="#" class="btn btn-success w-sm nohide" data-toggle="modal" data-target="#exampleModal">
                    <i class="fas fa-plus-circle"></i> 添加帮助
                  </a>
                  <select class="form-control">
                    <option value="1">全部</option>
                    <option value="2">正常</option>
                    <option value="3">失效</option>
                  </select>
                  <input type="text" class="form-control" placeholder="输入关键字">
                  <btn class="btn btn-primary w-xs"><i class="fas fa-search"></i> 搜索</btn>
                </div>
                <div class=""></div>
              </div>
              <div class="table-body table-responsive">
                <table class="table table-bordered table-hover">
                  <caption>选中的项目： <button type="button" class="btn btn-danger btn-sm">批量删除</button></caption>
                  <thead class="thead-light">
                    <tr>
                      <th class="checkbox">
                        <div class="custom-control custom-checkbox">
                          <input type="checkbox" class="custom-control-input" id="customCheckHead" name="headCheckbox">
                          <label class="custom-control-label" for="customCheckHead">&nbsp;</label>
                        </div>
                      </th>
                      <th class="center t1">ID </th>
                      <th class="t4">标题</th>
                      <th>分类</th>
                      <th>发布时间</th>
                      <th class="center">隐藏</th>
                      <th class="center t5">操作</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="checkbox">
                        <div class="custom-control custom-checkbox">
                          <input type="checkbox" class="custom-control-input row-checkbox" id="customCheck1">
                          <label class="custom-control-label" for="customCheck1">&nbsp;</label>
                        </div>
                      </td>
                      <td class="center">1</td>
                      <td>2021-01-08 14:02:16</td>
                      <td><a>125.80.129.102</a></td>
                      <td>未支付</td>
                      <td class="center"><i class="fa fa-check-circle text-success"></i></td>
                      <td>
                        <button type="button" class="btn btn-link"><i class="fas fa-edit"></i> 下载</button>
                        <button type="button" class="btn btn-link green"><i class="fas fa-check"></i> 通过</button>
                        <button type="button" class="btn btn-link red"><i class="fas fa-times"></i> 删除</button>
                      </td>
                    </tr>
                    <tr>
                      <td class="checkbox">
                        <div class="custom-control custom-checkbox">
                          <input type="checkbox" class="custom-control-input row-checkbox" id="customCheck1">
                          <label class="custom-control-label" for="customCheck1">&nbsp;</label>
                        </div>
                      </td>
                      <td class="center">1</td>
                      <td>2021-01-08 14:02:16</td>
                      <td>125.80.129.102</td>
                      <td>未支付</td>
                      <td class="center"><i class="fa fa-check-circle text-success"></i></td>
                      <td>
                        <button type="button" class="btn btn-link"><i class="fas fa-edit"></i> 下载</button>
                        <button type="button" class="btn btn-link green"><i class="fas fa-check"></i> 通过</button>
                        <button type="button" class="btn btn-link red"><i class="fas fa-times"></i> 删除</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="table-footer">
                <div class="table-pagination">
                  <div class="table-pageinfo">
                    每页显示 10 条数据
                  </div>
                  <nav>
                    <ul class="pagination">
                      <li class="page-item disabled"><a class="page-link" href="#">上一页</a></li>
                      <li class="page-item"><a class="page-link" href="#">1</a></li>
                      <li class="page-item active"><a class="page-link" href="#">2</a></li>
                      <li class="page-item"><a class="page-link" href="#">3</a></li>
                      <li class="page-item"><a class="page-link" href="#">下一页</a></li>
                    </ul>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
 