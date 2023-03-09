<!-- 页面独有样式 -->
<link rel="stylesheet" href="/plugins/server/mf_cloud/template/clientarea/css/cloudList.css">
<div id="cloudList">
  <!-- 自己的东西 -->
  <div class="main-card">
    <div class="main-card-title">
      <span class="title-text">{{lang.cloud_title}}</span>
      <!-- <div class="add-btn" @click="toOrder">
                <i class="el-icon-plus"></i>
                添加
              </div> -->
    </div>

    <!-- 表格 -->
    <div class="main-card-table">
      <!-- 筛选 -->
      <div class="main-card-search">
        <!-- 数据中心 -->
        <el-select v-model="params.data_center_id" @change="centerSelectChange" style="width:3.2rem;margin-right: .2rem;" :filterable="true" :clearable="true" :placeholder="lang.cloud_tip_1">
          <el-option v-for="item in center" :key="item.id" :value="item.id" :label="item.label">
            <div class="center-option-label">
              <img :src="'/upload/common/country/' + item.iso + '.png'" class="area-img">
              <span class="option-text">{{item.label}}</span>
            </div>
          </el-option>
        </el-select>
        <!-- 产品状态 -->
        <el-select v-model="params.status" @change="statusSelectChange" style="width:3.2rem;margin-right: .2rem;" clearable>
          <el-option v-for="item in statusSelect" :key="item.id" :value="item.status" :label="item.label"></el-option>
        </el-select>
        <!-- <el-input suffix-icon="el-input__icon el-icon-search" @input="inputChange" v-model="params.keywords" style="width: 3.2rem;margin-left: .2rem;" :placeholder="lang.cloud_tip_2"></el-input> -->
        <el-input v-model="params.keywords" style="width: 3.2rem;margin-right: .2rem;" :placeholder="lang.cloud_tip_2" clearable @clear="getCloudList">
        </el-input>
        <div class="search-btn" @Click="inputChange">查询</div>
      </div>
      <div class="table">

        <el-table v-loading="loading" :data="cloudData" style="width: 100%;margin-bottom: .2rem;" @row-click="(row)=>toDetail(row)">
          <el-table-column prop="id" label="ID" width="100" align="left">
            <template slot-scope="scope">
              <span class="column-id" @click="toDetail(scope.row)">{{scope.row.id}}</span>
            </template>
          </el-table-column>
          <el-table-column :label="lang.cloud_table_head_1" width="180" :show-overflow-tooltip="true">
            <template slot-scope="scope">
              <div class="area" v-if="scope.row.country">
                <img :src="'/upload/common/country/' + scope.row.country_code + '.png'" class="area-img">
                <span class="area-country">{{scope.row.country}}</span>
                <span class="area-city">{{scope.row.city}}</span>
              </div>
              <div v-else>--</div>
            </template>
          </el-table-column>
          <el-table-column prop="name" label="商品名称" min-width="180" :show-overflow-tooltip="true">
            <template slot-scope="scope">
              <div class="cloud-name" @click="toDetail(scope.row)">
                <span class="packge-name">{{ scope.row.product_name }}</span>
                <span class="name">{{ scope.row.name }}</span>
              </div>
            </template>
          </el-table-column>
          <el-table-column prop="power_status" label="电源" width="80">
            <template slot-scope="scope">
              <div class="power-status">
                <img :src="powerStatus[scope.row.power_status]?.icon" v-if="scope.row.power_status" :title="powerStatus[scope.row.power_status]?.text">
                <div v-else>--</div>
                <!-- <span class="status-text" v-if="scope.row.power_status">{{powerStatus[scope.row.power_status]?.text}}</span> -->
              </div>
            </template>
          </el-table-column>
          <el-table-column prop="ip" label="IP" width="250" :show-overflow-tooltip="true">
            <template slot-scope="scope">
              <div>
                {{scope.row.ip && scope.row.status!== 'Deleted'?scope.row.ip:'--'}}
              </div>
            </template>
          </el-table-column>
          <el-table-column prop="id" label="OS" width="80" :show-overflow-tooltip="true">
            <template slot-scope="scope">
              <div class="os">
                <img :title="scope.row.image_name" v-if="scope.row.image_group_name" class="os-img" :src="'/plugins/server/mf_cloud/view/img/'+scope.row.image_group_name +'.png'">
                <!-- <span class="os-text">{{scope.row.image_name.split('-')[1]}}</span> -->
              </div>
            </template>
          </el-table-column>
          <!-- <el-table-column prop="due_time" label="开通时间" width="180">
                    <template slot-scope="scope">
                      {{scope.row.active_time | formateTime}} 
                    </template>
                  </el-table-column> -->
          <el-table-column prop="due_time" :label="lang.cloud_table_head_4" width="200">
            <template slot-scope="scope">
              {{scope.row.due_time | formateTime}}
            </template>
          </el-table-column>
          <el-table-column prop="id" label="状态" width="150" align="left">
            <template slot-scope="scope">
              <div class="status" :style="'color:'+status[scope.row.status].color + ';background:' + status[scope.row.status].bgColor">
                {{status[scope.row.status].text }}
              </div>
            </template>
          </el-table-column>

        </el-table>
      </div>
      <div class="page">
        <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange" />
      </div>
    </div>
  </div>
</div>
<!-- =======页面独有======= -->
<script src="/plugins/server/mf_cloud/template/clientarea/api/cloud.js"></script>
<script src="/plugins/server/mf_cloud/template/clientarea/js/cloudList.js"></script>
<script src="/plugins/server/mf_cloud/template/clientarea/components/pagination/pagination.js"></script>
<script src="/plugins/server/mf_cloud/template/clientarea/utils/util.js"></script>