/**
 * Split a text element into word/char spans.
 *
 * Contract:
 * - input may contain inline markup; tags are preserved
 * - returns { words, chars, restore }
 */
export function splitText(targetEl, { wordClass = "word", charClass = "char" } = {}) {
  const el = targetEl;
  if (!el) return { words: [], chars: [], restore: () => {} };

  const originalText = el.textContent ?? "";
  const originalHtml = el.innerHTML;

  el.setAttribute("aria-label", originalText);
  const chars = [];
  const words = [];

  function splitTextNode(text) {
    const frag = document.createDocumentFragment();
    const parts = text.split(/(\s+)/);

    parts.forEach(part => {
      if (!part) return;

      if (/^\s+$/.test(part)) {
        frag.appendChild(document.createTextNode(part));
        return;
      }

      const wordSpan = document.createElement("span");
      wordSpan.className = wordClass;

      for (let i = 0; i < part.length; i++) {
        const chSpan = document.createElement("span");
        chSpan.className = charClass;
        chSpan.textContent = part[i];
        wordSpan.appendChild(chSpan);
        chars.push(chSpan);
      }

      words.push(wordSpan);
      frag.appendChild(wordSpan);
    });

    return frag;
  }

  function transformNode(node) {
    if (node.nodeType === Node.TEXT_NODE) {
      return splitTextNode(node.textContent ?? "");
    }

    if (node.nodeType !== Node.ELEMENT_NODE) {
      return node.cloneNode(true);
    }

    const clone = node.cloneNode(false);
    node.childNodes.forEach(child => {
      clone.appendChild(transformNode(child));
    });
    return clone;
  }

  const frag = document.createDocumentFragment();
  Array.from(el.childNodes).forEach(node => {
    frag.appendChild(transformNode(node));
  });

  el.replaceChildren(frag);

  function restore() {
    el.removeAttribute("aria-label");
    el.innerHTML = originalHtml;
  }

  return { words, chars, restore };
}
