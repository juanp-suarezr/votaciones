import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
// theme
import Aura from '@primeuix/themes/aura';
import { definePreset } from '@primeuix/themes';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import PrimeVue from 'primevue/config';
import 'vue-toast-notification/dist/theme-default.css';
import Tooltip from 'primevue/tooltip';
import Traduccion from '../../public/traduccion.json';
import { VueReCaptcha } from "vue-recaptcha-v3";


const MyPreset = definePreset(Aura, {
    semantic: {
        primary: {
            50: '{indigo.50}',
            100: '{indigo.100}',
            200: '{indigo.200}',
            300: '{indigo.300}',
            400: '{indigo.400}',
            500: '{indigo.500}',
            600: '{indigo.600}',
            700: '{indigo.700}',
            800: '{indigo.800}',
            900: '{indigo.900}',
            950: '{indigo.950}'
        },
        focusRing: {
            width: '2px',
            style: 'dashed',
            color: '{primary.color}',
            offset: '1px'
        }
    }
});

const appName = 'VotaYa';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const application =  createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(VueSweetalert2)
            .use(PrimeVue, {
                theme: {
                    preset: MyPreset,
                    options: {
                        prefix: 'p',
                        darkModeSelector: false,
                    },
                    cssLayer: {
                        name: "primevue",
                        order: "tailwind-base, primevue, tailwind-utilities",
                    },
                },
                locale: Traduccion,
            })
            .use(ZiggyVue)
            .use(VueReCaptcha, {
                siteKey: props.initialPage.props.recaptcha.site_key, // Obtiene la clave desde Inertia
                loaderOptions: {
                    autoHideBadge: true, // opcional para ocultar el logo flotante
                },
            })
            .directive('tooltip', Tooltip)
            .mount(el);

            delete el.dataset.page;
        return application;
    },
    progress: {
        color: '#0ea5e9',
        // Whether the NProgress spinner will be shown...
        showSpinner: true,
    },
});
