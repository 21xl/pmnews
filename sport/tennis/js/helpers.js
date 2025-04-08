import Handlebars from "handlebars";
import i18next from "./i18n";
import competitionStore from "./competition.store";

export function registerHandlebarsHelpers() {
  Handlebars.registerHelper("t", (key) => i18next.t(key));

  Handlebars.registerHelper(
    "isHomeScoreGreater",
    (homeScores) => Array.isArray(homeScores) && homeScores[2] > 0
  );

  Handlebars.registerHelper("redCards", (homeScores) => {
    if (Array.isArray(homeScores) && homeScores[2] > 1) return homeScores[2];
    if (Array.isArray(homeScores) && homeScores[2] == 1) return "";
  });

  Handlebars.registerHelper("statusClass", (status) => {
    switch (String(status)) {
      case "0":
        return "hidden";
      case "1":
        return "";
      case "3":
      case "51":
      case "52":
      case "53":
      case "54":
      case "55":
        return "live";
      case "100":
        return "ended";
      case "20":
      case "22":
      case "23":
        return "ended walkover";
      case "21":
      case "24":
      case "25":
        return "ended retired";
      case "26":
      case "27":
        return "ended defaulted";
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
        return "to-be-determined";
      default:
        return "";
    }
  });

  Handlebars.registerHelper("winnerClass", (homeScores, awayScores) =>
    homeScores > awayScores ? "winner" : ""
  );

  Handlebars.registerHelper("displayScore", (status, scores) => {
    const statusesWithDash = ["1", "9", "10", "11", "12", "13"];
    return statusesWithDash.includes(String(status))
      ? "-"
      : (scores?.[0] ?? "-");
  });

  Handlebars.registerHelper("finishedMatchTime", function (matchId) {
    const data = competitionStore.getData();

    if (!data || typeof data !== "object") return "";

    // Преобразуем объект в массив значений и ищем матч
    const match = Object.values(data)
      .flatMap((tournamentGroup) => tournamentGroup.matches || [])
      .find((m) => m.id === matchId);

    if (!match || String(match.status_id) !== "100") return "";

    const kickoffTimestamp = match.match_time;
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

  Handlebars.registerHelper("finishedMatchTimeSingle", function (match) {
    if (!match || String(match.status_id) !== "100") return "";

    const kickoffTimestamp = match.match_time;
    const kickoffDate = new Date(kickoffTimestamp * 1000);
    const today = new Date();

    const isToday =
      kickoffDate.getDate() === today.getDate() &&
      kickoffDate.getMonth() === today.getMonth() &&
      kickoffDate.getFullYear() === today.getFullYear();

    const isCurrentYear = kickoffDate.getFullYear() === today.getFullYear();

    const timeStr = kickoffDate.toLocaleTimeString([], {
      hour: "2-digit",
      minute: "2-digit",
    });

    if (isToday) {
      return timeStr; // Только время, если матч сегодня, например "20:20"
    }

    const dateStr = `${kickoffDate.getDate().toString().padStart(2, "0")}.${(
      kickoffDate.getMonth() + 1
    )
      .toString()
      .padStart(2, "0")}`;

    if (isCurrentYear) {
      return `${dateStr} ${timeStr}`; // Дата и время без года, например "19.03 20:20"
    }

    const yearStr = kickoffDate.getFullYear().toString().slice(-2); // Последние 2 цифры года
    return `${dateStr}.${yearStr} ${timeStr}`; // Дата, год и время, например "19.03.24 20:20"
  });

  Handlebars.registerHelper("getLocalizedName", (names) => {
    if (!names || typeof names !== "object") return "";
    const currentLocale = i18next.language || "en";
    const localeKey = `name_${currentLocale.toLowerCase()}`;
    return names[localeKey] || names["name_en"] || "Unknown";
  });

  Handlebars.registerHelper("matchType", (type) => {
    const typeMap = {
      1: { en: "Singles", ru: "Одиночный" },
      2: { en: "Doubles", ru: "Парный" },
      3: { en: "Mixed", ru: "Смешанный" },
    };
    const currentLanguage = i18next.language || "en";
    return typeMap[type]?.[currentLanguage] || typeMap[type]?.en || "";
  });

  Handlebars.registerHelper("matchTimeOrBreak", function (matchId) {
    console.log("matchTimeOrBreak", competitionStore.getData());
    const data = competitionStore.getData();

    // Проверяем, что данные существуют и имеют ожидаемую структуру
    if (
      !data ||
      typeof data !== "object" ||
      !data["0"] ||
      !Array.isArray(data["0"].matches)
    ) {
      return "Матч не найден"; // Или i18next.t("match_not_found")
    }

    // Получаем массив матчей из объекта с ключом "0"
    const match = data["0"].matches.find((m) => m.id === matchId);

    if (!match) {
      return "Матч не найден"; // Или i18next.t("match_not_found")
    }

    const status = String(match.status_id);
    const kickoffTimestamp = match.match_time; // Используем match_time из ваших данных
    const currentTimestamp = Math.floor(Date.now() / 1000);
    const matchElement = document.querySelector(`[data-match_id="${matchId}"]`);
    const startTime = new Date(kickoffTimestamp * 1000);
    const today = new Date();

    const isToday =
      startTime.getDate() === today.getDate() &&
      startTime.getMonth() === today.getMonth() &&
      startTime.getFullYear() === today.getFullYear();

    switch (status) {
      case "0":
        return i18next.t("hidden"); // "Скрыто"
      case "1": // NOT_STARTED
        if (isToday) {
          return startTime.toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit",
          });
        } else {
          return `${startTime.getDate().toString().padStart(2, "0")}.${(
            startTime.getMonth() + 1
          )
            .toString()
            .padStart(2, "0")} ${startTime.toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit",
          })}`;
        }
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
        matchElement?.classList.add("ended");
        return i18next.t("match_ended"); // "Завершён"
      case "20": // WALKOVER
        return i18next.t("walkover");
      case "21": // RETIRED
        return i18next.t("retired");
      case "22": // WALKOVER1
        return i18next.t("walkover1");
      case "23": // WALKOVER2
        return i18next.t("walkover2");
      case "24": // RETIRED1
        return i18next.t("retired1");
      case "25": // RETIRED2
        return i18next.t("retired2");
      case "26": // DEFAULTED1
        return i18next.t("defaulted1");
      case "27": // DEFAULTED2
        return i18next.t("defaulted2");
      case "14": // POSTPONED
        return i18next.t("postponed");
      case "15": // DELAYED
        return i18next.t("delay");
      case "16": // CANCELED
        return i18next.t("cancelled");
      case "17": // INTERRUPTED
        return i18next.t("interrupt");
      case "18": // SUSPENSION
        return i18next.t("suspension");
      case "19": // Cut in half
        return i18next.t("cut_in_half");
      case "99": // To be determined
        return i18next.t("to_be_determined");
      default:
        return i18next.t("hidden");
    }
  });

  Handlebars.registerHelper("matchTimeOrBreakSingle", function (match) {
    const status = String(match.status_id);
    const kickoffTimestamp = match.match_time; // Используем match_time из ваших данных
    const currentTimestamp = Math.floor(Date.now() / 1000);
    const matchElement = document.querySelector(
      `[data-match_id="${match.id}"]`
    );
    const startTime = new Date(kickoffTimestamp * 1000);
    const today = new Date();

    const isToday =
      startTime.getDate() === today.getDate() &&
      startTime.getMonth() === today.getMonth() &&
      startTime.getFullYear() === today.getFullYear();

    switch (status) {
      case "0":
        return i18next.t("hidden"); // "Скрыто"
      case "1": // NOT_STARTED
        if (isToday) {
          return startTime.toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit",
          });
        } else {
          return `${startTime.getDate().toString().padStart(2, "0")}.${(
            startTime.getMonth() + 1
          )
            .toString()
            .padStart(2, "0")} ${startTime.toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit",
          })}`;
        }
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
        matchElement?.classList.add("ended");
        return i18next.t("match_ended"); // "Завершён"
      case "20": // WALKOVER
        return i18next.t("walkover");
      case "21": // RETIRED
        return i18next.t("retired");
      case "22": // WALKOVER1
        return i18next.t("walkover1");
      case "23": // WALKOVER2
        return i18next.t("walkover2");
      case "24": // RETIRED1
        return i18next.t("retired1");
      case "25": // RETIRED2
        return i18next.t("retired2");
      case "26": // DEFAULTED1
        return i18next.t("defaulted1");
      case "27": // DEFAULTED2
        return i18next.t("defaulted2");
      case "14": // POSTPONED
        return i18next.t("postponed");
      case "15": // DELAYED
        return i18next.t("delay");
      case "16": // CANCELED
        return i18next.t("cancelled");
      case "17": // INTERRUPTED
        return i18next.t("interrupt");
      case "18": // SUSPENSION
        return i18next.t("suspension");
      case "19": // Cut in half
        return i18next.t("cut_in_half");
      case "99": // To be determined
        return i18next.t("to_be_determined");
      default:
        return i18next.t("hidden");
    }
  });

  Handlebars.registerHelper("getSetScores", (scores) => {
    const periods = [
      "p1",
      "p2",
      "p3",
      "p4",
      "p5",
      "x1",
      "x2",
      "x3",
      "x4",
      "x5",
    ];
    const setScores = [];
    periods.forEach((period) => {
      if (
        scores[period] &&
        scores[period].length === 2 &&
        (scores[period][0] > 0 || scores[period][1] > 0)
      ) {
        const isExtraSet = period.startsWith("x");
        setScores.push({
          homeScore: scores[period][0],
          awayScore: scores[period][1],
          isExtraSet,
          extraScore: isExtraSet ? scores[period][0] : null,
        });
      }
    });
    return setScores;
  });

  Handlebars.registerHelper("isMatchLive", (statusId) =>
    ["3", "51", "52", "53", "54", "55"].includes(String(statusId))
  );

  Handlebars.registerHelper("isMatchEnded", (statusId) =>
    ["100"].includes(String(statusId))
  );

  Handlebars.registerHelper("isBall", (serving_side, data) =>
    serving_side === data ? "ball" : ""
  );

  Handlebars.registerHelper("or", function (value, fallback) {
    return value !== undefined && value !== null && value !== ""
      ? value
      : fallback;
  });

  Handlebars.registerHelper(
    "hasSubs",
    (subs) => Array.isArray(subs) && subs.length > 0
  );
}

