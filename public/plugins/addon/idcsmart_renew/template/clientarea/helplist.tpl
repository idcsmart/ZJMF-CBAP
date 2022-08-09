
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
						<div class="help-block">
							可创建用户常见问题及答案的文档，用户在前台可按类别或输入的关键字搜索文档进行解决。
						</div>
						<div class="table-container">
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
									<btn class="btn btn-outline-secondary w-xs" id="search-more">高级搜索</btn>
								</div>
							</div>
							<div class="more-search">
								<div class="form-row">
									<div class="form-group col-lg-3 col-md-4 col-sm-2 row">
										<label class="col-4">手机</label>
										<input type="text" class="form-control col-8">
									</div>
									<div class="form-group col-lg-3 col-md-4 col-sm-2 row">
										<label class="col-4">手机</label>
										<input type="text" class="form-control col-8">
									</div>
									<div class="form-group col-lg-3 col-md-4 col-sm-2 row">
										<label class="col-4">手机</label>
										<input type="text" class="form-control col-8">
									</div>
									<div class="form-group col-lg-3 col-md-4 col-sm-2 row">
										<label class="col-4">手机</label>
										<input type="text" class="form-control col-8">
									</div>
									<div class="form-group col-lg-3 col-md-4 col-sm-2 row">
										<label class="col-4">手机</label>
										<input type="text" class="form-control col-8">
									</div>
									<div class="form-group col-lg-3 col-md-4 col-sm-2 row">
										<label class="col-4">手机</label>
										<input type="text" class="form-control col-8">
									</div>
									<div class="form-group col-lg-3 col-md-4 col-sm-2 row">
										<label class="col-4">手机</label>
										<input type="text" class="form-control col-8">
									</div>
									<div class="form-group col-lg-3 col-md-4 col-sm-2 row">
										<label class="col-4"></label>
										<btn class="btn btn-primary"><i class="fas fa-search"></i> 搜索</btn>
										<btn class="btn btn-outline-secondary">重置</btn>
									</div>
								</div>
							</div>
							<div class="table-body table-responsive">
								<table class="table table-bordered table-hover">
									<caption>选中的项目： <button type="button" class="btn btn-danger btn-sm"
											onclick="getSelectedRow()">批量删除</button></caption>
									<thead class="thead-light">
										<tr>
											<th class="checkbox">
												<div class="custom-control custom-checkbox">
													<input type="checkbox" class="custom-control-input" id="selectAll">
													<label class="custom-control-label" for="selectAll">&nbsp;</label>
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
													<input type="checkbox" class="custom-control-input row-checkbox" id="1">
													<label class="custom-control-label" for="1">&nbsp;</label>
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
													<input type="checkbox" class="custom-control-input row-checkbox" id="2">
													<label class="custom-control-label" for="2">&nbsp;</label>
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

	<!-- 添加弹窗 -->
	<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
		aria-hidden="true">
		<div class="modal-dialog " role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form class="form">
						<div class="form-group row invalid">
							<label class="require">帮助标题</label>
							<div class="col-sm-9">
								<input type="text" class="form-control">
								<div class="invalid-feedback">
									Please provide a valid city.
								</div>
							</div>
						</div>

						<div class="form-group row">
							<label class="require">分类</label>
							<div class="col-sm-4">
								<select class="form-control">
									<option value="1">选项1</option>
									<option value="2">选项2</option>
									<option value="3">选项3</option>
								</select>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">关闭</button>
					<button type="button" class="btn btn-primary">保存</button>
				</div>
			</div>
		</div>
	</div>
 