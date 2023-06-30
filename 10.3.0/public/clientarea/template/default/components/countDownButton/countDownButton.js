// 父组件执行该组件的countDown() 实现倒计时
const countDownButton = {
    template:
        `
        <el-button :class="myClass" v-loading="loading" :disabled="!flag">{{ flag? lang.finance_text145 : num + lang.finance_text146}}</el-button>
        `,
    data() {
        return {
            num: 60,
            flag: true,
            timer: null,
            loading: false
        }
    },
    props: {
        myClass: {
            type: String,
            default: "count-down-btn"
        },
    },
    created() {
    },
    methods: {
        countDown() {
            this.flag = false
            this.num = --this.num
            this.timer = setInterval(() => {
                if (this.num > 1) {
                    this.flag = false
                    this.num = --this.num
                } else {
                    clearInterval(this.timer);
                    this.timer = null
                    this.flag = true
                    this.num = 60
                }
            }, 1000)
        },
    },
}