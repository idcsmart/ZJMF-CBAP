

window.onload = function () {
  var ele = document.getElementById('app')
  var app = new Vue({
    data: {
      curItem: 'h-doc-http',
      list: [],
    },
    methods: {
      getData () {
        axios
          .get('/v1/doc')
          .then(res => {
            this.list = res.data.data.list.filter(data=>data.section === 'function').map(item => {
              var temp = item
              temp.list = item.list.filter(ele => ele.class !== 'common')
              return temp
            })
          })
          .catch(err => {
            console.log(err)
          })
      },
      curHandel (event) {
        this.curItem = event.currentTarget.dataset.id
      }
    },
    created () {
      this.getData()
    },
    // watch: {
    //   list () {
    //     this.$forceUpdate()
    //   }
    // }
  }).$mount(ele)
}