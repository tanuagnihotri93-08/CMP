<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="data:,">
    <title>{{ $errorCode }} — {{ $errorTitle }} | CRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="crm-error-page">
    <main class="crm-error-shell" aria-labelledby="crm-error-title">
        <section class="crm-error-card" aria-describedby="crm-error-description">
            <div class="crm-error-illustration" aria-hidden="true">
                <span class="crm-error-orbit crm-error-orbit-one"></span>
                <span class="crm-error-orbit crm-error-orbit-two"></span>
                <span class="crm-error-icon"><i class="bi {{ $errorIcon }}"></i></span>
                <span class="crm-error-spark crm-error-spark-one"></span>
                <span class="crm-error-spark crm-error-spark-two"></span>
            </div>
            <p class="crm-error-code">{{ $errorCode }}</p>
            <h1 id="crm-error-title">{{ $errorTitle }}</h1>
            <p id="crm-error-description" class="crm-error-description">{{ $errorDescription }}</p>
            <div class="crm-error-actions">
                <a class="crm-error-dashboard" href="{{ route('dashboard') }}">
                    <i class="bi bi-grid-1x2" aria-hidden="true"></i>
                    Return to Dashboard
                </a>
                <a class="crm-error-back" href="{{ route('dashboard') }}" data-crm-go-back>
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    Go Back
                </a>
            </div>
            <p class="crm-error-footnote">CRM workspace <span aria-hidden="true">·</span> Secure business operations</p>
        </section>
    </main>
    @if(isset($feedbackError))
        @include('partials.flash-alerts')
    @endif
    <script>
        document.querySelector('[data-crm-go-back]')?.addEventListener('click', (event) => {
            if (window.history.length > 1) {
                event.preventDefault();
                window.history.back();
            }
        });
    </script>
</body>
</html>
