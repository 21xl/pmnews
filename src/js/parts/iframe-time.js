document.addEventListener("DOMContentLoaded", function () {
  function setupLazyLoading(selector) {
    var lazyVideos = [].slice.call(document.querySelectorAll(selector));

    if ("IntersectionObserver" in window) {
      let lazyVideoObserver = new IntersectionObserver(function (
        entries,
        observer
      ) {
        entries.forEach(function (video) {
          if (video.isIntersecting) {
            var iframe = document.createElement("iframe");
            var videoURL = video.target.getAttribute("data-embed");
            iframe.setAttribute("src", videoURL);
            iframe.setAttribute("frameborder", "0");
            iframe.setAttribute("allowfullscreen", "");
            iframe.setAttribute(
              "allow",
              "accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
            );
            video.target.innerHTML = ""; // Очистка заглушки
            video.target.appendChild(iframe); // Вставляем iframe
            lazyVideoObserver.unobserve(video.target); // Прекращаем наблюдение
          }
        });
      });

      lazyVideos.forEach(function (video) {
        lazyVideoObserver.observe(video);
      });
    }
  }

  // Настраиваем ленивую загрузку для обоих классов
  setupLazyLoading(".youtube-slider__slide");
  setupLazyLoading(".youtube-widget__slide");
});
