@include('errors.page', [
    'errorCode' => '429',
    'errorTitle' => 'Too many requests',
    'errorDescription' => 'The CRM is receiving requests too quickly. Please pause briefly, then try again.',
    'errorIcon' => 'bi-hourglass-split',
])
