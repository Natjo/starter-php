const config = window.__adminWebVitals;

if (!config || !config.endpoint) {
    throw new Error("Web Vitals config missing");
}

let lcpValue = null;
let clsValue = 0;
let inpValue = null;
let sent = false;

const updateINP = (entry) => {
    const duration = Math.round(entry.duration || entry.processingEnd - entry.startTime || 0);
    if (inpValue === null || duration > inpValue) {
        inpValue = duration;
    }
};

try {
    const lcpObserver = new PerformanceObserver((list) => {
        const entries = list.getEntries();
        const last = entries[entries.length - 1];
        if (last) {
            lcpValue = Math.round(last.startTime);
        }
    });
    lcpObserver.observe({ type: "largest-contentful-paint", buffered: true });

    const clsObserver = new PerformanceObserver((list) => {
        for (const entry of list.getEntries()) {
            if (!entry.hadRecentInput) {
                clsValue += entry.value;
            }
        }
    });
    clsObserver.observe({ type: "layout-shift", buffered: true });

    const inpObserver = new PerformanceObserver((list) => {
        for (const entry of list.getEntries()) {
            updateINP(entry);
        }
    });
    inpObserver.observe({ type: "event", buffered: true, durationThreshold: 16 });
} catch (error) {}

const sendVitals = () => {
    if (sent) {
        return;
    }

    sent = true;

    const payload = {
        path: window.location.pathname || "/",
        url: window.location.href,
        metrics: {
            lcp: lcpValue,
            cls: Number(clsValue.toFixed(3)),
            inp: inpValue,
        },
    };

    fetch(config.endpoint, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
        keepalive: true,
        credentials: "same-origin",
    }).catch(() => {});
};

window.setTimeout(sendVitals, 10000);

document.addEventListener("visibilitychange", () => {
    if (document.visibilityState === "hidden") {
        sendVitals();
    }
});

window.addEventListener("pagehide", sendVitals);
