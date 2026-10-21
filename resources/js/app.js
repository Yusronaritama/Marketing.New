import './bootstrap';
import { loadRegisterForm } from './register-loader.js';

import Alpine from 'alpinejs';

import { createApp } from 'vue';
import OpeningScreen from './components/OpeningScreen.vue';

window.Alpine = Alpine;

// Export ke global scope untuk diakses dari Blade
window.registerStep = {
    loadRegisterForm
};
Alpine.start();

import CountdownTimer from './components/CountdownTimer.vue';
import ChatbotApp from './components/Chatbot/ChatbotApp.vue';
import RingkasanApp from './components/Ringkasan/RingkasanApp.vue';

document.addEventListener('DOMContentLoaded', () => {
    // 1. Opening Screen App
    const openingEl = document.getElementById('vue-opening-app');
    if (openingEl) {
        const isLoggedIn = openingEl.dataset.isLoggedIn === 'true';
        const logoUrl = openingEl.dataset.logoUrl || '/images/logo_ubp.png';
        const openingApp = createApp(OpeningScreen, {
            isLoggedIn: isLoggedIn,
            logoUrl: logoUrl
        });
        openingApp.mount('#vue-opening-app');
    }
    
    // 2. Countdown Timer App
    const timerEl = document.getElementById('vue-timer-app');
    if (timerEl) {
        const initialSeconds = Number(timerEl.dataset.initialSeconds) || 0;
        const timerApp = createApp(CountdownTimer, {
            initialSeconds: initialSeconds
        });
        timerApp.mount('#vue-timer-app');
    }

    // 3. Chatbot App
    const chatbotEl = document.getElementById('vue-chatbot-app');
    if (chatbotEl) {
        const userName = chatbotEl.dataset.userName || 'Calon Mahasiswa';
        const chatbotApp = createApp(ChatbotApp, {
            userName: userName
        });
        chatbotApp.mount('#vue-chatbot-app');
    }

    // 4. Ringkasan App
    const ringkasanEl = document.getElementById('vue-ringkasan-app');
    if (ringkasanEl) {
        const dataScript = document.getElementById('ringkasan-data');
        let initialData = {};
        if (dataScript) {
            try {
                initialData = JSON.parse(dataScript.textContent || '{}');
            } catch (e) {
                console.error('Failed to parse ringkasan data', e);
            }
        }
        const ringkasanApp = createApp(RingkasanApp, {
            initialData: initialData
        });
        ringkasanApp.mount('#vue-ringkasan-app');
    }
});
