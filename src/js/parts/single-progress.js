document.addEventListener("scroll", function () {
  updateProgressBar();
});

function updateProgressBar() {
  var progressBarInner = document.querySelector(".progress-bar__inner");

  if (progressBarInner) {
    var scrollTop =
      document.documentElement.scrollTop || document.body.scrollTop;
    var scrollHeight =
      document.documentElement.scrollHeight -
      document.documentElement.clientHeight;

    if (scrollHeight > 0) {
      var scrolled = (scrollTop / scrollHeight) * 100;
      progressBarInner.style.width = scrolled + "%";
    }
  }
}
