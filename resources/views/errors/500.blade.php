@include('errors.page', [
    'errorCode' => '500',
    'errorTitle' => 'Something went wrong',
    'errorDescription' => 'The CRM could not complete this request. Please return to the dashboard and try again. If the problem continues, contact your CRM administrator.',
    'errorIcon' => 'bi-exclamation-diamond',
])
