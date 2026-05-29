const CLOSE_ANIM_MS = 160;

const _lastTriggerByDialog = new WeakMap();

const FOCUSABLE_SELECTOR = [
    'a[href]:not([disabled])',
    'button:not([disabled])',
    'textarea:not([disabled])',
    'input:not([disabled])',
    'select:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
].join(", ");

const isDialogOpen = (dialog) => {
    if (!dialog) return false;
    try {
        if (typeof dialog.open === "boolean") return dialog.open;
    } catch (_) { /* noop */ }
    return dialog.hasAttribute("open");
};

const getFocusableElements = (root) => {
    if (!root) return [];
    return Array.from(root.querySelectorAll(FOCUSABLE_SELECTOR)).filter((el) => {
        if (!(el instanceof HTMLElement)) return false;
        if (el.closest("[inert]")) return false;
        try {
            if (typeof el.checkVisibility === "function" && !el.checkVisibility({ checkOpacity: true, checkVisibilityCSS: true })) {
                return false;
            }
        } catch (_) { /* noop */ }
        return true;
    });
};

const openDialog = (dialog) => {
    if (!dialog) return;
    if (isDialogOpen(dialog)) return;
    try {
        if (typeof dialog.showModal === "function") dialog.showModal();
        else dialog.setAttribute("open", "");
    } catch (_) {
        try { dialog.setAttribute("open", ""); } catch (_) { /* noop */ }
    }
};

const closeDialog = (dialog, returnValue = "close") => {
    if (!dialog) return;
    try {
        if (typeof dialog.close === "function") dialog.close(returnValue);
        else dialog.removeAttribute("open");
    } catch (_) {
        try { dialog.removeAttribute("open"); } catch (_) { /* noop */ }
    }
};

const requestCloseDialog = (dialog, returnValue = "close") => {
    if (!dialog) return;

    if (dialog.__dialogCloseTimer) return;
    try { dialog.setAttribute("data-dialog-closing", "1"); } catch (_) { /* noop */ }

    dialog.__dialogCloseTimer = window.setTimeout(() => {
        dialog.__dialogCloseTimer = null;
        closeDialog(dialog, returnValue);
    }, CLOSE_ANIM_MS);
};

const focusDialogDefault = (dialog) => {
    if (!dialog) return;
    const preferred =
        dialog.querySelector("[autofocus]") ||
        dialog.querySelector("[data-dialog-focus]") ||
        dialog.querySelector("[data-dialog-close]") ||
        dialog.querySelector("button, [href], input, select, textarea, [tabindex]:not([tabindex='-1'])");
    if (preferred && typeof preferred.focus === "function") {
        try { preferred.focus(); } catch (_) { /* noop */ }
    }
};

class Dialog {
    /**
     * @param {HTMLDialogElement} dialog
     */
    constructor(dialog) {
        if (!(dialog instanceof HTMLDialogElement)) return;

        this.dialog = dialog;
        this.onopen = null;
        this.onclose = null;

        this._cleanups = [];
        this._trapCleanups = [];
        this._onTrapKeydown = null;
        this._onTrapFocusIn = null;

        this._bindDialogEvents();
        this._bindTriggers();
        this._bindCloseButtons();
    }

    destroy() {
        this._disableFocusTrap();
        this._cleanups.forEach((fn) => {
            try { fn(); } catch (_) { /* noop */ }
        });
        this._cleanups = [];
    }

    _disableFocusTrap() {
        this._trapCleanups.forEach((fn) => {
            try { fn(); } catch (_) { /* noop */ }
        });
        this._trapCleanups = [];
        this._onTrapKeydown = null;
        this._onTrapFocusIn = null;
    }

    _enableFocusTrap() {
        const dialog = this.dialog;
        this._disableFocusTrap();

        this._onTrapKeydown = (event) => {
            if (event.key !== "Tab" || !isDialogOpen(dialog)) return;

            const focusable = getFocusableElements(dialog);
            if (!focusable.length) return;

            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            const active = document.activeElement;
            const activeInDialog = active === dialog || dialog.contains(active);

            if (event.shiftKey) {
                if (!activeInDialog || active === first) {
                    try { event.preventDefault(); } catch (_) { /* noop */ }
                    try { last.focus(); } catch (_) { /* noop */ }
                }
            } else if (!activeInDialog || active === last) {
                try { event.preventDefault(); } catch (_) { /* noop */ }
                try { first.focus(); } catch (_) { /* noop */ }
            }
        };

        this._onTrapFocusIn = (event) => {
            if (!isDialogOpen(dialog)) return;
            const t = event.target;
            if (!t || !(t instanceof Node)) return;
            if (dialog.contains(t)) return;

            const focusable = getFocusableElements(dialog);
            if (!focusable.length) return;
            window.requestAnimationFrame(() => {
                if (!isDialogOpen(dialog)) return;
                const cur = document.activeElement;
                if (cur && dialog.contains(cur)) return;
                try { focusable[0].focus(); } catch (_) { /* noop */ }
            });
        };

        dialog.addEventListener("keydown", this._onTrapKeydown);
        document.addEventListener("focusin", this._onTrapFocusIn, true);

        this._trapCleanups.push(() => dialog.removeEventListener("keydown", this._onTrapKeydown));
        this._trapCleanups.push(() => document.removeEventListener("focusin", this._onTrapFocusIn, true));
    }

