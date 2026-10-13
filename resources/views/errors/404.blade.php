@php($feedbackError = 'The requested record could not be found.')
@include('errors.page', [
    'errorCode' => '404',
    'errorTitle' => 'Page not found',
    'errorDescription' => 'The page or record may have moved, been removed, or the address may be incorrect.',
    'errorIcon' => 'bi-search',
])
