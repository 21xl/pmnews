document.addEventListener("DOMContentLoaded", function () {
  const searchModal = document.getElementById("search-modal");
  const searchInput = document.getElementById("search-input");
  const searchResults = document.getElementById("search-results");
  const recentQueries = document.getElementById("recent-queries");
  const searchTrigger = document.querySelector(".header__search");
  const closeSearch = document.getElementById("modal-search__close");
  const additionalSearch = document.getElementById("close-search");
  const modalTitle = document.querySelector(".modal-search__title");
  const modalSearchResults = document.querySelector(".modal-search__results");
  const mobileWrapper = document.querySelector(".modal-search__mobile");
  const recommendationsSection = document.getElementById("recommendations-section");
  const searchMessage = document.querySelector(".modal-search__message");
  const clearSearchButton = document.getElementById("clear-search");
  const searchButton = document.querySelector(".view-all-results");
  const loadingSpinner = document.querySelector(".modal-search__loading");

  let currentQuery = "";
  let searchTimeout = null;

  if (modalSearchResults) modalSearchResults.style.display = "none";
  if (recommendationsSection) recommendationsSection.style.display = "none";
  if (searchMessage) searchMessage.style.display = "none";
  if (clearSearchButton) clearSearchButton.style.display = "none";
  if (loadingSpinner) loadingSpinner.style.display = "none";

  function getCurrentLanguage() {
    return document.documentElement.lang;
  }

  function updateRecentQueries() {
    if (!recentQueries || !modalTitle) return;

    const lang = getCurrentLanguage();
    const queries = JSON.parse(localStorage.getItem(`recentQueries_${lang}`)) || [];
    recentQueries.innerHTML = "";
    queries.forEach((query) => {
      const li = document.createElement("li");
      li.textContent = query;
      li.classList.add("recent-query-item");

      li.addEventListener("click", function () {
        searchInput.value = query;
        currentQuery = query;
        searchInput.dispatchEvent(new Event("input"));
      });

      const closeButton = document.createElement("button");
      closeButton.textContent = "×";
      closeButton.classList.add("close-query-button");
      closeButton.addEventListener("click", function (e) {
        e.stopPropagation();
        removeRecentQuery(query);
      });

      li.appendChild(closeButton);
      recentQueries.appendChild(li);
    });

    modalTitle.style.display = queries.length > 0 ? "block" : "none";
    recentQueries.style.display = queries.length > 0 ? "block" : "none";
  }

  function removeRecentQuery(queryToRemove) {
    const lang = getCurrentLanguage();
    let queries = JSON.parse(localStorage.getItem(`recentQueries_${lang}`)) || [];
    queries = queries.filter((query) => query !== queryToRemove);
    localStorage.setItem(`recentQueries_${lang}`, JSON.stringify(queries));
    updateRecentQueries();
  }

  function saveRecentQuery(query) {
    const lang = getCurrentLanguage();
    let queries = JSON.parse(localStorage.getItem(`recentQueries_${lang}`)) || [];

    if (!queries.includes(query)) {
      queries.push(query);
      if (queries.length > 5) queries.shift();
      localStorage.setItem(`recentQueries_${lang}`, JSON.stringify(queries));
    }
  }

  function storeTokenInSession(token) {
    // Сохраняем токен в sessionStorage
    sessionStorage.setItem('searchToken', token);
  }

  function toggleModalSearchResults(show) {
    if (modalSearchResults)
      modalSearchResults.style.display = show ? "block" : "none";
  }

  function closeSearchModal() {
    if (searchModal) searchModal.classList.remove("modal-search__open");
    if (searchResults) {
      searchResults.innerHTML = "";
      searchResults.classList.remove("active");
    }
    if (searchInput) searchInput.value = "";
    toggleModalSearchResults(false);
    document.body.classList.remove("modal-search__freeze");
  }

  
  function getCurrentLangFromURL() {
    const pathSegments = window.location.pathname.split("/").filter(Boolean);
    const languagePrefixes = [
      "af", "sq", "am", "ar", "hy", "az", "eu", "be", "bn", "bs", "bg", "ca", "ceb", "ny", "zh", "zh-cn", "zh-tw", "zh-hk",
      "co", "hr", "cs", "da", "nl", "en", "en-us", "en-gb", "eo", "et", "tl", "fi", "fr", "fr-ca", "fr-fr", "fy", "gl", "ka",
      "de", "de-at", "de-ch", "el", "gu", "ht", "ha", "haw", "iw", "he", "hi", "hmn", "hu", "is", "ig", "id", "ga", "it",
      "ja", "jw", "kn", "kk", "km", "ko", "ku", "ky", "lo", "la", "lv", "lt", "lb", "mk", "mg", "ms", "ml", "mt", "mi",
      "mr", "my", "ne", "no", "nb", "nn", "or", "ps", "fa", "pl", "pt", "pt-br", "pt-pt", "pa", "ro", "ru", "sm", "gd",
      "sr", "sr-latn", "st", "sn", "sd", "si", "sk", "sl", "so", "es", "es-es", "es-mx", "su", "sw", "sv", "tg", "ta", "te",
      "th", "tr", "uk", "ua", "ur", "uz", "vi", "cy", "xh", "yi", "yo", "zu"
  ];
  
  
  
    if (pathSegments.length > 0 && languagePrefixes.includes(pathSegments[0])) {
      return pathSegments[0];  
    }
    return "";  
  }

  if (searchTrigger) {
    searchTrigger.addEventListener("click", function () {
      if (searchModal) searchModal.classList.add("modal-search__open");
      if (searchInput) searchInput.focus();
      if (searchResults) searchResults.innerHTML = "";
      updateRecentQueries();
      if (window.innerWidth <= 541)
        document.body.classList.add("modal-search__freeze");
    });
  }

  if (closeSearch) {
    closeSearch.addEventListener("click", function () {
      closeSearchModal();
    });
  }

  window.addEventListener("click", function (event) {
    if (
      searchModal &&
      searchModal.classList.contains("modal-search__open") &&
      !searchModal.contains(event.target) &&
      !searchTrigger.contains(event.target)
    ) {
      closeSearchModal();
    }
  });

  if (searchInput) {
    searchInput.addEventListener("input", function () {
      currentQuery = searchInput.value.trim();
      if (clearSearchButton)
        clearSearchButton.style.display =
          currentQuery.length > 0 ? "block" : "none";

      if (currentQuery.length > 3) {
        toggleModalSearchResults(true);
        if (recommendationsSection)
          recommendationsSection.style.display = "none";

        if (loadingSpinner) loadingSpinner.style.display = "block";

        if (searchTimeout) {
          clearTimeout(searchTimeout);
        }

        searchTimeout = setTimeout(function () {
          const xhr = new XMLHttpRequest();
          xhr.open("POST", ajax_object.ajaxurl, true);
          xhr.setRequestHeader(
            "Content-Type",
            "application/x-www-form-urlencoded; charset=UTF-8"
          );

          xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 400) {
              const response = JSON.parse(xhr.responseText);
              console.log(xhr.responseText);
              if (searchResults) searchResults.innerHTML = response.html;

              const searchCount = document.getElementById("search-count");
              if (searchCount) searchCount.innerText = response.total;

              const viewAllContainer = document.getElementById(
                "view-all-results-container"
              );

              if (viewAllContainer)
                viewAllContainer.innerHTML = response.view_all_button;

              if (response.total > 0) {
                if (searchResults) searchResults.classList.add("active");
                if (recommendationsSection)
                  recommendationsSection.style.display = "none";
                if (searchMessage) searchMessage.style.display = "none";
              } else {
                if (searchResults) searchResults.classList.remove("active");
                toggleModalSearchResults(false);
                if (recommendationsSection)
                  recommendationsSection.style.display = "block";
                mobileWrapper.classList.add("mobile-wrapper");
                if (searchMessage) searchMessage.style.display = "flex";
              }


              if (response.token) {
                storeTokenInSession(response.token);
              }
            }

            if (loadingSpinner) loadingSpinner.style.display = "none";
          };

          xhr.onerror = function () {
            if (loadingSpinner) loadingSpinner.style.display = "none";
          };

          xhr.send(
            `action=search_ajax&query=${encodeURIComponent(currentQuery)}`
          );
        }, 500);
      } else {
        toggleModalSearchResults(false);
        if (searchResults) {
          searchResults.classList.remove("active");
          searchResults.innerHTML = "";
        }
        const searchCount = document.getElementById("search-count");
        if (searchCount) searchCount.innerText = "0";
        const viewAllContainer = document.getElementById(
          "view-all-results-container"
        );
        if (viewAllContainer) viewAllContainer.innerHTML = "";
        if (recommendationsSection)
          recommendationsSection.style.display = "block";
        if (searchMessage) searchMessage.style.display = "none";
        if (loadingSpinner) loadingSpinner.style.display = "none";
      }
    });

    searchInput.addEventListener("keydown", function (event) {
      if (event.key === "Enter") {
        event.preventDefault();
        if (currentQuery.length > 0) {
          saveRecentQuery(currentQuery);
          const searchQuery = encodeURIComponent(currentQuery);
          const currentLang = getCurrentLangFromURL();
          const searchURL = currentLang ? `/${currentLang}/?s=${searchQuery}` : `/?s=${searchQuery}`;
          window.location.href = searchURL;
        }
      }
    });

    additionalSearch.addEventListener("click", function () {
      const currentQuery = searchInput.value.trim();
      if (currentQuery.length > 0) {
        saveRecentQuery(currentQuery);
        const searchQuery = encodeURIComponent(currentQuery);
        const currentLang = getCurrentLangFromURL();
        const resultsPage = currentLang ? `/${currentLang}/` : "/";
        window.location.href = `${resultsPage}?s=${searchQuery}`;
      }
    });
  }

  if (clearSearchButton) {
    clearSearchButton.addEventListener("click", function () {
      if (searchInput) searchInput.value = "";
      clearSearchButton.style.display = "none";
      toggleModalSearchResults(false);
      if (searchResults) searchResults.innerHTML = "";
      const searchCount = document.getElementById("search-count");
      if (searchCount) searchCount.innerText = "0";
      if (recommendationsSection)
        recommendationsSection.style.display = "block";
      if (searchMessage) searchMessage.style.display = "none";
      if (loadingSpinner) loadingSpinner.style.display = "none";
    });
  }

  if (searchResults) {
    searchResults.addEventListener("click", function (event) {
      if (event.target.matches("a") || event.target.closest("a")) {
        const queryText = searchInput.value.trim();
        if (queryText.length > 0) {
          saveRecentQuery(queryText);
        }
        searchInput.value = queryText;
        currentQuery = queryText;
      }
    });
  }

  const viewAllResultsContainer = document.getElementById('view-all-results-container');
  if (viewAllResultsContainer) {
    viewAllResultsContainer.addEventListener("click", function (event) {
      if (event.target) {
        const queryText = searchInput.value.trim();
        if (queryText.length > 0) {
          saveRecentQuery(queryText);
        }
        searchInput.value = queryText;
        currentQuery = queryText;
      }
    });
  }

  if (searchButton) {
    searchButton.addEventListener("click", function () {
      if (currentQuery.length > 0) {
        saveRecentQuery(currentQuery);
      }
    });
  }

  updateRecentQueries();
});
