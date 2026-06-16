# `scrollAnimate.js`

Scroll-driven animation helpers for this project (no GSAP required).


```js
import { ScrollDriver, style, stagger, cubicBezier } from "../../modules/scrollAnimate.js";

export default (el, ctx) => {
    const sectionAnimate = new ScrollDriver();

    sectionAnimate.add(el, "top-bottom", (e) => {
        e.timeline(0, 100, (val) => {
        // val is always a number in 0..100
        });
    });

    sectionAnimate.enable();

    return () => {
        sectionAnimate.destroy();
    };
};
```
- **`ScrollDriver`**: controller you create per view/module
- **`style`**: cached style-writer helpers (`translate`, `inset`, `opacity`, `var`, …)
- **`stagger`**: callable helper to drive “progress across many items”
- **`cubicBezier`**: easing function factory for JS eases

## 1. ScrollDriver

### sectionAnimate.add(el, type, animation)

- **`el`**: the section root element
- **`type`**: determines how `e._percent` is computed (see “Types”)
    - `"top-bottom"` (default)
    - `"bottom-bottom"`
    - `"bottom-top"`
    - `"top-top"`
    - `"middle-middle"`
    - `"middle-bottom"`
    - `[startPct, endPct]` (viewport percent)


- **`animation(e)`**: called every RAF tick; `e` is the section helper object

> Types :<br>
><b>startPct</b>: % du viewport où le top de l’élément doit arriver pour que la >progression soit 0.<br>
><b>endPct</b>: % du viewport où le bottom de l’élément doit arriver pour que la progression soit 100.

### sectionAnimate.onScroll(scrollY)

Schedules a RAF update for an explicit scroll position. Most modules do not need
to call it because `ScrollDriver` automatically uses Lenis when available,
otherwise native scroll.

### sectionAnimate.refresh()

Re-measures sections and reapplies the current scroll position. Use it after a
module changes its own layout.

### sectionAnimate.enable() / disable() / destroy()

`enable()` registers the driver with the shared scroll/resize controller.
`disable()` pauses it. `destroy()` additionally disconnects observers, removes
`.viewed`, and releases registered sections.


## The `e` helper (passed to `animation(e)`)


### e.timeline(start, end, onscroll)

`onscroll(val)` receives a **number** in 0..100.

<hr>

### e.timelineInOut(inStart, inEnd, outStart, outEnd, onscroll)


- 0 → 100 during `[inStart, inEnd]`
- stays 100 between `inEnd..outStart`
- 100 → 0 during `[outStart, outEnd]`
- 0 outside

<hr>

### e.snap(els, handlersOrCurrent, onPrev?)

Slices the section range into equal intervals and gives each element a **local** 0..100 value.

```js
sectionAnimate.add(el, "top-bottom", (e) => {
      e.snap(items, {
        onchange: (i, item) => {
          // i = active index in the original list (includes the skipped first item)
          // item = active element (or null)
        },
        onenter: (i, status) => {
          // status: "fromtop" | "frombottom"
        },
        onleave: (i, status) => {
          // status: "totop" | "tobottom"
        },
        current: (val, i, el) => { 
            style.inset(item, `${100 - val}%`);
            style.translate(item, 0, -val + 100);
        },
        prev: (val, i, el) => { 
            style.translate(item, 0, -val / 2);
        },
    });
});
```

<hr>

### e.toggle(threshold, onChange)

Edge-triggered boolean when crossing `threshold` (>=). Runs only when the state changes.

```js
sectionAnimate.add(el, [75, 50], (e) => {
    e.toggle(50, (isAbove) => {
        console.log("toggle", isAbove);
    });   
});
```
<hr>

### e.trigger(threshold, onFire)

One-shot trigger: fires once when reaching `threshold` (>=).
```js 
sectionAnimate.add(el, [75, 50], (e) => {
    e.trigger(0, (trig) => {
        console.log("trigger", trig);
    });
}); 
```

## 3. style 
cached style writers<br>
`style` avoids redundant writes by caching the last string written per element.

Methods:

- **`style.translate(el, x, y, z)`**: writes `transform: translate3d(...)` (numbers treated as px)
- **`style.clipPath(el, value)`**
- **`style.inset(el, top, right, bottom, left)`**: writes `clip-path: inset(...)`
- **`style.polygonFromInset(el, top, right, bottom, left)`**
- **`style.opacity(el, value)`**
- **`style.color(el, value)`**
- **`style.scale(el, value)`**: uses the CSS `scale` property
- **`style.var(el, value, name="--p")`**: writes CSS custom properties with caching

## 2. stagger()

`stagger` is callable (one-shot):

```js
stagger(rootOrList, val, opts);
```

Defaults:

- if `rootOrList` is an Element, `stagger` selects children with: `[data-progress-item], .char, .item`





### Options

`stagger()` drives `progressChars()` which writes these root vars:

- `--p` (0..1 overall progress)
- `--denom` (n-1)
- `--softness`

And sets `--i` once on each item.

Supported option names:

- **`softness`**: main option
- **`stagger`**: legacy alias (still supported)

If `softness` is in `0..1`, it’s interpreted as a “span fraction” and converted to a segment softness value.

Use this in css to get Per-char progress computed from root vars set by JS:<br>
`--t: calc(var(--p) * (var(--denom) + var(--softness))); --progress: clamp(0, calc((var(--t) - var(--i)) / var(--softness)), 1);`

```css
.item { 
    --t: calc(var(--p) * (var(--denom) + var(--softness)));
    --progress: clamp(0, calc((var(--t) - var(--i)) / var(--softness)), 1);

    opacity: calc(0.1 + 0.9 * var(--progress));
}
```

## 4. cubicBezier
Same as css easing cubicBezier();

```js
const easeCustom = cubicBezier(.35, .91, .73, .11);

sectionAnimate.add(el, [75, 50], (e) => {
  e.timeline(0, 100, (val) => {
    const t = val / 100;
    const eased = easeCustom(t) * 100; // 0..100
    console.log(eased);
  });  
});
```
