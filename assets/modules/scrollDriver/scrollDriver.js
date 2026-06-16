function num0(x) {
    const n = Number(x);
    return Number.isFinite(n) ? n : 0;
}

function docTop(el) {
    let y = 0;
    for (let n = el; n; n = n.offsetParent) y += n.offsetTop || 0;
    return y;
}

const easeInOutCubic = (t) => (t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2);

function upDown(val, inMin = 0, inMax = 100, outMax = 100) {
    const v = num0(val);
    // Convenience: if you call upDown(t) with t in 0..1, return 0..1..0 directly.
    if (arguments.length === 1 && v >= 0 && v <= 1) {
        return v <= 0.5 ? (v * 2) : ((1 - v) * 2);
    }
    const denom = num0(inMax) - num0(inMin) || 1;
    let t = (v - num0(inMin)) / denom;
    if (t < 0) t = 0;
    if (t > 1) t = 1;
    const u = t <= 0.5 ? (t * 2) : ((1 - t) * 2); // 0->1->0
    return u * num0(outMax);
}

function cubicBezier(p1x, p1y, p2x, p2y) {
    const cx = 3 * p1x;
    const bx = 3 * (p2x - p1x) - cx;
    const ax = 1 - cx - bx;
    const cy = 3 * p1y;
    const by = 3 * (p2y - p1y) - cy;
    const ay = 1 - cy - by;
    const sampleX = (t) => ((ax * t + bx) * t + cx) * t;
    const sampleY = (t) => ((ay * t + by) * t + cy) * t;
    const sampleDerivX = (t) => (3 * ax * t + 2 * bx) * t + cx;
    function solveTforX(x) {
        // Newton-Raphson
        let t = x;
        for (let i = 0; i < 8; i++) {
            const x2 = sampleX(t) - x;
            const d2 = sampleDerivX(t);
            if (Math.abs(x2) < 1e-6) return t;
            if (Math.abs(d2) < 1e-6) break;
            t = t - x2 / d2;
        }
        // Fallback bisection
        let t0 = 0, t1 = 1;
        t = x;
        for (let i = 0; i < 20; i++) {
            const x2 = sampleX(t);
            if (Math.abs(x2 - x) < 1e-6) return t;
            if (x > x2) t0 = t; else t1 = t;
            t = (t0 + t1) / 2;
        }
        return t;
    }
    return function ease(t) {
        const x = Math.min(1, Math.max(0, t));
        return sampleY(solveTforX(x));
    };
}
const GATED_TYPES = new Set([
    "bottom-bottom",
    "bottom-top",
    "top-top",
    "top-bottom",
    "middle-middle",
    "middle-bottom",
]);

