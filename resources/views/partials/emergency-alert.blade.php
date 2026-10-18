<!-- Global Emergency Follow-Up Alert Container (visible on every CRM page) -->
<div
    id="emergency-alert-container"
    class="fixed inset-0 z-[100000] hidden"
    aria-live="assertive"
    aria-atomic="true"
>
    <!-- Injected by JavaScript when an emergency broadcast arrives -->
</div>

<!-- Hidden audio element for emergency hooter sound (created by JS) -->
<template id="emergency-alert-audio-template">
    <audio id="emergency-alert-sound" preload="auto"></audio>
</template>