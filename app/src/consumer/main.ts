import { createApp } from 'vue'
import App from './App.vue'
import { createRouter, createWebHistory } from 'vue-router';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import 'dayjs/locale/de';

import 'vfonts/Lato.css';
import 'virtual:windi.css';

import IndexComponent from './components/Index.vue';
import BookingComponent from './components/Booking.vue';

const routes = [
	{
		path: '/404',
		name: 'NotFound',
		component: IndexComponent
	},
	{
		path: '/booking/:id',
		component: () => import('./components/BookingView.vue')
	},
	{
		path: '/:id',
		component: BookingComponent
	},
	{
		path: '/:pathMatch(.*)*',
		name: 'CatchAny',
		component: IndexComponent
	}
];

dayjs.locale('de');
dayjs.extend(utc);

const app = createApp(App);

let $isDarkMode = false;
if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
	document.documentElement.classList.add('dark');
	$isDarkMode = true;
} else {
	document.documentElement.classList.remove('dark');
}

app.provide('$isMobileScreen', screen.width <= 760);
app.provide('$isDarkMode', $isDarkMode);

app
	.use(createRouter({
		history: createWebHistory('/'),
		routes,
	}))
	.mount('#app');

