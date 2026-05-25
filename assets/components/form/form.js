
const getInvalidMsgEl = (field) => {
    const group = field.closest('[role="group"]');
    const container = group || field.parentNode;
    return container?.querySelector('.invalid-msg') || null;
};

const ensureErrorId = (field) => {
    const describedby = (field.getAttribute('aria-describedby') || '').trim();
    if (describedby) {
        return describedby.split(/\s+/).filter(Boolean)[0];
    }
    const errorId = field.id ? `${field.id}-error` : `field-error-${crypto.randomUUID?.() || Date.now()}`;
    field.setAttribute('aria-describedby', errorId);
    return errorId;
};

const getMandatoryMessage = (field) => {
    const group = field.closest('[role="group"]');
    return field.dataset.mandatory || group?.dataset?.mandatory || '';
};

const isGroupChecked = (group) => {
    const inputs = group.querySelectorAll('input[type="checkbox"], input[type="radio"]');
    return Array.from(inputs).some((input) => input.checked && !input.disabled);
};

const FormValidate = function (form, onSend) {
    const fields = form.querySelectorAll(':required');
    const requiredGroups = form.querySelectorAll('[role="group"][data-required]');
    let validity = true;
    let init = true;

    const resetGroup = (group) => {
        const inputs = group.querySelectorAll('input[type="checkbox"], input[type="radio"]');
        inputs.forEach((input) => input.removeAttribute('aria-invalid'));
        const invalid_msg = group.querySelector('.invalid-msg');
        if (invalid_msg) {
            invalid_msg.innerHTML = '';
            invalid_msg.hidden = true;
        }
    };

    const validateGroup = (group) => {
        const invalid_msg = group.querySelector('.invalid-msg');
        const msg = (group.dataset.mandatory || '').trim();
        const inputs = group.querySelectorAll('input[type="checkbox"], input[type="radio"]');
        const valid = isGroupChecked(group);

        if (!valid) {
            inputs.forEach((input) => input.setAttribute('aria-invalid', 'true'));
            if (invalid_msg) {
                if (msg) {
                    invalid_msg.innerHTML = msg;
                } else {
                    const anchor = Array.from(inputs).find((input) => input.required) || inputs[0];
                    if (anchor) {
                        anchor.setCustomValidity('');
                        invalid_msg.textContent = anchor.validationMessage;
                    } else {
                        invalid_msg.innerHTML = '';
                    }
                }
                invalid_msg.hidden = false;
            }
            return false;
        }

        inputs.forEach((input) => {
            input.removeAttribute('aria-invalid');
            input.setCustomValidity('');
        });
        if (invalid_msg) {
            invalid_msg.innerHTML = '';
            invalid_msg.hidden = true;
        }
        return true;
    };

    this.reset = () => {
        init = true;
        for (const field of fields) {
            field.removeAttribute('aria-invalid');
            field.setCustomValidity('');
            const invalid_msg = getInvalidMsgEl(field);
            if (invalid_msg) {
                invalid_msg.innerHTML = '';
                invalid_msg.hidden = true;
            }
        }
        requiredGroups.forEach(resetGroup);
    };

    const validate = () => {
        if (init) return;
        validity = true;

        for (const field of fields) {
            const group = field.closest('[role="group"]');
            if (group?.hasAttribute('data-required')) continue;

            const dataTypeMismatch = field.dataset.typemismatch;
            const dataPatternMismatch = field.dataset.patternmismatch;
            const dataMandatory = getMandatoryMessage(field);
            const typeMismatch = field.validity.typeMismatch;
            const tooShort = field.validity.tooShort;
            const tooLong = field.validity.tooLong;
            const stepMismatch = field.validity.stepMismatch;
            const patternMismatch = field.validity.patternMismatch;
            const valueMissing = field.validity.valueMissing;
            let invalid_msg = getInvalidMsgEl(field);

            if (!invalid_msg) {
                invalid_msg = document.createElement('div');
                invalid_msg.className = 'invalid-msg';
                invalid_msg.id = ensureErrorId(field);
                if (group) {
                    group.insertAdjacentElement('beforeend', invalid_msg);
                } else {
                    field.insertAdjacentElement('afterend', invalid_msg);
                }
            }

            if (!field.checkValidity()) {
                field.setAttribute('aria-invalid', 'true');
                let msg = '';
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
                invalid_msg.innerHTML = field.validationMessage;
                invalid_msg.hidden = false;
                validity = false;
            } else {
                field.removeAttribute('aria-invalid');
                field.setCustomValidity('');
                invalid_msg.innerHTML = '';
                invalid_msg.hidden = true;
            }
        }

        for (const group of requiredGroups) {
            if (!validateGroup(group)) validity = false;
        }

        return validity;
    };

    for (const field of fields) {
        field.addEventListener('input', () => validate());
        field.addEventListener('change', () => validate());
    }

    requiredGroups.forEach((group) => {
        group.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach((input) => {
            input.addEventListener('change', () => validate());
        });
    });

    form.addEventListener('reset', () => this.reset());

    form.onsubmit = (e) => {
        e.preventDefault();
        init = false;
        if (validate()) {
            onSend(this);
        }
    };
};

export default FormValidate;
