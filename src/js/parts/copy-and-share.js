document.addEventListener("DOMContentLoaded", function () {
  var copyButtons = document.querySelectorAll(".copy");
  if (copyButtons.length > 0) {
    copyButtons.forEach(function (button) {
      button.addEventListener("click", function () {
       
        var homePageUrl = window.location.origin;
        var text = window.location.href;
        var copyText = `Source: ${homePageUrl}\nLink: ${text}`;
        var dummy = document.createElement("textarea");
        
        document.body.appendChild(dummy);
        dummy.value = copyText;
        dummy.select();
        document.execCommand("copy");
        document.body.removeChild(dummy);
  
        
        var copiedText = this.querySelector('.copy-copied');
        copiedText.classList.add('active');
        setTimeout(function () {
          copiedText.classList.remove('active');
        }, 5000);  
      });
    });
  }
  
  
  

  var facebookButtons = document.querySelectorAll(".share-facebook");
  if (facebookButtons.length > 0) {
    facebookButtons.forEach(function (button) {
      button.addEventListener("click", function () {
        var url = window.location.href;
        var facebookUrl =
          "https://www.facebook.com/sharer/sharer.php?u=" +
          encodeURIComponent(url);
        window.open(facebookUrl, "_blank", "width=600,height=400");
      });
    });
  }

  var twitterButtons = document.querySelectorAll(".share-twitter");
  if (twitterButtons.length > 0) {
    twitterButtons.forEach(function (button) {
      button.addEventListener("click", function () {
        var url = window.location.href;
        var twitterUrl =
          "https://twitter.com/intent/tweet?url=" + encodeURIComponent(url);
        window.open(twitterUrl, "_blank", "width=600,height=400");
      });
    });
  }
});
