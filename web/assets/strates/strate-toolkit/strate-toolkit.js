import { ScrollDriver, style, stagger } from "../../modules/scrollDriver/scrollDriver.js";
import textAnimated from "../../modules/textAnimated/textAniimated.js";
export default el => {
  textAnimated(el);
  return () => {};
};