Handlebars.registerHelper("formatMonth", function (monthString) {
  if (
    !monthString ||
    typeof monthString !== "string" ||
    !monthString.match(/^\d{4}-\d{2}$/)
  ) {
    return monthString; // Возвращаем исходную строку, если формат неверный
  }

  // Разделяем строку на год и месяц
  const [year, month] = monthString.split("-").map(Number);
  const monthIndex = month - 1; // Месяцы в JavaScript начинаются с 0

  // Получаем текущий язык из i18next
  const currentLanguage = i18next.language || "en";

  // Список месяцев для разных языков
  const monthNames = {
    en: [
      "January",
      "February",
      "March",
      "April",
      "May",
      "June",
      "July",
      "August",
      "September",
      "October",
      "November",
      "December",
    ],
    ru: [
      "Январь",
      "Февраль",
      "Март",
      "Апрель",
      "Май",
      "Июнь",
      "Июль",
      "Август",
      "Сентябрь",
      "Октябрь",
      "Ноябрь",
      "Декабрь",
    ],
    // Добавьте другие языки по необходимости
  };

  // Получаем название месяца на текущем языке
  const months = monthNames[currentLanguage] || monthNames.en; // Fallback на английский
  const monthName = months[monthIndex] || monthString;

  // Формируем результат: "Месяц Год"
  return `${monthName} ${year}`;
});

