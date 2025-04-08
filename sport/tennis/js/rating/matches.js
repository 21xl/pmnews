import Handlebars from "handlebars";
import ratingTemplate from "../../templates/rating-template.html";
import ratingSkeleton from "../../templates/rating-sceleton.html";
import searchError from "../../templates/search-error.html";
import templatefav from "../../templates/fav-team-template.html";
import templatefavempty from "../../templates/fav-team-template-empty.html";
import { DiffDOM } from "diff-dom";
const apiUrl = process.env.API_URL || "http://localhost:3277";
import favoriteTeamsStore from "../favoriteTeamsStore";
import i18next from "../i18n";

const dd = new DiffDOM();

// Компилируем Handlebars-шаблоны
const compiledRatingTemplate = Handlebars.compile(ratingTemplate);
const compiledSkeletonTemplate = Handlebars.compile(ratingSkeleton);
const compiledErrorTemplate = Handlebars.compile(searchError);

// Регистрируем хелпер для локализованных имен
Handlebars.registerHelper("getLocalizedName", function (names) {
  if (!names || typeof names !== "object") return "No name provided";
  const currentLocale = i18next.language || "en";
  const localeKey = `name_${currentLocale.toLowerCase()}`;
  return names[localeKey] || names["name_en"] || "Unknown";
});

