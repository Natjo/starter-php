/**
 * Custom Select (combobox + listbox)
 *
 * Comportements clavier :
 *  - Click / Espace sur le bouton  → ouvre et focus l'option sélectionnée
 *  - Enter sur le bouton           → ne fait rien (ne doit PAS ouvrir)
 *  - ArrowDown / ArrowUp (bouton)  → ouvre et focus first/last option
 *  - ArrowDown / ArrowUp (listbox) → navigation en boucle entre options
 *  - Home / End (listbox)          → premier / dernier
 *  - Escape (listbox)              → ferme et remet le focus sur le bouton
 */
class SelectCustom {
    constructor(root) {
        if (!(root instanceof HTMLElement)) return;
        if (root.__selectCustomInstance) return root.__selectCustomInstance;

        this.root = root;
        this.button = root.querySelector('[role="combobox"]');
        this.listbox = root.querySelector('[role="listbox"]');
        if (!this.button || !this.listbox) return;

        this.options = Array.from(this.listbox.querySelectorAll('[role="option"]'));
        // Rendre les options focusables programmatiquement.
        this.options.forEach((opt) => {
            if (!opt.hasAttribute("tabindex")) opt.setAttribute("tabindex", "-1");
        });

        this.isMulti = this.listbox.getAttribute("aria-multiselectable") === "true";
        this.placeholder = root.dataset?.placeholder ?? "";

        this._cleanups = [];

        this._onButtonClick = this._onButtonClick.bind(this);
        this._onButtonKeydown = this._onButtonKeydown.bind(this);
        this._onListboxKeydown = this._onListboxKeydown.bind(this);
        this._onOptionClick = this._onOptionClick.bind(this);
        this._onDocumentPointerDown = this._onDocumentPointerDown.bind(this);

        this.button.addEventListener("click", this._onButtonClick);
        this.button.addEventListener("keydown", this._onButtonKeydown);
        this.listbox.addEventListener("keydown", this._onListboxKeydown);
        document.addEventListener("pointerdown", this._onDocumentPointerDown);
        this._cleanups.push(() => this.button.removeEventListener("click", this._onButtonClick));
        this._cleanups.push(() => this.button.removeEventListener("keydown", this._onButtonKeydown));
        this._cleanups.push(() => this.listbox.removeEventListener("keydown", this._onListboxKeydown));
        this._cleanups.push(() => document.removeEventListener("pointerdown", this._onDocumentPointerDown));

        this.options.forEach((opt) => {
            opt.addEventListener("click", this._onOptionClick);
            this._cleanups.push(() => opt.removeEventListener("click", this._onOptionClick));
        });

        root.__selectCustomInstance = this;
    }

    _isOpen() {
        return this.button.getAttribute("aria-expanded") === "true";
    }

    _open() {
        this.button.setAttribute("aria-expanded", "true");
    }

    _close() {
        this.button.setAttribute("aria-expanded", "false");
    }

    _toggle() {
        this._isOpen() ? this._close() : this._open();
    }

    _onButtonClick() {
        const wasOpen = this._isOpen();
        this._toggle();
        if (!wasOpen) this._focusSelectedOption();
    }

    _onButtonKeydown(event) {
        const key = event.key;

        // Enter NE DOIT PAS ouvrir : on annule l'activation native du bouton.
        if (key === "Enter") {
            event.preventDefault();
            return;
        }

        if (key === "ArrowDown" || key === "ArrowUp") {
            event.preventDefault();
            if (!this._isOpen()) this._open();
            this._focusSelectedOption(key === "ArrowUp" ? "last" : "first");
            return;
        }

        if (key === "Home" || key === "End") {
            event.preventDefault();
            if (!this._isOpen()) this._open();
            const enabled = this._enabledOptions();
            const idx = key === "End" ? enabled.length - 1 : 0;
            // La listbox vient d'être affichée via CSS, on attend le prochain frame.
            requestAnimationFrame(() => this._focusOptionByIndex(idx));
            return;
        }

        // Espace : on laisse le navigateur déclencher un click natif sur le <button>,
        // c'est `_onButtonClick` qui ouvrira et déplacera le focus.
    }

    _onListboxKeydown(event) {
        const key = event.key;

        if (key === "Escape") {
            event.preventDefault();
            this._close();
            try { this.button.focus(); } catch (_) { /* noop */ }
            return;
        }

        // Multi-select : Shift+Space toggle l'option focus sans fermer la liste.
        if (this.isMulti && event.shiftKey && (key === " " || key === "Spacebar")) {
            event.preventDefault();
            const target = document.activeElement;
            if (target && this.options.includes(target)) this._toggleOption(target);
            return;
        }

        if (key === "Enter" || key === " " || key === "Spacebar") {
            event.preventDefault();
            const target = document.activeElement;
            if (target && this.options.includes(target)) this._selectOption(target);
            return;
        }

        // Tab / Shift+Tab : on valide l'option focus puis on ramène le focus sur
        // le bouton (et on annule la navigation native pour ne pas perdre le contexte).
        if (key === "Tab") {
            event.preventDefault();
            const target = document.activeElement;
            if (target && this.options.includes(target)) {
                this._selectOption(target);
            } else {
                this._close();
                try { this.button.focus(); } catch (_) { /* noop */ }
            }
            return;
        }

        if (key === "ArrowDown") {
            event.preventDefault();
            this._moveFocus(1);
            return;
        }

        if (key === "ArrowUp") {
            event.preventDefault();
            this._moveFocus(-1);
            return;
        }

        if (key === "Home") {
            event.preventDefault();
            this._focusOptionByIndex(0);
            return;
        }

        if (key === "End") {
            event.preventDefault();
            this._focusOptionByIndex(this._enabledOptions().length - 1);
        }
    }

