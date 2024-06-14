const productFilter = {
  template: `
    <div class="product-tab-list">
      <div v-for="(item,index) in tabList" :key="index" :class="select_tab === item.tab ? 'pro-tab-item active': 'pro-tab-item'" @click="handelTab(item)">
        {{item.name}}
        <span v-if="item.tab === 'expiring' && count > 0">({{count}})</span>
      </div>
    </div>
    `,
  data() {
    return {
      select_tab: "using",
    };
  },
  props: {
    tabList: {
      type: Array,
      required: false,
      default: () => {
        return [
          {
            name: lang.product_list_status1,
            tab: "using",
          },
          {
            name: lang.product_list_status2,
            tab: "expiring",
          },
          {
            name: lang.product_list_status3,
            tab: "overdue",
          },
          {
            name: lang.product_list_status4,
            tab: "deleted",
          },
          {
            name: lang.finance_btn5,
            tab: "",
          },
        ];
      },
    },
    tab: {
      type: String,
      required: false,
      default: "",
    },
    count: {
      type: Number | String,
      required: false,
      default: 0,
    },
  },
  mounted() {
    if (this.tab) {
      this.select_tab = this.tab;
    }
  },
  methods: {
    handelTab(item) {
      if (this.select_tab === item.tab) {
        this.select_tab = "";
      } else {
        this.select_tab = item.tab;
      }
      this.$emit("update:tab", this.select_tab);
      this.$emit("change");
    },
  },
};
