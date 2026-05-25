/**
 * Video component
 *
 * Au click sur le bouton .poster :
 *  - On retire `tabindex="-1"` sur l'iframe / <video> (réintégration dans la
 *    séquence de tabulation, on n'en a plus besoin puisque le poster va
 *    disparaître).
 *  - Si data-autoplay="true" :
 *      • iframe : on swap iframe.src par data-autoplay-src,
 *      • video  : on appelle .play(),
 *      • on attend ~600ms le temps que l'embed/lecture démarre avant de
 *        masquer le poster et de déplacer le focus sur le lecteur.
 *  - Si data-autoplay="false" :
 *      • aucune lecture déclenchée, on masque immédiatement le poster
 *        et on bascule le focus sur le lecteur (controls natifs).
 */
const AUTOPLAY_DELAY_MS = 600;

export default class Video {
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

        // Réintègre le lecteur dans la séquence de tabulation.
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
            try { video.play(); } catch (_) { /* noop */ }
        }

        this._hideTimer = window.setTimeout(() => this._reveal(player), AUTOPLAY_DELAY_MS);
    }

    _reveal(player) {
        this.root.classList.add("is-playing");
        if (player) {
            try { player.focus(); } catch (_) { /* noop */ }
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
