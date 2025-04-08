import Handlebars from "handlebars";
import fbTemplateSource from "../../templates/fav-template.html";
import tnTemplateSource from "../../templates/fav-tennis-template.html";
import matcheserror from "../../templates/matches-error.html";
import sceleton from "../../templates/matches-sceleton.html";
import incidents from "./incidents.store";
import competitionStore from "./competition.store";
import i18next from "./i18n";

const apiUrl = process.env.API_URL || "http://localhost:3277";

const fbTemplate = Handlebars.compile(fbTemplateSource);
const tnTemplate = Handlebars.compile(tnTemplateSource);
const mEerortemplate = Handlebars.compile(matcheserror);
const sceletontemplate = Handlebars.compile(sceleton);

jQuery(document).ready(function ($) {
  const currentLanguage = document.documentElement.lang;
  const matchesMain = $(".matches__main");

  const ITEMS_PER_PAGE = 10;
  let tab = "all";

  let renderedCount = 0;

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
      const sportType = league.sportType || "fb";
      const template = sportType === "tn" ? tnTemplate : fbTemplate;
      html += template({ ...league, sportType });
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

    toggleHide();
    initToggleFav();
  }

  function renderMatchesWithUpdatedOrder() {
    const data = competitionStore.getData();
    const excludeStatuses = ["9", "13", "0"];

    const filteredData = data.map((competition) => ({
      ...competition,
      matches: competition.matches.filter(
        (match) => !excludeStatuses.includes(String(match.status_id))
      ),
    }));

    const nonEmptyData = filteredData.filter(
      (competition) => competition.matches && competition.matches.length > 0
    );
    console.log(nonEmptyData);
    renderMatches(nonEmptyData, false, false, true);
  }

  async function onDateChange(newDateValue, tab) {
    console.log(newDateValue);
    await fetchAndRenderMatches(newDateValue, tab);

    const tabElement = document.querySelector(`.tabs__item[data-status="all"]`);
    if (tab == "ended") return;
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
        if (tab && (tab == "all" || tab == "ended")) {
          onDateChange(newDateValue, tab);
        }
      }
    }
  });

  const datePickerDisplay = document.querySelector(".date-picker__display");
  if (datePickerDisplay) {
    observer.observe(datePickerDisplay, { attributes: true });
  }

  async function fetchAndRenderMatches(date = null, tab = null) {
    try {
      const favorites = getAllFavorites();
      if (!favorites?.length) return matchesMain.html(mEerortemplate);

      const now = date ? new Date(date) : new Date();
      matchesMain.html(sceletontemplate);
      const timezoneOffset = now.getTimezoneOffset();
      const formattedDate = now.toISOString().split("T")[0];

      const requestBody = {
        date: formattedDate,
        timezone_offset: timezoneOffset,
        favorites: favorites,
      };

      const fbFavorites = favorites.filter((fav) => fav.key === "fb");
      const tnFavorites = favorites.filter((fav) => fav.key === "tn");

      const fetchPromises = [];

      if (fbFavorites.length > 0) {
        fetchPromises.push(
          fetch("/wp-json/sports/v1/favorite_matches", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(requestBody),
          }).then((res) => res.json())
        );
      }

      if (tnFavorites.length > 0) {
        fetchPromises.push(
          fetch(`${apiUrl}/api/global/favourites`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(requestBody),
          }).then((res) => res.json())
        );
      }

      const responses = await Promise.all(fetchPromises);
      const allMatches = responses.flat();

      competitionStore.setData(allMatches);
      console.log(tab);
      if (!tab) return renderMatchesWithUpdatedOrder();
      const filteredMatches = competitionStore.filterDataByStatus(tab);
      console.log("filteredMatches", filteredMatches);
      renderMatches(filteredMatches, false, false, true);
    } catch (error) {
      console.error("Ошибка при выполнении fetchAndRenderMatches:", error);
      matchesMain.html(mEerortemplate);
    }
  }

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

    if (!matchElement) return;

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
              if (leagueElement) leagueElement.remove();
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
      setTimeout(() => goalElement.classList.remove("goal"), 25000);
    }
  }

  function handlePenalty(id) {
    const goalElement = document.querySelector(`[data-match_id="${id}"]`);
    if (goalElement) goalElement.classList.add("penalty");
  }

  function handlePenaltyEnd(id) {
    const goalElement = document.querySelector(`[data-match_id="${id}"]`);
    if (goalElement) goalElement.classList.remove("penalty");
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

  Handlebars.registerHelper("statusClass", function (status, sportType) {
    sportType = sportType || "fb";
    if (sportType === "fb") {
      switch (String(status)) {
        case "2":
          return "live";
        case "3":
          return "pause";
        case "4":
          return "live";
        case "5":
          return "overtime";
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
    } else if (sportType === "tn") {
      switch (String(status)) {
        case "3":
          return "live";
        case "51":
          return "live";
        case "52":
          return "live";
        case "53":
          return "live";
        case "54":
          return "live";
        case "55":
          return "live";
        case "100":
          return "ended";
        case "14":
          return "postponed";
        case "15":
          return "delayed";
        case "16":
          return "canceled";
        case "17":
          return "interrupted";
        case "18":
          return "suspension";
        case "19":
          return "cut-in-half";
        case "99":
          return "determinated";
        default:
          return "";
      }
    }
  });

  Handlebars.registerHelper("winnerClass", function (homeScores, awayScores) {
    if (typeof homeScores === "string") {
      try {
        homeScores = JSON.parse(homeScores);
      } catch (error) {
        console.error("Ошибка парсинга homeScores:", error);
        return "";
      }
    }
    if (typeof awayScores === "string") {
      try {
        awayScores = JSON.parse(awayScores);
      } catch (error) {
        console.error("Ошибка парсинга awayScores:", error);
        return "";
      }
    }
    return homeScores?.[0] > awayScores?.[0] ? "winner" : "";
  });

  Handlebars.registerHelper("displayScore", function (status, scores) {
    const statusesWithDash = ["1", "9", "10", "11", "12", "13"];
    if (typeof scores === "string") {
      try {
        scores = JSON.parse(scores);
      } catch (error) {
        console.error("Ошибка парсинга scores:", error);
        return "-";
      }
    }
    if (statusesWithDash.includes(String(status))) return "-";
    return scores?.[0] ?? "-";
  });

  Handlebars.registerHelper("finishedMatchTime", function (matchId) {
    const match = competitionStore
      .getData()
      .flatMap((competition) => competition.matches)
      .find((m) => m.id === matchId);

    if (!match || String(match.status_id) !== "8") return "";

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
    const competitions = competitionStore.getData();
    const match = competitions
      .flatMap((competition) => competition.matches)
      .find((m) => m.id === matchId);

    if (!match) return "Матч не найден";

    const competition = competitions.find((comp) =>
      comp.matches.some((m) => m.id === matchId)
    );
    const sportType = competition?.sportType || "fb";

    const currentTimestamp = Math.floor(Date.now() / 1000);
    const status = match.status_id;
    const kickoffTimestamp =
      match.kickoff_timestamp && match.kickoff_timestamp !== "0"
        ? match.kickoff_timestamp
        : match.match_time;

    if (sportType === "fb") {
      switch (String(status)) {
        case "1":
          const startTime = new Date(kickoffTimestamp * 1000);
          return startTime.toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit",
          });
        case "2":
          let matchMinutes =
            Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 1;
          if (matchMinutes > 45) matchMinutes = `45+${matchMinutes - 45}`;
          return `${matchMinutes}`;
        case "3":
          return i18next.t("half_time");
        case "4":
          matchMinutes =
            Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 46;
          if (matchMinutes > 90) matchMinutes = `90+${matchMinutes - 90}`;
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
    } else if (sportType === "tn") {
      switch (String(status)) {
        case "1":
          const startTime = new Date(kickoffTimestamp * 1000);
          return startTime.toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit",
          });
        case "3": // IN_PROGRESS
          return i18next.t("match_start"); // "Матч начался"

        case "51": // FIRST_SET
          return `1 ${i18next.t("set")}`; // "1 Сет"

        case "52": // SECOND_SET
          return `2 ${i18next.t("set")}`; // "2 Сет"

        case "53": // THIRD_SET
          return `3 ${i18next.t("set")}`; // "3 Сет"

        case "54": // FOURTH_SET
          return `4 ${i18next.t("set")}`; // "4 Сет"

        case "55": // FIFTH_SET
          return `5 ${i18next.t("set")}`; // "5 Сет"

        case "100": // ENDED
          return i18next.t("match_ended"); // "Завершён"

        case "20": // WALKOVER
          return i18next.t("walkover"); // Добавим этот ключ

        case "21": // RETIRED
          return i18next.t("retired"); // Добавим этот ключ

        case "22": // WALKOVER1
          return i18next.t("walkover1"); // Добавим этот ключ

        case "23": // WALKOVER2
          return i18next.t("walkover2"); // Добавим этот ключ

        case "24": // RETIRED1
          return i18next.t("retired1"); // Добавим этот ключ

        case "25": // RETIRED2
          return i18next.t("retired2"); // Добавим этот ключ

        case "26": // DEFAULTED1
          return i18next.t("defaulted1"); // Добавим этот ключ

        case "27": // DEFAULTED2
          return i18next.t("defaulted2"); // Добавим этот ключ

        case "14": // POSTPONED
          return i18next.t("postponed"); // Добавим этот ключ

        case "15": // DELAYED
          return i18next.t("delay"); // "Задержка"

        case "16": // CANCELED
          return i18next.t("cancelled"); // "Отменён"

        case "17": // INTERRUPTED
          return i18next.t("interrupt"); // "Прерван"

        case "18": // SUSPENSION
          return i18next.t("suspension"); // Добавим этот ключ

        case "19": // Cut in half
          return i18next.t("cut_in_half"); // "Остановлен"

        case "99": // To be determined
          return i18next.t("to_be_determined"); // "Определяется"

        default:
          return i18next.t("hidden"); // "Скрыто" для неизвестных статусов
      }
    }
  });

  function toggleHide() {
    document.querySelectorAll(".matches__ligue").forEach((parentBlock) => {
      const toggleButton = parentBlock.querySelector(".matches__ligue-hide");
      if (toggleButton) {
        toggleButton.addEventListener("click", function (event) {
          if (event.target.tagName === "A" || event.target.closest("a")) return;
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

// function initToggleFav() {
//   const matchesLigueBlocks = document.querySelectorAll(".matches__ligue");

//   matchesLigueBlocks.forEach((block) => {
//     const addFavButtons = block.querySelectorAll(".fav.addfav");
//     const addLigueButton = block.querySelector(".fav.addligue");

//     addFavButtons.forEach((button) => {
//       button.addEventListener("click", (event) => {
//         event.stopPropagation();
//         event.preventDefault();
//         toggleFav(
//           button.closest(".matches__item").dataset.match_id,
//           button,
//           !button.classList.contains("active")
//         );
//       });
//     });

//     if (addLigueButton) {
//       addLigueButton.addEventListener("click", () => {
//         const shouldActivate = !addLigueButton.classList.contains("active");
//         addLigueButton.classList.toggle("active", shouldActivate);
//         const matchItems = block.querySelectorAll(".matches__item");
//         matchItems.forEach((item) => {
//           const matchId = item.dataset.match_id;
//           const button = item.querySelector(".fav.addfav");
//           toggleFav(matchId, button, shouldActivate);
//         });
//       });
//     }
//   });

//   const favorites = getFavorites();
//   const favElements = Array.from(document.querySelectorAll(".fav.addfav"));

//   favElements.forEach((btn) => {
//     const matchId = btn.closest(".matches__item").dataset.match_id;
//     if (favorites.includes(matchId)) btn.classList.add("active");
//     btn
//       .closest(".matches__ligue")
//       .querySelector(".fav.addligue")
//       .classList.toggle(
//         "active",
//         Array.from(
//           btn.closest(".matches__ligue").querySelectorAll(".matches__item")
//         ).some((item) => favorites.includes(item.dataset.match_id))
//       );
//   });
// }

Handlebars.registerHelper("getLocalizedName", function (names) {
  if (!names || typeof names !== "object") return "No name provided";
  const currentLocale = i18next.language || "en";
  const localeKey = `name_${currentLocale.toLowerCase()}`;
  return names[localeKey] || names["name_en"] || "Unknown";
});

Handlebars.registerHelper("isPinned", function (competitionId) {
  const pinned = JSON.parse(localStorage.getItem("pinned")) || [];
  return pinned.includes(competitionId) ? "active" : "";
});

Handlebars.registerHelper("matchType", function (type) {
  // Маппинг типов матчей
  const typeMap = {
    1: { en: "Singles", ru: "Одиночный" },
    2: { en: "Doubles", ru: "Парный" },
    3: { en: "Mixed", ru: "Смешанный" },
  };

  // Получаем текущий язык из i18next
  const currentLanguage = i18next.language || "en"; // По умолчанию "en", если язык не определён

  // Проверяем, есть ли тип в маппинге
  if (!typeMap[type]) {
    return ""; // Возвращаем заглушку, если тип не найден
  }

  // Возвращаем перевод для текущего языка
  return typeMap[type][currentLanguage] || typeMap[type].en; // Фallback на английский, если язык не поддерживается
});

Handlebars.registerHelper("isBall", function (serving_side, data) {
  if (serving_side !== data) return "";
  return "ball";
});

Handlebars.registerHelper("hasSubs", function (subs) {
  return Array.isArray(subs) && subs.length > 0;
});

Handlebars.registerHelper("isMatchLive", function (statusId) {
  const liveStatuses = ["3", "51", "52", "53", "54", "55"]; // IN_PROGRESS и сеты
  return liveStatuses.includes(String(statusId));
});

Handlebars.registerHelper("isMatchEnded", function (statusId) {
  const liveStatuses = ["100"]; // IN_PROGRESS и сеты
  return liveStatuses.includes(String(statusId));
});

Handlebars.registerHelper("extra", function (key, index, scores) {
  // Маппинг соответствий ключей
  const keyMap = {
    p1: "x1",
    p2: "x2",
    p3: "x3",
    p4: "x4",
    p5: "x5",
  };

  // Если ключ есть в маппинге
  if (keyMap[key]) {
    const targetArray = scores[keyMap[key]];
    // Проверяем, существует ли массив и есть ли в нем элементы
    if (Array.isArray(targetArray) && targetArray.length > index) {
      return targetArray[index];
    }
    return ""; // Возвращаем пустую строку, если данных нет
  }
  return ""; // Возвращаем пустую строку, если данных нет
});

Handlebars.registerHelper("hasP", function (key) {
  return (
    typeof key === "string" &&
    key.toLowerCase().includes("p") &&
    key.toLowerCase() !== "pt"
  );
});

Handlebars.registerHelper("or", function (value, fallback) {
  return value !== undefined && value !== null && value !== ""
    ? value
    : fallback;
});

function getAllFavorites() {
  const prefix = "favorit_";
  const result = [];
  for (let i = 0, len = localStorage.length; i < len; i++) {
    const key = localStorage.key(i);
    if (key.startsWith(prefix)) {
      try {
        const data = JSON.parse(localStorage.getItem(key));
        if (Array.isArray(data) && data.length > 0) {
          result.push({
            key: key.slice(prefix.length),
            data: data,
          });
        }
      } catch {}
    }
  }
  return result.length > 0 ? result : null;
}

// Функция для определения ключа избранного по matchId
function getFavoriteKeyForMatch(matchId) {
  const match = competitionStore
    .getData()
    .flatMap((competition) => competition.matches)
    .find((m) => m.id === matchId);

  if (!match) return "favorit_fb"; // Значение по умолчанию

  const competition = competitionStore
    .getData()
    .find((comp) => comp.matches.some((m) => m.id === matchId));

  const sportType = competition?.sportType || "fb";
  return `favorit_${sportType}`;
}

// Модифицированная функция получения избранного для конкретного ключа
function getFavorites(key) {
  try {
    const favorites = JSON.parse(localStorage.getItem(key));
    return Array.isArray(favorites) ? favorites : [];
  } catch {
    return [];
  }
}

// Модифицированная функция сохранения избранного для конкретного ключа
function saveFavorites(key, favorites) {
  localStorage.setItem(key, JSON.stringify(favorites));
}

function initToggleFav() {
  // Обрабатываем оба типа лиг
  const leagueBlocks = document.querySelectorAll(
    ".matches__ligue, .matches-tennis__ligue"
  );

  leagueBlocks.forEach((block) => {
    const addFavButtons = block.querySelectorAll(".fav.addfav");
    const addLigueButton = block.querySelector(".fav.addligue");

    // Обрабатываем кнопки "добавить в избранное" для элементов матчей
    addFavButtons.forEach((button) => {
      button.addEventListener("click", (event) => {
        event.stopPropagation();
        event.preventDefault();
        const item = button.closest(".matches__item, .matches-tennis__item");
        if (item) {
          toggleFav(
            item.dataset.match_id,
            button,
            !button.classList.contains("active")
          );
        }
      });
    });

    // Обрабатываем кнопку "добавить лигу в избранное"
    if (addLigueButton) {
      addLigueButton.addEventListener("click", () => {
        const shouldActivate = !addLigueButton.classList.contains("active");
        addLigueButton.classList.toggle("active", shouldActivate);
        const matchItems = block.querySelectorAll(
          ".matches__item, .matches-tennis__item"
        );
        matchItems.forEach((item) => {
          const matchId = item.dataset.match_id;
          const button = item.querySelector(".fav.addfav");
          toggleFav(matchId, button, shouldActivate);
        });
      });
    }
  });

  // Получаем все избранные элементы
  const allFavorites = getAllFavorites();
  const favoriteMatchIds = new Set();

  if (allFavorites) {
    allFavorites.forEach((fav) => {
      fav.data.forEach((matchId) => favoriteMatchIds.add(matchId));
    });
  }

  // Устанавливаем начальное состояние кнопок
  const favElements = Array.from(document.querySelectorAll(".fav.addfav"));

  favElements.forEach((btn) => {
    const item = btn.closest(".matches__item, .matches-tennis__item");
    if (item) {
      const matchId = item.dataset.match_id;
      if (favoriteMatchIds.has(matchId)) {
        btn.classList.add("active");
      }

      const leagueBlock = btn.closest(
        ".matches__ligue, .matches-tennis__ligue"
      );
      if (leagueBlock) {
        const leagueFavButton = leagueBlock.querySelector(".fav.addligue");
        if (leagueFavButton) {
          const anyMatchFavorited = Array.from(
            leagueBlock.querySelectorAll(
              ".matches__item, .matches-tennis__item"
            )
          ).some((item) => favoriteMatchIds.has(item.dataset.match_id));
          leagueFavButton.classList.toggle("active", anyMatchFavorited);
        }
      }
    }
  });
}

function toggleFav(matchId, button, activate) {
  const favoriteKey = getFavoriteKeyForMatch(matchId);
  const favorites = getFavorites(favoriteKey);
  const index = favorites.indexOf(matchId);

  if (activate && index === -1) {
    favorites.push(matchId);
    button.classList.add("active");
  } else if (!activate && index !== -1) {
    favorites.splice(index, 1);
    button.classList.remove("active");

    const leagueBlock = button.closest(
      ".matches__ligue, .matches-tennis__ligue"
    );
    const matchItem = button.closest(".matches__item, .matches-tennis__item");

    const matchItems = leagueBlock.querySelectorAll(
      ".matches__item, .matches-tennis__item"
    );
    const allFavorites = getAllFavorites();
    const favoriteMatchIds = new Set();
    if (allFavorites) {
      allFavorites.forEach((fav) => {
        fav.data.forEach((id) => favoriteMatchIds.add(id));
      });
    }

    const anyFav = Array.from(matchItems).some((item) =>
      favoriteMatchIds.has(item.dataset.match_id)
    );

    const leagueButton = leagueBlock.querySelector(".fav.addligue");
    if (leagueButton) {
      leagueButton.classList.toggle("active", anyFav);
    }

    competitionStore.deleteMatchById(matchItem.dataset.match_id);
    matchItem.remove();

    const remainingMatches = leagueBlock.querySelectorAll(
      ".matches__item, .matches-tennis__item"
    );
    if (remainingMatches.length === 0) {
      leagueBlock.remove();
    }
  }

  saveFavorites(favoriteKey, favorites);

  if (
    document.querySelectorAll(".matches__item, .matches-tennis__item")
      .length === 0
  ) {
    const matchesMain = document.querySelector(".matches__main"); // Убрано [0], так как предполагается один элемент
    if (matchesMain) {
      matchesMain.innerHTML = mEerortemplate();
    }
  }
}
