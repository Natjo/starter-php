let fallbackErrorId = 0;
const DEFAULT_GROUP_REQUIRED_MESSAGE = "Veuillez sélectionner une option.";

const getInvalidMsgEl = (field) => {
    const group = field.closest('[role="group"]');
    const fieldContainer = field.closest(".field");
    const container = group || fieldContainer || field.parentNode;

    return container?.querySelector(".invalid-msg") || null;
};

const ensureErrorId = (field) => {
    const describedby = (field.getAttribute("aria-describedby") || "").trim();
    if (describedby) {
        return describedby.split(/\s+/).filter(Boolean)[0];
    }

    fallbackErrorId += 1;
    const errorId = field.id ? `${field.id}-error` : `field-error-${fallbackErrorId}`;
    field.setAttribute("aria-describedby", errorId);

    return errorId;
};

const getDefaultErrorMessage = (messageEl) => {
    return (messageEl?.dataset?.default || "").trim();
};

const setErrorMessage = (messageEl, message = null) => {
    if (!messageEl) return;

    const defaultMessage = getDefaultErrorMessage(messageEl);
    const nextMessage = message === null ? defaultMessage : message;
    messageEl.textContent = nextMessage;
    messageEl.hidden = message === null || nextMessage === "";
};

const getMandatoryMessage = (field) => {
    const group = field.closest('[role="group"]');

    return field.dataset.mandatory || group?.dataset?.mandatory || "";
};

const isGroupChecked = (group) => {
    const inputs = group.querySelectorAll('input[type="checkbox"], input[type="radio"]');

    return Array.from(inputs).some((input) => input.checked && !input.disabled);
};

const getGroupAnchor = (inputs) => {
    return Array.from(inputs).find((input) => input.required) || inputs[0] || null;
};

const getCustomFieldInvalidMsg = (field) => {
    return field.closest(".field")?.querySelector(".invalid-msg") || field.querySelector(".invalid-msg") || null;
};

const getCustomFieldValue = (field) => {
    return Array.from(field.querySelectorAll("[data-field-value]"))
        .map((input) => input.value.trim())
        .filter(Boolean)
        .join(",");
};

