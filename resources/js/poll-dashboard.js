import './bootstrap';

import App from './AppPollDashboard.vue';

import { createApp } from "vue";
import { Quasar } from "quasar";

import "@quasar/extras/material-icons/material-icons.css";
import "quasar/dist/quasar.css";


const el = document.getElementById('app');
const props = JSON.parse(el.dataset.props ?? '{}');
const myApp = createApp(App, props);

myApp.use(Quasar, {
    config: {
        brand: {
            // Définissez ici vos couleurs de thème personnalisées
        },
    },
    plugins: {},
});

myApp.mount(el);