Handlebars.registerHelper("add", function (a, b) {
  return a + b;
});

Handlebars.registerHelper("checkWinner", function (str1, str2) {
  // Сравниваем строки
  if (str1 === str2) {
    return "win"; // Если строки совпадают, возвращаем "win"
  }
  return ""; // Если не совпадают, возвращаем пустую строку
});

Handlebars.registerHelper("checkState", function (state_id) {
  return state_id === 0 || state_id === 1 ? false : true;
});

Handlebars.registerHelper("checkPenalty", function (match) {
  if (!match || typeof match !== "object") return "";
  if (match.parsed_note && match.parsed_note.PEN) return "penalty";
  return "";
});

Handlebars.registerHelper("isBall", function (serving_side, data) {
  if (serving_side !== data) return "";
  return "ball";
});

Handlebars.registerHelper("or", function (value, fallback) {
  return value !== undefined && value !== null ? value : fallback;
});

Handlebars.registerHelper("hasSubs", function (subs) {
  return Array.isArray(subs) && subs.length > 0;
});

Handlebars.registerHelper("eq", function (a, b) {
  return a === b;
});

Handlebars.registerHelper("getStatDescription", function (statType) {
  // Таблица расшифровок статистик
  const statDescriptions = {
    301: "aces",
    302: "first_serve_success",
    303: "first_serve_total",
    304: "first_serve_rate",
    305: "second_serve_success",
    306: "second_serve_total",
    307: "second_serve_rate",
    308: "break_points_success",
    309: "break_points_total",
    310: "break_points_rate",
    311: "double_faults",
    312: "unforced_err",
    313: "points_won",
    314: "first_serve_points_success",
    315: "first_serve_points_rate",
    316: "second_serve_points_success",
    317: "second_serve_points_rate",
    318: "max_points_in_a_row",
    319: "receiver_points_won",
  };

  // Возвращаем переведенное значение или код, если перевода нет
  const key = statDescriptions[statType] || statType;
  return i18next.t(key);
});

