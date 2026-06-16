document.documentElement.classList.add("admin-ready");

const WEB_VITALS_SESSION_KEY = "admin-web-vitals-collecting";
const ADMIN_NAV_GROUPS_KEY = "admin-nav-groups";

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

        const panel = document.getElementById(panelId);
        if (!(panel instanceof HTMLElement)) {
            return;
        }

        panel.classList.toggle("is-active", isActive);
        panel.hidden = !isActive;
    });
};

const initAdminNavGroups = () => {
    const groups = document.querySelectorAll("[data-admin-nav-group]");
    if (!groups.length) {
        return;
    }

    let savedState = {};
    try {
        const raw = window.localStorage.getItem(ADMIN_NAV_GROUPS_KEY);
        const parsed = raw ? JSON.parse(raw) : {};
        savedState = parsed && typeof parsed === "object" ? parsed : {};
    } catch (error) {}

    const persist = () => {
        try {
            window.localStorage.setItem(ADMIN_NAV_GROUPS_KEY, JSON.stringify(savedState));
        } catch (error) {}
    };

    const applyState = (toggle, panel, isOpen) => {
        toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
        panel.hidden = !isOpen;
    };

    groups.forEach((group) => {
        if (!(group instanceof HTMLElement)) {
            return;
        }

        if (group.dataset.navReady === "true") {
            return;
        }

        const toggle = group.querySelector("[data-admin-nav-toggle]");
        if (!(toggle instanceof HTMLButtonElement)) {
            return;
        }

        const panelId = toggle.getAttribute("aria-controls");
        if (!panelId) {
            return;
        }

        const panel = document.getElementById(panelId);
        if (!(panel instanceof HTMLElement)) {
            return;
        }

        const storageKey = panelId;
        const defaultOpen = group.dataset.defaultOpen === "true";
        const isOpen = typeof savedState[storageKey] === "boolean" ? savedState[storageKey] : defaultOpen;

        applyState(toggle, panel, isOpen);
        savedState[storageKey] = isOpen;

        toggle.addEventListener("click", () => {
            const nextOpen = toggle.getAttribute("aria-expanded") !== "true";
            applyState(toggle, panel, nextOpen);
            savedState[storageKey] = nextOpen;
            persist();
        });
    });

    persist();
};