function createStyleCache() {
    const lastTransform = new WeakMap();
    const lastClipPath = new WeakMap();
    const lastOpacity = new WeakMap();
    const lastScale = new WeakMap();
    const lastColor = new WeakMap();
    const lastVars = new WeakMap(); // el -> Map(varName -> stringValue)

    const set = (map, el, prop, value) => {
        if (!el) return;
        const v = String(value);
        if (map.get(el) === v) return;
        map.set(el, v);
        el.style[prop] = v;
    };

    return {
        translate(el, x = 0, y = 0, z = 0) {
            // Accept numbers or strings with units (e.g. "12px", "3vh").
            const xx = typeof x === "number" ? `${x}px` : String(x);
            const yy = typeof y === "number" ? `${y}px` : String(y);
            const zz = typeof z === "number" ? `${z}px` : String(z);
            set(lastTransform, el, "transform", `translate3d(${xx}, ${yy}, ${zz})`);
        },
        clipPath(el, value) {
            set(lastClipPath, el, "clipPath", value);
        },
        var(el, value, name = "--p") {
            if (!el) return;
            const n = String(name);
            const v = String(value);
            let map = lastVars.get(el);
            if (!map) {
                map = new Map();
                lastVars.set(el, map);
            }
            if (map.get(n) === v) return;
            map.set(n, v);
            el.style.setProperty(n, v);
        },
        set(el, percent, name, start = 0, end = 100) {
            // Map `percent` within [start..end] to 0..100 and write it as a CSS var.
            // Before start => 0, after end => 100.
            const p = Number(percent);
            const s = Number(start);
            const e = Number(end);
            const denom = e - s || 1;
            let t = (p - s) / denom; // can be <0 / >1
            if (t < 0) t = 0;
            if (t > 1) t = 1;
            const v = String(t * 100);
            this.var(el, v, name);
        },
        inset(el, top = 0, right = 0, bottom = 0, left = 0) {
            // Accept numbers (treated as px) or strings with units (%, px, vh...).
            const t = typeof top === "number" ? `${top}px` : String(top);
            const r = typeof right === "number" ? `${right}px` : String(right);
            const b = typeof bottom === "number" ? `${bottom}px` : String(bottom);
            const l = typeof left === "number" ? `${left}px` : String(left);
            set(lastClipPath, el, "clipPath", `inset(${t} ${r} ${b} ${l})`);
        },
        polygonFromInset(el, top = 0, right = 0, bottom = 0, left = 0) {
            // Equivalent to clip-path: inset(t r b l) expressed as polygon().
            // polygon points: (l,t) -> (100%-r,t) -> (100%-r,100%-b) -> (l,100%-b)
            const t = typeof top === "number" ? `${top}px` : String(top);
            const r = typeof right === "number" ? `${right}px` : String(right);
            const b = typeof bottom === "number" ? `${bottom}px` : String(bottom);
            const l = typeof left === "number" ? `${left}px` : String(left);
            set(
                lastClipPath,
                el,
                "clipPath",
                `polygon(${l} ${t}, calc(100% - ${r}) ${t}, calc(100% - ${r}) calc(100% - ${b}), ${l} calc(100% - ${b}))`
            );
        },
        opacity(el, value) {
            set(lastOpacity, el, "opacity", value);
        },
        color(el, value) {
            set(lastColor, el, "color", value);
        },
        scale(el, value) {
            // Uses CSS Transform Level 2 `scale` property.
            // If you prefer wider support, set scale via transform string instead.
            set(lastScale, el, "scale", value);
        },
        clear(el) {
            if (!el) return;

            el.style.transform = "";
            el.style.clipPath = "";
            el.style.opacity = "";
            el.style.scale = "";
            el.style.color = "";

            lastTransform.delete(el);
            lastClipPath.delete(el);
            lastOpacity.delete(el);
            lastScale.delete(el);
            lastColor.delete(el);

            const vars = lastVars.get(el);
            vars?.forEach((_, name) => el.style.removeProperty(name));
            lastVars.delete(el);
        },
    };
}

// Shared cache instance (WeakMaps won't leak after unmount / PJAX swaps).
const style = createStyleCache();
const activeDrivers = new Set();
const observedSections = new Map();
let scrollSource = null;
let layoutObserver = null;
let resizeRaf = null;

const currentScrollY = () => window.lenis?.animatedScroll ?? window.scrollY ?? 0;

const notifyDrivers = scrollY => {
    for (const driver of activeDrivers) {
        driver.onScroll(scrollY);
    }
};

const onNativeScroll = () => notifyDrivers(window.scrollY || 0);
const onWindowResize = () => {
    if (resizeRaf != null) return;

    resizeRaf = requestAnimationFrame(() => {
        resizeRaf = null;
        for (const driver of activeDrivers) {
            driver._handleResize();
        }
    });
};
const onLenisScroll = instance => notifyDrivers(instance?.animatedScroll ?? currentScrollY());
const intersectionObserver = new IntersectionObserver(
    entries => {
        for (const entry of entries) {
            const record = observedSections.get(entry.target);
            record?.driver?._handleIntersection(record.section, entry.isIntersecting);
        }
    },
    { rootMargin: "100px 0px 100px 0px", threshold: 0 }
);

const connectSharedObservers = () => {
    if (activeDrivers.size !== 1) return;

    window.addEventListener("resize", onWindowResize, { passive: true });

    if (window.lenis?.on) {
        scrollSource = window.lenis;
        scrollSource.on("scroll", onLenisScroll);
    } else {
        scrollSource = window;
        window.addEventListener("scroll", onNativeScroll, { passive: true });
    }

    if ("ResizeObserver" in window && document.body) {
        layoutObserver = new ResizeObserver(onWindowResize);
        layoutObserver.observe(document.body);
    }
};

