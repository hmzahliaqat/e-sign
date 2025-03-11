import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PrimeVue from 'primevue/config';
import 'primeicons/primeicons.css';
import Aura from '@primeuix/themes/aura';
import router from './router';
import { createPinia } from 'pinia';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) });
        const pinia = createPinia();

        // Use Inertia plugin
        vueApp.use(plugin);

        // Use Ziggy for route handling (provided by Jetstream)
        vueApp.use(ZiggyVue);

        // Use your existing PrimeVue configuration
        vueApp.use(PrimeVue, {
            theme: {
                preset: Aura,
                options: {
                    prefix: 'p',
                    darkModeSelector: 'system',
                    cssLayer: false
                }
            }
        });

        // Add your custom router and Pinia
        vueApp.use(router);
        vueApp.use(pinia);

        return vueApp.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
