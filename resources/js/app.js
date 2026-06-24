const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (!prefersReducedMotion) {
    document.documentElement.classList.add('motion-ready');
}

const initializeGalleryLightbox = () => {
    const triggers = Array.from(document.querySelectorAll('[data-gallery-trigger]'));
    const lightbox = document.querySelector('[data-gallery-lightbox]');

    if (!triggers.length || !lightbox) {
        return;
    }

    const image = lightbox.querySelector('[data-gallery-image]');
    const caption = lightbox.querySelector('[data-gallery-caption]');
    const closeButton = lightbox.querySelector('[data-gallery-close]');
    const previousButton = lightbox.querySelector('[data-gallery-prev]');
    const nextButton = lightbox.querySelector('[data-gallery-next]');
    const placeholderSrc = image.dataset.galleryPlaceholder || '';
    let activeIndex = 0;

    const render = () => {
        const trigger = triggers[activeIndex];
        const title = trigger.dataset.galleryTitle || '';
        const src = trigger.dataset.gallerySrc || '';

        if (src) {
            image.src = src;
        } else {
            image.removeAttribute('src');
        }

        image.alt = title;
        caption.textContent = title;
    };

    const open = (index) => {
        activeIndex = index;
        render();
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.classList.add('lightbox-open');
        closeButton?.focus();
    };

    const close = () => {
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('lightbox-open');
        if (placeholderSrc) {
            image.src = placeholderSrc;
        } else {
            image.removeAttribute('src');
        }
        image.alt = '';
    };

    const move = (direction) => {
        activeIndex = (activeIndex + direction + triggers.length) % triggers.length;
        render();
    };

    triggers.forEach((trigger, index) => {
        trigger.addEventListener('click', () => open(index));
    });

    closeButton?.addEventListener('click', close);
    previousButton?.addEventListener('click', () => move(-1));
    nextButton?.addEventListener('click', () => move(1));

    lightbox.addEventListener('click', (event) => {
        if (event.target === lightbox) {
            close();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (!lightbox.classList.contains('is-open')) {
            return;
        }

        if (event.key === 'Escape') {
            close();
        }

        if (event.key === 'ArrowLeft') {
            move(-1);
        }

        if (event.key === 'ArrowRight') {
            move(1);
        }
    });
};

const initializePublicNavigation = () => {
    const navShell = document.querySelector('[data-public-nav]');
    const navMenu = document.querySelector('[data-public-nav-menu]');
    const navToggle = document.querySelector('[data-public-nav-toggle]');

    if (!navShell || !navMenu || !navToggle) {
        return;
    }

    const closeMenu = () => {
        navMenu.classList.remove('is-open');
        navToggle.setAttribute('aria-expanded', 'false');
    };

    const openMenu = () => {
        navMenu.classList.add('is-open');
        navToggle.setAttribute('aria-expanded', 'true');
    };

    navToggle.addEventListener('click', () => {
        if (navMenu.classList.contains('is-open')) {
            closeMenu();
            return;
        }

        openMenu();
    });

    navMenu.addEventListener('click', (event) => {
        if (event.target instanceof HTMLAnchorElement) {
            closeMenu();
        }
    });

    document.addEventListener('click', (event) => {
        if (!navMenu.classList.contains('is-open')) {
            return;
        }

        if (navShell.contains(event.target) || navToggle.contains(event.target)) {
            return;
        }

        closeMenu();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 900) {
            closeMenu();
        }
    });
};

const initializeRevealMotion = () => {
    if (prefersReducedMotion) {
        return;
    }

    const selectors = [
        '.page-hero .container',
        '.hero-copy',
        '.trust-card',
        '.section-heading',
        '.split-section > *',
        '.package-card',
        '.package-wide',
        '.info-panel',
        '.contact-panel',
        '.map-panel',
        '.table-card',
        '.gallery-tile',
        '.gallery-card',
        '.detail-image',
        '.detail-content',
        '.profile-photo',
    ];

    const elements = Array.from(document.querySelectorAll(selectors.join(','))).filter(
        (element) => !element.closest('.site-footer'),
    );

    if (!elements.length) {
        return;
    }

    elements.forEach((element, index) => {
        element.dataset.reveal = 'true';
        element.style.setProperty('--reveal-delay', `${Math.min(index * 70, 420)}ms`);
    });

    if (!('IntersectionObserver' in window)) {
        elements.forEach((element) => element.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        {
            threshold: 0.14,
            rootMargin: '0px 0px -6% 0px',
        },
    );

    elements.forEach((element) => observer.observe(element));
};

const initializeHeroParallax = () => {
    if (prefersReducedMotion) {
        return;
    }

    const heroImage = document.querySelector('[data-hero-parallax]');

    if (!heroImage || window.matchMedia('(max-width: 900px)').matches) {
        return;
    }

    let frame = null;

    const update = () => {
        frame = null;
        const offset = Math.max(-14, Math.min(14, Math.round(window.scrollY * 0.03)));
        heroImage.style.setProperty('--hero-parallax-y', `${offset}px`);
    };

    const schedule = () => {
        if (frame !== null) {
            return;
        }

        frame = window.requestAnimationFrame(update);
    };

    update();
    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', schedule, { passive: true });
};

const initializeBookingForm = () => {
    const form = document.querySelector('[data-booking-form]');

    if (!form) {
        return;
    }

    const packageSelect = form.querySelector('[data-booking-package]');
    const scheduleSelect = form.querySelector('[data-booking-schedule]');
    const quotaHint = form.querySelector('[data-booking-quota]');
    const pilgrimsInput = form.querySelector('[data-booking-pilgrims]');

    if (!packageSelect || !scheduleSelect) {
        return;
    }

    const scheduleOptions = Array.from(scheduleSelect.options)
        .filter((option) => option.value)
        .map((option) => ({
            value: option.value,
            label: option.textContent,
            packageId: option.dataset.package,
            quota: option.dataset.quota,
            selected: option.selected,
        }));

    const renderSchedules = () => {
        const previousValue = scheduleSelect.value;
        const packageId = packageSelect.value;
        const placeholder = new Option(packageId ? 'Pilih jadwal' : 'Pilih paket terlebih dahulu', '');
        scheduleSelect.replaceChildren(placeholder);

        scheduleOptions
            .filter((option) => option.packageId === packageId)
            .forEach((option) => {
                const element = new Option(option.label, option.value);
                element.dataset.quota = option.quota;
                element.selected = option.value === previousValue || option.selected;
                scheduleSelect.add(element);
                option.selected = false;
            });

        scheduleSelect.dispatchEvent(new Event('change'));
    };

    const updateQuota = () => {
        const option = scheduleSelect.selectedOptions[0];
        const quota = Number(option?.dataset.quota || 0);

        if (quotaHint) {
            quotaHint.textContent = quota > 0
                ? `Sisa kuota saat ini: ${quota} kursi.`
                : 'Pilih jadwal untuk melihat sisa kuota.';
        }

        if (pilgrimsInput && quota > 0) {
            pilgrimsInput.max = String(quota);
        }
    };

    packageSelect.addEventListener('change', renderSchedules);
    scheduleSelect.addEventListener('change', updateQuota);
    renderSchedules();
};

document.addEventListener('DOMContentLoaded', () => {
    initializePublicNavigation();
    initializeRevealMotion();
    initializeHeroParallax();
    initializeGalleryLightbox();
    initializeBookingForm();
});
