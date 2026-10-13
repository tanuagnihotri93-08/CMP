@php($feedbackError = 'You are not authorized to access this resource.')
@include('errors.page', [
    'errorCode' => '403',
    'errorTitle' => 'Access denied',
    'errorDescription' => 'Your account does not have permission to view this page. If you believe this is a mistake, contact your CRM administrator.',
    'errorIcon' => 'bi-shield-lock',
])