    _bindDialogEvents() {
        const dialog = this.dialog;

        const onClick = (event) => {
            // Click on the backdrop closes the dialog.
            if (event.target !== dialog) return;
            requestCloseDialog(dialog, "backdrop");
        };

        const onCancel = (event) => {
            // Prevent native immediate close so we can animate out.
            try { event.preventDefault(); } catch (_) { /* noop */ }
            requestCloseDialog(dialog, "cancel");
        };

        const onClose = () => {
            this._disableFocusTrap();

            if (dialog.__dialogCloseTimer) {
                try { window.clearTimeout(dialog.__dialogCloseTimer); } catch (_) { /* noop */ }
                dialog.__dialogCloseTimer = null;
            }
            try { dialog.removeAttribute("data-dialog-closing"); } catch (_) { /* noop */ }

            const trigger = _lastTriggerByDialog.get(dialog);
            if (trigger) {
                try { trigger.setAttribute("aria-expanded", "false"); } catch (_) { /* noop */ }
                try { trigger.focus(); } catch (_) { /* noop */ }
            }
            _lastTriggerByDialog.delete(dialog);

            if (typeof this.onclose === "function") {
                try { this.onclose({ dialog, trigger: trigger || null, returnValue: dialog.returnValue || "" }); } catch (_) { /* noop */ }
            }
        };

        dialog.addEventListener("click", onClick);
        dialog.addEventListener("cancel", onCancel);
        dialog.addEventListener("close", onClose);

        this._cleanups.push(() => dialog.removeEventListener("click", onClick));
        this._cleanups.push(() => dialog.removeEventListener("cancel", onCancel));
        this._cleanups.push(() => dialog.removeEventListener("close", onClose));
    }

    _bindTriggers() {
        const dialog = this.dialog;
        const id = dialog.id;
        if (!id) return;

        const selector = `[data-dialog-id="${CSS.escape(id)}"]`;
        const triggers = Array.from(document.querySelectorAll(selector));
        triggers.forEach((btn) => {
            const onOpen = (event) => {
                if (btn instanceof HTMLAnchorElement) {
                    try { event?.preventDefault(); } catch (_) { /* noop */ }
                }
                if (btn instanceof HTMLButtonElement && btn.disabled) return;
                if (btn.getAttribute("aria-disabled") === "true") return;
                if (isDialogOpen(dialog)) return;

                _lastTriggerByDialog.set(dialog, btn);
                try { btn.setAttribute("aria-expanded", "true"); } catch (_) { /* noop */ }
                openDialog(dialog);
                this._enableFocusTrap();
                requestAnimationFrame(() => focusDialogDefault(dialog));

                if (typeof this.onopen === "function") {
                    try { this.onopen({ dialog, trigger: btn }); } catch (_) { /* noop */ }
                }
            };
            btn.addEventListener("click", onOpen);
            this._cleanups.push(() => btn.removeEventListener("click", onOpen));
        });
    }

    _bindCloseButtons() {
        const dialog = this.dialog;
        const closeButtons = Array.from(dialog.querySelectorAll("[data-dialog-close]"));
        closeButtons.forEach((btn) => {
            const onCloseBtn = (event) => {
                // Prevent native immediate close (form method="dialog") to animate out.
                try { event.preventDefault(); } catch (_) { /* noop */ }
                const returnValue = (btn instanceof HTMLButtonElement && btn.value) ? btn.value : "close";
                requestCloseDialog(dialog, returnValue);
            };
            btn.addEventListener("click", onCloseBtn);
            this._cleanups.push(() => btn.removeEventListener("click", onCloseBtn));
        });
    }
}

export default el => {
    if (!(el instanceof HTMLDialogElement)) return;
    if (el.__dialogInstance) return;

    el.__dialogInstance = new Dialog(el);
};