document.addEventListener("submit", (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    if (form.hasAttribute("data-webp-form")) {
        event.preventDefault();

        const submitter = event.submitter;
        if (!(submitter instanceof HTMLButtonElement)) {
            return;
        }

        setAdminButtonLoading(submitter);

        form.webPConvert?.("download")
            .then(({ blob, filename }) => {
                const url = URL.createObjectURL(blob);
                const link = document.createElement("a");
                link.href = url;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(url);
                showAdminToast("Image WebP generee.");
            })
            .catch((error) => {
                showAdminToast(error.message || "La conversion WebP a echoue.", "error");
            })
            .finally(() => {
                clearAdminButtonLoading(submitter);
            });

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
initAdminNavGroups();

const initWebpTool = () => {
    const form = document.querySelector("[data-webp-form]");
    const input = document.querySelector("[data-webp-input]");
    const dropzone = document.querySelector("[data-webp-preview]");
    const image = document.querySelector("[data-webp-preview-image]");
    const status = document.querySelector("[data-webp-preview-status]");
    const meta = document.querySelector("[data-webp-file-meta]");
    const metaName = document.querySelector("[data-webp-meta-name]");
    const metaSource = document.querySelector("[data-webp-meta-source]");
    const metaWebp = document.querySelector("[data-webp-meta-webp]");
    const metaGain = document.querySelector("[data-webp-meta-gain]");
    const quality = document.querySelector("[data-webp-quality]");
    const qualityOutput = document.querySelector("[data-webp-quality-output]");
    const filter = document.querySelector("[data-webp-filter]");
    const filterDescription = document.querySelector("[data-webp-filter-description]");
    const sharpenRadius = document.querySelector("[data-webp-sharpen-radius]");
    const sharpenRadiusOutput = document.querySelector("[data-webp-sharpen-radius-output]");
    const sharpenSigma = document.querySelector("[data-webp-sharpen-sigma]");
    const sharpenSigmaOutput = document.querySelector("[data-webp-sharpen-sigma-output]");

    if (
        !(form instanceof HTMLFormElement)
        || !(input instanceof HTMLInputElement)
        || !(dropzone instanceof HTMLElement)
        || !(image instanceof HTMLImageElement)
    ) {
        return;
    }

    let previewUrl = "";
    let previewTimer = 0;
    let previewRequest = null;
    let previewSequence = 0;
    let activeFile = null;

    input.value = "";
    image.removeAttribute("src");
    image.hidden = true;
    if (meta instanceof HTMLElement) {
        meta.hidden = true;
    }

    const revokePreview = () => {
        if (!previewUrl) return;
        URL.revokeObjectURL(previewUrl);
        previewUrl = "";
    };

    const setStatus = (message = "") => {
        if (!(status instanceof HTMLElement)) return;
        status.textContent = message;
        status.hidden = message === "";
        dropzone.classList.toggle("is-processing", message !== "");
    };

    const selectedFile = () => activeFile;

    const validateFile = (file) => {
        if (!file.type.match(/^image\/(jpeg|png|webp)$/)) {
            showAdminToast("Formats acceptes : JPEG, PNG et WebP.", "error");
            return false;
        }
        if (file.size > 25 * 1024 * 1024) {
            showAdminToast("L image doit peser moins de 25 Mo.", "error");
            return false;
        }
        return true;
    };

    const showOriginal = (file) => {
        revokePreview();
        previewUrl = URL.createObjectURL(file);
        image.src = previewUrl;
        image.hidden = false;
        dropzone.classList.add("has-image");

        if (meta instanceof HTMLElement) {
            if (metaName instanceof HTMLElement) metaName.textContent = file.name;
            if (metaSource instanceof HTMLElement) metaSource.textContent = `${(file.size / 1024).toFixed(1)} Ko`;
            if (metaWebp instanceof HTMLElement) metaWebp.textContent = "En attente";
            if (metaGain instanceof HTMLElement) metaGain.textContent = "En attente";
            meta.hidden = false;
        }
    };

    const convert = async (mode = "preview") => {
        const file = selectedFile();
        if (!file) {
            throw new Error("Selectionne une image valide.");
        }

        if (mode === "preview") {
            previewRequest?.abort();
            previewRequest = new AbortController();
            setStatus("Conversion...");
        }

        const data = new FormData(form);
        data.set("mode", mode);
        const response = await window.fetch(form.action || window.location.href, {
            method: "POST",
            body: data,
            signal: mode === "preview" ? previewRequest.signal : undefined,
            headers: {
                Accept: "application/json, image/webp",
                "X-Requested-With": "XMLHttpRequest",
            },
        });

        if (!response.ok) {
            const payload = await response.json().catch(() => null);
            throw new Error(payload?.error || "La conversion WebP a echoue.");
        }

        const disposition = response.headers.get("Content-Disposition") || "";
        const filenameMatch = disposition.match(/filename="([^"]+)"/);

        return {
            blob: await response.blob(),
            filename: filenameMatch?.[1] || "image.webp",
        };
    };

    const refreshPreview = () => {
        const file = selectedFile();
        if (!file) return;

        window.clearTimeout(previewTimer);
        const sequence = ++previewSequence;
        previewTimer = window.setTimeout(() => {
            convert("preview")
                .then(({ blob }) => {
                    if (sequence !== previewSequence) return;
                    revokePreview();
                    previewUrl = URL.createObjectURL(blob);
                    image.src = previewUrl;

                    if (meta instanceof HTMLElement) {
                        const gain = Math.max(0, (1 - blob.size / file.size) * 100);
                        if (metaWebp instanceof HTMLElement) metaWebp.textContent = `${(blob.size / 1024).toFixed(1)} Ko`;
                        if (metaGain instanceof HTMLElement) metaGain.textContent = `${gain.toFixed(1)} %`;
                    }
                })
                .catch((error) => {
                    if (sequence === previewSequence && error.name !== "AbortError") {
                        showAdminToast(error.message || "La previsualisation a echoue.", "error");
                    }
                })
                .finally(() => {
                    if (sequence === previewSequence) {
                        setStatus("");
                    }
                });
        }, 180);
    };

    const loadFile = (file) => {
        if (!validateFile(file)) return;

        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;
        activeFile = file;
        showOriginal(file);
        refreshPreview();
    };

    form.webPConvert = convert;

    input.addEventListener("change", () => {
        const file = input.files?.[0] || null;
        if (!file || !validateFile(file)) return;
        activeFile = file;
        showOriginal(file);
        refreshPreview();
    });

    dropzone.addEventListener("click", () => input.click());
    dropzone.addEventListener("keydown", (event) => {
        if (event.key !== "Enter" && event.key !== " ") return;
        event.preventDefault();
        input.click();
    });

    ["dragenter", "dragover"].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.add("is-dragging");
        });
    });

    ["dragleave", "drop"].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.remove("is-dragging");
        });
    });

    dropzone.addEventListener("drop", (event) => {
        const file = event.dataTransfer?.files?.[0];
        if (file) loadFile(file);
    });

    dropzone.addEventListener("pointermove", (event) => {
        if (event.pointerType !== "mouse" || image.hidden || dropzone.classList.contains("is-dragging")) {
            return;
        }

        const rect = image.getBoundingClientRect();
        if (rect.width <= 0 || rect.height <= 0) return;

        const x = Math.max(0, Math.min(100, ((event.clientX - rect.left) / rect.width) * 100));
        const y = Math.max(0, Math.min(100, ((event.clientY - rect.top) / rect.height) * 100));
        image.style.setProperty("--zoom-x", `${x}%`);
        image.style.setProperty("--zoom-y", `${y}%`);
        dropzone.classList.add("is-zoomed");
    });

    dropzone.addEventListener("pointerleave", () => {
        dropzone.classList.remove("is-zoomed");
        image.style.removeProperty("--zoom-x");
        image.style.removeProperty("--zoom-y");
    });

    if (quality instanceof HTMLInputElement && qualityOutput instanceof HTMLOutputElement) {
        quality.addEventListener("input", () => {
            qualityOutput.value = quality.value;
            refreshPreview();
        });
    }

    if (sharpenRadius instanceof HTMLInputElement && sharpenRadiusOutput instanceof HTMLOutputElement) {
        sharpenRadius.addEventListener("input", () => {
            sharpenRadiusOutput.value = sharpenRadius.value;
            refreshPreview();
        });
    }

    if (sharpenSigma instanceof HTMLInputElement && sharpenSigmaOutput instanceof HTMLOutputElement) {
        sharpenSigma.addEventListener("input", () => {
            sharpenSigmaOutput.value = sharpenSigma.value;
            refreshPreview();
        });
    }

    filter?.addEventListener("change", () => {
        if (filter instanceof HTMLSelectElement && filterDescription instanceof HTMLElement) {
            const option = filter.selectedOptions[0];
            const keyword = document.createElement("strong");
            const description = document.createElement("span");
            keyword.textContent = option?.dataset.keyword || "";
            description.textContent = option?.dataset.description || "";
            filterDescription.replaceChildren(keyword, description);
        }
        refreshPreview();
    });
};

