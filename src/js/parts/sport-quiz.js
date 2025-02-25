document.addEventListener("DOMContentLoaded", function () {
  const quizWrapper = document.querySelector("[data-quiz-id]");
  if (!quizWrapper) return;

  let quizId = quizWrapper.dataset.quizId;

  function getCookie(name) {
    const matches = document.cookie.match(
      new RegExp(
        "(?:^|; )" + name.replace(/([$?*|{}()[]\\\/+.^])/g, "\\$1") + "=([^;]*)"
      )
    );
    return matches ? decodeURIComponent(matches[1]) : null;
  }

  function setCookie(name, value, days = 365) {
    const date = new Date();
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie = `${name}=${value}; path=/; expires=${date.toUTCString()}`;
  }

  function updateStats(stats) {
    document.querySelectorAll(".sport-quiz__option").forEach((option) => {
      const choice = option.dataset.choice;
      const stat = stats.data[choice] || { count: 0, percentage: 0 };
      const statsElem = option
        .closest(".sport-quiz__item")
        .querySelector(".sport-quiz__stats");

      statsElem.textContent = `${stat.count} • ${stat.percentage}%`;
      option
        .closest(".sport-quiz__item")
        .style.setProperty("--progress-width", `${stat.percentage}%`);
      statsElem.classList.add("progress-width");
    });
  }

  function disableQuizOptions() {
    document
      .querySelectorAll(".sport-quiz__option")
      .forEach((option) => (option.disabled = true));
  }

  function enableQuizOptions() {
    document
      .querySelectorAll(".sport-quiz__option")
      .forEach((option) => (option.disabled = false));
  }

  function checkIfVoted() {
    fetchStats();
  }

  function saveVoteChoice(choice) {
    console.log("Сохранение выбора:", choice);
    setCookie(`voteChoice_${quizId}`, choice);
  }

  function restoreVoteSelection() {
    const savedChoice = getCookie(`voteChoice_${quizId}`);
   
    if (savedChoice) {
      const selectedOption = document.querySelector(
        `.sport-quiz__option[data-choice="${savedChoice}"]`
      );
      if (selectedOption) {
        selectedOption.checked = true;
      }
    }
  }

  function saveStats(stats) {
    setCookie(`quizStats_${quizId}`, JSON.stringify(stats));
  }

  function getStats() {
    const statsCookie = getCookie(`quizStats_${quizId}`);
    console.log(`stats cookie`, statsCookie);
    return statsCookie ? JSON.parse(statsCookie) : {};
  }

  let elements = document.querySelectorAll("input[data-choice]");
  
  function fetchStats() {
    fetch(quizAjax.ajax_url, {
      method: "POST",
      body: new URLSearchParams({
        action: "get_quiz_stats",
        quiz_id: quizId,
        elements: elements.length,
      }),
    })
      .then((response) => response.json())
      .then((response) => {
        if (response.success) {
          const currentStats = getStats();
          let updatedStats = response.data;
          if (response.data.reset) {    
            setCookie(`quizStats_${quizId}`, "", -1);  
            setCookie(`voted_${quizId}`, "", -1);
            enableQuizOptions() 
          }
          const totalVotes = Object.values(updatedStats).reduce(
            (sum, stat) => sum + stat.count,
            0
          );
          Object.keys(updatedStats).forEach((choice) => {
            if (currentStats[choice]) {
              updatedStats[choice].count += currentStats[choice].count;
            }
            updatedStats[choice].percentage = (
              (updatedStats[choice].count / totalVotes) *
              100
            ).toFixed(2);
          });

          if (getCookie(`voted_${quizId}`) === "true") {
            restoreVoteSelection();
            disableQuizOptions();
            updateStats(updatedStats);

            document
              .querySelectorAll(".sport-quiz__stats")
              .forEach((statElem) => {
                statElem.classList.add("show");
              });
          }
        } else {
          console.log(
            "Ошибка при получении статистики:",
            response.message || "Неизвестная ошибка"
          );
        }
      })
      .catch(() => {
        console.log("Ошибка сервера.");
      });
  }

  checkIfVoted();

  quizWrapper.addEventListener("change", function (event) {
    if (!event.target.classList.contains("sport-quiz__option")) return;

    if (getCookie(`voted_${quizId}`) === "true") {
      console.log("Вы уже проголосовали!");
      return;
    }

    const choice = event.target.dataset.choice;

    disableQuizOptions();
    saveVoteChoice(choice);
    setCookie(`voted_${quizId}`, "true");

    document.querySelectorAll(".sport-quiz__stats").forEach((statElem) => {
      statElem.classList.add("show");
    });

    fetch(quizAjax.ajax_url, {
      method: "POST",
      body: new URLSearchParams({
        action: "get_quiz_stats",
        quiz_id: quizId,
        elements: elements.length,
      }),
    })
      .then((response) => response.json())
      .then((response) => {
        if (response.success) {
          let updatedStats = response.data.data;
           
          updatedStats[choice].count += 1;
          
          const totalVotes = Object.values(updatedStats).reduce(
            (sum, stat) => sum + stat.count,
            0
          );

          Object.keys(updatedStats).forEach((choiceItem) => {
            updatedStats[choiceItem].percentage = (
              (updatedStats[choiceItem].count / totalVotes) *
              100
            ).toFixed(2);
          });

          let updatedStatsJson = JSON.stringify(updatedStats);

          fetch(quizAjax.ajax_url, {
            method: "POST",
            body: new URLSearchParams({
              action: "quiz_ajax",
              quiz_id: quizId,
              timestamp: Date.now(),
              updatedStats: updatedStatsJson,
            }),
          })
            .then((response) => response.json())
            .then((response) => {
              if (response.success) {
                saveStats(updatedStats);
                fetchStats();
              } else {
                console.log(
                  "Ошибка при голосовании:",
                  response.message || "Неизвестная ошибка"
                );
              }
            })
            .catch(() => {
              console.log("Ошибка сервера.");
            });
        } else {
          console.log(
            "Ошибка при получении статистики:",
            response.message || "Неизвестная ошибка"
          );
        }
      })
      .catch(() => {
        console.log("Ошибка сервера.");
      });
  });
});
