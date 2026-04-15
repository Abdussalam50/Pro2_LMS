function registerExamSecurity() {
    Alpine.data('examSecurity', (config) => ({
        config: config,
        isRestricted: config.isRestricted,
        isFullscreen: false,
        showOverlay: config.isRestricted ? config.showOverlay : false,
        warningCount: 0,
        overlayMessage: 'Ujian ini menggunakan batasan keamanan. Silakan mulai ujian dalam mode layar penuh.',
        showMateri: false,
        showViewer: false,
        viewMateriUrl: '',
        isConfirming: false,

        init() {
            // For open mode: ensure everything is completely disabled
            if (!this.isRestricted) {
                this.showOverlay = false;
                return;
            }
            // For restricted modes: check fullscreen state
            this.checkFullscreen();
        },

        enterFullscreen() {
            if (!this.isRestricted) return;
            const docElm = document.documentElement;
            if (docElm.requestFullscreen) {
                docElm.requestFullscreen().catch(err => {
                    console.error(`Error attempting to enable fullscreen: ${err.message}`);
                    alert('Browser Anda memblokir layar penuh. Silakan izinkan.');
                });
            }
        },

        checkFullscreen() {
            if (!this.isRestricted || this.warningCount >= this.config.maxWarnings || this.isConfirming) return;
            
            const isFull = document.fullscreenElement != null;
            if (this.isFullscreen && !isFull && !this.showOverlay) {
                this.handleViolation('Peringatan! Anda keluar dari mode layar penuh. Aktivitas ini dicatat sebagai pelanggaran.');
            } else if (isFull) {
                this.isFullscreen = true;
                this.showOverlay = false;
            }
        },

        handleVisibilityChange() {
            if (!this.isRestricted || this.warningCount >= this.config.maxWarnings || this.isConfirming || this.showViewer) return;
            
            if (document.visibilityState === 'hidden') {
                this.handleViolation('Peringatan! Anda menduplikasi tab atau berpindah aplikasi. Aktivitas ini dicatat sebagai pelanggaran.');
            }
        },

        handleViolation(message) {
            if (!this.isRestricted) return;
            this.warningCount++;
            this.showOverlay = true;
            
            if (this.warningCount >= this.config.maxWarnings) {
                this.overlayMessage = `PELANGGARAN MAKSIMAL (${this.config.maxWarnings}x). Sistem sedang mengumpulkan jawaban Anda secara otomatis...`;
                this.autoSubmit();
            } else {
                this.overlayMessage = message;
            }
        },
        
        openViewer(url) {
            this.viewMateriUrl = url;
            this.showViewer = true;
        },
        
        confirmSubmit() {
            this.isConfirming = true;
            window.dispatchEvent(new CustomEvent('swal', {
                detail: {
                    icon: 'warning',
                    title: 'Kumpulkan Ujian?',
                    message: 'Apakah Anda yakin ingin mengumpulkan jawaban? Anda tidak dapat mengubah jawaban setelah dikumpulkan.',
                    confirm: true,
                    confirmText: 'Kumpulkan',
                    cancel: true,
                    onConfirm: {
                        componentId: this.$wire.__instance ? this.$wire.__instance.id : null,
                        method: 'submit'
                    },
                    options: {
                        allowOutsideClick: false
                    }
                }
            }));
        },

        cancelSubmit() {
            this.isConfirming = false;
            if (this.isRestricted && !document.fullscreenElement) {
                this.enterFullscreen();
            }
        },

        executeSubmit() {
            this.$wire.submit();
        },
        
        autoSubmit() {
            setTimeout(() => {
                this.$wire.submit();
            }, 3500);
        },

        handleKeydown(e) {
            if (!this.isRestricted || this.config.allowCopyPaste) return;
            
            if (e.keyCode == 123) { e.preventDefault(); }
            if (e.ctrlKey && e.shiftKey && e.keyCode == 73) { e.preventDefault(); }
            if (e.ctrlKey && e.shiftKey && e.keyCode == 74) { e.preventDefault(); }
            if (e.ctrlKey && e.keyCode == 85) { e.preventDefault(); }
        },

        acknowledgeWarning() {
            if (!this.isRestricted) return;
            if (this.warningCount >= this.config.maxWarnings) return;
            if (document.fullscreenElement) {
                this.showOverlay = false;
            } else {
                this.enterFullscreen();
            }
        }
    }));
}

// Register the component whether Alpine is already initialized or not.
// This handles both fresh page loads and Livewire SPA navigation.
if (window.Alpine) {
    // Alpine already initialized (SPA navigation case)
    registerExamSecurity();
} else {
    // Alpine not yet initialized (fresh page load case)
    document.addEventListener('alpine:init', registerExamSecurity);
}
