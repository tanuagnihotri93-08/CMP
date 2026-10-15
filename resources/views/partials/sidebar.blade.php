<div class="sidebar" id="crm-sidebar">

    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="brand-logo">
            C
        </div>
        <div class="brand-details">
            <h2>
                CRM Pro
            </h2>
            <p>
                Business Suite
            </p>
        </div>
    </div>

    <!-- Navigation -->
    <div class="sidebar-navigation">

        <div class="menu-title">
            OVERVIEW
        </div>

        @if(auth()->user()?->hasPermission('dashboard.view'))
        <a href="{{ route('dashboard') }}"
           class="{{ request()->is('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-fill"></i>
            <span>
                Dashboard
            </span>
        </a>
        @endif

        <div class="menu-title">
            CRM MANAGEMENT
        </div>

        @if(auth()->user()?->hasPermission('leads.view'))
        <a href="{{ route('leads.index') }}"
           class="{{ request()->is('leads*') ? 'active' : '' }}">
            <i class="bi bi-person-lines-fill"></i>
            <span>
                Leads
            </span>
        </a>
        @endif

        @if(auth()->user()?->hasPermission('customers.view'))
        <a href="{{ route('customers.index') }}"
           class="{{ request()->is('customers*') ? 'active' : '' }}">
            <i class="bi bi-building"></i>
            <span>
                Customers
            </span>
        </a>
        @endif

        @if(auth()->user()?->hasPermission('follow_ups.view'))
        <a href="{{ route('followups.index') }}"
           class="{{ request()->is('followups*') ? 'active' : '' }}">
            <i class="bi bi-calendar-check"></i>
            <span>
                Follow Ups
            </span>
        </a>
        @endif

        @if(auth()->user()?->hasPermission('activities.view'))
        <a href="{{ route('activities.index') }}"
           class="{{ request()->is('activities*') ? 'active' : '' }}">
            <i class="bi bi-graph-up-arrow"></i>
            <span>
                Activities
            </span>
        </a>
        @endif

        @if(auth()->user()?->hasPermission('projects.view'))
        <a href="{{ route('projects.index') }}"
           class="{{ request()->is('projects*') ? 'active' : '' }}">
            <i class="bi bi-kanban-fill"></i>
            <span>
                Projects
            </span>
        </a>
        @endif

        @if(auth()->user()?->hasPermission('tasks.view'))
        <a href="{{ route('tasks.index') }}"
           class="{{ request()->is('tasks*') ? 'active' : '' }}">
            <i class="bi bi-list-task"></i>
            <span>
                Tasks
            </span>
        </a>
        @endif

       @if(auth()->user()?->hasPermission('performance.view'))
<a href="{{ route('performance.index') }}"
   class="{{ request()->is('performance') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i>
    <span>Performance</span>
</a>
@endif

@if(auth()->user()?->hasPermission('performance.feedback.view'))
<a href="{{ route('performance-feedback.index') }}"
   class="{{ request()->is('performance-feedback*') ? 'active' : '' }}">
    <i class="bi bi-chat-square-text"></i>
    <span>Feedback</span>
</a>
@endif
        @if(auth()->user()?->hasPermission('audit_logs.view')
            || auth()->user()?->hasPermission('reports.view')
            || auth()->user()?->hasPermission('users.manage')
            || auth()->user()?->hasPermission('roles.manage'))
            <div class="menu-title">
                SYSTEM
            </div>
        @endif

        @if(auth()->user()?->hasPermission('audit_logs.view'))
        <a href="{{ route('audit-logs.index') }}"
           class="{{ request()->is('audit-logs*') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i>
            <span>Audit Logs</span>
        </a>
        @endif

        @if(auth()->user()?->hasPermission('reports.view'))
        <a href="{{ route('reports.index') }}"
           class="{{ request()->is('reports*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i>
            <span>Reports</span>
        </a>
        @endif

        @if(auth()->user()?->hasPermission('users.manage'))
        <a href="{{ route('admin.users.index') }}"
           class="{{ request()->is('admin/users*') ? 'active' : '' }}">
            <i class="bi bi-people"></i>
            <span>User Management</span>
        </a>
        @endif

        @if(auth()->user()?->hasPermission('roles.manage'))
        <a href="{{ route('admin.roles.index') }}"
           class="{{ request()->is('admin/roles*') ? 'active' : '' }}">
            <i class="bi bi-shield-check"></i>
            <span>Roles &amp; Permissions</span>
        </a>
        @endif

    </div>

    <!-- User Profile -->
    <div class="sidebar-profile">
        <div class="profile-avatar">
            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
            <span></span>
        </div>

        <div class="profile-info">
            <h4>
                {{ auth()->user()->name }}
            </h4>
            <p>
                {{ auth()->user()->role?->display_name ?? 'CRM User' }}
            </p>
        </div>

        <form action="{{ route('logout') }}" method="POST" class="logout-form" novalidate>
            @csrf
            <button type="submit" class="logout-btn" aria-label="Sign out">
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </form>
    </div>
</div>

<style>
/* ================================
   CRM PRO SIDEBAR THEME
   Dark Futuristic SaaS Style
================================ */

