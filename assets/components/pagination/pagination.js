export default function pagination(root) {
    if (root.__paginationInitialized) return;

    const targetId = root.dataset.target || '';
    const endpoint = root.dataset.endpoint || '';
    const pageParam = root.dataset.pageParam || 'page';
    const target = targetId !== '' ? document.getElementById(targetId) : null;
    let controller = null;

    if (!target || endpoint === '') return;

    root.__paginationInitialized = true;

    const setLoading = loading => {
        root.classList.toggle('is-loading', loading);
        root.setAttribute('aria-busy', String(loading));
        target.setAttribute('aria-busy', String(loading));
    };

    const replacePagination = html => {
        const template = document.createElement('template');
        template.innerHTML = html.trim();
        const nextPagination = template.content.querySelector('.pagination');

        if (!nextPagination) return;

        root.innerHTML = nextPagination.innerHTML;
    };

    const load = async (page, url, pushState = true) => {
        controller?.abort();
        controller = new AbortController();
        setLoading(true);

        try {
            const publicUrl = new URL(url, window.location.origin);
            const requestUrl = new URL(endpoint, window.location.origin);

            publicUrl.searchParams.forEach((value, key) => {
                if (key !== pageParam) {
                    requestUrl.searchParams.append(key, value);
                }
            });
            requestUrl.searchParams.set('page', String(page));
            requestUrl.searchParams.set('target', targetId);
            requestUrl.searchParams.set('page_param', pageParam);

            const response = await fetch(requestUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: controller.signal,
            });

            if (!response.ok) {
                throw new Error(`Pagination request failed: ${response.status}`);
            }

            const data = await response.json();

            target.innerHTML = data.results || '';
            replacePagination(data.pagination || '');

            if (pushState) {
                window.history.pushState({ paginationPage: page }, '', publicUrl);
            }

            root.querySelector('[data-pagination-status]')?.replaceChildren(
                document.createTextNode(`Page ${page} chargée`)
            );
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (error) {
            if (error.name === 'AbortError') return;

            console.error(error);
            window.location.assign(publicUrl);
        } finally {
            setLoading(false);
        }
    };

    root.addEventListener('click', event => {
        const link = event.target.closest('a[href]');

        if (
            !link
            || !root.contains(link)
            || link.getAttribute('aria-disabled') === 'true'
            || link.getAttribute('aria-current') === 'page'
        ) return;

        const url = new URL(link.href, window.location.origin);
        const page = Number.parseInt(url.searchParams.get(pageParam) || '1', 10);

        if (!Number.isInteger(page) || page < 1) return;

        event.preventDefault();
        load(page, url.toString());
    });

    window.addEventListener('popstate', () => {
        const url = new URL(window.location.href);
        const page = Number.parseInt(url.searchParams.get(pageParam) || '1', 10);

        load(Number.isInteger(page) && page > 0 ? page : 1, url.toString(), false);
    });
}
