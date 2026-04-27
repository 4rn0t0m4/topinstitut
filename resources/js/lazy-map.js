/**
 * Lazy-load map dependencies (Leaflet, plugins, Google Maps API) only when
 * the target element is about to enter the viewport.
 *
 * Goal: keep ~700 KB of JS off the critical path → improves INP & LCP.
 */
window.lazyLoadMap = function ({ target, scripts = [], styles = [], onReady, rootMargin = '400px' }) {
    const el = typeof target === 'string' ? document.querySelector(target) : target;
    if (!el) return;

    let triggered = false;

    const trigger = () => {
        if (triggered) return;
        triggered = true;

        styles.forEach((href) => {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            document.head.appendChild(link);
        });

        scripts.reduce((promise, src) => {
            return promise.then(() => new Promise((resolve, reject) => {
                const s = document.createElement('script');
                s.src = src;
                s.async = false;
                s.onload = resolve;
                s.onerror = reject;
                document.head.appendChild(s);
            }));
        }, Promise.resolve()).then(() => onReady && onReady())
            .catch((e) => console.error('lazyLoadMap failed:', e));
    };

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                observer.disconnect();
                trigger();
            }
        }, { rootMargin });
        observer.observe(el);
    } else {
        // Old browser — load immediately as fallback.
        trigger();
    }
};
