export default function selectLang(element) {
  const trigger = element.querySelector('.select-lang-trigger');
  const list = element.querySelector('.select-lang-list');
  if (!(trigger instanceof HTMLButtonElement) || !(list instanceof HTMLElement)) return;
  const links = [...list.querySelectorAll('a.select-lang-option')];
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
      var _ref;
      (_ref = list.querySelector('[aria-current="page"]') || links[0]) === null || _ref === void 0 || _ref.focus();
    } else if (focus === 'last') {
      var _links$at;
      (_links$at = links.at(-1)) === null || _links$at === void 0 || _links$at.focus();
    } else if (focus === 'first') {
      var _links$;
      (_links$ = links[0]) === null || _links$ === void 0 || _links$.focus();
    }
  };
  trigger.addEventListener('click', () => {
    if (list.hidden) {
      open('current');
    } else {
      close();
    }
  });
  trigger.addEventListener('keydown', event => {
    if (event.key === 'ArrowDown') {
      event.preventDefault();
      open('first');
    } else if (event.key === 'ArrowUp') {
      event.preventDefault();
      open('last');
    }
  });
  list.addEventListener('keydown', event => {
    const currentIndex = links.indexOf(document.activeElement);
    if (event.key === 'Escape') {
      event.preventDefault();
      close(true);
    } else if (event.key === 'ArrowDown') {
      var _links;
      event.preventDefault();
      (_links = links[(currentIndex + 1) % links.length]) === null || _links === void 0 || _links.focus();
    } else if (event.key === 'ArrowUp') {
      var _links2;
      event.preventDefault();
      (_links2 = links[(currentIndex - 1 + links.length) % links.length]) === null || _links2 === void 0 || _links2.focus();
    } else if (event.key === 'Home') {
      var _links$2;
      event.preventDefault();
      (_links$2 = links[0]) === null || _links$2 === void 0 || _links$2.focus();
    } else if (event.key === 'End') {
      var _links$at2;
      event.preventDefault();
      (_links$at2 = links.at(-1)) === null || _links$at2 === void 0 || _links$at2.focus();
    }
  });
  document.addEventListener('pointerdown', event => {
    if (!element.contains(event.target)) close();
  });
  element.addEventListener('focusout', () => {
    window.requestAnimationFrame(() => {
      if (!element.contains(document.activeElement)) close();
    });
  });
}