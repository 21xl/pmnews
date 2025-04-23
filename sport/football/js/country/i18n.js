import i18next from "i18next";

i18next.init({
  lng: "en",
  resources: {
    en: {
      translation: require("./locales/en.json"),
    },
  },
});

export default i18next;
