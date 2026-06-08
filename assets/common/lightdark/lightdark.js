const STORAGE_KEY = "lightdark";
const LEGACY_STORAGE_KEY = "theme";
const themeMetaSelector = 'meta[name="theme-color"]';
const themes = new Set(["light", "dark"]);
let memoryTheme = "";

const readStoredTheme = () => {
    try {
        return localStorage.getItem(STORAGE_KEY) || localStorage.getItem(LEGACY_STORAGE_KEY) || memoryTheme;
    } catch (error) {
        return memoryTheme;
    }
};

const writeStoredTheme = (theme) => {
    memoryTheme = theme;

    try {
        if (!theme) {
            localStorage.removeItem(STORAGE_KEY);
            localStorage.removeItem(LEGACY_STORAGE_KEY);
            return;
        }

        localStorage.setItem(STORAGE_KEY, theme);
        localStorage.removeItem(LEGACY_STORAGE_KEY);
    } catch (error) {}
};

const systemTheme = () => {
    return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
};

const currentTheme = () => {
    return document.documentElement.dataset.lightdark || systemTheme();
};

const applyTheme = (theme, persist = true) => {
    const root = document.documentElement;

    if (!themes.has(theme)) {
        delete root.dataset.lightdark;

        if (persist) {
            writeStoredTheme("");
        }
    } else {
        root.dataset.lightdark = theme;

        if (persist) {
            writeStoredTheme(theme);
        }
    }

    const activeTheme = currentTheme();
    const meta = document.querySelector(themeMetaSelector);

    if (meta) {
        const value = meta.dataset[activeTheme];

        if (value) {
            meta.setAttribute("content", value);
        }
    }

    document.querySelectorAll("[data-lightdark-toggle]").forEach((button) => {
        const isDark = activeTheme === "dark";
        const label = isDark ? "Mode clair" : "Mode sombre";

        button.setAttribute("aria-pressed", String(isDark));
        button.setAttribute("aria-label", label);

        const text = button.querySelector("[data-lightdark-toggle-label]");

        if (text) {
            text.textContent = label;
        }
    });
};

export default function LightDark(root = document) {
    const savedTheme = readStoredTheme();
    const media = window.matchMedia("(prefers-color-scheme: dark)");

    if (themes.has(savedTheme || "")) {
        applyTheme(savedTheme, false);
    } else {
        applyTheme("", false);
    }

    const buttons = root instanceof Element && root.matches("[data-lightdark-toggle]")
        ? [root]
        : root.querySelectorAll("[data-lightdark-toggle]");

    buttons.forEach((button) => {
        if (button.dataset.lightdarkHydrated === "true") return;

        button.addEventListener("click", () => {
            const nextTheme = currentTheme() === "dark" ? "light" : "dark";
            applyTheme(nextTheme);
        });

        button.dataset.lightdarkHydrated = "true";
    });

    if (document.documentElement.dataset.lightdarkSyncBound === "true") return;

    const syncWithSystem = () => {
        const persistedTheme = readStoredTheme();

        if (!themes.has(persistedTheme || "")) {
            applyTheme("", false);
        }
    };

    if (typeof media.addEventListener === "function") {
        media.addEventListener("change", syncWithSystem);
    } else if (typeof media.addListener === "function") {
        media.addListener(syncWithSystem);
    }

    document.documentElement.dataset.lightdarkSyncBound = "true";
}
