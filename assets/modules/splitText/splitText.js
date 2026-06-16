/**
 * Split a text element into word/char spans.
 *
 * Contract:
 * - preserves inline HTML by splitting text nodes in place
 * - returns { words, chars, restore }
 */
export function splitText(targetEl, { wordClass = "word", charClass = "char", skipSelector = "" } = {}) {
  const el = targetEl;
  if (!el) return { words: [], chars: [], restore: () => {} };

  const originalText = el.textContent?.replace(/\s+/g, " ").trim() ?? "";
  const originalHtml = el.innerHTML;

  el.setAttribute("aria-label", originalText);

  const words = [];
  const chars = [];
  const textNodes = [];
  const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, {
    acceptNode: node => {
      if (!skipSelector) return NodeFilter.FILTER_ACCEPT;
      const parent = node.parentElement;
      return parent?.closest?.(skipSelector)
        ? NodeFilter.FILTER_REJECT
        : NodeFilter.FILTER_ACCEPT;
    },
  });

  while (walker.nextNode()) {
    textNodes.push(walker.currentNode);
  }

  const splitTextNode = textNode => {
    const text = textNode.nodeValue ?? "";
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

  return { words, chars, restore };
}
