const COPY_FEEDBACK_DURATION = 1800;

const copyToClipboard = async (text) => {
    if (!text) return false;

    if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
        return true;
    }

    const textarea = document.createElement("textarea");
    textarea.value = text;
    textarea.setAttribute("readonly", "");
    textarea.style.position = "fixed";
    textarea.style.top = "-1000px";
    textarea.style.left = "-1000px";

    document.body.appendChild(textarea);
    textarea.select();

    let copied = false;
    try {
        copied = document.execCommand("copy");
    } catch (_) {
        copied = false;
    }

    textarea.remove();
    return copied;
};

const setCopyFeedback = (button) => {
    const status = button.querySelector("[role='status']");
    const statusText = status?.dataset?.text || "";

    if (status && statusText) {
        status.textContent = statusText;
    }

    button.classList.add("is-copied");

    window.clearTimeout(button.__sharesCopyTimer);
    button.__sharesCopyTimer = window.setTimeout(() => {
        button.classList.remove("is-copied");

        if (status) {
            status.textContent = "";
        }
    }, COPY_FEEDBACK_DURATION);
};

const openShareWindow = (url) => {
    if (!url) return;

    window.open(
        url,
        "_blank",
        "noopener,noreferrer,width=640,height=520"
    );
};

export default function shares(root) {
    if (!(root instanceof HTMLElement)) return;
    if (root.__sharesInstance) return;

    const buttons = Array.from(root.querySelectorAll("button[data-type][data-url]"));

    buttons.forEach((button) => {
        button.addEventListener("click", async () => {
            const type = button.dataset.type || "";
            const url = button.dataset.url || "";

            if (!url) return;

            if (type === "copy") {
                const copied = await copyToClipboard(url);
                if (copied) setCopyFeedback(button);
                return;
            }

            openShareWindow(url);
        });
    });

    root.__sharesInstance = true;
}
