(function(window, undefined) {
  var old_onload = window.onload;
  window.onload = function() {
    const template = document.getElementsByClassName('template')[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      data() {
        return {
          fromData: {},
          typeOptions: [
            {
              id: 'Artificial',
              name: lang.Artificial
            },
            {
              id: 'Auto',
              name: lang.automatic_refund
            }
          ],
          pay_type: '',
          productOptions: [],
          productConfig: null,
          productDetail: []
        };
      },
      methods: {
        //获取路由参数
        getUrlOption() {
          let str = location.search.substr(1).split('&');
          let obj = {};
          str.forEach(e => {
            let list = e.split('=');
            obj[list[0]] = list[1];
          });
          return obj;
        },
        //获取单个退款商品详情
        getRefundDetail(id) {
          getARefund(id).then(res => {
            this.fromData = res.data.data.refund_product;
            this.fromData.range = Number(this.fromData.range);
            this.fromData.ratio_value = Number(this.fromData.ratio_value);
            this.pay_type = this.productOptions.filter(item=>item.id===this.fromData.product_id)[0].pay_type; //所选商品的付款方式
          }).catch();
        },
        //获取商品下拉框数据
        getProductOptions() {
          getProductList({ page: 1, limit: 10000 }).then(res => {
            this.productOptions = res.data.data.list;
          }).catch();
        },
        //获取商品配置列表
        getConfigList(id) {
          getARefundConfig(id).then(res => {
            this.fromData.config_option = res.data.data.content;
            this.$forceUpdate();
          }).catch();
        },
        //所选商品改变
        productChange(val) {
          this.fromData.rule = '';
          this.fromData.config_option = '';
          const product = this.productOptions.filter(item=>item.id===val)[0];
          this.pay_type = product?product.pay_type:null; //所选商品的付款方式
          if(this.pay_type==='onetime'){
            this.fromData.rule = 'Ratio';
            this.$forceUpdate();
          }
          this.getConfigList(val);
        },
        //所选规则改变
        ruleChange() {
          this.$forceUpdate();
        },
        //选择退款规则购买天数
        checkRange() {
          this.fromData.require = 'range';
          this.$forceUpdate();
        },
        checkChange() {
          this.$forceUpdate();
        },

        //提交 判断是否编辑新增退款商品
        addEdit() {
          if(!this.fromData.product_id){
            this.$message.warning({ content: lang.product_id_empty_tip, placement: 'top-right' });
            return;
          }
          if(!this.fromData.type){
            this.$message.warning({ content: lang.type_empty_tip, placement: 'top-right' });
            return;
          }
          if(!this.fromData.rule){
            this.$message.warning({ content: lang.rule_empty_tip, placement: 'top-right' });
            return;
          }
          const params = {
            product_id:	this.fromData.product_id, //int	-	required	商品ID
            type:	this.fromData.type, //string	-	required	退款类型:Artificial人工，Auto自动
            require: this.fromData.require!=='range'?this.fromData.require:'', //string	-	退款要求:First首次订购,Same同类商品首次订购
            range: this.fromData.range, //int	-	required	购买后X天内
            range_control: this.fromData.require==='range'?1:0,
            rule: this.fromData.rule, //string	-	required	退款规则:Day按天退款,Month按月退款,Ratio按比例退款
          };
          if(this.fromData.rule==='Ratio'){
            params.ratio_value = this.fromData.ratio_value;//比例,当rule=Ratio时,需要传此值,默认为0
          }
          if (this.fromData.id) {
            params.id = this.fromData.id;
            upDateRefund(params).then(res => {
              this.$message.success({ content: res.data.msg, placement: 'top-right' });
              this.goback();
            }).catch(error => {
              this.$message.warning({ content: error.data.msg, placement: 'top-right' });
            });
          } else {
            addRefund(params).then(res => {
              this.$message.success({ content: res.data.msg, placement: 'top-right' });
              this.goback();
            }).catch(error => {
              this.$message.warning({ content: error.data.msg, placement: 'top-right' });
            });
          }
        },
        goback(showTip) {
          if(showTip){
            this.$dialog({
                theme:'warning',
                header:`${lang.sure_cancel}`,
                className: 't-dialog-new-class1 t-dialog-new-class2',
                style: 'color: rgba(0, 0, 0, 0.6)',
                confirmBtn:lang.sure,
                cancelBtn:lang.cancel,
                onConfirm: () => {
                  location.href = 'refund.html';
                  mydialog.hide();
                }
            });
          }else{
            location.href = 'refund.html';
          }
        }
      },
      created() {
        this.getProductOptions();
        if (this.getUrlOption().id) {
          this.getRefundDetail(this.getUrlOption().id);
        }
      },
    }).$mount(template);
    typeof old_onload == 'function' && old_onload();
  };
})(window);
