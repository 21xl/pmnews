function initTabs() {
  const tabContainers = document.querySelectorAll(".tabs");

  tabContainers.forEach((tabsContainer) => {
    const tabs = tabsContainer.querySelectorAll(".tabs__item");

    const contentContainer = tabsContainer
      .closest(".tabs")
      .parentElement.querySelector(".tabs__content");

    if (tabs.length && contentContainer) {
      const contents = Array.from(
        contentContainer.querySelectorAll(".tabs__content-item")
      );

      let activeTab = tabsContainer.querySelector(".tabs__item.active");
      let activeContent = contents.find((content) =>
        content.classList.contains("active")
      );

      if (!activeTab || !activeContent) {
        tabs.forEach((tab) => tab.classList.remove("active"));
        contents.forEach((content) => content.classList.remove("active"));

        tabs[0].classList.add("active");
        contents[0].classList.add("active");
      }

      tabs.forEach((tab) => {
        tab.addEventListener("click", (event) =>
          handleTabClick(event, tabsContainer, contentContainer)
        );
      });
    }
  });
}

function handleTabClick(event, tabsContainer, contentContainer) {
  const activeTab = tabsContainer.querySelector(".tabs__item.active");
  const activeContents = Array.from(
    contentContainer.querySelectorAll(".tabs__content-item")
  );

  const currentLevelContents = activeContents.filter(
    (content) => content.parentElement === contentContainer
  );

  if (activeTab) activeTab.classList.remove("active");
  currentLevelContents.forEach((tab) => tab.classList.remove("active"));

  const clickedTab = event.currentTarget;
  clickedTab.classList.add("active");

  const status = clickedTab.getAttribute("data-status");
  const targetContent = Array.from(
    contentContainer.querySelectorAll(
      `.tabs__content-item[data-status="${status}"]`
    )
  ).filter((content) => content.parentElement === contentContainer);

  if (targetContent) {
    targetContent.forEach((content) => content.classList.add("active"));
  }
}

document.addEventListener("DOMContentLoaded", () => {
  initTabs();

  const observer = new MutationObserver(() => {
    initTabs();
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true,
  });
});
