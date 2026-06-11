function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == typeof i ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != typeof t || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != typeof i) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function num0(x) {
  const n = Number(x);
  return Number.isFinite(n) ? n : 0;
}
function docTop(el) {
  let y = 0;
  for (let n = el; n; n = n.offsetParent) y += n.offsetTop || 0;
  return y;
}
const easeInOutCubic = t => t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
function upDown(val, inMin = 0, inMax = 100, outMax = 100) {
  const v = num0(val);
  if (arguments.length === 1 && v >= 0 && v <= 1) {
    return v <= 0.5 ? v * 2 : (1 - v) * 2;
  }
  const denom = num0(inMax) - num0(inMin) || 1;
  let t = (v - num0(inMin)) / denom;
  if (t < 0) t = 0;
  if (t > 1) t = 1;
  const u = t <= 0.5 ? t * 2 : (1 - t) * 2;
  return u * num0(outMax);
}
function cubicBezier(p1x, p1y, p2x, p2y) {
  const cx = 3 * p1x;
  const bx = 3 * (p2x - p1x) - cx;
  const ax = 1 - cx - bx;
  const cy = 3 * p1y;
  const by = 3 * (p2y - p1y) - cy;
  const ay = 1 - cy - by;
  const sampleX = t => ((ax * t + bx) * t + cx) * t;
  const sampleY = t => ((ay * t + by) * t + cy) * t;
  const sampleDerivX = t => (3 * ax * t + 2 * bx) * t + cx;
  function solveTforX(x) {
    let t = x;
    for (let i = 0; i < 8; i++) {
      const x2 = sampleX(t) - x;
      const d2 = sampleDerivX(t);
      if (Math.abs(x2) < 1e-6) return t;
      if (Math.abs(d2) < 1e-6) break;
      t = t - x2 / d2;
    }
    let t0 = 0,
      t1 = 1;
    t = x;
    for (let i = 0; i < 20; i++) {
      const x2 = sampleX(t);
      if (Math.abs(x2 - x) < 1e-6) return t;
      if (x > x2) t0 = t;else t1 = t;
      t = (t0 + t1) / 2;
    }
    return t;
  }
  return function ease(t) {
    const x = Math.min(1, Math.max(0, t));
    return sampleY(solveTforX(x));
  };
}
const GATED_TYPES = new Set(["bottom-bottom", "bottom-top", "top-top", "top-bottom", "middle-middle", "middle-bottom"]);
function createStyleCache() {
  const lastTransform = new WeakMap();
  const lastClipPath = new WeakMap();
  const lastOpacity = new WeakMap();
  const lastScale = new WeakMap();
  const lastColor = new WeakMap();
  const lastVars = new WeakMap();
  const set = (map, el, prop, value) => {
    if (!el) return;
    const v = String(value);
    if (map.get(el) === v) return;
    map.set(el, v);
    el.style[prop] = v;
  };
  return {
    translate(el, x = 0, y = 0, z = 0) {
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
      const p = Number(percent);
      const s = Number(start);
      const e = Number(end);
      const denom = e - s || 1;
      let t = (p - s) / denom;
      if (t < 0) t = 0;
      if (t > 1) t = 1;
      const v = String(t * 100);
      this.var(el, v, name);
    },
    inset(el, top = 0, right = 0, bottom = 0, left = 0) {
      const t = typeof top === "number" ? `${top}px` : String(top);
      const r = typeof right === "number" ? `${right}px` : String(right);
      const b = typeof bottom === "number" ? `${bottom}px` : String(bottom);
      const l = typeof left === "number" ? `${left}px` : String(left);
      set(lastClipPath, el, "clipPath", `inset(${t} ${r} ${b} ${l})`);
    },
    polygonFromInset(el, top = 0, right = 0, bottom = 0, left = 0) {
      const t = typeof top === "number" ? `${top}px` : String(top);
      const r = typeof right === "number" ? `${right}px` : String(right);
      const b = typeof bottom === "number" ? `${bottom}px` : String(bottom);
      const l = typeof left === "number" ? `${left}px` : String(left);
      set(lastClipPath, el, "clipPath", `polygon(${l} ${t}, calc(100% - ${r}) ${t}, calc(100% - ${r}) calc(100% - ${b}), ${l} calc(100% - ${b}))`);
    },
    opacity(el, value) {
      set(lastOpacity, el, "opacity", value);
    },
    color(el, value) {
      set(lastColor, el, "color", value);
    },
    scale(el, value) {
      set(lastScale, el, "scale", value);
    }
  };
}
const style = createStyleCache();
class ScrollDriver {
  constructor() {
    this._wh = window.innerHeight;
    this._sections = [];
    this._enabled = false;
    this._latestScrollY = window.scrollY || 0;
    this._rafId = null;
    this._activeSections = new Set();
    this._io = new IntersectionObserver(entries => {
      let needsUpdate = false;
      for (const entry of entries) {
        const s = this._sectionsByEl.get(entry.target);
        if (!s) continue;
        entry.isIntersecting ? s.el.classList.add("viewed") : s.el.classList.remove("viewed");
        if (entry.isIntersecting) {
          if (!this._activeSections.has(s)) {
            this._activeSections.add(s);
            needsUpdate = true;
          }
        } else {
          if (this._activeSections.has(s)) {
            if (this._enabled) {
              try {
                s._update(this._latestScrollY);
              } catch (_unused) {}
            }
            this._activeSections.delete(s);
            needsUpdate = true;
          }
        }
        if (entry.isIntersecting) needsUpdate = true;
      }
      if (this._enabled && needsUpdate) this._schedule();
    }, {
      rootMargin: "100px 0px 100px 0px",
      threshold: 0
    });
    this._sectionsByEl = new Map();
    this._onResize = () => {
      this._wh = window.innerHeight;
      for (const s of this._sections) s._measure(this._wh);
      this._schedule();
    };
    this._onScroll = () => {
      this.onScroll(window.scrollY || 0);
    };
  }
  onScroll(scrollY) {
    const y = Number(scrollY);
    this._latestScrollY = Number.isFinite(y) ? y : 0;
    this._schedule();
  }
  _schedule() {
    if (this._rafId != null) return;
    this._rafId = requestAnimationFrame(() => {
      this._rafId = null;
      const y = this._latestScrollY;
      const list = this._activeSections.size ? this._activeSections : this._sections;
      for (const s of list) s._update(y);
    });
  }
  add(el, type = "top-bottom", animation) {
    if (!el) return null;
    const s = new SectionSection(el, type, animation, () => this._wh);
    this._sections.push(s);
    this._sectionsByEl.set(el, s);
    this._io.observe(el);
    s._measure(this._wh);
    return s;
  }
  enable() {
    if (this._enabled) return;
    this._enabled = true;
    window.addEventListener("resize", this._onResize, {
      passive: true
    });
    window.addEventListener("scroll", this._onScroll, {
      passive: true
    });
    this._onResize();
    this._onScroll();
  }
  disable() {
    if (!this._enabled) return;
    this._enabled = false;
    window.removeEventListener("resize", this._onResize);
    window.removeEventListener("scroll", this._onScroll);
    if (this._rafId != null) {
      cancelAnimationFrame(this._rafId);
      this._rafId = null;
    }
  }
}
class SectionSection {
  constructor(el, type, animation, getWh) {
    this.el = el;
    this._type = type;
    this._animation = typeof animation === "function" ? animation : () => {};
    this._getWh = getWh;
    this._percent = 0;
    this._lastTimelineValue = null;
    this._lastTimelineRawValue = null;
    this._triggerPrevByKey = new Map();
    this._triggerOnceFiredByKey = new Map();
    this._wasInTypeRange = null;
    this._top = 0;
    this._height = 0;
    this._calc = () => 0;
  }
  _getGateBoundary() {
    if (typeof this._type !== "string" || !GATED_TYPES.has(this._type)) {
      return {
        ok: true,
        boundary: null
      };
    }
    const inRange = this._percent >= 0 && this._percent <= 100;
    const prev = this._wasInTypeRange;
    this._wasInTypeRange = inRange;
    if (inRange) return {
      ok: true,
      boundary: null
    };
    if (prev == null || prev) {
      return {
        ok: false,
        boundary: this._percent > 100 ? 100 : 0
      };
    }
    return {
      ok: false,
      boundary: null
    };
  }
  _measure(wh) {
    this._top = docTop(this.el);
    this._height = this.el.clientHeight;
    const top = this._top;
    const h = this._height;
    if (Array.isArray(this._type) && this._type.length >= 2) {
      const a = Number(this._type[0]);
      const b = Number(this._type[1]);
      const startPct = Number.isFinite(a) ? a : 0;
      const endPct = Number.isFinite(b) ? b : 100;
      const startY = top - startPct / 100 * wh;
      const endY = top + h - endPct / 100 * wh;
      const denom = endY - startY || 1;
      this._calc = scrollY => (scrollY - startY) / denom;
      return;
    }
    switch (this._type) {
      case "bottom-bottom":
        this._calc = scrollY => (scrollY - top + wh) / (h || 1);
        break;
      case "middle-middle":
        this._calc = scrollY => (scrollY - top + wh / 2) / (h || 1);
        break;
      case "middle-bottom":
        this._calc = scrollY => (scrollY - top + wh / 2) / (h - wh / 2 || 1);
        break;
      case "top-top":
        this._calc = scrollY => (scrollY - top) / (h || 1);
        break;
      case "bottom-top":
        this._calc = scrollY => (scrollY - top + wh) / (h + wh || 1);
        break;
      case "top-bottom":
      default:
        this._calc = scrollY => (-scrollY + top) / (wh - h || 1);
        break;
    }
  }
  _update(scrollY) {
    this._percent = Math.round(this._calc(scrollY) * 10000) / 100;
    this._animation(this);
  }
  snap(els, onCurrentOrHandlers, onPrevOrOpts) {
    var _ref, _handlers$key, _this$_snapStates$get, _handlers$span;
    const hasHandlers = !!onCurrentOrHandlers && typeof onCurrentOrHandlers === "object";
    const handlers = hasHandlers ? onCurrentOrHandlers : null;
    const onCurrent = hasHandlers ? handlers === null || handlers === void 0 ? void 0 : handlers.current : onCurrentOrHandlers;
    const onChange = hasHandlers ? handlers === null || handlers === void 0 ? void 0 : handlers.onchange : null;
    const onEnter = hasHandlers ? handlers === null || handlers === void 0 ? void 0 : handlers.onenter : null;
    const onLeave = hasHandlers ? handlers === null || handlers === void 0 ? void 0 : handlers.onleave : null;
    const onPrev = hasHandlers ? typeof onPrevOrOpts === "function" ? onPrevOrOpts : handlers === null || handlers === void 0 ? void 0 : handlers.prev : typeof onPrevOrOpts === "function" ? onPrevOrOpts : null;
    const list = Array.isArray(els) ? els : Array.from(els || []);
    const skip = 1;
    const items = list.slice(skip);
    if (!items.length) return;
    const count = items.length;
    const totalCount = list.length;
    const span = 100;
    const slice = span / count;
    const stepsAll = Math.max(1, totalCount - 1);
    const sliceAll = span / stepsAll;
    if (!this._snapStates) this._snapStates = new Map();
    const snapKey = (_ref = (_handlers$key = handlers === null || handlers === void 0 ? void 0 : handlers.key) !== null && _handlers$key !== void 0 ? _handlers$key : els) !== null && _ref !== void 0 ? _ref : onCurrent;
    const prevState = (_this$_snapStates$get = this._snapStates.get(snapKey)) !== null && _this$_snapStates$get !== void 0 ? _this$_snapStates$get : {
      inRange: null,
      activeI: null,
      lastPercent: null
    };
    const isGated = typeof this._type === "string" && GATED_TYPES.has(this._type);
    const inRangeNow = !isGated || this._percent >= 0 && this._percent <= 100;
    const emitChange = toI => {
      if (!onChange) return;
      const lastI = Math.max(0, totalCount - 1);
      const nextI = Math.max(0, Math.min(lastI, Number(toI)));
      const prevI = prevState.activeI;
      if (prevI == null) {
        var _list$nextI;
        onChange(nextI, (_list$nextI = list[nextI]) !== null && _list$nextI !== void 0 ? _list$nextI : null);
        return;
      }
      if (prevI === nextI) return;
      const step = nextI > prevI ? 1 : -1;
      for (let i = prevI + step;; i += step) {
        var _list$i;
        onChange(i, (_list$i = list[i]) !== null && _list$i !== void 0 ? _list$i : null);
        if (i === nextI) break;
      }
    };
    const getActiveI = (pct0to100, dir) => {
      if (pct0to100 <= 0) return 0;
      if (pct0to100 >= 100) return totalCount - 1;
      const eps = 1e-6;
      const bucket = Math.floor((pct0to100 - eps) / sliceAll);
      if (dir > 0) return Math.max(0, Math.min(totalCount - 1, bucket));
      if (dir < 0) return Math.max(0, Math.min(totalCount - 1, bucket + 1));
      if (prevState.activeI != null) return prevState.activeI;
      return Math.max(0, Math.min(totalCount - 1, bucket));
    };
    if (inRangeNow && prevState.inRange !== true) {
      const p0 = Math.max(0, Math.min(100, this._percent));
      const lp = Number(prevState.lastPercent);
      const fromBottom = Number.isFinite(lp) && lp > 100;
      const i0 = getActiveI(p0, fromBottom ? -1 : 1);
      const status = fromBottom ? "frombottom" : "fromtop";
      onEnter === null || onEnter === void 0 || onEnter(i0, status);
    } else if (!inRangeNow && prevState.inRange === true) {
      const boundary = this._percent > 100 ? 100 : 0;
      const iPrev = prevState.activeI;
      const status = boundary === 0 ? "totop" : "tobottom";
      onLeave === null || onLeave === void 0 || onLeave(iPrev !== null && iPrev !== void 0 ? iPrev : 0, status);
      emitChange(boundary === 0 ? 0 : totalCount - 1);
    }
    const gate = this._getGateBoundary();
    if (!gate.ok) {
      if (gate.boundary != null && prevState.inRange === true) {
        const val = num0(gate.boundary);
        const lastI = Math.max(skip, totalCount - 1);
        const fromI = prevState.activeI != null ? prevState.activeI : skip;
        if (gate.boundary === 100) {
          for (let i = Math.max(skip, fromI); i <= lastI; i++) {
            var _list$i2;
            const el = (_list$i2 = list[i]) !== null && _list$i2 !== void 0 ? _list$i2 : null;
            if (el) onCurrent === null || onCurrent === void 0 || onCurrent(val, i, el);
          }
        } else {
          for (let i = Math.max(skip, fromI); i >= skip; i--) {
            var _list$i3;
            const el = (_list$i3 = list[i]) !== null && _list$i3 !== void 0 ? _list$i3 : null;
            if (el) onCurrent === null || onCurrent === void 0 || onCurrent(val, i, el);
          }
        }
      }
      if (gate.boundary != null) {
        emitChange(gate.boundary === 0 ? 0 : totalCount - 1);
        this._snapStates.set(snapKey, {
          inRange: false,
          activeI: gate.boundary === 0 ? 0 : totalCount - 1,
          lastPercent: this._percent
        });
      } else {
        this._snapStates.set(snapKey, {
          inRange: false,
          activeI: prevState.activeI,
          lastPercent: this._percent
        });
      }
      return;
    }
    const p = this._percent;
    const pClamped = Math.max(0, Math.min(100, p));
    const lp2 = Number(prevState.lastPercent);
    const dir = Number.isFinite(lp2) ? p - lp2 : 0;
    const activeI = getActiveI(pClamped, dir);
    if (prevState.activeI != null && prevState.activeI !== activeI && dir !== 0) {
      const boundaryVal = dir > 0 ? 100 : 0;
      if (dir > 0) {
        for (let i = prevState.activeI; i < activeI; i++) {
          var _list$i4;
          if (i < skip) continue;
          const el = (_list$i4 = list[i]) !== null && _list$i4 !== void 0 ? _list$i4 : null;
          if (el) onCurrent === null || onCurrent === void 0 || onCurrent(boundaryVal, i, el);
        }
      } else {
        for (let i = prevState.activeI; i > activeI; i--) {
          var _list$i5;
          if (i < skip) continue;
          const el = (_list$i5 = list[i]) !== null && _list$i5 !== void 0 ? _list$i5 : null;
          if (el) onCurrent === null || onCurrent === void 0 || onCurrent(boundaryVal, i, el);
        }
      }
    }
    emitChange(activeI);
    this._snapStates.set(snapKey, {
      inRange: true,
      activeI,
      lastPercent: this._percent
    });
    const spanSlices = Math.max(1, Number((_handlers$span = handlers === null || handlers === void 0 ? void 0 : handlers.span) !== null && _handlers$span !== void 0 ? _handlers$span : 1) || 1);
    for (let index = 0; index < items.length; index++) {
      var _list;
      const s = index * slice;
      const e = Math.min(100, s + slice * spanSlices);
      const denom = e - s || 1;
      let t = (p - s) / denom;
      if (t < 0) t = 0;
      if (t > 1) t = 1;
      const localVal = num0(t * 100);
      const i = index + skip;
      const el = items[index];
      const prevEl = (_list = list[i - 1]) !== null && _list !== void 0 ? _list : null;
      onCurrent === null || onCurrent === void 0 || onCurrent(localVal, i, el);
      if (onPrev && t > 0 && prevEl) onPrev(localVal, i, prevEl);
    }
  }
  timeline(start, end, onscroll) {
    const s = Number(start);
    const e = Number(end);
    const denom = e - s || 1;
    const raw = (this._percent - s) / denom;
    let value = raw;
    if (this._percent < s) value = 0;
    if (this._percent > e) value = 1;
    const out = num0(value);
    const rawOut = num0(raw);
    this._lastTimelineValue = out;
    this._lastTimelineRawValue = rawOut;
    const gate = this._getGateBoundary();
    if (!gate.ok) {
      if (gate.boundary != null) onscroll(num0(gate.boundary) / 100);
      return;
    }
    onscroll(out);
  }
  timelineInOut(inStart, inEnd, outStart, outEnd, onscroll) {
    const a = Number(inStart);
    const b = Number(inEnd);
    const c = Number(outStart);
    const d = Number(outEnd);
    const p = this._percent;
    let out = 0;
    if (p <= a) out = 0;else if (p >= d) out = 0;else if (p >= b && p <= c) out = 1;else if (p > a && p < b) {
      const denom = b - a || 1;
      out = (p - a) / denom;
    } else if (p > c && p < d) {
      const denom = d - c || 1;
      out = 1 - (p - c) / denom;
    }
    this._lastTimelineValue = num0(Math.max(0, Math.min(1, out)));
    this._lastTimelineRawValue = num0(out);
    const gate = this._getGateBoundary();
    if (!gate.ok) {
      if (gate.boundary != null) onscroll(num0(gate.boundary) / 100);
      return;
    }
    onscroll(num0(this._lastTimelineValue));
  }
  toggle(threshold, onChange) {
    var _ref2, _this$_lastTimelineRa;
    let t = Number(threshold);
    if (Number.isFinite(t) && t > 1) t = t / 100;
    const current = (_ref2 = (_this$_lastTimelineRa = this._lastTimelineRawValue) !== null && _this$_lastTimelineRa !== void 0 ? _this$_lastTimelineRa : this._lastTimelineValue) !== null && _ref2 !== void 0 ? _ref2 : this._percent / 100;
    const now = Number(current) >= t;
    const key = String(t);
    const prev = this._triggerPrevByKey.get(key);
    if (prev !== now) {
      this._triggerPrevByKey.set(key, now);
      onChange(now, current);
    }
  }
  trigger(threshold, onFire) {
    var _ref3, _this$_lastTimelineRa2;
    let t = Number(threshold);
    if (Number.isFinite(t) && t > 1) t = t / 100;
    const current = (_ref3 = (_this$_lastTimelineRa2 = this._lastTimelineRawValue) !== null && _this$_lastTimelineRa2 !== void 0 ? _this$_lastTimelineRa2 : this._lastTimelineValue) !== null && _ref3 !== void 0 ? _ref3 : this._percent / 100;
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
function stagger(target = null, val = 0, opts = {}) {
  const v = Number(val);
  const value = Number.isFinite(v) ? v : 0;
  const o = opts || {};
  if (target !== null && target !== void 0 && target.tagName) {
    var _o$selector, _o$rootEl, _cached$els, _cached$rootEl, _els;
    if (!stagger._cache) stagger._cache = new WeakMap();
    const selector = String((_o$selector = o === null || o === void 0 ? void 0 : o.selector) !== null && _o$selector !== void 0 ? _o$selector : "[data-progress-item], .char, .item");
    const wantedRoot = (_o$rootEl = o === null || o === void 0 ? void 0 : o.rootEl) !== null && _o$rootEl !== void 0 ? _o$rootEl : target;
    const cached = stagger._cache.get(target);
    let els = (_cached$els = cached === null || cached === void 0 ? void 0 : cached.els) !== null && _cached$els !== void 0 ? _cached$els : null;
    let rootEl = (_cached$rootEl = cached === null || cached === void 0 ? void 0 : cached.rootEl) !== null && _cached$rootEl !== void 0 ? _cached$rootEl : null;
    if (!cached || cached.selector !== selector || rootEl !== wantedRoot || !((_els = els) !== null && _els !== void 0 && _els.length)) {
      var _target$querySelector, _target$querySelector2;
      const found = Array.from((_target$querySelector = target === null || target === void 0 || (_target$querySelector2 = target.querySelectorAll) === null || _target$querySelector2 === void 0 ? void 0 : _target$querySelector2.call(target, selector)) !== null && _target$querySelector !== void 0 ? _target$querySelector : []);
      els = found.length ? found : [target];
      rootEl = wantedRoot;
      stagger._cache.set(target, {
        selector,
        els,
        rootEl
      });
    }
    progressChars(els, value, _objectSpread(_objectSpread({}, o), {}, {
      rootEl
    }));
    return;
  }
  if (!(target !== null && target !== void 0 && target.tagName) && typeof (target === null || target === void 0 ? void 0 : target.length) === "number") {
    var _o$rootEl2;
    const list = Array.from(target || []);
    progressChars(list, value, _objectSpread(_objectSpread({}, o), {}, {
      rootEl: (_o$rootEl2 = o === null || o === void 0 ? void 0 : o.rootEl) !== null && _o$rootEl2 !== void 0 ? _o$rootEl2 : list
    }));
    return;
  }
  if (target) progressChars([target], value, o);
}
function clamp01(x) {
  const n = Number(x);
  if (!Number.isFinite(n)) return 0;
  return Math.min(1, Math.max(0, n));
}
function progressChars(items, val, opts = {}) {
  var _opts$rootEl, _opts$rootEl2, _ref4, _ref5, _opts$rootEl3, _list$, _list$$closest, _list$2;
  const list = Array.isArray(items) ? items : Array.from(items || []);
  const raw = Number(val);
  const progress = clamp01(raw >= 0 && raw <= 1 ? raw : raw / 100);
  if (!list.length) return;
  const n = list.length;
  const denom = Math.max(1, n - 1);
  const hasSoftnessOpt = opts && (opts.softness != null || opts.stagger != null);
  let softness = Number(opts.softness);
  if (!Number.isFinite(softness)) softness = Number(opts.stagger);
  if (Number.isFinite(softness)) {
    if (softness > 0 && softness < 1) softness = denom * (1 - softness) / softness;
  } else {
    softness = 10;
  }
  softness = Math.max(0.0001, softness);
  const roots = opts.rootEl && !((_opts$rootEl = opts.rootEl) !== null && _opts$rootEl !== void 0 && _opts$rootEl.tagName) && typeof ((_opts$rootEl2 = opts.rootEl) === null || _opts$rootEl2 === void 0 ? void 0 : _opts$rootEl2.length) === "number" ? Array.from(opts.rootEl || []).filter(Boolean) : [(_ref4 = (_ref5 = (_opts$rootEl3 = opts.rootEl) !== null && _opts$rootEl3 !== void 0 ? _opts$rootEl3 : (_list$ = list[0]) === null || _list$ === void 0 || (_list$$closest = _list$.closest) === null || _list$$closest === void 0 ? void 0 : _list$$closest.call(_list$, "p")) !== null && _ref5 !== void 0 ? _ref5 : (_list$2 = list[0]) === null || _list$2 === void 0 ? void 0 : _list$2.parentElement) !== null && _ref4 !== void 0 ? _ref4 : list[0]].filter(Boolean);
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
  for (const root of roots) {
    style.var(root, String(progress), "--p");
    style.var(root, String(denom), "--denom");
    if (hasSoftnessOpt) {
      style.var(root, String(softness), "--softness");
    }
  }
}
export { ScrollDriver, ScrollDriver as SectionAnimate, stagger, style, cubicBezier, upDown };