    _onOptionClick(event) {
        const opt = event.currentTarget;
        if (!(opt instanceof HTMLElement)) return;
        if (this.isMulti) {
            this._toggleOption(opt);
            return;
        }
        this._selectOption(opt);
    }

    _onDocumentPointerDown(event) {
        if (!this._isOpen()) return;
        const t = event.target;
        if (!(t instanceof Node)) return this._close();
        if (this.button.contains(t) || this.listbox.contains(t)) return;
        this._close();
    }

    _selectOption(opt, { returnFocus = true } = {}) {
        if (!opt || !this.options.includes(opt)) return;
        if (opt.getAttribute("aria-disabled") === "true") return;

        this.options.forEach((o) => {
            if (o === opt) o.setAttribute("aria-selected", "true");
            else o.removeAttribute("aria-selected");
        });

        const label = (opt.textContent || "").trim();
        const value = opt.dataset?.value ?? label;
        this.button.textContent = label;
        this.button.value = value;
        this.button.setAttribute("value", value);
        this._setActiveDescendant(opt);

        this._close();
        if (returnFocus) {
            try { this.button.focus(); } catch (_) { /* noop */ }
        }
    }

    _setActiveDescendant(opt) {
        if (opt && opt.id) {
            this.button.setAttribute("aria-activedescendant", opt.id);
        } else {
            this.button.removeAttribute("aria-activedescendant");
        }
    }

    /** Multi-select : toggle aria-selected sans toucher aux autres et sans fermer. */
    _toggleOption(opt) {
        if (!opt || !this.options.includes(opt)) return;
        if (opt.getAttribute("aria-disabled") === "true") return;

        if (opt.getAttribute("aria-selected") === "true") {
            opt.removeAttribute("aria-selected");
        } else {
            opt.setAttribute("aria-selected", "true");
        }
        this._setActiveDescendant(opt);
        this._updateButtonFromSelection();
    }

    _updateButtonFromSelection() {
        const selected = this.options.filter((o) => o.getAttribute("aria-selected") === "true");
        const labels = selected.map((o) => (o.textContent || "").trim()).filter(Boolean);
        const values = selected.map((o) => o.dataset?.value ?? (o.textContent || "").trim()).filter(Boolean);
        const label = labels.length ? labels.join(", ") : this.placeholder;
        const value = values.join(",");
        this.button.textContent = label;
        this.button.value = value;
        this.button.setAttribute("value", value);
    }

    _enabledOptions() {
        return this.options.filter((o) => o.getAttribute("aria-disabled") !== "true");
    }

    _focusOptionByIndex(idx) {
        const list = this._enabledOptions();
        if (!list.length) return;
        const i = ((idx % list.length) + list.length) % list.length;
        const target = list[i];
        try { target.focus(); } catch (_) { /* noop */ }
        this._setActiveDescendant(target);
    }

    /** Trap : déplace le focus dans la liste avec wrap-around. */
    _moveFocus(direction) {
        const list = this._enabledOptions();
        if (!list.length) return;
        const current = document.activeElement;
        const idx = list.indexOf(current);
        const start = idx === -1 ? (direction > 0 ? -1 : 0) : idx;
        const next = ((start + direction) % list.length + list.length) % list.length;
        const target = list[next];
        try { target.focus(); } catch (_) { /* noop */ }
        this._setActiveDescendant(target);
    }

    _focusSelectedOption(fallback = "first") {
        const list = this._enabledOptions();
        if (!list.length) return;
        const selected = list.find((o) => o.getAttribute("aria-selected") === "true");
        const target = selected || (fallback === "last" ? list[list.length - 1] : list[0]);
        // La listbox passe de display:none à display:block via le CSS `:has(...)`.
        // On attend le prochain frame pour que l'élément soit réellement focusable.
        requestAnimationFrame(() => {
            try { target.focus(); } catch (_) { /* noop */ }
            this._setActiveDescendant(target);
        });
    }

    destroy() {
        this._cleanups.forEach((fn) => { try { fn(); } catch (_) { /* noop */ } });
        this._cleanups = [];
        if (this.root) delete this.root.__selectCustomInstance;
    }
}

export default el => {
    if (!(el instanceof HTMLElement)) return;
    return new SelectCustom(el);
};
