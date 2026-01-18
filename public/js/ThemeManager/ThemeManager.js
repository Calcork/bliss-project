export default class ThemeManager {
  #root = document.documentElement;
  #storageKey = "theme";
  #btn;

  constructor(btn) {
    this.#btn = btn;
    this.#apply(); // apply state on init
  }

  reset() {
    this.#clearCookie();
    localStorage.removeItem(this.#storageKey);
    this.#apply();
  }

  isDarkApplied() {
    return this.#root.classList.contains("dark");
  }

  flip() {
    if (this.isDarkApplied()) {
      this.#setCookie("light");
      localStorage.setItem(this.#storageKey, "light");
    } else {
      this.#setCookie("dark");
      localStorage.setItem(this.#storageKey, "dark");
    }
    this.#apply();
  }

  applyDark() {
    this.#setCookie("dark");
    localStorage.setItem(this.#storageKey, "dark");
    this.#apply();
  }

  applyBright() {
    this.#setCookie("light");
    localStorage.setItem(this.#storageKey, "light");
    this.#apply();
  }

  // ---------------------------
  // Private helpers
  // ---------------------------

  #apply() {
    const cookiePref = this.#getCookie();
    const storagePref = localStorage.getItem(this.#storageKey);

    const isDark =
      cookiePref === "dark" ||
      (!cookiePref && storagePref === "dark") ||
      (!cookiePref && !storagePref && this.#isDarkSystemDefault());

    this.#root.classList.toggle("dark", isDark);

    // ✅ update button emoji every time
    if (this.#btn) {
      if (isDark) {
        this.#btn.textContent = this.#btn.getAttribute("darkmode-value");
      } else {
        this.#btn.textContent = this.#btn.getAttribute("brightmode-value");
      }
    }
  }

  #isDarkSystemDefault() {
    return window.matchMedia("(prefers-color-scheme: dark)").matches;
  }

  #getCookie() {
    const match = document.cookie.match(
      new RegExp("(^| )" + this.#storageKey + "=([^;]+)")
    );
    return match ? match[2] : null;
  }

  #setCookie(value) {
    const expires = new Date();
    expires.setDate(expires.getDate() + 30); // 30-day cookie
    document.cookie = `${this.#storageKey}=${value}; expires=${expires.toUTCString()}; path=/`;
  }

  #clearCookie() {
    document.cookie = `${this.#storageKey}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/`;
  }
}