initWebpTool();

const initIconsTool = () => {
    const search = document.querySelector("[data-icons-search]");
    const cards = [...document.querySelectorAll("[data-icon-card]")];
    const groups = [...document.querySelectorAll("[data-icon-group]")];
    const empty = document.querySelector("[data-icons-empty]");

    if (search instanceof HTMLInputElement && cards.length) {
        search.addEventListener("input", () => {
            const query = search.value.trim().toLowerCase();
            let visibleCount = 0;

            cards.forEach((card) => {
                if (!(card instanceof HTMLElement)) return;
                const visible = (card.dataset.iconId || "").includes(query);
                card.hidden = !visible;
                visibleCount += visible ? 1 : 0;
            });

            groups.forEach((group) => {
                if (!(group instanceof HTMLElement)) return;
                group.hidden = !group.querySelector("[data-icon-card]:not([hidden])");
            });

            if (empty instanceof HTMLElement) {
                empty.hidden = visibleCount !== 0;
            }
        });
    }

    document.querySelectorAll("[data-copy-icon]").forEach((button) => {
        button.addEventListener("click", async () => {
            if (!(button instanceof HTMLButtonElement)) return;
            const iconId = button.dataset.copyIcon || "";
            const php = `<?php component::icon("${iconId}", 16, 16); ?>`;

            try {
                await navigator.clipboard.writeText(php);
                const initialLabel = button.textContent;
                button.textContent = "Copie";
                window.setTimeout(() => {
                    button.textContent = initialLabel;
                }, 1400);
            } catch (error) {
                window.prompt("Copiez le composant PHP :", php);
            }
        });
    });

    document.querySelectorAll("[data-icon-delete-form]").forEach((form) => {
        form.addEventListener("submit", (event) => {
            if (!(form instanceof HTMLFormElement)) return;
            const iconName = form.dataset.iconName || "";
            if (!window.confirm(`Supprimer definitivement l icone "${iconName}" ?`)) {
                event.preventDefault();
            }
        });
    });
};

initIconsTool();