const FormValidate = function (form, onSend) {
    if (!(form instanceof HTMLFormElement)) return null;
    if (form.__formValidateInstance) return form.__formValidateInstance;

    const api = {};
    const send = typeof onSend === "function" ? onSend : () => HTMLFormElement.prototype.submit.call(form);
    const listeners = [];
    const fields = Array.from(form.querySelectorAll(":required"));
    const requiredGroups = Array.from(form.querySelectorAll('[role="group"][data-required]'));
    const requiredCustomFields = Array.from(form.querySelectorAll("[data-field-required]"));
    let validity = true;
    let init = true;
    let firstInvalidControl = null;

    form.noValidate = true;

    const addListener = (target, type, listener, options) => {
        target.addEventListener(type, listener, options);
        listeners.push([target, type, listener, options]);
    };

    const resetGroup = (group) => {
        const inputs = group.querySelectorAll('input[type="checkbox"], input[type="radio"]');
        inputs.forEach((input) => {
            input.removeAttribute("aria-invalid");
            input.setCustomValidity("");
        });
        setErrorMessage(group.querySelector(".invalid-msg"));
    };

    const getCustomFieldControl = (field) => {
        return field.querySelector("[data-field-control]") || field;
    };

    const resetCustomField = (field) => {
        getCustomFieldControl(field).removeAttribute("aria-invalid");
        setErrorMessage(getCustomFieldInvalidMsg(field));
    };

    const validateCustomField = (field) => {
        const valid = getCustomFieldValue(field) !== "";
        const control = getCustomFieldControl(field);
        const invalidMsg = getCustomFieldInvalidMsg(field);

        if (!valid) {
            firstInvalidControl ||= control;
            control.setAttribute("aria-invalid", "true");
            const message = (field.dataset.mandatory || getDefaultErrorMessage(invalidMsg)).trim()
                || "Ce champ est obligatoire.";
            setErrorMessage(invalidMsg, message);
            return false;
        }

        control.removeAttribute("aria-invalid");
        setErrorMessage(invalidMsg);

        return true;
    };

    const validateGroup = (group) => {
        const invalidMsg = group.querySelector(".invalid-msg");
        const inputs = group.querySelectorAll('input[type="checkbox"], input[type="radio"]');
        const valid = isGroupChecked(group);

        if (!valid) {
            inputs.forEach((input) => input.setAttribute("aria-invalid", "true"));
            const anchor = getGroupAnchor(inputs);
            firstInvalidControl ||= anchor;
            const msg = (group.dataset.mandatory || getDefaultErrorMessage(invalidMsg)).trim();
            const message = msg || anchor?.validationMessage || DEFAULT_GROUP_REQUIRED_MESSAGE;
            if (anchor) anchor.setCustomValidity(message);
            setErrorMessage(invalidMsg, message);
            return false;
        }

        inputs.forEach((input) => {
            input.removeAttribute("aria-invalid");
            input.setCustomValidity("");
        });
        setErrorMessage(invalidMsg);

        return true;
    };

    api.reset = () => {
        init = true;
        for (const field of fields) {
            field.removeAttribute("aria-invalid");
            field.setCustomValidity("");
            setErrorMessage(getInvalidMsgEl(field));
        }
        requiredGroups.forEach(resetGroup);
        requiredCustomFields.forEach(resetCustomField);
    };

    const validate = () => {
        if (init) return true;
        validity = true;
        firstInvalidControl = null;

        for (const field of fields) {
            const group = field.closest('[role="group"]');
            if (group?.hasAttribute("data-required")) continue;

            field.setCustomValidity("");
            const dataTypeMismatch = field.dataset.typemismatch;
            const dataPatternMismatch = field.dataset.patternmismatch;
            const dataMandatory = getMandatoryMessage(field);
            const { typeMismatch, tooShort, tooLong, stepMismatch, patternMismatch, valueMissing } = field.validity;
            let invalidMsg = getInvalidMsgEl(field);

            if (!invalidMsg) {
                invalidMsg = document.createElement("div");
                invalidMsg.className = "invalid-msg";
                invalidMsg.id = ensureErrorId(field);
                if (group) {
                    group.insertAdjacentElement("beforeend", invalidMsg);
                } else {
                    field.insertAdjacentElement("afterend", invalidMsg);
                }
            }

            if (!field.checkValidity()) {
                firstInvalidControl ||= field;
                field.setAttribute("aria-invalid", "true");
                let msg = "";
                if ((typeMismatch || stepMismatch || tooShort || tooLong) && dataTypeMismatch) {
                    msg = dataTypeMismatch;
                }
                if (patternMismatch && dataPatternMismatch) {
                    msg = dataPatternMismatch;
                }
                if (valueMissing && dataMandatory) {
                    msg = dataMandatory;
                }
                field.setCustomValidity(msg);
                setErrorMessage(invalidMsg, field.validationMessage);
                validity = false;
            } else {
                field.removeAttribute("aria-invalid");
                field.setCustomValidity("");
                setErrorMessage(invalidMsg);
            }
        }

        for (const group of requiredGroups) {
            if (!validateGroup(group)) validity = false;
        }

        for (const field of requiredCustomFields) {
            if (!validateCustomField(field)) validity = false;
        }

        return validity;
    };

    const onValidateChange = () => validate();
    fields.forEach((field) => {
        addListener(field, "input", onValidateChange);
        addListener(field, "change", onValidateChange);
    });

    requiredGroups.forEach((group) => {
        group.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach((input) => {
            addListener(input, "change", onValidateChange);
        });
    });

    requiredCustomFields.forEach((field) => {
        const input = field.querySelector("[data-field-value], [data-field-values]");
        if (input) addListener(input, "change", onValidateChange);
    });

    addListener(form, "reset", () => api.reset());

    addListener(form, "submit", (event) => {
        event.preventDefault();
        init = false;
        if (validate()) {
            send(api);
        } else {
            firstInvalidControl?.focus?.();
        }
    });

    api.destroy = () => {
        listeners.forEach(([target, type, listener, options]) => {
            target.removeEventListener(type, listener, options);
        });
        listeners.length = 0;
        delete form.__formValidateInstance;
    };

    form.__formValidateInstance = api;

    return api;
};

export default FormValidate;
