import './bootstrap';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

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