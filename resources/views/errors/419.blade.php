@include('errors.page', [
    'errorCode' => '419',
    'errorTitle' => 'Your session has expired',
    'errorDescription' => 'For your security, this page is no longer active. Return to the dashboard and try your action again.',
    'errorIcon' => 'bi-clock-history',
])
