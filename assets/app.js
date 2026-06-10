import Lenis from "@vendors/lenis/lenis";
import HeaderNav from "@common/header-nav";
import ModulesHydration from "@modules/hydration";

const supportsSmoothScroll = () => {
    return !window.matchMedia("(prefers-reduced-motion: reduce)").matches
        && window.matchMedia("(pointer: fine)").matches;
};

const initLenis = () => {
    if (!supportsSmoothScroll()) return;
    window.lenis = new Lenis({ autoRaf: true });
};

initLenis();
ModulesHydration();
HeaderNav();
