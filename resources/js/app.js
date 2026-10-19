import './bootstrap';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

const themeToggle = document.querySelector('.crm-theme-toggle');
const chartThemeSnapshots = new WeakMap();
const getChartPalette = () => {
    const styles = getComputedStyle(document.documentElement);
    const series = [
        ['--crm-chart-blue', '37, 99, 235'],
        ['--crm-chart-cyan', '6, 182, 212'],
        ['--crm-chart-green', '16, 185, 129'],
        ['--crm-chart-amber', '245, 158, 11'],
    ];

    return series.map(([name, rgb]) => ({
        color: styles.getPropertyValue(name).trim() || `rgb(${rgb})`,
        fill: `rgba(${rgb}, .16)`,
    }));
};

const updateChartTheme = (theme) => {
    const charts = window.Chart?.instances;
    if (!charts) {
        return;
    }

    const chartPalette = getChartPalette();

    Object.values(charts).forEach((chart) => {
        if (!chartThemeSnapshots.has(chart)) {
            chartThemeSnapshots.set(chart, {
                datasets: chart.data.datasets.map((dataset) => ({
                    backgroundColor: dataset.backgroundColor,
                    borderColor: dataset.borderColor,
                })),
                ticks: Object.fromEntries(Object.entries(chart.options.scales || {}).map(([axis, options]) => [axis, options.ticks?.color])),
                grids: Object.fromEntries(Object.entries(chart.options.scales || {}).map(([axis, options]) => [axis, options.grid?.color])),
                legend: chart.options.plugins?.legend?.labels?.color,
            });
        }

        const original = chartThemeSnapshots.get(chart);

        if (theme === 'dark') {
            chart.data.datasets.forEach((dataset, index) => {
                dataset.backgroundColor = original.datasets[index]?.backgroundColor;
                dataset.borderColor = original.datasets[index]?.borderColor;
            });
            Object.entries(chart.options.scales || {}).forEach(([axis, options]) => {
                options.ticks.color = original.ticks[axis];
                options.grid.color = original.grids[axis];
            });
            if (chart.options.plugins?.legend?.labels) {
                chart.options.plugins.legend.labels.color = original.legend;
            }
        } else {
            chart.data.datasets.forEach((dataset, index) => {
                const palette = chartPalette[index % chartPalette.length];
                dataset.backgroundColor = Array.isArray(original.datasets[index]?.backgroundColor)
                    ? Array.from({ length: original.datasets[index].backgroundColor.length || chart.data.labels?.length || chartPalette.length }, (_, colorIndex) => chartPalette[colorIndex % chartPalette.length].color)
                    : palette.fill;
                dataset.borderColor = chart.config.type === 'doughnut' || chart.config.type === 'pie'
                    ? '#FFFFFF'
                    : palette.color;
            });
            Object.values(chart.options.scales || {}).forEach((options) => {
                options.ticks.color = '#64748B';
                options.grid.color = 'rgba(148, 163, 184, .24)';
            });
            if (chart.options.plugins?.legend?.labels) {
                chart.options.plugins.legend.labels.color = '#475569';
            }
        }

        chart.update('none');
    });
};

const setTheme = (theme) => {
    const normalizedTheme = theme === 'light' ? 'light' : 'dark';
    document.documentElement.dataset.theme = normalizedTheme;

    try {
        localStorage.setItem('crm-theme', normalizedTheme);
    } catch (error) {
        // Keep the current session theme active when browser storage is unavailable.
    }

    if (themeToggle) {
        const isLight = normalizedTheme === 'light';
        themeToggle.setAttribute('aria-pressed', String(isLight));
        themeToggle.setAttribute('aria-label', `Switch to ${isLight ? 'dark' : 'light'} theme`);
        themeToggle.title = `Switch to ${isLight ? 'dark' : 'light'} theme`;
        themeToggle.innerHTML = `<i class="bi bi-${isLight ? 'moon-stars' : 'sun'}" aria-hidden="true"></i>`;
    }

    updateChartTheme(normalizedTheme);
};

themeToggle?.addEventListener('click', () => {
    setTheme(document.documentElement.dataset.theme === 'light' ? 'dark' : 'light');
});

setTheme(document.documentElement.dataset.theme || 'dark');
document.addEventListener('DOMContentLoaded', () => updateChartTheme(document.documentElement.dataset.theme || 'dark'));

const mobileNavigation = window.matchMedia('(max-width: 620px)');
const compactNavigation = window.matchMedia('(min-width: 621px) and (max-width: 1024px)');
const sidebar = document.getElementById('crm-sidebar');
const sidebarToggle = document.querySelector('.sidebar-toggle');
const sidebarOverlay = document.querySelector('.sidebar-overlay');

