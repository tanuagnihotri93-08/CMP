<div class="navbar">


    <!-- Left Section -->

    <div class="navbar-left">


        <button
            class="sidebar-toggle"
            type="button"
            aria-label="Toggle navigation menu"
            aria-controls="crm-sidebar"
            aria-expanded="true"
            onclick="toggleSidebar()">

            <i class="bi bi-list"></i>

        </button>




        <div class="page-greeting">


            <h3>
                Welcome back, {{ auth()->user()->name }}
            </h3>


            <p>
                Manage your CRM operations efficiently
            </p>


        </div>



    </div>






    <!-- Right Section -->


    <div class="navbar-right">

        @php
            $navbarNotifications = auth()->user()->notifications()->latest()->limit(8)->get();
            $unreadNotificationCount = auth()->user()->unreadNotifications()->count();
        @endphp

        <details class="crm-notification-menu">
            <summary class="crm-notification-toggle" aria-label="Notifications, {{ $unreadNotificationCount }} unread" title="Notifications">
                <i class="bi bi-bell" aria-hidden="true"></i>
                <span>Notifications</span>
                @if($unreadNotificationCount > 0)
                    <span class="crm-notification-count" aria-label="{{ $unreadNotificationCount }} unread">
                        {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                    </span>
                @endif
            </summary>

            <div class="crm-notification-panel">
                <div class="crm-notification-heading">
                    <div>
                        <strong>Notifications</strong>
                        <span>{{ $unreadNotificationCount }} unread</span>
                    </div>
                    @if($unreadNotificationCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}" novalidate>
                            @csrf
                            <button type="submit" class="crm-notification-mark-all">Mark all read</button>
                        </form>
                    @endif
                </div>

                @forelse($navbarNotifications as $notification)
                    <form
                        method="POST"
                        action="{{ route('notifications.read', $notification->id) }}"
                        @class(['crm-notification-item', 'is-unread' => is_null($notification->read_at)])
                        novalidate>
                        @csrf
                        <button type="submit" class="crm-notification-entry">
                            <span class="crm-notification-indicator" aria-hidden="true"></span>
                            <span class="crm-notification-copy">
                                <strong>{{ $notification->data['title'] ?? 'CRM notification' }}</strong>
                                <span>{{ $notification->data['message'] ?? '' }}</span>
                                <time datetime="{{ $notification->created_at->toIso8601String() }}">
                                    {{ $notification->created_at->diffForHumans() }}
                                </time>
                                <span class="crm-notification-read-state">
                                    {{ is_null($notification->read_at) ? 'Unread' : 'Read' }}
                                </span>
                            </span>
                        </button>
                    </form>
                @empty
                    <p class="crm-notification-empty">You are all caught up.</p>
                @endforelse
            </div>
        </details>

        <button
            class="crm-theme-toggle"
            type="button"
            aria-label="Switch to light theme"
            aria-pressed="false"
            title="Switch to light theme">
            <i class="bi bi-sun" aria-hidden="true"></i>
        </button>





        <div class="admin-profile">


            <div class="admin-avatar">

                {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}

            </div>



            <div class="admin-details">


                <h4>
                    {{ auth()->user()->name }}
                </h4>


                <p>
                    {{ auth()->user()->role?->display_name ?? 'CRM User' }}
                </p>


            </div>



            <i class="bi bi-chevron-down" aria-hidden="true"></i>



        </div>





    </div>



</div>







<style>

/* ================================
   CRM PRO NAVBAR THEME
================================ */


.navbar{

    width:100%;
    margin-left:0;
    box-sizing:border-box;

    height:80px;


    background:

    rgba(15,23,42,.75);


    backdrop-filter:

    blur(20px);


    -webkit-backdrop-filter:

    blur(20px);



    display:flex;

    align-items:center;

    justify-content:space-between;


    padding:0 35px;


    border-bottom:

    1px solid rgba(255,255,255,.08);



    position:sticky;


    top:0;


    z-index:900;


    transition:.35s ease;


}





/* LEFT */


.navbar-left{

    display:flex;

    align-items:center;

    gap:20px;

}





.sidebar-toggle{


    height:42px;

    width:42px;


    border-radius:14px;


    border:

    1px solid rgba(255,255,255,.10);



    background:

    rgba(255,255,255,.06);



    color:#cbd5e1;



    display:flex;


    align-items:center;


    justify-content:center;



    cursor:pointer;


    font-size:20px;


    transition:.3s ease;

}




.sidebar-toggle:hover{


    background:

    linear-gradient(
        135deg,
        #2563eb,
        #7c3aed
    );


    color:white;


    box-shadow:

    0 10px 25px rgba(37,99,235,.35);

}





/* GREETING */


.page-greeting h3{


    margin:0;


    font-size:18px;


    color:#f8fafc;


}





.page-greeting p{


    margin:3px 0 0;


    font-size:13px;


    color:#94a3b8;


}





/* RIGHT */


.navbar-right{


    display:flex;

    align-items:center;

    gap:20px;

}





/* ================================
   NOTIFICATION PANEL (PREMIUM LOOK)
================================ */

.crm-notification-panel{
    background:
        radial-gradient(circle at top right, rgba(124,58,237,.18), transparent 55%),
        radial-gradient(circle at top left, rgba(37,99,235,.16), transparent 50%),
        rgba(15,23,42,.92);
    backdrop-filter:blur(24px);
    -webkit-backdrop-filter:blur(24px);
    border:1px solid rgba(255,255,255,.10);
    border-radius:22px;
    box-shadow:
        0 30px 70px rgba(0,0,0,.55),
        inset 0 1px 0 rgba(255,255,255,.08);
    overflow:hidden;
    animation:notifPanelIn .25s ease;
}

@keyframes notifPanelIn{
    from{ opacity:0; transform:translateY(-10px) scale(.97); }
    to{ opacity:1; transform:translateY(0) scale(1); }
}

@supports selector(details::details-content) {
    .crm-notification-menu::details-content {
        content-visibility: hidden;
        transition: content-visibility .25s allow-discrete;
    }

    .crm-notification-menu[open]::details-content {
        content-visibility: visible;
    }

    .crm-notification-panel {
        animation: none;
        opacity: 0;
        transform: translateY(-10px) scale(.97);
        transition: opacity .25s ease, transform .25s ease;
    }

    .crm-notification-menu[open] .crm-notification-panel {
        opacity: 1;
        transform: translateY(0) scale(1);
    }

    @starting-style {
        .crm-notification-menu[open] .crm-notification-panel {
            opacity: 0;
            transform: translateY(-10px) scale(.97);
        }
    }
}

/* Heading */
.crm-notification-heading{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:18px 20px;
    background:rgba(15,23,42,.6);
    border-bottom:1px solid rgba(255,255,255,.08);
}
.crm-notification-heading strong{
    display:block;
    color:#f8fafc;
    font-size:16px;
    font-weight:700;
}
.crm-notification-heading span{
    display:inline-block;
    margin-top:4px;
    padding:2px 10px;
    font-size:11px;
    font-weight:600;
    color:#93c5fd;
    background:rgba(59,130,246,.15);
    border:1px solid rgba(96,165,250,.25);
    border-radius:999px;
}

.crm-notification-mark-all{
    padding:8px 14px;
    font-size:12px;
    font-weight:600;
    color:#e2e8f0;
    background:rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.12);
    border-radius:12px;
    cursor:pointer;
    transition:.3s ease;
}
.crm-notification-mark-all:hover{
    color:#fff;
    background:linear-gradient(135deg,#2563eb,#7c3aed);
    border-color:transparent;
    box-shadow:0 8px 20px rgba(37,99,235,.35);
}

/* Items */
.crm-notification-item{
    position:relative;
    display:flex;
    align-items:flex-start;
    gap:14px;
    padding:16px 20px;
    border-bottom:1px solid rgba(255,255,255,.05);
    transition:.25s ease;
}
.crm-notification-item:last-of-type{ border-bottom:none; }
.crm-notification-item:hover{ background:rgba(255,255,255,.05); }

.crm-notification-item.is-unread{
    background:linear-gradient(90deg, rgba(59,130,246,.14), rgba(59,130,246,0) 80%);
}
.crm-notification-item.is-unread::before{
    content:"";
    position:absolute;
    left:0; top:12px; bottom:12px;
    width:3px;
    border-radius:0 4px 4px 0;
    background:linear-gradient(180deg,#2563eb,#7c3aed);
}

.crm-notification-indicator{
    flex-shrink:0;
    width:10px;
    height:10px;
    margin-top:6px;
    border-radius:50%;
    background:#475569;
}
.crm-notification-item.is-unread .crm-notification-indicator{
    background:linear-gradient(135deg,#60a5fa,#a78bfa);
    box-shadow:0 0 0 4px rgba(96,165,250,.18), 0 0 12px rgba(96,165,250,.6);
}

.crm-notification-copy{ flex:1; min-width:0; }
.crm-notification-copy strong{
    display:block;
    color:#f8fafc;
    font-size:14px;
    font-weight:600;
    line-height:1.35;
}
.crm-notification-copy > span:not(.crm-notification-read-state){
    display:block;
    margin:4px 0 8px;
    color:#cbd5e1;
    font-size:13px;
    line-height:1.5;
}
.crm-notification-copy time{
    color:#64748b;
    font-size:11.5px;
    font-weight:500;
}

/* Mark as read button */
.crm-notification-entry{
    display:flex;
    width:100%;
    align-items:flex-start;
    gap:14px;
    border:0;
    padding:0;
    background:transparent;
    color:inherit;
    font:inherit;
    text-align:left;
    cursor:pointer;
}
.crm-notification-entry:focus-visible{
    outline:2px solid #60a5fa;
    outline-offset:4px;
    border-radius:8px;
}
.crm-notification-read-state{
    display:block;
    margin-top:4px;
    color:#94a3b8;
    font-size:11px;
}
.crm-notification-item.is-unread .crm-notification-read-state{
    color:#93c5fd;
}

/* Empty state */
.crm-notification-empty{
    margin:0;
    padding:40px 20px;
    text-align:center;
    color:#94a3b8;
    font-size:14px;
}
.crm-notification-empty::before{
    content:"\f18a";
    font-family:"bootstrap-icons";
    display:block;
    margin-bottom:10px;
    font-size:30px;
    color:#475569;
}



/* PROFILE */


.admin-profile{


    display:flex;


    align-items:center;


    gap:12px;


    padding-left:15px;



    border-left:

    1px solid rgba(255,255,255,.10);

}





.admin-avatar{


    height:42px;


    width:42px;


    border-radius:50%;



    background:

    linear-gradient(
        135deg,
        #2563eb,
        #7c3aed
    );



    color:white;



    display:flex;


    align-items:center;


    justify-content:center;



    font-weight:700;



    box-shadow:

    0 8px 20px rgba(37,99,235,.35);

}





.admin-details h4{


    margin:0;


    font-size:14px;


    color:#f8fafc;

}





.admin-details p{


    margin:2px 0 0;


    font-size:12px;


    color:#94a3b8;

}





.admin-profile > i{


    color:#94a3b8;

}





/* SIDEBAR SYNC */


@media(max-width:900px){


.admin-details{

    display:none;

}


}
/* Panel position fix */
.crm-notification-menu{
    position:relative;
}

.crm-notification-panel{
    position:absolute !important;
    top:calc(100% + 14px) !important;
    right:0 !important;
    left:auto !important;
    bottom:auto !important;
    width:380px;
    max-width:calc(100vw - 30px);
    max-height:460px;
    overflow-y:auto;
    z-index:1000;
}

.crm-notification-toggle{
    list-style:none;
}
.crm-notification-toggle::-webkit-details-marker{
    display:none;
}.navbar{
    z-index:1100;
}


/* Light-theme header finish. These overrides are scoped to the header only. */
html[data-theme="light"] body .crm-main-layout > .navbar {
    background: linear-gradient(105deg, #FFFFFF 0%, #FFFFFF 72%, #F5F3FF 100%) !important;
    border-bottom: 1px solid #E0E7FF;
    box-shadow: 0 10px 28px rgba(15, 23, 42, .075), 0 2px 8px rgba(124, 58, 237, .06);
}

html[data-theme="light"] body .crm-main-layout > .navbar::after {
    position: absolute;
    right: 0;
    bottom: 0;
    left: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent 0%, #2563EB 28%, #7C3AED 55%, #EC4899 78%, #06B6D4 100%);
    content: "";
    opacity: .8;
    pointer-events: none;
}

html[data-theme="light"] body .crm-main-layout > .navbar .page-greeting h3,
html[data-theme="light"] body .crm-main-layout > .navbar .admin-details h4 {
    color: #0F172A !important;
}

html[data-theme="light"] body .crm-main-layout > .navbar .page-greeting p,
html[data-theme="light"] body .crm-main-layout > .navbar .admin-details p,
html[data-theme="light"] body .crm-main-layout > .navbar .admin-profile > i {
    color: #64748B !important;
}

html[data-theme="light"] body .crm-main-layout > .navbar .sidebar-toggle,
html[data-theme="light"] body .crm-main-layout > .navbar .crm-theme-toggle {
    color: #475569;
    background: #FFFFFF;
    border-color: #E2E8F0;
}

html[data-theme="light"] body .crm-main-layout > .navbar .sidebar-toggle:hover,
html[data-theme="light"] body .crm-main-layout > .navbar .crm-theme-toggle:hover {
    color: #FFFFFF;
    background: linear-gradient(135deg, #2563EB, #7C3AED);
    border-color: transparent;
    box-shadow: 0 10px 25px rgba(37, 99, 235, .28);
}

html[data-theme="light"] body .crm-main-layout > .navbar .crm-notification-toggle {
    display: inline-flex;
    min-height: 42px;
    align-items: center;
    gap: 9px;
    padding: 0 13px;
    border: 1px solid #DDD6FE;
    border-radius: 13px;
    background: #F5F3FF;
    color: #334155;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

html[data-theme="light"] body .crm-main-layout > .navbar .crm-notification-toggle > i {
    color: #7C3AED;
}

html[data-theme="light"] body .crm-main-layout > .navbar .crm-notification-toggle:hover {
    background: #ECFEFF;
    border-color: #67E8F9;
}

html[data-theme="light"] body .crm-main-layout > .navbar .crm-notification-count {
    padding: 2px 7px;
    border: 1px solid rgba(255, 255, 255, .7);
    border-radius: 999px;
    background: linear-gradient(135deg, #2563EB, #7C3AED);
    color: #FFFFFF;
    font-size: 11px;
    font-weight: 700;
    line-height: 1.4;
}

html[data-theme="light"] body .crm-main-layout > .navbar .admin-profile {
    border-left-color: #DDD6FE;
}

html[data-theme="light"] body .crm-main-layout > .navbar .admin-avatar {
    background: linear-gradient(135deg, #7C3AED, #EC4899);
    box-shadow: 0 8px 20px rgba(124, 58, 237, .24);
}

html[data-theme="light"] body .crm-main-layout > .navbar .crm-notification-panel {
    border-color: #DDD6FE;
    box-shadow: 0 24px 58px rgba(15, 23, 42, .15), 0 4px 14px rgba(124, 58, 237, .07);
}

html[data-theme="light"] body .crm-main-layout > .navbar .crm-notification-heading {
    background: linear-gradient(110deg, #FFFFFF, #F5F3FF);
    border-bottom-color: #EDE9FE;
}

html[data-theme="light"] body .crm-main-layout > .navbar .crm-notification-heading span {
    color: #5B21B6;
    background: #F5F3FF;
    border-color: #DDD6FE;
}

html[data-theme="light"] body .crm-main-layout > .navbar .crm-notification-mark-all:hover {
    background: linear-gradient(135deg, #7C3AED, #EC4899);
    box-shadow: 0 8px 20px rgba(124, 58, 237, .25);
}

html[data-theme="light"] body .crm-main-layout > .navbar .crm-notification-entry:hover .crm-notification-copy strong {
    color: #5B21B6;
}

html[data-theme="light"] body .crm-main-layout > .navbar .crm-notification-item.is-unread {
    background: linear-gradient(90deg, rgba(37, 99, 235, .12), rgba(6, 182, 212, .025) 80%);
}

html[data-theme="light"] body .crm-main-layout > .navbar .crm-notification-item.is-unread::before {
    background: linear-gradient(180deg, #2563EB, #06B6D4);
}

html[data-theme="light"] body .crm-main-layout > .navbar .crm-notification-item.is-unread .crm-notification-read-state {
    color: #5B21B6;
    border-color: #DDD6FE;
    background: #F5F3FF;
}

</style>
