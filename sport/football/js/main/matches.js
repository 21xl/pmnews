import Handlebars from "handlebars";
import templateSource from "../../templates/match-template.html";
import matcheserror from "../../templates/matches-error.html";
import sceleton from "../../templates/matches-sceleton.html";
import pinned from "./pinned.store";
import incidents from "./incidents.store";
import competitionStore from "./competition.store";
import i18next from "./i18n";

jQuery(document).ready(function ($) {
  const currentLanguage = document.documentElement.lang;

  const template = Handlebars.compile(templateSource);
  const mEerortemplate = Handlebars.compile(matcheserror);
  const sceletontemplate = Handlebars.compile(sceleton);
  const matchesMain = $(".matches__main");

  const ITEMS_PER_PAGE = 10;
  let tab = "all";

  let renderedCount = 0;

  function getPinnedItems() {
    return JSON.parse(localStorage.getItem("pinned")) || [];
  }

  async function updatePinnedItems(id) {
    const pinnedItems = getPinnedItems();
    const index = pinnedItems.indexOf(id);

    if (index === -1) {
      pinnedItems.push(id);
    } else {
      pinnedItems.splice(index, 1);
    }

    localStorage.setItem("pinned", JSON.stringify(pinnedItems));

    competitionStore.updatePinned(id, index === -1);

    if (id) {
      pinned.updatePinned(id);
    } else {
      if (index !== -1) {
        pinned.delPinned(id);
      }
    }

    if (tab === "live") {
      const liveMatches = competitionStore.filterDataByStatus(tab);
      renderMatches(liveMatches, false, false, true);
    } else if (tab === "ended") {
      const endedMatches = competitionStore.filterDataByStatus(tab);
      renderMatches(endedMatches, false, false, true);
    } else {
      renderMatchesWithUpdatedOrder();
    }
  }

  function addPinnedFlag(data) {
    const pinnedItems = getPinnedItems();
    return data.map((item) => {
      const isPinned = pinnedItems.includes(item.competition.id);
      return {
        ...item,
        pinned: isPinned,
      };
    });
  }

  function renderMatches(
    data,
    append = false,
    showLoadMore = true,
    allData = false
  ) {
    const startIndex = append ? renderedCount : 0;
    const endIndex = append
      ? data.length
      : Math.min(data.length, ITEMS_PER_PAGE);
    const matchesToRender = allData ? data : data.slice(startIndex, endIndex);

    if (append) {
      renderedCount += matchesToRender.length;
    } else {
      renderedCount = matchesToRender.length;
    }

    let html = "";
    matchesToRender.forEach((league) => {
      html += template(league);
    });

    if (append) {
      matchesMain.append(html);
    } else {
      if (matchesToRender.length > 0) {
        matchesMain.html(html);
      } else {
        matchesMain.html(mEerortemplate);
      }
    }

    initializePinnedItems();
    toggleHide();
    initToggleFav();
  }

  function renderMatchesWithUpdatedOrder() {
    const data = competitionStore.getData();
    const excludeStatuses = ["9", "13"];

    const filteredData = data.map((competition) => ({
      ...competition,
      matches: competition.matches.filter(
        (match) => !excludeStatuses.includes(String(match.status_id))
      ),
    }));

    const nonEmptyData = filteredData.filter(
      (competition) => competition.matches && competition.matches.length > 0
    );

    const sortedData = [...nonEmptyData].sort((a, b) => b.pinned - a.pinned);

    renderMatches(sortedData, false, false, true);
  }

  function initializePinnedItems() {
    const pinnedItems = getPinnedItems();
    $(".pin").each(function () {
      const competitionId = $(this).data("competition_id");
    });
  }

  async function onDateChange(newDateValue, tab) {
    await fetchAndRenderMatches(newDateValue, tab);

    const tabElement = document.querySelector(`.tabs__item[data-status="all"]`);
    if (tab == "ended" || tab == "scheduled") return;
    if (tabElement) {
      document.querySelectorAll(".tabs__item").forEach((item) => {
        item.classList.remove("active");
      });

      tabElement.classList.add("active");
    } else {
      console.error(`Элемент с data-status="all" не найден.`);
    }
  }

  const observer = new MutationObserver((mutationsList) => {
    for (const mutation of mutationsList) {
      if (
        mutation.type === "attributes" &&
        mutation.attributeName === "data-value"
      ) {
        const newDateValue = mutation.target.getAttribute("data-value");

        if (tab && (tab == "all" || tab == "ended" || tab == "scheduled")) {
          onDateChange(newDateValue, tab);
        }
      }
    }
  });

  const datePickerDisplay = document.querySelector(".date-picker__display");
  if (datePickerDisplay) {
    observer.observe(datePickerDisplay, {
      attributes: true,
    });
  }

  async function fetchAndRenderMatches(date = null, tab = null) {
    try {
      const now = date ? new Date(date) : new Date();
      matchesMain.html(sceletontemplate);
      const year = now.getFullYear();
      const month = String(now.getMonth() + 1).padStart(2, "0"); // Месяц начинается с 0
      const day = String(now.getDate()).padStart(2, "0");
      const localFormattedDate = `${year}-${month}-${day}`;

      const response = await fetch(
        `/wp-json/sports/v1/matches_by_date?date=${localFormattedDate}&timezone_offset=0`
      );

      if (!response.ok) {
        throw new Error(`Ошибка запроса: ${response.statusText}`);
      }

      const data = await response.json();
      const processedData = addPinnedFlag(data);
      competitionStore.setData(processedData);

      if (!tab) return renderMatchesWithUpdatedOrder();
      const endedMatches = competitionStore.filterDataByStatus(tab);
      renderMatches(endedMatches, false, false, true);
    } catch (error) {}
  }

  $(document).on("click", ".pin", function (event) {
    const competitionId = $(this).data("competition_id");

    updatePinnedItems(competitionId);
    $(this).toggleClass("active");
  });

  function setActiveTab(tab) {
    $(".tabs__item").removeClass("active");
    $(tab).addClass("active");
  }

  const datePicker = document.querySelector(".date-picker");
  const displayElement = document.querySelector(".date-picker__display");
  const dateElement = document.querySelector(".date-picker__date");
  const daysOfWeek = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

  function formatDateDisplay(date) {
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const dayOfWeek = daysOfWeek[date.getDay()];
    return `${day}/${month} ${dayOfWeek}`;
  }

  $(".tabs__item").on("click", async function () {
    tab = $(this).data("status");

    setActiveTab(this);

    const now = new Date();
    const todayFormatted = now.toISOString().split("T")[0];
    const currentSelectedDate =
      displayElement?.getAttribute("data-value") || todayFormatted;

    if (tab === "live" && currentSelectedDate !== todayFormatted) {
      if (datePicker) {
        datePicker.classList.add("hidden");
      }

      displayElement.setAttribute("data-value", todayFormatted);
      if (dateElement)
        dateElement.textContent = formatDateDisplay(new Date(todayFormatted));

      await fetchAndRenderMatches(todayFormatted, "live");

      if (tab === "live") {
        const liveMatches = competitionStore.filterDataByStatus(tab);
        renderMatches(liveMatches, false, false, true);
      } else if (tab === "ended") {
        const endedMatches = competitionStore.filterDataByStatus(tab);

        renderMatches(endedMatches, false, false, true);
      } else if (tab === "scheduled") {
        const endedMatches = competitionStore.filterDataByStatus(tab);
        renderMatches(endedMatches, false, false, true);
      }
    } else {
      if (tab === "live") {
        if (datePicker) {
          datePicker.classList.add("hidden");
        }
        const liveMatches = competitionStore.filterDataByStatus(tab);
        renderMatches(liveMatches, false, false, true);
      } else if (tab === "ended") {
        if (datePicker) {
          datePicker.classList.remove("hidden");
        }
        const endedMatches = competitionStore.filterDataByStatus(tab);
        renderMatches(endedMatches, false, false, true);
      } else if (tab === "scheduled") {
        if (datePicker) {
          datePicker.classList.remove("hidden");
        }
        const endedMatches = competitionStore.filterDataByStatus(tab);
        renderMatches(endedMatches, false, false, true);
      } else {
        datePicker.classList.remove("hidden");
        renderMatchesWithUpdatedOrder();
      }
    }
  });

  if (matchesMain) {
    fetchAndRenderMatches();
  }

  function updateLiveMatchesTime() {
    $(".matches__item.live").each(function () {
      const matchId = $(this).data("match_id");
      const matchTimeElement = $(this).find(".matches__item-time");

      const match = competitionStore
        .getData()
        .flatMap((competition) => competition.matches)
        .find((m) => m.id === matchId);

      if (match) {
        const currentTimestamp = Math.floor(Date.now() / 1000);
        const status = String(match.status_id);
        const kickoffTimestamp = match.kickoff_timestamp || match.match_time;

        if (status === "2" || status === "4" || status === "5") {
          let matchMinutes;
          if (status === "2") {
            matchMinutes =
              Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 1;
            if (matchMinutes > 45) {
              matchMinutes = `45+${matchMinutes - 45}`;
            }
          } else if (status === "4") {
            matchMinutes =
              Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 46;
            if (matchMinutes > 90) {
              matchMinutes = `90+${matchMinutes - 90}`;
            }
          } else if (status === "5") {
            matchMinutes =
              Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 45;
            matchMinutes = `90+${matchMinutes - 90}`;
          }

          matchTimeElement.text(`${matchMinutes}`);
        }
      }
    });
  }

  function syncWithRealTime() {
    const now = new Date();
    const nextMinute = new Date(
      now.getFullYear(),
      now.getMonth(),
      now.getDate(),
      now.getHours(),
      now.getMinutes() + 1,
      0,
      0
    );
    const timeUntilNextMinute = nextMinute - now;

    setTimeout(() => {
      updateLiveMatchesTime();
      setInterval(updateLiveMatchesTime, 60000);
    }, timeUntilNextMinute);
  }

  syncWithRealTime();

  const socket = new WebSocket("wss://sync-stage.sport-pulse.kz");

  socket.onopen = () => {};

  setInterval(() => {
    if (socket.readyState === WebSocket.OPEN) {
      socket.send(".");
    }
  }, 30000);

  socket.onmessage = (event) => {
    const data = JSON.parse(event.data);

    data.forEach((matchUpdate) => {
      if (matchUpdate.score) {
        competitionStore.updateMatchData(matchUpdate.score);
      }
      if (matchUpdate.incidents) {
        updateIncident(matchUpdate);
      }
    });
  };

  competitionStore.onDataChange(({ updatedMatch }) => {
    if (updatedMatch) {
      updateMatchInDOM(updatedMatch);
    }
  });

  function updateMatchInDOM(updatedMatch) {
    const matchElement = document.querySelector(
      `.matches__item[data-match_id="${updatedMatch.id}"]`
    );

    if (!matchElement) {
      return;
    }

    const timeElement = matchElement.querySelector(".matches__item-time");
    if (timeElement) {
      timeElement.textContent = getMatchTimeOrBreak(
        updatedMatch.id,
        updatedMatch.status_id,
        updatedMatch.kickoff_timestamp
      );
    }

    const scoreElement = matchElement.querySelector(".matches__item-score");
    if (scoreElement) {
      const [homeScore, awayScore] = scoreElement.querySelectorAll("span");
      homeScore.textContent = updatedMatch.home_scores[0];
      awayScore.textContent = updatedMatch.away_scores[0];
    }

    const homeTeamWrapper = matchElement.querySelector(
      ".matches__item-team:first-child"
    );
    const awayTeamWrapper = matchElement.querySelector(
      ".matches__item-team:last-child"
    );

    updateWinnerClass(
      homeTeamWrapper,
      updatedMatch.home_scores[0],
      updatedMatch.away_scores[0]
    );
    updateWinnerClass(
      awayTeamWrapper,
      updatedMatch.away_scores[0],
      updatedMatch.home_scores[0]
    );
  }

  function updateWinnerClass(teamElement, teamScore, opponentScore) {
    teamElement.classList.remove("winner");
    if (teamScore > opponentScore) {
      teamElement.classList.add("winner");
    }
  }

  function getMatchTimeOrBreak(id, status, kickoffTimestamp) {
    const currentTimestamp = Math.floor(Date.now() / 1000);
    let matchMinutes;
    const matchElement = document.querySelector(`[data-match_id="${id}"]`);

    switch (String(status)) {
      case "1":
        const startTime = new Date(kickoffTimestamp * 1000);
        return startTime.toLocaleTimeString([], {
          hour: "2-digit",
          minute: "2-digit",
        });

      case "2":
        matchMinutes =
          Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 1;
        matchElement.classList.add("live");
        return `${matchMinutes}`;

      case "3":
        if (matchElement.classList.contains("live")) {
          matchElement.classList.remove("live");
        }
        matchElement.classList.add("pause");
        return i18next.t("half_time");

      case "4":
        matchElement.classList.remove("pause");
        matchMinutes =
          Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 46;
        if (!matchElement.classList.contains("live")) {
          matchElement.classList.add("live");
        }
        return `${matchMinutes}`;

      case "5":
        matchMinutes =
          Math.floor((currentTimestamp - kickoffTimestamp) / 60) - 45;
        return `90+${matchMinutes}`;

      case "7":
        return i18next.t("penalty_shootout");

      case "8":
        if (matchElement) {
          setTimeout(() => {
            const parentElement = matchElement.closest(
              ".matches__ligue-content"
            );
            matchElement.remove();

            if (parentElement && parentElement.children.length === 0) {
              const leagueElement = parentElement.closest(".matches__ligue");
              if (leagueElement) {
                leagueElement.remove();
              }
            }
          }, 60000);
        }
        matchElement.classList.add("ended");
        return i18next.t("match_ended");

      case "9":
        return i18next.t("delay");

      case "10":
        return i18next.t("interrupt");

      case "11":
        return i18next.t("cut_in_half");

      case "12":
        return i18next.t("cancelled");

      case "13":
        // return i18next.t("to_be_determined");
        return "--:--";

      default:
        return i18next.t("hidden");
    }
  }

  function updateIncident(incidentData) {
    const result = incidents.addIncidents(incidentData);
    if (!result) return;
    const { id, incident } = result;

    switch (incident.type) {
      case 1:
        handleGoal(id);
        break;
      case 8:
        handlePenalty(id);
        break;
      case 27:
        handlePenaltyEnd(id);
        break;

      default:
        break;
    }
  }

  function handleGoal(id) {
    const goalElement = document.querySelector(`[data-match_id="${id}"]`);

    if (goalElement) {
      goalElement.classList.add("goal");
      goalElement.classList.remove("penalty");

      setTimeout(() => {
        goalElement.classList.remove("goal");
      }, 25000);
    } else {
    }
  }

  function handlePenalty(id) {
    const goalElement = document.querySelector(`[data-match_id="${id}"]`);

    if (goalElement) {
      goalElement.classList.add("penalty");
    } else {
    }
  }
  function handlePenaltyEnd(id) {
    const goalElement = document.querySelector(`[data-match_id="${id}"]`);

    if (goalElement) {
      goalElement.classList.remove("penalty");
    } else {
    }
  }

  socket.onclose = () => {};

  Handlebars.registerHelper("t", function (key) {
    return i18next.t(key);
  });

  Handlebars.registerHelper("isHomeScoreGreater", function (homeScores) {
    return Array.isArray(homeScores) && homeScores[2] > 0;
  });

  Handlebars.registerHelper("redCards", function (homeScores) {
    if (Array.isArray(homeScores) && homeScores[2] > 1) return homeScores[2];
    if (Array.isArray(homeScores) && homeScores[2] == 1) return "";
  });

  Handlebars.registerHelper("statusClass", function (status) {
    switch (String(status)) {
      case "2":
        return "live";
      case "3":
        return "pause";
      case "4":
        return "live";
      case "5":
        return "overtime";
      case "6":
        return "overtime-deprecated";
      case "7":
        return "live";
      case "8":
        return "ended";
      case "9":
        return "delay";
      case "10":
        return "interrupt";
      case "11":
        return "cut-in-half";
      case "12":
        return "canceled";
      case "13":
        return "determinated";
      default:
        return "";
    }
  });

  Handlebars.registerHelper("winnerClass", function (homeScores, awayScores) {
    return homeScores[0] > awayScores[0] ? "winner" : "";
  });

  Handlebars.registerHelper("displayScore", function (status, scores) {
    const statusesWithDash = ["1", "9", "10", "11", "12", "13"];

    if (statusesWithDash.includes(String(status))) {
      return "-";
    }
    return scores?.[0] ?? "-";
  });

  Handlebars.registerHelper("finishedMatchTime", function (matchId) {
    const match = competitionStore
      .getData()
      .flatMap((competition) => competition.matches)
      .find((m) => m.id === matchId);

    if (!match || String(match.status_id) !== "8") {
      return "";
    }

    const kickoffTimestamp =
      match.kickoff_timestamp && match.kickoff_timestamp !== "0"
        ? match.kickoff_timestamp
        : match.match_time;

    const kickoffDate = new Date(kickoffTimestamp * 1000);
    const today = new Date();

    const isToday =
      kickoffDate.getDate() === today.getDate() &&
      kickoffDate.getMonth() === today.getMonth() &&
      kickoffDate.getFullYear() === today.getFullYear();

    if (isToday) {
      return kickoffDate.toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
      });
    }

    return `${kickoffDate.getDate().toString().padStart(2, "0")}.${(
      kickoffDate.getMonth() + 1
    )
      .toString()
      .padStart(2, "0")} ${kickoffDate.toLocaleTimeString([], {
      hour: "2-digit",
      minute: "2-digit",
    })}`;
  });

  Handlebars.registerHelper("matchTimeOrBreak", function (matchId) {
    const match = competitionStore
      .getData()
      .flatMap((competition) => competition.matches)
      .find((m) => m.id === matchId);

    if (!match) {
      return "Матч не найден";
    }

    const currentTimestamp = Math.floor(Date.now() / 1000);
    const status = match.status_id;
    const kickoffTimestamp =
      match.kickoff_timestamp && match.kickoff_timestamp !== "0"
        ? match.kickoff_timestamp
        : match.match_time;
    let matchMinutes;

    switch (String(status)) {
      case "1":
        const startTime = new Date(kickoffTimestamp * 1000);

        return startTime.toLocaleTimeString([], {
          hour: "2-digit",
          minute: "2-digit",
        });

      case "2":
        matchMinutes =
          Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 1;
        if (matchMinutes > 45) {
          matchMinutes = `45+${matchMinutes - 45}`;
        }
        return `${matchMinutes}`;

      case "3":
        return i18next.t("half_time");

      case "4":
        matchMinutes =
          Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 46;
        if (matchMinutes > 90) {
          matchMinutes = `90+${matchMinutes - 90}`;
        }
        return `${matchMinutes}`;

      case "5":
        matchMinutes =
          Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 45;
        return `${i18next.t("overtime")}<br>${matchMinutes}`;

      case "7":
        return i18next.t("penalty_shootout");

      case "8":
        return i18next.t("match_ended");

      case "9":
        return i18next.t("delay");

      case "10":
        return i18next.t("interrupt");

      case "11":
        return i18next.t("cut_in_half");

      case "12":
        return i18next.t("cancelled");

      case "13":
        return i18next.t("to_be_determined");

      default:
        return i18next.t("hidden");
    }
  });

  function toggleHide() {
    document.querySelectorAll(".matches__ligue").forEach((parentBlock) => {
      const toggleButton = parentBlock.querySelector(".matches__ligue-hide");

      if (toggleButton) {
        toggleButton.addEventListener("click", function (event) {
          if (event.target.tagName === "A" || event.target.closest("a")) {
            return;
          }
          parentBlock.classList.toggle("hidden");

          const linkElement = toggleButton.querySelector("a");
          const spanElement = toggleButton.querySelector("span");

          if (linkElement && spanElement) {
            linkElement.classList.toggle("hidden");
            spanElement.classList.toggle("hidden");
          }
        });
      }
    });
  }
});