const syncSidebarAccessibility = () => {
    const isMobile = mobileNavigation.matches;
    const isOpen = document.body.classList.contains('sidebar-open');

    if (sidebar) {
        sidebar.inert = isMobile && !isOpen;
        sidebar.setAttribute('aria-hidden', String(isMobile && !isOpen));
    }

    if (sidebarToggle) {
        sidebarToggle.setAttribute('aria-expanded', String(isMobile ? isOpen : !document.body.classList.contains('sidebar-collapsed')));
    }

    if (sidebarOverlay) {
        sidebarOverlay.hidden = !isMobile || !isOpen;
        sidebarOverlay.setAttribute('aria-hidden', String(!isMobile || !isOpen));
        sidebarOverlay.tabIndex = isMobile && isOpen ? 0 : -1;
    }
};

const closeMobileSidebar = (restoreFocus = false) => {
    document.body.classList.remove('sidebar-open');
    syncSidebarAccessibility();

    if (restoreFocus) {
        sidebarToggle?.focus();
    }
};

window.toggleSidebar = function () {
    if (mobileNavigation.matches) {
        document.body.classList.toggle('sidebar-open');
    } else {
        document.body.classList.toggle('sidebar-collapsed');
    }

    syncSidebarAccessibility();
};

const syncSidebarToViewport = () => {
    document.body.classList.remove('sidebar-open');
    document.body.classList.toggle('sidebar-collapsed', compactNavigation.matches);
    syncSidebarAccessibility();
};

syncSidebarToViewport();
mobileNavigation.addEventListener('change', syncSidebarToViewport);
compactNavigation.addEventListener('change', syncSidebarToViewport);
sidebarOverlay?.addEventListener('click', () => closeMobileSidebar(true));
sidebar?.addEventListener('click', (event) => {
    if (mobileNavigation.matches && event.target.closest('.sidebar-navigation a')) {
        closeMobileSidebar();
    }
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
        closeMobileSidebar(true);
    }
});

window.crmToast = function (icon, title) {
    return Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title,
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
    });
};

const flashMessages = document.getElementById('crm-flash-alerts');

if (flashMessages) {
    const { success, error, validationError } = flashMessages.dataset;

    if (error) {
        window.crmToast('error', error);
    } else if (validationError === 'true') {
        window.crmToast('error', 'Please review the highlighted fields.');
    } else if (success) {
        document.querySelectorAll('.module-alert, .alert-success').forEach((alert) => alert.remove());
        window.crmToast('success', success);
    }
}

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('form');

    if (!form) {
        return;
    }

    if (form.dataset.confirm && form.dataset.confirmed !== 'true') {
        event.preventDefault();

        const result = await Swal.fire({
            title: form.dataset.confirmTitle || 'Confirm deletion',
            text: form.dataset.confirm,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#b42318',
            cancelButtonColor: '#64748b',
            reverseButtons: true,
            focusCancel: true,
        });

        if (result.isConfirmed) {
            form.dataset.confirmed = 'true';
            form.requestSubmit();
        }

        return;
    }

    delete form.dataset.confirmed;

    if (form.dataset.crmSubmitting === 'true') {
        event.preventDefault();
        return;
    }

    if (form.hasAttribute('data-no-loading')) {
        return;
    }

    form.dataset.crmSubmitting = 'true';
    form.setAttribute('aria-busy', 'true');

    form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]').forEach((button) => {
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');

        if (button instanceof HTMLInputElement) {
            button.value = button.dataset.loadingLabel || 'Processing...';
            return;
        }

        const label = button.dataset.loadingLabel || button.textContent.trim() || 'Processing...';
        button.replaceChildren();

        const labelElement = document.createElement('span');
        labelElement.textContent = label;

        const spinner = document.createElement('span');
        spinner.className = 'crm-loading-spinner';
        spinner.setAttribute('aria-hidden', 'true');

        button.append(labelElement, spinner);
    });
});

document.addEventListener('click', (event) => {
    const link = event.target.closest('a[data-export-loading]');

    if (!link) {
        return;
    }

    if (link.dataset.crmExporting === 'true') {
        event.preventDefault();
        return;
    }

    link.dataset.crmExporting = 'true';
    link.setAttribute('aria-disabled', 'true');
    link.setAttribute('aria-busy', 'true');
    link.classList.add('is-loading');

    const spinner = document.createElement('span');
    spinner.className = 'crm-loading-spinner';
    spinner.setAttribute('aria-hidden', 'true');
    link.append(spinner);

    window.setTimeout(() => {
        delete link.dataset.crmExporting;
        link.removeAttribute('aria-disabled');
        link.removeAttribute('aria-busy');
        link.classList.remove('is-loading');
        spinner.remove();
    }, 12000);
});

