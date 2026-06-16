const COPY_FEEDBACK_DURATION = 1800;
const getSafeShareUrl = url => {
  if (!url) return "";
  try {
    const parsedUrl = new URL(url, window.location.href);
    const allowedProtocols = ["http:", "https:", "mailto:"];
    return allowedProtocols.includes(parsedUrl.protocol) ? parsedUrl.href : "";
  } catch (_) {
    return "";
  }
};
const copyToClipboard = async text => {
  if (!text) return false;
  if (navigator.clipboard && window.isSecureContext) {
    try {
      await navigator.clipboard.writeText(text);
      return true;
    } catch (_) {
      return false;
    }
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
const setCopyFeedback = (button, copied) => {
  var _status$dataset, _status$dataset2;
  const status = button.querySelector("[role='status']");
  const statusText = copied ? (status === null || status === void 0 || (_status$dataset = status.dataset) === null || _status$dataset === void 0 ? void 0 : _status$dataset.success) || "" : (status === null || status === void 0 || (_status$dataset2 = status.dataset) === null || _status$dataset2 === void 0 ? void 0 : _status$dataset2.error) || "";
  if (status && statusText) {
    status.textContent = statusText;
  }
  button.classList.toggle("is-copied", copied);
  button.classList.toggle("is-copy-error", !copied);
  window.clearTimeout(button.__sharesCopyTimer);
  button.__sharesCopyTimer = window.setTimeout(() => {
    button.classList.remove("is-copied");
    button.classList.remove("is-copy-error");
    if (status) {
      status.textContent = "";
    }
  }, COPY_FEEDBACK_DURATION);
};
const openShareWindow = url => {
  const safeUrl = getSafeShareUrl(url);
  if (!safeUrl) return;
  window.open(safeUrl, "_blank", "noopener,noreferrer,width=640,height=520");
};
export default function shares(root) {
  if (!(root instanceof HTMLElement)) return;
  if (root.__sharesInstance) return;
  const onClick = async event => {
    const target = event.target instanceof Element ? event.target : null;
    const button = target === null || target === void 0 ? void 0 : target.closest("button[data-type][data-url]");
    if (!button || !root.contains(button)) return;
    const type = button.dataset.type || "";
    const url = button.dataset.url || "";
    if (!url) return;
    if (type === "copy") {
      const copied = await copyToClipboard(url);
      setCopyFeedback(button, copied);
      return;
    }
    openShareWindow(url);
  };
  root.addEventListener("click", onClick);
  root.__sharesInstance = true;
  return () => {
    root.removeEventListener("click", onClick);
    delete root.__sharesInstance;
  };
}