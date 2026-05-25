/**
 * Slider
 * Smooth drag/scroll slider with pointer events and RAF-throttled callbacks.
 * Public API: add(), remove(), slideTo(n), onchange, onnext, onprev
 */
function Slider(slider) {
    if (!slider) return;

    const content = slider.querySelector(".slider-content");
    const items = slider.querySelectorAll(".item");
    const itemsArray = Array.from(items);
    const itemIndexMap = new WeakMap();
    itemsArray.forEach((item, i) => itemIndexMap.set(item, i));
    const btnNext = slider.querySelector(".next");
    const btnPrev = slider.querySelector(".prev");
    const statusEl = slider.querySelector("[data-slider-status]");
    const paginationEl = slider.querySelector("[data-slider-pagination]");
    const measureEl = slider.querySelector(".slider-wrapper") || slider;
    if (!content || !items.length) return;

    const prefersReduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    const s = {
        index: 0,
        prevIndex: 0,
        maxIndex: 0,
        gap: 0, left: 0,
        itemWidth: 0,
        itemStep: 0,
        contentWidth: 0,
        viewportWidth: 0,
        itemOffsets: [],
        disabled: false,
        visible: true,
        raf: null,
        scrollFrame: null,
        resizeFrame: null,
        dragStartTime: 0,
        dragStartScroll: 0,
        lastScrollLeft: 0,
        lastOnchangeAt: 0,
        dots: [],
        paginationSize: -1,
    };

    const api = this;
    const clamp = (v, lo, hi) => Math.min(Math.max(v, lo), hi);
    const pct = () => content.scrollLeft * 100 / (s.contentWidth - s.viewportWidth || 1);

    const updateControls = () => {
        btnPrev?.setAttribute("aria-disabled", s.index <= 0);
        btnPrev?.classList[s.disabled ? "add" : "remove"]("hide");
        btnNext?.setAttribute("aria-disabled", s.index === s.maxIndex);
        btnNext?.classList[s.disabled ? "add" : "remove"]("hide");
    };

    const computeMaxIndex = () => {
        // Cache offsets once to avoid reflows in a loop
        s.itemOffsets = itemsArray.map(el => el.offsetLeft);
        s.maxIndex = 0;
        const scrollable = s.contentWidth - s.viewportWidth + s.left;
        for (let i = 0; i < s.itemOffsets.length - 1; i++) {
            if (s.itemOffsets[i] < scrollable) s.maxIndex++;
        }
    };

    const easeOutQuint = (t, b, c, d) => c * ((t = t / d - 1) * t * t * t * t + 1) + b;

    const animateTo = (end) => {
        updateControls();
        cancelAnimationFrame(s.raf);
        const start = content.scrollLeft;
        const distance = end - start;
        const duration = 800;

        if (Math.abs(distance) < 1 || prefersReduced) {
            content.scrollLeft = end;
            onAnimEnd();
            return;
        }

        let init = null;
        const step = (now) => {
            if (!init) init = now;
            const t = now - init;
            content.scrollLeft = easeOutQuint(t, start, distance, duration);
            if (t < duration) {
                s.raf = requestAnimationFrame(step);
            } else {
                content.scrollLeft = end;
                onAnimEnd();
            }
        };
        s.raf = requestAnimationFrame(step);
    };

    const onAnimEnd = () => {
        content.style.scrollSnapType = "";
        setSwipe(false);
    };

    const setSwipe = (on) => content.classList[on ? "add" : "remove"]("swipe");

    const goToCurrent = () => {
        s.index = clamp(s.index, 0, s.maxIndex);
        if (s.disabled) return;

        content.style.scrollSnapType = "none";
        animateTo(
            s.index === s.maxIndex ? s.contentWidth - s.viewportWidth : s.itemOffsets[s.index] - s.left
        );
    };

    const syncIndex = () => {
        if (s.disabled || !s.visible) return;   // #7 — skip if off-screen
        const scrollLeft = content.scrollLeft;
        const nextIndex = Math.trunc(
            (scrollLeft + (s.itemWidth / 2 + s.gap)) / s.itemStep
        );
        const clampedIndex =
            scrollLeft + s.viewportWidth >= s.contentWidth
                ? s.maxIndex
                : clamp(nextIndex, 0, s.maxIndex);

        const p = scrollLeft * 100 / (s.contentWidth - s.viewportWidth || 1);
        const now = performance.now();

        if (clampedIndex !== s.index) {
            s.index = clampedIndex;
            updateAria();
            api.onchange(s.index, s.maxIndex, p);
            s.lastOnchangeAt = now;
            return;
        }

        // If the consumer does expensive work, avoid calling on every scroll frame.
        // Still allow progress updates at a lower rate.
        if (now - s.lastOnchangeAt > 120) {
            api.onchange(s.index, s.maxIndex, p);
            s.lastOnchangeAt = now;
        }
    };

    const updateAria = () => {
        if (statusEl) statusEl.textContent = `Diapositive ${s.index + 1} sur ${s.maxIndex + 1}`;
        if (!s.dots.length) return;
        if (s.prevIndex !== s.index) {
            const prevDot = s.dots[s.prevIndex];
            if (prevDot) {
                prevDot.classList.remove("is-active");
                prevDot.setAttribute("aria-current", "false");
            }
        }
        const currentDot = s.dots[s.index];
        if (currentDot) {
            currentDot.classList.add("is-active");
            currentDot.setAttribute("aria-current", "true");
        }
        s.prevIndex = s.index;
    };

    const buildPagination = () => {
        if (!paginationEl) return;
        const expectedSize = s.maxIndex + 1;
        if (s.paginationSize === expectedSize && s.dots.length === expectedSize) {
            updateAria();
            return;
        }
        paginationEl.innerHTML = "";
        s.dots = [];
        const fragment = document.createDocumentFragment();
        for (let i = 0; i < expectedSize; i++) {
            const dot = document.createElement("button");
            dot.className = "slider-dot" + (i === s.index ? " is-active" : "");
            dot.setAttribute("type", "button");
            dot.setAttribute("aria-label", `Aller à la diapositive ${i + 1}`);
            dot.setAttribute("aria-current", i === s.index ? "true" : "false");
            dot.dataset.index = String(i);
            s.dots.push(dot);
            fragment.appendChild(dot);
        }
        paginationEl.appendChild(fragment);
        s.paginationSize = expectedSize;
        s.prevIndex = s.index;
    };

    const onPaginationClick = (e) => {
        if (!paginationEl) return;
        const dot = e.target.closest(".slider-dot");
        if (!dot || !paginationEl.contains(dot)) return;
        const nextIndex = Number(dot.dataset.index);
        if (!Number.isInteger(nextIndex)) return;
        setSwipe(true);
        s.index = nextIndex;
        goToCurrent();
        updateAria();
    };

    const onScroll = () => {
        if (s.scrollFrame) return;
        s.scrollFrame = requestAnimationFrame(() => {
            s.scrollFrame = null;
            syncIndex();
        });
    };

    const resize = () => {
        if (!s.visible) return;     // #7 — skip if off-screen
        const cs = getComputedStyle(content);
        s.gap = parseInt(cs.gap, 10) || 0;
        s.itemWidth = items[0].offsetWidth;
        s.itemStep = s.itemWidth + s.gap;

        const bound = measureEl.getBoundingClientRect();
        // Use the same coordinate space for both left/right: viewport rect from getBoundingClientRect().
        // This avoids Firefox inconsistencies when side panels/scrollbars affect clientWidth/innerWidth.
        const viewportRect = slider.ownerDocument.documentElement.getBoundingClientRect();
        s.left = Math.round(bound.left - viewportRect.left);
        slider.style.setProperty("--left", `${s.left}px`);
        slider.style.setProperty("--right", `${Math.round(viewportRect.right - bound.right)}px`);
        s.contentWidth = content.scrollWidth;
        s.viewportWidth = content.offsetWidth;

        computeMaxIndex();
        s.disabled = s.contentWidth <= s.viewportWidth;
        slider.classList[s.disabled ? "add" : "remove"]("disable");
        updateControls();
        buildPagination();
    };

    const onResize = () => {
        if (s.resizeFrame) return;
        s.resizeFrame = requestAnimationFrame(() => { s.resizeFrame = null; resize(); });
    };

    // ── Pointer drag (mouse only) ─────────────────────────────────────────────

    const onPointerMove = (e) => {
        if (e.pointerType !== "mouse" || e.buttons === 0) return;
        setSwipe(true);
        content.scrollLeft = -e.clientX + s.dragStartScroll;
    };

    const endDrag = () => {
        window.removeEventListener("pointermove", onPointerMove);
        window.removeEventListener("pointerup", endDrag);
        window.removeEventListener("pointercancel", endDrag);
        content.classList.remove("grab");

        if (Date.now() - s.dragStartTime < 300) {
            const diff = content.scrollLeft - s.lastScrollLeft;
            if (diff > 4) s.index = clamp(s.index + 1, 0, s.maxIndex);
            if (diff < -4) s.index = clamp(s.index - 1, 0, s.maxIndex);
        }
        s.lastScrollLeft = content.scrollLeft;
        goToCurrent();
    };

    const startDrag = (e) => {
        if (e.pointerType !== "mouse" || e.button !== 0) return;
        e.preventDefault();
        s.dragStartTime = Date.now();
        s.lastScrollLeft = content.scrollLeft;
        s.dragStartScroll = e.clientX + content.scrollLeft;
        cancelAnimationFrame(s.raf);
        content.classList.add("grab");
        window.addEventListener("pointermove", onPointerMove);
        window.addEventListener("pointerup", endDrag);
        window.addEventListener("pointercancel", endDrag);
    };

    // ── Keyboard navigation ──────────────────────────────────────────────────

    const onWheel = (e) => {
        const absX = Math.abs(e.deltaX);
        const absY = Math.abs(e.deltaY);
        const horizontalIntent = (e.shiftKey && absY > 0) || (absX > absY && absX > 0);

        if (horizontalIntent) {
            // Let the browser scroll the horizontal container natively (best inertia/feel),
            // but prevent Lenis from hijacking the wheel gesture.
            e.lenisStopPropagation = true;
            return;
        }

        // Vertical intent: ensure the horizontal scroller doesn't swallow the wheel.
        // If Lenis is active, let it handle the page scroll (so we only prevent default here).
        const lenisActive = document.documentElement.classList.contains("lenis");
        if (lenisActive && e.cancelable) e.preventDefault();
    };

    // ── Touch: prevent Lenis when intent is horizontal ───────────────────────

    let touchStartX = 0;
    let touchStartY = 0;
    let touchLock = null; // 'x' | 'y' | null
    const touchThreshold = 6;

    const onTouchStart = (e) => {
        if (!e.touches || e.touches.length !== 1) return;
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
        touchLock = null;
    };

    const onTouchMove = (e) => {
        if (!e.touches || e.touches.length !== 1) return;
        const dx = e.touches[0].clientX - touchStartX;
        const dy = e.touches[0].clientY - touchStartY;

        if (touchLock === null) {
            const absX = Math.abs(dx);
            const absY = Math.abs(dy);
            if (absX < touchThreshold && absY < touchThreshold) return;
            touchLock = absX > absY ? "x" : "y";
        }

        if (touchLock === "x") {
            e.lenisStopPropagation = true;
            e.stopPropagation();
        }
    };

    const onTouchEnd = () => {
        touchLock = null;
    };

    const onKeyDown = (e) => {
        if (s.disabled) return;
        if (e.key === "ArrowLeft") { e.preventDefault(); prev(); }
        if (e.key === "ArrowRight") { e.preventDefault(); next(); }
    };

    // ── Focus tracking — scroll to the item receiving focus ──────────────────

    const onFocusIn = (e) => {
        if (s.disabled) return;
        const item = e.target.closest(".item");
        if (!item) return;
        const idx = itemIndexMap.get(item);
        if (idx == null || idx === s.index) return;
        setSwipe(true);
        s.index = clamp(idx, 0, s.maxIndex);
        goToCurrent();
        updateAria();
    };

    // ── Navigation buttons ───────────────────────────────────────────────────

    const next = () => {
        setSwipe(true);
        s.index = clamp(s.index + 1, 0, s.maxIndex);
        goToCurrent();
        updateAria();
        api.onnext(s.index);
    };

    const prev = () => {
        setSwipe(true);
        s.index = clamp(s.index - 1, 0, s.maxIndex);
        goToCurrent();
        updateAria();
        api.onprev(s.index);
    };

    api.onnext = () => { };
    api.onprev = () => { };
    api.onchange = () => { };

    if (btnNext) btnNext.onclick = next;
    if (btnPrev) btnPrev.onclick = prev;

    // ── Public API ───────────────────────────────────────────────────────────

    api.add = () => {
        content.scrollTo(0, 0);
        content.setAttribute("tabindex", "0");
       // slider.classList.add("slider");
        slider.classList.remove("inactive");
        resize();
        updateAria();
        window.addEventListener("load", resize);
        content.onpointerdown = startDrag;
        content.addEventListener("scroll", onScroll, { passive: true });
        content.addEventListener("wheel", onWheel, { passive: false });
        content.addEventListener("touchstart", onTouchStart, { passive: true });
        content.addEventListener("touchmove", onTouchMove, { passive: true });
        content.addEventListener("touchend", onTouchEnd, { passive: true });
        content.addEventListener("touchcancel", onTouchEnd, { passive: true });
        content.addEventListener("keydown", onKeyDown);
        content.addEventListener("focusin", onFocusIn);
        paginationEl?.addEventListener("click", onPaginationClick);
        window.addEventListener("resize", onResize, { passive: true });

        // #7 — suspend callbacks when slider is off-screen
        const io = new IntersectionObserver((entries) => {
            s.visible = entries[0].isIntersecting;
            if (s.visible) resize();
        }, { threshold: 0 });
        io.observe(slider);
        s._io = io;
    };

    api.remove = () => {
       // slider.classList.remove("slider");
       slider.classList.add("inactive");
        content.onpointerdown = null;
        content.removeEventListener("scroll", onScroll);
        content.removeEventListener("wheel", onWheel);
        content.removeEventListener("touchstart", onTouchStart);
        content.removeEventListener("touchmove", onTouchMove);
        content.removeEventListener("touchend", onTouchEnd);
        content.removeEventListener("touchcancel", onTouchEnd);
        content.removeEventListener("keydown", onKeyDown);
        content.removeEventListener("focusin", onFocusIn);
        paginationEl?.removeEventListener("click", onPaginationClick);
        window.removeEventListener("load", resize);
        window.removeEventListener("resize", onResize);
        window.removeEventListener("pointermove", onPointerMove);
        window.removeEventListener("pointerup", endDrag);
        window.removeEventListener("pointercancel", endDrag);
        cancelAnimationFrame(s.raf);
        if (s.scrollFrame) cancelAnimationFrame(s.scrollFrame);
        if (s.resizeFrame) cancelAnimationFrame(s.resizeFrame);
        s._io?.disconnect();
        content.classList.remove("grab", "swipe");
        content.style.scrollSnapType = "";
    };

    api.slideTo = (value) => {
        setSwipe(true);
        s.index = clamp(value, 0, s.maxIndex);
        goToCurrent();
    };
}

export default Slider;
