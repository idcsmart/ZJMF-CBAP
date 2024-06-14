/* 通用treeSelect：用于商品筛选（单/多选） */
const comTreeSelect = {
  template: `
      <t-tree-select
        :data="proData"
        v-model="checkPro"
        :popupProps="popupProps"
        :treeProps="treeProps"
        filterable
        clearable
        :multiple="multiple"
        :minCollapsedNum="1"
        :placeholder="prePlaceholder"
        :disabled="disabled"
        @change="onChange">
        <template #panelTopContent>
          <t-checkbox v-model="checkAll" @change="chooseAll" class="tree-check-all" v-if="showAll">{{lang.check_all}}</t-checkbox>
        </template>
      </t-tree-select>

      `,
  data() {
    return {
      popupProps: {
        overlayInnerStyle: (trigger) => ({
          width: `${trigger.offsetWidth}px`,
        }),
      },
      proData: [],
      checkPro: "",
      isInit: true,
      checkAll: false,
      proList: [],
    };
  },
  props: {
    treeProps: {
      default() {
        return {
          valueMode: "onlyLeaf",
          keys: {
            label: "name",
            value: "key",
            children: "children",
          },
        };
      },
    },
    // 是否多选
    multiple: {
      default() {
        return false;
      },
    },
    showAll: {
      // 是否展示全选
      default() {
        return false;
      },
    },
    disabled: {
      // 是否禁用
      default() {
        return false;
      },
    },
    value: {
      // 回显传参
      default() {
        return false;
      },
    },
    prePlaceholder: {
      default() {
        return lang.product_id_empty_tip;
      },
    },
    need: {
      // 是否返回商品列表
      default() {
        return false;
      },
    },
    allProducts: {
      default() {
        return [];
      },
    },
    product: {
      default() {
        return [];
      },
    },
  },
  watch: {
    value: {
      deep: true,
      immediate: true,
      handler(val) {
        if (!this.isInit) {
          return;
        }
        if ((typeof val === "string" || typeof val === "number") && val) {
          this.$nextTick(() => {
            this.checkPro = `t-${val}`;
            this.onChange(this.checkPro);
          });
        }
        if (typeof val === "object" && val.length > 0) {
          this.$nextTick(() => {
            val.forEach((el) => {
              this.checkPro.push(`t-${el}`);
            });
            const temp = Array.from(new Set(this.checkPro));
            this.checkPro = temp;
            this.onChange(temp);
          });
        }
      },
    },
    checkPro(val) {
      if (!this.showAll) {
        return;
      }
      if (val.length === 0) {
        return;
      }
      if (val.length === this.proList.length) {
        this.checkAll = true;
      } else {
        this.checkAll = false;
      }
    },
  },
  created() {
    this.checkPro = this.multiple ? [] : "";
    if (this.allProducts.length > 0) {
      // 单页面多次引用组件
      this.proData = this.allProducts;
      this.proList = this.product;
      return;
    }
    this.init();
  },
  methods: {
    chooseAll(e) {
      let arr1 = [];
      if (e) {
        const arr = this.proList.map((item) => `t-${item.id}`);
        arr1 = this.proList.map((item) => item.id);
        this.checkPro = arr;
      } else {
        this.checkPro = [];
      }
      if (this.need) {
        this.$emit("choosepro", arr1, this.proList || []);
      } else {
        this.$emit("choosepro", arr1);
      }
    },
    onChange(e) {
      let val = "";
      this.isInit = false;
      if (e instanceof Object) {
        val = e.map((item) => Number(String(item).replace("t-", "")));
      } else {
        if (e) {
          val = Number(String(e).replace("t-", ""));
        } else {
          val = "";
        }
      }
      if (this.need) {
        this.$emit("choosepro", val, this.proList || []);
      } else {
        this.$emit("choosepro", val);
      }
    },
    // 商品列表
    async getProList() {
      try {
        const res = await getComProduct();
        const temp = res.data.data.list.map((item) => {
          item.key = `t-${item.id}`;
          return item;
        });
        // 过滤没有父级id的商品
        this.proList = temp.filter((item) => item.product_group_id_second);
        return this.proList;
      } catch (error) {}
    },
    // 获取一级分组
    async getFirPro() {
      try {
        const res = await getFirstGroup();
        this.firstGroup = res.data.data.list.map((item) => {
          item.key = `f-${item.id}`;
          return item;
        });
        return this.firstGroup;
      } catch (error) {}
    },
    // 获取二级分组
    async getSecPro() {
      try {
        const res = await getSecondGroup();
        this.secondGroup = res.data.data.list.map((item) => {
          item.key = `s-${item.id}`;
          return item;
        });
        return this.secondGroup;
      } catch (error) {}
    },
    init() {
      try {
        // 获取商品，一级，二级分组
        Promise.all([
          this.getProList(),
          this.getFirPro(),
          this.getSecPro(),
        ]).then((res) => {
          const fArr = res[1].map((item) => {
            let secondArr = [];
            res[2].forEach((sItem) => {
              if (sItem.parent_id === item.id) {
                secondArr.push(sItem);
              }
            });
            item.children = secondArr;
            return item;
          });
          setTimeout(() => {
            const temp = fArr.map((item) => {
              item.children.map((ele) => {
                let temp = [];
                res[0].forEach((e) => {
                  if (e.product_group_id_second === ele.id) {
                    temp.push(e);
                  }
                });
                ele.children = temp;
                return ele;
              });
              return item;
            });
            // 过滤无子项数据
            this.proData = temp
              .filter((item) => item.children.length > 0)
              .map((item) => {
                item.children = item.children.filter(
                  (el) => el.children.length > 0
                );
                return item;
              });
          }, 0);
        });
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
  },
};
