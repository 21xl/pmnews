const apiUrl = process.env.API_URL || "http://localhost:3277";

class FavoriteTeamsStore {
  constructor() {
    this.teams = []; // Хранилище данных о командах
    this.eventTarget = new EventTarget();
    this.sportType = null; // Тип спорта, если указан
  }

  // Инициализация стора с опциональным типом спорта
  async initialize(sportType = null) {
    this.sportType = sportType;
    await this.loadInitialFavorites();
  }

  // Загрузка начальных данных из localStorage и запрос на сервер
  async loadInitialFavorites() {
    const storedFavorites = localStorage.getItem("favorite_teams");
    const favoriteIds = storedFavorites ? JSON.parse(storedFavorites) : {};

    // Если sportType указан, загружаем только команды этого вида спорта
    if (this.sportType) {
      const teamIds = favoriteIds[this.sportType] || [];
      if (teamIds.length > 0) {
        await this.fetchTeams({ [this.sportType]: teamIds });
      }
    } else {
      // Если sportType не указан, загружаем команды всех видов спорта
      const requestData = Object.keys(favoriteIds).reduce((acc, sport) => {
        if (favoriteIds[sport].length > 0) {
          acc[sport] = favoriteIds[sport];
        }
        return acc;
      }, {});
      if (Object.keys(requestData).length > 0) {
        await this.fetchTeams(requestData);
      }
    }
  }

  // Запрос данных команд с сервера
  async fetchTeams(requestData) {
    try {
      const response = await fetch(`${apiUrl}/api/global/favteam`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(requestData),
      });

      if (!response.ok) {
        throw new Error(
          `Server error: ${response.status} - ${response.statusText}`
        );
      }

      const { data } = await response.json();
      if (data && data.length > 0) {
        this.teams = data;
        this.notifyDataChange();
      } else {
        this.teams = [];
        this.notifyDataChange();
      }
    } catch (error) {
      console.error("Error fetching teams:", error);
    }
  }

  // Обновление данных в сторе и уведомление подписчиков
  setData(newTeams) {
    this.teams = newTeams;
    this.notifyDataChange();
  }

  // Уведомление о изменении данных
  notifyDataChange() {
    const event = new CustomEvent("teamsChanged", { detail: this.teams });
    this.eventTarget.dispatchEvent(event);
  }

  // Получение текущих данных
  getData() {
    return this.teams;
  }

  // Подписка на изменения данных
  onDataChange(callback) {
    this.eventTarget.addEventListener("teamsChanged", (event) => {
      callback(event.detail);
    });
  }

  // Добавление/удаление команды в избранное
  async toggleFavoriteTeam(teamId, sportType) {
    if (!sportType) {
      throw new Error("Sport type is required for toggleFavoriteTeam");
    }

    const storedFavorites = localStorage.getItem("favorite_teams");
    let favoriteIds = storedFavorites ? JSON.parse(storedFavorites) : {};

    // Инициализируем массив для данного вида спорта, если его нет
    if (!favoriteIds[sportType]) {
      favoriteIds[sportType] = [];
    }

    const isFavorite = favoriteIds[sportType].includes(teamId);

    if (isFavorite) {
      // Удаление из избранного
      favoriteIds[sportType] = favoriteIds[sportType].filter(
        (id) => id !== teamId
      );
    } else {
      // Добавление в избранное
      favoriteIds[sportType].push(teamId);
    }

    // Удаляем пустые виды спорта из объекта
    Object.keys(favoriteIds).forEach((key) => {
      if (favoriteIds[key].length === 0) {
        delete favoriteIds[key];
      }
    });

    // Обновление localStorage
    localStorage.setItem("favorite_teams", JSON.stringify(favoriteIds));

    // Формируем запрос для загрузки всех команд с учётом sportType
    if (this.sportType) {
      const teamIds = favoriteIds[this.sportType] || [];
      if (teamIds.length > 0) {
        await this.fetchTeams({ [this.sportType]: teamIds });
      } else {
        this.setData([]); // Если нет команд для текущего sportType, очищаем массив
      }
    } else {
      const requestData = Object.keys(favoriteIds).reduce((acc, sport) => {
        if (favoriteIds[sport].length > 0) {
          acc[sport] = favoriteIds[sport];
        }
        return acc;
      }, {});
      if (Object.keys(requestData).length > 0) {
        await this.fetchTeams(requestData);
      } else {
        this.setData([]); // Если нет команд вообще, очищаем массив
      }
    }
  }
}

const favoriteTeamsStore = new FavoriteTeamsStore();
export default favoriteTeamsStore;
