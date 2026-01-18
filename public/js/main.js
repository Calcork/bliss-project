import ThemeManager from "./ThemeManager/ThemeManager.js";
import "./LazyImage/LazyImage.js";

const btn_dark_toggle = document.getElementById("dark-toggle");
const btn_language_toggle = document.getElementById("language-toggle");

const tm = new ThemeManager(btn_dark_toggle);

btn_dark_toggle.addEventListener("click", () => tm.flip());
btn_language_toggle.addEventListener("change", () => changeLanguage(btn_language_toggle.value));

function changeLanguage(locale) {
    // Set cookie named "locale" to the chosen value
    // Expires in ~1 year, path=/ ensures it's visible to all routes
    document.cookie = 'locale=' + encodeURIComponent(locale) + '; path=/; max-age=' + (60 * 60 * 24 * 365);

    // Reload the page to apply the new locale
    window.location.reload();
}
