$(function () {
  let navflag = false;
  $('.nav-tab').click(function () {
    $(this).siblings().each(function () {
      $(this).removeClass('a_active');
      // $(this).removeClass('a_active');
      $(this).find('.nav-box').css('height', '0')
      //关闭右侧箭头
      if ($(this).attr('class').indexOf('nav-ul') != -1) {
        $(this).find('.icon-bottom').css('transform', 'rotateZ(0deg)')
        $(this).find('.icon-bottom').css('transition', 'all .5s')
        $(this).removeClass('nav-show')
        // $(this).find('div').removeClass('nav-box')
      }
    })
    //当前选中
    $(this).addClass('a_active')
    $(this).find('.li-a').addClass('active')
    // 打开右侧箭头
    $(this).find('.icon-bottom').css('transform', 'rotateZ(180deg)')
    $(this).find('.icon-bottom').css('transition', 'all .5s')
    $(this).addClass('nav-show')
    // $(this).find('div').addClass('nav-box')
  })
  /* 二级菜单a点击事件 */
  $(".li-a-a").click(function () {
    $(".li-a-a").each(function () {
      $(this).removeClass('active-li-a');
    })
    $(this).addClass('active-li-a');
  })


  $('.icon-menu').click(function () {
    $('.overview').toggle()
  });

  /* 文档左侧切换效果 */
  $('.chevron-right-bottom').click(function(){
    let btn = $(this)
    $(this).toggleClass('active');
    $('.nav-div ul li').each(function(ind,el){
      if (btn.hasClass('active')) {
        $(el).addClass('nav-show active').find('.li-a').addClass('active')
      } else {
        $(el).removeClass('nav-show active').find('.li-a').removeClass('active')
      }
    })
  })
})