const prevButton = document.querySelector(".date-picker__button--prev");
const nextButton = document.querySelector(".date-picker__button--next");
const dateElement = document.querySelector(".date-picker__date");
const displayElement = document.querySelector(".date-picker__display");
const dropdownList = document.querySelector(".date-picker__list");
const tabsContainer = document.querySelector(".tabs__list");

const DAYS_OF_WEEK = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

if (prevButton && nextButton && dateElement && displayElement && dropdownList) {
  const today = new Date();
  const minDate = new Date(
    today.getFullYear(),
    today.getMonth(),
    today.getDate() - 7
  );
  const maxDate = new Date(
    today.getFullYear(),
    today.getMonth(),
    today.getDate() + 7
  );

  let currentDate = new Date(
    displayElement.getAttribute("data-value") || today.toDateString()
  );

  function getActiveTabStatus() {
    const activeTab = tabsContainer.querySelector(".tabs__item.active");
    return activeTab ? activeTab.getAttribute("data-status") : null;
  }

  function formatDateValue(date) {
    return date.toISOString().split("T")[0]; // ISO date format for yyyy-mm-dd
  }

  function formatDateDisplay(date) {
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const dayOfWeek = DAYS_OF_WEEK[date.getDay()];
    return `${day}/${month} ${dayOfWeek}`;
  }

  function getDateWithoutTime(date) {
    return new Date(date.getFullYear(), date.getMonth(), date.getDate());
  }

  function updateButtonsState() {
    const tabStatus = getActiveTabStatus();
    const referenceDate = getDateWithoutTime(
      new Date(displayElement.getAttribute("data-value"))
    );

    let canGoBack, canGoForward;

    if (tabStatus === "scheduled") {
      canGoBack = referenceDate > getDateWithoutTime(today);
      canGoForward = referenceDate < getDateWithoutTime(maxDate);
    } else if (tabStatus === "ended") {
      canGoBack = referenceDate > getDateWithoutTime(minDate);
      canGoForward = referenceDate < getDateWithoutTime(today);
    } else {
      canGoBack = referenceDate > minDate;
      canGoForward = referenceDate < maxDate;
    }

    toggleButtonState(prevButton, canGoBack);
    toggleButtonState(nextButton, canGoForward);
  }

  function toggleButtonState(button, isEnabled) {
    button.classList.toggle("disabled", !isEnabled);
    button.toggleAttribute("disabled", !isEnabled);
  }

  function updateDateDisplay(init = false) {
    currentDate = init ? today : currentDate;
    displayElement.setAttribute("data-value", formatDateValue(currentDate));
    dateElement.textContent = formatDateDisplay(currentDate);
    updateActiveDate();
    updateButtonsState();
  }

  function updateActiveDate() {
    Array.from(dropdownList.children).forEach((item) => {
      item.classList.toggle(
        "active",
        item.getAttribute("data-value") === formatDateValue(currentDate)
      );
    });
  }

  function generateDateList() {
    dropdownList.innerHTML = "";
    let startDate = new Date(today);
    let endDate = new Date(today);

    const tabStatus = getActiveTabStatus();
    if (tabStatus === "ended") {
      startDate.setDate(today.getDate() - 7);
    } else if (tabStatus === "scheduled") {
      endDate.setDate(today.getDate() + 7);
    } else {
      startDate.setDate(today.getDate() - 7);
      endDate.setDate(today.getDate() + 7);
    }

    for (
      let date = startDate;
      date <= endDate;
      date.setDate(date.getDate() + 1)
    ) {
      const listItem = document.createElement("li");
      listItem.className = "date-picker__list-item";
      listItem.textContent =
        date.toDateString() === today.toDateString()
          ? "Today"
          : formatDateDisplay(date);
      listItem.setAttribute("data-value", formatDateValue(date));
      listItem.addEventListener("click", handleDateSelection);
      dropdownList.appendChild(listItem);
    }

    updateActiveDate();
  }

  function handleDateSelection(event) {
    const value = event.currentTarget.getAttribute("data-value");
    currentDate = new Date(value);
    updateDateDisplay();
    dropdownList.classList.remove("visible");
  }

  displayElement.addEventListener("click", (e) => {
    e.stopPropagation();
    dropdownList.classList.toggle("visible");
  });

  document.addEventListener("click", (e) => {
    if (!dropdownList.contains(e.target)) {
      dropdownList.classList.remove("visible");
    }
  });

  prevButton.addEventListener("click", () => {
    if (getDateWithoutTime(currentDate) > minDate) {
      currentDate.setDate(currentDate.getDate() - 1);
      updateDateDisplay();
    }
  });

  nextButton.addEventListener("click", () => {
    if (getDateWithoutTime(currentDate) < maxDate) {
      currentDate.setDate(currentDate.getDate() + 1);
      updateDateDisplay();
    }
  });

  tabsContainer.addEventListener("click", () => {
    updateDateDisplay(true);
    generateDateList();
  });

  updateDateDisplay(true);
  generateDateList();
}
