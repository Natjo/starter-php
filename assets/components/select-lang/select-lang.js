export default function selectLang(element) {
    const trigger = element.querySelector('.select-lang-trigger');
    const list = element.querySelector('.select-lang-list');

    if (!(trigger instanceof HTMLButtonElement) || !(list instanceof HTMLElement)) return;

    const links = [...list.querySelectorAll('a.select-lang-option')];
    const controller = new AbortController();
    const listenerOptions = { signal: controller.signal };

    const close = (restoreFocus = false) => {
        trigger.setAttribute('aria-expanded', 'false');
        list.hidden = true;
        element.classList.remove('is-open');

        if (restoreFocus) trigger.focus();
    };

    const open = (focus = 'first') => {
        trigger.setAttribute('aria-expanded', 'true');
        list.hidden = false;
        element.classList.add('is-open');

        if (focus === 'current') {
            (list.querySelector('[aria-current="page"]') || links[0])?.focus();
        } else if (focus === 'last') {
            links.at(-1)?.focus();
        } else if (focus === 'first') {
            links[0]?.focus();
        }
    };

    trigger.addEventListener('click', () => {
        if (list.hidden) {
            open('current');
        } else {
            close();
        }
    }, listenerOptions);

    trigger.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            open('first');
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            open('last');
        }
    }, listenerOptions);

    list.addEventListener('keydown', (event) => {
        const currentIndex = links.indexOf(document.activeElement);

        if (event.key === 'Escape') {
            event.preventDefault();
            close(true);
        } else if (event.key === 'ArrowDown') {
            event.preventDefault();
            links[(currentIndex + 1) % links.length]?.focus();
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            links[(currentIndex - 1 + links.length) % links.length]?.focus();
        } else if (event.key === 'Home') {
            event.preventDefault();
            links[0]?.focus();
        } else if (event.key === 'End') {
            event.preventDefault();
            links.at(-1)?.focus();
        }
    }, listenerOptions);

    document.addEventListener('pointerdown', (event) => {
        if (!element.contains(event.target)) close();
    }, listenerOptions);

    element.addEventListener('focusout', () => {
        window.requestAnimationFrame(() => {
            if (!element.contains(document.activeElement)) close();
        });
    }, listenerOptions);

    return () => {
        controller.abort();
        close();
    };
}
