<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>CRM Pro | Business Suite</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/betabyte.jpg') }}">

    <script>
        (() => {
            try {
                const savedTheme = localStorage.getItem('crm-theme');
                document.documentElement.dataset.theme = ['dark', 'light'].includes(savedTheme) ? savedTheme : 'dark';
            } catch (error) {
                document.documentElement.dataset.theme = 'dark';
            }
        })();
    </script>


    <!-- Bootstrap Icons -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <!-- Vite -->

    @vite(['resources/css/app.css', 'resources/js/app.js'])



</head>


<body
    data-user-id="{{ auth()->check() ? auth()->user()->id : '' }}"
>


    @include('partials.sidebar')

    <button
        class="sidebar-overlay"
        type="button"
        aria-label="Close navigation menu"
        aria-hidden="true"
        tabindex="-1"
        hidden>
    </button>

    <div class="crm-main-layout">
        @include('partials.navbar')

        <main class="content">
            @yield('content')
        </main>
    </div>





<style>

/* ===============================
   CRM PRO FUTURISTIC THEME
================================ */

:root{

    --bg-main:#020617;

    --bg-secondary:#0f172a;

    --glass:
    rgba(255,255,255,0.06);

    --glass-border:
    rgba(255,255,255,0.10);

    --primary:#2563eb;

    --secondary:#7c3aed;

    --cyan:#38bdf8;

    --text:#f8fafc;

    --muted:#94a3b8;

    --success:#22c55e;

}



/* BODY */

body{

    background:
    radial-gradient(
        circle at top right,
        rgba(37,99,235,.18),
        transparent 35%
    ),

    radial-gradient(
        circle at bottom left,
        rgba(124,58,237,.15),
        transparent 35%
    ),

    var(--bg-main);


    color:var(--text);

    font-family:
    'Inter',
    sans-serif;

}



/* ===============================
   GLASS CARD
================================ */


.glass-card{

    background:
    var(--glass);


    border:
    1px solid var(--glass-border);


    backdrop-filter:
    blur(18px);


    -webkit-backdrop-filter:
    blur(18px);


    border-radius:22px;


    box-shadow:

    0 20px 50px
    rgba(0,0,0,.35);


    transition:.35s ease;

}



.glass-card:hover{


    transform:
    translateY(-6px);


    border-color:
    rgba(96,165,250,.35);


    box-shadow:

    0 25px 70px
    rgba(37,99,235,.20);

}



/* ===============================
   HERO SECTION
================================ */


.crm-hero{


    padding:35px;


    border-radius:28px;


    background:

    linear-gradient(
        135deg,
        rgba(37,99,235,.25),
        rgba(124,58,237,.25)
    );


    border:
    1px solid
    rgba(255,255,255,.12);


    position:relative;


    overflow:hidden;

}



.crm-hero::before{


    content:"";


    position:absolute;


    width:300px;

    height:300px;


    background:
    rgba(56,189,248,.25);


    filter:
    blur(90px);


    right:-100px;


    top:-100px;


}



/* ===============================
   KPI CARDS
================================ */


.kpi-card{


    padding:25px;


    border-radius:22px;


    background:

    linear-gradient(
        145deg,
        rgba(255,255,255,.08),
        rgba(255,255,255,.03)
    );


    border:
    1px solid rgba(255,255,255,.1);


    transition:.35s ease;

}



.kpi-card:hover{


    transform:
    translateY(-8px);


    box-shadow:

    0 20px 45px
    rgba(37,99,235,.25);

}



.kpi-icon{


    height:55px;

    width:55px;


    display:flex;


    align-items:center;


    justify-content:center;


    border-radius:16px;


    background:

    linear-gradient(
        135deg,
        var(--primary),
        var(--secondary)
    );


    font-size:22px;

}



/* ===============================
   BUTTONS
================================ */


.btn-premium{


    border:none;


    padding:
    12px 22px;


    border-radius:14px;


    color:white;


    background:

    linear-gradient(
        135deg,
        #2563eb,
        #7c3aed
    );


    box-shadow:

    0 10px 30px
    rgba(37,99,235,.35);


    transition:.3s ease;

}



.btn-premium:hover{


    transform:
    translateY(-3px);


    box-shadow:

    0 15px 40px
    rgba(124,58,237,.45);

}



/* ===============================
   TABLE STYLE
================================ */


.crm-table{


    background:
    rgba(255,255,255,.04);


    border-radius:18px;


    overflow:hidden;

}



.crm-table th{


    color:
    #cbd5e1;


    background:
    rgba(255,255,255,.06);


    border:none;

}



.crm-table td{


    color:
    #e2e8f0;


    border-color:
    rgba(255,255,255,.08);

}



.crm-table tr:hover{


    background:
    rgba(37,99,235,.10);

}



/* ===============================
   BADGES
================================ */


.badge-glow{


    padding:
    6px 12px;


    border-radius:50px;


    background:

    rgba(37,99,235,.18);


    color:
    #93c5fd;


    border:
    1px solid
    rgba(96,165,250,.25);

}



/* ===============================
   SCROLLBAR
================================ */


::-webkit-scrollbar{

    width:8px;

}


