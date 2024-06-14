/* 视图字段 */
const comViewFiled = {
  template: `
    <div class="view-filed">
      <t-tooltip :content="lang.field_setting" :show-arrow="false" theme="light" placement="top-left">
        <t-icon name="setting" @click="handleFiled" class="set-icon"></t-icon>
      </t-tooltip>
      <t-dialog :header="lang.field_setting" :visible.sync="filedModel" :footer="false" width="900"
        class="filed-dialog">
        <div class="con">
          <div class="filed-box">
          <t-input :placeholder="lang.field_search" v-model="keywords" clearable class="top">
            <template #suffixIcon>
              <t-icon name="search"></t-icon>
            </template>
          </t-input>
            <div class="scroll t-table__content">
              <div class="type-item" v-for="(item,index) in filterField" :key="index">
                <p class="s-tit" v-if="item.field.length > 0">
                  {{item.name}}
                  <t-tooltip :content="lang.product_field_tip" :show-arrow="false" theme="light"
                    placement="top-left" v-if="view === 'host' && index === 2">
                    <t-icon name="help-circle"></t-icon>
                  </t-tooltip>
                </p>
                <div class="filed">
                  <p class="item" v-for="el in item.field" :key="el.key">
                    <t-checkbox v-model="el.checked" :title="el.name" :disabled="el.key === 'id'"
                    @change="changeField($event, el.key)">
                      <span v-html="replaceText(el.name)"></span>
                    </t-checkbox>
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="select-filed">
            <p class="top">{{lang.cur_choose_field}}</p>
            <div class="scroll t-table__content">
              <t-table row-key="key" :data="calcSelectFiled" size="medium" :columns="filedColumns" :hover="hover"
                table-layout="fixed" display-type="fixed-width" :hide-sort-tips="true"
                drag-sort="row-handler" @drag-sort="changeSort">
                <template slot="sortIcon">
                  <t-icon name="caret-down-small"></t-icon>
                </template>
                <template #drag="{row}">
                  <t-icon name="move"></t-icon>
                </template>
                <template #op="{row}">
                  <div class="com-opt">
                    <t-icon name="delete" @click="delField(row)" v-if="row.key !== 'id'"></t-icon>
                  </div>
                </template>
              </t-table>
            </div>
          </div>
        </div>
        <div class="com-f-btn">
          <t-button theme="primary" @click="submitField" :loading="submitLoading">{{lang.sure}}</t-button>
          <t-button theme="default" variant="base" @click="filedModel=false">{{lang.cancel}}</t-button>
        </div>
      </t-dialog>
    </div>

      `,
  data () {
    return {
      hover: true,
      filedArr: [],
      selectField: [],
      filedModel: false,
      submitLoading: false,
      keywords: "",
      childField: [],
      tempField: [],
      isInit: false,
      filedColumns: [
        {
          colKey: 'drag',
          width: 30,
          className: 'drag-icon'
        },
        {
          colKey: "name",
          title: "",
          ellipsis: true,
        },
        {
          colKey: "op",
          width: 30,
        },
      ],
    };
  },
  computed: {
    filterField () {
      if (!this.keywords) {
        return this.filedArr;
      } else {
        const temp = JSON.parse(JSON.stringify(this.filedArr));
        return temp.map(item => {
          item.field = item.field.filter(el => (el.name.indexOf(this.keywords) !== -1));
          return item;
        });
      }
    },
    replaceText () {
      return text => {
        if (text.indexOf(this.keywords) !== -1 && this.keywords !== "") {
          return text.replace(this.keywords, '<span style="color: var(--td-brand-color);">' + this.keywords + '</span>');
        } else {
          return text;
        }
      };
    },
    calcSelectFiled () {
      return this.selectField.reduce((all, cur) => {
        all.push({
          key: cur,
          name: this.childField.filter(item => item.key === cur)[0]?.name
        });
        return all;
      }, []);
    }
  },
  props: {
    view: {
      type: String,
      required: true,
      default: "", // client, order, host, transaction
    }
  },
  watch: {
  },
  created () {
    this.getViewFiledList();
  },
  methods: {
    handleFiled () {
      this.filedModel = true;
    },
    async submitField () {
      try {
        this.submitLoading = true;
        const res = await saveViewFiled({
          view: this.view,
          select_field: this.selectField
        });
        this.submitLoading = false;
        this.filedModel = false;
        this.$message.success(res.data.msg);
        this.getViewFiledList();
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    changeSort ({ newData }) {
      this.selectField = newData.map(item => item.key);
    },
    changeField (bol, key) {
      if (bol) {
        this.selectField.push(key);
      } else {
        const index = this.selectField.findIndex(item => item === key);
        this.selectField.splice(index, 1);
      }
    },
    delField (row) {
      const index = this.selectField.findIndex(item => item === row.key);
      this.selectField.splice(index, 1);
      this.handelData();
    },
    async getViewFiledList () {
      try {
        const res = await getViewFiled({
          view: this.view
        });
        const { field, select_field } = res.data.data;
        this.filedArr = field || [];
        this.selectField = select_field || [];
        this.childField = field.reduce((all, cur) => {
          all.push(...cur.field);
          return all;
        }, []);
        this.handelData();
        let sortArr = [];
        // 排序字段
        switch (this.view) {
          case 'client':
            sortArr = ["id", "reg_time", "host_active_num_host_num", "client_credit", "cost_price", "refund_price", "withdraw_price"];
            break;
          case 'order':
            sortArr = ["id", "order_amount", "client_id", "reg_time"];
            break;
          case 'host':
            sortArr = ["id", "renew_amount_cycle", "due_time", "first_payment_amount", "active_time", "client_id", "reg_time"];
            break;
          case 'transaction':
            sortArr = ["id", "amount", "transaction_number", "order_id", "transaction_time", "client_id", "reg_time"];
            break;
        }
        const backData = select_field.reduce((all, cur) => {
          const item = this.childField.filter(item => item.key === cur)[0] || [];
          const params = {
            colKey: item.key,
            title: item.name,
            ellipsis: true,
            minWidth: 120
          };
          if (sortArr.includes(item.key)) {
            params.sortType = "all";
            params.sorter = true;
          }
          all.push(params);
          return all;
        }, []);
        // 直接返回处理好的表头
        const customField = select_field.filter(item => (item.indexOf('addon_client_custom_field')!== -1) || item.indexOf('self_defined_field') !== -1)
        this.$emit("changefield", backData, customField, this.isInit);
        this.isInit = true;
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    handelData () {
      this.filedArr = this.filedArr.map(item => {
        item.field.map(el => {
          if (this.selectField.includes(el.key)) {
            el.checked = true;
          } else {
            el.checked = false;
          }
          return el;
        });
        return item;
      });
    }
  }
};
