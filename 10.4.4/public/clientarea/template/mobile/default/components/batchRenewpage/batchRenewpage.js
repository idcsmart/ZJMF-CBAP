const batchRenewpage = {
  template: `

    <div class="branch-rennew-dialog">
      <el-dialog width="10rem" :visible.sync="isShow" @close="rzClose">
        <div class="dialag-content" v-loading="loading">
          <h2 class="tips-title">{{lang.cart_tip_text7}}</h2>
          <el-table  :data="dataList" style="width: 100%" max-height="600">
            <el-table-column prop="id" label="ID" width="120">
            </el-table-column>
            <el-table-column prop="product_name" :label="lang.cart_tip_text11" min-width="180" :show-overflow-tooltip="true">
            </el-table-column>
            <el-table-column prop="billing_cycles" :label="lang.cart_tip_text12" width="300">
              <template slot-scope="{row}">
                <el-select v-model="row.select_cycles" @change="(val) =>changeCycles(row)">
                  <el-option v-for="item in row.billing_cycles" :key="item.id" :value="item.id"
                    :label="item.billing_cycle">
                    <span>{{currency_prefix + item.price}} /{{ item.billing_cycle}}</span>
                  </el-option>
                </el-select>
              </template>
            </el-table-column>
            <el-table-column prop="base_price" :label="lang.cart_tip_text13" width="200" align="right">
              <template slot-scope="{row}">
                <span>{{currency_prefix + row.cur_pirce}}</span>
              </template>
            </el-table-column>
          </el-table>
          <div class="total-price">{{lang.template_text87}}：<span class="pay-money">{{currency_prefix }} <span class="font-26">{{ calcTotalPrice}}</span> </span> </div>
        </div>
        <div slot="footer" class="dialog-footer">
          <el-button type="primary" @click="handelRenew" :loading="subLoading">{{lang.cart_tip_text9}}</el-button>
          <el-button @click="rzClose">{{lang.cart_tip_text10}}</el-button>
        </div>
      </el-dialog>
      <pay-dialog ref="RennwPayDialog" @payok="paySuccess" @paycancel="payCancel"></pay-dialog>
    </div>
    `,
  props: {
    ids: {
      type: Array,
      required: true,
      default: [],
    },
  },
  components: {
    payDialog,
  },
  data() {
    return {
      currency_prefix:
        (JSON.parse(localStorage.getItem("common_set_before")) || {})
          ?.currency_prefix || "￥",
      isShow: false,
      loading: false,
      subLoading: false,
      dataList: [],
    };
  },
  computed: {
    idsArr() {
      return this.ids.map((item) => {
        return item.id;
      });
    },
    calcTotalPrice() {
      return this.dataList
        .reduce((acc, cur) => {
          return acc + cur.cur_pirce * 1;
        }, 0)
        .toFixed(2);
    },
  },
  methods: {
    openDia() {
      this.isShow = true;
      this.getRenewList();
    },
    changeCycles(item) {
      item.cur_pirce = this.calcPrice(item);
    },
    calcPrice(row) {
      return (
        row.billing_cycles.filter((item) => item.id == row.select_cycles)[0]
          ?.price || 0
      );
    },
    getRenewList() {
      this.loading = true;
      batchRenewList({ ids: this.idsArr })
        .then((res) => {
          this.dataList = res.data.data.list.map((item) => {
            item.select_cycles = item.billing_cycles[0].id;
            item.cur_pirce = item.billing_cycles[0].price;
            return item;
          });
          this.loading = false;
        })
        .catch((err) => {
          this.loading = false;
          this.$message.error(err.data.msg);
        });
    },
    rzClose() {
      this.isShow = false;
    },
    paySuccess(e) {
      this.isShow = false;
      this.$emit("success");
    },
    // 取消支付回调
    payCancel(e) {},
    handelRenew() {
      this.subLoading = true;
      const billing_cycles = {};
      this.dataList.forEach((item) => {
        billing_cycles[item.id] = item.billing_cycles.filter(
          (items) => items.id == item.select_cycles
        )[0].billing_cycle;
      });
      aipBatchRenew({ ids: this.idsArr, billing_cycles, customfield: {} })
        .then((res) => {
          this.subLoading = false;
          if (res.data.code === "Unpaid") {
            this.$refs.RennwPayDialog.showPayDialog(res.data.data.id);
          } else {
            this.isShow = false;
            this.$emit("success");
          }
        })
        .catch((err) => {
          this.subLoading = false;
          this.$message.error(err.data.msg);
        });
    },
  },
  watch: {},
};
