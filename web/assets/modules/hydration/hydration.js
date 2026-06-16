const moduleVersions = __MODULE_VERSIONS__;
const moduleCache = new Map();
const moduleStates = new WeakMap();
const versionedModulePath = modulePath => {
  const version = moduleVersions[modulePath.replace(/^\.\//, '')];
  return version ? `${modulePath}?v=${version}` : modulePath;
};
const moduleCandidates = moduleName => {
  const parts = moduleName.split('/').filter(Boolean);
  const last = parts[parts.length - 1];
  if (parts.length >= 2 && last) {
    return [`${moduleName}/${last}`];
  }
  return [moduleName].filter(Boolean);
};
const importModule = async moduleName => {
  let lastError;
  for (const candidate of moduleCandidates(moduleName)) {
    const [dir, ...rest] = candidate.split('/');
    const file = rest.join('/');
    let modulePath = "";
    try {
      if (dir === 'common') modulePath = `./common/${file}.js`;
      if (dir === 'components') modulePath = `./components/${file}.js`;
      if (dir === 'form') modulePath = `./form/${file}.js`;
      if (dir === 'modules') modulePath = `./modules/${file}.js`;
      if (dir === 'strates') modulePath = `./strates/${file}.js`;
      if (dir === 'heros') modulePath = `./heros/${file}.js`;
      if (modulePath) {
        modulePath = versionedModulePath(modulePath);
        if (!moduleCache.has(modulePath)) {
          moduleCache.set(modulePath, import(modulePath));
        }
        return await moduleCache.get(modulePath);
      }
      throw new Error(`Préfixe de module inconnu : ${dir}`);
    } catch (error) {
      lastError = error;
    }
  }
  throw lastError;
};
const cleanupFromResult = result => {
  if (typeof result === "function") return result;
  if (typeof (result === null || result === void 0 ? void 0 : result.destroy) === "function") return () => result.destroy();
  if (typeof (result === null || result === void 0 ? void 0 : result.remove) === "function") return () => result.remove();
  return null;
};
const cleanupModule = el => {
  var _state$cleanup;
  const state = moduleStates.get(el);
  state === null || state === void 0 || (_state$cleanup = state.cleanup) === null || _state$cleanup === void 0 || _state$cleanup.call(state);
  moduleStates.delete(el);
  delete el.dataset.moduleHydrated;
  delete el.dataset.moduleHydrating;
};
const hydrateModule = async el => {
  if (el.dataset.moduleHydrated === "true" || el.dataset.moduleHydrating === "true") return;
  const moduleName = el.dataset.module;
  if (!moduleName) return;
  el.dataset.moduleHydrating = "true";
  try {
    const module = await importModule(moduleName);
    const hydrate = module.default;
    if (typeof hydrate !== "function") return;
    const cleanup = cleanupFromResult(hydrate(el));
    if (!el.isConnected) {
      cleanup === null || cleanup === void 0 || cleanup();
      return;
    }
    moduleStates.set(el, {
      cleanup
    });
    el.dataset.moduleHydrated = "true";
  } catch (error) {
    console.error(`Module introuvable : ${moduleName}`, error);
  } finally {
    delete el.dataset.moduleHydrating;
  }
};
const shouldHydrateOnVisible = el => /\B@visible\s+true\b/.test(el.dataset.context || "");
export default function ModulesHydration() {
  const visibleObserver = "IntersectionObserver" in window ? new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      visibleObserver.unobserve(entry.target);
      hydrateModule(entry.target);
    });
  }, {
    rootMargin: "200px 0px"
  }) : null;
  const observeModule = el => {
    if (!(el instanceof HTMLElement)) return;
    if (shouldHydrateOnVisible(el)) {
      if (visibleObserver) {
        visibleObserver.observe(el);
        return;
      }
    }
    hydrateModule(el);
  };
  const modulesIn = node => {
    if (!(node instanceof Element)) return [];
    return [...(node.matches("[data-module]") ? [node] : []), ...node.querySelectorAll("[data-module]")];
  };
  document.querySelectorAll("[data-module]").forEach(observeModule);
  const domObserver = new MutationObserver(records => {
    records.forEach(record => {
      record.removedNodes.forEach(node => {
        modulesIn(node).forEach(el => {
          visibleObserver === null || visibleObserver === void 0 || visibleObserver.unobserve(el);
          cleanupModule(el);
        });
      });
      record.addedNodes.forEach(node => {
        modulesIn(node).forEach(observeModule);
      });
    });
  });
  domObserver.observe(document.body, {
    childList: true,
    subtree: true
  });
  return () => {
    domObserver.disconnect();
    visibleObserver === null || visibleObserver === void 0 || visibleObserver.disconnect();
    document.querySelectorAll("[data-module]").forEach(cleanupModule);
  };
}