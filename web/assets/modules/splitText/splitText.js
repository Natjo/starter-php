export function splitText(targetEl, {
  wordClass = "word",
  charClass = "char"
} = {}) {
  var _el$textContent;
  const el = targetEl;
  if (!el) return {
    words: [],
    chars: [],
    restore: () => {}
  };
  const originalText = (_el$textContent = el.textContent) !== null && _el$textContent !== void 0 ? _el$textContent : "";
  const originalHtml = el.innerHTML;
  el.setAttribute("aria-label", originalText);
  el.textContent = "";
  const frag = document.createDocumentFragment();
  const words = originalText.split(/\s+/).filter(Boolean);
  const chars = [];
  for (let w = 0; w < words.length; w++) {
    const word = words[w];
    const wordSpan = document.createElement("span");
    wordSpan.className = wordClass;
    for (let i = 0; i < word.length; i++) {
      const chSpan = document.createElement("span");
      chSpan.className = charClass;
      chSpan.textContent = word[i];
      wordSpan.appendChild(chSpan);
      chars.push(chSpan);
    }
    frag.appendChild(wordSpan);
    if (w !== words.length - 1) frag.appendChild(document.createTextNode(" "));
  }
  el.appendChild(frag);
  function restore() {
    el.removeAttribute("aria-label");
    el.innerHTML = originalHtml;
  }
  return {
    words,
    chars,
    restore
  };
}