::-webkit-scrollbar-thumb{


    background:

    linear-gradient(
        #2563eb,
        #7c3aed
    );


    border-radius:20px;

}



/* ===============================
   FOLLOW-UP REMINDER POPUP (GLOBAL)
================================== */

#followup-reminder-popup-container .swal2-container {
    z-index: 10000 !important;
}

#followup-reminder-popup-container .swal2-popup {
    background: linear-gradient(135deg, #f59e0b, #ef4444) !important;
    border: none !important;
    border-radius: 16px !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
    color: #ffffff !important;
    padding: 24px 32px !important;
    font-family: 'Inter', sans-serif !important;
}

#followup-reminder-popup-container .swal2-title {
    color: #ffffff !important;
    font-size: 20px !important;
    font-weight: 700 !important;
    margin-bottom: 12px !important;
}

#followup-reminder-popup-container .swal2-html-container {
    color: #fef3c7 !important;
    font-size: 15px !important;
    line-height: 1.5 !important;
    margin-bottom: 24px !important;
}

#followup-reminder-popup-container .swal2-confirm,
#followup-reminder-popup-container .swal2-cancel {
    border-radius: 8px !important;
    font-weight: 600 !important;
    padding: 10px 24px !important;
    font-size: 14px !important;
    margin: 0 8px !important;
}

#followup-reminder-popup-container .swal2-confirm {
    background: #ffffff !important;
    color: #f59e0b !important;
    border: none !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

#followup-reminder-popup-container .swal2-cancel {
    background: rgba(255, 255, 255, 0.2) !important;
    color: #ffffff !important;
    border: none !important;
}

#followup-reminder-popup-container .swal2-confirm:hover {
    background: #fef3c7 !important;
    color: #f59e0b !important;
}

#followup-reminder-popup-container .swal2-cancel:hover {
    background: rgba(255, 255, 255, 0.35) !important;
}

#followup-reminder-popup-container .swal2-timer-progress-bar {
    background: rgba(255, 255, 255, 0.4) !important;
    height: 4px !important;
    border-radius: 2px !important;
}

/* ===============================
   EMERGENCY FOLLOW-UP ALERT (GLOBAL)
================================== */

#emergency-alert-container {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

#emergency-alert-container:not(.hidden) {
    display: flex !important;
}

#emergency-alert-container .emergency-alert-card {
    position: relative;
    background: #ffffff;
    color: #111827;
    border-radius: 16px;
    padding: 28px 32px;
    min-width: 360px;
    max-width: 520px;
    box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.55);
    border: 3px solid #dc2626;
    font-family: 'Inter', sans-serif;
    animation: emergencyPulse 1.2s infinite alternate;
}

@keyframes emergencyPulse {
    0% {
        box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.6);
        border-color: #dc2626;
    }
    100% {
        box-shadow: 0 0 0 16px rgba(220, 38, 38, 0);
        border-color: #b91c1c;
    }
}

#emergency-alert-container .emergency-alert-icon {
    position: absolute;
    top: -14px;
    left: 50%;
    transform: translateX(-50%);
    width: 48px;
    height: 48px;
    background: #dc2626;
    color: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    border: 4px solid #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    animation: emergencySpin 1.5s linear infinite;
}

@keyframes emergencySpin {
    from { transform: translateX(-50%) rotate(0deg); }
    to { transform: translateX(-50%) rotate(360deg); }
}

#emergency-alert-container .emergency-alert-title {
    font-size: 20px;
    font-weight: 800;
    color: #dc2626;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

#emergency-alert-container .emergency-alert-title i {
    color: #dc2626;
}

#emergency-alert-container .emergency-alert-body {
    font-size: 14px;
    line-height: 1.55;
    color: #374151;
    margin: 0 0 16px 0;
}

#emergency-alert-container .emergency-alert-detail {
    font-size: 13px;
    color: #6b7280;
    margin: 4px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

#emergency-alert-container .emergency-alert-detail i {
    color: #dc2626;
    width: 16px;
    text-align: center;
}

#emergency-alert-container .emergency-alert-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 22px;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
}

#emergency-alert-container .emergency-alert-btn-confirm {
    background: #dc2626;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    padding: 10px 26px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
    transition: transform 0.15s, box-shadow 0.15s;
}

#emergency-alert-container .emergency-alert-btn-confirm:hover {
    background: #b91c1c;
    box-shadow: 0 6px 16px rgba(220, 38, 38, 0.45);
    transform: translateY(-1px);
}

#emergency-alert-container .emergency-alert-btn-confirm:active {
    transform: translateY(0);
}

html[data-theme="light"] #emergency-alert-container .emergency-alert-card {
    background: #ffffff;
    color: #111827;
}

html[data-theme="light"] #emergency-alert-container .emergency-alert-body {
    color: #374151;
}

html[data-theme="light"] #emergency-alert-container .emergency-alert-detail {
    color: #4b5563;
}

html[data-theme="light"] #emergency-alert-container .emergency-alert-actions {
    border-top-color: #e5e7eb;
}


</style>

@include('partials.flash-alerts')

@include('partials.emergency-alert')

@include('partials.followup-reminder-popup')

</body>


</html>
