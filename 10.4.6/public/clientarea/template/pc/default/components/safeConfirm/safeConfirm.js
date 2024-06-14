const safeConfirm = {
  template: `
        <div>
            <el-dialog width="6.8rem" :visible.sync="visible" :show-close=false @close="closeDialog" custom-class="withdraw-dialog">
              <div class="dialog-title">
                  {{lang.account_tips_text3}}
              </div>
              <div class="dialog-main">
                  <el-form label-width="80px" ref="ruleForm"  :rules="rules" :model="dataForm" label-position="top">
                      <el-form-item :label="lang.account_tips_text3" prop="password">
                          <el-input class="input-select" type="password" v-model="dataForm.password" autocomplete="off" :placeholder="lang.account_tips_text2"></el-input>
                      </el-form-item>
                  </el-form>
              </div>
              <div slot="footer" class="dialog-footer">
                  <el-button class="btn-ok" type="primary" @click="save" v-loading="submitLoading">{{lang.cart_tip_text9}}</el-button>
                  <el-button class="btn-no" @click="closeDialog">{{lang.cart_tip_text10}}</el-button>
              </div>
            </el-dialog>
        </div>
        `,
  data() {
    return {
      visible: false,
      submitLoading: false,
      passData: "",
      callbackFun: "",
      home_enforce_safe_method: [],
      dataForm: {
        password: "",
      },
      rules: {
        password: [
          { required: true, message: lang.account_tips_text2, trigger: "blur" },
        ],
      },
    };
  },
  computed: {},
  props: {
    password: {
      type: String,
      default: "",
    },
    isLogin: {
      type: Boolean,
      default: false,
    },
  },
  watch: {},
  created() {
    this.home_enforce_safe_method =
      JSON.parse(localStorage.getItem("common_set_before"))
        .home_enforce_safe_method || [];
  },
  methods: {
    /**
     * @param  {String}  callbackFun 回调函数名称
     */
    openDialog(callbackFun) {
      this.callbackFun = callbackFun;
      this.dataForm.password = "";
      this.$emit("update:password", this.dataForm.password);

      if (
        !this.home_enforce_safe_method.includes("operate_password") &&
        !this.isLogin
      ) {
        this.$emit("update:password", "noNeed");
        // 执行父级方法
        this.$emit("confirm", this.callbackFun);
      } else {
        this.visible = true;
        setTimeout(() => {
          this.$refs.ruleForm.resetFields();
        }, 0);
      }
    },
    closeDialog() {
      this.visible = false;
    },
    save() {
      this.$refs.ruleForm.validate((valid) => {
        if (!valid) {
          return false;
        }
        this.$emit("update:password", this.dataForm.password);
        // 执行父级方法
        this.$emit("confirm", this.callbackFun);
        this.closeDialog();
      });
    },
  },
};
