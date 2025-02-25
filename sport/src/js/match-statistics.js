document.addEventListener("DOMContentLoaded", () => {
  document
    .querySelectorAll(".match__statistics-segment")
    .forEach((progressBar) => {
      if (progressBar.hasAttribute("data-value")) {
        const value = progressBar.getAttribute("data-value");
        if (!isNaN(value) && value >= 0 && value <= 100) {
          const inner = progressBar.querySelector(
            ".match__statistics-progress"
          );
          if (inner) inner.style.width = `${value}%`;
        }
      }
    });
});

document.addEventListener("DOMContentLoaded", () => {
  const pinned = JSON.parse(localStorage.getItem("pinned")) || [];
  const pins = document.querySelectorAll(".pin:not(.active)");
  pins.forEach((pin) => {
    const competitionId = pin.getAttribute("data-competition_id");
    if (pinned.includes(competitionId)) {
      pin.classList.add("active");
    }
  });
});
