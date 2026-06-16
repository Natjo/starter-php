export function splitText(targetEl, {
  wordClass = "word",
  charClass = "char",
  skipSelector = ""
} = {}) {
  var _el$textContent$repla, _el$textContent;
  const el = targetEl;
  if (!el) return {
    words: [],
    chars: [],
    restore: () => {}
  };
  const originalText = (_el$textContent$repla = (_el$textContent = el.textContent) === null || _el$textContent === void 0 ? void 0 : _el$textContent.replace(/\s+/g, " ").trim()) !== null && _el$textContent$repla !== void 0 ? _el$textContent$repla : "";
  const originalHtml = el.innerHTML;
  el.setAttribute("aria-label", originalText);
  const words = [];
  const chars = [];
  const textNodes = [];
  const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, {
    acceptNode: node => {
      var _parent$closest;
      if (!skipSelector) return NodeFilter.FILTER_ACCEPT;
      const parent = node.parentElement;
      return parent !== null && parent !== void 0 && (_parent$closest = parent.closest) !== null && _parent$closest !== void 0 && _parent$closest.call(parent, skipSelector) ? NodeFilter.FILTER_REJECT : NodeFilter.FILTER_ACCEPT;
    }
  });
  while (walker.nextNode()) {
    textNodes.push(walker.currentNode);
  }
  const splitTextNode = textNode => {
    var _textNode$nodeValue;
    const text = (_textNode$nodeValue = textNode.nodeValue) !== null && _textNode$nodeValue !== void 0 ? _textNode$nodeValue : "";
    if (text === "") return;
    const frag = document.createDocumentFragment();
    const parts = text.split(/(\s+)/);
    for (const part of parts) {
      if (part === "") continue;
      if (/^\s+$/.test(part)) {
        frag.appendChild(document.createTextNode(part));
        continue;
      }
      const wordSpan = document.createElement("span");
      wordSpan.className = wordClass;
      for (const char of Array.from(part)) {
        const chSpan = document.createElement("span");
        chSpan.className = charClass;
        chSpan.textContent = char;
        wordSpan.appendChild(chSpan);
        chars.push(chSpan);
      }
      words.push(wordSpan);
      frag.appendChild(wordSpan);
    }
    textNode.replaceWith(frag);
  };
  textNodes.forEach(splitTextNode);
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