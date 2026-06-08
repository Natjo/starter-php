const moduloPosition = (position, width) => {
    if (width <= 0) return 0;

    while (position <= -width) position += width;
    while (position > 0) position -= width;

    return position;
};

export default function marquee(root) {
    const viewport = root.querySelector('[data-marquee-viewport]');
    const track = root.querySelector('[data-marquee-track]');
    const group = root.querySelector('[data-marquee-group]');

    if (!viewport || !track || !group || group.children.length === 0) return;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const initialDirection = root.dataset.direction === 'right' ? 1 : -1;
    const speed = Math.max(1, Number.parseFloat(root.dataset.speed || '50') || 50);
    const resumeDelay = Math.max(0, Number.parseInt(root.dataset.resumeDelay || '120', 10) || 0);
    const sourceItems = Array.from(group.children).map(item => item.cloneNode(true));
    let direction = initialDirection;
    let position = initialDirection > 0 ? -1 : 0;
    let loopWidth = 0;
    let previousTime = 0;
    let previousScrollY = window.scrollY;
    let pointerId = null;
    let pointerX = 0;
    let dragged = false;
    let manuallyPaused = false;
    let visible = !('IntersectionObserver' in window);
    let animationFrame = null;
    let resumeTimer = null;
    let resizeFrame = null;
    let touchStartX = 0;
    let touchStartY = 0;
    let touchIntent = null;

    const setCloneAccessibility = element => {
        element.setAttribute('aria-hidden', 'true');
        element.inert = true;
        element.querySelectorAll('a, button, input, select, textarea, [tabindex]').forEach(child => {
            child.setAttribute('tabindex', '-1');
        });
    };

    const appendSourceItems = (target, hidden = false) => {
        sourceItems.forEach(source => {
            const item = source.cloneNode(true);

            if (hidden) setCloneAccessibility(item);
            target.append(item);
        });
    };

    const measure = () => {
        track.querySelectorAll('[data-marquee-clone]').forEach(clone => clone.remove());
        group.replaceChildren();
        appendSourceItems(group);

        let copyCount = 0;
        while (group.scrollWidth < viewport.clientWidth && copyCount < 20) {
            appendSourceItems(group, true);
            copyCount++;
        }

        const trackStyle = window.getComputedStyle(track);
        const gap = Number.parseFloat(trackStyle.columnGap || trackStyle.gap || '0') || 0;
        loopWidth = group.getBoundingClientRect().width + gap;

        const clone = group.cloneNode(true);
        clone.dataset.marqueeClone = '';
        setCloneAccessibility(clone);
        track.append(clone);

        position = moduloPosition(position, loopWidth);
        track.style.transform = `translate3d(${position}px, 0, 0)`;
    };

    const pause = () => {
        window.clearTimeout(resumeTimer);
        resumeTimer = null;
        manuallyPaused = true;
    };

    const scheduleResume = () => {
        window.clearTimeout(resumeTimer);

        if (resumeDelay === 0) return;

        resumeTimer = window.setTimeout(() => {
            manuallyPaused = false;
            previousTime = performance.now();
        }, resumeDelay);
    };

    const onPointerDown = event => {
        if (event.button !== 0 && event.pointerType === 'mouse') return;

        pause();
        pointerId = event.pointerId;
        pointerX = event.clientX;
        dragged = false;
        viewport.setPointerCapture?.(pointerId);
        root.classList.add('is-dragging');
    };

    const onPointerMove = event => {
        if (event.pointerId !== pointerId) return;

        const delta = event.clientX - pointerX;
        pointerX = event.clientX;

        if (Math.abs(delta) > 1) dragged = true;
        position = moduloPosition(position + delta, loopWidth);
        track.style.transform = `translate3d(${position}px, 0, 0)`;
    };

    const onPointerEnd = event => {
        if (event.pointerId !== pointerId) return;

        viewport.releasePointerCapture?.(pointerId);
        pointerId = null;
        root.classList.remove('is-dragging');
        scheduleResume();
    };

    const onClick = event => {
        if (!dragged) return;

        event.preventDefault();
        event.stopPropagation();
        dragged = false;
    };

    const onWheel = event => {
        const horizontalDelta = event.shiftKey ? event.deltaY : event.deltaX;
        const horizontalIntent = event.shiftKey || Math.abs(event.deltaX) > Math.abs(event.deltaY);

        if (!horizontalIntent || horizontalDelta === 0) return;

        event.preventDefault();
        event.stopPropagation();
        event.lenisStopPropagation = true;
        pause();
        position = moduloPosition(position - horizontalDelta, loopWidth);
        track.style.transform = `translate3d(${position}px, 0, 0)`;
        scheduleResume();
    };

    const onTouchStart = event => {
        if (event.touches.length !== 1) return;

        touchStartX = event.touches[0].clientX;
        touchStartY = event.touches[0].clientY;
        touchIntent = null;
    };

    const onTouchMove = event => {
        if (event.touches.length !== 1) return;

        const deltaX = event.touches[0].clientX - touchStartX;
        const deltaY = event.touches[0].clientY - touchStartY;

        if (touchIntent === null && Math.max(Math.abs(deltaX), Math.abs(deltaY)) > 6) {
            touchIntent = Math.abs(deltaX) > Math.abs(deltaY) ? 'horizontal' : 'vertical';
        }

        if (touchIntent === 'horizontal') {
            event.lenisStopPropagation = true;
            event.stopPropagation();
        }
    };

    const onScroll = () => {
        if (!visible) return;

        const currentScrollY = window.scrollY;
        const delta = currentScrollY - previousScrollY;

        if (Math.abs(delta) > 1) {
            direction = delta > 0 ? initialDirection : -initialDirection;
            previousScrollY = currentScrollY;
        }
    };

    const onKeyDown = event => {
        if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;

        event.preventDefault();
        pause();
        position += event.key === 'ArrowLeft' ? 40 : -40;
        position = moduloPosition(position, loopWidth);
        track.style.transform = `translate3d(${position}px, 0, 0)`;
        scheduleResume();
    };

    const onResize = () => {
        if (resizeFrame) return;

        resizeFrame = window.requestAnimationFrame(() => {
            resizeFrame = null;
            measure();
        });
    };

    const animate = time => {
        const deltaTime = previousTime === 0 ? 0 : Math.min((time - previousTime) / 1000, 0.05);
        previousTime = time;

        if (!manuallyPaused && !reducedMotion.matches && pointerId === null) {
            position = moduloPosition(position + direction * speed * deltaTime, loopWidth);
            track.style.transform = `translate3d(${position}px, 0, 0)`;
        }

        animationFrame = window.requestAnimationFrame(animate);
    };

    const startAnimation = () => {
        if (animationFrame !== null) return;

        previousTime = performance.now();
        animationFrame = window.requestAnimationFrame(animate);
    };

    const stopAnimation = () => {
        if (animationFrame === null) return;

        window.cancelAnimationFrame(animationFrame);
        animationFrame = null;
    };

    viewport.addEventListener('pointerdown', onPointerDown);
    viewport.addEventListener('pointermove', onPointerMove);
    viewport.addEventListener('pointerup', onPointerEnd);
    viewport.addEventListener('pointercancel', onPointerEnd);
    viewport.addEventListener('click', onClick, true);
    viewport.addEventListener('wheel', onWheel, { passive: false });
    viewport.addEventListener('touchstart', onTouchStart, { passive: true });
    viewport.addEventListener('touchmove', onTouchMove, { passive: true });
    viewport.addEventListener('keydown', onKeyDown);
    window.addEventListener('resize', onResize, { passive: true });

    if ('ResizeObserver' in window) {
        const resizeObserver = new ResizeObserver(onResize);
        resizeObserver.observe(viewport);
    }

    if ('IntersectionObserver' in window) {
        const intersectionObserver = new IntersectionObserver(entries => {
            const nextVisible = entries[0]?.isIntersecting ?? false;

            if (nextVisible === visible) return;

            visible = nextVisible;
            previousScrollY = window.scrollY;

            if (visible) {
                window.addEventListener('scroll', onScroll, { passive: true });
                startAnimation();
            } else {
                window.removeEventListener('scroll', onScroll);
                stopAnimation();
            }
        });
        intersectionObserver.observe(root);
    } else {
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    measure();
    if (visible) startAnimation();
}