const disconnectSharedObservers = () => {
    if (activeDrivers.size) return;

    window.removeEventListener("resize", onWindowResize);
    window.removeEventListener("scroll", onNativeScroll);
    scrollSource?.off?.("scroll", onLenisScroll);
    scrollSource = null;
    layoutObserver?.disconnect();
    layoutObserver = null;
    if (resizeRaf != null) {
        cancelAnimationFrame(resizeRaf);
        resizeRaf = null;
    }
};

class ScrollDriver {
    constructor() {
        this._wh = window.innerHeight;
        this._sections = [];
        this._enabled = false;
        this._latestScrollY = currentScrollY();
        this._rafId = null;
        this._activeSections = new Set();

        this._handleResize = () => {
            this._wh = window.innerHeight;
            this._latestScrollY = currentScrollY();
            for (const s of this._sections) s._measure(this._wh);
            this._syncActiveSections();
            this._schedule();
        };
    }

    _handleIntersection(section, isIntersecting) {
        this._latestScrollY = currentScrollY();
        section.el.classList.toggle("viewed", isIntersecting);

        if (isIntersecting) {
            this._activeSections.add(section);
        } else if (this._activeSections.has(section)) {
            this._activeSections.delete(section);
        }

        // The next scroll/resize tick will update active sections. Updating from
        // IntersectionObserver itself can race Lenis wheel smoothing and produce
        // a transient boundary value.
    }

    // Public: allows external scroll drivers (e.g. Lenis).
    onScroll(scrollY) {
        const y = Number(scrollY);
        this._latestScrollY = Number.isFinite(y) ? y : 0;
        this._schedule();
    }

    refresh() {
        this._handleResize();
        this.onScroll(currentScrollY());
    }

    _schedule() {
        if (this._rafId != null) return;
        this._rafId = requestAnimationFrame(() => {
            this._rafId = null;
            const y = this._latestScrollY;
            for (const s of this._activeSections) s._update(y);
        });
    }

    _syncActiveSections() {
        const margin = 100;

        for (const s of this._sections) {
            const rect = s.el.getBoundingClientRect();
            const isActive = rect.bottom >= -margin && rect.top <= this._wh + margin;

            s.el.classList.toggle("viewed", isActive);
            if (isActive) {
                this._activeSections.add(s);
            } else {
                this._activeSections.delete(s);
            }
        }
    }

    add(el, type = "top-bottom", animation) {
        if (!el) return null;
        const s = new SectionSection(el, type, animation, () => this._wh);
        this._sections.push(s);
        observedSections.set(el, { driver: this, section: s });
        intersectionObserver.observe(el);
        s._measure(this._wh);
        return s;
    }

    enable() {
        if (this._enabled) return;
        this._enabled = true;
        activeDrivers.add(this);
        connectSharedObservers();
        this._handleResize();
        this.onScroll(currentScrollY());
    }

    disable() {
        if (!this._enabled) return;
        this._enabled = false;
        activeDrivers.delete(this);
        disconnectSharedObservers();
        if (this._rafId != null) {
            cancelAnimationFrame(this._rafId);
            this._rafId = null;
        }
    }

    destroy() {
        this.disable();

        for (const s of this._sections) {
            intersectionObserver.unobserve(s.el);
            observedSections.delete(s.el);
            s.el.classList.remove("viewed");
        }

        this._activeSections.clear();
        this._sections.length = 0;
    }
}

class SectionSection {
    constructor(el, type, animation, getWh) {
        this.el = el;
        this._type = type;
        this._animation = typeof animation === "function" ? animation : () => { };
        this._getWh = getWh;

        this._percent = 0;
        this._lastTimelineValue = null; // last value (0..1) computed by timeline()
        this._lastTimelineRawValue = null; // last unclamped value (can be <0 or >1)
        this._triggerPrevByKey = new Map(); // key -> boolean
        this._triggerOnceFiredByKey = new Map(); // key -> boolean
        this._wasInTypeRange = null; // tracks percent in 0..100 for gating timeline callbacks

        this._top = 0;
        this._height = 0;
        this._calc = () => 0;
    }

