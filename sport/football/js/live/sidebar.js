import store from "./pinned.store";
import Handlebars from "handlebars";
import templateSource from "../../templates/pinned-template.html";
import templateComp from "../../templates/side-template.html";

const template = Handlebars.compile(templateSource);
const templateCompet = Handlebars.compile(templateComp);

export async function fetchAndStoreMatches() {
  const storedPinned = localStorage.getItem("pinned");
  const defaultPin = [
    "jednm9whz0ryox8",
    "vl7oqdehlyr510j",
    "4zp5rzghp5q82w1",
    "gy0or5jhg6qwzv3",
    "yl5ergphnzr8k0o",
    "kn54qllh40qvy9d",
    "vl7oqdeheyr510j",
    "9vjxm8ghx2r6odg",
    "z8yomo4h7wq0j6l",
    "56ypq3nh0xmd7oj",
    "p4jwq2gh754m0ve",
    "d23xmvkh43oqg8n",
    "49vjxm8ghgr6odg",
  ];

  if (!storedPinned) {
    localStorage.setItem("pinned", JSON.stringify(defaultPin));
  }
  const pinned = JSON.parse(localStorage.getItem("pinned")) || [];
  const sceleton = document.querySelector(".sceleton_sb_pined");
  const empty = document.querySelector(".empty_sb_pined");

  if (pinned.length === 0) {
    if (sceleton) {
      sceleton.classList.add("hidden");
    }
    if (empty) {
      empty.classList.remove("hidden");
    }

    return;
  }

  try {
    const response = await fetch("/wp-json/sports/v1/matches_by_competition", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ competition_ids: pinned }),
    });

    if (!response.ok) {
      throw new Error(
        `Server error: ${response.status} - ${response.statusText}`
      );
    }
    if (sceleton) {
      sceleton.classList.add("hidden");
    } else {
      console.error("Элемент с классом 'sceleton_sb_pined' не найден.");
    }
    const data = await response.json();

    store.setData(data);
  } catch (error) {
    console.error("Error fetching matches:", error);
  }
}

function addLeague(league, competitionList) {
  const html = template(league);
  competitionList.insertAdjacentHTML("beforeend", html);
}

function updateLeague(league) {
  const existingElement = document.querySelector(
    `[data-id="${league.competition.id}"]`
  );
  if (existingElement) {
    existingElement.outerHTML = template(league);
  }
}

function removeLeague(id) {
  const existingElement = document.querySelector(`[data-leagid="${id}"]`);

  if (existingElement) {
    existingElement.remove();
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const countryItems = document.querySelectorAll(".countries__item");

  countryItems.forEach((item) => {
    item.addEventListener("click", async function (event) {
      const currentItem = event.currentTarget;
      const loader = currentItem.querySelector(
        ".statistics-sidebar__item-loader"
      );
      const competitionsContainer = currentItem.querySelector(
        ".statistics-sidebar__submenu"
      );

      document
        .querySelectorAll(".countries__item.active")
        .forEach((activeItem) => {
          if (activeItem !== currentItem) {
            const activeCompetitionsContainer = activeItem.querySelector(
              ".statistics-sidebar__submenu"
            );
            if (activeCompetitionsContainer) {
              activeCompetitionsContainer.style.display = "none";
            }
            activeItem.classList.remove("active");
          }
        });

      if (currentItem.classList.contains("active")) {
        if (competitionsContainer) {
          competitionsContainer.style.display = "none";
        }
        currentItem.classList.remove("active");
        return;
      }

      currentItem.classList.add("active");
      if (loader) loader.style.display = "flex";

      if (competitionsContainer) {
        if (loader) loader.style.display = "none";
        competitionsContainer.style.display = "flex";
        return;
      }

      try {
        const response = await fetch(
          `/wp-json/sports/v1/competitions_by_c?country_id=${currentItem.dataset.id}&category_id=${currentItem.dataset.idcat}`,
          {
            method: "GET",
            headers: {
              "Content-Type": "application/json",
            },
          }
        );

        if (!response.ok) {
          throw new Error(
            `Ошибка сервера: ${response.status} ${response.statusText}`
          );
        }

        const data = await response.json();
        const pinned = JSON.parse(localStorage.getItem("pinned")) || [];

        const competitions = data.competitions.map((item) => ({
          ...item,
          pinned: pinned.includes(item.id),
        }));

        const html = templateCompet({ competitions });
        const newContainer = document.createElement("div");
        newContainer.innerHTML = html;
        currentItem.appendChild(newContainer);
      } catch (error) {
        console.error("Ошибка запроса:", error);
      } finally {
        if (loader) loader.style.display = "none";
      }
    });
  });
});

Handlebars.registerHelper("isPinnedClass", function (isPined) {
  if (isPined) return "active";
  else return "";
});

document.addEventListener("DOMContentLoaded", () => {
  const toggleButton = document.getElementById("toggle-button");
  const toggleButtonText = toggleButton.querySelector("span");
  const hiddenItems = document.querySelectorAll(".countries__item.hidden");
  const competitionList = document.getElementById("pinned");
  const empty = document.querySelector(".empty_sb_pined");
  const otherList = document.querySelector(".statistics-sidebar__block--other");
  let hidden = true;

  if (toggleButton) {
    toggleButton.addEventListener("click", () => {
      if (hidden) {
        hiddenItems.forEach((item) => item.classList.remove("hidden"));
        toggleButtonText.textContent = "Show less";
        toggleButton.classList.add("less");
        otherList.classList.remove("hidden");
        hidden = false;
      } else {
        hiddenItems.forEach((item) => item.classList.add("hidden"));
        toggleButtonText.textContent = "Show more";
        toggleButton.classList.remove("less");
        otherList.classList.add("hidden");
        hidden = true;
      }
    });
  }

  fetchAndStoreMatches();

  let previousData = new Map();

  store.onDataChange((newData) => {
    const newDataMap = new Map(newData.map((league) => [league.id, league]));

    newDataMap.forEach((newItem, id) => {
      const oldItem = previousData.get(id);
      if (!oldItem) {
        addLeague(newItem, competitionList);
      } else if (JSON.stringify(newItem) !== JSON.stringify(oldItem)) {
        updateLeague(newItem);
      }
    });

    previousData.forEach((_, id) => {
      if (!newDataMap.has(id)) {
        removeLeague(id);
      }
    });

    previousData = newDataMap;
    const pinned = JSON.parse(localStorage.getItem("pinned")) || [];

    if (pinned.length === 0) {
      if (empty) {
        empty.classList.remove("hidden");
      }
    } else {
      if (empty) {
        empty.classList.add("hidden");
      }
    }
  });
});