document.querySelectorAll('.lead-filter-card').forEach((filterCard) => {
    const page = filterCard.closest('.lead-crud-page');
    const search = filterCard.querySelector('[data-table-search]');
    const status = filterCard.querySelector('[data-table-status]');
    if (!page || !search || !status) {
        return;
    }

    const rows = Array.from(page.querySelectorAll('.module-filter-row'));
    const emptyRow = page.querySelector('.module-filter-empty');

    const filterRows = () => {
        const term = search.value.trim().toLocaleLowerCase();
        let visibleCount = 0;

        rows.forEach((row) => {
            const matchesSearch = row.dataset.filterSearch.includes(term);
            const matchesStatus = !status.value || row.dataset.filterStatus === status.value;
            row.hidden = !matchesSearch || !matchesStatus;
            visibleCount += row.hidden ? 0 : 1;
        });

        if (emptyRow) {
            emptyRow.hidden = visibleCount > 0;
        }
    };

    search.addEventListener('input', filterRows);
    status.addEventListener('change', filterRows);
});

document.querySelectorAll('[data-crm-card-filter]').forEach((filterCard) => {
    const page = filterCard.closest('.crm-page');
    const search = filterCard.querySelector('[data-crm-filter-search]');
    const role = filterCard.querySelector('[data-crm-filter-role]');
    const rows = Array.from(page.querySelectorAll('.crm-filterable-row'));
    const emptyState = page.querySelector('.crm-filter-empty');

    const filterRows = () => {
        const term = search.value.trim().toLocaleLowerCase();
        let visibleCount = 0;

        rows.forEach((row) => {
            const matchesSearch = row.dataset.crmFilterSearch.includes(term);
            const matchesRole = !role.value || row.dataset.crmFilterRole === role.value;
            row.hidden = !matchesSearch || !matchesRole;
            visibleCount += row.hidden ? 0 : 1;
        });

        if (emptyState) {
            emptyState.hidden = visibleCount > 0;
        }
    };

    search.addEventListener('input', filterRows);
    role.addEventListener('change', filterRows);
});

/* =========================================
   GLOBAL FOLLOW-UP REMINDER POPUP LISTENER
   Appears on every CRM page via broadcast
========================================= */

const body = document.querySelector('body');
const userId = body?.dataset?.userId;

let notificationAudioContext = null;

function unlockNotificationAudio() {
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) {
        return;
    }

    notificationAudioContext ??= new AudioContext();
    if (notificationAudioContext.state === 'suspended') {
        notificationAudioContext.resume().catch(() => {});
    }
}

document.addEventListener('pointerdown', unlockNotificationAudio, { once: true, capture: true });
document.addEventListener('keydown', unlockNotificationAudio, { once: true, capture: true });

function playNotificationSound() {
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) {
        return;
    }

    notificationAudioContext ??= new AudioContext();
    const audioCtx = notificationAudioContext;
    const play = () => {
        const start = audioCtx.currentTime;
        [880, 1175].forEach((frequency, index) => {
            const oscillator = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            const at = start + index * 0.13;
            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(frequency, at);
            gain.gain.setValueAtTime(0.0001, at);
            gain.gain.exponentialRampToValueAtTime(0.12, at + 0.015);
            gain.gain.exponentialRampToValueAtTime(0.0001, at + 0.16);
            oscillator.connect(gain);
            gain.connect(audioCtx.destination);
            oscillator.start(at);
            oscillator.stop(at + 0.17);
        });
    };

    if (audioCtx.state === 'suspended') {
        audioCtx.resume().then(play).catch(() => {});
    } else {
        play();
    }
}

function incrementUnreadNotificationBadge() {
    const toggle = document.querySelector('.crm-notification-toggle');
    if (!toggle) {
        return;
    }

    const badge = toggle.querySelector('.crm-notification-count');
    const current = Number(toggle.dataset.unreadCount ?? badge?.dataset.count ?? 0);
    const count = current + 1;
    toggle.dataset.unreadCount = String(count);

    const countBadge = badge ?? document.createElement('span');
    countBadge.classList.add('crm-notification-count');
    countBadge.dataset.count = String(count);
    countBadge.setAttribute('aria-label', `${count} unread`);
    countBadge.textContent = count > 99 ? '99+' : String(count);
    if (!badge) {
        toggle.append(countBadge);
    }
    toggle.setAttribute('aria-label', `Notifications, ${count} unread`);

    const unreadLabel = document.querySelector('.crm-notification-heading > div > span');
    if (unreadLabel) {
        unreadLabel.textContent = `${count} unread`;
    }
}

