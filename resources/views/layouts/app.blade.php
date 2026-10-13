<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>
        CRM System
    </title>


    <!-- Bootstrap Icons -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <!-- Vite -->

    @vite(['resources/css/app.css', 'resources/js/app.js'])



</head>


<body>


    @include('partials.sidebar')

    <button
        class="sidebar-overlay"
        type="button"
        aria-label="Close navigation menu"
        aria-hidden="true"
        tabindex="-1"
        hidden>
    </button>

    @include('partials.navbar')



    <main class="content">


        @yield('content')


    </main>





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



</style>

@include('partials.flash-alerts')

</body>


</html>