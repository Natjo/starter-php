export default function selectLang(element) {
    const select = element.querySelector('select');

    if (!select) return;

    select.addEventListener('change', () => {
        const url = select.value.trim();

        if (url === '') return;

        window.location.assign(url);
    });
}
