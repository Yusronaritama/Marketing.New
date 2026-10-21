{{-- CUSTOM CSS --}}
<style>
    .glass-white { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.5); }
    .glass-form { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.6); border-radius: 1rem; padding: 1.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .section-title { font-size: 1.125rem; font-weight: 800; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 1rem; }
    .input-glass { width: 100%; margin-top: 0.25rem; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 0.6rem 1rem; color: #1f2937; font-size: 0.875rem; transition: all 0.2s; }
    .input-glass:focus { outline: none; border-color: #3b82f6; background-color: white; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .label-text { font-size: 0.75rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-left: 0.25rem; }
    .step-btn { padding: 0.5rem 1rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; color: #4b5563; background: transparent; transition: all 0.3s; display: flex; align-items: center; }
    .step-btn.active-step { color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .btn-next { color: white; font-weight: 700; padding: 0.75rem 1.5rem; border-radius: 0.75rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 0.5rem; transition: transform 0.2s; }
    .btn-next:hover { transform: translateY(-2px); }
    .btn-back { color: #6b7280; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; transition: color 0.2s; }
    .btn-back:hover { color: #1f2937; }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
        20%, 40%, 60%, 80% { transform: translateX(4px); }
    }
    .input-error {
        border-color: #ef4444 !important; /* Merah */
        background-color: #fef2f2 !important; /* Pink tipis */
        animation: shake 0.4s ease-in-out; /* Efek getar */
    }
</style>