
  <section class="admin-main">
    <div class="container-fluid">
      <div class="page-container">
        <div class="card">
          <div class="card-body">
            <!-- class="col-lg-1 col-md-12 col-sm-12" -->
            <div class="card-title row"> 
                <div class="pl-4 pr-4">{$Title}</div>
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
           

            <div class="tab-content mt-4">
              <div class="table-body">
                <form class="form">
                  <div class="form-group row">
                    <label class="require">帮助标题</label>
                    <div class="col-sm-4">
                      <input type="text" class="form-control" name="title">
                    </div>
                  </div>

                  <div class="form-group row">
                    <label class="require">分类
                      <i class="far fa-question-circle" style="color: blue;" aria-hidden="true" data-toggle="tooltip"
                        data-placement="top" title="<em>Tooltip</em> <u>with</u> <b>HTML</b>" data-html="true"></i>
                    </label>
                    <div class="col-sm-4">
                      <select class="form-control" name="category">
                        <option value="1">选项1</option>
                        <option value="2">选项2</option>
                        <option value="3">选项3</option>
                      </select>
                    </div>
                  </div>

                  <div class="form-group row">
                    <label>是否隐藏</label>
                    <div class="col-sm-4">
                      <div class="custom-control custom-switch" dir="ltr">
                        <input type="checkbox" class="custom-control-input" id="customSwitchsizemd" name="isHide">
                        <label class="custom-control-label" for="customSwitchsizemd"></label>
                      </div>
                    </div>
                  </div>

                  <div class="form-group row">
                    <label>多选</label>
                    <div class="col-sm-4">
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="inlineCheckbox1" value="option1"
                          name="mulity">
                        <label class="form-check-label" for="inlineCheckbox1">1</label>
                      </div>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="inlineCheckbox2" value="option2"
                          name="mulity">
                        <label class="form-check-label" for="inlineCheckbox2">2</label>
                      </div>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="inlineCheckbox3" value="option3" disabled
                          name="mulity">
                        <label class="form-check-label" for="inlineCheckbox3">3 (disabled)</label>
                      </div>
                    </div>
                  </div>

                  <div class="form-group row">
                    <label>单选</label>
                    <div class="col-sm-4">
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="inlineCheckbox1" value="option1" name="single">
                        <label class="form-check-label" for="inlineCheckbox1">1</label>
                      </div>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="inlineCheckbox2" value="option2" name="single">
                        <label class="form-check-label" for="inlineCheckbox2">2</label>
                      </div>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="inlineCheckbox3" value="option3" disabled
                          name="single">
                        <label class="form-check-label" for="inlineCheckbox3">3 (disabled)</label>
                      </div>
                    </div>
                  </div>

                  <div class="form-group row">
                    <label class="require">日期选择</label>
                    <div class="col-sm-4">
                      <input type="text" class="form-control datetime" name="datetime">
                    </div>
                  </div>

                  <div class="form-group row">
                    <label class="require">日期范围</label>
                    <div class="col-sm-4">
                      <input type="text" class="form-control daterange" name="daterange">
                    </div>
                  </div>

                  <div class="form-group row">
                    <label>标签</label>
                    <div class="col-sm-4">
                      <input type="text" class="form-control" name="label">
                    </div>
                  </div>

                  <div class="form-group row">
                    <label>描述</label>
                    <div class="col-sm-4">
                      <input type="text" class="form-control" name="desc">
                    </div>
                  </div>


                  <div class="form-group row">
                    <label>文章内容</label>
                    <div class="col-sm-4">
                      <textarea rows="5" class="form-control" name="content"></textarea>
                    </div>
                  </div>

                  <div class="form-group row">
                    <div class="col-sm-10">
                      <button type="submit" class="btn btn-primary w-md">保存更改</button>
                      <button type="submit" class="btn btn-outline-secondary w-md">取消更改</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

 