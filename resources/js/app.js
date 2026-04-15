import './bootstrap';
import './alerts';
import * as mathlive from 'mathlive';
import {createIcons, icons} from 'lucide';


window.mathlive = mathlive;

// Disable MathLive sounds to prevent 404 errors for missing WAV files
if (window.mathVirtualKeyboard) {
    window.mathVirtualKeyboard.soundDirectory = null;
}
if (window.MathfieldElement) {
    window.MathfieldElement.soundsDirectory = null;
    window.MathfieldElement.fontsDirectory = window.location.origin + '/fonts/mathlive/';
}

window.lucide = {createIcons, icons};

// Initialize Lucide icons
function initializeIcons() {
    console.log('Attempting to initialize Lucide icons...');
    if (window.lucide && window.lucide.createIcons) {
        window.lucide.createIcons({
            icons: window.lucide.icons
        });
        console.log('Lucide icons initialized successfully');
    } else {
        console.error('Lucide library not found in window object');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM Content Loaded - Initializing Icons');
    initializeIcons();
});

document.addEventListener('livewire:navigated', () => {
    console.log('Livewire Navigated - Re-initializing Icons');
    initializeIcons();
});

document.addEventListener('livewire:initialized', () => {
    console.log('Livewire Initialized - Setting up request hook');
    Livewire.hook('request', ({ respond, succeed }) => {
        succeed(({ responses }) => {
            setTimeout(initializeIcons, 50);
        });
    });
});