Handlebars.registerHelper("getStatType", function (statObj, side) {
  // Список кодов, которые являются процентами
  const percentageStats = {
    304: "first_serve_rate",
    307: "second_serve_rate",
    310: "break_points_rate",
    315: "first_serve_points_rate",
    317: "second_serve_points_rate",
  };

  const statType = statObj.stat_type;
  const homeValue = statObj.home_value || 0;
  const awayValue = statObj.away_value || 0;
  const total = homeValue + awayValue;

  // Если это процентный показатель
  if (percentageStats[statType]) {
    const value = side === "home" ? homeValue : awayValue;
    return `${(value * 100).toFixed(1)}%`; // Умножаем на 100 и округляем до 1 знака
  }

  // Если это числовое значение
  if (total > 0) {
    // Проверяем, что есть общее значение для вычисления процента
    const value = side === "home" ? homeValue : awayValue;
    const percentage = ((value / total) * 100).toFixed(1); // Процент от общего
    return `${percentage}%(${value}/${total})`; // Формат: "60%(3/5)"
  }

  // Если нет данных или total = 0
  return side === "home" ? `${homeValue}` : `${awayValue}`; // Возвращаем просто значение
});

Handlebars.registerHelper("isPinned", function (competitionId) {
  const pinned = JSON.parse(localStorage.getItem("pinnedTennis")) || [];
  return pinned.includes(competitionId) ? "active" : "";
});

Handlebars.registerHelper("eq", function (a, b) {
  return a === b;
});

Handlebars.registerHelper("neq", function (a, b) {
  return a !== b;
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

Handlebars.registerHelper("isNotEmpty", function (array) {
  return Array.isArray(array) && array.length > 0;
});

Handlebars.registerHelper("hasElements", function (array) {
  // Проверяем, является ли array массивом и содержит ли он элементы
  return Array.isArray(array) && array.length > 0;
});

Handlebars.registerHelper(
  "getMatchLink",
  function (matchIds, homeTeam, awayTeam) {
    if (matchIds && matchIds[0] && homeTeam && awayTeam) {
      // Явная проверка matchIds и matchIds[0]
      return `/statistics/tennis/match/${matchIds[0]}`;
    }
    return "#";
  }
);

Handlebars.registerHelper("ifLogo", function (data) {
  if (data) {
    // Явная проверка matchIds и matchIds[0]
    return data;
  }
  return "/wp-content/themes/sport-pulse/sport/src/img/world.svg";
});
