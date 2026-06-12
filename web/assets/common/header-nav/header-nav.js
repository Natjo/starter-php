import smoothAnchors from "../../modules/smoothAnchors/smoothAnchors.js";
export default function HeaderNav() {
  return smoothAnchors({
    links: '#nav a[href*="#"], .header-nav-logo',
    duration: 1.2
  });
}