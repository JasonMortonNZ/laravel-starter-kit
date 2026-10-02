import { createPinia } from 'pinia';
import { createApp } from 'vue';
import App from '@/App.vue';
import { initializeTheme } from '@/composables/useAppearance';
import { installHttpInterceptors } from '@/lib/httpInterceptors';
import { router } from '@/router';

const app = createApp(App);

app.use(createPinia());
app.use(router);

app.directive('focus', {
    mounted: (el: HTMLElement, { value }) => {
        if (value !== false) {
            el.focus();
        }
    },
});

installHttpInterceptors();

app.mount('#app');

// This will set light / dark mode on page load...
initializeTheme();
