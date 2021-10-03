import { createApp } from 'vue'
import App from './App.vue'
import { createRouter, createWebHistory, createWebHashHistory } from 'vue-router';
import dayjs from 'dayjs';
import 'dayjs/locale/de';
import 'vfonts/Lato.css'
import 'vfonts/FiraCode.css'

import IndexComponent from '../consumer/components/Index.vue';
import LoginComponent from './components/Login.vue';

const routes = [
	{
		path: '/404',
		name: 'NotFound',
		component: IndexComponent
	},
    {
        path: '/login',
        name: 'Login',
        component: LoginComponent
    }
];

dayjs.locale('de');

const app = createApp(App);
app
	.use(createRouter({
		history: (import.meta.env.MODE == 'development') ? createWebHashHistory() : createWebHistory('/admin'),
		routes,
	}))
	.mount('#app');

app.provide('$isMobileScreen', screen.width <= 760);
app.provide('$isDarkMode', window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);