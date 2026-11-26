function playRewardMotion(element, keyframes, options) {
    element.getAnimations?.().forEach(animation => animation.cancel());
    element.animate?.(keyframes, { easing: 'cubic-bezier(.23, 1, .32, 1)', ...options });
}

function rollRewardNumber(windowElement) {
    const reel = document.createElement('span');
    if (!reel.animate) return 450;

    const display = windowElement.querySelector('.reward-number').cloneNode(true);
    const digits = [...display.querySelectorAll('.reward-digit')];
    const delay = 300;

    reel.className = 'reward-reel';
    reel.setAttribute('aria-hidden', 'true');
    reel.append(display);
    windowElement.querySelector('.reward-reel')?.remove();
    windowElement.append(reel);
    windowElement.classList.add('is-rolling');

    let lastAnimation;
    let stopTime = delay;

    digits.forEach((digit, index) => {
        const track = document.createElement('span');
        const target = Number(digit.textContent);
        const steps = 13 + (index % 3) * 2;
        const travel = 100 * steps / (steps + 1);
        track.className = 'reward-digit-track';

        for (let step = 0; step <= steps; step++) {
            const value = document.createElement('span');
            value.textContent = String((target + step + 20 - steps) % 10);
            track.append(value);
        }

        digit.replaceChildren(track);
        const duration = 1150 + index / Math.max(1, digits.length - 1) * 580;
        stopTime = delay + duration;
        lastAnimation = track.animate([
            { transform: 'translateY(0)', offset: 0 },
            { transform: 'translateY(-' + travel * .72 + '%)', offset: .43, easing: 'ease-out' },
            { transform: 'translateY(-' + travel * .91 + '%)', offset: .74, easing: 'ease-out' },
            { transform: 'translateY(-' + travel + '%)', offset: 1 },
        ], { duration, delay, fill: 'both' });
    });

    lastAnimation.onfinish = () => {
        if (!reel.isConnected) return;
        reel.remove();
        windowElement.classList.remove('is-rolling');
    };

    return stopTime;
}

function celebrateReward(modal) {
    const layer = modal.querySelector('.reward-confetti');
    layer.replaceChildren();
    if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const stage = modal.querySelector('.reward-stage');
    const colors = ['#ffe47b', '#ff9874', '#dcb8ff', '#85efc2', '#fff8dc'];
    const bounds = layer.getBoundingClientRect();
    const fallDistance = bounds.height * .68;

    playRewardMotion(stage.querySelector('.reward-title'), [
        { transform: 'translateY(68px) scale(.9)', opacity: 0 },
        { transform: 'translateY(-7px) scale(1.02)', opacity: 1, offset: .79 },
        { transform: 'translateY(0) scale(1)', opacity: 1 },
    ], { duration: 480, delay: 130, fill: 'both' });

    const revealDelay = rollRewardNumber(stage.querySelector('.reward-number-window'));
    playRewardMotion(stage.querySelector('.reward-ticket-shine'), [
        { transform: 'translateX(-135%)' },
        { transform: 'translateX(135%)' },
    ], { delay: revealDelay, duration: 520 });
    playRewardMotion(stage.querySelector('.reward-stamp'), [
        { opacity: 0, transform: 'translateY(-50%) scale(1.45) rotate(-30deg)' },
        { opacity: 1, transform: 'translateY(-50%) scale(.94) rotate(-11deg)', offset: .72 },
        { opacity: 1, transform: 'translateY(-50%) scale(1) rotate(-13deg)' },
    ], { duration: 260, delay: revealDelay, fill: 'both' });

    stage.querySelectorAll('.reward-popper').forEach((popper, side) => {
        const mouth = popper.getBoundingClientRect();
        const originX = mouth.left + mouth.width * .5 - bounds.left;
        const originY = mouth.top + mouth.height * .15 - bounds.top;
        const direction = side === 0 ? 1 : -1;

        const ring = document.createElement('span');
        ring.className = 'reward-ripple';
        ring.style.left = originX + 'px';
        ring.style.top = originY + 'px';
        ring.style.animationDelay = (revealDelay + side * 145) + 'ms';
        ring.addEventListener('animationend', () => ring.remove(), { once: true });
        layer.append(ring);

        playRewardMotion(popper, [
            { transform: 'rotate(' + direction * 24 + 'deg) translateY(4px) scale(.92)' },
            { transform: 'rotate(' + direction * 24 + 'deg) translateY(-5px) scale(1.1)' },
            { transform: 'rotate(' + direction * 24 + 'deg)' },
        ], { duration: 260, delay: revealDelay + side * 145 });

        for (let index = 0; index < 25; index++) {
            const particle = document.createElement('span');
            const circle = index % 3 === 0;
            const x = direction * bounds.width * (.07 + Math.random() * .51) - direction * bounds.width * .19;
            const y = -(bounds.height * (.26 + Math.random() * .42));
            const spin = direction * (150 + Math.random() * 360);
            particle.className = 'reward-particle';
            Object.assign(particle.style, {
                left: originX + 'px',
                top: originY + 'px',
                width: (circle ? 7 : 6) + 'px',
                height: (circle ? 7 : 13) + 'px',
                backgroundColor: colors[(index + side) % colors.length],
                borderRadius: circle ? '50%' : '2px',
                animationDelay: (revealDelay + side * 145 + (index % 5) * 35) + 'ms',
            });
            particle.style.setProperty('--confetti-peak', 'translate(' + x + 'px, ' + y + 'px) rotate(' + spin + 'deg)');
            particle.style.setProperty('--confetti-end', 'translate(' + x * 1.25 + 'px, ' + (y + fallDistance) + 'px) rotate(' + spin * 1.7 + 'deg)');
            particle.addEventListener('animationend', () => particle.remove(), { once: true });
            layer.append(particle);
        }
    });
}

