$(function () {
  const setIndexData = () => {
    const commentObj = JSON.parse(sessionStorage.commentData)
    commentObj.feedback_type.forEach((item, index) => {
      $('#radioBox').append(`
        <div class="radio">
          <input type="radio" name="radioId" id="ptionsRadios${index}" value=${item.id} />
          <label for="${item.id}">
            <p class="radio-title">${item.name}</p>
            <p class="radio-desc">${item.description}</p>
          </label>
        </div>
      `)
    })
    $("#ptionsRadios0").prop('checked', true)
    console.log();
  }

  $("#subBtn").click(function (event) {
    event.preventDefault(); // 阻止默认的提交行为
    // 获取表单元素
    const titleInput = $('#inputTitle');
    const descriptionInput = $('#inputDescription');
    // 验证表单元素
    if (!validateRequired(titleInput)) {
      return;
    }
    if (!validateRequired(descriptionInput)) {
      return;
    }
    subFeedback()
    $("#ptionsRadios0").prop('checked', true)
    $('#inputTitle').val("")
    $('#inputDescription').val("")
    $('#contactInput').val("")
  })
  // 提交
  function subFeedback() {
    $.ajax({
      url: "/console/v1/feedback",
      method: 'POST',
      headers: {
        'Authorization': "Bearer" + " " + localStorage.jwt
      },
      data: {
        type: $('input[name="radioId"]:checked').val(),
        title: $('#inputTitle').val(),
        description: $('#inputDescription').val(),
        attachment: [],
        contact: $('#contactInput').val()
      },
      success: function (res) {
        showMessage('success', '提交成功！', 2000); // 显示 3 秒钟的成功消息
      }
    });
  }


  // 表单元素必填验证函数
  function validateRequired(input) {
    if (input.val().trim() === '') {
      input.attr('style', 'border: 1px solid #FF6739;')
      input.focus();
      return false;
    }
    input.attr('style', 'border: 1px solid #E6EAED;')
    return true;
  }
  // 获取通用配置信息
  function getCommentInfo() {
    $.ajax({
      url: "/console/v1/common",
      method: 'get',
      headers: {
        'Authorization': "Bearer" + " " + localStorage.jwt
      },
      success: function (res) {
        sessionStorage.commentData = JSON.stringify(res.data)
        setIndexData()
      }
    });
  }
  getCommentInfo()
})