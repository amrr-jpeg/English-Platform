document.addEventListener('DOMContentLoaded', () => {
    initModernNavigation();
    initMicroInteractions();
    initAchievementEffects();
    initSkinTilt();
});

function initModernNavigation() {
    const navRoot = document.querySelector('[data-modern-nav]');

    if (!navRoot) {
        return;
    }

    const navToggle = navRoot.querySelector('[data-nav-toggle]');
    const primaryNav = navRoot.querySelector('#primaryNav');
    const navGroups = navRoot.querySelectorAll('[data-nav-group]');
    const profileMenu = navRoot.querySelector('[data-profile-menu]');
    const profileToggle = navRoot.querySelector('[data-profile-toggle]');

    const closeGroups = (except = null) => {
        navGroups.forEach((group) => {
            if (group === except) {
                return;
            }

            group.classList.remove('is-open');
            group.querySelector('[data-nav-group-button]')?.setAttribute('aria-expanded', 'false');
        });
    };

    navToggle?.addEventListener('click', () => {
        const isOpen = primaryNav.classList.toggle('is-open');
        navToggle.classList.toggle('is-open', isOpen);
        navToggle.setAttribute('aria-expanded', String(isOpen));
    });

    navGroups.forEach((group) => {
        const button = group.querySelector('[data-nav-group-button]');

        button?.addEventListener('click', (event) => {
            event.preventDefault();
            const isOpen = group.classList.toggle('is-open');
            button.setAttribute('aria-expanded', String(isOpen));
            closeGroups(group);

            if (profileMenu) {
                profileMenu.classList.remove('is-open');
                profileToggle?.setAttribute('aria-expanded', 'false');
            }
        });
    });

    profileToggle?.addEventListener('click', (event) => {
        event.preventDefault();
        const isOpen = profileMenu.classList.toggle('is-open');
        profileToggle.setAttribute('aria-expanded', String(isOpen));
        closeGroups();
    });

    document.addEventListener('click', (event) => {
        if (navRoot.contains(event.target)) {
            return;
        }

        closeGroups();
        profileMenu?.classList.remove('is-open');
        profileToggle?.setAttribute('aria-expanded', 'false');
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        closeGroups();
        profileMenu?.classList.remove('is-open');
        profileToggle?.setAttribute('aria-expanded', 'false');
        primaryNav?.classList.remove('is-open');
        navToggle?.classList.remove('is-open');
        navToggle?.setAttribute('aria-expanded', 'false');
    });
}

function initMicroInteractions() {
    const clickableElements = document.querySelectorAll(
        '.btn, .miniBtn, .mainMenu__link, .navDropdown__item, .profileDropdown__item, .skinCard, .choice, .levelBtn, .gameTile, .lessonCard'
    );

    clickableElements.forEach((element) => {
        element.addEventListener('click', () => {
            element.classList.remove('click-pop');
            void element.offsetWidth;
            element.classList.add('click-pop');
        });
    });

    document.querySelectorAll('.btn').forEach((button) => {
        button.addEventListener('click', (event) => {
            const text = button.textContent.toLowerCase();

            if (
                text.includes('купить') ||
                text.includes('выбрать') ||
                text.includes('ответ') ||
                text.includes('заверш') ||
                text.includes('провер') ||
                text.includes('начать')
            ) {
                createFloatingText('✨', event.clientX, event.clientY);
            }
        });
    });
}

function initAchievementEffects() {
    const successToast = document.querySelector('.toast--ok, .toast--good');

    if (successToast) {
        createFloatingText('✨ успех!', window.innerWidth / 2, 120);
    }

    const achievementToast = document.querySelector('.achievementToast');

    if (!achievementToast) {
        return;
    }

    createConfetti();
    createFloatingText('🏆 достижение!', window.innerWidth / 2, 120);

    setTimeout(() => {
        achievementToast.style.opacity = '0';
        achievementToast.style.transform = 'translateY(20px) scale(0.96)';
    }, 4500);

    setTimeout(() => {
        achievementToast.remove();
    }, 5200);
}

function initSkinTilt() {
    const cards = document.querySelectorAll('.skinCard, .gameTile, .lessonCard');

    cards.forEach((card) => {
        card.addEventListener('mousemove', (event) => {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            const rect = card.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;

            const rotateX = ((y / rect.height) - 0.5) * -4;
            const rotateY = ((x / rect.width) - 0.5) * 4;

            card.style.transform = `translateY(-4px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });
}

function createFloatingText(text, x, y) {
    const item = document.createElement('div');
    item.className = 'coin-fly';
    item.textContent = text;
    item.style.left = `${x}px`;
    item.style.top = `${y}px`;

    document.body.appendChild(item);

    setTimeout(() => {
        item.remove();
    }, 950);
}

function createConfetti() {
    const emojis = ['🏆', '✨', '⭐', '🎉', '🪙', '🌈', '💫'];

    for (let i = 0; i < 28; i++) {
        const item = document.createElement('div');
        item.className = 'confetti-item';
        item.textContent = emojis[Math.floor(Math.random() * emojis.length)];

        item.style.left = `${Math.random() * 100}%`;
        item.style.animationDelay = `${Math.random() * 0.35}s`;
        item.style.animationDuration = `${1.5 + Math.random() * 1.2}s`;

        document.body.appendChild(item);

        setTimeout(() => {
            item.remove();
        }, 3200);
    }
}