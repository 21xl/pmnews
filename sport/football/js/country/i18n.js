import i18next from "i18next";

i18next.init({
  lng: document.documentElement.lang,
  resources: {
    ru: {
      translation: require("./locales/ru.json"),
    },
  },
});

export default i18next;
