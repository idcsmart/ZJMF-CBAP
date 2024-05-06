const customGoods = {
  template: `   
  <div class="custom-goods-box">
    <el-form  :model="ruleForm" ref="ruleForm" :label-position="labelPosition" :label-width="labelWidth" :rules="rules">
      <el-form-item :prop="item.id + ''" :label="item.field_name" v-for="item in customFieldList" :key="item.id">
          <el-select v-model="ruleForm[item.id]" :placeholder="item.description" v-if="item.field_type === 'dropdown'">
              <el-option :label="items" :value="items" v-for="(items,indexs) in calcFieldOption(item.field_option)" :key="indexs"></el-option>
          </el-select>
          <el-checkbox true-label="1" false-label="0" :label="item.field_name" v-model="ruleForm[item.id]" v-else-if="item.field_type === 'tickbox'">
              {{item.description}}
          </el-checkbox>
          <el-input type="textarea" v-model="ruleForm[item.id]" v-else-if="item.field_type === 'textarea'" :placeholder="item.description"></el-input>
          <el-input v-model="ruleForm[item.id]" :placeholder="item.description" v-else></el-input>
      </el-form-item>
    </el-form>
  </div>
        `,
  data() {
    return {
      ruleForm: {},
      rules: {},
      customFieldList: [],
    };
  },
  components: {},
  props: {
    id: {
      type: Number | String,
      required: true,
    },
    self_defined_field: {
      type: Object,
      required: true,
    },
    is_show_custom: {
      type: Boolean,
      required: false,
      default: false,
    },
    labelWidth: {
      type: String,
      required: false,
      default: "auto",
    },
    labelPosition: {
      // :value 	right/left/top
      type: String,
      required: false,
      default: "left",
    },
  },
  created() {
    this.getCustomFields();
  },
  watch: {},
  mounted() {},
  methods: {
    getSelfDefinedField() {
      let isValid = false;
      this.$refs.ruleForm.validate((valid) => {
        if (valid) {
          this.$emit("update:self_defined_field", this.ruleForm);
          isValid = true;
        } else {
          this.$message.error(lang.custom_goods_text3);
        }
      });
      return isValid;
    },
    getCustomFields() {
      customFieldsProduct(this.id).then((res) => {
        const obj = {};
        const rules = {};
        this.customFieldList = res.data.data.data.map((item) => {
          if (item.field_type === "dropdown" && item.is_required === 1) {
            obj[item.id + ""] = this.calcFieldOption(item.field_option)[0];
          } else {
            obj[item.id + ""] = item.field_type === "tickbox" ? 0 : "";
          }
          rules[item.id + ""] = this.calcRules(item);
          return item;
        });
        this.$set(this, "ruleForm", obj);
        if (Object.keys(this.self_defined_field).length > 0) {
          // 设置默认值
          for (let i = 0; i < this.customFieldList.length; i++) {
            const item = this.customFieldList[i];
            if (this.self_defined_field[item.id + ""] !== undefined) {
              this.ruleForm[item.id + ""] =
                this.self_defined_field[item.id + ""];
            }
          }
        }
        this.$emit("update:is_show_custom", this.customFieldList.length > 0);
        this.$set(this, "rules", rules);
      });
    },
    calcValidator(item, value, callback, regexpr) {
      if (item.is_required === 1 && value === "") {
        callback(new Error(lang.custom_goods_text1));
        return;
      }
      if (
        value !== "" &&
        !new RegExp(regexpr.replace(/^\/|\/$/g, "")).test(value)
      ) {
        callback(new Error(lang.custom_goods_text2));
        return;
      }
      callback();
    },

    calcRules(item) {
      const rules = [];
      if (item.is_required === 1) {
        rules.push({
          required: true,
          message: lang.custom_goods_text1,
          trigger: ["blur", "change"],
        });
      } else {
        rules.push({
          required: false,
          trigger: ["blur", "change"],
        });
      }

      if (item.field_type === "link") {
        // 类型为链接时需要校验url格式 http://www.baidu.com
        const url =
          "/^(((ht|f)tps?)://)?([^!@#$%^&*?.s-]([^!@#$%^&*?.s]{0,63}[^!@#$%^&*?.s])?.)+[a-z]{2,6}/?/";
        rules.push({
          validator: (rule, value, callback) =>
            this.calcValidator(item, value, callback, url),
          trigger: ["blur", "change"],
        });
      }
      if (
        item.field_type !== "dropdown" &&
        item.field_type !== "tickbox" &&
        item.regexpr
      ) {
        rules.push({
          validator: (rule, value, callback) =>
            this.calcValidator(item, value, callback, item.regexpr),
          trigger: ["blur", "change"],
        });
      }
      return rules;
    },
    calcFieldOption(item) {
      return item.split(",");
    },
  },
};
