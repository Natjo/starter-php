import initPagination from '../pagination/pagination.js';

const parseResponse = async response => {
    if (!response.ok) {
        throw new Error(`Filters request failed: ${response.status}`);
    }

    return response.json();
};

export default function filters(form) {
    if (form.__filtersInitialized) return;

    const target = document.getElementById(form.dataset.target || '');
    const paginationContainer = document.getElementById(form.dataset.paginationTarget || '');
    const endpoint = form.dataset.endpoint || '';
    const queryName = form.dataset.queryName || 'q';
    const filterName = form.dataset.filterName || 'type[]';
    const pageParam = form.dataset.pageParam || 'page';
    let controller = null;

    if (!target || !paginationContainer || endpoint === '') return;

    form.__filtersInitialized = true;

    const syncFormFromUrl = () => {
        const url = new URL(window.location.href);
        const queryInput = form.querySelector(`[name="${queryName}"]`);
        const selectedFilters = url.searchParams.getAll(filterName);

        if (queryInput) {
            queryInput.value = url.searchParams.get(queryName) || '';
        }

        form.querySelectorAll(`input[name="${filterName}"]`).forEach(input => {
            input.checked = selectedFilters.length === 0 || selectedFilters.includes(input.value);
        });
    };

    const replacePagination = html => {
        const template = document.createElement('template');
        template.innerHTML = html.trim();
        const nextPagination = template.content.querySelector('.pagination');
        const currentPagination = paginationContainer.querySelector('.pagination');

        if (!nextPagination) {
            if (currentPagination) currentPagination.hidden = true;
            return;
        }

        if (currentPagination) {
            currentPagination.dataset.endpoint = nextPagination.dataset.endpoint || '';
            currentPagination.dataset.target = nextPagination.dataset.target || '';
            currentPagination.dataset.pageParam = nextPagination.dataset.pageParam || 'page';
            currentPagination.setAttribute('aria-label', nextPagination.getAttribute('aria-label') || 'Pagination');
            currentPagination.innerHTML = nextPagination.innerHTML;
            currentPagination.hidden = false;
            return;
        }

        paginationContainer.append(nextPagination);
        initPagination(nextPagination);
    };

    const setLoading = loading => {
        form.classList.toggle('is-loading', loading);
        form.setAttribute('aria-busy', String(loading));
        target.setAttribute('aria-busy', String(loading));
    };

    const load = async (url, pushState = true) => {
        controller?.abort();
        controller = new AbortController();
        setLoading(true);

        try {
            const publicUrl = new URL(url, window.location.origin);
            const requestUrl = new URL(endpoint, window.location.origin);

            publicUrl.searchParams.forEach((value, key) => {
                requestUrl.searchParams.append(key, value);
            });
            requestUrl.searchParams.set('page', '1');
            requestUrl.searchParams.set('target', form.dataset.target);
            requestUrl.searchParams.set('page_param', pageParam);

            const data = await fetch(requestUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: controller.signal,
            }).then(parseResponse);

            target.innerHTML = data.results || '';
            replacePagination(data.pagination || '');

            if (pushState) {
                window.history.pushState({ filters: true }, '', publicUrl);
            }

            target.focus({ preventScroll: true });
        } catch (error) {
            if (error.name === 'AbortError') return;

            console.error(error);
            window.location.assign(url);
        } finally {
            setLoading(false);
        }
    };

    const submit = () => {
        const url = new URL(form.action, window.location.origin);

        new FormData(form).forEach((value, key) => {
            if (String(value).trim() !== '') {
                url.searchParams.append(key, String(value));
            }
        });

        load(url);
    };

    form.addEventListener('submit', event => {
        event.preventDefault();
        submit();
    });

    form.addEventListener('change', event => {
        if (event.target.matches('input[type="checkbox"]')) {
            submit();
        }
    });

    const initialPagination = paginationContainer.querySelector('.pagination');
    if (initialPagination) initPagination(initialPagination);

    window.addEventListener('popstate', syncFormFromUrl);
}
