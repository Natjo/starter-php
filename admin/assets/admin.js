document.documentElement.classList.add("admin-ready");

const WEB_VITALS_SESSION_KEY = "admin-web-vitals-collecting";

const showAdminToast = (message, type = "success") => {
    if (!message) {
        return;
    }

    const toast = document.createElement("div");
    toast.className = `admin-toast is-${type}`;
    toast.setAttribute("data-admin-toast", "");
    toast.setAttribute("role", "status");
    toast.setAttribute("aria-live", "polite");
    const paragraph = document.createElement("p");
    paragraph.textContent = message;
    toast.appendChild(paragraph);
    document.body.appendChild(toast);
    initAdminToasts();
};

const initAdminToasts = () => {
    const toasts = document.querySelectorAll("[data-admin-toast]");
    toasts.forEach((toast) => {
        if (!(toast instanceof HTMLElement)) {
            return;
        }

        window.setTimeout(() => {
            toast.classList.add("is-hidden");

            window.setTimeout(() => {
                toast.remove();
            }, 220);
        }, 2600);
    });
};

const syncWebVitalsToggle = () => {
    const button = document.querySelector("[data-web-vitals-toggle]");
    if (!(button instanceof HTMLButtonElement)) {
        return;
    }

    let isCollecting = false;

    try {
        isCollecting = window.sessionStorage.getItem(WEB_VITALS_SESSION_KEY) === "1";
    } catch (error) {}

    button.value = isCollecting ? "stop" : "start";
    button.textContent = isCollecting
        ? button.dataset.stopLabel || "Stop"
        : button.dataset.startLabel || "Start";
    button.classList.toggle("is-secondary", isCollecting);
};

const setAdminButtonLoading = (button) => {
    if (!(button instanceof HTMLElement) || button.dataset.loadingActive === "true") {
        return;
    }

    button.dataset.loadingActive = "true";
    button.classList.add("is-loading");
    button.setAttribute("aria-busy", "true");

    if (button instanceof HTMLButtonElement) {
        button.disabled = true;
    }

    if (button instanceof HTMLAnchorElement) {
        button.setAttribute("aria-disabled", "true");
        button.style.pointerEvents = "none";
    }
};

const clearAdminButtonLoading = (button) => {
    if (!(button instanceof HTMLElement)) {
        return;
    }

    delete button.dataset.loadingActive;
    button.classList.remove("is-loading");
    button.removeAttribute("aria-busy");

    if (button instanceof HTMLButtonElement) {
        button.disabled = false;
    }

    if (button instanceof HTMLAnchorElement) {
        button.removeAttribute("aria-disabled");
        button.style.pointerEvents = "";
    }
};

const activateAdminTab = (trigger) => {
    if (!(trigger instanceof HTMLButtonElement)) {
        return;
    }

    const container = trigger.closest("[data-admin-tabs]");
    if (!(container instanceof HTMLElement)) {
        return;
    }

    const triggers = container.querySelectorAll("[data-admin-tab-trigger]");
    const targetId = trigger.getAttribute("aria-controls");

    triggers.forEach((item) => {
        if (!(item instanceof HTMLButtonElement)) {
            return;
        }

        const isActive = item === trigger;
        item.classList.toggle("is-active", isActive);
        item.setAttribute("aria-selected", isActive ? "true" : "false");

        const panelId = item.getAttribute("aria-controls");
        if (!panelId) {
            return;
        }

        const panel = container.querySelector(`#${CSS.escape(panelId)}`);
        if (!(panel instanceof HTMLElement)) {
            return;
        }

        panel.classList.toggle("is-active", isActive);
        panel.hidden = !isActive;
    });
};

document.addEventListener("submit", (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    if (form.hasAttribute("data-performance-run-form")) {
        event.preventDefault();

        const submitter = event.submitter;
        if (!(submitter instanceof HTMLButtonElement)) {
            return;
        }

        setAdminButtonLoading(submitter);

        const formData = new FormData(form);
        const url = new URL(form.action, window.location.href);
        url.searchParams.set("ajax", "1");

        window.fetch(url.toString(), {
            method: "POST",
            body: formData,
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
        })
            .then(async (response) => {
                if (!response.ok) {
                    throw new Error("network");
                }

                return response.json();
            })
            .then((payload) => {
                const localRoot = document.querySelector("[data-performance-local-root]");
                if (localRoot instanceof HTMLElement && typeof payload.localHtml === "string") {
                    localRoot.innerHTML = payload.localHtml;
                }

                const toast = payload.toast && typeof payload.toast === "object" ? payload.toast : null;
                if (toast && typeof toast.message === "string") {
                    showAdminToast(toast.message, typeof toast.type === "string" ? toast.type : "success");
                }
            })
            .catch(() => {
                form.submit();
            })
            .finally(() => {
                clearAdminButtonLoading(submitter);
            });

        return;
    }

    const submitter = event.submitter;
    const isWebVitalsForm = form.getAttribute("action") === "./toggle-web-vitals.php";

    if (isWebVitalsForm && submitter instanceof HTMLButtonElement) {
        try {
            if (submitter.value === "start") {
                window.sessionStorage.setItem(WEB_VITALS_SESSION_KEY, "1");
            } else {
                window.sessionStorage.removeItem(WEB_VITALS_SESSION_KEY);
            }
        } catch (error) {}
    }

    if (submitter instanceof HTMLElement && submitter.classList.contains("admin-button")) {
        setAdminButtonLoading(submitter);
    }

    const formButtons = form.querySelectorAll(".admin-button");
    formButtons.forEach((button) => {
        if (!(button instanceof HTMLButtonElement) || button === submitter) {
            return;
        }

        button.disabled = true;
    });
});

document.addEventListener("click", (event) => {
    const target = event.target;
    if (!(target instanceof Element)) {
        return;
    }

    const tabTrigger = target.closest("[data-admin-tab-trigger]");
    if (tabTrigger instanceof HTMLButtonElement) {
        activateAdminTab(tabTrigger);
        return;
    }

    const button = target.closest(".admin-button");
    if (!(button instanceof HTMLAnchorElement)) {
        return;
    }

    const href = button.getAttribute("href");
    if (!href || href.startsWith("#") || button.target === "_blank" || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
    }

    setAdminButtonLoading(button);
});

syncWebVitalsToggle();
initAdminToasts();
