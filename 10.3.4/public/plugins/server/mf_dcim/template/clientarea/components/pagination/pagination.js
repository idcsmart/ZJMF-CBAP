
// css 样式依赖common.css
const pagination = {
    template:
        `
        <div class="myPage">
            <el-pagination 
            @size-change="handleSizeChange" 
            @current-change="handleCurrentChange"
            :current-page="pageData.page" 
            :page-sizes="pageData.pageSizes" :page-size="pageData.limit"
            layout="slot, sizes, prev, pager,jumper, next" :total="pageData.total"
            :pager-count=5
            >
            <span class="page-total">共{{pageData.total}}项数据</span>
            </el-pagination>
        </div>
        `,
    data() {
        return {

        }
    },
    props: {
        pageData: {
            default: function () {
                return {
                    page: 1,
                    pageSizes: [20, 50, 100],
                    limit: 20,
                    total: 400
                }
            }
        }
    },
    methods: {
        handleSizeChange(e) {
            this.pageData.limit = e
            this.$emit('sizechange', e)
        },
        handleCurrentChange(e) {
            this.pageData.page = e
            this.$emit('currentchange', e)

        }
    },
}