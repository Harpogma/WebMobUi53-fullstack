import './bootstrap';

import App from './AppPollVote.vue';

import { createApp } from 'vue';
import { Quasar, Notify } from 'quasar';

import '@quasar/extras/material-icons/material-icons.css';
import 'quasar/dist/quasar.css';

const el = document.getElementById('app');
const props = JSON.parse(el.dataset.props ?? '{}');

const myApp = createApp(App, {
    token:      props.token,
    loginUrl:   props.loginUrl,
    authUserId: props.authUserId ?? null,
});

myApp.use(Quasar, {
    plugins: { Notify },
});

myApp.mount(el);
