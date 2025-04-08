class Store {
  constructor() {
    this.data = null; // Теперь храним только один матч вместо массива
    this.eventTarget = new EventTarget();
  }

  setData(newData) {
    console.log(newData);
    this.data = { ...newData }; // Копируем объект матча
  }

  updateMatchData(matchUpdate) {
    const [matchId, newStatusId, serving_side, scores] = matchUpdate;

    if (!this.data || this.data.id !== matchId) {
      return; // Если матч не существует или ID не совпадает, ничего не делаем
    }

    // Создаем обновленный объект матча
    let updatedMatch = {
      ...this.data,
      status_id: newStatusId,
      serving_side: serving_side,
      scores: scores,
    };

    // Проверяем наличие scores.ft и его значения
    if (scores && scores.ft && Array.isArray(scores.ft)) {
      const [homeFt, awayFt] = scores.ft;

      // Проверяем, изменился ли счет ft по сравнению с предыдущим значением
      const previousFt =
        this.data.scores && this.data.scores.ft ? this.data.scores.ft : [0, 0];
      const ftChanged = previousFt[0] !== homeFt || previousFt[1] !== awayFt;

      if (ftChanged && homeFt === 2 && awayFt === 0) {
        updatedMatch.set_win = 1; // Home выиграл сет
      } else if (ftChanged && homeFt === 0 && awayFt === 2) {
        updatedMatch.set_win = 2; // Away выиграл сет
      } else if (!ftChanged && "set_win" in updatedMatch) {
        delete updatedMatch.set_win;
      }
    } else if ("set_win" in updatedMatch) {
      delete updatedMatch.set_win;
    }

    this.data = updatedMatch;

    const event = new CustomEvent("dataChanged", {
      detail: {
        updatedMatch,
      },
    });
    this.eventTarget.dispatchEvent(event);
  }

  updateMatchGame(matchUpdate) {
    const { id, timeline } = matchUpdate;

    if (!this.data || this.data.id !== id) {
      return;
    }

    // Находим последний сет и последний раунд
    const lastSet = timeline[timeline.length - 1];
    const lastRound = lastSet?.rounds[lastSet.rounds.length - 1];

    // Копируем текущий матч
    let updatedMatch = { ...this.data };

    if (lastRound && lastRound.score) {
      const { home, away } = lastRound.score;

      // Определяем победителя раунда по счету
      if (home > away) {
        updatedMatch.round_win = 1; // Home выиграл
      } else if (away > home) {
        updatedMatch.round_win = 2; // Away выиграл
      } else {
        delete updatedMatch.round_win;
      }
    } else if ("round_win" in updatedMatch) {
      delete updatedMatch.round_win;
    }

    this.data = updatedMatch;

    // Отправляем событие с обновленным матчем
    const event = new CustomEvent("gameChanged", {
      detail: {
        updatedMatch,
      },
    });
    this.eventTarget.dispatchEvent(event);
  }

  getMatch() {
    // Упрощаем метод, так как теперь храним только один матч
    return this.data;
  }

  getData() {
    return this.data;
  }

  onDataChange(callback) {
    this.eventTarget.addEventListener("dataChanged", (event) => {
      callback(event.detail);
    });
  }

  gameChanged(callback) {
    this.eventTarget.addEventListener("gameChanged", (event) => {
      callback(event.detail);
    });
  }
}

const matchStore = new Store();
export default matchStore;
