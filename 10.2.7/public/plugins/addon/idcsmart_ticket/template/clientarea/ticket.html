<!-- 页面独有样式 -->
<link rel="stylesheet" href="/plugins/addon/idcsmart_ticket/template/clientarea/css/ticket.css">

</head>

<body>
  <!-- mounted之前显示 -->
  <div id="mainLoading">
    <div class="ddr ddr1"></div>
    <div class="ddr ddr2"></div>
    <div class="ddr ddr3"></div>
    <div class="ddr ddr4"></div>
    <div class="ddr ddr5"></div>
  </div>
  <div class="template ticket">
    <el-container>
      <aside-menu></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <el-main>
          <!-- 自己的东西 -->
          <div class="main-card">
            <div class="main-card-title">{{lang.ticket_title}}</div>
            <div class="content_searchbar">
              <div class="new-ticket-btn" @click="showCreateDialog">{{lang.ticket_btn1}}</div>
              <div class="searchbar com-search">
                <el-input class="select-input" :placeholder="lang.ticket_tips1" v-model="params.keywords" clearable></el-input>
                <el-select class="select-input" :placeholder="lang.ticket_tips2" v-model="params.ticket_type_id" clearable>
                  <el-option v-for="item in ticketType" :key="item.id" :value="item.id" :label="item.name"></el-option>
                </el-select>
                <el-select class="select-input" multiple collapse-tags :placeholder="lang.ticket_tips3" v-model="params.status" clearable>
                  <el-option v-for="item in ticketStatus" :key="item.id" :value="item.id" :label="item.name"></el-option>
                </el-select>
                <el-button @click="inputChange">{{lang.ticket_btn2}}</el-button>
              </div>
            </div>

            <div class="tabledata">
              <el-table v-loading="tableLoading" :data="dataList" style="width: 100%;margin-bottom: .2rem;" @row-click="(row)=>itemReply(row)">
                <el-table-column prop="title" min-width="400" :label="lang.ticket_label1" align="left" :show-overflow-tooltip="true">
                  <template slot-scope="scope">
                    <span>{{'#' + scope.row.ticket_num + "-" + scope.row.title }}</span>
                  </template>
                </el-table-column>
                <el-table-column prop="name" :label="lang.ticket_label2" width="400" align="left">
                  <template slot-scope="scope">
                    {{scope.row.name?scope.row.name:'--'}}
                  </template>
                </el-table-column>

                <el-table-column prop="post_time" :label="lang.ticket_label3" width="300" align="left">
                  <template slot-scope="scope">
                    <span>{{scope.row.last_reply_time | formateTime}}</span>
                  </template>
                </el-table-column>
                <el-table-column prop="status" :label="lang.ticket_label4" width="150" align="left">
                  <template slot-scope="scope">
                    <span class="status-text" :style="{background:hexToRgb(scope.row.color),color:scope.row.color}">{{scope.row.status}}</span>
                  </template>
                </el-table-column>
                <el-table-column prop="id" width="100" :label="lang.ticket_label5" align="left">
                  <template slot-scope="scope">
                    <el-popover placement="top-start" trigger="hover">
                      <div class="operation">
                        <div class="operation-item" @click="itemReply(scope.row)">{{lang.ticket_btn3}}</div>
                        <div v-if="scope.row.status != 'Closed' && scope.row.status != 'Resolved'" class="operation-item" @click="itemUrge(scope.row)">{{lang.ticket_btn4}}</div>
                        <div v-if="scope.row.status != 'Closed' && scope.row.status != 'Resolved'" class="operation-item" @click="itemClose(scope.row)">{{lang.ticket_btn5}}</div>
                      </div>
                      <div class="more-operation" @click.stop="handelMore" slot="reference">...</div>
                    </el-popover>
                  </template>
                </el-table-column>
              </el-table>
              <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange"></pagination>
            </div>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/plugins/addon/idcsmart_ticket/template/clientarea/api/common.js"></script>
  <script src="/plugins/addon/idcsmart_ticket/template/clientarea/api/ticket.js"></script>
  <script src="/plugins/addon/idcsmart_ticket/template/clientarea/js/ticket.js"></script>
  <script src="/plugins/addon/idcsmart_ticket/template/clientarea/components/pagination/pagination.js"></script>
  <script src="/plugins/addon/idcsmart_ticket/template/clientarea/utils/util.js"></script>