document.addEventListener("DOMContentLoaded", () => {
  const rating = document.querySelector(".rating");
  const ratingMain = document.querySelector(".rating__main");
  const tableWrapper = ratingMain.querySelector(".table__wrapper");
  const searchInput = document.querySelector("#team"); // Поле ввода
  const type = ratingMain.dataset.type || 1; // Берем type из data-type
  let currentPage = 1;
  let totalPages = 1;
  let isLoading = false;
  let searchTerm = ""; // Текущий поисковый запрос

  favoriteTeamsStore.initialize("tn");
  const favTemplate = Handlebars.compile(templatefav);
  const favTemplateEmpty = Handlebars.compile(templatefavempty);
  favoriteTeamsStore.onDataChange((teams) => {
    let newHtml;
    if (teams.length === 0) {
      newHtml = favTemplateEmpty();
    } else {
      newHtml = teams.map((team) => favTemplate(team)).join("");
    }

    // Находим целевой элемент на странице
    const targetElement = document.querySelector("#tennis-fav-team");
    if (!targetElement) {
      console.error("Элемент #tennis-fav-team не найден на странице");
      return;
    }

    // Создаём временный контейнер с тем же id и классом
    const tempContainer = document.createElement("ul");
    tempContainer.id = "tennis-fav-team"; // Устанавливаем тот же id
    tempContainer.className = "statistics-sidebar__list";
    tempContainer.innerHTML = newHtml;

    // Применяем diff и обновляем DOM
    const differences = dd.diff(targetElement, tempContainer, {
      filterOuterDiff: (node) => !node.closest?.(".matches-tennis__item-fav"),
    });
    dd.apply(targetElement, differences);
  });

  // Проверка стилей для прокрутки
  const computedStyle = window.getComputedStyle(ratingMain);

  // Функция для показа скелетона
  const showSkeleton = () => {
    const skeletonRows = Array.from({ length: 5 }, () =>
      compiledSkeletonTemplate({})
    );
    tableWrapper.insertAdjacentHTML("beforeend", skeletonRows.join(""));
  };

  const showError = (search) => {
    ratingMain.style.display = "none";
    clearSkeleton();
    const skeletonRows = compiledErrorTemplate(search);
    rating.insertAdjacentHTML("beforeend", skeletonRows);
  };

  const clearError = () => {
    const errorRows = rating.querySelectorAll(".error-handle");
    errorRows.forEach((row) => row.remove());
    ratingMain.style.display = "block";
  };

  // Функция для очистки скелетона
  const clearSkeleton = () => {
    const skeletonRows = tableWrapper.querySelectorAll(".row.skel");
    skeletonRows.forEach((row) => row.remove());
  };

  // Функция для очистки всех строк результата
  const clearResults = () => {
    const resultRows = tableWrapper.querySelectorAll(".row.result");
    resultRows.forEach((row) => row.remove());
  };

  // Функция для загрузки данных
  const fetchRankings = async (page, name = "") => {
    if (isLoading) return;
    isLoading = true;
    let search = "";

    try {
      clearError();
      showSkeleton();
      // Формируем URL с учетом поискового запроса
      let url = `${apiUrl}/api/tennis/team-rankings?type=${type}&page=${page}`;
      if (name) {
        search = name;
        url += `&name=${encodeURIComponent(name)}`;
      }

      const response = await fetch(url);
      if (!response.ok) throw new Error(`Ошибка HTTP: ${response.status}`);

      const data = await response.json();
      if (!data.rankings.length) return showError(search);
      totalPages = data.pagination.totalPages;

      // Удаляем скелетон
      clearSkeleton();

      // Формируем строки рейтинга
      const rankingRows = data.rankings.map((item) =>
        compiledRatingTemplate(item)
      );

      // Добавляем новые строки в таблицу
      tableWrapper.insertAdjacentHTML("beforeend", rankingRows.join(""));
    } catch (error) {
      console.error("Ошибка при загрузке данных:", error);
      clearSkeleton(); // Удаляем скелетон в случае ошибки
    } finally {
      isLoading = false;
    }
  };

  // Debounce функция для задержки поиска
  const debounce = (func, delay) => {
    let timeoutId;
    return (...args) => {
      clearTimeout(timeoutId);
      timeoutId = setTimeout(() => func(...args), delay);
    };
  };

  // Обработчик ввода текста с debounce
  const handleSearch = debounce((value) => {
    searchTerm = value.trim();
    currentPage = 1; // Сбрасываем страницу на первую
    clearResults(); // Удаляем все строки результата
    fetchRankings(currentPage, searchTerm); // Выполняем поиск
  }, 2000); // 2 секунды задержки

  // Слушатель события ввода в поле #team
  searchInput.addEventListener("input", (e) => {
    handleSearch(e.target.value);
  });

  // Инициализация - загрузка первой страницы без поиска
  fetchRankings(currentPage);

  // Обработчик прокрутки
  const handleScroll = () => {
    if (isLoading || currentPage >= totalPages) return;

    const lastRow = tableWrapper.querySelector(".row:last-child");
    if (!lastRow) return;

    const rect = lastRow.getBoundingClientRect();
    const containerRect = ratingMain.getBoundingClientRect();

    // Проверяем, виден ли последний ряд в контейнере (с буфером 100px)
    const isLastRowVisible = rect.top <= containerRect.bottom + 100;

    if (isLastRowVisible && !isLoading) {
      console.log(`Прокрутка до конца, загрузка страницы ${currentPage + 1}`);
      currentPage += 1;
      fetchRankings(currentPage, searchTerm); // Учитываем текущий поисковый запрос
    }
  };

  // Добавляем слушатель прокрутки на .rating__main
  ratingMain.addEventListener("scroll", handleScroll);

  // Альтернатива: прокрутка на уровне window, если .rating__main не имеет скролла
  if (
    computedStyle.overflowY !== "auto" &&
    computedStyle.overflowY !== "scroll"
  ) {
    window.addEventListener("scroll", () => {
      const rect = tableWrapper.getBoundingClientRect();
      const windowHeight = window.innerHeight;

      if (
        rect.bottom <= windowHeight + 100 &&
        !isLoading &&
        currentPage < totalPages
      ) {
        currentPage += 1;
        fetchRankings(currentPage, searchTerm); // Учитываем текущий поисковый запрос
      }
    });
  }
});

Handlebars.registerHelper("isPinned", function (competitionId) {
  const pinned = JSON.parse(localStorage.getItem("pinnedTennis")) || [];
  return pinned.includes(competitionId) ? "active" : "";
});
