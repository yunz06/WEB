(() => {
    'use strict';

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        const submitter = event.submitter;
        const confirmation = submitter instanceof HTMLElement && submitter.dataset.confirm
            ? submitter.dataset.confirm
            : form.dataset.confirm;

        if (confirmation && !window.confirm(confirmation)) {
            event.preventDefault();
        }
    });

    const initEditorialMotion = () => {
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reducedMotion) return;

        const revealTargets = [
            '.featured-story',
            '.featured-supporting > article',
            '.latest-story',
            '.category-section',
            '.article-detail__header',
            '.article-detail__hero',
            '.article-detail__content',
            '.article-detail__gallery figure',
            '.related-stories',
            '.article-comments',
            '.auth-panel',
            '.account-panel',
            '.admin-page-heading',
            '.dashboard-quick-actions',
            '.admin-page .card',
            '.admin-page .table-responsive'
        ];

        const elements = document.querySelectorAll(revealTargets.join(','));
        if (!elements.length || !('IntersectionObserver' in window)) return;

        elements.forEach((element, index) => {
            element.classList.add('reveal-on-scroll');
            element.style.setProperty('--reveal-delay', `${Math.min(index % 4, 3) * 65}ms`);
        });

        document.body.classList.add('motion-ready');
        const observer = new IntersectionObserver((entries, currentObserver) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                currentObserver.unobserve(entry.target);
            });
        }, {
            root: null,
            rootMargin: '0px 0px -8% 0px',
            threshold: 0.08
        });

        elements.forEach((element) => observer.observe(element));

        const progress = document.createElement('div');
        progress.className = 'news-scroll-progress';
        progress.setAttribute('aria-hidden', 'true');
        document.body.appendChild(progress);

        let frameRequested = false;
        const updateProgress = () => {
            const scrollable = document.documentElement.scrollHeight - window.innerHeight;
            const ratio = scrollable > 0 ? Math.min(1, Math.max(0, window.scrollY / scrollable)) : 0;
            progress.style.transform = `scaleX(${ratio})`;
            frameRequested = false;
        };

        window.addEventListener('scroll', () => {
            if (frameRequested) return;
            frameRequested = true;
            window.requestAnimationFrame(updateProgress);
        }, { passive: true });
        window.addEventListener('resize', updateProgress, { passive: true });
        updateProgress();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEditorialMotion, { once: true });
    } else {
        initEditorialMotion();
    }
})();
