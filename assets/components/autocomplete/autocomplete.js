/**
 * Autocomplete accessible (combobox + listbox)
 *
 * Adapté de https://access42.net/concevoir-un-composant-d-auto-completion-accessible/
 *
 * Comportements clavier (zone de saisie) :
 *  - ArrowDown                          → ouvre le menu, focus 1ère option
 *  - Tab                                → ferme le menu (laisse passer la nav)
 *  - Echap / Up / Left / Right / Espace / Entrée / Shift → ignorés (pas de rebuild)
 *  - Tout autre caractère               → filtre les options et affiche le menu
 *
 * Comportements clavier (listbox) :
 *  - ArrowDown                          → option suivante (ne dépasse pas la dernière)
 *  - ArrowUp (sur première option)      → revient au champ
 *  - ArrowUp (autre)                    → option précédente
 *  - Entrée / Espace                    → sélectionne et revient au champ
 *  - Echap                              → ferme et revient au champ
 *  - Tab                                → ferme (laisse passer la nav)
 *  - Tout autre caractère               → renvoie au champ
 *
 * Click extérieur                       → ferme le menu
 */
export default class Autocomplete {
    constructor(root) {
        if (!(root instanceof HTMLElement)) return;
        if (root.__autocompleteInstance) return root.__autocompleteInstance;

        this.root = root;
        this.container = root.querySelector(".autocomplete");
        this.select = root.querySelector("select");
        this.textBox = root.querySelector('[role="combobox"]');
        this.menu = root.querySelector('[role="listbox"]');
        this.status = root.querySelector('[role="status"]');
        if (!this.select || !this.textBox || !this.menu) return;

        this.activeOptionId = null;

        this._onTextBoxKeyUp = this._onTextBoxKeyUp.bind(this);
        this._onTextBoxKeyDown = this._onTextBoxKeyDown.bind(this);
        this._onMenuKeyDown = this._onMenuKeyDown.bind(this);
        this._onMenuClick = this._onMenuClick.bind(this);
        this._onDocumentPointerDown = this._onDocumentPointerDown.bind(this);

        this.textBox.addEventListener("keyup", this._onTextBoxKeyUp);
        this.textBox.addEventListener("keydown", this._onTextBoxKeyDown);
        this.menu.addEventListener("keydown", this._onMenuKeyDown);
        this.menu.addEventListener("click", this._onMenuClick);
        document.addEventListener("pointerdown", this._onDocumentPointerDown);

        this._cleanups = [
            () => this.textBox.removeEventListener("keyup", this._onTextBoxKeyUp),
            () => this.textBox.removeEventListener("keydown", this._onTextBoxKeyDown),
            () => this.menu.removeEventListener("keydown", this._onMenuKeyDown),
            () => this.menu.removeEventListener("click", this._onMenuClick),
            () => document.removeEventListener("pointerdown", this._onDocumentPointerDown),
        ];

        this._hideMenu();
        root.__autocompleteInstance = this;
    }

    // ---------------------------------------------------------------------
    // Handlers — zone de saisie
    // ---------------------------------------------------------------------

    _onTextBoxKeyUp(event) {
        switch (event.key) {
            case "Escape":
            case "ArrowUp":
            case "ArrowLeft":
            case "ArrowRight":
            case "Enter":
            case " ":
            case "Spacebar":
            case "Tab":
            case "Shift":
                return;
            case "ArrowDown":
                this._onTextBoxDownPressed(event);
                return;
            default:
                this._onTextBoxType();
        }
    }

    _onTextBoxKeyDown(event) {
        if (event.key === "Tab") this._hideMenu();
    }

    _onTextBoxDownPressed(event) {
        event.preventDefault();
        const value = this.textBox.value.trim();

        let options;
        if (value.length === 0 || this._isExactMatch(value)) {
            options = this._getAllOptions();
        } else {
            options = this._getOptions(value);
            if (!options.length) return;
        }

        this._buildMenu(options);
        this._showMenu();
        const first = this._firstOption();
        if (first) this._highlightOption(first);
    }

    _onTextBoxType() {
        const value = this.textBox.value.trim();
        if (value.length > 0) {
            const options = this._getOptions(value.toLowerCase());
            this._buildMenu(options);
            this._showMenu();
            this._updateStatus(options.length);
        } else {
            this._hideMenu();
            this._updateStatus(0);
        }
        this._updateSelectBox();
    }

    // ---------------------------------------------------------------------
    // Handlers — menu / listbox
    // ---------------------------------------------------------------------

