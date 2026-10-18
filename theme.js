
(function () {
    const storageKey = "programmingAssistantTheme";
    const root = document.documentElement;

    function getSavedTheme() {
        try {
            return localStorage.getItem(storageKey) === "dark"
                ? "dark"
                : "light";
        } catch (error) {
            return "light";
        }
    }

    function applyTheme(theme) {
        root.setAttribute("data-theme", theme);

        const button = document.getElementById("themeToggle");

        if (button) {
            const darkMode = theme === "dark";

            button.textContent = darkMode
                ? "Light Mode"
                : "Dark Mode";

            button.setAttribute(
                "aria-label",
                darkMode ? "Switch to light mode" : "Switch to dark mode"
            );

            button.setAttribute("aria-pressed", String(darkMode));
        }
    }

    function saveTheme(theme) {
        try {
            localStorage.setItem(storageKey, theme);
        } catch (error) {
            // The theme still works for the current page if storage is unavailable.
        }
    }

    applyTheme(getSavedTheme());

    document.addEventListener("DOMContentLoaded", function () {
        applyTheme(getSavedTheme());

        const button = document.getElementById("themeToggle");

        if (button) {
            button.addEventListener("click", function () {
                const currentTheme =
                    root.getAttribute("data-theme") || "light";

                const newTheme =
                    currentTheme === "dark" ? "light" : "dark";

                saveTheme(newTheme);
                applyTheme(newTheme);
            });
        }
    });
})();

