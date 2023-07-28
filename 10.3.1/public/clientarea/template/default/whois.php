{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/whois.css">
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
  <div class="template">
    <el-container>
      <aside-menu></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <el-main>
          <!-- 自己的东西 -->
          <div class="main-card">
            <div class="whios-title">
              <el-divider direction="vertical"></el-divider>
              <span>{{lang.template_text144}}{{domainName}}{{lang.template_text145}}</span>
            </div>
            <div class="whios-info">
              <div class="whios-item">
                <div class="whios-left">
                  <p>{{lang.template_text146}}</p>
                  <p>Registrant</p>
                </div>
                <div class="whios-right">
                  {{whoisInfo.dom_org || lang.template_text147}}
                </div>
              </div>
              <div class="whios-item">
                <div class="whios-left">
                  <p>{{lang.template_text148}}</p>
                  <p>Registrant Email</p>
                </div>
                <div class="whios-right">
                  {{whoisInfo.dom_em || lang.template_text147}}
                </div>
              </div>
              <div class="whios-item">
                <div class="whios-left">
                  <p>{{lang.template_text149}}</p>
                  <p>Registrar</p>
                </div>
                <div class="whios-right">
                  {{whoisInfo.registrar}}
                </div>
              </div>
              <div class="whios-item">
                <div class="whios-left">
                  <p>{{lang.template_text150}}</p>
                  <p>Registration Date</p>
                </div>
                <div class="whios-right">
                  {{whoisInfo.regdate}}
                </div>
              </div>
              <div class="whios-item">
                <div class="whios-left">
                  <p>{{lang.template_text151}}</p>
                  <p>Expiration Date</p>
                </div>
                <div class="whios-right">
                  {{whoisInfo.expdate}}
                </div>
              </div>
              <div class="whios-item">
                <div class="whios-left">
                  <p>{{lang.template_text152}}</p>
                  <p>Domain Status</p>
                </div>
                <div class="whios-right">
                  {{whoisInfo.status}}
                </div>
              </div>
              <div class="whios-item">
                <div class="whios-left">
                  <p>{{lang.template_text153}}</p>
                  <p>Name Server</p>
                </div>
                <div class="whios-right">
                  {{whoisInfo.nameserver}}
                </div>
              </div>
            </div>
            <div class="whios-title">
              <el-divider direction="vertical"></el-divider>
              <span>{{lang.template_text154}}</span>
            </div>
            <div class="bizserver-box" v-html="whoisInfo.body"></div>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/goodsList.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/whois.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/util.js"></script>
  {include file="footer"}