function escapeNotificationHtml(value) {
    const entities = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    };

    return String(value).replace(/[&<>"']/g, (character) => entities[character]);
}

if (userId && window.Echo) {
    window.Echo.private(`App.Models.User.${userId}`)
        .notification((event) => {
            const notification = event.notification ?? event;
            const data = notification.data ?? event.data ?? notification;
            const type = event.type ?? notification.type ?? data.type;
            const isReminder = type === 'reminder'
                || data.type === 'reminder'
                || (data.module === 'follow_ups' && data.urgency !== 'emergency');

            if (data.urgency === 'emergency' || type === 'emergency') {
                incrementUnreadNotificationBadge();
                return;
            }

            incrementUnreadNotificationBadge();
            playNotificationSound();

            if (isReminder) {
                Swal.fire({
                    title: data.title ?? 'Follow Up Reminder',
                    html: escapeNotificationHtml(data.message ?? 'You have a follow up scheduled.'),
                    icon: 'warning',
                    confirmButtonText: data.action_url ? 'View Follow Up' : 'Dismiss',
                    cancelButtonText: 'Dismiss',
                    showCancelButton: true,
                    timer: 30000,
                    timerProgressBar: true,
                    allowOutsideClick: false,
                    allowEscapeKey: true,
                    showConfirmButton: true,
                    customClass: {
                        container: 'followup-reminder-swal-container',
                        popup: 'followup-reminder-swal-popup',
                        title: 'followup-reminder-swal-title',
                        htmlContainer: 'followup-reminder-swal-html',
                        confirmButton: 'followup-reminder-swal-confirm',
                        cancelButton: 'followup-reminder-swal-cancel',
                    },
                }).then((result) => {
                    if (result.isConfirmed && data.action_url) {
                        window.location.href = data.action_url;
                    }
                });
                return;
            }

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: data.title ?? 'New CRM notification',
                text: data.message ?? 'You have a new notification.',
                showConfirmButton: Boolean(data.action_url),
                confirmButtonText: 'View',
                timer: data.action_url ? undefined : 7000,
                timerProgressBar: !data.action_url,
            }).then((result) => {
                if (result.isConfirmed && data.action_url) {
                    window.location.href = data.action_url;
                }
            });
        });
}

/* =========================================
   GLOBAL EMERGENCY FOLLOW-UP ALERT LISTENER
   Red overlay + hooter sound + CONFIRM button
   Appears on every CRM page via broadcast
========================================= */

const EMERGENCY_ALERT_MAX_DURATION = 2 * 60 * 1000;
let emergencySound = null;
let emergencyAlertTimeout = null;

function removeEmergencyAudioGestureListeners() {
    document.removeEventListener('pointerdown', retryEmergencySound, true);
    document.removeEventListener('keydown', retryEmergencySound, true);
}

function retryEmergencySound() {
    const activeSound = emergencySound;
    if (!activeSound || !activeSound.blocked) {
        return;
    }

    // Calling play() directly from this user gesture can satisfy autoplay policies.
    activeSound.blocked = false;
    const playRequest = activeSound.audio.play();
    if (playRequest && typeof playRequest.then === 'function') {
        playRequest.then(removeEmergencyAudioGestureListeners).catch((error) => {
            activeSound.blocked = error?.name === 'NotAllowedError';
            if (!activeSound.blocked) {
                removeEmergencyAudioGestureListeners();
                console.warn('Emergency alert sound could not be played.', error);
            }
        });
    } else {
        removeEmergencyAudioGestureListeners();
    }
}

function playEmergencySound(source) {
    if (emergencySound || !source) {
        return;
    }

    const audio = new Audio(source);
    audio.preload = 'auto';
    audio.loop = true;
    audio.volume = 1;
    emergencySound = { audio, blocked: false, timeoutId: null };

    // The timer starts with the alert, even if the browser initially blocks playback.
    emergencySound.timeoutId = window.setTimeout(stopEmergencySound, EMERGENCY_ALERT_MAX_DURATION);

    const playRequest = audio.play();
    if (playRequest && typeof playRequest.then === 'function') {
        playRequest.catch((error) => {
            if (error?.name === 'NotAllowedError') {
                emergencySound.blocked = true;
                document.addEventListener('pointerdown', retryEmergencySound, true);
                document.addEventListener('keydown', retryEmergencySound, true);
                return;
            }

            console.warn('Emergency alert sound could not be played.', error);
        });
    }
}

