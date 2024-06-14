const safeConfirm = {
  template: `
        <div>
            <t-dialog :z-index="2600" :header="lang.setting_text34" :visible.sync="visible" :footer="false" width="520">
                <div class="dia-pass">
                    <t-input type="password" name="password" v-model="passData" :placeholder="lang.setting_text33"
                        oncopy="return false" oncut="return false" onpaste="return false" autocomplete="off">
                    </t-input>
                </div>
                <div class="com-f-btn" style="margin-top: 20px;">
                    <t-button theme="primary" :loading="submitLoading" @click="save">{{lang.setting_text31}}</t-button>
                    <t-button theme="default" variant="base" @click="closeDialog">{{lang.setting_text32}}</t-button>
                </div>
            </t-dialog>
        </div>
        `,
  data() {
    return {
      visible: false,
      submitLoading: false,
      passData: "",
      callbackFun: "",
      admin_enforce_safe_method: [],
    };
  },
  computed: {},
  props: {
    password: {
      type: String,
      default: "",
    },
  },
  watch: {},
  created() {
    this.admin_enforce_safe_method =
      JSON.parse(localStorage.getItem("common_set"))
        .admin_enforce_safe_method || [];
  },
  methods: {
    /**
     * @param  {String}  callbackFun 回调函数名称
     */
    openDialog(callbackFun) {
      this.callbackFun = callbackFun;
      this.passData = "";
      this.$emit("update:password", this.passData);
      if (!this.admin_enforce_safe_method.includes("operate_password")) {
        this.$emit("update:password", "noNeed");
        // 执行父级方法
        this.$emit("confirm", this.callbackFun);
      } else {
        this.visible = true;
      }
    },
    closeDialog() {
      this.visible = false;
    },
    save() {
      if (!this.passData) {
        this.$message.error(lang.setting_text33);
        return;
      }
      this.$emit("update:password", this.passData);
      // 执行父级方法
      this.$emit("confirm", this.callbackFun);
      this.closeDialog();
    },
  },
};