    _getGateBoundary() {
        if (typeof this._type !== "string" || !GATED_TYPES.has(this._type)) {
            return { ok: true, boundary: null };
        }

        const inRange = this._percent >= 0 && this._percent <= 100;
        const prev = this._wasInTypeRange;
        this._wasInTypeRange = inRange;
        if (inRange) return { ok: true, boundary: null };

        if (prev == null || prev) {
            return { ok: false, boundary: this._percent > 100 ? 100 : 0 };
        }

        return { ok: false, boundary: null };
    }

    _measure(wh) {
        // Cache a document-relative top (offsetTop is only offsetParent-relative).
        this._top = docTop(this.el);
        this._height = this.el.clientHeight;

        const top = this._top;
        const h = this._height;

        // Custom range shorthand:
        // type: [startPct, endPct]
        //
        // Example: [50, 100]
        // - val=0 when element TOP is at 50% viewport
        // - val=100 when element BOTTOM is at 100% viewport
        //
        // Formula maps scrollY from startY..endY to 0..1:
        //   startY = top - (startPct/100)*wh
        //   endY   = (top + h) - (endPct/100)*wh
        //   calc   = (scrollY - startY) / (endY - startY)
        if (Array.isArray(this._type) && this._type.length >= 2) {
            const a = Number(this._type[0]);
            const b = Number(this._type[1]);
            const startPct = Number.isFinite(a) ? a : 0;
            const endPct = Number.isFinite(b) ? b : 100;
            const startY = top - (startPct / 100) * wh;
            const endY = top + h - (endPct / 100) * wh;
            const denom = endY - startY || 1;
            this._calc = (scrollY) => (scrollY - startY) / denom;
            return;
        }

        switch (this._type) {
            case "bottom-bottom":
                this._calc = (scrollY) => (scrollY - top + wh) / (h || 1);
                break;
            case "middle-middle":
                // 0 when element top hits viewport middle, 1 when element bottom hits viewport middle
                this._calc = (scrollY) => (scrollY - top + wh / 2) / (h || 1);
                break;
            case "middle-bottom":
                // 0 when element top hits viewport middle, 1 when element bottom hits viewport bottom
                this._calc = (scrollY) => (scrollY - top + wh / 2) / ((h - wh / 2) || 1);
                break;
            case "top-top":
                this._calc = (scrollY) => (scrollY - top) / (h || 1);
                break;
            case "bottom-top":
                this._calc = (scrollY) => (scrollY - top + wh) / ((h + wh) || 1);
                break;
            case "top-bottom":
            default:
                this._calc = (scrollY) => (-scrollY + top) / ((wh - h) || 1);
                break;
        }
    }

    _update(scrollY) {
        // percent 0..100 with 2 decimals
        this._percent = Math.round(this._calc(scrollY) * 10000) / 100;
        this._animation(this);
    }

