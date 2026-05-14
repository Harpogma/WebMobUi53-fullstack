import './bootstrap';

import App from './AppPollDashboard.vue';
import { polls } from "./store/polls";

import { createApp } from "vue";
import { Quasar, Loading } from "quasar";

import "@quasar/extras/material-icons/material-icons.css";
import "quasar/dist/quasar.css";

const el = document.getElementById('app');
const props = JSON.parse(el.dataset.props ?? '{}');
polls.value = props.polls;

const myApp = createApp(App, { loginUrl: props['login-url'] });

myApp.use(Quasar, {
    config: {
        brand: {
            primary: "#7E57C2",
            secondary: "#B39DDB",
            accent: "#AB47BC",
            dark: "#4A148C",
            positive: "#9575CD",
            info: "#7E57C2",
            warning: "#CE93D8",
            negative: "#E1BEE7",
        },
    },
    plugins: { Loading },
});

myApp.mount(el);
