const AUTOPLAY_DELAY_MS = 600;
class Video {
  constructor(root) {
    if (!(root instanceof HTMLElement)) return;
    if (root.__videoInstance) return root.__videoInstance;
    this.root = root;
    this.poster = root.querySelector(".poster");
    if (!this.poster) return;
    this._onClick = this._onClick.bind(this);
    this.poster.addEventListener("click", this._onClick);
    root.__videoInstance = this;
  }
  _onClick() {
    const iframe = this.root.querySelector("iframe");
    const video = this.root.querySelector("video");
    const player = iframe || video;
    if (player) player.removeAttribute("tabindex");
    const isAutoplay = this.root.dataset.autoplay === "true";
    if (!isAutoplay) {
      this._reveal(player);
      return;
    }
    const autoplaySrc = this.root.dataset.autoplaySrc;
    if (iframe && autoplaySrc) {
      iframe.src = autoplaySrc;
    } else if (video) {
      try {
        video.play();
      } catch (_) {}
    }
    this._hideTimer = window.setTimeout(() => this._reveal(player), AUTOPLAY_DELAY_MS);
  }
  _reveal(player) {
    if (player instanceof HTMLIFrameElement && !player.src && player.dataset.src) {
      player.src = player.dataset.src;
    }
    this.root.classList.add("is-playing");
    if (player) {
      try {
        player.focus();
      } catch (_) {}
    }
  }
  destroy() {
    if (this.poster && this._onClick) {
      this.poster.removeEventListener("click", this._onClick);
    }
    if (this._hideTimer) {
      window.clearTimeout(this._hideTimer);
      this._hideTimer = null;
    }
    if (this.root) delete this.root.__videoInstance;
  }
}
export default function video(root) {
  return new Video(root);
}