    /**
     * Slice helper (equal ranges) to drive per-item animations.
     *
     * It does NOT rely on `timeline()` internally (so it still works when
     * the section is outside the 0..100 "type range" gate).
     *
     * @param {ArrayLike<Element>|Element[]} els
     * @param {(val:number, i:number, el:Element)=>void} onCurrent
     * @param {(val:number, i:number, el:Element)=>void} [onPrev]
     */
    snap(els, onCurrentOrHandlers, onPrevOrOpts) {
        // Supported call forms:
        // - snap(els, onCurrent)
        // - snap(els, onCurrent, onPrev)
        // - snap(els, { current, prev, onchange, onenter, onleave })
        const hasHandlers = !!onCurrentOrHandlers && typeof onCurrentOrHandlers === "object";
        const handlers = hasHandlers ? onCurrentOrHandlers : null;
        const onCurrent = hasHandlers ? handlers?.current : onCurrentOrHandlers;
        const onChange = hasHandlers ? handlers?.onchange : null;
        const onEnter = hasHandlers ? handlers?.onenter : null;
        const onLeave = hasHandlers ? handlers?.onleave : null;
        const onPrev =
            hasHandlers
                ? (typeof onPrevOrOpts === "function" ? onPrevOrOpts : handlers?.prev) // allow overriding prev via 3rd arg
                : typeof onPrevOrOpts === "function"
                    ? onPrevOrOpts
                    : null;

        const list = Array.isArray(els) ? els : Array.from(els || []);
        // Default to skipping the first element (common case: first item is the base layer).
        const skip = 1;
        const items = list.slice(skip);
        if (!items.length) return;
        const count = items.length;
        const totalCount = list.length;
        const span = 100;
        const slice = span / count;
        // For onchange(): we want i=0..(N-1) across the full scroll span.
        // With N stacked full-viewport panels, there are (N-1) scroll steps.
        const stepsAll = Math.max(1, totalCount - 1);
        const sliceAll = span / stepsAll;

        // Track enter/leave and active index changes per snap usage.
        if (!this._snapStates) this._snapStates = new Map();
        // IMPORTANT: `handlers` is often passed as an object literal inside the
        // per-frame callback, so it's not stable across frames. Prefer `els`
        // (NodeList/Element/Array reference) as a stable key. Allow overriding
        // with `handlers.key` when provided.
        const snapKey = handlers?.key ?? els ?? onCurrent;
        const prevState = this._snapStates.get(snapKey) ?? { inRange: null, activeI: null, lastPercent: null };

        const isGated = typeof this._type === "string" && GATED_TYPES.has(this._type);
        const inRangeNow = !isGated || (this._percent >= 0 && this._percent <= 100);
        const emitChange = (toI) => {
            if (!onChange) return;
            const lastI = Math.max(0, totalCount - 1);
            const nextI = Math.max(0, Math.min(lastI, Number(toI)));
            const prevI = prevState.activeI;
            if (prevI == null) {
                onChange(nextI, list[nextI] ?? null);
                return;
            }
            if (prevI === nextI) return;
            const step = nextI > prevI ? 1 : -1;
            for (let i = prevI + step; ; i += step) {
                onChange(i, list[i] ?? null);
                if (i === nextI) break;
            }
        };

        const getActiveI = (pct0to100, dir) => {
            if (pct0to100 <= 0) return 0;
            if (pct0to100 >= 100) return totalCount - 1;
            const eps = 1e-6;
            // bucket is 0..(N-2) across 0..100 (since stepsAll = N-1)
            const bucket = Math.floor((pct0to100 - eps) / sliceAll);
            // dir > 0 (down): switch when current val hits 100 (end of slice)
            if (dir > 0) return Math.max(0, Math.min(totalCount - 1, bucket));
            // dir < 0 (up): switch when current val hits 0 (start of slice)
            if (dir < 0) return Math.max(0, Math.min(totalCount - 1, bucket + 1));
            // dir unknown: stick to previous if available, otherwise default to down behavior
            if (prevState.activeI != null) return prevState.activeI;
            return Math.max(0, Math.min(totalCount - 1, bucket));
        };

        if (inRangeNow && prevState.inRange !== true) {
            // entering (also on first in-range call)
            const p0 = Math.max(0, Math.min(100, this._percent));
            const lp = Number(prevState.lastPercent);
            const fromBottom = Number.isFinite(lp) && lp > 100;
            const i0 = getActiveI(p0, fromBottom ? -1 : 1);
            const status = fromBottom ? "frombottom" : "fromtop";
            onEnter?.(i0, status);
        } else if (!inRangeNow && prevState.inRange === true) {
            // leaving
            const boundary = this._percent > 100 ? 100 : 0;
            const iPrev = prevState.activeI;
            const status = boundary === 0 ? "totop" : "tobottom";
            onLeave?.(iPrev ?? 0, status);

            // Force onchange to boundaries on exit (emit intermediate indices if needed).
            emitChange(boundary === 0 ? 0 : (totalCount - 1));
        }

        const gate = this._getGateBoundary();
        if (!gate.ok) {
            // Out of range: set a final boundary state ONCE on leave, then stop.
            // This avoids "pops" (no extra intermediate calls), while ensuring val is 0/100.
            if (gate.boundary != null && prevState.inRange === true) {
                const val = num0(gate.boundary);
                const lastI = Math.max(skip, totalCount - 1);
                const fromI = prevState.activeI != null ? prevState.activeI : skip;
                if (gate.boundary === 100) {
                    for (let i = Math.max(skip, fromI); i <= lastI; i++) {
                        const el = list[i] ?? null;
                        if (el) onCurrent?.(val, i, el);
                    }
                } else {
                    for (let i = Math.max(skip, fromI); i >= skip; i--) {
                        const el = list[i] ?? null;
                        if (el) onCurrent?.(val, i, el);
                    }
                }
            }
            // update state as out-of-range
            if (gate.boundary != null) {
                emitChange(gate.boundary === 0 ? 0 : (totalCount - 1));
                this._snapStates.set(snapKey, {
                    inRange: false,
                    activeI: gate.boundary === 0 ? 0 : (totalCount - 1),
                    lastPercent: this._percent,
                });
            } else {
                this._snapStates.set(snapKey, { inRange: false, activeI: prevState.activeI, lastPercent: this._percent });
            }
            return;
        }

        const p = this._percent;
        // active item index for onchange()
        const pClamped = Math.max(0, Math.min(100, p));
        const lp2 = Number(prevState.lastPercent);
        const dir = Number.isFinite(lp2) ? (p - lp2) : 0; // <0 up, >0 down
        const activeI = getActiveI(pClamped, dir);

        // When active section changes, finalize the previous "current" to a boundary value.
        // This ensures the section we leave ends at 0/100 depending on scroll direction,
        // even though current() is not called at t===0/1.
        if (prevState.activeI != null && prevState.activeI !== activeI && dir !== 0) {
            const boundaryVal = dir > 0 ? 100 : 0;
            if (dir > 0) {
                for (let i = prevState.activeI; i < activeI; i++) {
                    if (i < skip) continue;
                    const el = list[i] ?? null;
                    if (el) onCurrent?.(boundaryVal, i, el);
                }
            } else {
                for (let i = prevState.activeI; i > activeI; i--) {
                    if (i < skip) continue;
                    const el = list[i] ?? null;
                    if (el) onCurrent?.(boundaryVal, i, el);
                }
            }
        }

        emitChange(activeI);
        this._snapStates.set(snapKey, { inRange: true, activeI, lastPercent: this._percent });

        const spanSlices = Math.max(1, Number(handlers?.span ?? 1) || 1);
        for (let index = 0; index < items.length; index++) {
            const s = index * slice;
            const e = Math.min(100, s + slice * spanSlices);
            const denom = e - s || 1;
            let t = (p - s) / denom; // can be <0 / >1
            if (t < 0) t = 0;
            if (t > 1) t = 1;
            const localVal = num0(t * 100);
            const i = index + skip;
            const el = items[index];
            const prevEl = list[i - 1] ?? null;
            onCurrent?.(localVal, i, el);
            if (onPrev && t > 0 && prevEl) onPrev(localVal, i, prevEl);
        }
    }