    _onMenuKeyDown(event) {
        const key = event.key;
        const current = document.activeElement;

        if (key === "ArrowUp") {
            event.preventDefault();
            const first = this._firstOption();
            if (current === first) {
                this._focusTextBox();
            } else if (current && current.previousElementSibling) {
                this._highlightOption(current.previousElementSibling);
            }
            return;
        }

        if (key === "ArrowDown") {
            event.preventDefault();
            if (current && current.nextElementSibling) {
                this._highlightOption(current.nextElementSibling);
            }
            return;
        }

        if (key === "Enter" || key === " " || key === "Spacebar") {
            event.preventDefault();
            if (current && current.matches('[role="option"]')) this._selectOption(current);
            return;
        }

        if (key === "Escape") {
            event.preventDefault();
            this._hideMenu();
            this._focusTextBox();
            return;
        }

        if (key === "Tab") {
            this._hideMenu();
            return;
        }

        // Toute autre touche : on rend la main au champ pour continuer la saisie.
        this._focusTextBox();
    }

    _onMenuClick(event) {
        const opt = event.target.closest('[role="option"]');
        if (opt && this.menu.contains(opt)) this._selectOption(opt);
    }

    _onDocumentPointerDown(event) {
        const t = event.target;
        if (!(t instanceof Node)) return this._hideMenu();
        if (this.root.contains(t)) return;
        this._hideMenu();
    }

    // ---------------------------------------------------------------------
    // Surbrillance / sélection
    // ---------------------------------------------------------------------

    _highlightOption(option) {
        if (this.activeOptionId) {
            const previous = this.menu.querySelector("#" + CSS.escape(this.activeOptionId));
            if (previous) previous.setAttribute("aria-selected", "false");
        }
        option.setAttribute("aria-selected", "true");
        this._scrollIntoView(option);
        this.activeOptionId = option.id;
        try { option.focus(); } catch (_) { /* noop */ }
    }

    _selectOption(option) {
        const value = option.getAttribute("data-option-value") || "";
        const text = (option.textContent || "").trim();
        this._setValue(text, value);
        this._hideMenu();
        this._focusTextBox();
    }

    _setValue(text, value) {
        this.textBox.value = text;
        if (this.select) this.select.value = value;
    }

    _focusTextBox() {
        try { this.textBox.focus(); } catch (_) { /* noop */ }
    }

    // ---------------------------------------------------------------------
    // Construction / affichage du menu
    // ---------------------------------------------------------------------

    _buildMenu(options) {
        const fragment = document.createDocumentFragment();
        const baseId = this.menu.id || "autocomplete-menu";
        options.forEach((opt, i) => {
            const li = document.createElement("li");
            li.setAttribute("role", "option");
            li.setAttribute("tabindex", "-1");
            li.setAttribute("aria-selected", "false");
            li.setAttribute("data-option-value", opt.value);
            li.id = baseId + "__" + i;
            li.textContent = opt.text;
            fragment.appendChild(li);
        });
        this.menu.replaceChildren(fragment);
        this.activeOptionId = null;
    }

    _showMenu() {
        this.menu.classList.remove("hidden");
        this.textBox.setAttribute("aria-expanded", "true");
    }

    _hideMenu() {
        this.menu.classList.add("hidden");
        this.textBox.setAttribute("aria-expanded", "false");
        this.activeOptionId = null;
    }

    _updateStatus(count) {
        if (!this.status) return;
        this.status.textContent = count > 0
            ? count + " résultat" + (count > 1 ? "s" : "") + " disponible" + (count > 1 ? "s" : "") + "."
            : "Aucun résultat disponible.";
    }

    _updateSelectBox() {
        if (!this.select) return;
        const value = this.textBox.value.trim().toLowerCase();
        const match = Array.from(this.select.options).find((o) => o.text.trim().toLowerCase() === value);
        this.select.value = match ? match.value : "";
    }

    // ---------------------------------------------------------------------
    // Filtrage des options sources (<select>)
    // ---------------------------------------------------------------------

    _getAllOptions() {
        return Array.from(this.select.options)
            .filter((o) => o.value.trim().length > 0)
            .map((o) => ({ text: o.text, value: o.value, alt: o.dataset?.alt || "" }));
    }

    _getOptions(value) {
        const needle = value.toLowerCase();
        return this._getAllOptions().filter(({ text, alt }) => {
            const t = text.toLowerCase();
            const a = (alt || "").toLowerCase();
            return t.indexOf(needle) > -1 || (a && a.indexOf(needle) > -1);
        });
    }

    _isExactMatch(value) {
        const needle = value.toLowerCase();
        return this._getAllOptions().some(({ text }) => text.toLowerCase() === needle);
    }

    _firstOption() {
        return this.menu.querySelector('[role="option"]');
    }

    _scrollIntoView(option) {
        const parent = option.parentElement;
        if (!parent) return;
        const optTop = option.offsetTop;
        const optBottom = optTop + option.offsetHeight;
        const viewTop = parent.scrollTop;
        const viewBottom = viewTop + parent.clientHeight;
        if (optTop < viewTop) parent.scrollTop = optTop;
        else if (optBottom > viewBottom) parent.scrollTop = optBottom - parent.clientHeight;
    }

    destroy() {
        (this._cleanups || []).forEach((fn) => { try { fn(); } catch (_) { /* noop */ } });
        this._cleanups = [];
        if (this.root) delete this.root.__autocompleteInstance;
    }
}
