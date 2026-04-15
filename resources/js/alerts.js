import Swal from 'sweetalert2';

/**
 * Global Alert Handler for Pro2Lms
 * Listen for 'swal' events dispatched from Livewire or standard JS.
 */
window.addEventListener('swal', (event) => {
    const data = event.detail;

    // Use primary color from CSS variable if available
    const primaryColor = getComputedStyle(document.documentElement)
        .getPropertyValue('--primary-color').trim() || '#4f46e5';

    Swal.fire({
        icon: data.icon || 'info',
        title: data.title || '',
        text: data.message || '',
        timer: data.timer || (data.confirm ? null : 3000),
        timerProgressBar: !!(!data.confirm && (data.timer || true)),
        showConfirmButton: !!data.confirm,
        confirmButtonText: data.confirmText || 'OK',
        confirmButtonColor: primaryColor,
        showCancelButton: !!data.cancel,
        cancelButtonText: data.cancelText || 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-[2rem] border-none shadow-2xl',
            confirmButton: 'rounded-xl px-6 py-3 font-bold uppercase tracking-widest text-xs',
            cancelButton: 'rounded-xl px-6 py-3 font-bold uppercase tracking-widest text-xs',
            title: 'font-black tracking-tight text-gray-900',
            htmlContainer: 'font-medium text-gray-500'
        },
        ...data.options // Allow passing any other SweetAlert2 options
    }).then((result) => {
        // Handle callbacks for confirmations
        if (result.isConfirmed && data.onConfirm) {
            if (data.onConfirm.componentId) {
                // If it's a Livewire component callback
                const component = Livewire.find(data.onConfirm.componentId);
                if (component) {
                    component.call(data.onConfirm.method, ...(data.onConfirm.params || []));
                }
            } else if (typeof window[data.onConfirm] === 'function') {
                // If it's a global JS function
                window[data.onConfirm]();
            }
        }
    });
});

// Helper for session flash messages
window.showSessionAlert = (type, message) => {
    window.dispatchEvent(new CustomEvent('swal', {
        detail: {
            icon: type,
            title: type === 'success' ? 'Berhasil!' : 'Peringatan',
            message: message,
            confirm: false
        }
    }));
};
