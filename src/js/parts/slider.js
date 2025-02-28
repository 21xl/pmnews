document.addEventListener("DOMContentLoaded", function () {
  var heroSwiperEl = document.querySelector(".hero__swipper");
  if (heroSwiperEl) {
    var heroSwiper = new Swiper(heroSwiperEl, {
      loop: true,
      watchSlidesProgress: true,
      // autoplay: {
      //   delay: 4000,
      //   disableOnInteraction: false,
      // },
      navigation: {
        nextEl: ".hero__navigation-next",
        prevEl: ".hero__navigation-prev",
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
        renderBullet: function (index, className) {
          var formattedIndex = (index + 1).toString().padStart(2, "0");

          return (
            '<span class="' +
            className +
            '" data-index="' +
            formattedIndex +
            '">' +
            '<span class="swiper-counter">' +
            formattedIndex +
            "</span></span>"
          );
        },
      },
      effect: "fade",
      speed: 800,
      slidesPerView: 1,
    });
  }

  var dailySwiperEl = document.querySelector(".daily-swiper");

  if (dailySwiperEl) {
    var dailySwiper = new Swiper(dailySwiperEl, {
      navigation: {
        prevEl: ".daily-news__prev",
        nextEl: ".daily-news__next",
      },
      slidesPerView: 2,
      spaceBetween: 16,
      a11y: false,
      breakpoints: {
        768: {
          slidesPerView: 4,
          spaceBetween: 24,
        },
      },
      on: {
        slideChange: function () {
          updateLastVisibleSlide();
        },
        resize: function () {
          updateLastVisibleSlide();
        },
      },
    });

    function updateLastVisibleSlide() {
      dailySwiper.slides.forEach((slide) =>
        slide.classList.remove("last-visible")
      );

      var screenWidth = window.innerWidth;

      if (screenWidth <= 541) {
        return;
      } else {
        if (dailySwiper.slides.length > 4) {
          var lastVisibleIndex =
            dailySwiper.activeIndex + dailySwiper.params.slidesPerView - 1;
          setTimeout(function () {
            dailySwiper.slides[lastVisibleIndex].classList.add("last-visible");
          }, 50);
        }
      }
    }

    updateLastVisibleSlide();
  }

  var youtubeSwiperEl = document.querySelector(".youtube-slider__swiper");
  if (youtubeSwiperEl) {
    var youtubeSwiper = new Swiper(youtubeSwiperEl, {
      loop: true,
      slidesPerView: "auto",
      centeredSlides: true,
      draggable: true,
      speed: 600,
      effect: "coverflow",
      coverflowEffect: {
        rotate: 0,
        stretch: 0,
        depth: 100,
        modifier: 2,
        slideShadows: true,
      },
    });

    var prevButtons = document.querySelectorAll(".youtube-slider__prev");
    var nextButtons = document.querySelectorAll(".youtube-slider__next");

    prevButtons.forEach(function (button) {
      button.addEventListener("click", function () {
        youtubeSwiper.slidePrev();
      });
    });

    nextButtons.forEach(function (button) {
      button.addEventListener("click", function () {
        youtubeSwiper.slideNext();
      });
    });
  }

  var youtubeWidgetEl = document.querySelector(".youtube-widget__swiper");
  if (youtubeWidgetEl) {
    var youtubeWidgetSwiper = new Swiper(youtubeWidgetEl, {
      loop: true,
      slidesPerView: "auto",
      centeredSlides: true,
      draggable: true,
      speed: 600,
      effect: "coverflow",
      coverflowEffect: {
        rotate: 0,
        stretch: 0,
        depth: 100,
        modifier: 2,
        slideShadows: true,
      },
    });

    var prevButtons2 = document.querySelectorAll(".youtube-widget__prev");
    var nextButtons2 = document.querySelectorAll(".youtube-widget__next");

    prevButtons2.forEach(function (button) {
      button.addEventListener("click", function () {
        youtubeWidgetSwiper.slidePrev();
      });
    });

    nextButtons2.forEach(function (button) {
      button.addEventListener("click", function () {
        youtubeWidgetSwiper.slideNext();
      });
    });
  }

  //Single recommendation
  var recommendationEl = document.querySelector(".single__recommendation");

  if (recommendationEl) {
    var slides = recommendationEl.querySelectorAll(".swiper-slide");

    if (slides.length > 9) {
      slides.forEach(function (slide, index) {
        if (index >= 9) {
          slide.remove();
        }
      });
    }

    var paginationEl = document.querySelector(
      ".single__recommendation__pagination"
    );
    var nextEl = document.querySelector(".single__recommendation__next");
    var prevEl = document.querySelector(".single__recommendation__prev");

    var recommendationSwiper = new Swiper(recommendationEl, {
      slidesPerView: 1,
      a11y: false,
      spaceBetween:5,
      grid: {
        rows: 3,
        fill: "row",
      },
      pagination: paginationEl
        ? {
            el: paginationEl,
            clickable: true,
            renderBullet: function (index, className) {
              if (index < 3) {
                return '<span class="' + className + '"></span>';
              }
              return "";
            },
          }
        : false,
      navigation: {
        nextEl: nextEl ? nextEl : "",
        prevEl: prevEl ? prevEl : "",
      },
    });
  }

  // Category slider
var categorySliders = document.querySelectorAll(".category-slider__inner");

categorySliders.forEach(function(categoryEl) {
  var slides = categoryEl.querySelectorAll(".swiper-slide");

  if (slides.length > 15) {
    slides.forEach(function (slide, index) {
      if (index >= 15) {
        slide.remove();
      }
    });
  }

  var paginationEl = categoryEl.querySelector(".category-slider__pagination");
  var nextEl = categoryEl.querySelector(".category-slider__next");
  var prevEl = categoryEl.querySelector(".category-slider__prev");

  function initializeSwiper() {
    var categorySwiper = new Swiper(categoryEl, {
      slidesPerView: window.innerWidth <= 542 ? 1 : 3,
      spaceBetween: window.innerWidth <= 542 ? 5 : 20,
        a11y: false,
      grid: {
        rows: window.innerWidth <= 542 ? 6 : 2, 
        fill: "row",
      },
      pagination: paginationEl
        ? {
            el: paginationEl,
            clickable: true,
            renderBullet: function (index, className) {
              if (index < 6) {
                return '<span class="' + className + '"></span>';
              }
              return "";
            },
          }
        : false,
      navigation: {
        nextEl: nextEl ? nextEl : "",
        prevEl: prevEl ? prevEl : "",
      },
    });

    window.addEventListener("resize", function () {
      if (categorySwiper) {
        categorySwiper.destroy(true, true);
      }
      initializeSwiper();
    });
  }

  initializeSwiper();
});


});
