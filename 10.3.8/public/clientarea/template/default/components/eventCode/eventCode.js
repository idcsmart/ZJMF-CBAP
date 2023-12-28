const eventCode = {
  template: `
  <div>
    <el-popover placement="bottom" trigger="click" v-model="visibleShow" :visible-arrow="false" v-if="!disabled && options.length !==0">
      <div class="event-content">
          <el-select class="event-select" @change="changePromotion" v-model="eventId" 
              :placeholder="lang.goods_text5" >
              <el-option v-for="item in options" :key="item.id" :value="item.id" :label="calcLebal(item)">
              </el-option>
          </el-select>
      </div>
      <span slot="reference" class="event-text">{{showText}}<i class="el-icon-caret-bottom"></i></span>
    </el-popover>
    <span class="event-text" v-if="disabled && options.length > 0">{{showText}}</span>
</div>
          `,
  data() {
    return {
      eventId: "", // 活动促销ID
      options: [],
      discount: 0,
      visibleShow: false
    };
  },
  computed: {
    showText() {
      return this.eventId
        ? this.calcLebal(
          this.options.filter(item => item.id === this.eventId)[0]
        )
        : lang.goods_text6;
    }
  },
  watch: {
    billing_cycle_time() {
      this.getEventList();
    },
    amount() {
      this.getEventList();
    },
    qty() {
      this.getEventList();
    }
  },
  props: {
    id: {
      type: String | Number
    },
    // 场景中的所有商品ID
    product_id: {
      type: String | Number,
      required: true
    },
    // 需要支付的原价格
    amount: {
      type: Number | String,
      required: true
    },
    // 购买数量
    qty: {
      type: Number | String,
      default: 1,
      required: true
    },
    //周期时间
    billing_cycle_time: {
      type: Number | String,
      required: true
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },
  created() {
    this.getEventList();
  },
  mounted() {

  },
  methods: {
    calcLebal(item) {
      if (!item) {
        return "";
      }
      return item.type === "percent"
        ? lang.goods_text1 + " " + item.value + "%"
        : item.type === "reduce"
          ? lang.goods_text2 + item.full + lang.goods_text3 + " " + item.value
          : lang.goods_text6;
    },
    getEventList() {
      eventPromotion({
        id: this.product_id,
        billing_cycle_time: this.billing_cycle_time,
        qty: this.qty,
        amount: this.amount,
        billing_cycle_time: this.billing_cycle_time
      }).then(res => {
        this.options = res.data.list;
        if (this.options.length > 0) {
          this.options.push({
            id: 0,
            type: "no",
            value: 0,
            full: 0
          });
        }
        // 默认选中处理
        if (this.id && this.options.map(item => { return item.id; }).includes(this.id)) {
          this.eventId = this.id;
        }
        // 处理没有当前选中项的情况
        if (!this.options.map(item => { return item.id; }).includes(this.eventId)) {
          this.eventId = '';
        }
      })
        .catch(err => {
          this.$message.error(err.data.msg);
        })
        .finally(() => {
          this.changePromotion();
        });
    },
    changePromotion() {
      if (!this.eventId || this.eventId === 0) {
        this.discount = 0;
        this.$emit("change", { discount: 0, id: "" });
        return;
      }
      applyEventPromotion({
        event_promotion: this.eventId,
        product_id: this.product_id,
        qty: this.qty,
        amount: this.amount,
        billing_cycle_time: this.billing_cycle_time
      })
        .then(res => {
          this.discount = res.data.data.discount;
        })
        .catch(err => {
          this.discount = 0;
          console.log(err.data);
        })
        .finally(() => {
          this.$emit("change", { discount: this.discount, id: this.eventId });
        });
    },
    clearPromotion() {
      this.discount = 0;
      this.$emit("change", { discount: this.discount, id: this.eventId });
    }
  }
};
