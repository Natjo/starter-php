import Lenis from "@vendors/lenis/lenis";
import HeaderNav from "@common/header-nav";
import ModulesHydration from "@modules/hydration";

const supportsSmoothScroll = () => {
    return !window.matchMedia("(prefers-reduced-motion: reduce)").matches
        && window.matchMedia("(pointer: fine)").matches;
};

const isChrome = () => {
    const brands = navigator.userAgentData?.brands;
    if (Array.isArray(brands)) {
        return brands.some(({ brand }) => brand === "Google Chrome" || brand === "Chromium")
            && !brands.some(({ brand }) => brand === "Microsoft Edge" || brand === "Opera");
    }

    return /\bChrome\//.test(navigator.userAgent)
        && !/\b(?:Edg|OPR)\//.test(navigator.userAgent);
};

const initLenis = () => {
    if (!supportsSmoothScroll()) return;
    window.lenis = new Lenis({
        autoRaf: true,
        wheelMultiplier: isChrome() ? 0.7 : 1,
    });
};

initLenis();
ModulesHydration();
HeaderNav();
