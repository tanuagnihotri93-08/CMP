<!-- Global Emergency Follow-Up Alert Container (visible on every CRM page) -->
<div
    id="emergency-alert-container"
    class="fixed inset-0 z-[100000] hidden"
    data-emergency-audio-src="{{ asset('audio/emergency-alert.wav') }}"
    aria-live="assertive"
    aria-atomic="true"
>
    <!-- Injected by JavaScript when an emergency broadcast arrives -->
</div>