Handlebars.registerHelper("isPinned", function (competitionId) {
  const pinned = JSON.parse(localStorage.getItem("pinned")) || [];
  return pinned.includes(competitionId) ? "active" : "";
});

function initToggleFav() {
  const matchesLigueBlocks = document.querySelectorAll(".matches__ligue");

  matchesLigueBlocks.forEach((block) => {
    const addFavButtons = block.querySelectorAll(".fav.addfav");
    const addLigueButton = block.querySelector(".fav.addligue");

    addFavButtons.forEach((button) => {
      button.addEventListener("click", (event) => {
        event.stopPropagation();
        event.preventDefault();
        toggleFav(
          button.closest(".matches__item").dataset.match_id,
          button,
          !button.classList.contains("active")
        );
      });
    });

    if (addLigueButton) {
      addLigueButton.addEventListener("click", () => {
        const shouldActivate = !addLigueButton.classList.contains("active");
        addLigueButton.classList.toggle("active", shouldActivate);
        const matchItems = block.querySelectorAll(".matches__item");

        matchItems.forEach((item) => {
          const matchId = item.dataset.match_id;
          const button = item.querySelector(".fav.addfav");
          toggleFav(matchId, button, shouldActivate);
        });
      });
    }
  });

  // Load and apply the favorite matches from localStorage
  const favorites = getFavorites();
  const favElements = Array.from(document.querySelectorAll(".fav.addfav"));

  favElements.forEach((btn) => {
    const matchId = btn.closest(".matches__item").dataset.match_id;
    if (favorites.includes(matchId)) {
      btn.classList.add("active");
    }
    // Set .fav.addligue active if any matches are favorited
    btn
      .closest(".matches__ligue")
      .querySelector(".fav.addligue")
      .classList.toggle(
        "active",
        Array.from(
          btn.closest(".matches__ligue").querySelectorAll(".matches__item")
        ).some((item) => favorites.includes(item.dataset.match_id))
      );
  });
}

function toggleFav(matchId, button, activate) {
  const favorites = getFavorites();
  const index = favorites.indexOf(matchId);

  if (activate && index === -1) {
    favorites.push(matchId);
    button.classList.add("active");
  } else if (!activate && index !== -1) {
    favorites.splice(index, 1);
    button.classList.remove("active");
  }

  saveFavorites(favorites);

  // Update the league button active state based on current match item states
  const leagueButton = button
    .closest(".matches__ligue")
    .querySelector(".fav.addligue");
  const matchItems = button
    .closest(".matches__ligue")
    .querySelectorAll(".matches__item");

  // Set .fav.addligue active if any match is favorited
  const anyFav = Array.from(matchItems).some((item) =>
    item.querySelector(".fav.addfav").classList.contains("active")
  );
  leagueButton.classList.toggle("active", anyFav);
}

function getFavorites() {
  try {
    const favorites = JSON.parse(localStorage.getItem("favorit_fb"));
    return Array.isArray(favorites) ? favorites : [];
  } catch {
    return [];
  }
}

function saveFavorites(favorites) {
  localStorage.setItem("favorit_fb", JSON.stringify(favorites));
}