    /**
     * Calls `onscroll` with a clamped value from 0 to 100.
     */
    timeline(start, end, onscroll) {
        const s = Number(start);
        const e = Number(end);
        const denom = e - s || 1;
        const raw = (this._percent - s) / denom;
        let value = raw;
        if (this._percent < s) value = 0;
        if (this._percent > e) value = 1;
        const out = num0(value); // clamped 0..1
        const rawOut = num0(raw); // can be <0 / >1
        this._lastTimelineValue = out;
        this._lastTimelineRawValue = rawOut;
        const gate = this._getGateBoundary();
        if (!gate.ok) {
            if (gate.boundary != null) onscroll(num0(this._lastTimelineValue) * 100);
            return;
        }
        onscroll(out * 100);
    }

    /**
     * In/Out timeline helper:
     * - 0 -> 100 during [inStart, inEnd]
     * - stays 100 between (inEnd .. outStart)
     * - 100 -> 0 during [outStart, outEnd]
     * - 0 outside
     *
     * @example
     * e.timelineInOut(0, 20, 80, 100, (val) => { ... });
     */
    timelineInOut(inStart, inEnd, outStart, outEnd, onscroll) {
        const a = Number(inStart);
        const b = Number(inEnd);
        const c = Number(outStart);
        const d = Number(outEnd);
        const p = this._percent;

        let out = 0;
        if (p <= a) out = 0;
        else if (p >= d) out = 0;
        else if (p >= b && p <= c) out = 1;
        else if (p > a && p < b) {
            const denom = b - a || 1;
            out = (p - a) / denom;
        } else if (p > c && p < d) {
            const denom = d - c || 1;
            out = 1 - (p - c) / denom;
        }

        // Reuse the same gating behavior as timeline() and emit one last 0/100 boundary.
        // We store raw as "virtual timeline" value for compatibility with toggle/trigger logic.
        this._lastTimelineValue = num0(Math.max(0, Math.min(1, out)));
        this._lastTimelineRawValue = num0(out);

        const gate = this._getGateBoundary();
        if (!gate.ok) {
            if (gate.boundary != null) onscroll(num0(this._lastTimelineValue) * 100);
            return;
        }
        onscroll(num0(this._lastTimelineValue) * 100);
    }

