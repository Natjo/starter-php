import FormValidate from '../../form/form.js';

const simulateRequest = () => new Promise(resolve => {
    window.setTimeout(() => resolve({ ok: true }), 450);
});

export default function newsletter(root) {
    const form = root.querySelector('[data-newsletter-form]');
    const submit = root.querySelector('[data-newsletter-submit]');
    const status = root.querySelector('[data-newsletter-status]');

    if (!form || !submit || !status) return;

    const setLoading = loading => {
        submit.disabled = loading;
        submit.setAttribute('aria-busy', String(loading));
        form.setAttribute('aria-busy', String(loading));
    };

    const setStatus = (message, error = false) => {
        status.textContent = message;
        status.hidden = message === '';
        status.classList.toggle('is-error', error);
        status.setAttribute('role', error ? 'alert' : 'status');
    };

    const send = async validator => {
        const endpoint = form.dataset.endpoint || '';
        const successMessage = form.dataset.successMessage || 'Inscription enregistrée.';
        const errorMessage = form.dataset.errorMessage || "L'inscription a échoué.";

        setLoading(true);
        setStatus('');

        try {
            const response = endpoint
                ? await fetch(endpoint, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { Accept: 'application/json' },
                })
                : await simulateRequest();

            if (!response.ok) {
                throw new Error(`Newsletter request failed: ${response.status || 'unknown'}`);
            }

            form.reset();
            validator.reset();
            setStatus(successMessage);
        } catch (error) {
            console.error(error);
            setStatus(errorMessage, true);
        } finally {
            setLoading(false);
        }
    };

    FormValidate(form, send);
}
