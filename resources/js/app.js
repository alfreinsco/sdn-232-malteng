const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.documentElement.classList.add('motion-ready');

const revealElements = document.querySelectorAll('[data-reveal]');

if (reducedMotion || !('IntersectionObserver' in window)) {
    revealElements.forEach((element) => element.classList.add('is-visible'));
} else {
    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            const delay = Number(entry.target.dataset.revealDelay ?? 0);
            window.setTimeout(() => entry.target.classList.add('is-visible'), delay);
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.14, rootMargin: '0px 0px -40px' });

    revealElements.forEach((element) => revealObserver.observe(element));
}

const parallaxElement = document.querySelector('[data-parallax]');

if (parallaxElement && !reducedMotion) {
    parallaxElement.addEventListener('pointermove', (event) => {
        const bounds = parallaxElement.getBoundingClientRect();
        const horizontal = ((event.clientX - bounds.left) / bounds.width - 0.5) * 2;
        const vertical = ((event.clientY - bounds.top) / bounds.height - 0.5) * 2;

        parallaxElement.style.setProperty('--hero-x', horizontal.toFixed(2));
        parallaxElement.style.setProperty('--hero-y', vertical.toFixed(2));
    });

    parallaxElement.addEventListener('pointerleave', () => {
        parallaxElement.style.setProperty('--hero-x', '0');
        parallaxElement.style.setProperty('--hero-y', '0');
    });
}

const showcaseImage = document.querySelector('#showcase-image');
const showcaseTitle = document.querySelector('#showcase-title');
const showcaseCopy = document.querySelector('#showcase-copy');
const showcaseTabs = document.querySelectorAll('[data-showcase-tab]');

showcaseTabs.forEach((tab) => {
    tab.addEventListener('click', () => {
        if (!showcaseImage || tab.classList.contains('is-active')) {
            return;
        }

        showcaseTabs.forEach((item) => {
            item.classList.remove('is-active');
            item.setAttribute('aria-selected', 'false');
        });

        tab.classList.add('is-active');
        tab.setAttribute('aria-selected', 'true');
        showcaseImage.classList.add('is-changing');

        window.setTimeout(() => {
            showcaseImage.src = tab.dataset.image;
            showcaseImage.alt = tab.dataset.alt;
            showcaseTitle.textContent = tab.dataset.title;
            showcaseCopy.textContent = tab.dataset.copy;
            showcaseImage.classList.remove('is-changing');
        }, reducedMotion ? 0 : 160);
    });
});