function stopEmergencySound() {
    if (!emergencySound) {
        return;
    }

    const activeSound = emergencySound;
    emergencySound = null;
    window.clearTimeout(activeSound.timeoutId);
    removeEmergencyAudioGestureListeners();
    activeSound.audio.pause();
    activeSound.audio.currentTime = 0;
}

function getConfirmedEmergencyIds() {
    try {
        const raw = sessionStorage.getItem('crm_emergency_confirmed');
        return raw ? JSON.parse(raw) : [];
    } catch (e) {
        return [];
    }
}

function addConfirmedEmergencyId(followUpId) {
    const ids = getConfirmedEmergencyIds();
    if (!ids.includes(followUpId)) {
        ids.push(followUpId);
        try {
            sessionStorage.setItem('crm_emergency_confirmed', JSON.stringify(ids));
        } catch (e) { /* ignore */ }
    }
}

function showEmergencyAlert(data) {
    const followUpId = data?.follow_up_id ?? null;

    // Prevent re-showing already confirmed emergency
    if (followUpId && getConfirmedEmergencyIds().includes(followUpId)) {
        return;
    }

    const container = document.getElementById('emergency-alert-container');
    if (!container) {
        return;
    }

    playEmergencySound(container.dataset.emergencyAudioSrc);

    const leadName = escapeNotificationHtml(data?.lead_id ? `Lead #${data.lead_id}` : 'Unknown Lead');
    const title = escapeNotificationHtml(data?.title ?? 'Emergency Follow Up Alert');
    const message = escapeNotificationHtml(data?.message ?? 'You have an urgent follow up scheduled.');
    const type = escapeNotificationHtml(data?.type ?? 'Follow Up');
    const scheduledAt = escapeNotificationHtml(data?.scheduled_at ? new Date(data.scheduled_at).toLocaleString() : 'Not set');
    const remark = data?.remark ? escapeNotificationHtml(data.remark) : null;
    const actionUrl = data?.action_url ?? null;

    const html = `
        <div class="emergency-alert-card" role="alertdialog" aria-modal="true" aria-label="Emergency Follow Up Alert">
            <span class="emergency-alert-icon"><i class="bi bi-bell-fill"></i></span>
            <h2 class="emergency-alert-title"><i class="bi bi-exclamation-triangle-fill"></i> ${title}</h2>
            <p class="emergency-alert-body">${message}</p>
            <div class="emergency-alert-detail"><i class="bi bi-person-fill"></i> ${leadName}</div>
            <div class="emergency-alert-detail"><i class="bi bi-telephone-outbound-fill"></i> ${type}</div>
            <div class="emergency-alert-detail"><i class="bi bi-clock-fill"></i> ${scheduledAt}</div>
            ${remark ? `<div class="emergency-alert-detail"><i class="bi bi-chat-right-text-fill"></i> ${remark}</div>` : ''}
            <div class="emergency-alert-actions">
                <button type="button" class="emergency-alert-btn-confirm" id="emergency-alert-confirm-btn">ACKNOWLEDGE</button>
            </div>
        </div>
    `;

    container.innerHTML = html;
    container.style.display = 'flex'; // Fix: Ensures display layout and popup visibility alongside overlay blur
    container.classList.remove('hidden');

    const confirmBtn = document.getElementById('emergency-alert-confirm-btn');
    const hideAlert = () => {
        window.clearTimeout(emergencyAlertTimeout);
        stopEmergencySound();
        container.classList.add('hidden');
        container.style.display = 'none'; // Fix: Completely resets display to avoid phantom blur layer
        container.innerHTML = '';
        if (followUpId) {
            addConfirmedEmergencyId(followUpId);
        }
    };

    confirmBtn.addEventListener('click', hideAlert);
    confirmBtn.focus();

    // Keep the prominent alert visible for the same bounded period as its sound.
    window.clearTimeout(emergencyAlertTimeout);
    emergencyAlertTimeout = window.setTimeout(() => {
        if (!container.classList.contains('hidden')) {
            hideAlert();
        }
    }, EMERGENCY_ALERT_MAX_DURATION);
}

const emergencyBody = document.querySelector('body');
const emergencyUserId = emergencyBody?.dataset?.userId;

if (emergencyUserId && window.Echo) {
    window.Echo.private(`App.Models.User.${emergencyUserId}`)
        .notification((event) => {
            const notification = event.notification ?? event;
            const data = notification.data ?? event.data ?? notification;
            const isEmergency = data?.urgency === 'emergency' || event.type === 'emergency';

            if (!isEmergency) {
                return;
            }

            showEmergencyAlert(data);
        });
}
