// css 样式依赖common.css
const asideMenu = {
  template: ` <el-aside width="190px">
    <img class="ali-logo" :src="logo" @click="goHome"></img>

    <el-menu class="menu-top" :default-active="menuActiveId" @select="handleSelect" background-color="#1E41C9" text-color="rgba(255, 255, 255, .6)" active-text-color="#FFF">
        <template v-for="item in menu1">
            <!-- 只有一级菜单 -->
            <el-menu-item v-if="!item.child || item.child?.length === 0" :key="item.id" :index="item.url" :id="item.url">

                    <i class="iconfont" :class="item.icon"></i>
                    <span class="aside-menu-text" slot="title">{{item.name}}</span>
            </el-menu-item>
            <!-- 有二级菜单 -->
            <el-submenu v-else :key="item.id" :index="item.icon" :id="item.url">
                <template slot="title">
                    <i class="iconfont" :class="item.icon"></i>
                    <span class="aside-menu-text" slot="title">{{item.name}}</span>
                </template>
                <template v-for="child in item.child">
                    <el-menu-item  :index="child.url" :key="child.id">{{child.name}}</el-menu-item>
                </template>
            </el-submenu>
        </template>
    </el-menu>

    <div class="line" v-if="hasSeparate"></div>

    <el-menu class="menu-top" :default-active="menuActiveId" @select="handleSelect" background-color="#1E41C9" text-color="rgba(255, 255, 255, .6)" active-text-color="#FFF">
        <template v-for="item in menu2">
            <!-- 只有一级菜单 -->
            <el-menu-item v-if="!item.child || item.child?.length === 0" :key="item.id" :index="item.url" :id="item.url">
                <i class="iconfont" :class="item.icon"></i>
                <span class="aside-menu-text" slot="title">{{item.name}}</span>
            </el-menu-item>
            <!-- 有二级菜单 -->
            <el-submenu v-else :key="item.id" :index="item.icon" :id="item.url">
                <template slot="title">
                    <i class="iconfont" :class="item.icon"></i>
                    <span class="aside-menu-text" slot="title">{{item.name}}</span>
                </template>
                <template v-for="child in item.child">
                    <el-menu-item  :index="child.url" :key="child.id">{{child.name}}</el-menu-item>
                </template>
            </el-submenu>
        </template>
    </el-menu>



</el-aside>`,
  // 云服务器 当前
  // 物理服务器 dcim
  // 通用产品
  data() {
    return {
      activeId: 1,
      menu1: [],
      menu2: [],
      logo: "",
      menuActiveId: "",
      iconsData: [],
      commonData: {},
      noRepeat: [],
      hasSeparate:false
    };
  },
  mounted() { },
  created() {
    this.doGetMenu();
    this.getCommonSetting();
  },
  beforeUpdate() {
    const mainLoading = document.getElementById("mainLoading");
    mainLoading;
    if (mainLoading) {
      mainLoading.style.display = "none";
    }
    if (document.getElementsByClassName("template")[0]) {
      document.getElementsByClassName("template")[0].style.display = "block";
    }
  },
  mixins: [mixin],
  updated() {
    // // 关闭loading

    // document.getElementsByClassName('template')[0].style.display = 'block'
  },
  methods: {
    // 页面跳转
    // toPage(e) {
    //     // 获取 当前点击导航的id 存入本地
    //     const id = e.id
    //     localStorage.setItem('frontMenusActiveId', id)
    //     // 跳转到对应路径
    //     location.href = '/' + e.url
    // },
    // 获取通用配置
    async getCommonSetting() {
      // console.log(JSON.parse(localStorage.getItem("common_set_before")));
      this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
      this.logo = this.commonData.system_logo;
    },
    // 判断当前菜单激活
    setActiveMenu() {
      const url = location.href;
      let isTrue = true;
      this.menu1.forEach((item) => {
        // 当前url下存在和导航菜单对应的路径
        if (item.url && url.indexOf(item.url) != -1) {
          this.menuActiveId = item.url;
          isTrue = false;
        }
      });
      if (isTrue) {
        // 不存在对应的 读取本地存储的 导航id\
        this.menuActiveId = localStorage.getItem("frontMenusActiveId");
      }
    },
    goHome() {
      localStorage.frontMenusActiveId = "";
      location.href = "/index.html";
    },
    // 获取前台导航
    doGetMenu() {
      getMenu().then((res) => {
        if (res.data.status === 200) {
          const menu = res.data.data.menu;
          localStorage.setItem("frontMenus", JSON.stringify(menu));

          let index = menu.findIndex((item) => item.name == "分隔符");
          if (index != -1) {
            this.hasSeparate = true
            this.menu1 = menu.slice(0, index);
            this.menu2 = menu.slice(index + 1);
          } else {
            this.hasSeparate = false
            this.menu1 = menu;
          }

          this.setActiveMenu();
        }
      });
      // 获取详情
      accountDetail().then((res) => {
        if (res.data.status == 200) {
          let obj = res.data.data.account;
          let id = res.data.data.account.id;
          localStorage.setItem("is_sub_account", obj.customfiled.is_sub_account);
          if (obj.customfiled.is_sub_account == 1) {
            // 子账户
            console.log("子账户");
            accountPermissions(id).then((relust) => {
              let rule = relust.data.data.rule;
              this.$emit("getruleslist", rule);
            });
          } else {
            console.log("主账户");
            // 主账户
            this.$emit("getruleslist", "all");
          }
        }
      }).catch((err) => {
        console.log(err, "err----->");
      });
    },
    arrFun(n) {
      for (var i = 0; i < n.length; i++) {
        //用typeof判断是否是数组
        if (n[i].child && typeof n[i].child == "object") {
          let obj = JSON.parse(JSON.stringify(n[i]));
          delete obj.child;
          this.noRepeat.push(obj);
          this.arrFun(n[i].child);
        } else {
          this.noRepeat.push(n[i]);
        }
      }
    },
    tochild(id) {
      this.menuActiveId = id;
      localStorage.setItem("frontMenusActiveId", id);
      if (id == 1) {
        location.href = "/cloudList.html";
      }
      if (id == 2) {
      }
      if (id == 3) {
        location.href = "/common_product_list.html";
      }
    },
    handleSelect(key) {
      localStorage.setItem("frontMenusActiveId", key);
      // 跳转到对应路径
      location.href = "/" + key;
    },
    getAllIcon() {
      let url = "/upload/common/iconfont/iconfont.json";
      let _this = this;

      // 申明一个XMLHttpRequest
      let request = new XMLHttpRequest();
      // 设置请求方法与路径
      request.open("get", url);
      // 不发送数据到服务器
      request.send(null);
      //XHR对象获取到返回信息后执行
      request.onload = function () {
        // 解析获取到的数据
        let data = JSON.parse(request.responseText);
        _this.iconsData = data.glyphs;
        _this.iconsData.map((item) => {
          item.font_class = "icon-" + item.font_class;
        });
      };
    },
  },
};