    /**
     * Fires only when crossing the threshold.
     * By default it uses the last value produced by timeline() (0..1),
     * falling back to the raw percent (0..1) if timeline() wasn't called yet.
     *
     * @example
     * e.timeline(0, 100, (val) => { ... });
     * e.toggle(50, (isAbove) => { ... });
     */
    toggle(threshold, onChange) {
        let t = Number(threshold);
        // Backward compatible thresholds:
        // - if you pass 0..1 -> treated as-is
        // - if you pass 0..100 -> normalized to 0..1
        if (Number.isFinite(t) && t > 1) t = t / 100;
        // Use the raw timeline value when available so thresholds like 0/100 can
        // still "cross" even though the timeline output is clamped.
        const current = this._lastTimelineRawValue ?? this._lastTimelineValue ?? (this._percent / 100);
        const now = Number(current) >= t;
        const key = String(t);
        const prev = this._triggerPrevByKey.get(key);
        if (prev !== now) {
            this._triggerPrevByKey.set(key, now);
            onChange(now, current);
        }
    }

    /**
     * Fires once (one-shot) when reaching the threshold (>=).
     *
     * @example
     * e.trigger(50, (isAbove) => { ... }); // runs once when it becomes true
     */
    trigger(threshold, onFire) {
        let t = Number(threshold);
        if (Number.isFinite(t) && t > 1) t = t / 100;
        const current = this._lastTimelineRawValue ?? this._lastTimelineValue ?? (this._percent / 100);
        const now = Number(current) >= t;
        if (!now) return;
        const key = String(t);
        if (this._triggerOnceFiredByKey.get(key)) return;
        this._triggerOnceFiredByKey.set(key, true);
        onFire(true, current);
    }
}


class Toggle {
    constructor() {
        this._prev = null;
    }
    trig(percent, value, func) {
        const now = Number(percent) > Number(value);
        if (now !== this._prev) {
            func(now);
            this._prev = now;
        }
    }
}

/**
 * Stagger helper (callable):
 *
 * One-shot form (convenience, avoid calling every frame):
 * - stagger(p, val, opts?)
 */
function stagger(target = null, val = 0, opts = {}) {
    const v = Number(val);
    const value = Number.isFinite(v) ? v : 0;
    const o = opts || {};

    // Fast path for Element roots with caching (avoid querySelectorAll every call)
    if (target?.tagName) {
        if (!stagger._cache) stagger._cache = new WeakMap();
        // Default supports both text chars (.char) and generic list items (.item).
        const selector = String(o?.selector ?? "[data-progress-item], .char, .item");
        const wantedRoot = o?.rootEl ?? target;
        const cached = stagger._cache.get(target);
        let els = cached?.els ?? null;
        let rootEl = cached?.rootEl ?? null;
        if (!cached || cached.selector !== selector || rootEl !== wantedRoot || !els?.length) {
            const found = Array.from(target?.querySelectorAll?.(selector) ?? []);
            els = found.length ? found : [target];
            rootEl = wantedRoot;
            stagger._cache.set(target, { selector, els, rootEl });
        }
        progressChars(els, value, { ...o, rootEl });
        return;
    }

    // Fallback for arrays/NodeLists passed directly
    if (!target?.tagName && typeof target?.length === "number") {
        const list = Array.from(target || []);
        // When a list is provided, set vars on the list itself (per-item),
        // not only on a single parent (items may not share a parent).
        progressChars(list, value, { ...o, rootEl: o?.rootEl ?? list });
        return;
    }

    // Fallback: treat as single element
    if (target) progressChars([target], value, o);
}