.sidebar {
    width: var(--sidebar-width, 280px);
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    padding: 28px 18px;
    display: flex;
    flex-direction: column;
    background: 
        radial-gradient(circle at top left, rgba(37,99,235,.18), transparent 35%),
        radial-gradient(circle at bottom, rgba(124,58,237,.12), transparent 40%),
        linear-gradient(180deg, #020617, #0b1224);
    border-right: 1px solid rgba(255,255,255,.08);
    box-shadow: 20px 0 60px rgba(0,0,0,.45);
    backdrop-filter: blur(25px);
    z-index: 100;
}

/* BRAND */
.sidebar-brand {
    display: flex;
    flex: 0 0 auto;
    align-items: center;
    gap: 14px;
    padding: 0 8px 30px;
    border-bottom: 1px solid rgba(255,255,255,.10);
}

.brand-logo {
    height: 52px;
    width: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    font-size: 24px;
    font-weight: 700;
    color: white;
    box-shadow: 0 10px 30px rgba(37,99,235,.45);
    animation: logoFloat 3s ease-in-out infinite;
}

@keyframes logoFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}

.brand-details h2 {
    margin: 0;
    font-size: 22px;
    color: #f8fafc;
}

.brand-details p {
    margin: 4px 0 0;
    font-size: 12px;
    color: #94a3b8;
}

/* NAVIGATION */
.sidebar-navigation {
    margin-top: 28px;
    min-height: 0;
    flex: 1 1 auto;
    overflow-x: hidden;
    overflow-y: auto;
    overscroll-behavior: contain;
    scrollbar-color: rgba(148,163,184,.35) transparent;
    scrollbar-width: thin;
}

.sidebar-navigation::-webkit-scrollbar {
    width: 6px;
}

.sidebar-navigation::-webkit-scrollbar-thumb {
    border-radius: 99px;
    background: rgba(148,163,184,.35);
}

.menu-title {
    font-size: 11px;
    letter-spacing: 1.5px;
    color: #64748b;
    margin: 22px 0 12px 10px;
}

.sidebar-navigation a {
    height: 48px;
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 0 15px;
    margin-bottom: 8px;
    border-radius: 14px;
    text-decoration: none;
    color: #cbd5e1;
    position: relative;
    overflow: hidden;
    transition: .35s ease;
}

.sidebar-navigation a i {
    font-size: 18px;
    width: 22px;
    text-align: center;
    transition: .3s ease;
}

/* HOVER */
.sidebar-navigation a:hover {
    background: linear-gradient(135deg, rgba(37,99,235,.25), rgba(124,58,237,.25));
    color: white;
    transform: translateX(8px);
}

.sidebar-navigation a:hover i {
    transform: scale(1.15) rotate(5deg);
}

.sidebar-navigation a::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,.15), transparent);
    transform: translateX(-120%);
    transition: .6s ease;
}

.sidebar-navigation a:hover::after {
    transform: translateX(120%);
}

/* ACTIVE MENU */
.sidebar-navigation a.active {
    background: linear-gradient(135deg, rgba(37,99,235,.95), rgba(124,58,237,.95));
    box-shadow: 0 10px 35px rgba(37,99,235,.45), inset 0 0 20px rgba(255,255,255,.15);
    transform: translateX(5px);
}

/* PROFILE CARD */
.sidebar-profile {
    position: relative;
    flex: 0 0 auto;
    margin-top: 16px;
    padding: 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 18px;
    transition: .35s ease;
}

.sidebar-profile:hover {
    transform: translateY(-5px);
    background: rgba(255,255,255,.10);
    box-shadow: 0 20px 45px rgba(0,0,0,.25);
}

.profile-avatar {
    height: 42px;
    width: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    position: relative;
    flex-shrink: 0;
}

.profile-avatar span {
    height: 10px;
    width: 10px;
    position: absolute;
    right: 0;
    bottom: 2px;
    background: #22c55e;
    border-radius: 50%;
    border: 2px solid #020617;
}

.profile-info {
    overflow: hidden;
}

.profile-info h4 {
    margin: 0;
    font-size: 14px;
    color: #f8fafc;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.profile-info p {
    margin: 3px 0 0;
    font-size: 12px;
    color: #94a3b8;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* LOGOUT */
.logout-form {
    margin-left: auto;
}

.logout-btn {
    height: 36px;
    width: 36px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.05);
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: .35s ease;
}

.logout-btn:hover {
    background: rgba(239,68,68,.15);
    color: #ef4444;
    transform: translateY(-3px);
}

/* ================================
   COLLAPSED SIDEBAR
================================ */
body.sidebar-collapsed .sidebar {
    width: 90px;
}

body.sidebar-collapsed .brand-details {
    display: none;
}

body.sidebar-collapsed .sidebar-brand {
    justify-content: center;
    padding: 0 0 20px;
}

body.sidebar-collapsed .menu-title {
    display: none;
}

body.sidebar-collapsed .sidebar-navigation a {
    justify-content: center;
    padding: 0;
}

body.sidebar-collapsed .sidebar-navigation span {
    display: none;
}

body.sidebar-collapsed .sidebar-profile {
    justify-content: center;
    padding: 12px;
    flex-direction: column;
    gap: 8px;
}

body.sidebar-collapsed .profile-info {
    display: none;
}

body.sidebar-collapsed .logout-form {
    margin-left: 0;
}

</style>