document.querySelectorAll('.modal').forEach((modal) => {
    modal.addEventListener('shown.bs.modal', () => {
        modal.querySelector('[data-modal-focus]')?.focus();
    });

    if (modal.id === 'reward-editor') {
        modal.addEventListener('show.bs.modal', (event) => {
            if (modal.hasAttribute('data-show-on-load') && !event.relatedTarget) return;

            const formatter = new Intl.DateTimeFormat('en-GB', {
                timeZone: 'Asia/Kolkata', year: 'numeric', month: '2-digit', day: '2-digit',
                hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
            });
            const ist = Object.fromEntries(formatter.formatToParts(new Date()).map(({ type, value }) => [type, value]));
            const date = ist.year + '-' + ist.month + '-' + ist.day;
            const time = ist.hour + ':' + ist.minute;
            modal.querySelector('#reward-effective-date').value = date;
            modal.querySelector('#reward-effective-time').value = time;
            modal.querySelector('[name="default_effective_at"]').value = date + 'T' + time;
        });
    }

    if (modal.id === 'welcome-reward') {
        modal.addEventListener('shown.bs.modal', () => celebrateReward(modal));
        modal.addEventListener('hidden.bs.modal', () => {
            modal.querySelector('.reward-confetti').replaceChildren();
            document.getElementById('rewards-heading')?.focus({ preventScroll: true });
        });
    }

    if (modal.hasAttribute('data-show-on-load')) {
        bootstrap.Modal.getOrCreateInstance(modal).show();
    }
});

const adminSidebar = document.getElementById('admin-sidebar');

if (adminSidebar) {
    const desktop = matchMedia('(min-width: 992px)');
    const opener = document.querySelector('[data-bs-toggle="offcanvas"][aria-controls="admin-sidebar"]');

    function releaseDesktopSidebar() {
        if (!desktop.matches) return;

        if (adminSidebar.classList.contains('show')) {
            bootstrap.Offcanvas.getInstance(adminSidebar)?.hide();
        } else {
            adminSidebar.removeAttribute('aria-hidden');
        }
    }

    desktop.addEventListener('change', releaseDesktopSidebar);
    adminSidebar.addEventListener('shown.bs.offcanvas', releaseDesktopSidebar);
    adminSidebar.addEventListener('hide.bs.offcanvas', () => {
        opener.disabled = true;
    });
    adminSidebar.addEventListener('hidden.bs.offcanvas', () => {
        opener.disabled = false;
        releaseDesktopSidebar();
    });
}

const customerEditor = document.getElementById('customer-editor');

customerEditor?.addEventListener('show.bs.modal', (event) => {
    const customer = event.relatedTarget?.dataset;
    if (!customer?.customer) {
        event.preventDefault();
        return;
    }

    document.getElementById('edit-customer-id').value = customer.customer;
    document.getElementById('edit-customer-name').textContent = customer.name;
    document.getElementById('customer-points').value = customer.points;
});

const search = document.getElementById('customer-search');

if (search) {
    const rows = [...document.querySelectorAll('[data-directory-row]')];
    const noResults = document.getElementById('customer-no-results');

    function filterCustomers() {
        const query = search.value.trim().toLowerCase();
        let matches = 0;
        for (const row of rows) {
            row.hidden = !row.querySelector('.directory-person').textContent.toLowerCase().includes(query);
            if (!row.hidden) matches++;
        }
        noResults.hidden = matches > 0;
    }

    search.addEventListener('input', filterCustomers);
    filterCustomers();
}

const directoryScroll = document.getElementById('customer-table-scroll');
const directorySlider = document.getElementById('customer-table-slider');

if (directoryScroll && directorySlider) {
    function syncSlider() {
        const max = directoryScroll.scrollWidth - directoryScroll.clientWidth;
        directorySlider.hidden = max <= 0;
        directoryScroll.classList.toggle('has-slider', max > 0);
        directoryScroll.tabIndex = max > 0 ? 0 : -1;
        if (max > 0) {
            directorySlider.max = max;
            directorySlider.value = directoryScroll.scrollLeft;
            directorySlider.style.setProperty('--slider-thumb', (directoryScroll.clientWidth / directoryScroll.scrollWidth) * 100 + '%');
        }
    }

    directoryScroll.addEventListener('scroll', () => {
        directorySlider.value = directoryScroll.scrollLeft;
    }, { passive: true });
    directorySlider.addEventListener('input', () => {
        directoryScroll.scrollLeft = directorySlider.valueAsNumber;
    });

    const resize = new ResizeObserver(syncSlider);
    resize.observe(directoryScroll);
    resize.observe(directoryScroll.querySelector('table'));
    syncSlider();
}