function clamp01(x) {
    const n = Number(x);
    if (!Number.isFinite(n)) return 0;
    return Math.min(1, Math.max(0, n));
}



/**
 * Helper: progressive color interpolation along 0..100 (per character).
 *
 * Each character transitions from `fromColor` to `toColor` as the overall
 * progress advances, distributed evenly across the characters.
 */
function progressChars(items, val, opts = {}) {
    const list = Array.isArray(items) ? items : Array.from(items || []);
    const raw = Number(val);
    const progress = clamp01(raw >= 0 && raw <= 1 ? raw : (raw / 100));

    if (!list.length) return;

    // Optimized mode: write O(1) vars on root, let CSS compute per-char --progress.
    // Root vars:
    // - --p (0..1) overall progress
    // - --denom (n-1)
    // - --softness (segments)
    //
    // Char vars (set once):
    // - --i (0..denom)
    const n = list.length;
    const denom = Math.max(1, n - 1);

    const hasSoftnessOpt = opts && (opts.softness != null || opts.stagger != null);
    let softness = Number(opts.softness);
    if (!Number.isFinite(softness)) softness = Number(opts.stagger); // legacy alias

    if (Number.isFinite(softness)) {
        // If softness is given as 0..1, treat it as "stagger span" fraction
        // and convert it to a segment softness.
        if (softness > 0 && softness < 1) softness = (denom * (1 - softness)) / softness;
    } else {
        softness = 10;
    }

    softness = Math.max(0.0001, softness);

    // Root(s) for CSS vars:
    // - if opts.rootEl is an Element => vars are written once on that root (fast path)
    // - if opts.rootEl is list-like => vars are written on each root element (safe when items don't share a parent)
    const roots =
        opts.rootEl && !opts.rootEl?.tagName && typeof opts.rootEl?.length === "number"
            ? Array.from(opts.rootEl || []).filter(Boolean)
            : [
                opts.rootEl ??
                list[0]?.closest?.("p") ??
                list[0]?.parentElement ??
                list[0],
            ].filter(Boolean);

    // One-time init: set indices on items.
    // - single-root mode: track init per root (fast)
    // - multi-root mode: track init per item (safe)
    if (!progressChars._initedRoot) progressChars._initedRoot = new WeakMap();
    if (!progressChars._initedItem) progressChars._initedItem = new WeakSet();
    const initedRoot = progressChars._initedRoot;
    const initedItem = progressChars._initedItem;

    if (roots.length === 1) {
        const root = roots[0];
        if (!initedRoot.get(root)) {
            for (let i = 0; i < n; i++) {
                const ch = list[i];
                if (!ch) continue;
                style.var(ch, String(i), "--i");
            }
            initedRoot.set(root, true);
        }
    } else {
        for (let i = 0; i < n; i++) {
            const ch = list[i];
            if (!ch || initedItem.has(ch)) continue;
            style.var(ch, String(i), "--i");
            initedItem.add(ch);
        }
    }

    // Update root vars:
    // - 1 root => O(1)
    // - multiple roots => O(roots.length)
    for (const root of roots) {
        style.var(root, String(progress), "--p");
        style.var(root, String(denom), "--denom");
        if (hasSoftnessOpt) {
            style.var(root, String(softness), "--softness");
        }
    }
}



// Backward-compatible alias: keep `SectionAnimate` export for older imports.
export { ScrollDriver, ScrollDriver as SectionAnimate, stagger, style, cubicBezier, upDown };
