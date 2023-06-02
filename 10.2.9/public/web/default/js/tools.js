$(function () {
  var galleryThumbs = new Swiper('.gallery-thumbs', {
    spaceBetween: 10,
    slidesPerView: 10,
    freeMode: true,
    watchSlidesVisibility: true,
    watchSlidesProgress: true,
    noSwiping: true

  });
  var galleryTop = new Swiper('.gallery-top', {
    spaceBetween: 10,
    thumbs: {
      swiper: galleryThumbs
    },
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
  });
})
