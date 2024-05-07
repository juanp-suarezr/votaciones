import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
// theme
import 'primevue/resources/themes/aura-light-blue/theme.css';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import PrimeVue from 'primevue/config';
import 'vue-toast-notification/dist/theme-default.css';


const appName = 'VotaYa';

createInertiaApp({
    title: (title) => `${title} - VotaYa`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(VueSweetalert2)
            .use(PrimeVue)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#0ea5e9',
        // Whether the NProgress spinner will be shown...
        showSpinner: true,
    },
});
