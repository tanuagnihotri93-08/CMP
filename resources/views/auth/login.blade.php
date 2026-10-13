<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>CRM Pro | Login</title>

@vite(['resources/js/app.js'])

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


<style>

*{

    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter',Arial,sans-serif;

}


body{

    min-height:100vh;

    display:flex;

    align-items:center;

    justify-content:center;

    overflow-x:clip;
    overflow-y:auto;

    background:
    radial-gradient(circle at top left,#2563eb55,transparent 35%),
    radial-gradient(circle at bottom right,#7c3aed55,transparent 35%),
    #020617;

}



/* Background Glow */


body::before{

    content:"";

    position:absolute;

    width:400px;

    height:400px;

    background:#2563eb;

    filter:blur(180px);

    top:-150px;

    left:-150px;

    opacity:.4;

}



body::after{

    content:"";

    position:absolute;

    width:400px;

    height:400px;

    background:#9333ea;

    filter:blur(180px);

    bottom:-150px;

    right:0;

    opacity:.4;

}

.login-card{

    width:min(460px,calc(100vw - 32px));

    padding:50px 45px;

    position:relative;

    z-index:2;


    background:

    linear-gradient(
        145deg,
        rgba(255,255,255,0.16),
        rgba(255,255,255,0.06)
    );


    backdrop-filter:blur(25px);


    -webkit-backdrop-filter:blur(25px);



    border:

    1px solid rgba(255,255,255,0.22);



    border-radius:32px;



    box-shadow:

    0 40px 100px rgba(0,0,0,.55),

    inset 0 0 40px rgba(255,255,255,.05);



    animation:cardEnter .7s ease;

}

@media(max-width:480px){

    body{
        align-items:flex-start;
        padding:20px 0;
    }

    .login-card{
        padding:38px 24px;
        border-radius:24px;
    }

    .brand{
        margin-bottom:28px;
    }

    .brand h1{
        font-size:30px;
    }

}

@media(max-height:620px){

    body{
        align-items:flex-start;
        padding-top:16px;
        padding-bottom:16px;
    }

}

.login-card::before{

    content:"";

    position:absolute;

    inset:-2px;

    border-radius:36px;


    background:

    linear-gradient(
        135deg,
        #60a5fa,
        transparent,
        #a855f7
    );


    z-index:-1;


    opacity:.45;


    filter:blur(15px);

}

@keyframes cardEnter{


    from{

        opacity:0;

        transform:translateY(30px) scale(.96);

    }


    to{

        opacity:1;

        transform:translateY(0) scale(1);

    }


}

.brand{

    text-align:center;

    margin-bottom:35px;

}
.brand-icon{

    height:78px;

    width:78px;

    margin:auto;

    border-radius:24px;


    display:flex;

    align-items:center;

    justify-content:center;


    font-size:34px;


    color:white;


    background:

    linear-gradient(
        135deg,
        #3b82f6,
        #9333ea
    );


    box-shadow:

    0 20px 45px rgba(59,130,246,.45),

    inset 0 1px 10px rgba(255,255,255,.3);


}

.brand h1{

    margin-top:22px;

    color:white;

    font-size:34px;

    font-weight:700;

    letter-spacing:.5px;

}

.brand p{


    color:#cbd5e1;

    margin-top:8px;

    font-size:14px;

}





.input-box{


    margin-bottom:22px;


}



.input-box label{


    color:#e2e8f0;

    font-size:14px;

    margin-bottom:8px;

    display:block;


}




.input-field{


    position:relative;


}



.input-field i{


    position:absolute;

    left:16px;

    top:50%;

    transform:translateY(-50%);

    color:#94a3b8;


}



.input-field input{


    width:100%;


    padding:16px 18px 16px 48px;


    border-radius:16px;


    background:

    rgba(255,255,255,.10);


    border:

    1px solid rgba(255,255,255,.18);



    color:white;


    font-size:15px;


    transition:.3s;


}



.input-field input::placeholder{

    color:#94a3b8;

}



.input-field input:focus{


    border-color:#60a5fa;


    box-shadow:

    0 0 0 4px rgba(96,165,250,.15);


}







.login-btn{


    width:100%;


    padding:15px;


    border:none;


    border-radius:14px;


    cursor:pointer;


    color:white;


    font-size:16px;


    font-weight:600;


    background:


    linear-gradient(
    135deg,
    #2563eb,
    #7c3aed
    );


    box-shadow:


    0 15px 30px rgba(37,99,235,.35);


    transition:.3s;


}



.login-btn:hover{


    transform:translateY(-3px);


    box-shadow:


    0 20px 40px rgba(124,58,237,.45);


}






.error{


    background:rgba(239,68,68,.15);


    border:

    1px solid rgba(239,68,68,.3);


    color:#fecaca;


    padding:12px;


    border-radius:12px;


    margin-bottom:20px;


}





.footer{


    text-align:center;


    color:#94a3b8;


    margin-top:25px;


    font-size:13px;


}



</style>


</head>


<body>



<div class="login-card">



<div class="brand">


<div class="brand-icon">

<i class="bi bi-shield-lock"></i>

</div>


<h1>
CRM Pro
</h1>


<p>
Luxury Business Management Suite
</p>


</div>





<x-crm.validation-alert title="We could not sign you in. Check the details below." />





<form method="POST" action="{{ request()->url() }}" novalidate>

@csrf



<div class="input-box">


<label for="email">
Email Address
</label>


<div class="input-field">


<i class="bi bi-envelope"></i>


<input 
id="email"
type="email"
name="email"
@class(['crm-invalid-field' => $errors->has('email')])
aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
@if($errors->has('email')) aria-describedby="crm-error-email" @endif
placeholder="Enter email"
required
value="{{ old('email') }}">


</div>

<x-crm.field-error field="email" />

</div>






<div class="input-box">


<label for="password">
Password
</label>


<div class="input-field">


<i class="bi bi-lock"></i>


<input 
id="password"
type="password"
name="password"
@class(['crm-invalid-field' => $errors->has('password')])
aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
@if($errors->has('password')) aria-describedby="crm-error-password" @endif
placeholder="Enter password"
required>


</div>

<x-crm.field-error field="password" />

</div>






<button class="login-btn">

Access CRM Dashboard

</button>


</form>




<div class="footer">

CRM Pro © 2026

</div>



</div>


@include('partials.flash-alerts')


</body>

</html>