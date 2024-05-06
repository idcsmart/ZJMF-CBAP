// 创建一个 <link> 元素
const link = document.createElement("link");
// 设置 <link> 元素的属性
link.rel = "stylesheet";
link.type = "text/css";
link.href =
  "/plugins/addon/idcsmart_ticket/template/admin/css/opinionButton.css";
// 将 <link> 元素插入到 <head> 中
document.head.appendChild(link);
// css 样式依赖opinionButton.css
const opinionButton = {
  template: `
    <t-tooltip :content="lang.feedback" theme="light">
        <div class="draggable" ref="opinionButton" @mousedown.prevent="startDrag">
        <t-icon name="mail" />
        </div>
    </t-tooltip>
    `,
  created() {},
  data() {
    return {
      initialX: 0,
      initialY: 0,
      isDraging: false,
    };
  },
  props: {},
  methods: {
    startDrag(event) {
      event.preventDefault(); // 防止默认行为，比如选择文本
      const button = this.$refs.opinionButton;
      document.addEventListener("mousemove", this.drag);
      document.addEventListener("mouseup", this.stopDrag);
      this.initialX = event.clientX - button.offsetLeft;
      this.initialY = event.clientY - button.offsetTop;
    },
    drag(event) {
      const button = this.$refs.opinionButton;
      button.style.cursor = "move";
      this.isDraging = true;
      button.style.left = event.clientX - this.initialX + "px";
      button.style.top = event.clientY - this.initialY + "px";
    },
    stopDrag() {
      const button = this.$refs.opinionButton;
      button.style.cursor = "pointer";
      document.removeEventListener("mousemove", this.drag);
      document.removeEventListener("mouseup", this.stopDrag);
      if (!this.isDraging) {
        this.goOpinion();
      } else {
        this.isDraging = false;
      }
    },
    goOpinion() {
      location.href =
        "http://" +
        location.host +
        "/" +
        location.pathname.split("/")[1] +
        "/" +
        `template.htm`;
    },
  },
};
