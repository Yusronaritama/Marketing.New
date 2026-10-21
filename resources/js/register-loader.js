export function loadRegisterForm(initialData) {
    document.addEventListener('alpine:init', () => {
        Alpine.data('registerForm', () => {
            const { initializeRegisterForm } = window.registerStep;
            return initializeRegisterForm(initialData);
        });
    });
}

// Export default untuk compatibility
export default loadRegisterForm;