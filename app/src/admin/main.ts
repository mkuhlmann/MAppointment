import { createApp } from 'vue'
import { createRouter, createWebHistory, createWebHashHistory, RouteRecordRaw } from 'vue-router';
import App from './AdminApp.vue';

import dayjs from 'dayjs';
import 'dayjs/locale/de';
import 'vfonts/Lato.css'
import 'vfonts/FiraCode.css'
import 'virtual:windi.css';

import IndexComponent from '../consumer/components/Index.vue';
import AppointmentComponent from './views/Appointments.vue';
import LoginComponent from './views/Login.vue';
import DashboardComponent from './views/Dashboard.vue';

import { useApi } from '@/shared/composables/api';

const routes: RouteRecordRaw[] = [
	{
		path: '/404',
		name: 'NotFound',
		component: IndexComponent
	},
	{
		path: '/login',
		name: 'Login',
		component: LoginComponent
	},
	{
		path: '/appointments',
		name: 'Appointments',
		component: AppointmentComponent
	},
	{
		path: '/',
		name:'Dashboard',
		component: DashboardComponent
	}
];

dayjs.locale('de');

const vueApp = createApp(App);
const router = createRouter({
	history: (import.meta.env.MODE == 'development') ? createWebHashHistory() : createWebHistory('/admin'),
	routes,
});
vueApp
	.use(router)
	.mount('#app');

const { isSignedIn } = useApi();
if (!isSignedIn()) {
	router.push('/login');
}

vueApp.provide('$isMobileScreen', screen.width <= 760);
vueApp.provide('$isDarkMode', window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);