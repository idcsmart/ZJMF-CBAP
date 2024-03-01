/* 通用用户选择，滚动加载 */
const comChooseUser = {
  template: `
    <div class="choose-user">
      <t-select v-model="userForm.type" class="type" @change="changeType" :disabled="disabled">
        <t-option v-for="item in typeOption" :value="item.value" :label="item.label" :key="item.value"></t-option>
      </t-select>
      <t-select v-model="client_id" filterable :placeholder="prePlaceholder" clearable :loading="searchLoading"
        @search="remoteMethod" clearable @clear="clearKey" :scroll="{ type: 'virtual' }" :multiple="multiple"
        :popup-props="popupProps" class="user-select" :disabled="disabled" @change="changeUser"
        :minCollapsedNum="1">
        <!-- 用户详情独有(显示当前用户) -->
        <t-option :key="curUser.id" :value="curUser.id" :label="calcShow(curUser)" v-if="userId || curUser.id" class="com-custom">
          <div class="info">
            <p>#{{curUser.id}}-{{curUser.username ? curUser.username : (curUser.phone? curUser.phone: curUser.email)}}<span v-show="curUser.company">({{curUser.company}})</span></p>
            <div class="des">
              <p v-show="curUser.phone" class="tel">+{{curUser.phone_code}}-{{curUser.phone}}</p>
              <p v-show="curUser.email" class="tel"><span v-show="curUser.phone && curUser.email">/</span>{{curUser.email}}</p>
            </div>
          </div>
        </t-option>
        <t-option v-for="item in calcUserList" :value="item.id" :label="calcShow(item)" :key="item.id" class="com-custom">
          <div class="info">
            <p>#{{item.id}}-{{item.username}}<span v-show="item.company">({{item.company}})</span></p>
            <div class="des">
              <p v-show="item.phone" class="tel">+{{item.phone_code}}-{{item.phone}}</p>
              <p v-show="item.email" class="tel"><span v-show="item.phone && item.email">/</span>{{item.email}}</p>
            </div>
          </div>
        </t-option>
      </t-select>
    </div>

      `,
  data() {
    return {
      popupProps: {
        onScroll: this.handleScroll,
        overlayClassName: "client-pup",
        overlayInnerStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` }),
      },
      type: "",
      client_id: "",
      clientList: [],
      curUser: {},
      searchLoading: false,
      isExist: false,
      typeOption: [
        { value: "", label: lang.auth_all },
        { value: "id", label: "ID" },
        { value: "username", label: lang.name },
        { value: "phone", label: lang.phone },
        { value: "email", label: lang.email },
      ],
      userForm: {
        page: 1,
        limit: 20,
        type: "", // "" | id | username | phone | email
        orderby: "id",
        sort: "desc",
      },
      init: true,
    };
  },
  computed: {
    calcShow() {
      return (data) => {
        return (
          `#${data.id}-` +
          (data.username
            ? data.username
            : data.phone
            ? data.phone
            : data.email) +
          (data.company ? `(${data.company})` : "")
        );
      };
    },
    calcUserList() {
      if (!this.userId) {
        return this.clientList;
      } else {
        // 去除当前ID项
        return this.clientList.filter((item) => item.id !== this.userId);
      }
    },
  },
  props: {
    disabled: {
      default() {
        return false;
      },
    },
    prePlaceholder: {
      default() {
        return lang.input_search;
      },
    },
    multiple: {
      default() {
        return false;
      },
    },
    checkId: {
      // 回填已选用户
      default() {
        return this.multiple ? [] : "";
      },
    },
    userId: {
      // 传入用户ID，下拉首个默认展示当前用户
      default() {
        return null;
      },
    },
    all: {
      // 通用拉取全部用户列表， false 获取未绑定销售用户
      default() {
        return true;
      },
    },
    curInfo: {
      // 当前用户信息
      default() {
        return {};
      },
    },
  },
  watch: {
    userId: {
      deep: true,
      immediate: true,
      handler(id) {
        if (id) {
          this.getUserDetails(id);
        }
      },
    },
    curInfo: {
      deep: true,
      immediate: true,
      handler(val) {
        this.curUser = val
      },
    }
  },
  created() {
    this.getUserList();
  },
  methods: {
    changeUser() {
      this.$emit("changeuser", this.client_id);
    },
    async getUserDetails(id) {
      try {
        const res = await getUserInfo({ id });
        this.curUser = res.data.data.client;
      } catch (error) {
        console.log("error", error);
      }
    },
    clearKey() {
      this.changeType();
    },
    changeType() {
      this.userForm.page = 1;
      this.clientList = [];
      this.userTotal = 0;
      this.userForm.keywords = "";
      if (this.multiple && this.client_id.length === 0) {
        this.client_id = [];
      }
      if (!this.multiple && !this.client_id) {
        this.client_id = "";
      }
      this.searchLoading = true;
      this.getUserList();
    },
    // 远程搜素
    remoteMethod(key) {
      this.userForm.page = 1;
      this.clientList = [];
      this.userForm.keywords = key;
      this.searchLoading = true;
      this.getUserList();
    },
    async handleScroll({ e }) {
      const { scrollTop, clientHeight, scrollHeight } = e.target;
      if (scrollHeight - scrollTop === clientHeight) {
        this.userForm.page++;
        await Promise.resolve(this.getUserList()).then((res) => {
          this.$nextTick(() => {
            document.querySelector(".client-pup .t-popup__content").scrollTop =
              scrollHeight;
          });
        });
      }
    },
    async getUserList() {
      try {
        if (this.userTotal > 0 && this.clientList.length === this.userTotal) {
          return;
        }
        const reqApi = this.all ? getComClientList : noBindClientList;
        const res = await reqApi(this.userForm);
        this.clientList = this.clientList.concat(res.data.data.list);
        this.userTotal = res.data.data.count;
        this.searchLoading = false;
        if (this.init) {
          this.client_id = this.checkId;
        }
        this.init = false;
        return true;
      } catch (error) {
        console.log("error", error);
        this.searchLoading = false;
        this.$message.error(error.data.msg);
      }
    },
  },
};
